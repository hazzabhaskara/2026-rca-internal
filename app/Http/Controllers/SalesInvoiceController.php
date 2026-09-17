<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\CompanyProfile;
use App\Customers;
use App\Employee;
use App\Item;
use App\LocationInventory;
use App\PaymentMethod;
use App\Project;
use App\SalesInvoice;
use App\SalesInvoiceDetail;
use App\SalesOrder;
use App\SalesTaxes;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesInvoiceController extends Controller
{
    //

    public function index()
    {
        $data = SalesInvoice::with(['customer', 'jenisPembayaran', 'salesOrder', 'salesPerson'])
            ->orderBy('invoice_date', 'asc')
            ->orderBy('status', 'asc')
            ->orderBy('invoice_number', 'asc')
            ->paginate(10);
        return view('sales_invoice.index', compact('data'));
    }
    public function create()
    {
        $customer = Customers::all();
        $jenis_pembayaran = PaymentMethod::all();
        $employee = Employee::all();
        $items = Item::all(); // semua item yang bisa dipilih
        $accounts = chartOfAccount::all();
        $sales_order = SalesOrder::where('status_sales', 0)->get();
        $project = Project::all();
        $sales_taxes = SalesTaxes::where('type', 'input_tax')->get();
        $withholding = SalesTaxes::where('type', 'withholding_tax')->get();
        $freightAccount = \App\linkedAccounts::with('akun')
            ->where('kode', 'Freight Revenue')
            ->first();
        $lokasi_inventory = LocationInventory::all();

        return view('sales_invoice.create', compact('customer', 'jenis_pembayaran', 'employee', 'items', 'accounts', 'sales_order', 'project', 'sales_taxes', 'freightAccount', 'lokasi_inventory', 'withholding'));
    }

    public function getItemsFromSalesOrder($salesOrderId)
    {
        $salesOrder = SalesOrder::with([
            'details.item.quantities',
            'details.item.accounts.cogsAccount',
            'details.item.accounts.assetAccount',
            'details.account',
            'details.sales_taxes.salesAccount'

        ])
            ->findOrFail($salesOrderId);

        $locationId = $salesOrder->location_id; // 🔹 ambil lokasi dari SO header

        return response()->json([
            'items' => $salesOrder->details->map(function ($detail) use ($locationId) {
                $item = $detail->item;

                // 🔹 filter qty & value sesuai location_id
                $onHandQty   = $item->quantities->where('location_id', $locationId)->sum('on_hand_qty') ?: 1;
                $onHandValue = $item->quantities->where('location_id', $locationId)->sum('on_hand_value');
                $unitCost    = $onHandQty > 0 ? $onHandValue / $onHandQty : 0;

                return [
                    'id'                  => $item->id,
                    'item_number'         => $item->item_number ?? '',
                    'item_name'           => $item->item_name ?? '',
                    'description'         => $detail->item_description,
                    'quantity'            => $detail->quantity,
                    'order'               => $detail->order,
                    'back_order'          => $detail->back_order,
                    'unit'                => $item->units->unit_of_measure ?? $detail->unit,
                    'base_price'          => $detail->base_price,
                    'discount'            => $detail->discount ?? 0,
                    'price'               => $detail->price ?? 0,
                    'amount'              => $detail->amount ?? 0,

                    // Pajak
                    'tax_id'              => $detail->tax_id ?? null,
                    'tax_rate'            => optional($detail->sales_taxes)->rate ?? 0,
                    'tax_sales_account_id' => optional($detail->sales_taxes)->sales_account_id ?? null,
                    'tax_sales_account_name' => optional(optional($detail->sales_taxes)->salesAccount)->nama_akun ?? '',

                    // Pendapatan (dari detail.account)
                    'account_id'          => $detail->account_id,
                    'account_code'        => $detail->account->kode_akun ?? '',
                    'account_name'        => $detail->account->nama_akun ?? '',

                    // Tambahan untuk HPP
                    'type'                => $item->type,
                    'unit_cost'           => $unitCost,
                    'cogs_account_id'     => $item->accounts->cogs_account_id ?? null,
                    'cogs_account_code'   => optional($item->accounts->cogsAccount)->kode_akun ?? '',
                    'cogs_account_name'   => optional($item->accounts->cogsAccount)->nama_akun ?? 'COGS',
                    'asset_account_id'    => $item->accounts->asset_account_id ?? null,
                    'asset_account_code'  => optional($item->accounts->assetAccount)->kode_akun ?? 'Inventory',
                    'asset_account_name'  => optional($item->accounts->assetAccount)->nama_akun ?? 'Inventory',
                ];
            }),
        ]);
    }

    private function generateKodeOrder($dateOrder)
    {
        // Formatkan tanggal order sesuai kebutuhan (misal YYYYMMDD)
        $dateFormatted = \Carbon\Carbon::parse($dateOrder)->format('Ymd');

        $prefix = 'SI-' . $dateFormatted . '-';

        // Cari order terakhir berdasarkan tanggal order yg sama
        $last = \App\SalesInvoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($last && preg_match('/' . $prefix . '(\d+)/', $last->invoice_number, $matches)) {
            $number = (int) $matches[1] + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $paymentStatus = PaymentMethod::where('id', $request->jenis_pembayaran_id)->value('status_payment');

            if ($paymentStatus != 1) {
                $statusSales = 3;
            } elseif ($request->sales_order_id) {
                $statusSales = 1;
            } else {
                $statusSales = 0;
            }
            // 1️⃣ HEADER
            $salesInvoice = \App\SalesInvoice::create([
                'invoice_number'      => $this->generateKodeOrder($request->invoice_date),
                'invoice_date'        => $request->invoice_date,
                'shipping_date'       => $request->shipping_date,
                'customers_id'        => $request->customers_id,
                'sales_order_id'      => $request->sales_order_id,
                'payment_method_account_id' => $request->header_account_id,
                'withholding_tax'     => $request->withholding_tax,
                'withholding_value'   => (float) str_replace(',', '', $request->withholding_value),
                'sales_person_id'     => $request->sales_person_id ?? null,
                'location_id'         => $request->location_id,
                'jenis_pembayaran_id' => $request->jenis_pembayaran_id,
                'shipping_address'    => $request->shipping_address,
                'freight'             => (float) str_replace(',', '', $request->freight),
                'early_payment_terms' => $request->early_payment_terms,
                'messages'            => $request->messages,
                'status' => $statusSales,
            ]);
            // dump('📌 SalesInvoice tersimpan:', $salesInvoice->toArray());

            // 3b) Update status SO
            if ($request->sales_order_id) {
                \App\SalesOrder::whereKey($request->sales_order_id)
                    ->update(['status_sales' => $statusSales]);
            }

            // 2️⃣ DETAIL ITEM + perhitungan stok
            $lines = [];

            foreach ($request->items as $row) {
                $base_price = (float) str_replace(',', '', $row['base_price'] ?? 0);
                $discount   = (float) str_replace(',', '', $row['discount'] ?? 0);
                $price      = (float) str_replace(',', '', $row['price'] ?? 0);
                $amount     = (float) str_replace(',', '', $row['amount'] ?? 0);
                $tax_amount = (float) str_replace(',', '', $row['tax_value'] ?? 0);
                $tax_id     = $row['tax_id'] ?? null;
                $shippedQty = (float) str_replace(',', '', $row['quantity'] ?? 0);

                $detail = \App\SalesInvoiceDetail::create([
                    'sales_invoice_id' => $salesInvoice->id,
                    'item_id'          => $row['item_id'],
                    'quantity'         => $shippedQty,
                    'order_quantity'   => $shippedQty,
                    'back_order'       => (float) str_replace(',', '', $row['back_order'] ?? 0),
                    'unit'             => $row['unit'],
                    'description'      => $row['description'],
                    'base_price'       => $base_price,
                    'discount'         => $discount,
                    'price'            => $price,
                    'amount'           => $amount,
                    'tax'              => $tax_amount,
                    'account_id'       => $row['account_id'],
                    'project_id'       => $row['project_id'],
                    'tax_id'           => $tax_id,
                ]);
                // dump("📝 Detail tersimpan untuk item {$row['item_id']}:", $detail->toArray());

                // ⚙️ Ambil definisi unit
                $itemUnit = \App\ItemUnit::where('item_id', $row['item_id'])->first();
                $qty = (float) $row['quantity'];
                $unit = $row['unit'] ?? null;

                // 🔹 Konversi selling/buying unit → stocking unit
                if ($itemUnit) {
                    if ($unit === $itemUnit->unit_of_measure) {
                        $qtyChange = $qty;
                    } elseif ($unit === $itemUnit->selling_unit) {
                        $qtyChange = $qty * max(1, (float)$itemUnit->selling_relationship);
                    } elseif ($unit === $itemUnit->buying_unit) {
                        $qtyChange = $qty * max(1, (float)$itemUnit->buying_relationship);
                    } else {
                        $qtyChange = $qty;
                    }
                } else {
                    $qtyChange = $qty;
                }

                // 🔻 Karena penjualan → stok berkurang
                $qtyChange = -$qtyChange;

                // Ambil unit cost SEBELUM stok disesuaikan
                $iq = \App\ItemQuantities::where('item_id', $row['item_id'])
                    ->where('location_id', $request->location_id)
                    ->first();

                $unitCost = ($iq && $iq->on_hand_qty > 0)
                    ? ($iq->on_hand_value / $iq->on_hand_qty)
                    : 0.0;

                $cogsAmount  = abs($qtyChange) * $unitCost;
                $valueChange = -$cogsAmount;

                // ✅ Kurangi stok (pakai adjustItemQuantity)
                $this->adjustItemQuantity($row['item_id'], $request->location_id, $qtyChange, $valueChange, true);

                // dump("📦 Update Inventory untuk Item #{$row['item_id']}", [
                //     'qty_before'   => optional($iq)->on_hand_qty,
                //     'value_before' => optional($iq)->on_hand_value,
                //     'qtyChange'    => $qtyChange,
                //     'valueChange'  => $valueChange,
                //     'unitCost'     => $unitCost,
                //     'qty_after'    => (optional($iq)->on_hand_qty ?? 0) + $qtyChange,
                //     'value_after'  => (optional($iq)->on_hand_value ?? 0) + $valueChange,
                // ]);

                // Simpan cache untuk jurnal
                $lines[] = [
                    'item_id'      => $row['item_id'],
                    'desc'         => $row['description'],
                    'revenue_acct' => $row['account_id'],
                    'revenue_amt'  => $amount,
                    'tax_id'       => $tax_id,
                    'tax_amount'   => $tax_amount,
                    'shipped_qty'  => $shippedQty,
                    'unit_cost'    => $unitCost,
                    'cogs_amount'  => $cogsAmount,
                ];
            }

            // 3️⃣ JURNAL HEADER
            $journal = \App\JournalEntry::create([
                'source'  => 'sales_invoice',
                'tanggal' => $request->invoice_date,
                'comment' => "Sales Invoice #{$salesInvoice->invoice_number}",
            ]);
            // dump('📒 Journal Entry tersimpan:', $journal->toArray());

            $coaCode = fn($id) => $id ? \App\ChartOfAccount::whereKey($id)->value('kode_akun') : null;

            foreach ($lines as $ln) {
                // Revenue (Credit)
                if ($ln['revenue_amt'] > 0 && !empty($ln['revenue_acct'])) {
                    $rev = \App\JournalEntryDetail::create([
                        'journal_entry_id' => $journal->id,
                        'kode_akun'        => $coaCode($ln['revenue_acct']),
                        'debits'           => 0,
                        'credits'          => $ln['revenue_amt'],
                        'comment'          => $ln['desc'],
                        'status' => 2
                    ]);
                    // dump('💰 Journal Revenue:', $rev->toArray());
                }

                // COGS (Debit) & Inventory (Credit)
                if ($ln['cogs_amount'] > 0) {
                    $cogsAcctId  = \App\ItemAccount::where('item_id', $ln['item_id'])->value('cogs_account_id');
                    $assetAcctId = \App\ItemAccount::where('item_id', $ln['item_id'])->value('asset_account_id');

                    $cogs = \App\JournalEntryDetail::create([
                        'journal_entry_id' => $journal->id,
                        'kode_akun'        => $coaCode($cogsAcctId),
                        'debits'           => $ln['cogs_amount'],
                        'credits'          => 0,
                        'comment'          => 'COGS ' . $ln['desc'],
                        'status' => 2
                    ]);
                    // dump('📊 Journal COGS:', $cogs->toArray());

                    $inv = \App\JournalEntryDetail::create([
                        'journal_entry_id' => $journal->id,
                        'kode_akun'        => $coaCode($assetAcctId),
                        'debits'           => 0,
                        'credits'          => $ln['cogs_amount'],
                        'comment'          => 'Inventory Out ' . $ln['desc'],
                        'status' => 2
                    ]);
                    // dump('📦 Journal Inventory Out:', $inv->toArray());
                }

                // PPN Keluaran (Credit)
                if ($ln['tax_amount'] > 0 && $ln['tax_id']) {
                    $tax = \App\SalesTaxes::find($ln['tax_id']);
                    if ($tax && $tax->type === 'input_tax') {
                        \App\JournalEntryDetail::create([
                            'journal_entry_id' => $journal->id,
                            'kode_akun'        => $coaCode($tax->sales_account_id),
                            'debits'           => 0,
                            'credits'          => $ln['tax_amount'],
                            'comment'          => 'PPN Keluaran',
                            'status' => 2
                        ]);
                    }
                }
            }

            // Freight (Credit)
            if ((float) $salesInvoice->freight > 0) {
                $freightLinked = \App\LinkedAccounts::where('kode', 'Freight Revenue')->first();
                $freight = \App\JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $coaCode(optional($freightLinked)->akun_id),
                    'debits'           => 0,
                    'credits'          => (float) $salesInvoice->freight,
                    'comment'          => 'Freight Revenue',
                    'status' => 2
                ]);
                // dump('🚚 Journal Freight:', $freight->toArray());
            }

            // Withholding (Debit)
            if ($salesInvoice->withholding_value > 0) {
                $withholdingLinked = \App\SalesTaxes::where('id', $request->withholding_tax)->first();
                $withholding =  \App\JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $coaCode(optional($withholdingLinked)->sales_account_id),
                    'debits'           => $salesInvoice->withholding_value,
                    'credits'          => 0,
                    'comment'          => 'PPh Dipotong pelanggan',
                    'status' => 2
                ]);
                // dump('📘 Journal Withholding:', $withholding->toArray());
            }

            // 🧮 Grand Total Payment (Debit)
            $totalItemRevenue = collect($lines)->sum('revenue_amt');
            $totalTax = collect($lines)->sum('tax_amount');
            $grandTotal = $totalItemRevenue + $totalTax + $salesInvoice->freight - $salesInvoice->withholding_value;

            if ($grandTotal > 0) {
                $pmDetail = \App\PaymentMethodDetail::where('payment_method_id', $request->jenis_pembayaran_id)
                    ->where('is_default', 1)->first()
                    ?? \App\PaymentMethodDetail::where('payment_method_id', $request->jenis_pembayaran_id)->first();

                $payment = \App\JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $coaCode(optional($pmDetail)->account_id),
                    'debits'           => $grandTotal,
                    'credits'          => 0,
                    'comment'          => 'Payment',
                    'status' => 2
                ]);
                // dump('🏦 Journal Payment (adjusted):', $payment->toArray());
            }

            // ⚖️ Balance Check
            $totalDebit = \App\JournalEntryDetail::where('journal_entry_id', $journal->id)->sum('debits');
            $totalCredit = \App\JournalEntryDetail::where('journal_entry_id', $journal->id)->sum('credits');
            $diff = round($totalDebit - $totalCredit, 2);
            // dump("⚖️ Journal Balance Check => Debit: {$totalDebit} | Credit: {$totalCredit} | Selisih: {$diff}");

            DB::commit();
            return redirect()->route('sales_invoice.index')->with('success', 'Sales invoice berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            // dd('❌ Error:', $e->getMessage(), $e->getTraceAsString());
        }
    }


    protected function adjustItemQuantity($itemId, $locationId, $qtyChange, $valueChange, $isSale = false)
    {
        // ambil data unit item
        $unit = \App\ItemUnit::where('item_id', $itemId)->first();

        if ($isSale && $unit && !$unit->selling_same_as_stocking) {
            // konversi satuan jual ke satuan stok
            $qtyChange = $qtyChange * $unit->selling_relationship;
            $valueChange = $valueChange * $unit->selling_relationship;
        }

        $itemQty = \App\ItemQuantities::firstOrCreate(
            ['item_id' => $itemId, 'location_id' => $locationId],
            ['on_hand_qty' => 0, 'on_hand_value' => 0]
        );

        $itemQty->on_hand_qty += $qtyChange;
        $itemQty->on_hand_value += $valueChange;

        if ($itemQty->on_hand_qty < 0) $itemQty->on_hand_qty = 0;
        if ($itemQty->on_hand_value < 0) $itemQty->on_hand_value = 0;

        $itemQty->save();
    }


    public function show($id)
    {
        // Ambil sales order beserta relasi terkait
        $salesInvoice = SalesInvoice::with([
            'customer',
            'salesPerson',
            'withholding',
            'lokasi_inventory',
            'jenisPembayaran',
            'details.item',
            'details.account'
        ])->findOrFail($id);

        return view('sales_invoice.show', compact('salesInvoice'));
    }

    public function edit($id)
    {
        $salesInvoice = \App\SalesInvoice::with(['details.item.quantities', 'details.item.accounts'])->findOrFail($id);

        $customers = \App\Customers::all();
        $employees = \App\Employee::all();
        $jenis_pembayaran = \App\PaymentMethod::all();
        $project = \App\Project::all();
        $location_inventory = \App\LocationInventory::all();
        $items = \App\Item::all(); // semua item yang bisa dipilih
        $sales_taxes = \App\SalesTaxes::where('type', 'input_tax')->get();
        $freightAccount = \App\linkedAccounts::with('akun')
            ->where('kode', 'Freight Revenue')
            ->first();
        $withholding = SalesTaxes::where('type', 'withholding_tax')->get();

        $locationId = $salesInvoice->location_id;

        foreach ($salesInvoice->details as $detail) {
            $item = $detail->item;
            if (!$item) continue;

            // Pastikan item punya tipe (inventory / service)
            $detail->item_type = strtolower($item->type ?? 'inventory');

            if ($detail->item_type === 'inventory') {
                // Hitung unit cost berdasarkan stok di lokasi
                $onHandQty = $item->quantities
                    ->where('location_id', $locationId)
                    ->sum('on_hand_qty') ?: 0;

                $onHandValue = $item->quantities
                    ->where('location_id', $locationId)
                    ->sum('on_hand_value');

                $unitCost = ($onHandQty > 0)
                    ? round($onHandValue / $onHandQty, 2)
                    : 0;
            } else {
                // Service item tidak punya stok, cost = 0
                $unitCost = 0;
            }

            $detail->computed_unit_cost = $unitCost;

            // Simpan juga nama akun COGS dan Inventory untuk preview
            $detail->cogs_account_name = optional($item->accounts->cogsAccount)->nama_akun ?? 'COGS';
            $detail->cogs_account_code = optional($item->accounts->cogsAccount)->kode_akun ?? '';
            $detail->asset_account_name = optional($item->accounts->assetAccount)->nama_akun ?? 'Inventory';
            $detail->asset_account_code = optional($item->accounts->assetAccount)->kode_akun ?? '';
        }

        return view('sales_invoice.edit', compact(
            'salesInvoice',
            'customers',
            'employees',
            'jenis_pembayaran',
            'items',
            'project',
            'location_inventory',
            'sales_taxes',
            'freightAccount',
            'withholding'
        ));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // dump("🟢 MULAI UPDATE SALES INVOICE ID:", $id);

            $paymentStatus = PaymentMethod::where('id', $request->jenis_pembayaran_id)->value('status_payment');

            if ($paymentStatus !== 1) {
                $salesStatus = 3;
            } elseif ($request->sales_order_id) {
                $salesStatus = 1;
            } else {
                $salesStatus = 0;
            }
            // 🔹 Ambil invoice beserta detail & stok
            $salesInvoice = \App\SalesInvoice::with('details.item.quantities')->findOrFail($id);

            // 🔹 Update header
            $salesInvoice->update([
                'invoice_date'             => $request->invoice_date,
                'shipping_date'            => $request->shipping_date,
                'customers_id'             => $request->customers_id,
                'sales_person_id'           => $request->sales_person_id ?? null,
                'sales_order_id'           => $request->sales_order_id ?? null,
                'location_id'              => $request->location_id,
                'payment_method_account_id' => $request->payment_method_account_id,
                'withholding_tax'          => $request->withholding_tax,
                'withholding_value'        => (float) str_replace(',', '', $request->withholding_value),
                'jenis_pembayaran_id'      => $request->jenis_pembayaran_id,
                'shipping_address'         => $request->shipping_address,
                'freight'                  => (float) str_replace(',', '', $request->freight),
                'early_payment_terms'      => $request->early_payment_terms,
                'messages'                 => $request->messages,
                'status' => $salesStatus
            ]);

            // dump("Header diupdate:", $salesInvoice->toArray());

            $locationId = $salesInvoice->location_id;

            // 🔁 Kembalikan stok lama
            foreach ($salesInvoice->details as $oldDetail) {
                $unitCost = $this->getUnitCost($oldDetail->item_id, $locationId);
                $valueRestore = $unitCost * $oldDetail->quantity;

                // dump("🔁 Kembalikan stok lama:", [
                //     'item_id'   => $oldDetail->item_id,
                //     'qty'       => $oldDetail->quantity,
                //     'unit_cost' => $unitCost,
                //     'value'     => $valueRestore,
                // ]);

                $this->adjustItemQuantity(
                    $oldDetail->item_id,
                    $locationId,
                    $oldDetail->quantity,
                    $valueRestore,
                    true
                );
            }

            // 🧹 Hapus detail & jurnal lama
            \App\SalesInvoiceDetail::where('sales_invoice_id', $salesInvoice->id)->delete();
            // dump("Detail lama dihapus.");

            $oldJournal = \App\JournalEntry::where('source', 'sales_invoice')
                ->where('comment', "Sales Invoice #{$salesInvoice->invoice_number}")
                ->first();

            if ($oldJournal) {
                \App\JournalEntryDetail::where('journal_entry_id', $oldJournal->id)->delete();
                $oldJournal->delete();
                // dump("Jurnal lama dihapus:", $oldJournal->id);
            }

            // ==============================
            // 🔹 INSERT DETAIL BARU
            // ==============================
            $lines = [];
            foreach ($request->items as $row) {
                // dump("🧩 Data item sebelum insert:", $row);

                $qty = (float) str_replace(',', '', $row['quantity'] ?? 0);
                $base_price = (float) str_replace(',', '', $row['base_price'] ?? 0);
                $discount = (float) str_replace(',', '', $row['discount'] ?? 0);
                $price = (float) str_replace(',', '', $row['price'] ?? 0);
                $amount = (float) str_replace(',', '', $row['amount'] ?? 0);
                $tax_amount = (float) str_replace(',', '', $row['tax_value'] ?? 0);
                $tax_id = $row['tax_id'] ?? null;
                $account_id = $row['account_id'] ?? $row['account'] ?? null;

                $detail = \App\SalesInvoiceDetail::create([
                    'sales_invoice_id' => $salesInvoice->id,
                    'item_id'          => $row['item_id'],
                    'quantity'         => $qty,
                    'order_quantity'   => $qty,
                    'back_order'       => (float) str_replace(',', '', $row['back_order'] ?? 0),
                    'unit'             => $row['unit'],
                    'description'      => $row['description'],
                    'base_price'       => $base_price,
                    'discount'         => $discount,
                    'price'            => $price,
                    'amount'           => $amount,
                    'tax'              => $tax_amount,
                    'account_id'       => $account_id,
                    'project_id'       => $row['project_id'] ?? null,
                    'tax_id'           => $tax_id,
                ]);

                // dump("🆕 Detail dibuat:", $detail->toArray());

                // ====================================================
                // 🔹 Revisi Tambahan: Konversi satuan jual ke stok
                // ====================================================
                $itemUnit = \App\ItemUnit::where('item_id', $row['item_id'])->first();
                $unit = $row['unit'] ?? null;
                $qtyChange = $qty;

                if ($itemUnit) {
                    if ($unit === $itemUnit->unit_of_measure) {
                        $qtyChange = $qty;
                    } elseif ($unit === $itemUnit->selling_unit) {
                        $qtyChange = $qty * max(1, (float)$itemUnit->selling_relationship);
                    } elseif ($unit === $itemUnit->buying_unit) {
                        $qtyChange = $qty * max(1, (float)$itemUnit->buying_relationship);
                    }
                }

                // 🔻 Karena penjualan → stok berkurang
                $qtyChange = -$qtyChange;

                // 🔹 Ambil unit cost & hitung COGS berdasar stok unit
                $unitCost = $this->getUnitCost($row['item_id'], $locationId);
                $cogsAmount = abs($qtyChange) * $unitCost;
                $valueChange = -$cogsAmount;

                // dump("🔹 Konversi Unit:", [
                //     'item_id'          => $row['item_id'],
                //     'unit'             => $unit,
                //     'selling_unit'     => optional($itemUnit)->selling_unit,
                //     'relationship'     => optional($itemUnit)->selling_relationship,
                //     'qty_as_stock'     => $qtyChange,
                //     'unitCost'         => $unitCost,
                //     'cogsAmount'       => $cogsAmount,
                //     'valueChange'      => $valueChange,
                // ]);

                // 🔹 Update stok berdasar hasil konversi
                // dump("📦 Update Inventory untuk Item #{$row['item_id']}", [
                //     'qtyChange'    => $qtyChange,
                //     'valueChange'  => $valueChange,
                //     'unitCost'     => $unitCost,
                // ]);

                $this->adjustItemQuantity($row['item_id'], $locationId, $qtyChange, $valueChange, true);

                // 🔹 Simpan cache untuk jurnal
                $lines[] = [
                    'item_id'      => $row['item_id'],
                    'desc'         => $row['description'],
                    'revenue_acct' => $account_id,
                    'revenue_amt'  => $amount,
                    'tax_id'       => $tax_id,
                    'tax_amount'   => $tax_amount,
                    'shipped_qty'  => $qty,
                    'unit_cost'    => $unitCost,
                    'cogs_amount'  => $cogsAmount,
                ];
            }

            // ==============================
            // 🔹 JURNAL BARU
            // ==============================
            $journal = \App\JournalEntry::create([
                'source'  => 'sales_invoice',
                'tanggal' => $salesInvoice->invoice_date,
                'comment' => "Sales Invoice #{$salesInvoice->invoice_number}",
            ]);
            // dump("📘 Jurnal baru dibuat:", $journal->toArray());

            $coaCode = fn($id) => $id ? \App\ChartOfAccount::whereKey($id)->value('kode_akun') : null;

            foreach ($lines as $ln) {
                // Pendapatan
                if ($ln['revenue_amt'] > 0 && $ln['revenue_acct']) {
                    $rev = \App\JournalEntryDetail::create([
                        'journal_entry_id' => $journal->id,
                        'kode_akun'        => $coaCode($ln['revenue_acct']),
                        'debits'           => 0,
                        'credits'          => $ln['revenue_amt'],
                        'comment'          => $ln['desc'],
                        'status' => 2

                    ]);
                    // dump("💰 Revenue:", $rev->toArray());
                }

                // HPP & Inventory
                if ($ln['cogs_amount'] > 0) {
                    $cogsAcct = \App\ItemAccount::where('item_id', $ln['item_id'])->value('cogs_account_id');
                    $invAcct  = \App\ItemAccount::where('item_id', $ln['item_id'])->value('asset_account_id');

                    $cogs = \App\JournalEntryDetail::create([
                        'journal_entry_id' => $journal->id,
                        'kode_akun'        => $coaCode($cogsAcct),
                        'debits'           => $ln['cogs_amount'],
                        'credits'          => 0,
                        'comment'          => 'COGS ' . $ln['desc'],
                        'status' => 2

                    ]);

                    $inv = \App\JournalEntryDetail::create([
                        'journal_entry_id' => $journal->id,
                        'kode_akun'        => $coaCode($invAcct),
                        'debits'           => 0,
                        'credits'          => $ln['cogs_amount'],
                        'comment'          => 'Inventory Out ' . $ln['desc'],
                        'status' => 2

                    ]);

                    // dump("🏭 HPP & Persediaan:", ['COGS' => $cogs->toArray(), 'Inventory' => $inv->toArray()]);
                }

                // Pajak
                if ($ln['tax_amount'] > 0 && $ln['tax_id']) {
                    $tax = \App\SalesTaxes::find($ln['tax_id']);
                    if ($tax) {
                        $taxAccountCode = $coaCode($tax->sales_account_id);
                        $isWithholding = $tax->type === 'withholding_tax';
                        $debit = $isWithholding ? $ln['tax_amount'] : 0;
                        $credit = $isWithholding ? 0 : $ln['tax_amount'];

                        $taxRow = \App\JournalEntryDetail::create([
                            'journal_entry_id' => $journal->id,
                            'kode_akun'        => $taxAccountCode,
                            'debits'           => $debit,
                            'credits'          => $credit,
                            'comment'          => $isWithholding ? 'PPh Dipotong' : 'PPN Keluaran',
                            'status' => 2

                        ]);
                        // dump("💸 Pajak ({$tax->type}):", $taxRow->toArray());
                    }
                }
            }

            // Freight
            if ((float) $salesInvoice->freight > 0) {
                $freightLinked = \App\LinkedAccounts::where('kode', 'Freight Revenue')->first();
                $freight = \App\JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $coaCode(optional($freightLinked)->akun_id),
                    'debits'           => 0,
                    'credits'          => (float) $salesInvoice->freight,
                    'comment'          => 'Freight Revenue',
                    'status' => 2

                ]);
                // dump("🚚 Freight:", $freight->toArray());
            }

            // Withholding (Debit)
            if ($salesInvoice->withholding_value > 0) {
                $withholdingLinked = \App\SalesTaxes::where('id', $request->withholding_tax)->first();
                $withholding =  \App\JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $coaCode(optional($withholdingLinked)->sales_account_id),
                    'debits'           => $salesInvoice->withholding_value,
                    'credits'          => 0,
                    'comment'          => 'PPh Dipotong pelanggan',
                    'status'           => 2
                ]);
                // dump('📘 Journal Withholding:', $withholding->toArray());
            }

            // ===================================
            // 🔹 PAYMENT (dengan fallback aman)
            // ===================================
            $grandTotal = collect($lines)->sum(fn($ln) => $ln['revenue_amt'] + $ln['tax_amount'])
                + (float) $salesInvoice->freight
                - (float) $salesInvoice->withholding_value;

            $payAcct = null;

            if (!empty($salesInvoice->payment_method_account_id)) {
                $payAcct = $coaCode($salesInvoice->payment_method_account_id);
            } else {
                $pmDetail = \App\PaymentMethodDetail::where('payment_method_id', $salesInvoice->jenis_pembayaran_id)
                    ->where('is_default', 1)
                    ->first();
                if ($pmDetail) {
                    $payAcct = $coaCode($pmDetail->account_id);
                    // dump("💳 Menggunakan akun default dari PaymentMethodDetail:", $payAcct);
                }
            }

            if (!$payAcct) {
                throw new \Exception("⚠️ Tidak ada account valid untuk Payment Method ID {$salesInvoice->jenis_pembayaran_id}");
            }

            $payment = \App\JournalEntryDetail::create([
                'journal_entry_id' => $journal->id,
                'kode_akun'        => $payAcct,
                'debits'           => $grandTotal,
                'credits'          => 0,
                'comment'          => 'Customer Payment',
                'status' => 2
            ]);
            // dump("💳 Payment:", $payment->toArray());

            // Balance check
            $totalDebit = \App\JournalEntryDetail::where('journal_entry_id', $journal->id)->sum('debits');
            $totalCredit = \App\JournalEntryDetail::where('journal_entry_id', $journal->id)->sum('credits');
            // dump("🔍 TOTAL DEBIT:", number_format($totalDebit, 2), "🔍 TOTAL CREDIT:", number_format($totalCredit, 2));
            // dump("⚖️ Selisih:", round($totalDebit - $totalCredit, 2));

            // dump("✅ SELESAI UPDATE SALES INVOICE");

            DB::commit();
            return redirect()->route('sales_invoice.index')->with('success', 'Sales Invoice berhasil diupdate.');
        } catch (\Throwable $e) {
            DB::rollBack();
            // dd("❌ ERROR UPDATE INVOICE:", $e->getMessage(), $e->getTraceAsString());
        }
    }


    protected function getUnitCost($itemId, $locationId)
    {
        $iq = \App\ItemQuantities::where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->first();

        if ($iq && $iq->on_hand_qty > 0) {
            return $iq->on_hand_value / $iq->on_hand_qty;
        }

        return 0;
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $invoice = \App\SalesInvoice::with('details')->findOrFail($id);

            // 🔹 Rollback stok dulu (berdasarkan selling unit & relationship)
            foreach ($invoice->details as $detail) {
                $qty   = (float) str_replace(',', '', $detail->quantity);
                $unit  = $detail->unit ?? null;
                $price = (float) str_replace(',', '', $detail->price);

                // Ambil relasi konversi satuan
                $itemUnit = \App\ItemUnit::where('item_id', $detail->item_id)->first();

                if ($itemUnit) {
                    if ($unit === $itemUnit->unit_of_measure) {
                        $qtyChange = $qty;
                    } elseif ($unit === $itemUnit->selling_unit) {
                        $qtyChange = $qty * max(1, (float)$itemUnit->selling_relationship);
                    } elseif ($unit === $itemUnit->buying_unit) {
                        $qtyChange = $qty * max(1, (float)$itemUnit->buying_relationship);
                    } else {
                        $qtyChange = $qty;
                    }
                } else {
                    $qtyChange = $qty;
                }

                // Value berdasarkan cost bukan price (price bisa inflated)
                $unitCost = $this->getUnitCost($detail->item_id, $invoice->location_id);
                $valueChange = $unitCost * $qtyChange;

                // dump("🔁 Rollback stok item_id={$detail->item_id}", [
                //     'qty_jual'        => $qty,
                //     'unit'            => $unit,
                //     'selling_unit'    => optional($itemUnit)->selling_unit,
                //     'relationship'    => optional($itemUnit)->selling_relationship,
                //     'qty_dikembalikan' => $qtyChange,
                //     'unitCost'        => $unitCost,
                //     'value_dikembalikan' => $valueChange,
                // ]);

                // Tambahkan kembali stok
                $this->adjustItemQuantity($detail->item_id, $invoice->location_id, $qtyChange, $valueChange, true);
            }

            if (!empty($invoice->sales_order_id)) {
                \App\SalesOrder::whereKey($invoice->sales_order_id)
                    ->update(['status' => 0]);
            }


            // 🔹 Hapus Journal Entry
            $journal = \App\JournalEntry::where('source', 'sales_invoice')
                ->where('comment', "Sales Invoice #{$invoice->invoice_number}")
                ->first();

            if ($journal) {
                \App\JournalEntryDetail::where('journal_entry_id', $journal->id)->delete();
                $journal->delete();
                // dump('🧾 Journal dihapus:', $journal->id);
            }

            // 🔹 Hapus detail
            \App\SalesInvoiceDetail::where('sales_invoice_id', $invoice->id)->delete();
            // dump('🗑️ Detail dihapus untuk invoice', $invoice->id);

            // 🔹 Hapus header
            $invoice->delete();
            // dump('🧾 Header SalesInvoice dihapus:', $id);

            DB::commit();

            return redirect()->route('sales_invoice.index')
                ->with('success', 'Sales Invoice berhasil dihapus beserta jurnal & rollback stok.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Gagal menghapus invoice: ' . $e->getMessage());
        }
    }


    public function exportPdf($id)
    {
        $salesInvoice = SalesInvoice::with('details.item', 'customer', 'jenisPembayaran', 'salesPerson', 'salesOrder', 'details.project')->findOrFail($id);

        $pdf = Pdf::loadView('sales_invoice.pdf', compact('salesInvoice'));
        return $pdf->download('SalesInvoice-' . $salesInvoice->invoice_number . '.pdf');
    }


    public function print($id)
    {
        // Ambil sales order beserta relasi terkait
        $salesInvoice = SalesInvoice::with([
            'customer',
            'salesPerson',
            'lokasi_inventory',
            'jenisPembayaran',
            'details.item',
            'details.account'
        ])->findOrFail($id);
        $companyProfile = CompanyProfile::first();

        return view('sales_invoice.print', compact('salesInvoice', 'companyProfile'));
    }
    public function downloadPdf($id)
    {
        $salesInvoice = SalesInvoice::with([
            'customer',
            'salesPerson',
            'lokasi_inventory',
            'jenisPembayaran',
            'details.item',
            'details.account'
        ])->findOrFail($id);
        $companyProfile = CompanyProfile::first();

        $isPdf = true;
        $pdf = Pdf::loadView('sales_invoice.print', compact(
            'salesInvoice',
            'companyProfile',
            'isPdf'
        ))->setPaper('A4', 'portrait');

        return $pdf->download("PO_{$salesInvoice->invoice_number}.pdf");
    }
}

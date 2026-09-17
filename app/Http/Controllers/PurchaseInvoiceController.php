<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use App\CompanyProfile;
use App\Customers;
use App\Item;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\linkedAccounts;
use App\LocationInventory;
use App\PaymentMethod;
use App\Project;
use App\PurchaseInvoice;
use App\PurchaseInvoiceDetail;
use App\PurchaseOrder;
use App\SalesTaxes;
use App\Vendors;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    //
    public function index()
    {

        $query = PurchaseInvoice::with(['jenisPembayaran', 'vendor', 'locationInventories']);
        // dd($query->get());

        if ($location_inventory = request('filter_location')) {
            $query->whereHas('locationInventories', function ($q) use ($location_inventory) {
                $q->where('kode_lokasi', $location_inventory);
            });
            // dd($query->toSql(), $query->getBindings());
        }
        if (request()->filled('filter_status')) {
            $query->where('status_purchase', request('filter_status'));
        }

        $searchable = ['invoice_number', 'date_invoice',];

        if ($search = request('search')) {
            $query->where(function ($q) use ($search, $searchable) {
                foreach ($searchable as $col) {
                    $q->orWhere($col, 'like', "%{$search}%");
                }
                $q->orWhereHas('jenisPembayaran', function ($q1) use ($search) {
                    $q1->where('nama_jenis', 'like', "%{$search}%");
                });
                $q->orWhereHas('vendor', function ($q2) use ($search) {
                    $q2->where('nama_vendors', 'like', "%{$search}%");
                });
            });
        }
        $data = $query->orderBy('date_invoice', 'asc')->orderBy('status_purchase', 'asc')->paginate(10);
        $location = LocationInventory::pluck('kode_lokasi', 'id')->filter()->unique()->values();
        return view('purchase_invoice.index', compact('data', 'location'));
    }
    public function create()
    {
        $jenis_pembayaran = PaymentMethod::all();
        $items = Item::all();
        $accounts = chartOfAccount::all();
        $purchase_order = PurchaseOrder::where('status_purchase', 0)->get();
        $project = Project::all();
        $vendor = Vendors::all();
        $sales_taxes = SalesTaxes::where('type', 'input_tax')->get();
        $freightAccount = \App\linkedAccounts::with('akun')
            ->where('kode', 'Freight Expense')
            ->first();
        $lokasi_inventory = LocationInventory::all();
        $withholding = SalesTaxes::where('type', 'withholding_tax')->get();
        return view('purchase_invoice.create', compact('jenis_pembayaran',  'items', 'accounts', 'purchase_order', 'project', 'vendor', 'sales_taxes', 'freightAccount', 'lokasi_inventory', 'withholding'));
    }

    public function getItemsFromPurchaseOrder($purchaseOrderId)
    {
        $purchaseOrder = PurchaseOrder::with([
            'details.item',
            'details.account',
            'details.sales_taxes'
        ])->findOrFail($purchaseOrderId);

        return response()->json([
            'items' => $purchaseOrder->details->map(function ($detail) {
                return [
                    'id'           => $detail->item->id ?? null,
                    'item_number'  => $detail->item->item_number ?? '',
                    'description'  => $detail->item_description ?? '',
                    'quantity'     => $detail->quantity ?? 0,
                    'order'        => $detail->order ?? 0,
                    'back_order'   => $detail->back_order ?? 0,
                    'unit'         => $detail->unit ?? '',
                    'price'        => $detail->price ?? 0,
                    'discount'        => $detail->discount ?? 0,
                    'tax_id'       => $detail->tax_id ?? null, // ambil ID pajak
                    'tax_rate'     => optional($detail->sales_taxes)->rate ?? 0, // ambil rate pajak
                    'tax_purchase_account_id'   => optional($detail->sales_taxes)->purchase_account_id ?? null,
                    'tax_purchase_account_name' => optional(optional($detail->sales_taxes)->purchaseAccount)->nama_akun ?? '',
                    'tax_amount'   => $detail->tax_amount ?? 0,
                    'amount'       => $detail->amount ?? 0,
                    'account_id'   => $detail->account_id ?? null,
                    'account_name' => $detail->account->nama_akun ?? '',
                    'account_code' => $detail->account->kode_akun ?? '',
                ];
            })->values(),
        ]);
    }

    private function generateKodeInvoice($dateOrder)
    {
        $dateFormatted = \Carbon\Carbon::parse($dateOrder)->format('Ymd');

        $prefix = 'INV-' . $dateFormatted . '-';

        // Cari order terakhir berdasarkan tanggal order yg sama
        $last = \App\PurchaseInvoice::where('invoice_number', 'like', $prefix . '%')
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
        // 0️⃣ Generate nomor invoice
        if ($request->has('auto_generate')) {
            $invoice_number = $this->generateKodeInvoice($request->date_invoice);
        } else {
            $request->validate(['invoice_number' => 'unique:purchase_invoices,invoice_number']);
            $invoice_number = $request->invoice_number;
        }

        // 1️⃣ Normalisasi payload
        $payload = $request->all();

        $payload['freight'] = isset($payload['freight'])
            ? floatval(str_replace(',', '', $payload['freight']))
            : 0;

        $payload['withholding_value'] = isset($payload['withholding_value'])
            ? floatval(str_replace(',', '', $payload['withholding_value']))
            : 0;

        $payload['items'] = collect($payload['items'] ?? [])->map(function ($row) {
            foreach (['price', 'tax', 'tax_amount', 'amount', 'quantity', 'order', 'back_order'] as $f) {
                if (isset($row[$f])) {
                    $row[$f] = $row[$f] === '' ? null : floatval(str_replace(',', '', $row[$f]));
                }
            }

            if (!empty($row['account_id']) && !is_numeric($row['account_id'])) {
                $id = \App\ChartOfAccount::where('kode_akun', $row['account_id'])->value('id');
                $row['account_id'] = $id ?: null;
            }

            if (isset($row['account_id']) && intval($row['account_id']) === 0) {
                $row['account_id'] = null;
            }

            return $row;
        })->all();

        $request->replace($payload);


        // 2️⃣ Validasi
        $request->validate([
            'date_invoice'        => 'required|date',
            'shipping_date'       => 'nullable|date',
            'vendor_id'           => 'required|exists:vendors,id',
            'header_account_id'   => 'required|exists:payment_method_details,id',
            'purchase_order_id'   => 'nullable|exists:purchase_orders,id',
            'location_id'         => 'nullable|exists:location_inventories,id',
            'jenis_pembayaran_id' => 'required|exists:payment_methods,id',
            'withholding_tax' => 'nullable|exists:sales_taxes,id',
            'withholding_value' => 'nullable|numeric|min:0',
            'shipping_address'    => 'nullable|string',
            'freight'             => 'nullable|numeric|min:0',
            'items'               => 'nullable|array|min:1',
            'items.*.item_id'     => 'nullable|exists:items,id',
            'items.*.account_id'  => 'nullable|integer|exists:chart_of_accounts,id',
            'items.*.tax_id'      => 'nullable|integer|exists:sales_taxes,id',
        ]);

        DB::beginTransaction();
        try {

            $paymentStatus = \App\PaymentMethod::where('id', $request->jenis_pembayaran_id)
                ->value('status_payment');

            if ($paymentStatus != 1) {
                $statusPurchase = 3;
            } else {
                $statusPurchase = 0;
            }

            // ==============================================================
            // 3️⃣ Simpan Header Purchase Invoice
            // ==============================================================
            $purchaseInvoice = \App\PurchaseInvoice::create([
                'invoice_number'      => $invoice_number,
                'date_invoice'        => $request->date_invoice,
                'shipping_date'       => $request->shipping_date,
                'vendor_id'           => $request->vendor_id,
                'payment_method_account_id'          => $request->header_account_id,
                'withholding_tax'          => $request->withholding_tax,
                'withholding_value' => $request->withholding_value,
                'purchase_order_id'   => $request->purchase_order_id,
                'location_id'         => $request->location_id,
                'jenis_pembayaran_id' => $request->jenis_pembayaran_id,
                'shipping_address'    => $request->shipping_address,
                'freight'             => $request->freight,
                'early_payment_terms' => $request->early_payment_terms,
                'messages'            => $request->messages,
                'status_purchase' => $statusPurchase
            ]);
            // dump('📘 Purchase Invoice tersimpan:', $purchaseInvoice->toArray());

            // 3b️⃣ Update status PO
            if ($request->purchase_order_id) {
                $status_purchase_order =    \App\PurchaseOrder::whereKey($request->purchase_order_id)
                    ->update(['status_purchase' => 1]);
                // dump('📦 Purchase Order diupdate:', $status_purchase_order->toArray());
            }

            // ==============================================================
            // 4️⃣ Simpan Detail Item & Update Inventory
            // ==============================================================
            foreach ($request->items ?? [] as $row) {
                $detail = \App\PurchaseInvoiceDetail::create([
                    'purchase_invoice_id' => $purchaseInvoice->id,
                    'item_id'             => $row['item_id'],
                    'quantity'            => $row['quantity'],
                    'order'               => $row['order'] ?? 0,
                    'back_order'          => $row['back_order'] ?? 0,
                    'unit'                => $row['unit'],
                    'item_description'    => $row['item_description'],
                    'price'               => $row['price'] ?? 0,
                    'discount'            => $row['discount'] ?? 0,
                    'tax_id'              => $row['tax_id'],
                    'tax_amount'          => $row['tax_amount'] ?? 0,
                    'amount'              => $row['amount'] ?? 0,
                    'account_id'          => $row['account_id'],
                    'project_id'          => $row['project_id'] ?? null,
                ]);
                // dump('🧾 Detail tersimpan:', $detail->toArray());

                // =========================================
                // 🧮 Konversi ke satuan dasar sebelum update stok
                // =========================================
                $itemUnit = \App\ItemUnit::where('item_id', $row['item_id'])->first();

                $qty = $row['quantity'] ?? 0;
                $unit = $row['unit'] ?? null;
                $price = $row['price'] ?? 0;

                // Konversi ke base unit (unit_of_measure)
                if ($itemUnit) {
                    if ($unit === $itemUnit->unit_of_measure) {
                        $qtyChange = $qty;
                    } elseif ($unit === $itemUnit->buying_unit) {
                        $qtyChange = $qty * max(1, (float)$itemUnit->buying_relationship);
                    } elseif ($unit === $itemUnit->selling_unit) {
                        $qtyChange = $qty * max(1, (float)$itemUnit->selling_relationship);
                    } else {
                        // fallback jika unit tidak dikenal
                        $qtyChange = $qty;
                    }
                } else {
                    // fallback kalau item belum punya definisi unit di ItemUnit
                    $qtyChange = $qty;
                }

                // Nilai barang (harga * qty dalam satuan transaksi)
                $valueChange = $qty * $price;

                // 🔁 Update stok berdasarkan base unit
                $inventory = $this->adjustItemQuantity($row['item_id'], $request->location_id, $qtyChange, $valueChange);
                // dump('📦 Update stok:', [
                //     'item_id' => $row['item_id'],
                //     'unit' => $unit,
                //     'qty_input' => $qty,
                //     'qtyChange (base)' => $qtyChange,
                //     'valueChange' => $valueChange,
                //     'after' => $inventory->toArray()
                // ]);
            }

            // ==============================================================
            // 5️⃣ Buat Journal Entry Header
            // ==============================================================
            $journal = \App\JournalEntry::create([
                'source'  => 'purchase_invoice',
                'tanggal' => $request->date_invoice,
                'comment' => "Purchase Invoice #{$purchaseInvoice->invoice_number}",
            ]);
            // dump('🪵 Journal Entry header dibuat:', $journal->toArray());

            $coaCode = fn($id) => $id ? \App\ChartOfAccount::whereKey($id)->value('kode_akun') : null;
            $totalDebit = 0;
            $totalCredit = 0;

            // ==============================================================
            // 6️⃣ Journal Detail (Items + Pajak)
            // ==============================================================
            foreach ($request->items ?? [] as $row) {
                // --- Barang / Beban (Debit)
                if (!empty($row['amount']) && $row['amount'] > 0 && !empty($row['account_id'])) {
                    $kodeAkun = $coaCode($row['account_id']);
                    $jd = \App\JournalEntryDetail::create([
                        'journal_entry_id' => $journal->id,
                        'kode_akun'        => $kodeAkun,
                        'debits'           => $row['amount'],
                        'credits'          => 0,
                        'comment'          => $row['item_description'] ?? null,
                        'status' => 2
                    ]);
                    // dump('💰 Journal Debit Item:', $jd->toArray());
                    $totalDebit += $row['amount'];
                }

                // --- Pajak
                if (!empty($row['tax_amount']) && !empty($row['tax_id'])) {
                    $tax = \App\SalesTaxes::find($row['tax_id']);
                    if ($tax) {
                        $taxAccountId = $tax->purchase_account_id;
                        $taxType = $tax->type;
                        $kodeAkunTax = $coaCode($taxAccountId);
                        $nilaiTax = abs($row['tax_amount']); // pakai nilai absolut

                        // dump("📑 Pajak Ditemukan:", [
                        //     'tax_id' => $row['tax_id'],
                        //     'type' => $taxType,
                        //     'rate' => $tax->rate,
                        //     'amount_raw' => $row['tax_amount'],
                        //     'amount_final' => $nilaiTax,
                        //     'kode_akun' => $kodeAkunTax,
                        // ]);

                        if ($taxType === 'input_tax') {
                            $jp = \App\JournalEntryDetail::create([
                                'journal_entry_id' => $journal->id,
                                'kode_akun'        => $kodeAkunTax,
                                'debits'           => $nilaiTax,
                                'credits'          => 0,
                                'comment'          => "PPN ({$tax->rate}%)",
                                'status' => 2

                            ]);
                            // dump('🧾 Journal Debit Pajak Masukan (PPN):', $jp->toArray());
                            $totalDebit += $nilaiTax;
                        } elseif ($taxType === 'withholding_tax') {
                            $jp = \App\JournalEntryDetail::create([
                                'journal_entry_id' => $journal->id,
                                'kode_akun'        => $kodeAkunTax,
                                'debits'           => 0,
                                'credits'          => $nilaiTax,
                                'comment'          => "PPh Potongan ({$tax->rate}%)",
                                'status' => 2

                            ]);
                            // dump('💸 Journal Credit Pajak Potongan (PPh):', $jp->toArray());
                            $totalCredit += $nilaiTax;
                        }
                    }
                }
            }

            // ==============================================================
            // 7️⃣ Freight Expense
            // ==============================================================
            if ($request->freight > 0) {
                $freightLinked = \App\linkedAccounts::where('kode', 'Freight Expense')->first();
                $freightKode   = $coaCode(optional($freightLinked)->akun_id);

                $jf = \App\JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $freightKode,
                    'debits'           => $request->freight,
                    'credits'          => 0,
                    'comment'          => 'Freight Expense',
                    'status' => 2

                ]);
                // dump('🚚 Journal Debit Freight Expense:', $jf->toArray());
                $totalDebit += $request->freight;
            }

            // ==============================================================
            // 8️⃣ Kredit (Kas / Hutang)
            // ==============================================================
            $netPayable = $totalDebit - $totalCredit;

            // Withholding (Debit)
            if ($purchaseInvoice->withholding_value > 0) {
                $withholdingLinked = \App\SalesTaxes::where('id', $request->withholding_tax)->first();
                $withholding =  \App\JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $coaCode(optional($withholdingLinked)->purchase_account_id),
                    'debits'           => 0,
                    'credits'          => $purchaseInvoice->withholding_value,
                    'comment'          => 'PPh Dipotong pelanggan',
                    'status' => 2
                ]);
                // dump('📘 Journal Withholding:', $withholding->toArray());
            }

            if ($netPayable > 0) {
                $pmDetail = \App\PaymentMethodDetail::where('payment_method_id', $request->jenis_pembayaran_id)
                    ->where('is_default', 1)->first()
                    ?? \App\PaymentMethodDetail::where('payment_method_id', $request->jenis_pembayaran_id)->first();

                $pmKode = $coaCode(optional($pmDetail)->account_id);

                $jc = \App\JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $pmKode,
                    'debits'           => 0,
                    'credits'          => $netPayable,
                    'comment'          => 'Payment / Credit',
                    'status' => 2
                ]);
                // dump('🏦 Journal Credit Payment:', $jc->toArray());
                $totalCredit += $netPayable;
            }

            // dump('📊 Total Journal:', [
            //     'total_debit' => $totalDebit,
            //     'total_credit' => $totalCredit
            // ]);

            DB::commit();
            // dump('✅ Semua proses berhasil disimpan.');
            return redirect()->route('purchase_invoice.index')
                ->with('success', 'Purchase invoice berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            // dd('❌ Error:', $e->getMessage(), $e->getTraceAsString(), [
            //     'request_items' => $request->items
            // ]);
        }
    }

    protected function adjustItemQuantity($itemId, $locationId, $qtyChange, $valueChange)
    {
        $itemQty = \App\ItemQuantities::firstOrCreate(
            ['item_id' => $itemId, 'location_id' => $locationId],
            ['on_hand_qty' => 0, 'on_hand_value' => 0]
        );

        $before = $itemQty->replicate()->toArray();

        $itemQty->on_hand_qty   += $qtyChange;
        $itemQty->on_hand_value += $valueChange;
        $itemQty->save();

        // dump("🔄 Update Inventory Item #$itemId", [
        //     'before' => $before,
        //     'change' => [
        //         'qty' => $qtyChange,
        //         'value' => $valueChange,
        //     ],
        //     'after' => $itemQty->toArray()
        // ]);

        return $itemQty;
    }

    public function show($id)
    {
        // Ambil sales order beserta relasi terkait
        $purchaseInvoice = PurchaseInvoice::with([
            'vendor',
            'jenisPembayaran',
            'details.item',
            'details.account',
            'details.sales_taxes'
        ])->findOrFail($id);


        return view('purchase_invoice.show', compact('purchaseInvoice'));
    }
    public function edit($id)
    {
        $purchaseInvoice = PurchaseInvoice::with('details')->findOrFail($id);
        $jenis_pembayaran = PaymentMethod::all();
        $vendor = Vendors::all();
        $items = Item::all(); // semua item yang bisa dipilih
        $sales_taxes = SalesTaxes::where('type', 'input_tax')->get();
        $withholding = SalesTaxes::where('type', 'withholding_tax')->get();
        $project = Project::all();
        $freightAccount = \App\linkedAccounts::with('akun')
            ->where('kode', 'Freight Expense')
            ->first();
        $locationInventory = LocationInventory::all();


        return view('purchase_invoice.edit', compact('purchaseInvoice', 'vendor', 'jenis_pembayaran', 'items', 'sales_taxes', 'project', 'freightAccount', 'locationInventory', 'withholding'));
    }
    public function update(Request $request, $id)
    {
        // 1) Normalisasi payload
        $payload = $request->all();
        $payload['freight'] = isset($payload['freight'])
            ? floatval(str_replace(',', '', $payload['freight']))
            : 0;
        $payload['withholding_value'] = isset($payload['withholding_value'])
            ? floatval(str_replace(',', '', $payload['withholding_value']))
            : 0;

        $payload['items'] = collect($payload['items'] ?? [])->map(function ($row) {
            foreach (['price', 'tax', 'tax_amount', 'amount', 'quantity', 'order', 'back_order'] as $f) {
                if (isset($row[$f])) {
                    $row[$f] = $row[$f] === '' ? null : floatval(str_replace(',', '', $row[$f]));
                }
            }
            return $row;
        })->all();

        $request->replace($payload);

        // 2) Validasi
        $request->validate([
            'date_invoice'        => 'required|date',
            'shipping_date'       => 'nullable|date',
            'vendor_id'           => 'required|exists:vendors,id',
            'payment_method_account_id'   => 'required|exists:payment_method_details,id',
            'withholding_tax'   => 'nullable|exists:sales_taxes,id',
            'location_id'         => 'required|exists:location_inventories,id',
            'purchase_order_id'   => 'nullable|exists:purchase_orders,id',
            'jenis_pembayaran_id' => 'required|exists:payment_methods,id',
            'shipping_address'    => 'nullable|string',
            'freight'             => 'nullable|numeric|min:0',
            'withholding_value'             => 'nullable|numeric|min:0',
            'discount'             => 'nullable|numeric|min:0',

            'items'                  => 'nullable|array|min:1',
            'items.*.item_id'        => 'nullable|exists:items,id',
            'items.*.quantity'       => 'nullable|numeric|min:0',
            'items.*.order'          => 'nullable|numeric|min:0',
            'items.*.back_order'     => 'nullable|numeric|min:0',
            'items.*.price'          => 'nullable|numeric|min:0',
            'items.*.tax_id'         => 'nullable|numeric|exists:sales_taxes,id',
            'items.*.tax_amount'     => 'nullable|numeric|min:0',
            'items.*.amount'         => 'nullable|numeric|min:0',
            'items.*.account_id'     => 'nullable|integer|exists:chart_of_accounts,id',
        ]);

        DB::beginTransaction();
        try {
            // 3) Ambil invoice lama
            $purchaseInvoice = \App\PurchaseInvoice::findOrFail($id);

            $paymentStatus = \App\PaymentMethod::where('id', $request->jenis_pembayaran_id)
                ->value('status_payment');

            if ($paymentStatus != 1) {
                $statusPurchase = 3;
            } else {
                $statusPurchase = 0;
            }

            // 4) Update header
            $purchaseInvoice->update([
                'date_invoice'        => $request->date_invoice,
                'shipping_date'       => $request->shipping_date,
                'vendor_id'           => $request->vendor_id,
                'location_id'         => $request->location_id,
                'payment_method_account_id'          => $request->payment_method_account_id,
                'withholding_tax'          => $request->withholding_tax,
                'purchase_order_id'   => $request->purchase_order_id,
                'jenis_pembayaran_id' => $request->jenis_pembayaran_id,
                'shipping_address'    => $request->shipping_address,
                'freight'             => $request->freight,
                'withholding_value'             => $request->withholding_value,
                'early_payment_terms' => $request->early_payment_terms,
                'messages'            => $request->messages,
                'status_purchase' => $statusPurchase
            ]);

            // dump("Header diupdate:", $purchaseInvoice->toArray());


            // 5) Update status PO
            if ($request->purchase_order_id) {
                \App\PurchaseOrder::whereKey($request->purchase_order_id)
                    ->update(['status_purchase' => 1]);
            }

            // 6) Rollback detail lama dari stok
            $oldDetails = \App\PurchaseInvoiceDetail::where('purchase_invoice_id', $purchaseInvoice->id)->get();

            foreach ($oldDetails as $old) {
                $itemUnit = \App\ItemUnit::where('item_id', $old->item_id)->first();

                $unit  = $old->unit ?? null;
                $qty   = $old->quantity ?? 0;
                $price = $old->price ?? 0;

                // konversi ke base unit
                if ($itemUnit) {
                    if ($unit === $itemUnit->unit_of_measure) {
                        $baseQty = $qty;
                    } elseif ($unit === $itemUnit->buying_unit) {
                        $baseQty = $qty * max(1, (float)$itemUnit->buying_relationship);
                    } elseif ($unit === $itemUnit->selling_unit) {
                        $baseQty = $qty * max(1, (float)$itemUnit->selling_relationship);
                    } else {
                        $baseQty = $qty;
                    }
                } else {
                    $baseQty = $qty;
                }

                $valueChange = $qty * $price;

                // rollback stok
                $this->adjustItemQuantity($old->item_id, $purchaseInvoice->location_id, -$baseQty, -$valueChange);
            }

            // hapus detail lama
            \App\PurchaseInvoiceDetail::where('purchase_invoice_id', $purchaseInvoice->id)->delete();

            // 7) Insert ulang detail baru & update stok
            foreach ($request->items ?? [] as $row) {
                $detail = \App\PurchaseInvoiceDetail::create([
                    'purchase_invoice_id' => $purchaseInvoice->id,
                    'item_id'             => $row['item_id'],
                    'quantity'            => $row['quantity'],
                    'order'               => $row['order'],
                    'back_order'          => $row['back_order'] ?? 0,
                    'unit'                => $row['unit'],
                    'item_description'    => $row['item_description'],
                    'price'               => $row['price'] ?? 0,
                    'discount'               => $row['discount'] ?? 0,
                    'tax_id'              => $row['tax_id'],
                    'tax_amount'          => $row['tax_amount'] ?? 0,
                    'amount'              => $row['amount'] ?? 0,
                    'account_id'          => $row['account_id'],
                    'project_id'          => $row['project_id'] ?? null,
                ]);
                $itemUnit = \App\ItemUnit::where('item_id', $row['item_id'])->first();

                $qty   = $row['quantity'] ?? 0;
                $unit  = $row['unit'] ?? null;
                $price = $row['price'] ?? 0;

                // konversi ke base unit
                if ($itemUnit) {
                    if ($unit === $itemUnit->unit_of_measure) {
                        $baseQty = $qty;
                    } elseif ($unit === $itemUnit->buying_unit) {
                        $baseQty = $qty * max(1, (float)$itemUnit->buying_relationship);
                    } elseif ($unit === $itemUnit->selling_unit) {
                        $baseQty = $qty * max(1, (float)$itemUnit->selling_relationship);
                    } else {
                        $baseQty = $qty;
                    }
                } else {
                    $baseQty = $qty;
                }

                $valueChange = $qty * $price;

                // update stok
                $this->adjustItemQuantity($row['item_id'], $request->location_id, $baseQty, $valueChange);
                // dump('📦 Update stok (UPDATE):', [
                //     'item_id' => $row['item_id'],
                //     'unit' => $unit,
                //     'qty_input' => $qty,
                //     'qtyChange (base)' => $baseQty,
                //     'valueChange' => $valueChange
                // ]);
            }

            // 8) Reset Journal lama
            $journal = \App\JournalEntry::where('source', 'purchase_invoice')
                ->where('comment', "Purchase Invoice #{$purchaseInvoice->invoice_number}")
                ->first();

            if ($journal) {
                \App\JournalEntryDetail::where('journal_entry_id', $journal->id)->delete();
            } else {
                $journal = \App\JournalEntry::create([
                    'source'  => 'purchase_invoice',
                    'tanggal' => $request->date_invoice,
                    'comment' => "Purchase Invoice #{$purchaseInvoice->invoice_number}",
                ]);
            }
            // dump('🪵 Journal Entry header dibuat:', $journal->toArray());


            // Helper ambil kode akun
            $coaCode = fn($id) => $id ? \App\ChartOfAccount::whereKey($id)->value('kode_akun') : null;

            $subtotal = collect($request->items)->sum('amount');
            $freight  = $request->freight ?? 0;
            $taxTotal = 0;

            // 9) Insert jurnal detail per item & pajak
            foreach ($request->items ?? [] as $row) {
                // Item utama
                if (!empty($row['amount']) && $row['amount'] > 0 && !empty($row['account_id'])) {
                    \App\JournalEntryDetail::create([
                        'journal_entry_id' => $journal->id,
                        'kode_akun'        => $coaCode($row['account_id']),
                        'debits'           => $row['amount'],
                        'credits'          => 0,
                        'comment'          => $row['item_description'] ?? null,
                        'status' => 2
                    ]);
                }

                // Pajak
                if (!empty($row['tax_amount']) && $row['tax_amount'] > 0 && !empty($row['tax_id'])) {
                    $salesTax = \App\SalesTaxes::find($row['tax_id']);
                    if ($salesTax) {
                        $taxAccountId = $salesTax->purchase_account_id;
                        $taxType = $salesTax->type;
                        $kodeAkunTax = $coaCode($taxAccountId);
                        $nilaiTax = abs($row['tax_amount']);

                        if ($taxType === 'input_tax') {
                            \App\JournalEntryDetail::create([
                                'journal_entry_id' => $journal->id,
                                'kode_akun'        => $kodeAkunTax,
                                'debits'           => $nilaiTax,
                                'credits'          => 0,
                                'comment'          => "PPN ({$salesTax->rate}%)",
                                'status' => 2
                            ]);
                            $taxTotal += $nilaiTax;
                        } elseif ($taxType === 'withholding_tax') {
                            \App\JournalEntryDetail::create([
                                'journal_entry_id' => $journal->id,
                                'kode_akun'        => $kodeAkunTax,
                                'debits'           => 0,
                                'credits'          => $nilaiTax,
                                'comment'          => "PPh Potongan ({$salesTax->rate}%)",
                                'status' => 2
                            ]);
                            $taxTotal -= $nilaiTax; // potongan
                        }
                    }
                }
            }

            // 10) Freight
            if ($freight > 0) {
                $freightLinked = \App\LinkedAccounts::where('kode', 'Freight Expense')->first();
                $freightKode   = $coaCode(optional($freightLinked)->akun_id);
                \App\JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $freightKode,
                    'debits'           => $freight,
                    'credits'          => 0,
                    'comment'          => 'Freight Expense',
                    'status' => 2
                ]);
            }


            // Withholding (Debit)
            if ($purchaseInvoice->withholding_value > 0) {
                $withholdingLinked = \App\SalesTaxes::where('id', $request->withholding_tax)->first();
                $withholding =  \App\JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $coaCode(optional($withholdingLinked)->purchase_account_id),
                    'debits'           => 0,
                    'credits'          => $purchaseInvoice->withholding_value,
                    'comment'          => 'PPh Dipotong pelanggan',
                    'status'           => 2
                ]);
                // dump('📘 Journal Withholding:', $withholding->toArray());
            }
            // 11) Credit lawan (kas / hutang usaha)
            $grandTotal = $subtotal + $taxTotal - $purchaseInvoice->withholding_value + $freight;

            if ($grandTotal > 0) {
                // 🔹 Gunakan payment_method_detail_id dari request
                $pmDetail = null;

                if ($request->filled('payment_method_account_id')) {
                    $pmDetail = \App\PaymentMethodDetail::find($request->payment_method_account_id);
                }

                // 🔹 Jika user tidak memilih, fallback ke default
                if (!$pmDetail) {
                    $pmDetail = \App\PaymentMethodDetail::where('payment_method_id', $request->jenis_pembayaran_id)
                        ->where('is_default', 1)
                        ->first()
                        ?? \App\PaymentMethodDetail::where('payment_method_id', $request->jenis_pembayaran_id)->first();
                }

                // 🔹 Ambil kode akun dari COA yang terkait dengan PaymentMethodDetail
                $pmKode = $coaCode(optional($pmDetail)->account_id);

                // 🔹 Simpan jurnal dengan akun sesuai pilihan user
                \App\JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $pmKode,
                    'debits'           => 0,
                    'credits'          => $grandTotal,
                    'comment'          => 'Payment / Credit',
                    'status' => 2
                ]);

                // (Opsional) Jika Anda ingin menyimpan untuk tracking:
                $purchaseInvoice->update([
                    'payment_method_detail_id' => $pmDetail->id ?? null,
                ]);
            }

            $totalDebit = \App\JournalEntryDetail::where('journal_entry_id', $journal->id)->sum('debits');
            $totalCredit = \App\JournalEntryDetail::where('journal_entry_id', $journal->id)->sum('credits');

            $selisih = round($totalDebit - $totalCredit, 2);

            // dump('📘 JOURNAL SUMMARY', [
            //     'journal_id' => $journal->id,
            //     'invoice_number' => $purchaseInvoice->invoice_number,
            //     'total_debit' => number_format($totalDebit, 2),
            //     'total_credit' => number_format($totalCredit, 2),
            //     'selisih' => number_format($selisih, 2),
            // ]);

            // // 🔍 Tampilkan semua detail jurnal untuk analisis
            // $details = \App\JournalEntryDetail::where('journal_entry_id', $journal->id)
            //     ->select('kode_akun', 'debits', 'credits', 'comment')
            //     ->get()
            //     ->toArray();

            // dump('📑 DETAIL JURNAL:', $details);

            // // ✅ Jika selisih ≠ 0, beri tanda warning
            // if ($selisih !== 0) {
            //     dump("⚠️ WARNING: Journal tidak seimbang! Selisih = {$selisih}");
            // } else {
            //     dump("✅ Journal seimbang (Debit = Credit).");
            // }
            DB::commit();
            return redirect()->route('purchase_invoice.index')
                ->with('success', 'Purchase Invoice berhasil diupdate beserta Journal (pajak diperhitungkan).');
        } catch (\Throwable $e) {
            DB::rollBack();
            // dd('❌ Error update:', $e->getMessage(), $e->getTraceAsString());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $invoice = \App\PurchaseInvoice::with('details')->findOrFail($id);

            // 1) Rollback stok dari semua detail SEBELUM dihapus
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
                //     'buying_unit'    => optional($itemUnit)->buying_unit,
                //     'relationship'    => optional($itemUnit)->buying_relationship,
                //     'qty_dikembalikan' => $qtyChange,
                //     'unitCost'        => $unitCost,
                //     'value_dikembalikan' => $valueChange,
                // ]);

                // Tambahkan kembali stok
                $this->adjustItemQuantity($detail->item_id, $invoice->location_id, $qtyChange, $valueChange, true);
            }
            // 5) Update status PO
            if (!empty($invoice->purchase_order_id)) {
                \App\PurchaseOrder::whereKey($invoice->purchase_order_id)
                    ->update(['status_purchase' => 0]);
            }

            // 2) Hapus Journal Entry
            $journal = \App\JournalEntry::where('source', 'purchase_invoice')
                ->where('comment', "Purchase Invoice #{$invoice->invoice_number}") // atau pakai source_id kalau ada
                ->first();

            if ($journal) {
                \App\JournalEntryDetail::where('journal_entry_id', $journal->id)->delete();
                $journal->delete();
            }

            // 3) Hapus detail invoice
            \App\PurchaseInvoiceDetail::where('purchase_invoice_id', $invoice->id)->delete();

            // 4) Hapus header invoice
            $invoice->delete();

            DB::commit();
            return redirect()->route('purchase_invoice.index')
                ->with('success', 'Purchase Invoice beserta jurnal & stok berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            // dd("❌ ERROR DELETE INVOICE:", $e->getMessage(), $e->getTraceAsString());
            return back()->withErrors('Gagal menghapus invoice: ' . $e->getMessage());
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
    public function print($id)
    {
        // Ambil sales order beserta relasi terkait
        $purchaseInvoice = PurchaseInvoice::with([
            'vendor',
            'jenisPembayaran',
            'details.item',
            'details.account',
            'details.sales_taxes'
        ])->findOrFail($id);
        $companyProfile = CompanyProfile::first();


        return view('purchase_invoice.print', compact('purchaseInvoice', 'companyProfile'));
    }
    public function downloadPdf($id)
    {
        // Ambil sales order beserta relasi terkait
        $purchaseInvoice = PurchaseInvoice::with([
            'vendor',
            'jenisPembayaran',
            'details.item',
            'details.account',
            'details.sales_taxes'
        ])->findOrFail($id);
        $companyProfile = CompanyProfile::first();
        $isPdf = true;
        $pdf = Pdf::loadView('purchase_invoice.print', compact(
            'purchaseInvoice',
            'companyProfile',
            'isPdf'
        ))->setPaper('A4', 'portrait');

        return $pdf->download("PO_{$purchaseInvoice->invoice_number}.pdf");
    }
}

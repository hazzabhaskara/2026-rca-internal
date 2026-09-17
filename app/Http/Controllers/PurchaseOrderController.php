<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use App\CompanyProfile;
use App\Customers;
use App\Item;
use App\LocationInventory;
use App\PaymentMethod;
use App\PurchaseOrder;
use App\PurchaseOrderDetail;
use App\SalesTaxes;
use App\Setting;
use App\Vendors;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    //

    public function index()
    {
        $query = PurchaseOrder::with(['jenisPembayaran', 'vendor', 'locationInventory']);
        if ($location_inventory = request('filter_location')) {
            $query->whereHas('locationInventory', function ($q) use ($location_inventory) {
                $q->where('kode_lokasi', $location_inventory);
            });
        }

        if (request()->filled('filter_status')) {
            $query->where('status_purchase', request('filter_status'));
        }

        $searchable = ['order_number', 'date_order',];

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
        $data = $query->orderBy('date_order', 'asc')->orderBy('status_purchase', 'asc')->paginate(10);
        $location = LocationInventory::pluck('kode_lokasi')->filter()->unique()->values();
        return view('purchase_order.index', compact('data', 'location'));
    }
    public function create()
    {
        $vendor = Vendors::all();
        $jenis_pembayaran = PaymentMethod::all();
        $items = Item::all(); // semua item yang bisa dipilih
        $accounts = chartOfAccount::all(); // akun-akun untuk entri jurnal
        $sales_taxes = SalesTaxes::all();
        $locationInventory = LocationInventory::all();
        return view('purchase_order.create', compact('vendor', 'jenis_pembayaran',  'items', 'accounts', 'sales_taxes', 'locationInventory'));
    }
    private function generateKodeOrder($dateOrder)
    {
        // Formatkan tanggal order sesuai kebutuhan (misal YYYYMMDD)
        $dateFormatted = \Carbon\Carbon::parse($dateOrder)->format('Ymd');

        $prefix = 'PO-' . $dateFormatted . '-';

        // Cari order terakhir berdasarkan tanggal order yg sama
        $last = \App\PurchaseOrder::where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($last && preg_match('/' . $prefix . '(\d+)/', $last->order_number, $matches)) {
            $number = (int) $matches[1] + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        // 1️⃣ Generate kode order
        if ($request->has('auto_generate')) {
            $order_number = $this->generateKodeOrder($request->date_order);
        } else {
            $request->validate([
                'order_number' => 'required|unique:purchase_orders,order_number',
            ]);
            $order_number = $request->order_number;
        }

        // 2️⃣ Bersihkan angka dari format ribuan sebelum validasi
        if ($request->has('items')) {
            $items = $request->items;
            foreach ($items as $i => $item) {
                foreach (['price', 'discount', 'tax_amount', 'amount', 'order', 'back_order', 'quantity'] as $key) {
                    if (isset($item[$key])) {
                        // Hapus semua karakter non-digit kecuali titik dan minus
                        $items[$i][$key] = preg_replace('/[^0-9.\-]/', '', $item[$key]);
                    }
                }
            }
            $request->merge(['items' => $items]);
        }

        // 3️⃣ Validasi input utama
        $validated = $request->validate([
            'order_number'         => 'required|unique:purchase_orders,order_number',
            'date_order'           => 'required|date',
            'shipping_date'        => 'nullable|date',
            'vendor_id'            => 'required|exists:vendors,id',
            'account_id'           => 'required|exists:payment_method_details,id',
            'jenis_pembayaran_id'  => 'required|exists:payment_methods,id',
            'location_id'          => 'required|exists:location_inventories,id',
            'shipping_address'     => 'nullable|string',
            'freight'              => 'nullable|numeric|min:0',
            'early_payment_terms'  => 'nullable|string',
            'messages'             => 'nullable|string',

            // Validasi detail item
            'items'                => 'required|array|min:1',
            'items.*.item_id'      => 'required|exists:items,id',
            'items.*.quantity'     => 'nullable|numeric|min:0',
            'items.*.order'        => 'required|numeric|min:0',
            'items.*.back_order'   => 'nullable|numeric|min:0',
            'items.*.unit'         => 'required|string',
            'items.*.description'  => 'required|string',
            'items.*.price'        => 'nullable|numeric|min:0',
            'items.*.tax_id'       => 'nullable|exists:sales_taxes,id',
            'items.*.tax_amount'   => 'nullable|numeric|min:0',
            'items.*.amount'       => 'nullable|numeric|min:0',
            'items.*.discount'     => 'nullable|numeric|min:0',
            'items.*.account'      => 'required|exists:chart_of_accounts,id',
        ]);

        DB::beginTransaction();
        try {
            // 4️⃣ Simpan header ke purchase_orders
            $purchaseOrder = PurchaseOrder::create([
                'order_number'         => $order_number,
                'date_order'           => $request->date_order,
                'shipping_date'        => $request->shipping_date,
                'vendor_id'            => $request->vendor_id,
                'account_id'           => $request->account_id,
                'location_id'          => $request->location_id,
                'jenis_pembayaran_id'  => $request->jenis_pembayaran_id,
                'shipping_address'     => $request->shipping_address,
                'freight'              => preg_replace('/[^0-9.\-]/', '', $request->freight ?? 0),
                'early_payment_terms'  => $request->early_payment_terms,
                'messages'             => $request->messages,
                'status_purchase' => 0
            ]);

            // 5️⃣ Simpan detail ke purchase_order_details
            foreach ($request->items as $item) {
                $price      = (float) ($item['price'] ?? 0);
                $discount   = (float) ($item['discount'] ?? 0);
                $tax_amount = (float) ($item['tax_amount'] ?? 0);
                $amount     = (float) ($item['amount'] ?? 0);

                PurchaseOrderDetail::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id'           => $item['item_id'],
                    'quantity'          => $item['quantity'] ?? 0,
                    'order'             => $item['order'],
                    'back_order'        => $item['back_order'] ?? 0,
                    'unit'              => $item['unit'],
                    'item_description'  => $item['description'],
                    'price'             => $price,
                    'discount'          => $discount,
                    'tax_id'            => $item['tax_id'] ?? null,
                    'tax_amount'        => $tax_amount,
                    'amount'            => $amount,
                    'account_id'        => $item['account'],
                ]);
            }

            DB::commit();
            return redirect()->route('purchase_order.index')->with('success', 'Purchase order berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        // Ambil sales order beserta relasi terkait
        $purchaseOrder = PurchaseOrder::with([
            'vendor',
            'locationInventory',
            'jenisPembayaran',
            'details.item',
            'details.account',
            'details.sales_taxes'
        ])->findOrFail($id);

        return view('purchase_order.show', compact('purchaseOrder'));
    }
    public function edit($id)
    {
        $purchaseOrder = PurchaseOrder::with('details', 'details.sales_taxes')->findOrFail($id);
        $vendors = Vendors::all();
        $jenis_pembayaran = PaymentMethod::all();
        $items = Item::all();
        $locationInventory = LocationInventory::all();
        $account = ChartOfAccount::all();
        $sales_taxes = SalesTaxes::all(); // ✅ ambil semua pajak

        return view('purchase_order.edit', compact(
            'purchaseOrder',
            'vendors',
            'jenis_pembayaran',
            'items',
            'account',
            'sales_taxes',
            'locationInventory' // ✅ kirim ke view
        ));
    }

    public function update(Request $request, $id)
    {

        if ($request->has('items')) {
            $items = $request->items;
            foreach ($items as $i => $item) {
                foreach (['price', 'discount', 'tax_amount', 'amount', 'order', 'back_order', 'quantity'] as $key) {
                    if (isset($item[$key])) {
                        // Hapus semua karakter non-digit kecuali titik dan minus
                        $items[$i][$key] = preg_replace('/[^0-9.\-]/', '', $item[$key]);
                    }
                }
            }
            $request->merge(['items' => $items]);
        }

        // dd($request->all());
        $request->validate([
            'order_number'         => 'required|string',
            'date_order'           => 'required|date',
            'shipping_date'        => 'nullable|date',
            'vendor_id'          => 'required|exists:vendors,id',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'location_id' => 'required|exists:location_inventories,id',
            'jenis_pembayaran_id'  => 'required|exists:payment_methods,id',
            'shipping_address'     => 'nullable|string',
            'freight'              => 'nullable|numeric|min:0',
            'early_payment_terms'  => 'nullable|string',
            'messages'             => 'nullable|string',
            // edatil
            'items'                => 'required|array|min:1',
            'items.*.item_id'      => 'required|exists:items,id',
            'items.*.quantity'     => 'nullable|numeric|min:0',
            'items.*.order'        => 'required|numeric|min:0',
            'items.*.back_order'   => 'nullable|numeric|min:0',
            'items.*.unit'         => 'required|string',
            'items.*.description'  => 'required|string',
            'items.*.price'   => 'nullable|numeric|min:0',
            'items.*.discount'   => 'nullable|numeric|min:0',
            'items.*.tax_id'     => 'nullable|exists:sales_taxes,id',
            'items.*.tax_amount'        => 'nullable|numeric|min:0',
            'items.*.amount'       => 'nullable|numeric|min:0',
            'items.*.account_id'      => 'required|exists:chart_of_accounts,id',
        ]);

        DB::beginTransaction();

        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);
            $purchaseOrder->update([
                'order_number'         => $request->order_number,
                'date_order'           => $request->date_order,
                'shipping_date'        => $request->shipping_date,
                'vendor_id'          => $request->vendor_id,
                'account_id' => $request->account_id,
                'location_id' => $request->location_id,
                'jenis_pembayaran_id'  => $request->jenis_pembayaran_id,
                'shipping_address'     => $request->shipping_address,
                'freight'              => $request->freight,
                'early_payment_terms'  => $request->early_payment_terms,
                'messages'             => $request->messages,
            ]);

            // Hapus detail lama dan simpan ulang
            PurchaseOrderDetail::where('purchase_order_id', $purchaseOrder->id)->delete();

            foreach ($request->items as $item) {
                $price      = (float) ($item['price'] ?? 0);
                $discount   = (float) ($item['discount'] ?? 0);
                $tax_amount = (float) ($item['tax_amount'] ?? 0);
                $amount     = (float) ($item['amount'] ?? 0);
                PurchaseOrderDetail::create([
                    'purchase_order_id'     => $purchaseOrder->id,
                    'item_id'            => $item['item_id'],
                    'quantity'           => $item['quantity'] ?? 0,
                    'order'              => $item['order'],
                    'back_order'         => $item['back_order'] ?? 0,
                    'unit'               => $item['unit'],
                    'item_description'   => $item['description'],
                    'price'         => $price,
                    'discount'         => $discount,
                    'tax_id'           => $item['tax_id'] ?? null,
                    'tax_amount'              => $item['tax_amount'],
                    'amount'             => $item['amount'],
                    'account_id'         => $item['account_id'],
                ]);
            }

            DB::commit();
            return redirect()->route('purchase_order.index')->with('success', 'Purchase order berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan saat update: ' . $e->getMessage())->withInput();
        }
    }
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $order = PurchaseOrder::with(['details', 'invoices'])->findOrFail($id);

                // 🚫 Cek apakah sudah dipakai di Invoice
                if ($order->invoices()->exists()) {
                    throw new \Exception("PO ini sudah digunakan dalam Purchase Invoice, tidak bisa dihapus.");
                }

                // ✅ Kalau aman, hapus (details ikut terhapus otomatis via cascade)
                $order->delete();
            });

            return redirect()->route('purchase_order.index')->with('success', 'Purchase Order berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('purchase_order.index')->with('error', $e->getMessage());
        }
    }
    public function print($id)
    {
        $purchaseOrder = PurchaseOrder::with(['vendor', 'locationInventory', 'jenisPembayaran', 'details.item'])->findOrFail($id);

        // bisa buat perhitungan subtotal, tax, grand total
        $subtotal = $purchaseOrder->details->sum('amount');
        $taxTotal = $purchaseOrder->details->sum('tax_amount');
        $grandTotal = $subtotal + $taxTotal + $purchaseOrder->freight;
        $companyProfile = CompanyProfile::first();
        $setting = Setting::first();

        return view('purchase_order.print', compact('purchaseOrder', 'subtotal', 'taxTotal', 'grandTotal', 'companyProfile', 'setting'));
    }

    public function downloadPdf($id)
    {
        $purchaseOrder = PurchaseOrder::with(['vendor', 'details.item'])->findOrFail($id);
        $subtotal = $purchaseOrder->details->sum('amount');
        $taxTotal = $purchaseOrder->details->sum('tax_amount');
        $grandTotal = $subtotal + $taxTotal + $purchaseOrder->freight;
        $companyProfile = CompanyProfile::first();
        $isPdf = true;
        $pdf = Pdf::loadView('purchase_order.print', compact(
            'purchaseOrder',
            'subtotal',
            'taxTotal',
            'grandTotal',
            'companyProfile',
            'isPdf'
        ))->setPaper('A4', 'portrait');

        return $pdf->download("PO_{$purchaseOrder->order_number}.pdf");
    }
}

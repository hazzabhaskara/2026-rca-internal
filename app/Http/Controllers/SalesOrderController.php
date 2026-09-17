<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\CompanyProfile;
use App\Customers;
use App\Employee;
use App\Item;
use App\jenis_pembayaran;
use App\LocationInventory;
use App\PaymentMethod;
use App\SalesOrder;
use App\SalesOrderDetail;
use App\SalesTaxes;
use Barryvdh\DomPDF\PDF as DomPDFPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class SalesOrderController extends Controller
{
    //
    public function index()
    {
        $data = SalesOrder::with(['customer', 'jenisPembayaran', 'salesPerson', 'locationInventory'])->orderBy('date_order', 'asc')->orderBy('status_sales', 'asc')->paginate(5);

        return view('sales_order.index', compact('data'));
    }
    public function create()
    {
        $customer = Customers::all();
        $jenis_pembayaran = PaymentMethod::all();
        $employee = Employee::all();
        $items = Item::all(); // semua item yang bisa dipilih
        $accounts = chartOfAccount::all(); // akun-akun untuk entri jurnal
        $sales_taxes = SalesTaxes::where('type', 'input_tax')->get();
        $lokasi_inventory = LocationInventory::all();
        return view('sales_order.create', compact('customer', 'jenis_pembayaran', 'employee', 'items', 'accounts', 'sales_taxes', 'lokasi_inventory'));
    }

    private function generateKodeOrder($dateOrder)
    {
        // Formatkan tanggal order sesuai kebutuhan (misal YYYYMMDD)
        $dateFormatted = \Carbon\Carbon::parse($dateOrder)->format('Ymd');

        $prefix = 'SO-' . $dateFormatted . '-';

        // Cari order terakhir berdasarkan tanggal order yg sama
        $last = \App\SalesOrder::where('order_number', 'like', $prefix . '%')
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

        // Cek apakah checkbox auto_generate dicentang
        if ($request->has('auto_generate')) {
            $order_number = $this->generateKodeOrder($request->date_order);
        } else {
            // Validasi manual
            $request->validate([
                'order_number' => 'unique:purchase_orders,order_number',
            ]);
            $order_number = $request->order_number;
        }
        $request->merge([
            'freight' => str_replace('.', '', $request->freight),
        ]);


        // ✅ VALIDASI FORM INPUT
        $request->validate([
            'date_order'           => 'required|date',
            'shipping_date'        => 'nullable|date',
            'customer_id'          => 'required|exists:customers,id',
            'location_id'          => 'required|exists:location_inventories,id',
            'sales_person_id'          => 'nullable|exists:employees,id',
            'jenis_pembayaran_id'  => 'required|exists:payment_methods,id',
            'payment_method_account_id'  => 'required|exists:payment_method_details,id',
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
            'items.*.base_price'   => 'nullable|numeric|min:0',
            'items.*.discount'     => 'nullable|numeric|min:0',
            'items.*.price'        => 'nullable|numeric|min:0',
            'items.*.amount'       => 'nullable|numeric|min:0',
            'items.*.tax_value'          => 'nullable|numeric|min:0',
            'items.*.account'      => 'required|exists:chart_of_accounts,id',
            'items.*.tax_id'      => 'nullable|exists:sales_taxes,id',
        ]);

        DB::beginTransaction();

        try {
            // ✅ Simpan data utama ke tabel sales_orders
            $salesOrder = SalesOrder::create([
                'order_number'         => $order_number,
                'date_order'           => $request->date_order,
                'shipping_date'        => $request->shipping_date,
                'customer_id'          => $request->customer_id,
                'location_id' => $request->location_id,
                'sales_person_id'          => $request->sales_person_id ?? null,
                'jenis_pembayaran_id'  => $request->jenis_pembayaran_id,
                'shipping_address'     => $request->shipping_address,
                'freight'              => $request->freight,
                'early_payment_terms'  => $request->early_payment_terms,
                'messages'             => $request->messages,
                'payment_method_account_id' => $request->payment_method_account_id,
                'status_sales' => 0
            ]);

            // dump("input sales order:", $salesOrder->toArray());

            // ✅ Simpan setiap item ke tabel sales_order_details
            foreach ($request->items as $item) {
                SalesOrderDetail::create([
                    'sales_order_id'   => $salesOrder->id,
                    'item_id'          => $item['item_id'],
                    'quantity'         => $this->normalizeNumber($item['quantity'] ?? 0),
                    'order'            => $this->normalizeNumber($item['order']),
                    'back_order'       => $this->normalizeNumber($item['back_order'] ?? 0),
                    'unit'             => $item['unit'],
                    'item_description' => $item['description'],
                    'base_price'       => $this->normalizeNumber($item['base_price']),
                    'discount'         => $this->normalizeNumber($item['discount']),
                    'price'            => $this->normalizeNumber($item['price']),
                    'amount'           => $this->normalizeNumber($item['amount']),
                    'tax'              => $this->normalizeNumber($item['tax_value']),
                    'account_id'       => $item['account'],
                    'tax_id'       => $item['tax_id'] ?? null,
                ]);
            }


            DB::commit();
            return redirect()->route('sales_order.index')->with('success', 'Sales order berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage()])->withInput();
        }
    }
    // Helper untuk normalisasi angka
    private function normalizeNumber($value)
    {
        if ($value === null) return 0;
        // cukup ganti koma jadi titik (kalau ada), jangan hapus titik desimal
        $value = str_replace(',', '.', $value);
        return (float) $value;
    }


    public function show($id)
    {
        // Ambil sales order beserta relasi terkait
        $salesOrder = SalesOrder::with([
            'customer',
            'locationInventory',
            'salesPerson',
            'jenisPembayaran',
            'details.item',
            'details.account',
            'details.sales_taxes'
        ])->findOrFail($id);

        return view('sales_order.show', compact('salesOrder'));
    }
    public function edit($id)
    {
        $salesOrder = SalesOrder::with('details')->findOrFail($id);
        $customers = Customers::all();
        $employees = Employee::all();
        $jenis_pembayaran = PaymentMethod::all();
        $items = Item::all(); // semua item yang bisa dipilih
        $sales_taxes = SalesTaxes::all();
        $location_inventory = LocationInventory::all();


        return view('sales_order.edit', compact('salesOrder', 'customers', 'employees', 'jenis_pembayaran', 'items', 'sales_taxes', 'location_inventory'));
    }

    /**
     * Update sales order.
     */
    public function update(Request $request, $id)
    {

        // dd($request->all());
        $request->validate([
            'order_number' => 'required|string',
            'date_order' => 'required|date',
            'shipping_date' => 'nullable|date',
            'customer_id' => 'required|exists:customers,id',
            'sales_person_id' => 'nullable|exists:employees,id',
            'location_id' => 'required|exists:location_inventories,id',
            'jenis_pembayaran_id' => 'required|exists:payment_methods,id',
            'payment_method_account_id' => 'required|exists:payment_method_details,id',
            'shipping_address' => 'nullable|string',
            'freight' => 'nullable|numeric',
            'early_payment_terms' => 'nullable|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'items.*.order' => 'required|numeric|min:0',
            'items.*.back_order' => 'nullable|numeric',
            'items.*.unit' => 'required|string',
            'items.*.description' => 'required|string',
            'items.*.base_price' => 'required|numeric',
            'items.*.discount' => 'required|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.tax_value' => 'required|numeric|min:0',
            'items.*.account' => 'required|exists:chart_of_accounts,id',
            'items.*.tax_id' => 'nullable|exists:sales_taxes,id',
        ]);

        DB::beginTransaction();

        try {
            $salesOrder = SalesOrder::findOrFail($id);
            $salesOrder->update([
                'order_number' => $request->order_number,
                'date_order' => $request->date_order,
                'shipping_date' => $request->shipping_date,
                'customer_id' => $request->customer_id,
                'location_id' => $request->location_id,
                'sales_person_id' => $request->sales_person_id,
                'jenis_pembayaran_id' => $request->jenis_pembayaran_id,
                'shipping_address' => $request->shipping_address,
                'freight' => $request->freight,
                'early_payment_terms' => $request->early_payment_terms,
                'messages' => $request->messages,
                'payment_method_account_id' => $request->payment_method_account_id,
                'status_sales' => 0,
            ]);

            // Hapus detail lama dan simpan ulang
            SalesOrderDetail::where('sales_order_id', $salesOrder->id)->delete();

            foreach ($request->items as $item) {
                SalesOrderDetail::create([
                    'sales_order_id' => $salesOrder->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'] ?? 0,
                    'order' => $item['order'],
                    'back_order' => $item['back_order'] ?? 0,
                    'unit' => $item['unit'],
                    'item_description' => $item['description'],
                    'base_price' => $item['base_price'],
                    'discount' => $item['discount'],
                    'price' => $item['price'],
                    'amount' => $item['amount'],
                    'tax' => $item['tax_value'],
                    'account_id' => $item['account'],
                    'tax_id' => $item['tax_id'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('sales_order.index')->with('success', 'Sales order berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan saat update: ' . $e->getMessage())->withInput();
        }
    }

    public function exportPdf($id)
    {
        $salesOrder = SalesOrder::with('details.item', 'customer', 'jenisPembayaran', 'salesPerson')->findOrFail($id);

        $pdf = Pdf::loadView('sales_order.pdf', compact('salesOrder'));
        return $pdf->download('SalesOrder-' . $salesOrder->order_number . '.pdf');
    }
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $order = SalesOrder::with(['details', 'invoices'])->findOrFail($id);

                // 🚫 Cek apakah sudah dipakai di Invoice
                if ($order->invoices()->exists()) {
                    throw new \Exception("SO ini sudah digunakan dalam Sales Invoice, tidak bisa dihapus.");
                }

                // ✅ Kalau aman, hapus (details ikut terhapus otomatis via cascade)
                $order->delete();
            });

            return redirect()->route('sales_order.index')->with('success', 'Sales Order berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('sales_order.index')->with('error', $e->getMessage());
        }
    }
    public function print($id)
    {
        // Ambil sales order beserta relasi terkait
        $salesOrder = SalesOrder::with([
            'customer',
            'locationInventory',
            'salesPerson',
            'jenisPembayaran',
            'details.item',
            'details.account',
            'details.sales_taxes'
        ])->findOrFail($id);

        $companyProfile = CompanyProfile::first();

        return view('sales_order.print', compact('salesOrder', 'companyProfile'));
    }
    public function downloadPdf($id)
    {
        $salesOrder = SalesOrder::with([
            'customer',
            'locationInventory',
            'salesPerson',
            'jenisPembayaran',
            'details.item',
            'details.account',
            'details.sales_taxes'
        ])->findOrFail($id);

        $companyProfile = CompanyProfile::first();
        $isPdf = true;
        $pdf = Pdf::loadView('sales_order.print', compact(
            'salesOrder',
            'companyProfile',
            'isPdf'
        ))->setPaper('A4', 'portrait');

        return $pdf->download("PO_{$salesOrder->order_number}.pdf");
    }
}

<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\Inventory;
use App\LocationInventory;
use App\PriceListInventory;
use App\Taxes;
use App\Vendors;
use App\Item;
use App\ItemQuantity;
use App\ItemUnit;
use App\ItemPrice;
use App\ItemVendor;
use App\ItemAccount;
use App\ItemBuild;
use App\ItemBuildDetail;
use App\ItemQuantities;
use App\ItemTaxes;
use App\SalesTaxes;
use App\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    //
    public function index()
    {
        $items = Item::with('quantities')->paginate(10);
        return view('inventory.index', compact('items'));
    }

    public function create()
    {
        $accounts = chartOfAccount::where('aktif', true)->orderBy('kode_akun')->get();
        $items = Item::all();
        $taxes = SalesTaxes::all();
        $lokasiInventory = LocationInventory::all();
        $priceListInventory = PriceListInventory::all();
        $vendors = Vendors::all();
        return view('inventory.create', compact('accounts', 'items', 'taxes', 'lokasiInventory', 'priceListInventory', 'vendors'));
    }
    public function store(Request $request)
    {
        // ===== 1) VALIDASI DASAR =====
        // dd($request->all());
        $rules = [
            'item_number' => ['required', 'string', 'max:100', Rule::unique('items', 'item_number')],
            'item_description'   => ['required', 'string', 'max:255'],
            'type'        => ['required', Rule::in(['inventory', 'service'])],

            // Quantities (opsional)
            'location_id'           => ['nullable', 'integer', 'exists:location_inventories,id'],
            'on_hand_qty'           => ['nullable', 'integer'],
            'on_hand_value'         => ['nullable', 'numeric'],
            'pending_orders_qty'    => ['nullable', 'integer'],
            'pending_orders_value'  => ['nullable', 'numeric'],
            'purchase_order_qty'    => ['nullable', 'integer'],
            'sales_order_qty'       => ['nullable', 'integer'],
            'reorder_minimum'       => ['nullable', 'integer'],
            'reorder_to_order'      => ['nullable', 'integer'],

            // Units
            'stocking_unit'         => ['nullable', 'string', 'max:100'],
            'selling_same_as_stocking' => ['nullable'],
            'selling_unit'          => ['nullable', 'string', 'max:100'],
            'selling_relationship'  => ['nullable', 'integer', 'min:0'],
            'buying_same_as_stocking'  => ['nullable'],
            'buying_unit'           => ['nullable', 'string', 'max:100'],
            'buying_relationship'   => ['nullable', 'integer', 'min:0'],

            // Pricing (array)
            'pricing'               => ['nullable', 'array'],
            'pricing.*.name'        => ['nullable', 'string', 'max:255'],
            'pricing.*.price'       => ['nullable', 'numeric'],

            // Vendors
            'vendor_id'             => ['nullable', 'integer', 'exists:vendors,id'],

            // Accounts (inventory)
            'asset_account_id'      => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'revenue_account_id'    => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'cogs_account_id'       => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'variance_account_id'   => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'expense_account_id'    => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'account_receivable_id'    => ['nullable', 'integer', 'exists:chart_of_accounts,id'],

            // Build
            'build'                 => ['nullable', 'array'],
            'build.*.item_id'       => ['nullable', 'integer', 'exists:items,id'],
            'build.*.unit'          => ['nullable', 'string', 'max:100'],
            'build.*.description'   => ['nullable', 'string', 'max:255'],
            'build.*.quantity'      => ['nullable', 'integer', 'min:0'],

            // Taxes
            'taxes'                 => ['nullable', 'array'],
            'taxes.*'               => ['integer', 'exists:sales_taxes,id'],
            'tax_exempt'            => ['nullable', 'array'],
            'tax_exempt.*'          => ['integer', 'exists:sales_taxes,id'],

            // Files
            'picture'               => ['nullable', 'file', 'image', 'max:2048'],
            'thumbnail'             => ['nullable', 'file', 'image', 'max:1024'],

            // Descriptions
            'long_description'      => ['nullable', 'string'],
        ];

        // Tambah aturan "wajib akun" sesuai type
        if ($request->type === 'inventory') {
            $rules['asset_account_id'][]    = 'nullable';
            $rules['revenue_account_id'][]  = 'nullable';
            $rules['cogs_account_id'][]     = 'nullable';
            $rules['variance_account_id'][] = 'nullable';
        } else { // service
            $rules['revenue_account_id'][]  = 'nullable';
            $rules['expense_account_id'][]  = 'nullable';
            $rules['account_receivable_id'][]  = 'nullable';
        }

        // Wajib akun (kalau kamu mau enforce saat edit, bisa ganti nullable→required)
        if ($request->type === 'inventory') {
            // tetap nullable sesuai versi store terakhir kamu
        } else {
            // service
        }

        $validated = $request->validate($rules);

        // ===== 2) SIMPAN DALAM TRANSAKSI =====
        DB::beginTransaction();

        try {
            // ===== 2a) Upload gambar (jika ada) =====
            $picturePath = null;
            $thumbPath   = null;

            if ($request->hasFile('picture')) {
                $picturePath = $request->file('picture')->store('items/pictures', 'public');
            }
            if ($request->hasFile('thumbnail')) {
                $thumbPath = $request->file('thumbnail')->store('items/thumbnails', 'public');
            }

            // ===== 2b) Buat Item =====
            $item = Item::create([
                'item_number'     => $validated['item_number'],
                'item_description' => $validated['item_description'], // deskripsi singkat
                'description'     => $request->input('long_description'), // deskripsi panjang
                'type'            => $validated['type'],
                'picture_path'    => $picturePath,
                'thubmnail_path'  => $thumbPath,
            ]);


            // ===== 2c) Item Quantities (opsional, buat 1 baris per item) =====
            $anyQuantityFilled = $request->filled([
                'location_id',
                'on_hand_qty',
                'on_hand_value',
                'pending_orders_qty',
                'pending_orders_value',
                'purchase_order_qty',
                'sales_order_qty',
                'reorder_minimum',
                'reorder_to_order'
            ]);

            if ($request->has('quantities')) {
                foreach ($request->quantities as $row) {
                    if (!empty($row['location_id'])) {
                        ItemQuantities::create([
                            'item_id'               => $item->id,
                            'location_id'           => $row['location_id'],
                            'on_hand_qty'           => (int) ($row['on_hand_qty'] ?? 0),
                            'on_hand_value'         => (float) ($row['on_hand_value'] ?? 0),
                            'pending_orders_qty'    => (int) ($row['pending_orders_qty'] ?? 0),
                            'pending_orders_value'  => (float) ($row['pending_orders_value'] ?? 0),
                            'purchase_order_qty'    => (int) ($row['purchase_order_qty'] ?? 0),
                            'sales_order_qty'       => (int) ($row['sales_order_qty'] ?? 0),
                            'reorder_minimum'       => (int) ($row['reorder_minimum'] ?? 0),
                            'reorder_to_order'      => (int) ($row['reorder_to_order'] ?? 0),
                        ]);
                    }
                }
            }


            // ===== 2d) Item Units =====
            // Checkbox akan bernilai true jika dicentang
            $sellingSame = $request->has('selling_same_as_stocking');
            $buyingSame  = $request->has('buying_same_as_stocking');

            ItemUnit::create([
                'item_id'                  => $item->id,

                // Selling
                'selling_same_as_stocking' => $sellingSame,
                'selling_unit'             => $sellingSame
                    ? $request->input('stocking_unit')
                    : $request->input('selling_unit'),
                'selling_relationship'     => $sellingSame
                    ? 1
                    : $request->input('selling_relationship'),

                // Buying
                'buying_same_as_stocking'  => $buyingSame,
                'buying_unit'              => $buyingSame
                    ? $request->input('stocking_unit')
                    : $request->input('buying_unit'),
                'buying_relationship'      => $buyingSame
                    ? 1
                    : $request->input('buying_relationship'),

                // Stocking / unit utama
                'unit_of_measure'          => $request->input('unit_of_measure')
                    ?: $request->input('stocking_unit'),
            ]);


            // ===== 2e) Item Prices (pricing array dari Inventory/Service) =====
            $pricing = collect($request->input('pricing', []))
                ->filter(function ($row) {
                    // minimal ada nama atau price
                    return !empty($row['name']) || isset($row['price']);
                });

            foreach ($pricing as $row) {
                ItemPrice::create([
                    'item_id'        => $item->id,
                    'price_list_name' => $row['name'] ?? '',
                    'price'          => (float) ($row['price'] ?? 0),
                ]);
            }
            // ===== 2f) Item Vendor (opsional) =====
            $vendorId = $request->input('vendor_id_inventory') ?? $request->input('vendor_id_service');

            if ($vendorId) {
                ItemVendor::create([
                    'item_id'        => $item->id,
                    'vendor_id'      => $vendorId,
                    'vendor_contact' => null, // isi kalau ada field kontak di form
                ]);
            }


            // ===== 2g) Item Accounts =====
            // Inventory: asset, revenue, cogs, variance (required)
            // Service: revenue, expense (required)
            $accountsPayload = [
                'item_id'            => $item->id,
                'asset_account_id'   => $request->input('asset_account_id'),
                'revenue_account_id' => $item->type === 'inventory'
                    ? $request->input('inventory_revenue_account_id')
                    : $request->input('service_revenue_account_id'),
                'cogs_account_id'    => $request->input('cogs_account_id'),
                'variance_account_id' => $request->input('variance_account_id'),
                'expense_account_id' => $request->input('expense_account_id'),
                'account_receivable_id' => $request->input('account_receivable_id'),
            ];

            // Kosongkan yang tidak relevan tergantung type agar bersih
            if ($item->type === 'inventory') {
                // expense tidak relevan untuk inventory (biarkan null)
                $accountsPayload['expense_account_id'] = null;
            } else { // service
                // inventory tidak butuh asset/cogs/variance (biarkan null)
                $accountsPayload['asset_account_id']    = null;
                $accountsPayload['cogs_account_id']     = null;
                $accountsPayload['variance_account_id'] = null;
            }

            ItemAccount::create($accountsPayload);

            // ===== 2h) Build (opsional): header + details =====
            $buildRows = collect($request->input('build', []))
                ->filter(function ($row) {
                    return !empty($row['item_id']) && !empty($row['quantity']);
                });

            if ($buildRows->isNotEmpty()) {
                // Default build header (silakan sesuaikan jika nanti ada di form)
                $build = ItemBuild::create([
                    'item_id'         => $item->id,
                    'build_quantity'  => 1,
                    'additional_costs' => null,
                    'cost_account'    => null,
                ]);

                foreach ($buildRows as $d) {
                    ItemBuildDetail::create([
                        'item_build_id' => $build->id,
                        'item_id'       => $d['item_id'],          // komponen
                        'unit'          => $d['unit'] ?? '',
                        'description'   => $d['description'] ?? null,
                        'quantity'      => (int) $d['quantity'],
                    ]);
                }
            }

            // ===== 2i) Taxes =====

            // Inventory taxes
            $selectedTaxIds = collect($request->input('taxes', []))->filter();
            $exemptTaxIds   = collect($request->input('tax_exempt', []))->filter();

            if ($selectedTaxIds->isNotEmpty()) {
                $taxes = SalesTaxes::whereIn('id', $selectedTaxIds)->get(['id', 'name']);
                foreach ($taxes as $t) {
                    ItemTaxes::create([
                        'item_id'   => $item->id,
                        'tax_name'  => $t->name,
                        'is_exempt' => $exemptTaxIds->contains($t->id),
                    ]);
                }
            }

            // Service taxes
            $selectedServiceTaxIds = collect($request->input('taxes_service', []))->filter();
            $exemptServiceTaxIds   = collect($request->input('tax_exempt_service', []))->filter();

            if ($selectedServiceTaxIds->isNotEmpty()) {
                $taxes = SalesTaxes::whereIn('id', $selectedServiceTaxIds)->get(['id', 'name']);
                foreach ($taxes as $t) {
                    ItemTaxes::create([
                        'item_id'   => $item->id,
                        'tax_name'  => $t->name,
                        'is_exempt' => $exemptServiceTaxIds->contains($t->id),
                    ]);
                }
            }




            DB::commit();

            return redirect()
                ->route('inventory.index')
                ->with('success', 'Item berhasil dibuat beserta seluruh konfigurasi terkait.');
        } catch (\Throwable $e) {
            DB::rollBack();

            // Hapus file yang terlanjur ter-upload bila gagal
            if (!empty($picturePath)) {
                Storage::disk('public')->delete($picturePath);
            }
            if (!empty($thumbPath)) {
                Storage::disk('public')->delete($thumbPath);
            }

            report($e);
            return back()->withInput()->with('error', 'Gagal menyimpan item: ' . $e->getMessage());
        }
    }
    public function edit($id)
    {
        // Eager load semua relasi yang dibutuhkan di form
        $item = Item::with([
            'quantities',
            'units',
            'prices',
            'vendors',
            'accounts',
            'builds',
            'builds.details',
            'taxes',
        ])->findOrFail($id);

        // Master data untuk dropdown & tabel
        $lokasiInventory    = LocationInventory::orderBy('kode_lokasi')->get();
        $vendors            = Vendors::orderBy('nama_vendors')->get();
        $accounts           = ChartOfAccount::orderBy('kode_akun')->get();
        $items              = Item::orderBy('item_description')->get(['id', 'item_description']); // untuk tab Build (dropdown item)
        $taxes              = SalesTaxes::orderBy('name')->get();
        $quantities = ItemQuantities::where('item_id', $item->id)->get();

        // 🔥 Ambil price list dari tabel master
        $priceListInventory = PriceListInventory::orderBy('description')->get();

        // Prefill harga yang sudah ada (key = price_list_name)
        $pricingPrefill = $item->prices->keyBy('price_list_name');

        // ===== Taxes: mapping balik ke ID =====
        $itemTaxNames       = $item->taxes->pluck('tax_name')->all();
        $itemTaxNamesExempt = $item->taxes->where('is_exempt', true)->pluck('tax_name')->all();

        $selectedTaxIds = $taxes->whereIn('name', $itemTaxNames)->pluck('id')->values()->all();
        $exemptTaxIds   = $taxes->whereIn('name', $itemTaxNamesExempt)->pluck('id')->values()->all();

        // ===== Quantities default (kalau kosong, untuk memudahkan form) =====
        $quantity = $item->quantities->first();

        // Kirim semua ke view edit
        return view('inventory.edit', [
            'item'               => $item,
            'quantity'           => $quantity,
            'lokasiInventory'    => $lokasiInventory,
            'vendors'            => $vendors,
            'accounts'           => $accounts,
            'items'              => $items,
            'taxes'              => $taxes,
            'priceListInventory' => $priceListInventory,
            'pricingPrefill'     => $pricingPrefill,
            'selectedTaxIds'     => $selectedTaxIds,
            'exemptTaxIds'       => $exemptTaxIds,
            'quantities' => $quantities
        ]);
    }

    public function update(Request $request, Item $inventory)

    {
        // dd($request->all());

        // 1) VALIDASI
        $rules = [
            'item_number' => ['required', 'string', 'max:100', Rule::unique('items', 'item_number')->ignore($inventory->id)],
            'item_description'   => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['inventory', 'service'])],

            // Quantities
            'location_id'           => ['nullable', 'integer', 'exists:location_inventories,id'],
            'on_hand_qty'           => ['nullable', 'integer'],
            'on_hand_value'         => ['nullable', 'numeric'],
            'pending_orders_qty'    => ['nullable', 'integer'],
            'pending_orders_value'  => ['nullable', 'numeric'],
            'purchase_order_qty'    => ['nullable', 'integer'],
            'sales_order_qty'       => ['nullable', 'integer'],
            'reorder_minimum'       => ['nullable', 'integer'],
            'reorder_to_order'      => ['nullable', 'integer'],

            // Units
            'stocking_unit'             => ['nullable', 'string', 'max:100'],
            'selling_same_as_stocking'  => ['nullable'],
            'selling_unit'              => ['nullable', 'string', 'max:100'],
            'selling_relationship'      => ['nullable', 'integer', 'min:0'],
            'buying_same_as_stocking'   => ['nullable'],
            'buying_unit'               => ['nullable', 'string', 'max:100'],
            'buying_relationship'       => ['nullable', 'integer', 'min:0'],

            // Pricing
            'pricing'               => ['nullable', 'array'],
            'pricing.*.name'        => ['nullable', 'string', 'max:255'],
            'pricing.*.price'       => ['nullable', 'numeric'],

            // Vendor
            'vendor_id'             => ['nullable', 'integer', 'exists:vendors,id'],

            // Accounts
            'asset_account_id'      => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'revenue_account_id'    => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'cogs_account_id'       => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'variance_account_id'   => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'expense_account_id'    => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'account_receivable_id'    => ['nullable', 'integer', 'exists:chart_of_accounts,id'],

            // Build
            'build'                 => ['nullable', 'array'],
            'build.*.item_id'       => ['nullable', 'integer', 'exists:items,id'],
            'build.*.unit'          => ['nullable', 'string', 'max:100'],
            'build.*.description'   => ['nullable', 'string', 'max:255'],
            'build.*.quantity'      => ['nullable', 'integer', 'min:0'],

            // Taxes
            'taxes'                 => ['nullable', 'array'],
            'taxes.*'               => ['integer', 'exists:sales_taxes,id'],
            'tax_exempt'            => ['nullable', 'array'],
            'tax_exempt.*'          => ['integer', 'exists:sales_taxes,id'],

            // Files
            'picture'   => ['nullable', 'file', 'image', 'max:2048'],
            'thumbnail' => ['nullable', 'file', 'image', 'max:1024'],

            // Long desc
            'long_description' => ['nullable', 'string'],
        ];

        // Wajib akun (kalau kamu mau enforce saat edit, bisa ganti nullable→required)
        if ($request->type === 'inventory') {
            // tetap nullable sesuai versi store terakhir kamu
        } else {
            // service
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // 2) FILES: ganti jika ada upload baru
            $picturePath = $inventory->picture_path;
            $thumbPath   = $inventory->thubmnail_path;

            if ($request->hasFile('picture')) {
                if ($picturePath) Storage::disk('public')->delete($picturePath);
                $picturePath = $request->file('picture')->store('items/pictures', 'public');
            }
            if ($request->hasFile('thumbnail')) {
                if ($thumbPath) Storage::disk('public')->delete($thumbPath);
                $thumbPath = $request->file('thumbnail')->store('items/thumbnails', 'public');
            }

            // 3) UPDATE ITEM (header)
            $inventory->update([
                'item_number'      => $validated['item_number'],
                'item_description' => $validated['item_description'],              // short desc
                'description'      => $request->input('long_description'),         // long desc
                'type'             => $validated['type'],
                'picture_path'     => $picturePath,
                'thubmnail_path'   => $thumbPath,
            ]);

            // 4) QUANTITIES: upsert satu baris/ lokasi
            $anyQuantityFilled = $request->filled([
                'location_id',
                'on_hand_qty',
                'on_hand_value',
                'pending_orders_qty',
                'pending_orders_value',
                'purchase_order_qty',
                'sales_order_qty',
                'reorder_minimum',
                'reorder_to_order'
            ]);

            if ($request->has('quantities')) {
                foreach ($request->quantities as $row) {
                    if (!empty($row['location_id'])) {
                        $qty = ItemQuantities::firstOrNew([
                            'item_id'     => $inventory->id,
                            'location_id' => $row['location_id'],
                        ]);

                        $qty->on_hand_qty          = (int) ($row['on_hand_qty'] ?? 0);
                        $qty->on_hand_value        = (float) ($row['on_hand_value'] ?? 0);
                        $qty->pending_orders_qty   = (int) ($row['pending_orders_qty'] ?? 0);
                        $qty->pending_orders_value = (float) ($row['pending_orders_value'] ?? 0);
                        $qty->purchase_order_qty   = (int) ($row['purchase_order_qty'] ?? 0);
                        $qty->sales_order_qty      = (int) ($row['sales_order_qty'] ?? 0);
                        $qty->reorder_minimum      = (int) ($row['reorder_minimum'] ?? 0);
                        $qty->reorder_to_order     = (int) ($row['reorder_to_order'] ?? 0);

                        $qty->save();
                    }
                }
            }

            // 5) UNITS: upsert satu baris
            $sellingSame = $request->has('selling_same_as_stocking');
            $buyingSame  = $request->has('buying_same_as_stocking');

            $unit = ItemUnit::firstOrNew(['item_id' => $inventory->id]);

            // Selling
            $unit->selling_same_as_stocking = $sellingSame;
            $unit->selling_unit             = $sellingSame
                ? $request->input('stocking_unit')
                : $request->input('selling_unit');
            $unit->selling_relationship     = $sellingSame
                ? 1 // kalau sama persis, relationship default = 1
                : $request->input('selling_relationship');

            // Buying
            $unit->buying_same_as_stocking = $buyingSame;
            $unit->buying_unit             = $buyingSame
                ? $request->input('stocking_unit')
                : $request->input('buying_unit');
            $unit->buying_relationship     = $buyingSame
                ? 1
                : $request->input('buying_relationship');

            // Stocking / Unit of Measure
            $unit->unit_of_measure = $request->input('unit_of_measure')
                ?: $request->input('stocking_unit')
                ?: null;

            $unit->save();


            // 6) PRICES: sync (hapus lama → insert baru)
            ItemPrice::where('item_id', $inventory->id)->delete();
            $pricing = collect($request->input('pricing', []))
                ->filter(fn($row) => !empty($row['name']) || isset($row['price']));
            foreach ($pricing as $row) {
                ItemPrice::create([
                    'item_id'         => $inventory->id,
                    'price_list_name' => $row['name'] ?? '',
                    'price'           => (float) ($row['price'] ?? 0),
                ]);
            }

            // ===== 7) VENDOR: upsert (satu vendor per item sesuai form kamu) =====
            $vendorId = $request->input('vendor_id_inventory') ?? $request->input('vendor_id_service');

            if ($vendorId) {
                $iv = ItemVendor::firstOrNew(['item_id' => $inventory->id]);
                $iv->vendor_id = $vendorId;
                // $iv->vendor_contact = $request->input('vendor_contact'); // kalau ada field
                $iv->save();
            } else {
                // jika user kosongkan vendor, hapus relasi vendor
                ItemVendor::where('item_id', $inventory->id)->delete();
            }

            // // Debugging
            // dd([
            //     'vendor_id_inventory' => $request->input('vendor_id_inventory'),
            //     'vendor_id_service'   => $request->input('vendor_id_service'),
            //     'vendorId dipakai'    => $vendorId,
            //     'record vendor (relasi hasOne)' => $inventory->vendor,
            //     'record vendor (relasi hasMany)' => $inventory->vendors,
            //     'record vendor (query manual)' => ItemVendor::where('item_id', $inventory->id)->first(),
            // ]);



            // 8) ACCOUNTS: upsert satu baris
            $accountsPayload = [
                'item_id'            => $inventory->id,
                'asset_account_id'   => $request->input('asset_account_id'),
                'revenue_account_id' => $inventory->type === 'inventory'
                    ? $request->input('inventory_revenue_account_id')
                    : $request->input('service_revenue_account_id'),
                'cogs_account_id'    => $request->input('cogs_account_id'),
                'variance_account_id' => $request->input('variance_account_id'),
                'expense_account_id' => $request->input('expense_account_id'),
                'account_receivable_id' => $request->input('account_receivable_id'),
            ];

            // Kosongkan field yang tidak relevan
            if ($inventory->type === 'inventory') {
                $accountsPayload['expense_account_id'] = null;
            } else { // service
                $accountsPayload['asset_account_id']    = null;
                $accountsPayload['cogs_account_id']     = null;
                $accountsPayload['variance_account_id'] = null;
            }

            // dd($request->input('revenue_account_id'));


            $acc = ItemAccount::firstOrNew(['item_id' => $inventory->id]);
            $acc->fill($accountsPayload)->save();

            // 9) BUILD: sync
            ItemBuild::where('item_id', $inventory->id)->each(function ($b) {
                // cascade akan hapus details, tapi untuk aman:
                ItemBuildDetail::where('item_build_id', $b->id)->delete();
                $b->delete();
            });

            $buildRows = collect($request->input('build', []))
                ->filter(fn($row) => !empty($row['item_id']) && !empty($row['quantity']));

            if ($buildRows->isNotEmpty()) {
                $build = ItemBuild::create([
                    'item_id'         => $inventory->id,
                    'build_quantity'  => 1,
                    'additional_costs' => null,
                    'cost_account'    => null,
                ]);

                foreach ($buildRows as $d) {
                    ItemBuildDetail::create([
                        'item_build_id' => $build->id,
                        'item_id'       => $d['item_id'],
                        'unit'          => $d['unit'] ?? '',
                        'description'   => $d['description'] ?? null,
                        'quantity'      => (int) $d['quantity'],
                    ]);
                }
            }

            // Hapus dulu semua pajak lama untuk item ini
            ItemTaxes::where('item_id', $inventory->id)->delete();

            // Inventory taxes
            $selectedTaxIds = collect($request->input('taxes', []))->filter();
            $exemptTaxIds   = collect($request->input('tax_exempt', []))->filter();

            if ($selectedTaxIds->isNotEmpty()) {
                $taxes = SalesTaxes::whereIn('id', $selectedTaxIds)->get(['id', 'name']);
                foreach ($taxes as $t) {
                    ItemTaxes::create([
                        'item_id'   => $inventory->id,
                        'tax_name'  => $t->name,
                        'is_exempt' => $exemptTaxIds->contains($t->id),
                    ]);
                }
            }

            // Service taxes
            $selectedServiceTaxIds = collect($request->input('taxes_service', []))->filter();
            $exemptServiceTaxIds   = collect($request->input('tax_exempt_service', []))->filter();

            if ($selectedServiceTaxIds->isNotEmpty()) {
                $taxes = SalesTaxes::whereIn('id', $selectedServiceTaxIds)->get(['id', 'name']);
                foreach ($taxes as $t) {
                    ItemTaxes::create([
                        'item_id'   => $inventory->id,
                        'tax_name'  => $t->name,
                        'is_exempt' => $exemptServiceTaxIds->contains($t->id),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('inventory.index')->with('success', 'Item berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'Gagal update item: ' . $e->getMessage());
        }
    }
    public function show($id)
    {
        // Ambil item + relasi lengkap
        $item = Item::with([
            'quantities',
            'units',
            'prices',
            'vendors.vendor',
            'accounts',
            'builds.details',
            'taxes',
        ])->findOrFail($id);

        // Ambil quantity pertama (kalau ada)
        $quantity = $item->quantities->first();

        // Kirim ke view
        return view('inventory.show', [
            'item'     => $item,
            'quantity' => $quantity,
        ]);
    }


    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $item = Item::with(['assemblyComponents', 'transferInventory', 'buildofbomComponents'])->findOrFail($id);

                // Validasi relasi
                if ($item->assemblyComponents()->exists()) {
                    throw new \Exception("Item ini sudah digunakan dalam Assembly Component, tidak bisa dihapus.");
                }
                if ($item->transferInventory()->exists()) {
                    throw new \Exception("Item ini sudah digunakan dalam Transfer Inventory Order, tidak bisa dihapus.");
                }
                if ($item->buildofbomComponents()->exists()) {
                    throw new \Exception("Item ini sudah digunakan dalam Build Of Bom, tidak bisa dihapus.");
                }

                // Hapus file gambar (kalau ada)
                if ($item->picture_path) {
                    Storage::disk('public')->delete($item->picture_path);
                }
                if ($item->thumbnail_path) {
                    Storage::disk('public')->delete($item->thumbnail_path);
                }

                // Hapus relasi satu per satu
                ItemQuantities::where('item_id', $item->id)->delete();
                ItemUnit::where('item_id', $item->id)->delete();
                ItemPrice::where('item_id', $item->id)->delete();
                ItemVendor::where('item_id', $item->id)->delete();
                ItemAccount::where('item_id', $item->id)->delete();
                ItemTaxes::where('item_id', $item->id)->delete();

                // Hapus build dan detailnya
                $builds = ItemBuild::where('item_id', $item->id)->get();
                foreach ($builds as $build) {
                    ItemBuildDetail::where('item_build_id', $build->id)->delete();
                    $build->delete();
                }

                // Terakhir, hapus item
                $item->delete();
            });

            return redirect()
                ->route('inventory.index')
                ->with('success', 'Item berhasil dihapus beserta seluruh relasinya.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal menghapus item: ' . $e->getMessage());
        }
    }
}

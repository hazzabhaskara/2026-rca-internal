<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use App\Item;
use App\ItemBuild;
use App\itemCategory;
use App\PriceListInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::with(['category', 'account'])->latest()->get();
        $data = itemCategory::get()
            ->pluck('nama_kategori')
            ->filter()
            ->unique()
            ->values();

        // $items = Item::all();
        return view('items.index', compact('items', 'data'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = chartOfAccount::all();
        $priceListInventory = PriceListInventory::all();
        $categories = itemCategory::where('status', 1)->orderBy('nama_kategori')->get();
        return view('items.create', compact('accounts', 'categories', 'priceListInventory'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_number' => 'required|string|unique:items,item_number',
            'item_name' => 'required|string|max:255',
            'item_description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'base_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'category_id' => 'nullable|exists:item_categories,id',
            'brand' => 'nullable|string|max:100',
            'stock_quantity' => 'nullable|numeric',
            'purchase_price' => 'nullable|numeric',
            'is_active' => 'boolean',
            'image'     => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);
        //upload image
        $image = $request->file('image');
        $imageName = null;

        if ($image) {
            $image->storeAs('public/items', $image->hashName());
            $imageName = $image->hashName();
        }

        // Tambahkan nama file gambar ke data validasi
        $validated['image'] = $imageName;
        // Simpan barcode berdasarkan item_number
        $validated['barcode'] = $request->item_number;

        Item::create($validated);

        return redirect()->route('items.index')->with('success', 'Item berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $data = Item::findOrFail($id);

        return view('items.show', compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = Item::findOrFail($id);
        $accounts = chartOfAccount::all();
        $categories = ItemCategory::all();

        return view('items.edit', compact('item', 'accounts', 'categories'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'item_number' => 'required|string|unique:items,item_number,' . $id,
            'item_name' => 'required|string|max:255',
            'item_description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'base_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'category_id' => 'nullable|exists:item_categories,id',
            'brand' => 'nullable|string|max:100',
            'stock_quantity' => 'nullable|numeric',
            'purchase_price' => 'nullable|numeric',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        // Jika ada file image baru di-upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $image->hashName();
            $image->storeAs('public/items', $imageName);
            $validated['image'] = $imageName;
        }

        // Barcode update berdasarkan item_number
        $validated['barcode'] = $validated['item_number'];

        $item->update($validated);

        return redirect()->route('items.index')->with('success', 'Item berhasil diperbarui.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        // Hapus gambar jika ada
        if ($item->image) {
            Storage::delete('public/items/' . $item->image);
        }

        // Hapus item dari database
        $item->delete();

        return redirect()->route('items.index')->with('success', 'Item berhasil dihapus.');
    }
    public function search(Request $request)
    {
        $context    = $request->get('context', 'sales');
        $locationId = $request->get('location_id');

        $items = Item::with([
            'units',
            'accounts',
            'taxes',
            'accounts.assetAccount',
            'accounts.expenseAccount',
            'accounts.revenueAccount',
            'accounts.cogsAccount',
            'quantities' => function ($q) use ($locationId) {
                if ($locationId) {
                    $q->where('location_id', $locationId);
                }
            }
        ])
            ->where(function ($q) use ($request) {
                $q->where('item_description', 'like', '%' . $request->q . '%')
                    ->orWhere('item_number', 'like', '%' . $request->q . '%');
            })
            ->when($locationId, function ($q) use ($locationId) {
                $q->where(function ($sub) use ($locationId) {
                    $sub->where('type', 'service') // ✅ service tidak ikut filter lokasi
                        ->orWhere(function ($sub2) use ($locationId) {
                            $sub2->where('type', 'inventory')
                                ->whereHas('quantities', fn($q2) => $q2->where('location_id', $locationId));
                        });
                });
            })
            ->get();


        foreach ($items as $it) {
            Log::info('DEBUG Item Account Info', [
                'id' => $it->id,
                'item_number' => $it->item_number,
                'context' => $context,
                'asset_code' => optional($it->accounts->assetAccount)->kode_akun,
                'cogs_code' => optional($it->accounts->cogsAccount)->kode_akun,
                'revenue_code' => optional($it->accounts->revenueAccount)->kode_akun,
                'account_code' => optional($it->accounts->assetAccount)->kode_akun
                    ?? optional($it->accounts->expenseAccount)->kode_akun
                    ?? optional($it->accounts->revenueAccount)->kode_akun
                    ?? optional($it->accounts->cogsAccount)->kode_akun,
            ]);
        }

        return response()->json($items->map(function ($item) use ($context) {
            switch ($context) {
                case 'purchase':
                    $accountId   = $item->accounts->asset_account_id ?? $item->accounts->expense_account_id ?? null;
                    $accountName = optional($item->accounts->assetAccount)->nama_akun
                        ?? optional($item->accounts->expenseAccount)->nama_akun
                        ?? '-';
                    $accountKode = optional($item->accounts->assetAccount)->kode_akun
                        ?? optional($item->accounts->expenseAccount)->kode_akun ?? '-';
                    $unit = $item->units->buying_unit ?? '-';
                    break;

                case 'sales':
                    $accountId   = $item->accounts->revenue_account_id ?? null;
                    $accountName = optional($item->accounts->revenueAccount)->nama_akun ?? '-';
                    $accountKode = optional($item->accounts->revenueAccount)->kode_akun ?? '-';
                    $unit = $item->units->selling_unit ?? '-';
                    break;

                default:
                    $accountId   = $item->accounts->cogs_account_id ?? null;
                    $accountName = optional($item->accounts->cogsAccount)->nama_akun ?? '-';
                    $accountKode = optional($item->accounts->cogsAccount)->kode_akun ?? '-';
                    $unit = $item->units->selling_unit ?? '-';
                    break;
            }

            // hitung dari lokasi yg difilter
            $onHandQty   = $item->quantities->sum('on_hand_qty');
            $onHandValue = $item->quantities->sum('on_hand_value');
            $unitCost    = $onHandQty > 0 ? $onHandValue / $onHandQty : 0;


            // Tambahkan ini sebelum return



            return [
                'id'                 => $item->id,
                'item_number'        => $item->item_number,
                'item_description'   => $item->item_description,
                'on_hand_qty'        => $onHandQty,
                'unit'               => $unit,
                'tax_rate'           => $item->taxes->first()->rate ?? 0,
                'account_id'         => $accountId,
                'account_name'       => $accountName,
                'account_code' => $accountKode,
                'type'               => $item->type,
                'unit_cost'          => $unitCost,
                'cogs_account_name'  => optional($item->accounts->cogsAccount)->nama_akun ?? 'COGS',
                'cogs_account_code'  => optional($item->accounts->cogsAccount)->kode_akun ?? '',
                'asset_account_name' => optional($item->accounts->assetAccount)->nama_akun ?? 'Inventory',
                'asset_account_code' => optional($item->accounts->assetAccount)->kode_akun ?? '',
            ];
        }));
    }
    public function info($id, Request $request)
    {
        $locationId = $request->get('location_id');

        $item = Item::with(['quantities' => function ($q) use ($locationId) {
            if ($locationId) {
                $q->where('location_id', $locationId);
            }
        }, 'units'])->findOrFail($id);

        $quantity = $item->quantities->first();

        $onHandQty   = $quantity->on_hand_qty ?? 0;
        $onHandValue = $quantity->on_hand_value ?? 0;

        // hitung unit cost
        $unitCost = $onHandQty > 0 ? $onHandValue / $onHandQty : 0;

        return response()->json([
            'description'   => $item->item_description,
            'unit'          => optional($item->units)->unit_of_measure ?? '-',
            'current_stock' => $onHandQty,
            'unit_cost'     => round($unitCost, 2),
        ]);
    }

    public function byLocation($locationId)
    {
        $items = Item::whereHas('quantities', function ($q) use ($locationId) {
            $q->where('location_id', $locationId);
        })->get(['id', 'item_description']);

        return response()->json($items);
    }

    public function getAccounts($id)
    {
        $itemAccount = \App\ItemAccount::with([
            'assetAccount',
            'revenueAccount',
            'cogsAccount',
            'varianceAccount',
            'expenseAccount'
        ])->where('item_id', $id)->first();

        if (!$itemAccount) {
            return response()->json([
                'asset_account'    => null,
                'revenue_account'  => null,
                'cogs_account'     => null,
                'variance_account' => null,
                'expense_account'  => null,
            ]);
        }

        return response()->json([
            'asset_account' => $itemAccount->assetAccount ? [
                'id'   => $itemAccount->assetAccount->id,
                'kode' => $itemAccount->assetAccount->kode_akun,
                'nama' => $itemAccount->assetAccount->nama_akun,
            ] : null,
            'revenue_account' => $itemAccount->revenueAccount ? [
                'id'   => $itemAccount->revenueAccount->id,
                'kode' => $itemAccount->revenueAccount->kode_akun,
                'nama' => $itemAccount->revenueAccount->nama_akun,
            ] : null,
            'cogs_account' => $itemAccount->cogsAccount ? [
                'id'   => $itemAccount->cogsAccount->id,
                'kode' => $itemAccount->cogsAccount->kode_akun,
                'nama' => $itemAccount->cogsAccount->nama_akun,
            ] : null,
            'variance_account' => $itemAccount->varianceAccount ? [
                'id'   => $itemAccount->varianceAccount->id,
                'kode' => $itemAccount->varianceAccount->kode_akun,
                'nama' => $itemAccount->varianceAccount->nama_akun,
            ] : null,
            'expense_account' => $itemAccount->expenseAccount ? [
                'id'   => $itemAccount->expenseAccount->id,
                'kode' => $itemAccount->expenseAccount->kode_akun,
                'nama' => $itemAccount->expenseAccount->nama_akun,
            ] : null,
        ]);
    }
    public function getBom(Request $request, $id)
    {
        $locationId = $request->get('location_id'); // ambil dari query string

        $item = \App\Item::with([
            'builds.details.item.accounts.assetAccount',
            'builds.details.item.units',
            'builds.details.item.quantities' => function ($q) use ($locationId) {
                if ($locationId) {
                    $q->where('location_id', $locationId);
                }
                $q->orderByDesc('id');
            },
        ])->findOrFail($id);

        $build = $item->builds->first();
        if (!$build) {
            return response()->json([
                'item_id' => $item->id,
                'details' => [],
            ]);
        }

        $details = $build->details->map(function ($d) use ($locationId) {
            $component = $d->item;

            $account = ($component && $component->accounts && $component->accounts->assetAccount)
                ? [
                    'id'   => $component->accounts->assetAccount->id,
                    'kode' => $component->accounts->assetAccount->kode_akun,
                    'nama' => $component->accounts->assetAccount->nama_akun,
                ]
                : null;

            // filter quantities per lokasi
            $quantities = $component->quantities;
            if ($locationId) {
                $quantities = $quantities->where('location_id', $locationId);
            }

            $layer = $quantities->first(); // karena sudah diurutkan desc

            $unitCost = 0.0;
            if ($layer) {
                $totalValue = floatval($layer->on_hand_value ?? 0);
                $totalQty   = floatval($layer->on_hand_qty ?? 0);
                $unitCost   = $totalQty > 0 ? ($totalValue / $totalQty) : $totalValue;
            }

            $available = $quantities->sum('on_hand_qty') ?? 0;

            return [
                'component_id' => $component->id,
                'description'  => $d->description ?? $component->item_description,
                'unit'         => $d->unit ?? optional($component->units)->unit_of_measure ?? '-',
                'quantity'     => $d->quantity,
                'unit_cost'    => $unitCost,
                'amount'       => $unitCost * $d->quantity,
                'available'    => $available,
                'account'      => $account,
            ];
        });

        return response()->json([
            'item_id' => $item->id,
            'details' => $details,
        ]);
    }
}

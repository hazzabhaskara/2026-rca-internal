<?php

namespace App\Http\Controllers;

use App\OptionsInventory;
use Illuminate\Http\Request;

class OptionsInventoryController extends Controller
{
    //
    public function index()
    {
        $data = OptionsInventory::all();
        return view('options_inventory.index', compact('data'));
    }
    public function create()
    {
        return view('options_inventory.create');
    }
    public function store(Request $request)
    {
        // ✅ Validasi input
        $validated = $request->validate([
            'costing_method'        => 'required|in:average,fifo',
            'profit_eval_method'    => 'required|in:markup,margin',
            'sort_inventory_service' => 'required|in:number,description',
            'allow_below_zero'      => 'nullable|in:1',
        ]);

        // ✅ Simpan data
        $option = OptionsInventory::create([
            'costing_method'         => $validated['costing_method'],
            'profit_eval_method'     => $validated['profit_eval_method'],
            'sort_inventory_service' => $validated['sort_inventory_service'],
            'allow_below_zero'       => $request->has('allow_below_zero') ? 1 : 0,
        ]);

        // ✅ Redirect dengan pesan sukses
        return redirect()
            ->route('options_inventory.edit', $option->id)
            ->with('success', 'Inventory options berhasil disimpan!');
    }
    public function edit($id)
    {
        $option = OptionsInventory::findOrFail($id);
        return view('options_inventory.edit', compact('option'));
    }

    public function update(Request $request, $id)
    {
        // ✅ Validasi input
        $validated = $request->validate([
            'costing_method'        => 'required|in:average,fifo',
            'profit_eval_method'    => 'required|in:markup,margin',
            'sort_inventory_service' => 'required|in:number,description',
            'allow_below_zero'      => 'nullable|in:1',
        ]);

        // ✅ Update data
        $option = OptionsInventory::findOrFail($id);
        $option->update([
            'costing_method'         => $validated['costing_method'],
            'profit_eval_method'     => $validated['profit_eval_method'],
            'sort_inventory_service' => $validated['sort_inventory_service'],
            'allow_below_zero'       => $request->has('allow_below_zero') ? 1 : 0,
        ]);

        return redirect()
            ->route('options_inventory.edit', $id)
            ->with('success', 'Inventory options berhasil diperbarui!');
    }
}

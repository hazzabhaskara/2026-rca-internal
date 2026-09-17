<?php

namespace App\Http\Controllers;

use App\PriceListInventory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PriceListInventoryController extends Controller
{
    //
    public function index()
    {
        $data = PriceListInventory::all();
        return view('price_list_inventory.index', compact('data'));
    }
    public function create()
    {
        return view('price_list_inventory.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Hapus 'required' karena bisa auto-generate
            'description' => 'nullable|string|max:255',
            'status' => 'nullable|string'
        ]);


        PriceListInventory::create($validated);

        return redirect()->route('price_list_inventory.index')->with('success', 'Price List Inventory created successfully.');
    }
    public function show($id)
    {
        $data = PriceListInventory::findOrFail($id);
        return view('price_list_inventory.show', compact('data'));
    }
    public function destroy($id): RedirectResponse
    {
        //get post by ID
        $price_list = PriceListInventory::findOrFail($id);


        //delete post
        $price_list->delete();

        //redirect to index
        return redirect()->route('price_list_inventory.index')->with('success', 'Price List Inventory deleted successfully.');
    }
    public function edit($id)
    {
        $data = PriceListInventory::findOrFail($id);
        return view('price_list_inventory.edit', compact('data'));
    }
    public function update(Request $request, $id)
    {
        $data = PriceListInventory::findOrFail($id);
        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
            'status' => 'nullable|string'
        ]);

        $data->update($validated);

        return redirect()->route('price_list_inventory.index')->with('success', 'Price List Inventory updated successfully.');
    }
}

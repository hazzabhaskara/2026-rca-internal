<?php

namespace App\Http\Controllers;

use App\LocationInventory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LokasiInventoryController extends Controller
{
    //
    public function index()
    {
        $data = LocationInventory::all();
        return view('lokasi_inventory.index', compact('data'));
    }
    public function create()
    {
        return view('lokasi_inventory.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Hapus 'required' karena bisa auto-generate
            'kode_lokasi' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
            'status' => 'nullable|string'
        ]);


        LocationInventory::create($validated);

        return redirect()->route('lokasi_inventory.index')->with('success', 'Location Inventory created successfully.');
    }
    public function show($id)
    {
        $data = LocationInventory::findOrFail($id);
        return view('lokasi_inventory.show', compact('data'));
    }
    public function edit($id)
    {
        $data = LocationInventory::findOrFail($id);
        return view('lokasi_inventory.edit', compact('data'));
    }
    public function update(Request $request, $id)
    {
        $data = LocationInventory::findOrFail($id);
        $validated = $request->validate([
            'kode_lokasi' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
            'status' => 'nullable|string'
        ]);

        $data->update($validated);

        return redirect()->route('lokasi_inventory.index')->with('success', 'Location Inventory updated successfully.');
    }
    public function destroy($id): RedirectResponse
    {
        //get post by ID
        $lokasi = LocationInventory::findOrFail($id);


        //delete post
        $lokasi->delete();

        //redirect to index
        return redirect()->route('lokasi_inventory.index')->with('success', 'Location Inventory deleted successfully.');
    }
}

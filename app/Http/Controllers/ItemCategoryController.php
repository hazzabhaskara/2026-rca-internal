<?php

namespace App\Http\Controllers;

use App\ItemCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    //
    public function index()
    {
        $data = itemCategory::latest()->paginate(10);
        return view('item_category.index', compact('data'));
    }
    public function create()
    {
        return view('item_category.create');
    }
    private function generateKodeJabatan()
    {
        $last = \App\itemCategory::orderBy('kode_kategori', 'desc')->first();

        if ($last && preg_match('/CAT-(\d+)/', $last->kode_kategori, $matches)) {
            $number = (int) $matches[1] + 1;
        } else {
            $number = 1;
        }

        return 'CAT-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Hapus 'required' karena bisa auto-generate
            'kode_kategori' => 'nullable|string|max:255',
            'nama_kategori' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
            'status' => 'required|boolean',

        ]);

        // Kalau kosong (auto generate), buat kode otomatis
        if (empty($validated['kode_kategori'])) {
            $validated['kode_kategori'] = $this->generateKodeJabatan();
        }

        itemCategory::create($validated);

        return redirect()->route('item_category.index')->with('success', 'Data created successfully.');
    }
    public function show($id)
    {
        $data = itemCategory::findOrFail($id);

        return view('item_category.show', compact('data'));
    }
    public function edit(string $id): View
    {
        //get post by ID
        $data = itemCategory::findOrFail($id);

        return view('item_category.edit', compact('data'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            // Hapus 'required' karena bisa auto-generate
            'kode_kategori' => 'nullable|string|max:255',
            'nama_kategori' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ]);

        $data = itemCategory::findOrFail($id);

        $data->update($request->all());

        return redirect()->route('item_category.index')->with('success', 'Data berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $data = itemCategory::findOrFail($id);

        $data->delete();

        return redirect()->route('item_category.index')->with('success', ' berhasil dihapus.');
    }
}

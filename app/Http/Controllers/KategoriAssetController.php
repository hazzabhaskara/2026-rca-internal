<?php

namespace App\Http\Controllers;

use App\KategoriAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KategoriAssetController extends Controller
{
    //
    public function index()
    {
        $data = KategoriAsset::latest()->paginate(10);
        return view('kategori_asset.index', compact('data'));
    }
    public function create()
    {
        return view('kategori_asset.create');
    }
    private function generateKodeAsset()
    {
        $last = \App\KategoriAsset::orderBy('kode_kategori', 'desc')->first();

        if ($last && preg_match('/KAT-(\d+)/', $last->kode_kategori, $matches)) {
            $number = (int) $matches[1] + 1;
        } else {
            $number = 1;
        }

        return 'KAT-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Hapus 'required' karena bisa auto-generate
            'kode_kategori' => 'nullable|string|max:255',
            'nama_kategori' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:255'
        ]);

        // Kalau kosong (auto generate), buat kode otomatis
        if (empty($validated['kode_kategori'])) {
            $validated['kode_kategori'] = $this->generateKodeAsset();
        }

        KategoriAsset::create($validated);

        return redirect()->route('kategori_asset.index')->with('success', 'Category Asset created successfully.');
    }
    public function show($kode_kategori)
    {
        $data = KategoriAsset::where('kode_kategori', $kode_kategori)->firstOrFail();
        return view('kategori_asset.show', compact('data'));
    }
    public function edit($kode_kategori)
    {
        $data = KategoriAsset::where('kode_kategori', $kode_kategori)->firstOrFail();
        return view('kategori_asset.edit', compact('data'));
    }
    public function update(Request $request, $kode_kategori)
    {
        $data = KategoriAsset::where('kode_kategori', $kode_kategori)->firstOrFail();
        $validated = $request->validate([
            'kode_kategori' => 'nullable|string|max:255',
            'nama_kategori' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:255'
        ]);

        $data->update($validated);

        return redirect()->route('kategori_asset.index')->with('success', 'Category Asset updated successfully.');
    }
    public function destroy($kode_kategori): RedirectResponse
    {
        //get post by ID
        $kategori = KategoriAsset::where('kode_kategori', $kode_kategori)->firstOrFail();

        //delete post
        $kategori->delete();

        //redirect to index
        return redirect()->route('kategori_asset.index')->with('success', 'Category Asset deleted successfully.');
    }
}

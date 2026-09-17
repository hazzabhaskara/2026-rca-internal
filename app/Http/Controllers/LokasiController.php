<?php

namespace App\Http\Controllers;

use App\Lokasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    //
    public function index()
    {
        $data = lokasi::latest()->paginate(10);
        return view('lokasi.index', compact('data'));
    }
    public function create()
    {
        return view('lokasi.create');
    }
    private function generateKodeAsset()
    {
        $last = \App\Lokasi::orderBy('kode_lokasi', 'desc')->first();

        if ($last && preg_match('/LOK-(\d+)/', $last->kode_lokasi, $matches)) {
            $number = (int) $matches[1] + 1;
        } else {
            $number = 1;
        }

        return 'LOK-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Hapus 'required' karena bisa auto-generate
            'kode_lokasi' => 'nullable|string|max:255',
            'nama_lokasi' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:255'
        ]);

        // Kalau kosong (auto generate), buat kode otomatis
        if (empty($validated['kode_lokasi'])) {
            $validated['kode_lokasi'] = $this->generateKodeAsset();
        }

        lokasi::create($validated);

        return redirect()->route('lokasi.index')->with('success', 'Location Asset created successfully.');
    }
    public function show($kode_lokasi)
    {
        $data = lokasi::where('kode_lokasi', $kode_lokasi)->firstOrFail();
        return view('lokasi.show', compact('data'));
    }
    public function edit($kode_lokasi)
    {
        $data = lokasi::where('kode_lokasi', $kode_lokasi)->firstOrFail();
        return view('lokasi.edit', compact('data'));
    }
    public function update(Request $request, $kode_lokasi)
    {
        $data = lokasi::where('kode_lokasi', $kode_lokasi)->firstOrFail();
        $validated = $request->validate([
            'kode_lokasi' => 'nullable|string|max:255',
            'nama_lokasi' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:255'
        ]);

        $data->update($validated);

        return redirect()->route('lokasi.index')->with('success', 'Location Asset updated successfully.');
    }
    public function destroy($id): RedirectResponse
    {
        //get post by ID
        $lokasi = lokasi::findOrFail($id);


        //delete post
        $lokasi->delete();

        //redirect to index
        return redirect()->route('lokasi.index')->with('success', 'Location Asset deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\MasaManfaat;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MasaManfaatController extends Controller
{
    //
    public function index()
    {
        $data = MasaManfaat::latest()->paginate(10);
        return view('masa_manfaat.index', compact('data'));
    }
    public function create()
    {
        return view('masa_manfaat.create');
    }
    public function store(Request $request)
    {
        // dd($request->all()); // Debug dulu
        $validated = $request->validate([
            'jenis' => 'required|string|max:255',
            'kelompok_harta' => 'required|string|max:255',
            'golongan.*.nama' => 'required|string|max:255',
            'golongan.*.masa' => 'required|integer',
            'golongan.*.tarif' => 'required|numeric'
        ]);

        foreach ($request->golongan as $item) {
            MasaManfaat::create([
                'jenis' => $request->jenis,
                'kelompok_harta' => $request->kelompok_harta,
                'nama_golongan' => $item['nama'],
                'masa_tahun' => $item['masa'],
                'tarif_penyusutan' => $item['tarif'],
            ]);
        }

        return redirect()->route('masa_manfaat.index')->with('success', 'Masa Manfaat berhasil disimpan.');
    }

    public function show($id)
    {
        $data = MasaManfaat::findOrFail($id);

        return view('masa_manfaat.show', compact('data'));
    }
    public function edit(string $id): View
    {
        //get post by ID
        $data = MasaManfaat::findOrFail($id);

        return view('masa_manfaat.edit', compact('data'));
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'masa_tahun' => 'nullable|string|max:255',
            'masa_bulan' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255'
        ]);

        $data = MasaManfaat::findOrFail($id);

        $data->update($validated);

        return redirect()->route('masa_manfaat.index')->with('success', ' Data berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $data = MasaManfaat::findOrFail($id);

        $data->delete();

        return redirect()->route('masa_manfaat.index')->with('success', ' Data Berhasil Dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\KategoriAsset;
use App\Lokasi;
use App\MasaManfaat;
use App\MetodePenyusutan;
use App\TangibleAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TangibleAssetController extends Controller
{
    //
    public function index()
    {
        $data = TangibleAsset::with('kategori', 'lokasi', 'golongan', 'metode_penyusutan')->orderBy('kode_asset')->paginate(10);
        return view('tangible_asset.index', compact('data'));
    }
    public function create()
    {
        $kategori = KategoriAsset::all();
        $lokasi = lokasi::all();
        $golongan = MasaManfaat::where('jenis', 'Tangible Asset')->get();
        $metode_penyusutan = MetodePenyusutan::all();
        return view('tangible_asset.create', compact('kategori', 'lokasi', 'golongan', 'metode_penyusutan'));
    }
    public function store(Request $request)
    {

        $validated = $request->validate([
            'kode_asset'         => 'nullable|string|unique:tangible_assets,kode_asset',
            'nama_asset'         => 'required|string|max:255',
            'kategori_id'        => 'required|exists:kategori_assets,id',
            'components'         => 'required|string|max:255',
            'capacity'           => 'required|string|max:255',
            'merk'               => 'required|string|max:255',
            'type'               => 'required|string|max:255',
            'lokasi_id'          => 'required|exists:lokasis,id',
            'golongan_id'        => 'required|exists:masa_manfaats,id',
            'dalam_tahun'        => 'required|numeric',
            'metode_penyusutan_id'  => 'required|exists:metode_penyusutans,id',
            'tarif_penyusutan'   => 'required|numeric',
        ]);

        // dd($validated);

        if (empty($validated['kode_asset'])) {
            $validated['kode_asset'] = 'AST-' . now()->format('Ymd') . '-' . str_pad(TangibleAsset::count() + 1, 4, '0', STR_PAD_LEFT);
        }

        // Ambil nama lokasi dari ID
        $lokasi = \App\Lokasi::find($validated['lokasi_id']);
        $lokasiNama = $lokasi ? $lokasi->nama_lokasi : '';

        // Gabungkan full name
        $validated['asset_full_name'] = $validated['nama_asset'] . ' - ' .
            $validated['components'] . ' - ' .
            $validated['merk'] . ' - ' .
            $validated['type'] . ' - ' .
            $lokasiNama;

        TangibleAsset::create($validated);


        return redirect()->route('tangible_asset.index')->with('success', 'Asset berhasil ditambahkan.');
    }
    public function edit($id)
    {
        $asset = TangibleAsset::findOrFail($id);
        $kategori = KategoriAsset::all();
        $lokasi = Lokasi::all();
        $golongan = MasaManfaat::all();
        $metode_penyusutan = MetodePenyusutan::all();

        return view('tangible_asset.edit', compact('asset', 'kategori', 'lokasi', 'golongan', 'metode_penyusutan'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kode_asset' => 'required|string|max:255',
            'nama_asset' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori_assets,id',
            'components' => 'nullable|string|max:255',
            'capacity' => 'nullable|string|max:255',
            'merk' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'lokasi_id' => 'required|exists:lokasis,id',
            'golongan_id' => 'required|exists:masa_manfaats,id',
            'dalam_tahun' => 'required|numeric',
            'metode_penyusutan_id' => 'required|exists:metode_penyusutans,id',
            'tarif_penyusutan' => 'required|numeric',
        ]);

        // Buat asset_full_name
        $validated['asset_full_name'] = implode(' ', [
            $validated['nama_asset'],
            $validated['components'] ?? '',
            $validated['merk'] ?? '',
            $validated['type'] ?? '',
        ]);

        $asset = TangibleAsset::findOrFail($id);
        $asset->update($validated);

        return redirect()->route('tangible_asset.index')->with('success', 'Aset berhasil diperbarui.');
    }


    public function show($id)
    {
        $data = TangibleAsset::with(['kategori', 'lokasi', 'golongan', 'metode_penyusutan'])->findOrFail($id);

        return view('tangible_asset.show', compact('data'));
    }
    public function destroy($id): RedirectResponse
    {
        //get post by ID
        $tangible_asset = TangibleAsset::findOrFail($id);

        //delete image


        //delete post
        $tangible_asset->delete();

        //redirect to index
        return redirect()->route('tangible_asset.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}

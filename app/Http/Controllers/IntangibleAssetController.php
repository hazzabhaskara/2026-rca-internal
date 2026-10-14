<?php

namespace App\Http\Controllers;

use App\IntangibleAsset;
use App\KategoriAsset;
use App\Lokasi;
use App\MasaManfaat;
use App\MetodePenyusutan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IntangibleAssetController extends Controller
{
    public function index()
    {
        $data = IntangibleAsset::with('kategori', 'lokasi', 'golongan', 'metode_penyusutan')->orderBy('kode_asset')->paginate(10);
        return view('intangible_asset.index', compact('data'));
    }

    public function create()
    {
        $kategori = KategoriAsset::all();
        $lokasi = Lokasi::all();
        $golongan = MasaManfaat::where('jenis', 'intangible Asset')->get();
        $metode_penyusutan = MetodePenyusutan::all();
        return view('intangible_asset.create', compact('kategori', 'lokasi', 'golongan', 'metode_penyusutan'));
    }

    public function edit($id)
    {
        $asset = IntangibleAsset::findOrFail($id);
        $kategori = KategoriAsset::all();
        $lokasi = Lokasi::all();
        $golongan = MasaManfaat::where('jenis', 'intangible Asset')->get();
        $metode_penyusutan = MetodePenyusutan::all();

        return view('intangible_asset.edit', compact('asset', 'kategori', 'lokasi', 'golongan', 'metode_penyusutan'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kode_asset'           => 'nullable|string|unique:intangible_assets,kode_asset,' . $id,
            'nama_asset'           => 'required|string|max:255',
            'kategori_id'          => 'required|exists:kategori_assets,id',
            'tanggal_perolehan'    => 'nullable|date',
            'nilai_perolehan'      => 'nullable|numeric|min:0',
            'deskripsi'            => 'required|string|max:255',
            'brand'                => 'required|string|max:255',
            'lokasi_id'            => 'required|exists:lokasis,id',
            'golongan_id'          => 'required|exists:masa_manfaats,id',
            'dalam_tahun'          => 'required|numeric',
            'metode_penyusutan_id' => 'required|exists:metode_penyusutans,id',
            'tarif_amortisasi'     => 'required|numeric',
        ]);

        $lokasi = Lokasi::find($validated['lokasi_id']);
        $lokasiNama = $lokasi ? $lokasi->nama_lokasi : '';

        $validated['asset_full_name'] = implode(' ', [
            $validated['nama_asset'],
            $validated['deskripsi'] ?? '',
            $validated['brand'] ?? '',
            $lokasiNama
        ]);

        $asset = IntangibleAsset::findOrFail($id);
        $asset->update($validated);

        if (function_exists('logActivity')) {
            logActivity("Update Intangible Asset {$asset->kode_asset}");
        }

        return redirect()->route('intangible_asset.index')->with('success', 'Aset berhasil diperbarui.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_asset'           => 'nullable|string|unique:intangible_assets,kode_asset',
            'nama_asset'           => 'required|string|max:255',
            'kategori_id'          => 'required|exists:kategori_assets,id',
            'tanggal_perolehan'    => 'nullable|date',
            'nilai_perolehan'      => 'nullable|numeric|min:0',
            'deskripsi'            => 'required|string|max:255',
            'brand'                => 'required|string|max:255',
            'lokasi_id'            => 'required|exists:lokasis,id',
            'golongan_id'          => 'required|exists:masa_manfaats,id',
            'dalam_tahun'          => 'required|numeric',
            'metode_penyusutan_id' => 'required|exists:metode_penyusutans,id',
            'tarif_amortisasi'     => 'required|numeric',
        ]);

        if (empty($validated['kode_asset'])) {
            $validated['kode_asset'] = 'AST-' . now()->format('Ymd') . '-INT-' . str_pad(IntangibleAsset::count() + 1, 4, '0', STR_PAD_LEFT);
        }

        $lokasi = Lokasi::find($validated['lokasi_id']);
        $lokasiNama = $lokasi ? $lokasi->nama_lokasi : '';

        $validated['asset_full_name'] = $validated['nama_asset'] . ' - ' .
            $validated['deskripsi'] . ' - ' .
            $validated['brand'] . ' - ' .
            $lokasiNama;

        $asset = IntangibleAsset::create($validated);

        if (function_exists('logActivity')) {
            logActivity("Create Intangible Asset {$asset->kode_asset} ({$asset->nama_asset})");
        }

        return redirect()->route('intangible_asset.index')->with('success', 'Asset berhasil ditambahkan.');
    }

    public function show($id)
    {
        $data = IntangibleAsset::with(['kategori', 'lokasi', 'golongan', 'metode_penyusutan'])->findOrFail($id);
        return view('intangible_asset.show', compact('data'));
    }

    public function destroy($id): RedirectResponse
    {
        $intangible_asset = IntangibleAsset::findOrFail($id);
        $kode = $intangible_asset->kode_asset;
        $intangible_asset->delete();

        if (function_exists('logActivity')) {
            logActivity("Delete Intangible Asset {$kode}");
        }

        return redirect()->route('intangible_asset.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}

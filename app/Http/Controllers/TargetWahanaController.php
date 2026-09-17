<?php

namespace App\Http\Controllers;

use App\JenisHari;
use App\TargetWahana;
use App\UnitKerja;
use App\Wahana;
use Facade\FlareClient\View;
use Illuminate\Contracts\View\View as ViewView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as FacadesView;

class TargetWahanaController extends Controller
{
    //
    public function index()
    {
        $query = TargetWahana::with(['wahana', 'jenis_hari', 'unit'])
            ->orderBy('wahana_id', 'asc')
            ->orderBy('bulan', 'asc')
            ->orderBy('tahun', 'asc');

        // Filter Unit (dropdown berisi nama_unit)
        if ($unit = request('filter_tipe')) {
            $query->whereHas('unit', function ($q) use ($unit) {
                $q->where('nama_unit', $unit);
            });
        }

        // Filter Wahana (dropdown berisi nama_wahana)
        if ($whn = request('filter_wahana')) {
            $query->whereHas('wahana', function ($q) use ($whn) {
                $q->where('nama_wahana', $whn);
            });
            // Alternatif jika yang dikirim id: $query->where('wahana_id', $whn);
        }

        // Search (di kolom milik tabel wahana)
        if ($search = request('search')) {
            $query->whereHas('wahana', function ($q) use ($search) {
                $q->where('nama_wahana', 'like', "%{$search}%")
                    ->orWhere('kode_wahana', 'like', "%{$search}%");
            });
        }

        // Eksekusi query SEKALI di akhir
        $data = $query->get();

        // Sumber data untuk dropdown
        $unitkerja = UnitKerja::select('nama_unit')->distinct()->orderBy('nama_unit')->pluck('nama_unit');
        $wahana    = Wahana::select('nama_wahana')->distinct()->orderBy('nama_wahana')->pluck('nama_wahana');

        return view('target_wahana.index', compact('data', 'unitkerja', 'wahana'));
    }

    public function create()
    {
        $unit = UnitKerja::all();
        $jenis_hari = JenisHari::all();
        return view('target_wahana.create', compact('jenis_hari', 'unit'));
    }
    public function getWahanaByUnit($unitId)
    {
        $wahana = Wahana::where('unit_kerja_id', $unitId)->get();
        return response()->json($wahana);
    }

    public function store(Request $request)
    {
        // Bersihkan titik ribuan sebelum validasi
        $request->merge([

            'target_harian' => str_replace('.', '', $request->target_harian),
        ]);

        // Validasi setelah input dibersihkan
        $validated = $request->validate([
            'wahana_id' => 'required|exists:wahanas,id',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'jenis_hari_id' => 'required|exists:jenis_haris,id',
            'target_harian' => 'required|numeric',
            'bulan' => 'required|integer',
            'tahun' => 'required|integer',
            'keterangan' => 'nullable|string'
        ]);

        TargetWahana::create($validated);

        return redirect()->route('target_wahana.index')->with('success', 'Data Target Wahana berhasil ditambahkan');
    }
    public function destroy($id)
    {
        $target_wahana = TargetWahana::findOrFail($id);

        $target_wahana->delete();

        return redirect()->route('target_wahana.index')->with('success', ' Data berhasil dihapus.');
    }
    public function show($id)
    {
        $target_wahana = TargetWahana::findOrFail($id);

        return view('target_wahana.show', compact('target_wahana'));
    }

    public function edit($id)
    {
        //get post by ID
        $target_wahana = \App\TargetWahana::findOrFail($id);
        $unit = UnitKerja::all();
        $jenis_hari = JenisHari::all();

        return view('target_wahana.edit', compact('target_wahana', 'jenis_hari', 'unit'));
    }
    public function update(Request $request, TargetWahana $target_wahana)
    {
        $request->merge([
            'target_harian' => str_replace('.', '', $request->target_harian),
        ]);

        $validated = $request->validate([
            'wahana_id' => 'required|exists:wahanas,id',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'jenis_hari_id' => 'required|exists:jenis_haris,id',
            'target_harian' => 'required|numeric',
            'bulan' => 'required|integer',
            'tahun' => 'required|integer',
            'keterangan' => 'nullable|string'
        ]);

        $target_wahana->update($validated);

        return redirect()->route('target_wahana.index')->with('success', 'Data Target Wahana berhasil diperbarui');
    }
}

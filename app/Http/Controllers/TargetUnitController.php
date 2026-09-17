<?php

namespace App\Http\Controllers;

use App\KomponenPenghasilan;
use App\LevelKaryawan;
use App\Targetunit;
use App\UnitKerja;
use Illuminate\Http\Request;

class TargetUnitController extends Controller
{
    //
    public function index()
    {
        $query = TargetUnit::with(['unit', 'komponen', 'levelKaryawan'])
            ->join('unit_kerjas', 'targetunits.unit_kerja_id', '=', 'unit_kerjas.id')
            ->orderBy('unit_kerjas.nama_unit', 'asc')
            ->orderBy('targetunits.bulan', 'asc')
            ->orderBy('targetunits.tahun', 'asc')
            ->select('targetunits.*');

        if ($unit = request('filter_tipe')) {
            $query->where('unit_kerjas.nama_unit', $unit);
        }

        // Filter Level Karyawan
        if ($level = request('filter_level')) {
            $query->whereHas('levelKaryawan', function ($q) use ($level) {
                $q->where('nama_level', $level);
            });
        }
        $searchable = ['bulan', 'tahun'];

        if ($search = request('search')) {
            $query->where(function ($q) use ($search, $searchable) {
                foreach ($searchable as $col) {
                    $q->orWhere($col, 'like', "%{$search}%");
                }

                // tambahkan juga relasi
                $q->orWhereHas('unit', function ($q2) use ($search) {
                    $q2->where('nama_unit', 'like', "%{$search}%");
                });
                // tambahkan juga relasi
                $q->orWhereHas('komponen', function ($q3) use ($search) {
                    $q3->where('nama_komponen', 'like', "%{$search}%");
                });
                $q->orWhereHas('levelKaryawan', function ($q4) use ($search) {
                    $q4->where('nama_level', 'like', "%{$search}%");
                });
            });
        }


        // Eksekusi query sekali di akhir
        $data = $query->get();
        // Atau kalau mau paginasi:
        // $data = $query->paginate(20)->appends(request()->query());

        // Sumber data untuk dropdown
        $unitkerja = UnitKerja::select('nama_unit')->distinct()->orderBy('nama_unit')->pluck('nama_unit');
        $levelKaryawan = LevelKaryawan::select('nama_level')->distinct()->orderBy('nama_level')->pluck('nama_level');

        return view('target_unit.index', compact('data', 'unitkerja', 'levelKaryawan'));
    }

    public function create()
    {
        $unit = UnitKerja::all();
        $levelKaryawan = LevelKaryawan::all();
        return view('target_unit.create', compact('unit', 'levelKaryawan'));
    }
    public function getKomponenByLevel($levelId)
    {
        $komponen = KomponenPenghasilan::where('level_karyawan_id', $levelId)->get();
        return response()->json($komponen);
    }
    public function store(Request $request)
    {
        // Bersihkan titik ribuan sebelum validasi
        $request->merge([

            'target_bulanan' => str_replace('.', '', $request->target_bulanan),
            'besaran_nominal' => str_replace('.', '', $request->besaran_nominal),
        ]);

        // Validasi setelah input dibersihkan
        $validated = $request->validate([
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'level_karyawan_id' => 'required|exists:level_karyawans,id',
            'komponen_penghasilan_id' => 'required|exists:komponen_penghasilans,id',
            'target_bulanan' => 'required|numeric',
            'besaran_nominal' => 'required|numeric',
            'bulan' => 'required|integer',
            'tahun' => 'required|integer',
        ]);

        Targetunit::create($validated);

        return redirect()->route('target_unit.index')->with('success', 'Data Target Unit berhasil ditambahkan');
    }
    public function show($id)
    {
        $target_unit = Targetunit::findOrFail($id);

        return view('target_unit.show', compact('target_unit'));
    }
    public function edit($id)
    {
        //get post by ID
        $target_unit = \App\Targetunit::findOrFail($id);
        $levelKaryawan = LevelKaryawan::all();
        $unit = UnitKerja::all();

        return view('target_unit.edit', compact('target_unit', 'levelKaryawan', 'unit'));
    }
    public function update(Request $request, Targetunit $target_unit)
    {
        $request->merge([
            'target_bulanan' => str_replace('.', '', $request->target_bulanan),
            'besaran_nominal' => str_replace('.', '', $request->besaran_nominal),
        ]);

        $validated = $request->validate([
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'level_karyawan_id' => 'required|exists:level_karyawans,id',
            'komponen_penghasilan_id' => 'required|exists:komponen_penghasilans,id',
            'target_bulanan' => 'required|numeric',
            'besaran_nominal' => 'required|numeric',
            'bulan' => 'required|integer',
            'tahun' => 'required|integer',
        ]);

        $target_unit->update($validated);

        return redirect()->route('target_unit.index')->with('success', 'Data Target Unit berhasil diperbarui');
    }


    public function destroy($id)
    {
        $target_unit = Targetunit::findOrFail($id);

        $target_unit->delete();

        return redirect()->route('target_unit.index')->with('success', ' Data berhasil dihapus.');
    }
}

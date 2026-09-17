<?php

namespace App\Http\Controllers;

use App\Jamkerja;
use App\UnitKerja;
use Illuminate\Http\Request;

class JamKerjaController extends Controller
{
    //
    public function index()
    {
        $data = Jamkerja::with('unitKerja')->get();
        return view('jam_kerja.index', compact('data'));
    }
    public function create()
    {
        $unit_kerja = UnitKerja::all();
        return view('jam_kerja.create', compact('unit_kerja'));
    }
    public function edit($id)
    {
        $data = Jamkerja::findOrFail($id);
        $unit_kerja = UnitKerja::all();
        return view('jam_kerja.edit', compact('data', 'unit_kerja'));
    }
    public function store(Request $request)
    {
        // 1️⃣ Validasi array dasar
        $validated = $request->validate([
            'unit_kerja_id.*' => 'required|exists:unit_kerjas,id',
            'jam_mulai.*'     => 'required|date_format:H:i',
            'jam_selesai.*'   => 'required|date_format:H:i',
        ]);

        // 2️⃣ Validasi jam selesai harus lebih besar
        for ($i = 0; $i < count($request->jam_mulai); $i++) {
            if (strtotime($request->jam_selesai[$i]) <= strtotime($request->jam_mulai[$i])) {
                return back()->with('error', "Jam selesai harus lebih besar dari jam mulai pada baris ke-" . ($i + 1));
            }
        }

        // 3️⃣ Cek duplikasi dalam input form
        $duplicateUnits = collect($request->unit_kerja_id)
            ->filter(fn($v) => !empty($v))
            ->duplicates();

        if ($duplicateUnits->isNotEmpty()) {
            return back()->with('error', 'Terdapat unit kerja yang sama dalam input. Harap periksa kembali.');
        }

        // 4️⃣ Cek duplikasi terhadap data di database
        foreach ($request->unit_kerja_id as $id) {
            if (\App\Jamkerja::where('unit_kerja_id', $id)->exists()) {
                return back()->with('error', 'Jam kerja untuk unit tersebut sudah ada di sistem.');
            }
        }

        // 5️⃣ Siapkan data untuk insert batch
        $data = [];
        for ($i = 0; $i < count($request->unit_kerja_id); $i++) {
            if (empty($request->unit_kerja_id[$i])) continue;

            $data[] = [
                'unit_kerja_id' => $request->unit_kerja_id[$i],
                'jam_masuk'     => $request->jam_mulai[$i],
                'jam_keluar'    => $request->jam_selesai[$i],
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        // 6️⃣ Simpan data
        if (count($data)) {
            \App\Jamkerja::insert($data);
            return redirect()->route('jam_kerja.index')->with('success', 'Data berhasil ditambahkan.');
        } else {
            return back()->with('error', 'Tidak ada data yang dimasukkan.');
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'jam_masuk'          => 'required',
            'jam_keluar'          => 'required',
        ]);

        $exists = \App\Jamkerja::where('unit_kerja_id', $validated['unit_kerja_id'])
            ->where('id', '!=', $id) // pastikan bukan record yang sedang di-edit
            ->exists();

        if ($exists) {
            return back()->with('error', 'Jam kerja untuk unit tersebut sudah ada di sistem.');
        }

        $jamKerja = Jamkerja::findOrFail($id);

        $jamKerja->update([
            'unit_kerja' => $validated['unit_kerja_id'],
            'jam_masuk'  => $validated['jam_masuk'],
            'jam_keluar' => $validated['jam_keluar'],
        ]);

        return redirect()
            ->route('jam_kerja.index')
            ->with('success', 'Jam Kerja berhasil diupdate.');
    }
    public function destroy($id)
    {
        $jam_kerja = Jamkerja::findOrFail($id);

        $jam_kerja->delete();

        return redirect()->route('jam_kerja.index')->with('success', ' Data berhasil dihapus.');
    }
}

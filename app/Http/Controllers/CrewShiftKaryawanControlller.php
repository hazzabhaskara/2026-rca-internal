<?php

namespace App\Http\Controllers;

use App\CrewShiftKaryawan;
use Illuminate\Http\Request;

class CrewShiftKaryawanControlller extends Controller
{
    //
    public function index()
    {
        $data = CrewShiftKaryawan::orderBy('nama')->get();
        return view('crew_shift_karyawan.index', compact('data'));
    }
    public function create()
    {
        return view('crew_shift_karyawan.create');
    }
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|array',
            'deskripsi' => 'nullable|array',
        ]);
        $data = [];

        for ($i = 0; $i < count($request->nama); $i++) {
            // Lewati jika nama kosong
            if (empty($request->nama[$i])) continue;

            $data[] = [
                'nama' => $request->nama[$i],
                'deskripsi' => $request->deskripsi[$i] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (count($data)) {
            CrewShiftKaryawan::insert($data);
            return redirect()->route('crew_shift_karyawan.index')->with('success', 'Data berhasil ditambahkan.');
        } else {
            return back()->with('error', 'Tidak ada data yang dimasukkan.');
        }
    }
    public function edit($id)
    {
        $data = CrewShiftKaryawan::findOrFail($id);
        return view('crew_shift_karyawan.edit', compact('data'));
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'required|string',
            'deskripsi' => 'nullable|string',

        ]);

        $crew_shift_karyawan = CrewShiftKaryawan::findOrFail($id);

        $crew_shift_karyawan->update($validated);

        return redirect()->route('crew_shift_karyawan.index')->with('success', 'Crew berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $crew_shift_karyawan = CrewShiftKaryawan::findOrFail($id);

        $crew_shift_karyawan->delete();

        return redirect()->route('crew_shift_karyawan.index')->with('success', ' Data berhasil dihapus.');
    }
}

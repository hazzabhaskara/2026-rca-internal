<?php

namespace App\Http\Controllers;

use App\JenisHari;
use App\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JenisHariController extends Controller
{
    //
    public function index()
    {
        $data = JenisHari::all();
        return view('jenis_hari.index', compact('data'));
    }
    public function create()
    {
        $unitKerja = UnitKerja::all();
        $jenis_hari = null;
        return view('jenis_hari.create', compact('unitKerja', 'jenis_hari'));
    }
    public function store(Request $request)
    {
        // 1️⃣ Validasi array
        $validated = $request->validate([
            'unit_kerja_id.*' => 'required|exists:unit_kerjas,id',
            'nama.*'          => 'required|string|max:255',
            'deskripsi.*'     => 'nullable|string',
            'jumlah_pengunjung_min.*' => 'required|numeric',
            'jumlah_pengunjung_max.*' => 'nullable|numeric',
            'jam_mulai.*'     => 'required|date_format:H:i',
            'jam_selesai.*'   => 'required|date_format:H:i',
        ]);

        // 2️⃣ Validasi jam_selesai setelah jam_mulai per baris
        foreach ($request->jam_mulai as $index => $mulai) {
            $selesai = $request->jam_selesai[$index] ?? null;
            if ($mulai && $selesai && $selesai <= $mulai) {
                return back()
                    ->withErrors(["jam_selesai.$index" => "Jam selesai harus lebih besar dari jam mulai pada baris " . ($index + 1)])
                    ->withInput();
            }
        }

        // 3️⃣ Siapkan data untuk insert batch
        $data = [];
        for ($i = 0; $i < count($request->unit_kerja_id); $i++) {
            if (empty($request->unit_kerja_id[$i])) continue;

            $data[] = [
                'unit_kerja_id' => $request->unit_kerja_id[$i],
                'nama'          => $request->nama[$i],
                'jam_mulai'     => $request->jam_mulai[$i],
                'jam_selesai'   => $request->jam_selesai[$i],
                'deskripsi'     => $request->deskripsi[$i] ?? null,
                'jumlah_pengunjung_min' => $request->jumlah_pengunjung_min[$i] ?? 0,
                'jumlah_pengunjung_max' => $request->jumlah_pengunjung_max[$i] ?? 0,
                'created_at'    => now(),
                'updated_at'    => now()
            ];
        }

        // 4️⃣ Simpan data
        if (count($data)) {
            \App\JenisHari::insert($data);
            return redirect()->route('jenis_hari.index')->with('success', 'Data berhasil ditambahkan.');
        } else {
            return back()->with('error', 'Tidak ada data yang dimasukkan.');
        }
    }

    public function show($id)
    {
        $data = JenisHari::findOrFail($id);
        return view('jenis_hari.show', compact('data'));
    }
    public function edit($id)
    {
        $data = JenisHari::with(['unitKerja'])->findOrFail($id);
        $unitKerja = UnitKerja::all();
        return view('jenis_hari.edit', compact('data', 'unitKerja'));
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'nama' => 'string|max:255',
            'deskripsi' => 'nullable|string',
            'jumlah_pengunjung_min' => 'nullable|numeric',
            'jumlah_pengunjung_max' => 'nullable|numeric',
            'jam_mulai'     => 'required|date_format:H:i',
            'jam_selesai'   => 'required|date_format:H:i|after:jam_mulai',

        ]);

        $jenis_hari = JenisHari::findOrFail($id);

        $jenis_hari->update($validated);

        return redirect()->route('jenis_hari.index')->with('success', 'jenis hari update successfully.');
    }
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $jenis_hari = JenisHari::with(['BonusKaryawan', 'scheduling'])->findOrFail($id);

                // 🚫 Cek apakah sudah dipakai di Invoice
                if ($jenis_hari->BonusKaryawan()->exists()) {
                    throw new \Exception("Jenis hari ini sudah digunakan dalam bonus karyawan tidak bisa dihapus.");
                }
                if ($jenis_hari->scheduling()->exists()) {
                    throw new \Exception("Jenis hari ini sudah digunakan dalam scheduling tidak bisa dihapus.");
                }

                // ✅ Kalau aman, hapus (details ikut terhapus otomatis via cascade)
                $jenis_hari->delete();
            });

            return redirect()->route('jenis_hari.index')->with('success', 'Jenis hari berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('jenis_hari.index')->with('error', $e->getMessage());
        }
    }
}

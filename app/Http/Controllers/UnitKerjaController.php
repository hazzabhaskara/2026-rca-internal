<?php

namespace App\Http\Controllers;

use App\GroupUnit;
use App\UnitKerja;
use App\Wahana;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitKerjaController extends Controller
{
    //
    public function index()
    {
        $unit_kerja = UnitKerja::with(['groupUnit'])->get();
        return view('unit_kerja.index', compact('unit_kerja'));
    }
    public function create()
    {
        $groupUnit = GroupUnit::all();
        return view('unit_kerja.create', compact('groupUnit'));
    }
    public function store(Request $request)
    {
        // 1️⃣ Validasi array
        $validated = $request->validate([
            'group_unit_id.*' => 'required|exists:group_units,id',
            'kode_unit.*'          => 'required|string|max:255',
            'nama_unit.*'          => 'required|string|max:255',
            'deskripsi.*'     => 'nullable|string',
            'urutan.*'     => 'required|integer',
            'format_closing.*'     => 'required|integer',
        ]);

        // 3️⃣ Siapkan data untuk insert batch
        $data = [];
        for ($i = 0; $i < count($request->group_unit_id); $i++) {
            if (empty($request->group_unit_id[$i])) continue;

            $data[] = [
                'group_unit_id' => $request->group_unit_id[$i],
                'kode_unit'          => $request->kode_unit[$i],
                'nama_unit'          => $request->nama_unit[$i],
                'urutan'     => $request->urutan[$i],
                'format_closing' => $request->format_closing[$i],
                'deskripsi'     => $request->deskripsi[$i] ?? null,
                'created_at'    => now(),
                'updated_at'    => now()
            ];
        }

        // 4️⃣ Simpan data
        if (count($data)) {
            \App\UnitKerja::insert($data);
            return redirect()->route('unit_kerja.index')->with('success', 'Data berhasil ditambahkan.');
        } else {
            return back()->with('error', 'Tidak ada data yang dimasukkan.');
        }
    }
    public function show($id)
    {
        $unit_kerja = UnitKerja::findOrFail($id);

        return view('unit_kerja.show', compact('unit_kerja'));
    }
    public function edit(string $id): View
    {
        //get post by ID
        $unit_kerja = UnitKerja::findOrFail($id);
        $groupUnit = GroupUnit::all();

        return view('unit_kerja.edit', compact('unit_kerja', 'groupUnit'));
    }
    public function update(Request $request, $id)
    {

        // dd($request->all());
        $validated = $request->validate([
            'group_unit_id' => 'required|exists:group_units,id',
            'kode_unit' => 'required|string',
            'nama_unit' => 'required|string',
            'urutan' => 'required|integer',
            'format_closing' => 'required|integer',
            'deskripsi' => 'nullable|string',
        ]);

        $unit_kerja = UnitKerja::findOrFail($id);

        $unit_kerja->update($validated);

        return redirect()->route('unit_kerja.index')->with('success', 'Unit Kerja berhasil diperbarui.');
    }
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $unit_kerja = UnitKerja::with(['jenisHari', 'employee', 'targetUnit'])->findOrFail($id);

                // 🚫 Cek apakah sudah dipakai di Invoice
                if ($unit_kerja->jenisHari()->exists()) {
                    throw new \Exception("Unit Kerja ini,tidak bisa di hapus karena sudah digunakan dalam Jenis Hari.");
                }
                if ($unit_kerja->employee()->exists()) {
                    throw new \Exception("Unit Kerja ini,tidak bisa di hapus karena sudah digunakan dalam Employee.");
                }
                if ($unit_kerja->targetUnit()->exists()) {
                    throw new \Exception("Unit Kerja ini,tidak bisa di hapus karena sudah digunakan dalam Target unit.");
                }

                // ✅ Kalau aman, hapus (details ikut terhapus otomatis via cascade)
                $unit_kerja->delete();
            });

            return redirect()->route('unit_kerja.index')->with('success', 'Unit Kerja berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('unit_kerja.index')->with('error', $e->getMessage());
        }
    }
    public function getWahanaByUnit($id)
    {
        $unit = UnitKerja::find($id);

        if (!$unit) {
            return response()->json([]);
        }

        $wahanaList = Wahana::with([
            'wahanaItem' => function ($q) {
                $q->where('status', 1)
                    ->orderBy('nama_item')
                    ->with([
                        'account:id,kode_akun,nama_akun',
                        'departemen:id,deskripsi'
                    ]); // 🔹 tambahkan relasi account
            }
        ])
            ->where('unit_kerja_id', $id)
            ->orderBy('urutan')
            ->get();

        return response()->json([
            'format_closing' => $unit->format_closing,
            'wahana' => $wahanaList
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\KomponenPenghasilan;
use App\LevelKaryawan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KomponenPenghasilanController extends Controller
{
    //
    public function index()
    {
        $query = DB::table('komponen_penghasilans')
            ->leftJoin('level_karyawans', 'komponen_penghasilans.level_karyawan_id', '=', 'level_karyawans.id')
            ->select('komponen_penghasilans.*', 'level_karyawans.nama_level')
            ->orderBy('level_karyawans.nama_level')
            ->orderBy('komponen_penghasilans.tipe');


        // Filter Level Karyawan
        if ($level_karyawan = request('filter_tipe')) {
            $query->where('level_karyawans.nama_level', $level_karyawan);
        }


        // Filter Status
        if ($status = request('filter_status')) {
            $query->where('status_komponen', $status);
            // pastikan di tabel Wahana ada kolom 'status'
            // misalnya nilainya 'aktif' / 'nonaktif' atau 1/0
        }
        if ($sifat = request('filter_sifat')) {
            $query->where('sifat', $sifat);
            // pastikan di tabel Wahana ada kolom 'status'
            // misalnya nilainya 'aktif' / 'nonaktif' atau 1/0
        }
        if ($tipe_gaji = request('filter_tipe_gaji')) {
            $query->where('tipe', $tipe_gaji);
            // pastikan di tabel Wahana ada kolom 'status'
            // misalnya nilainya 'aktif' / 'nonaktif' atau 1/0
        }

        $searchable = ['komponen_penghasilans.nama_komponen', 'komponen_penghasilans.deskripsi'];

        if ($search = request('search')) {
            $query->where(function ($q) use ($search, $searchable) {
                foreach ($searchable as $col) {
                    $q->orWhere($col, 'like', "%{$search}%");
                }

                // ini menggantikan orWhereHas()
                $q->orWhere('level_karyawans.nama_level', 'like', "%{$search}%");
            });
        }



        // Eksekusi query sekali di akhir
        $data = $query->get();
        // Atau kalau mau paginasi:
        // $data = $query->paginate(20)->appends(request()->query());

        // Sumber data untuk dropdown
        $levelKaryawan = LevelKaryawan::select('nama_level')->distinct()->orderBy('nama_level')->pluck('nama_level');
        return view('komponen_penghasilan.index', compact('data', 'levelKaryawan'));
    }
    public function create()
    {
        $levels = LevelKaryawan::all();
        return view('komponen_penghasilan.create', compact('levels'));
    }
    public function store(Request $request)
    {

        // dd($request->all());
        $validated = $request->validate([
            'nama_komponen.*' => 'required|string|max:255',
            'tipe.*' => 'required|string|max:100',
            'kategori.*' => 'nullable|string|max:100',
            'sifat.*' => 'required|string|max:100',
            'periode_perhitungan.*' => 'required|string|max:100',
            'status_komponen.*' => 'required|string|max:100',
            'level_karyawan_id.*' => 'required|exists:level_karyawans,id',
            'cek_komponen.*' => 'nullable|boolean',
            'is_kehadiran.*' => 'nullable|boolean',
        ]);

        // Pastikan boolean terset benar
        $validated['cek_komponen'] = $request->input('cek_komponen', []);
        $validated['is_kehadiran'] = $request->input('is_kehadiran', []);

        $data = [];
        for ($i = 0; $i < count($request->level_karyawan_id); $i++) {
            if (empty($request->level_karyawan_id[$i])) continue;

            $data[] = [
                'level_karyawan_id' => $request->level_karyawan_id[$i],
                'nama_komponen'          => $request->nama_komponen[$i],
                'tipe'     => $request->tipe[$i],
                'deskripsi'   => $request->deskripsi[$i] ?? null,
                'sifat'     => $request->sifat[$i],
                'periode_perhitungan'     => $request->periode_perhitungan[$i],
                'status_komponen'   => $request->status_komponen[$i],
                'cek_komponen' => isset($request->cek_komponen[$i]) ? 1 : 0,
                'is_kehadiran' => isset($request->is_kehadiran[$i]) ? 1 : 0,
                'created_at'    => now(),
                'updated_at'    => now()
            ];
        }

        // 4️⃣ Simpan data
        if (count($data)) {
            \App\KomponenPenghasilan::insert($data);
            return redirect()->route('komponen_penghasilan.index')->with('success', 'Data berhasil ditambahkan.');
        } else {
            return back()->with('error', 'Tidak ada data yang dimasukkan.');
        }
    }

    public function show($id)
    {
        $data = KomponenPenghasilan::findOrFail($id);

        return view('komponen_penghasilan.show', compact('data'));
    }
    public function edit($id)
    {
        $data = KomponenPenghasilan::findOrFail($id);
        $levels = LevelKaryawan::all(); // untuk dropdown level kepegawaian
        return view('komponen_penghasilan.edit', compact('data', 'levels'));
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'nama_komponen' => 'required',
            'tipe' => 'required',
            'kategori' => 'required',
            'sifat' => 'required',
            'periode_perhitungan' => 'required',
            'status_komponen' => 'required',
            'level_karyawan_id' => 'required|exists:level_karyawans,id',
            'baru.*.kode_komponen' => 'nullable',
            'baru.*.jumlah_hari' => 'nullable|numeric|min:0',
            'baru.*.nilai' => 'nullable|numeric|min:0',
            'baru.*.potongan' => 'nullable|numeric|min:0',
        ]);

        $data = KomponenPenghasilan::findOrFail($id);
        $data->update([
            'nama_komponen' => $request->nama_komponen,
            'tipe' => $request->tipe,
            'kategori' => $request->kategori,
            'sifat' => $request->sifat,
            'periode_perhitungan' => $request->periode_perhitungan,
            'status_komponen' => $request->status_komponen,
            'level_karyawan_id' => $request->level_karyawan_id,
            'cek_komponen' => $request->has('cek_komponen') ? 1 : 0,
            'is_kehadiran' => $request->has('is_kehadiran') ? 1 : 0,
        ]);


        if ($request->has('baru')) {
            foreach ($request->baru as $item) {
                DB::table('komposisi_gaji_details')->insert([
                    'komposisi_gaji_id' => $id,
                    'kode_komponen' => $item['kode_komponen'] ?? null,
                    'jumlah_hari' => $item['jumlah_hari'] ?? 0,
                    'nilai' => $item['nilai'] ?? 0,
                    'potongan' => $item['potongan'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('komponen_penghasilan.index')
            ->with('success', 'Data berhasil diperbarui.');
    }


    public function destroy($id)
    {
        $komponen_penghasilan = KomponenPenghasilan::findOrFail($id);



        // Hapus komponen_penghasilan dari database
        $komponen_penghasilan->delete();

        return redirect()->route('komponen_penghasilan.index')->with('success', 'komponen_penghasilan berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Employee;
use App\KomposisiGaji;
use Illuminate\Http\Request;
use App\KomponenPenghasilan;
use App\KomposisiGajiDetail;
use App\LevelKaryawan;
use App\UnitKerja;
use Illuminate\Support\Facades\DB;

class KomposisiGajiController extends Controller
{
    //

    public function index()
    {
        $query = DB::table('komposisi_gajis')
            ->leftJoin('employees', 'komposisi_gajis.kode_karyawan', '=', 'employees.id')
            ->leftjoin('unit_kerjas', 'employees.unit_kerja_id', '=', 'unit_kerjas.id')
            ->leftJoin('level_karyawans', 'employees.level_kepegawaian_id', '=', 'level_karyawans.id')
            ->select('employees.nama_karyawan', 'unit_kerjas.nama_unit', 'employees.nik', 'employees.nama_panggilan', 'level_karyawans.nama_level', 'komposisi_gajis.id')
            ->orderBy('level_karyawans.nama_level', 'asc');

        // Filter Level Karyawan
        if ($level_karyawan = request('filter_tipe')) {
            $query->where('level_karyawans.nama_level', $level_karyawan);
        }
        // Filter Unit
        if ($unit = request('filter_unit')) {
            $query->where('unit_kerjas.nama_unit', $unit);
        }

        // Kolom searchable ada di tabel employee
        $searchable = ['kode_karyawan', 'nama_karyawan', 'nik', 'tempat_lahir'];
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('employees.kode_karyawan', 'like', "%{$search}%")
                    ->orWhere('employees.nama_karyawan', 'like', "%{$search}%")
                    ->orWhere('employees.nik', 'like', "%{$search}%")
                    ->orWhere('employees.tempat_lahir', 'like', "%{$search}%")
                    ->orWhere('unit_kerjas.nama_unit', 'like', "%{$search}%")
                    ->orWhere('level_karyawans.nama_level', 'like', "%{$search}%");
            });
        }


        $data = $query->paginate(10);
        $unit = UnitKerja::pluck('nama_unit')->filter()->unique()->values();
        $level_karyawan = LevelKaryawan::pluck('nama_level')->filter()->unique()->values();

        return view('komposisi_gaji.index', compact('data', 'unit', 'level_karyawan'));
    }

    public function create()
    {
        $karyawan = Employee::all();
        return view('komposisi_gaji.create', compact('karyawan'));
    }
    public function getKomposisiGajiByKaryawan($id)
    {
        // Cek apakah data karyawan ada
        $karyawan = Employee::find($id);

        if (!$karyawan) {
            return response()->json(['status' => 'error', 'message' => 'Karyawan tidak ditemukan.']);
        }

        // Cek apakah ada komposisi gaji untuk karyawan ini
        $komposisi = KomposisiGaji::where('kode_karyawan', $id)->first();

        if (!$komposisi) {
            return response()->json([]); // kosong karena belum ada
        }

        // Ambil detail + relasi komponen
        $details = KomposisiGajiDetail::with('komponen')
            ->where('komposisi_gaji_id', $komposisi->id)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->komponen->id,
                    'nama_komponen' => $item->komponen->nama_komponen,
                    'tipe' => $item->komponen->tipe ?? '-',
                    'periode_perhitungan' => $item->komponen->periode_perhitungan ?? '-',
                    'nilai' => $item->nilai,
                    'jumlah_hari' => $item->jumlah_hari,
                    'potongan' => $item->potongan,
                ];
            });

        return response()->json($details);
    }


    public function store(Request $request)
    {
        // Bersihkan format 'nilai' dan 'potongan' sebelum validasi
        $komponen = $request->komponen;
        foreach ($komponen as $i => $row) {
            $komponen[$i]['nilai'] = isset($row['nilai'])
                ? (float) str_replace(',', '.', str_replace(['Rp', '.', ' '], '', $row['nilai']))
                : null;

            $komponen[$i]['potongan'] = isset($row['potongan'])
                ? (float) str_replace(',', '.', str_replace(['Rp', '.', ' '], '', $row['potongan']))
                : null;
        }

        // Replace komponen dengan versi yang sudah dibersihkan
        $request->merge(['komponen' => $komponen]);

        // Validasi
        $request->validate([
            'kode_karyawan' => 'required|exists:employees,id',
            'komponen' => 'required|array',
            'komponen.*.nilai' => 'nullable|numeric',
            'komponen.*.jumlah_hari' => 'nullable|numeric',
            'komponen.*.potongan' => 'nullable|numeric',
        ]);

        // Cek apakah sudah ada komposisi gaji untuk karyawan yang sama
        $sudahAda = KomposisiGaji::where('kode_karyawan', $request->kode_karyawan)->exists();

        if ($sudahAda) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Komposisi gaji untuk karyawan tersebut sudah pernah dibuat.');
        }

        // Simpan ke tabel utama
        $komposisi = KomposisiGaji::create([
            'kode_karyawan' => $request->kode_karyawan,
        ]);

        // Simpan detail
        $urut = 1;
        foreach ($request->komponen as $row) {
            if (
                isset($row['nilai']) ||
                isset($row['jumlah_hari']) ||
                isset($row['potongan'])
            ) {
                $nilai = floatval($row['nilai'] ?? 0);
                $jumlahHari = floatval($row['jumlah_hari'] ?? 0);
                $potongan = floatval($row['potongan'] ?? 0);

                $total = ($nilai * $jumlahHari) + ($potongan * $jumlahHari);

                KomposisiGajiDetail::create([
                    'komposisi_gaji_id' => $komposisi->id,
                    'kode_komponen' => $row['kode_komponen'] ?? null,
                    'nilai' => $nilai,
                    'jumlah_hari' => $jumlahHari,
                    'potongan' => $potongan,
                    'total_nilai' => $total,
                    'urut' => $urut++,
                ]);
            }
        }

        return redirect()->route('komposisi_gaji.index')->with('success', 'Komposisi gaji berhasil disimpan.');
    }

    public function edit($id)
    {
        $komposisi = KomposisiGaji::findOrFail($id);
        $karyawan = \App\Employee::all();
        $details = \App\KomposisiGajiDetail::where('komposisi_gaji_id', $id)->with('komponen')->get();

        $kodeKomponenTersimpan = $details->pluck('kode_komponen')->toArray();

        $komponenBaru = DB::table('komposisi_gajis')
            ->leftJoin('employees', 'komposisi_gajis.kode_karyawan', '=', 'employees.id')
            ->leftJoin('komponen_penghasilans', 'employees.level_kepegawaian_id', '=', 'komponen_penghasilans.level_karyawan_id')
            ->where('komposisi_gajis.id', '=', $id)
            ->select('komponen_penghasilans.*')
            ->get();


        return view('komposisi_gaji.edit', compact('komposisi', 'karyawan', 'details', 'komponenBaru'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'komponen' => 'required|array',
            'komponen.*.nilai' => 'nullable|numeric',
            'komponen.*.jumlah_hari' => 'nullable|numeric',
            'komponen.*.potongan' => 'nullable|numeric',
        ]);

        $komposisi = KomposisiGaji::findOrFail($id);

        foreach ($request->komponen as $row) {
            $detail = \App\KomposisiGajiDetail::find($row['id_detail']);

            if ($detail) {
                $nilai = floatval($row['nilai'] ?? 0);
                $jumlahHari = floatval($row['jumlah_hari'] ?? 0);
                $potongan = floatval($row['potongan'] ?? 0);
                $total = ($nilai * $jumlahHari) + ($potongan * $jumlahHari);

                $detail->update([
                    'nilai' => $nilai,
                    'jumlah_hari' => $jumlahHari,
                    'potongan' => $potongan,
                    'total_nilai' => $total,
                ]);
            }
        }
        if ($request->has('baru')) {
            $urutAwal = KomposisiGajiDetail::where('komposisi_gaji_id', $id)->max('urut') + 1;

            foreach ($request->baru as $row) {
                $nilai = floatval($row['nilai'] ?? 0);
                $jumlahHari = floatval($row['jumlah_hari'] ?? 0);
                $potongan = floatval($row['potongan'] ?? 0);
                $total = ($nilai * $jumlahHari) + ($potongan * $jumlahHari);

                KomposisiGajiDetail::create([
                    'komposisi_gaji_id' => $id,
                    'kode_komponen' => $row['kode_komponen'],
                    'nilai' => $nilai,
                    'jumlah_hari' => $jumlahHari,
                    'potongan' => $potongan,
                    'total_nilai' => $total,
                    'urut' => $urutAwal++,
                ]);
            }
        }


        return redirect()->route('komposisi_gaji.index')->with('success', 'Komposisi gaji berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $komposisi = \App\KomposisiGaji::findOrFail($id);
        $komposisi->delete();

        return redirect()->route('komposisi_gaji.index')->with('success', 'Komposisi gaji berhasil dihapus.');
    }
    public function destroyDetail($id)
    {
        $detail = \App\KomposisiGajiDetail::findOrFail($id);
        $komposisiId = $detail->komposisi_gaji_id;

        $detail->delete();

        return redirect()->route('komposisi_gaji.edit', $komposisiId)->with('success', 'Komponen berhasil dihapus.');
    }
    public function show($id)
    {
        $komposisi = KomposisiGaji::with('employee')->findOrFail($id);
        $details = KomposisiGajiDetail::where('komposisi_gaji_id', $id)->with('komponen')->get();

        return view('komposisi_gaji.show', compact('komposisi', 'details'));
    }
    public function getKomponenByKaryawan($id)
    {
        try {
            // Cari karyawan berdasarkan ID
            $karyawan = Employee::findOrFail($id);

            // Ambil level karyawan dari relasi
            $levelId = $karyawan->level_kepegawaian_id;

            // Ambil komponen berdasarkan level
            $komponen = KomponenPenghasilan::where('level_karyawan_id', $levelId)
                ->where('status_komponen', 'aktif')
                ->get();

            return response()->json($komponen);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mengambil data komponen.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Absensi;
use App\Employee;
use App\komposisi_gaji;
use App\komposisi_gaji_detail;
use App\KomposisiGaji;
use App\KomposisiGajiDetail;
use App\LevelKaryawan;
use App\pembayaran_gaji;
use App\pembayaran_gaji_detail;
use App\PembayaranGaji;
use App\PembayaranGajiDetail;
use App\UnitKerja;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranGajiController extends Controller
{
    //
    public function index()
    {
        $query = PembayaranGaji::whereHas('employee.levelkaryawan', function ($query) {
            $query->where('nama_level', 'STAFF');
        });
        // Filter Level Karyawan
        if ($level_karyawan = request('filter_tipe')) {
            $query->whereHas('employee.levelKaryawan', function ($q) use ($level_karyawan) {
                $q->where('nama_level', $level_karyawan);
            });
        }

        // Filter Unit
        if ($unit = request('filter_unit')) {
            $query->whereHas('employee.unitKerja', function ($q) use ($unit) {
                $q->where('nama_unit', $unit);
            });
        }

        // Kolom searchable ada di tabel employee
        $searchable = ['kode_karyawan', 'nama_karyawan', 'nik', 'tempat_lahir'];

        if ($search = request('search')) {
            $query->where(function ($q) use ($search, $searchable) {
                // cari di tabel employees
                $q->orWhereHas('employee', function ($qEmp) use ($search, $searchable) {
                    $qEmp->where(function ($qq) use ($search, $searchable) {
                        foreach ($searchable as $col) {
                            $qq->orWhere($col, 'like', "%{$search}%");
                        }
                    });
                });

                // cari di level karyawan
                $q->orWhereHas('employee.levelKaryawan', function ($q4) use ($search) {
                    $q4->where('nama_level', 'like', "%{$search}%");
                });

                // cari di unit kerja
                $q->orWhereHas('employee.unitKerja', function ($q1) use ($search) {
                    $q1->where('nama_unit', 'like', "%{$search}%");
                });
            });
        }


        $data = $query->paginate(10);
        $unit = UnitKerja::pluck('nama_unit')->filter()->unique()->values();
        $level_karyawan = LevelKaryawan::pluck('nama_level')->filter()->unique()->values();
        return view('pembayaran_gaji.index', compact('data', 'unit', 'level_karyawan'));
    }
    public function create()
    {
        $karyawan = Employee::whereHas('levelKaryawan', function ($query) {
            $query->where('nama_level', '=', 'STAFF');
        })->get();
        return view('pembayaran_gaji.create', compact('karyawan'));
    }
    public function getKomposisiGajiByKaryawan($id)
    {
        $komposisi = KomposisiGaji::where('kode_karyawan', $id)->first();

        if (!$komposisi) {
            return response()->json([]);
        }

        // Ambil periode dari request
        $periodeAwal = request()->get('periode_awal');
        $periodeAkhir = request()->get('periode_akhir');

        $details = KomposisiGajiDetail::with('komponen')
            ->where('komposisi_gaji_id', $komposisi->id)
            ->orderBy('urut')
            ->get()
            ->map(function ($item) use ($id, $periodeAwal, $periodeAkhir) {
                $jumlah = $item->jumlah_hari ?? 0;
                $catatan = null;

                if ($periodeAwal && $periodeAkhir) {
                    // === 1. Komponen Kehadiran (hitung jumlah hari masuk) ===
                    if ($item->komponen->is_kehadiran) {
                        $jumlah = Absensi::where('employee_id', $id)
                            ->whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
                            ->whereIn('status', ['Masuk', 'Terlambat'])
                            ->count();
                    }

                    // === 2. Komponen Lembur (hitung total jam lembur) ===
                    if (strtolower($item->komponen->nama_komponen) === 'lembur') {
                        $lemburRecords = Absensi::where('employee_id', $id)
                            ->whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
                            ->whereIn('status', ['Lembur Masuk', 'Lembur Pulang'])
                            ->orderBy('tanggal')
                            ->orderBy('jam')
                            ->get()
                            ->groupBy('tanggal');

                        $totalJam = 0;
                        foreach ($lemburRecords as $tanggal => $records) {
                            $masuk = $records->firstWhere('status', 'Lembur Masuk');
                            $pulang = $records->firstWhere('status', 'Lembur Pulang');

                            if ($masuk && $pulang) {
                                $masukTime = \Carbon\Carbon::parse($masuk->jam);
                                $pulangTime = \Carbon\Carbon::parse($pulang->jam);
                                $totalJam += $masukTime->diffInHours($pulangTime);
                            }
                        }
                        $jumlah = $totalJam; // jumlah jam lembur
                    }

                    // === 3. Komponen Transportasi (cek target unit) ===
                    if (strtolower($item->komponen->nama_komponen) === 'transportasi') {
                        $employee = \App\Employee::find($id);

                        if ($employee) {
                            $bulan = \Carbon\Carbon::parse($periodeAwal)->month;
                            $tahun = \Carbon\Carbon::parse($periodeAwal)->year;

                            $targetUnit = \App\Targetunit::where('unit_kerja_id', $employee->unit_kerja_id)
                                ->where('komponen_penghasilan_id', $item->komponen->id)
                                ->where('bulan', $bulan)
                                ->where('tahun', $tahun)
                                ->first();

                            $totalRealisasi = \App\TransaksiWahana::where('unit_kerja_id', $employee->unit_kerja_id)
                                ->whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
                                ->sum('realisasi');

                            if ($targetUnit) {
                                $target = number_format($targetUnit->target_bulanan, 0, ',', '.');
                                $realisasi = number_format($totalRealisasi, 0, ',', '.');

                                if ($totalRealisasi < $targetUnit->target_bulanan) {
                                    $catatan = "Target unit tidak tercapai. Target: Rp {$target}, Realisasi: Rp {$realisasi}";
                                    $item->nilai = 0;
                                } else {
                                    $catatan = "Target tercapai ✔. Realisasi: Rp {$realisasi}";
                                }
                            } else {
                                $catatan = "⚠ Tidak ada target unit untuk periode ini";
                            }
                        }
                    }
                    // === 4. Komponen Bonus (cek target harian via transaksi wahana) ===
                    if (strtolower($item->komponen->nama_komponen) === 'bonus') {
                        $employee = \App\Employee::find($id);

                        if ($employee) {
                            $bulan = \Carbon\Carbon::parse($periodeAwal)->month;
                            $tahun = \Carbon\Carbon::parse($periodeAwal)->year;

                            $targetUnit = \App\Targetunit::where('unit_kerja_id', $employee->unit_kerja_id)
                                ->where('komponen_penghasilan_id', $item->komponen->id)
                                ->where('bulan', $bulan)
                                ->where('tahun', $tahun)
                                ->first();

                            $totalRealisasi = \App\TransaksiWahana::where('unit_kerja_id', $employee->unit_kerja_id)
                                ->whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
                                ->sum('realisasi');

                            if ($targetUnit) {
                                $target = number_format($targetUnit->target_bulanan, 0, ',', '.');
                                $realisasi = number_format($totalRealisasi, 0, ',', '.');

                                if ($totalRealisasi < $targetUnit->target_bulanan) {
                                    $catatan = "Target unit tidak tercapai. Target: Rp {$target}, Realisasi: Rp {$realisasi}";
                                    $item->nilai = 0;
                                } else {
                                    $catatan = "Target tercapai ✔. Realisasi: Rp {$realisasi}";
                                }
                            } else {
                                $catatan = "⚠ Tidak ada target unit untuk periode ini";
                            }
                        }
                    }
                }

                return [
                    'id' => $item->komponen->id,
                    'nama_komponen' => $item->komponen->nama_komponen,
                    'tipe' => $item->komponen->tipe ?? '-',
                    'periode_perhitungan' => $item->komponen->periode_perhitungan ?? '-',
                    'nilai' => $item->nilai,
                    'jumlah_hari' => $jumlah,
                    'potongan' => $item->potongan,
                    'catatan' => $catatan, // tambahan untuk transportasi
                ];
            });

        return response()->json($details);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_karyawan' => 'required|exists:employees,id',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date|after_or_equal:periode_awal',
            'tanggal_pembayaran' => 'required|date',
            'komponen' => 'required|array',
            'komponen.*.kode_komponen' => 'required|exists:komponen_penghasilans,id',
            'komponen.*.nilai' => 'nullable|numeric',
            'komponen.*.jumlah_hari' => 'nullable|numeric',
            'komponen.*.potongan' => 'nullable|numeric',
            'komponen.*.total' => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            // Simpan pembayaran_gajis
            $pembayaran = PembayaranGaji::create([
                'kode_karyawan' => $request->kode_karyawan,
                'periode_awal' => $request->periode_awal,
                'periode_akhir' => $request->periode_akhir,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
            ]);

            // Simpan detailnya
            foreach ($request->komponen as $index => $komponen) {
                PembayaranGajiDetail::create([
                    'kode_pembayaran_id' => $pembayaran->id,
                    'kode_komponen' => $komponen['kode_komponen'],
                    'nilai' => $komponen['nilai'] ?? 0,
                    'jumlah_hari' => $komponen['jumlah_hari'] ?? 0,
                    'potongan' => $komponen['potongan'] ?? 0,
                    'urut' => $index + 1,
                ]);
            }

            DB::commit();

            return redirect()->route('pembayaran_gaji.index')
                ->with('success', 'Pembayaran gaji berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage())->withInput();
        }
    }
    public function show($id)
    {
        $pembayaran = PembayaranGaji::with('employee')->findOrFail($id);
        $details = PembayaranGajiDetail::where('kode_pembayaran_id', $id)->with('komponen')->get();

        return view('pembayaran_gaji.show', compact('pembayaran', 'details'));
    }
    public function edit($id)
    {
        // Ambil data pembayaran gaji berdasarkan ID
        $pembayaran = PembayaranGaji::findOrFail($id);

        // Ambil semua karyawan untuk dropdown
        $karyawan = Employee::whereHas('levelKaryawan', function ($query) {
            $query->where('nama_level', '=', 'STAFF');
        })->get();

        // Ambil detail komponen penghasilan yang terkait
        $details = PembayaranGajiDetail::where('kode_pembayaran_id', $id)
            ->with('komponen') // pastikan ada relasi komponen di model
            ->get();

        return view(
            'pembayaran_gaji.edit',
            compact('pembayaran', 'karyawan', 'details')
        );
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date',
            'tanggal_pembayaran' => 'required|date',
            'komponen.*.nilai' => 'nullable|numeric',
            'komponen.*.jumlah_hari' => 'nullable|numeric',
            'komponen.*.potongan' => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            // Update data pembayaran utama
            $pembayaran = PembayaranGaji::findOrFail($id);
            $pembayaran->update([
                'periode_awal' => $request->periode_awal,
                'periode_akhir' => $request->periode_akhir,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
            ]);

            // Update detail komponen satu per satu
            if ($request->has('komponen')) {
                foreach ($request->komponen as $komponen) {
                    if (isset($komponen['id'])) {
                        $detail = PembayaranGajiDetail::find($komponen['id']);

                        if ($detail) {
                            $detail->update([
                                'kode_komponen' => $komponen['kode_komponen'],
                                'nilai' => $komponen['nilai'] ?? 0,
                                'jumlah_hari' => $komponen['jumlah_hari'] ?? 0,
                                'potongan' => $komponen['potongan'] ?? 0,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('pembayaran_gaji.index')->with('success', 'Data pembayaran gaji berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan saat memperbarui: ' . $e->getMessage());
        }
    }
    public function destroy($id)
    {
        $pembayaran = \App\pembayaranGaji::findOrFail($id);
        $pembayaran->delete();

        return redirect()->route('pembayaran_gaji.index')->with('success', 'Pembayaran gaji berhasil dihapus.');
    }
}

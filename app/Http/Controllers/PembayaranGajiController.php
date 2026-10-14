<?php

namespace App\Http\Controllers;

use App\Absensi;
use App\Employee;
use App\KomposisiGaji;
use App\KomposisiGajiDetail;
use App\LevelKaryawan;
use App\PembayaranGaji;
use App\PembayaranGajiDetail;
use App\Services\PayrollJournalService;
use App\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranGajiController extends Controller
{
    public function index()
    {
        $query = PembayaranGaji::whereHas('employee.levelkaryawan', function ($query) {
            $query->where('nama_level', 'STAFF');
        });

        if ($level_karyawan = request('filter_tipe')) {
            $query->whereHas('employee.levelKaryawan', function ($q) use ($level_karyawan) {
                $q->where('nama_level', $level_karyawan);
            });
        }

        if ($unit = request('filter_unit')) {
            $query->whereHas('employee.unitKerja', function ($q) use ($unit) {
                $q->where('nama_unit', $unit);
            });
        }

        $searchable = ['kode_karyawan', 'nama_karyawan', 'nik', 'tempat_lahir'];

        if ($search = request('search')) {
            $query->where(function ($q) use ($search, $searchable) {
                $q->orWhereHas('employee', function ($qEmp) use ($search, $searchable) {
                    $qEmp->where(function ($qq) use ($search, $searchable) {
                        foreach ($searchable as $col) {
                            $qq->orWhere($col, 'like', "%{$search}%");
                        }
                    });
                });

                $q->orWhereHas('employee.levelKaryawan', function ($q4) use ($search) {
                    $q4->where('nama_level', 'like', "%{$search}%");
                });

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
                    if ($item->komponen->is_kehadiran) {
                        $jumlah = Absensi::where('employee_id', $id)
                            ->whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
                            ->whereIn('status', ['Masuk', 'Terlambat'])
                            ->count();
                    }

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
                        $jumlah = $totalJam;
                    }

                    if (in_array(strtolower($item->komponen->nama_komponen), ['transportasi', 'bonus'], true)) {
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
                    'catatan' => $catatan,
                ];
            });

        return response()->json($details);
    }

    public function store(Request $request, PayrollJournalService $payrollJournalService)
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
            $pembayaran = PembayaranGaji::create([
                'kode_karyawan' => $request->kode_karyawan,
                'periode_awal' => $request->periode_awal,
                'periode_akhir' => $request->periode_akhir,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
            ]);

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

            // Integrasi otomatis ke Buku Besar (JournalEntry + DepartemenAkun)
            $payrollJournalService->syncJournalForPayroll($pembayaran);

            if (function_exists('logActivity')) {
                logActivity("Create Pembayaran Gaji Staff #{$pembayaran->id} (Employee ID: {$pembayaran->kode_karyawan})");
            }

            DB::commit();

            return redirect()->route('pembayaran_gaji.index')
                ->with('success', 'Pembayaran gaji berhasil disimpan beserta jurnal buku besar.');
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
        $pembayaran = PembayaranGaji::findOrFail($id);
        $karyawan = Employee::whereHas('levelKaryawan', function ($query) {
            $query->where('nama_level', '=', 'STAFF');
        })->get();

        $details = PembayaranGajiDetail::where('kode_pembayaran_id', $id)
            ->with('komponen')
            ->get();

        return view('pembayaran_gaji.edit', compact('pembayaran', 'karyawan', 'details'));
    }

    public function update(Request $request, $id, PayrollJournalService $payrollJournalService)
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
            $pembayaran = PembayaranGaji::findOrFail($id);
            $pembayaran->update([
                'periode_awal' => $request->periode_awal,
                'periode_akhir' => $request->periode_akhir,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
            ]);

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

            // Sinkronisasi ulang Jurnal Buku Besar
            $payrollJournalService->syncJournalForPayroll($pembayaran);

            if (function_exists('logActivity')) {
                logActivity("Update Pembayaran Gaji Staff #{$pembayaran->id}");
            }

            DB::commit();

            return redirect()->route('pembayaran_gaji.index')->with('success', 'Data pembayaran gaji dan jurnal berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memperbarui: ' . $e->getMessage());
        }
    }

    public function destroy($id, PayrollJournalService $payrollJournalService)
    {
        DB::beginTransaction();
        try {
            $pembayaran = PembayaranGaji::findOrFail($id);
            $payrollJournalService->deleteJournalForPayroll($pembayaran->id);
            PembayaranGajiDetail::where('kode_pembayaran_id', $pembayaran->id)->delete();
            $pembayaran->delete();

            if (function_exists('logActivity')) {
                logActivity("Delete Pembayaran Gaji Staff #{$id}");
            }

            DB::commit();
            return redirect()->route('pembayaran_gaji.index')->with('success', 'Pembayaran gaji beserta jurnal berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus pembayaran gaji: ' . $e->getMessage());
        }
    }
}

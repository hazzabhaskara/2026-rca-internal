<?php

namespace App\Http\Controllers;

use App\Absensi;
use App\Employee;
use App\Jamkerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AbsensiController extends Controller
{
    //
    public function form()
    {
        $absensis = Absensi::select(
            'employee_id',
            'tanggal',
            DB::raw("MAX(CASE WHEN status = 'Masuk' OR status = 'Terlambat' THEN jam END) as jam_masuk"),
            DB::raw("MAX(CASE WHEN status = 'Pulang' THEN jam END) as jam_pulang"),
            DB::raw("MAX(CASE WHEN status = 'Lembur Masuk' THEN jam END) as jam_lembur_masuk"),
            DB::raw("MAX(CASE WHEN status = 'Lembur Pulang' THEN jam END) as jam_lembur_pulang")
        )
            ->groupBy('employee_id', 'tanggal')
            ->with('employee') // relasi ke karyawan
            ->orderBy('tanggal', 'desc')
            ->get();
        return view('absensi.scan', compact('absensis'));
    }
    public function scan(Request $request)
    {
        $rfid = $request->input('rfid');
        if (!$rfid) {
            return back()->with('error', 'RFID tidak dikirim');
        }

        $tanggal = now()->toDateString();
        $jam     = now()->toTimeString();

        // Cari karyawan
        $karyawan = Employee::where('rfid_code', $rfid)->first();
        if (!$karyawan) {
            return back()->with('error', 'RFID tidak ditemukan');
        }

        // Ambil jam kerja default
        $jamKerja = Jamkerja::first();
        if (!$jamKerja) {
            return back()->with('error', 'Jam kerja belum disetting');
        }

        // Ambil status absensi hari ini
        $statuses = Absensi::where('employee_id', $karyawan->id)
            ->whereDate('tanggal', $tanggal)
            ->pluck('status')
            ->toArray();

        Log::info("Absensi scan - {$karyawan->nama_karyawan}", [
            'tanggal'   => $tanggal,
            'jam'       => $jam,
            'statuses'  => $statuses,
        ]);

        $jamSekarang = strtotime($jam);
        $jamMasuk    = strtotime($jamKerja->jam_masuk);
        $jamPulang   = strtotime($jamKerja->jam_keluar);
        $toleransi   = 0; // 1 jam

        // Tentukan status absensi
        if (count($statuses) == 0) {
            // Absen pertama kali (masuk kerja)
            if ($jamSekarang <= $jamMasuk + $toleransi) {
                $status = 'Masuk';
                $pesan  = "Anda berhasil absen masuk tepat waktu.";
            } else {
                $status = 'Terlambat';
                $pesan  = "Anda berhasil absen masuk, tetapi terlambat.";
            }
        } elseif (in_array('Masuk', $statuses) || in_array('Terlambat', $statuses)) {
            // Sudah masuk kerja

            if (in_array('Pulang', $statuses)) {
                // Sudah ada pulang → lanjut lembur
                if (!in_array('Lembur Masuk', $statuses)) {
                    $status = 'Lembur Masuk';
                    $pesan  = "Anda berhasil absen lembur masuk.";
                } elseif (!in_array('Lembur Pulang', $statuses)) {
                    $status = 'Lembur Pulang';
                    $pesan  = "Anda berhasil absen lembur pulang.";
                } else {
                    return back()->with('error', 'Anda sudah lengkap absen lembur hari ini.');
                }
            } else {
                // Belum ada pulang
                if ($jamSekarang >= $jamPulang - $toleransi) {
                    if ($jamSekarang >= $jamPulang + 1800) {
                        // Jika sudah lewat jam pulang 30 menit → auto-insert pulang
                        Absensi::create([
                            'employee_id' => $karyawan->id,
                            'tanggal'     => $tanggal,
                            'jam'         => date('H:i:s', $jamPulang),
                            'status'      => 'Pulang',
                        ]);

                        $status = 'Lembur Masuk';
                        $pesan  = "Anda lupa absen pulang, sistem otomatis mencatat pulang, lalu sekarang absen lembur masuk.";
                    } else {
                        // Normal pulang
                        $status = 'Pulang';
                        $pesan  = "Anda berhasil absen pulang.";
                    }
                } else {
                    return back()->with('error', 'Belum waktunya pulang, silakan absen nanti.');
                }
            }
        } else {
            return back()->with('error', 'Absensi Anda hari ini sudah lengkap.');
        }

        // Simpan absensi
        Absensi::create([
            'employee_id' => $karyawan->id,
            'tanggal'     => $tanggal,
            'jam'         => $jam,
            'status'      => $status,
        ]);

        Log::info("Absensi berhasil disimpan", [
            'employee_id' => $karyawan->id,
            'status'      => $status,
        ]);

        return back()->with('success', "{$karyawan->nama_karyawan} - $pesan");
    }
}

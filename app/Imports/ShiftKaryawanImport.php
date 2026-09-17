<?php

namespace App\Imports;

use App\Employee;
use App\JenisHari;
use App\ShiftKaryawanWahana;
use App\UnitKerja;
use App\Wahana;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ShiftKaryawanImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    public function model(array $row)
    {
        // Debug awal: row mentah dari Excel
        Log::info('Row dibaca:', $row);

        try {
            $unitKerja  = UnitKerja::where('nama_unit', $row['unit_kerja'])->first();
            $jenis_hari = JenisHari::where('nama', $row['jenis_hari'])->first();
            $wahana     = Wahana::where('nama_wahana', $row['wahana'])->first();
            $karyawan   = Employee::where('nama_karyawan', $row['karyawan'])->first();

            if (!$unitKerja) {
                throw new \Exception("Unit kerja '{$row['unit_kerja']}' tidak ditemukan.");
            }
            if (!$jenis_hari) {
                throw new \Exception("Jenis Hari '{$row['jenis_hari']}' tidak ditemukan.");
            }
            if (!$karyawan) {
                throw new \Exception("Karyawan '{$row['karyawan']}' tidak ditemukan.");
            }
            if (!$wahana) {
                throw new \Exception("Wahana '{$row['wahana']}' tidak ditemukan.");
            }

            // Hitung lama jam
            $jamMulai   = \Carbon\Carbon::parse($row['jam_mulai']);
            $jamSelesai = \Carbon\Carbon::parse($row['jam_selesai']);
            $lamaJam    = $jamMulai->diffInMinutes($jamSelesai) / 60;

            // Hitung persentase dari default jam jenis_hari
            $persentase = null;
            if ($jenis_hari->jam_mulai && $jenis_hari->jam_selesai) {
                $defaultMulai   = \Carbon\Carbon::parse($jenis_hari->jam_mulai);
                $defaultSelesai = \Carbon\Carbon::parse($jenis_hari->jam_selesai);
                $defaultJam     = $defaultMulai->diffInMinutes($defaultSelesai) / 60;
                if ($defaultJam > 0) {
                    $persentase = $lamaJam / $defaultJam;
                }
            }

            $data = [
                'employee_id'    => $karyawan->id,
                'unit_kerja_id'  => $unitKerja->id,
                'wahana_id'      => $wahana->id,
                'jenis_hari_id'  => $jenis_hari->id,
                'tanggal'        => $this->transformDate($row['tanggal']),
                'jam_mulai'      => $row['jam_mulai'],
                'jam_selesai'    => $row['jam_selesai'],
                'lama_jam'       => $lamaJam,
                'persentase_jam' => $persentase,
                'status'         => $row['status'] ?? null,
                'keterangan'     => $row['keterangan'] ?? null,
                'posisi'         => $row['posisi'] ?? null,
            ];

            Log::info('Siap disimpan:', $data);

            return new ShiftKaryawanWahana($data);
        } catch (\Throwable $e) {
            // Tangkap error per baris, tidak hentikan seluruh import
            Log::error('Gagal import row:', [
                'row' => $row,
                'error' => $e->getMessage(),
            ]);
            return null; // baris ini dilewati
        }
    }

    private function transformDate($value)
    {
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}

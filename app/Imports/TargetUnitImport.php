<?php

namespace App\Imports;

use App\KomponenPenghasilan;
use App\LevelKaryawan;
use App\Targetunit;
use App\UnitKerja;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TargetUnitImport implements ToModel, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function model(array $row)
    {
        // Cari ID unit kerja dari nama
        $unitKerja = UnitKerja::where('nama_unit', $row['unit_kerja'])->first();
        $level_karyawan = LevelKaryawan::where('nama_level', $row['level_karyawan'])->first();
        $komponen = KomponenPenghasilan::where('nama_komponen', $row['komponen_penghasilan'])->first();

        if (!$unitKerja) {
            throw ValidationException::withMessages(['unit_kerja' => "unit kerja '{$row['unit_kerja']}' tidak ditemukan."]);
        }
        if (!$level_karyawan) {
            throw ValidationException::withMessages(['level_karyawan' => "level karyawan '{$row['level_karyawan']}' tidak ditemukan."]);
        }
        if (!$komponen) {
            throw ValidationException::withMessages(['komponen_penghasilan' => "Komponen Penghasilan '{$row['komponen_penghasilan']}' tidak ditemukan."]);
        }

        return new Targetunit([
            'unit_kerja_id' => $unitKerja->id ?? null, // bisa pakai null/0 jika tidak ditemukan
            'komponen_penghasilan_id'   => $komponen->id ?? null,
            'target_bulanan' => $row['target_bulanan'],
            'besaran_nominal'   => $row['besaran_nominal'],
            'bulan'        => $row['bulan'],
            'tahun'     => $row['tahun'] ?? null,
            'level_karyawan_id'      => $level_karyawan->id ?? null,

        ]);
    }
}

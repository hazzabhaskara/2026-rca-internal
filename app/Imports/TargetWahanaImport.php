<?php

namespace App\Imports;

use App\JenisHari;
use App\TargetWahana;
use App\UnitKerja;
use App\Wahana;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TargetWahanaImport implements ToModel, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function model(array $row)
    {
        // Cari ID unit kerja dari nama
        $jenis_hari = JenisHari::where('nama', $row['jenis_hari'])->first();
        $wahana = Wahana::where('nama_wahana', $row['wahana'])->first();
        $unitKerja = UnitKerja::where('nama_unit', $row['unit_kerja'])->first();

        if (!$unitKerja) {
            throw ValidationException::withMessages(['unit_kerja' => "unit kerja '{$row['unit_kerja']}' tidak ditemukan."]);
        }
        if (!$wahana) {
            throw ValidationException::withMessages(['wahana' => "wahana '{$row['wahana']}' tidak ditemukan."]);
        }
        if (!$jenis_hari) {
            throw ValidationException::withMessages(['jenis_hari' => "Jenis Hari '{$row['jenis_hari']}' tidak ditemukan."]);
        }

        return new TargetWahana([
            'wahana_id'   => $wahana->id ?? null,
            'unit_kerja_id' => $unitKerja->id ?? null, // bisa pakai null/0 jika tidak ditemukan
            'jenis_hari_id' => $jenis_hari->id ?? null,
            'target_harian'   => $row['target_harian'],
            'bulan'        => $row['bulan'],
            'tahun'     => $row['tahun'] ?? null,
            'keterangan'     => $row['keterangan'] ?? null,

        ]);
    }
}

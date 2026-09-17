<?php

namespace App\Imports;

use App\UnitKerja;
use App\Wahana;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WahanaImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Cari ID unit kerja dari nama
        $unitKerja = UnitKerja::where('nama_unit', $row['unit_kerja'])->first();

        if (!$unitKerja) {
            throw ValidationException::withMessages(['unit_kerja' => "unit kerja '{$row['unit_kerja']}' tidak ditemukan."]);
        }

        return new Wahana([
            'unit_kerja_id' => $unitKerja->id ?? null, // bisa pakai null/0 jika tidak ditemukan
            'kode_wahana'   => $row['kode_wahana'],
            'nama_wahana'   => $row['nama_wahana'],
            'status'        => $row['status'],
            'kapasitas'     => $row['kapasitas'] ?? null,
            'kategori'      => $row['kategori'] ?? null,
            'urutan'        => $row['urutan'],

        ]);
    }
}

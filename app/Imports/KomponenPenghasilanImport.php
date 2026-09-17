<?php

namespace App\Imports;

use App\KomponenPenghasilan;
use App\LevelKaryawan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Validation\ValidationException;

class KomponenPenghasilanImport implements ToModel, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function model(array $row)
    {

        // dd($row);
        // Cari ID unit kerja dari nama
        $level_karyawan = LevelKaryawan::where('nama_level', $row['level_karyawan'])->first();

        if (!$level_karyawan) {
            throw ValidationException::withMessages(['level_karyawan' => "level karyawan '{$row['level_karyawan']}' tidak ditemukan."]);
        }

        return new KomponenPenghasilan([
            'level_karyawan_id' => $level_karyawan->id ?? null, // bisa pakai null/0 jika tidak ditemukan
            'nama_komponen'   => $row['nama_komponen'],
            'tipe'   => $row['tipe'],
            'kategori'        => $row['deskripsi'],
            'sifat' => $row['sifat'],
            'periode_perhitungan'     => $row['periode_perhitungan'] ?? null,
            'status_komponen'      => $row['status_komponen'] ?? null,
        ]);
    }
}

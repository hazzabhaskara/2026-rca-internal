<?php

namespace App\Imports;

use App\KlasifikasiAkun;
use App\NumberingAccount;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KlasifikasiAkunImport implements ToModel, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function model(array $row)
    {
        // Cari ID numbering berdasarkan nama_grup
        $numbering = NumberingAccount::where('nama_grup', $row['nama_grup'])->first();

        if (!$numbering) {
            throw new \Exception("❌ Numbering dengan nama grup '{$row['nama_grup']}' tidak ditemukan!");
        }
        return new KlasifikasiAkun([
            'kode_klasifikasi'      => $row['kode_klasifikasi'],
            'nama_klasifikasi'      => $row['nama_klasifikasi'],
            'numbering_account_id'  => $numbering->id,
            'urutan'                => $row['urutan'] ?? 0,
            'deskripsi'             => $row['deskripsi'] ?? null,
            'aktif'                 => $row['aktif'] ?? 1,
        ]);
    }
}

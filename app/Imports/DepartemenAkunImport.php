<?php

namespace App\Imports;

use App\Departement;
use App\ChartOfAccount;
use App\DepartemenAkun;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class DepartemenAkunImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Mendukung kode_akun maupun kode_ak jika terjadi typo saat di-save di Excel
        $kodeDepartemen = $row['kode'] ?? null;
        $kodeAkun = $row['kode_akun'] ?? $row['kode_ak'] ?? null;

        if (!$kodeDepartemen || !$kodeAkun) {
            return null; // Skip if mandatory fields are missing
        }

        // Cari berdasarkan kode atau deskripsi
        $departemen = Departement::where('kode', $kodeDepartemen)
            ->orWhere('deskripsi', $kodeDepartemen)
            ->first();
            
        $akun = ChartOfAccount::where('kode_akun', $kodeAkun)->first();

        if ($departemen && $akun) {
            // Cek apakah sudah di-assign sebelumnya
            $exists = DepartemenAkun::where('departemen_id', $departemen->id)
                ->where('akun_id', $akun->id)
                ->exists();

            if (!$exists) {
                return new DepartemenAkun([
                    'departemen_id' => $departemen->id,
                    'akun_id'       => $akun->id,
                ]);
            }
        }

        return null;
    }
}

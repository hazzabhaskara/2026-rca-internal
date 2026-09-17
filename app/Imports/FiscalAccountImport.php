<?php

namespace App\Imports;

use App\FiscalAccount;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FiscalAccountImport implements ToModel, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function model(array $row)
    {
        return new FiscalAccount([
            'kode_akun'      => $row['kode_akun'],
            'nama_akun'      => $row['nama_akun'],
        ]);
    }
}

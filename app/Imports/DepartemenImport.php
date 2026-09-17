<?php

namespace App\Imports;

use App\Departement;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DepartemenImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Departement([
            'kode'      => $row['kode_departemen'],
            'deskripsi'      => $row['nama_departemen'],
        ]);
    }
}

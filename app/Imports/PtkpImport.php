<?php

namespace App\Imports;

use App\Ptkp;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PtkpImport implements ToModel, WithStartRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    // Mulai dari baris ke-2 agar judul tidak ikut diinput
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        return new Ptkp([
            'nama'      => $row[0], // kolom A
            'kategori'    => $row[1], // kolom B
            'nilai'  => $row[2] ?? null, // kolom C
        ]);
    }
}

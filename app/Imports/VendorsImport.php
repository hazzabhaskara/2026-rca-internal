<?php

namespace App\Imports;

use App\Vendors;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Str;

class VendorsImport implements ToModel, WithStartRow
{
    // Mulai dari baris ke-2 agar judul tidak ikut diinput
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        return new Vendors([
            'kd_vendor'      => $row[0] ?? 'VEN-' . Str::upper(Str::random(5)), // kolom A
            'nama_vendors'    => $row[1], // kolom B
            'contact_person'  => $row[2] ?? null, // kolom C
            'alamat'          => $row[3] ?? null, // kolom D
            'telepon'         => $row[4] ?? null, // kolom E
            'email'           => $row[5] ?? null, // kolom F
            'payment_terms'   => $row[6] ?? null, // kolom G
        ]);
    }
}

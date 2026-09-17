<?php

namespace App\Imports;

use App\Customers;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomersImport implements ToModel, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function model(array $row)
    {
        return new Customers([
            'kd_customers'      => $row['kode_customers'],
            'nama_customers'      => $row['nama_customers'],
            'contact_person' => $row['contact'] ?? null,
            'alamat' => $row['alamat'] ?? null,
            'telepon' => $row['telepon'] ?? null,
            'email' => $row['email'] ?? null,
            'limit_kredit' => $row['limit_kredit'] ?? null,
            'payment_terms' => $row['payment_terms'] ?? null
        ]);
    }
}

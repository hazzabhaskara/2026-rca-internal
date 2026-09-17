<?php

namespace App\Exports;

use App\Customers;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomersExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Customers::select(
            'kd_customers',
            'nama_customers',
            'contact_person',
            'alamat',
            'telepon',
            'email',
            'limit_kredit',
            'payment_terms',
        )->get();
    }
    public function headings(): array
    {
        return [
            'kode customers',
            'nama customers',
            'contact person',
            'alamat',
            'telepon',
            'email',
            'limit_kredit',
            'payment_terms',
        ];
    }
}

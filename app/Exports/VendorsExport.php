<?php

namespace App\Exports;

use App\Vendors;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VendorsExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Vendors::select(
            'kd_vendor',
            'nama_vendors',
            'contact_person',
            'alamat',
            'telepon',
            'email',
            'payment_terms',
        )->get();
    }
    public function headings(): array
    {
        return [
            'kode Vendors',
            'nama Vendors',
            'contact person',
            'alamat',
            'telepon',
            'email',
            'payment_terms',
        ];
    }
}

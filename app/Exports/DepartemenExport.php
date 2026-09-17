<?php

namespace App\Exports;

use App\Departement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DepartemenExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Departement::select(
            'kode',
            'deskripsi',
        )->get();
    }
    public function headings(): array
    {
        return [
            'kode_departemen',
            'nama_departemen',
        ];
    }
}

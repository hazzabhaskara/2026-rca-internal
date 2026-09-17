<?php

namespace App\Exports;

use App\Ptkp;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PtkpExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Ptkp::select(
            'nama',
            'kategori',
            'nilai'
        )->get();
    }
    public function headings(): array
    {
        return [
            'nama',
            'kategori',
            'nilai'
        ];
    }
}

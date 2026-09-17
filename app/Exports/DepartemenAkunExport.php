<?php

namespace App\Exports;

use App\DepartemenAkun;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DepartemenAkunExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DepartemenAkun::with(['departemen', 'chartOfAccount'])->get();
    }

    public function headings(): array
    {
        return [
            'Kode Departemen',
            'Nama Departemen',
            'Kode Akun',
            'Nama Akun',
        ];
    }

    public function map($row): array
    {
        return [
            $row->departemen->kode ?? '',
            $row->departemen->deskripsi ?? '',
            $row->chartOfAccount->kode_akun ?? '',
            $row->chartOfAccount->nama_akun ?? '',
        ];
    }
}

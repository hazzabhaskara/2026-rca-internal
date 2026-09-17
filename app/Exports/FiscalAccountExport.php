<?php

namespace App\Exports;

use App\FiscalAccount;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FiscalAccountExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return FiscalAccount::select(
            'kode_akun',
            'nama_akun',
        )->get();
    }
    public function headings(): array
    {
        return [
            'Kode Akun',
            'Nama Akun',
        ];
    }
}

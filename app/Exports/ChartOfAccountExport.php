<?php

namespace App\Exports;

use App\ChartOfAccount;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ChartOfAccountExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return chartOfAccount::select(
            'kode_akun',
            'nama_akun',
            'tipe_akun',
            'level_akun',
        )->get();
    }
    public function headings(): array
    {
        return [
            'kode_akun',
            'nama_akun',
            'tipe_akun',
            'level_akun',
        ];
    }
}

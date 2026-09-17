<?php

namespace App\Exports;

use App\KlasifikasiAkun;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KlasifikasiAkunExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return KlasifikasiAkun::with('numberingAccount')->get()->map(function ($item) {
            return [
                'Kode Klasifikasi' => $item->kode_klasifikasi,
                'Nama Klasifikasi' => $item->nama_klasifikasi,
                'Deskripsi' => $item->deskripsi,

            ];
        });
    }

    public function headings(): array
    {
        return [
            'Kode Klasifikasi',
            'Nama Klasifikasi',
            'Deskripsi',

        ];
    }
}

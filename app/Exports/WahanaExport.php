<?php

namespace App\Exports;

use App\Wahana;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WahanaExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = wahana::with('unitKerja')->get();

        return $data->map(function ($item) {
            return [
                $item->unitKerja->nama_unit ?? '-', // Ambil nama unit kerja, fallback '-'
                $item->kode_wahana,
                $item->nama_wahana,
                $item->status,
                $item->kapasitas,
                $item->kategori,
            ];
        });
    }
    public function headings(): array
    {
        return [
            'Unit Kerja',
            'Kode Wahana',
            'Nama Wahana',
            'Status',
            'Kapasitas',
            'Kategori'
        ];
    }
}

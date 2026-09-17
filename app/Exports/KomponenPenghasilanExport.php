<?php

namespace App\Exports;

use App\KomponenPenghasilan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KomponenPenghasilanExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = KomponenPenghasilan::with('levelKaryawan')->get();

        return $data->map(function ($item) {
            return [
                $item->nama_komponen,
                $item->tipe,
                $item->kategori,
                $item->sifat,
                $item->periode_perhitungan,
                $item->status_komponen,
                $item->levelKaryawan->nama_level
            ];
        });
    }
    public function headings(): array
    {
        return [
            'Nama Komponen',
            'Tipe',
            'Katgeori',
            'Sifat',
            'Periode Perhitungan',
            'Status Komponen',
            'Level Karyawan'
        ];
    }
}

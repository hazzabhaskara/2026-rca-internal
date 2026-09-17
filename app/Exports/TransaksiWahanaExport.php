<?php

namespace App\Exports;

use App\TransaksiWahana;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransaksiWahanaExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        //
        $data = TransaksiWahana::with('unitKerja', 'wahana', 'jenisHari')->get();

        return $data->map(function ($item) {
            return [
                $item->unitKerja->nama_unit ?? '-',
                $item->wahana->nama_wahana ?? '-',
                $item->jenisHari->nama,
                $item->tanggal ?? '-',
                $item->realisasi,
                $item->jumlah_pengunjung,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Unit',
            'Nama Wahana',
            'Jenis Hari',
            'Tanggal',
            'Realisasi',
            'Jumlah Pengunjung',
        ];
    }
}

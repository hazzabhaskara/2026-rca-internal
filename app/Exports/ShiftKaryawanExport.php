<?php

namespace App\Exports;

use App\ShiftKaryawanWahana;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ShiftKaryawanExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = ShiftKaryawanWahana::with('unitKerja', 'karyawan', 'wahana', 'jenisHari')->get();

        return $data->map(function ($item) {
            return [
                $item->karyawan->nama_karyawan,
                $item->unit->nama_unit ?? '-',
                $item->karyawan->levelKaryawan->nama_level ?? '-',
                $item->wahana->nama_wahana ?? '-',
                $item->tanggal,
                $item->jenisHari->nama,
                $item->jam_mulai,
                $item->jam_selesai,
                $item->lama_jam,
                $item->persentase_jam,
                $item->status,
                $item->keterangan ?? 'Tidak Ada'
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'Nama Unit',
            'Level Karyawan',
            'Nama Wahana',
            'Tanggal',
            'Jenis Hari',
            'Jam Mulai',
            'Jam Selesai',
            'Lama Jam',
            'Persentase Jam',
            'Status',
            'Keterangan'
        ];
    }
}

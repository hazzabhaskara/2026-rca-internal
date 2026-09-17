<?php

namespace App\Exports;

use App\Targetunit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TargetUnitExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $data = Targetunit::with('unit', 'komponen', 'levelKaryawan')->get();

        return $data->map(function ($item) {
            return [
                $item->unit->nama_unit ?? '-',
                $item->komponen->nama_komponen ?? '-',
                $item->target_bulanan,
                $item->levelKaryawan->nama_level ?? '-',
                $item->besaran_nominal,
                $item->tahun,
                $item->bulan,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Unit',
            'Nama Komponen',
            'Target Bulanan',
            'Level Karyawan',
            'Besaran Nominal',
            'Tahun',
            'Bulan'
        ];
    }
}

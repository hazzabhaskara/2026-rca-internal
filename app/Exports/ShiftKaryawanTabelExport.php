<?php

namespace App\Exports;

use App\ShiftKaryawanWahana;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ShiftKaryawanTabelExport implements WithMultipleSheets
{
    protected $tgl_awal;
    protected $tgl_akhir;

    public function __construct($tgl_awal, $tgl_akhir)
    {
        $this->tgl_awal = $tgl_awal;
        $this->tgl_akhir = $tgl_akhir;
    }

    public function sheets(): array
    {

        $dates = ShiftKaryawanWahana::whereBetween('tanggal', [$this->tgl_awal, $this->tgl_akhir])
            ->distinct()
            ->orderBy('tanggal')
            ->pluck('tanggal');
        // dd($this->tgl_awal, $this->tgl_akhir, $dates);


        $sheets = [];

        foreach ($dates as $date) {
            $sheets[] = new ShiftKaryawanPerTanggalExport($date);
        }

        return $sheets;
    }
}

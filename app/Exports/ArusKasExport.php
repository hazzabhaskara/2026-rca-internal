<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ArusKasExport implements FromView
{
    protected $rows, $tanggalAwal, $tanggalAkhir, $displayMode;

    public function __construct($rows, $tanggalAwal, $tanggalAkhir, $displayMode)
    {
        $this->rows         = $rows;
        $this->tanggalAwal  = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
        $this->displayMode  = $displayMode;
    }

    public function view(): View
    {
        return view('arus_kas.export_excel', [
            'rows'         => $this->rows,
            'tanggalAwal'  => $this->tanggalAwal,
            'tanggalAkhir' => $this->tanggalAkhir,
            'displayMode'  => $this->displayMode,
        ]);
    }
}

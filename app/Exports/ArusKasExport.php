<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ArusKasExport implements FromView
{
    protected $rows;
    protected $tanggalAwal;
    protected $tanggalAkhir;
    protected $displayMode;
    protected $aktivitasData;
    protected $saldoAwal;
    protected $saldoAkhir;
    protected $grandCashIn;
    protected $grandCashOut;
    protected $netCashFlow;

    public function __construct(
        $rows,
        $tanggalAwal,
        $tanggalAkhir,
        $displayMode,
        $aktivitasData = [],
        $saldoAwal = 0.0,
        $saldoAkhir = 0.0,
        $grandCashIn = 0.0,
        $grandCashOut = 0.0,
        $netCashFlow = 0.0
    ) {
        $this->rows          = $rows;
        $this->tanggalAwal   = $tanggalAwal;
        $this->tanggalAkhir  = $tanggalAkhir;
        $this->displayMode   = $displayMode;
        $this->aktivitasData = $aktivitasData;
        $this->saldoAwal     = $saldoAwal;
        $this->saldoAkhir    = $saldoAkhir;
        $this->grandCashIn   = $grandCashIn;
        $this->grandCashOut  = $grandCashOut;
        $this->netCashFlow   = $netCashFlow;
    }

    public function view(): View
    {
        return view('arus_kas.export_excel', [
            'rows'          => $this->rows,
            'tanggalAwal'   => $this->tanggalAwal,
            'tanggalAkhir'  => $this->tanggalAkhir,
            'displayMode'   => $this->displayMode,
            'aktivitasData' => $this->aktivitasData,
            'saldoAwal'     => $this->saldoAwal,
            'saldoAkhir'    => $this->saldoAkhir,
            'grandCashIn'   => $this->grandCashIn,
            'grandCashOut'  => $this->grandCashOut,
            'netCashFlow'   => $this->netCashFlow,
        ]);
    }
}

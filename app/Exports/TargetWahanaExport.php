<?php

namespace App\Exports;

use App\TargetWahana;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

use Maatwebsite\Excel\Concerns\FromCollection;

class TargetWahanaExport implements FromView
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $query = TargetWahana::with(['wahana.unitKerja', 'jenis_hari']);

        if ($this->request->filled('unit_id')) {
            $query->whereHas('wahana', fn($q) => $q->where('unit_kerja_id', $this->request->unit_id));
        }
        if ($this->request->filled('wahana_id')) {
            $query->where('wahana_id', $this->request->wahana_id);
        }
        if ($this->request->filled('jenis_hari_id')) {
            $query->where('jenis_hari_id', $this->request->jenis_hari_id);
        }
        if ($this->request->filled('tahun')) {
            $query->where('tahun', $this->request->tahun);
        }
        if ($this->request->filled('bulan')) {
            $query->where('bulan', $this->request->bulan);
        }

        $results = $query->get();

        return view('report.target_wahana.excel', compact('results'));
    }
}

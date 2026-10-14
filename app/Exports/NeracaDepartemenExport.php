<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class NeracaDepartemenExport implements FromView, ShouldAutoSize, WithTitle, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        $viewName = ($this->data['mode'] ?? 'matrix') === 'single'
            ? 'neraca_departemen.export_excel_single'
            : 'neraca_departemen.export_excel_matrix';

        return view($viewName, $this->data);
    }

    public function title(): string
    {
        $titleStr = ($this->data['mode'] ?? 'matrix') === 'single'
            ? 'Neraca - ' . ($this->data['singleDept']->deskripsi ?? 'Departemen')
            : 'Neraca Departemen Matriks';

        // Excel sheet title max 31 characters
        return mb_substr($titleStr, 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                if (($this->data['mode'] ?? 'matrix') === 'matrix') {
                    // Freeze columns A (Nama Akun) & B (Konsolidasi) and rows 1-5 -> cell C6
                    $event->sheet->getDelegate()->freezePane('C6');
                } else {
                    // Single mode: freeze column A and header rows 1-5 -> cell B6
                    $event->sheet->getDelegate()->freezePane('B6');
                }
            },
        ];
    }
}

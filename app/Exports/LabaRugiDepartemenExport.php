<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class LabaRugiDepartemenExport implements FromView, ShouldAutoSize, WithTitle, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        $viewName = ($this->data['mode'] ?? 'matrix') === 'single'
            ? 'laba_rugi_departemen.export_excel_single'
            : 'laba_rugi_departemen.export_excel_matrix';

        return view($viewName, $this->data);
    }

    public function title(): string
    {
        $titleStr = ($this->data['mode'] ?? 'matrix') === 'single'
            ? 'Laba Rugi - ' . ($this->data['singleDept']->deskripsi ?? 'Departemen')
            : 'Laba Rugi Matriks';

        // Excel sheet title limit is 31 characters
        return mb_substr($titleStr, 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                if (($this->data['mode'] ?? 'matrix') === 'matrix') {
                    // Freeze columns A & B and rows 1-5
                    $event->sheet->getDelegate()->freezePane('C6');
                } else {
                    // Single mode: freeze column A and rows 1-5
                    $event->sheet->getDelegate()->freezePane('B6');
                }
            },
        ];
    }
}

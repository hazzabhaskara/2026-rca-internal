<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekonsiliasiClosingExport implements FromView, ShouldAutoSize, WithTitle, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('rekonsiliasi_closing.export_excel', $this->data);
    }

    public function title(): string
    {
        $deptSuffix = !empty($this->data['activeDepartment'])
            ? ' - ' . $this->data['activeDepartment']->deskripsi
            : ' - Semua Departemen';

        return 'Rekonsiliasi Closing' . $deptSuffix;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['italic' => true, 'size' => 10]],
            4 => ['font' => ['italic' => true, 'size' => 10]],
        ];
    }
}

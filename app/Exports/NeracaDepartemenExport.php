<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class NeracaDepartemenExport implements FromView, ShouldAutoSize, WithTitle
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
        return ($this->data['mode'] ?? 'matrix') === 'single'
            ? 'Neraca - ' . ($this->data['singleDept']->deskripsi ?? 'Departemen')
            : 'Neraca Departemen Matriks';
    }
}

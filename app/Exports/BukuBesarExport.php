<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BukuBesarExport implements FromView, ShouldAutoSize, WithTitle, WithColumnFormatting
{
    protected $rows;
    protected $saldoAwalPerAkun;
    protected $groupedByAccount;
    protected $totalByType;
    protected $start_date;
    protected $end_date;
    protected $departemen_name;
    protected $selected_departemen_id;
    protected $showComment;
    protected $accounts;
    protected $show_lawan;

    public function __construct(
        $rows,
        $saldoAwalPerAkun,
        $groupedByAccount,
        $totalByType,
        $start_date,
        $end_date,
        $departemen_name = 'Semua Departemen (Konsolidasi)',
        $selected_departemen_id = null,
        $showComment = 'line_comment',
        $accounts = null,
        $show_lawan = false
    ) {
        $this->rows                   = $rows;
        $this->saldoAwalPerAkun       = $saldoAwalPerAkun;
        $this->groupedByAccount       = $groupedByAccount;
        $this->totalByType            = $totalByType;
        $this->start_date             = $start_date;
        $this->end_date               = $end_date;
        $this->departemen_name        = $departemen_name;
        $this->selected_departemen_id = $selected_departemen_id;
        $this->showComment            = $showComment;
        $this->accounts               = $accounts;
        $this->show_lawan             = (bool) $show_lawan;
    }

    public function view(): View
    {
        $isConsolidated = empty($this->selected_departemen_id) || $this->selected_departemen_id === 'all';

        return view('buku_besar.export_excel', [
            'rows'                   => $this->rows,
            'saldoAwalPerAkun'       => $this->saldoAwalPerAkun,
            'groupedByAccount'       => $this->groupedByAccount,
            'totalByType'            => $this->totalByType,
            'start_date'             => $this->start_date,
            'end_date'               => $this->end_date,
            'departemen_name'        => $this->departemen_name,
            'selected_departemen_id' => $this->selected_departemen_id,
            'is_consolidated'        => $isConsolidated,
            'showComment'            => $this->showComment,
            'accounts'               => $this->accounts,
            'show_lawan'             => $this->show_lawan,
        ]);
    }

    public function title(): string
    {
        return 'Buku Besar';
    }

    public function columnFormats(): array
    {
        $isConsolidated = empty($this->selected_departemen_id) || $this->selected_departemen_id === 'all';
        if ($this->show_lawan) {
            if ($isConsolidated) {
                return [
                    'F' => '#,##0.00',
                    'G' => '#,##0.00',
                    'H' => '#,##0.00',
                ];
            }
            return [
                'E' => '#,##0.00',
                'F' => '#,##0.00',
                'G' => '#,##0.00',
            ];
        }

        if ($isConsolidated) {
            return [
                'E' => '#,##0.00',
                'F' => '#,##0.00',
                'G' => '#,##0.00',
            ];
        }

        return [
            'D' => '#,##0.00',
            'E' => '#,##0.00',
            'F' => '#,##0.00',
        ];
    }
}

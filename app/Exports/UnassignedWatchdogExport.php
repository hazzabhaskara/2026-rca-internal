<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class UnassignedWatchdogExport implements FromView, ShouldAutoSize, WithTitle
{
    /**
     * Data payload containing unassigned transactions and filter metadata.
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new export instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Return the Blade view for Excel rendering.
     *
     * @return View
     */
    public function view(): View
    {
        return view('unassigned_watchdog.export_excel', $this->data);
    }

    /**
     * Set the worksheet title.
     *
     * @return string
     */
    public function title(): string
    {
        return 'Unassigned Watchdog Audit';
    }
}

<?php

namespace App\Exports;

use App\TaxRates;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TaxRatesExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return TaxRates::select(
            'ptkp_id',
            'min_penghasilan',
            'max_penghasilan',
            'tarif_ter'
        )->get();
    }
    public function headings(): array
    {
        return [
            'ptkp_id',
            'min_penghasilan',
            'max_penghasilan',
            'tarif_ter'
        ];
    }
}

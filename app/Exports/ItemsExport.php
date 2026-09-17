<?php

namespace App\Exports;

use App\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ItemsExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Item::select(
            'item_number',
            'item_name',
            'item_description',
            'unit',
            'base_price',
            'tax_rate',
            'is_active',
            'category_id',
            'brand',
            'stock_quantity',
            'purchase_price',
        )->get();
    }
    public function headings(): array
    {
        return [
            'item_number',
            'item_name',
            'item_description',
            'unit',
            'base_price',
            'tax_rate',
            'is_active',
            'category_id',
            'brand',
            'stock_quantity',
            'purchase_price',
        ];
    }
}

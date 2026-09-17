<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryStatistic extends Model
{
    //
    protected $fillable = [
        'inventory_id',
        'location',
        'ytd_transactions',
        'ytd_units',
        'ytd_amount',
        'ytd_cogs',
        'last_year_transactions',
        'last_year_units',
        'last_year_amount',
        'last_year_cogs',
        'last_sale_date',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}

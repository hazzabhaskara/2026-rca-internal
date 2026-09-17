<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryTaxes extends Model
{
    //
    protected $fillable = [
        'inventory_id',
        'tax_name',
        'is_exempt',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}

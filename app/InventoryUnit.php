<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryUnit extends Model
{
    //
    protected $fillable = [
        'inventory_id',
        'selling_same_as_stocking',
        'selling_unit',
        'selling_relationship',
        'buying_same_as_stocking',
        'buying_unit',
        'buying_relationship',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}

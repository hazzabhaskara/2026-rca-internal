<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryPrice extends Model
{
    //
    protected $fillable = [
        'inventory_id',
        'price_list_name',
        'price',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}

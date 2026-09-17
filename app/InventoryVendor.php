<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryVendor extends Model
{
    //
    protected $fillable =
    [
        'inventory_id',
        'vendor_id',
        'vendor_contact',
    ];
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
    public function vendor()
    {
        return $this->belongsTo(Vendors::class);
    }
}

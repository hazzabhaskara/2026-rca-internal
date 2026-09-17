<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemVendor extends Model
{
    //
    protected $fillable =
    [
        'item_id',
        'vendor_id',
        'vendor_contact',
    ];
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function vendor()
    {
        return $this->belongsTo(Vendors::class);
    }
}

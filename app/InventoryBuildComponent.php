<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryBuildComponent extends Model
{
    //
    protected $fillable = [
        'inventory_build_id',
        'item_id',
        'unit',
        'description',
        'quantity',
    ];

    public function build()
    {
        return $this->belongsTo(InventoryBuild::class, 'inventory_build_id');
    }
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryBuild extends Model
{
    //
    protected $fillable = [
        'inventory_id',
        'build_quantity',
        'additional_costs',
        'cost_account',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function components()
    {
        return $this->hasMany(InventoryBuildComponent::class);
    }
}

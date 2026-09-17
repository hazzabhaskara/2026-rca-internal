<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    //
    protected $fillable = [
        'name',
        'stocking_unit',
        'description',
        'picture_path',
        'thumbnail_path'
    ];
    public function quantities()
    {
        return $this->hasMany(InventoryQuantity::class);
    }

    public function units()
    {
        return $this->hasOne(InventoryUnit::class);
    }

    public function prices()
    {
        return $this->hasMany(InventoryPrice::class);
    }

    public function vendors()
    {
        return $this->hasMany(InventoryVendor::class);
    }

    public function accounts()
    {
        return $this->hasOne(InventoryAccount::class);
    }

    public function builds()
    {
        return $this->hasMany(InventoryBuild::class);
    }

    public function statistics()
    {
        return $this->hasOne(InventoryStatistic::class);
    }

    public function taxes()
    {
        return $this->hasMany(InventoryTaxes::class);
    }
}

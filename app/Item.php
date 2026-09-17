<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    //
    protected $fillable = [
        'item_number',
        'item_description',
        'description',
        'type',
        'picture_path',
        'thumbnail_path',
    ];
    public function quantities()
    {
        return $this->hasMany(ItemQuantities::class);
    }

    public function units()
    {
        return $this->hasOne(ItemUnit::class);
    }

    public function prices()
    {
        return $this->hasMany(ItemPrice::class);
    }

    public function vendors()
    {
        return $this->hasOne(ItemVendor::class, 'item_id');
    }


    public function accounts()
    {
        return $this->hasOne(ItemAccount::class);
    }

    public function builds()
    {
        return $this->hasMany(ItemBuild::class);
    }

    public function taxes()
    {
        return $this->hasMany(ItemTaxes::class);
    }
    public function assemblies()
    {
        return $this->hasMany(ItemAssemblie::class, 'parent_item_id');
    }

    public function assemblyComponents()
    {
        return $this->hasMany(ItemAssemblieDetail::class, 'component_item_id');
    }
    public function transferInventory()
    {
        return $this->hasMany(TransferInventoryDetail::class, 'component_item_id');
    }
    public function buildofbomComponents()
    {
        return $this->hasMany(BuildOfBomDetail::class, 'component_item_id');
    }
}

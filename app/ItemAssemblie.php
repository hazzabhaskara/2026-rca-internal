<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemAssemblie extends Model
{
    //
    protected $fillable = [
        'date',
        'parent_item_id',
        'qty_built',
        'total_cost',
        'status',
        'notes',
        'from_location_id',
    ];

    /**
     * Relasi ke produk jadi (parent item).
     */
    public function parentItem()
    {
        return $this->belongsTo(Item::class, 'parent_item_id');
    }
    public function LocationInventory()
    {
        return $this->belongsTo(LocationInventory::class, 'from_location_id');
    }

    /**
     * Relasi ke detail komponen.
     */
    public function details()
    {
        return $this->hasMany(ItemAssemblieDetail::class, 'item_assembly_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemAssemblieDetail extends Model
{
    //
    protected $fillable = [
        'item_assembly_id',
        'component_item_id',
        'unit',
        'qty_used',
        'unit_cost',
        'total_cost',
    ];

    /**
     * Relasi ke header assembly.
     */
    public function assembly()
    {
        return $this->belongsTo(ItemAssemblie::class, 'item_assembly_id');
    }

    /**
     * Relasi ke komponen item.
     */
    public function component()
    {
        return $this->belongsTo(Item::class, 'component_item_id');
    }
}

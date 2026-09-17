<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransferInventoryDetail extends Model
{
    //

    protected $fillable = [
        'transfer_inventory_id',
        'component_item_id',
        'unit',
        'qty',
        'amount',
        'unit_cost'
    ];

    public function transferInventory()
    {
        return $this->belongsTo(TransferInventory::class, 'transfer_inventory_id');
    }

    /**
     * Relasi ke komponen item.
     */
    public function component()
    {
        return $this->belongsTo(Item::class, 'component_item_id');
    }
}

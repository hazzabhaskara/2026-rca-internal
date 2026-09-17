<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryQuantity extends Model
{
    //
    protected $fillable = [
        'inventory_id',
        'location',
        'on_hand_qty',
        'on_hand_value',
        'pending_orders_qty',
        'pending_orders_value',
        'purchase_order_qty',
        'sales_order_qty',
        'reorder_minimum',
        'reorder_to_order',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemQuantities extends Model
{
    //
    protected $fillable = [
        'item_id',
        'location_id',
        'on_hand_qty',
        'on_hand_value',
        'pending_orders_qty',
        'pending_orders_value',
        'purchase_order_qty',
        'sales_order_qty',
        'reorder_minimum',
        'reorder_to_order',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}

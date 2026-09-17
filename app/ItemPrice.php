<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemPrice extends Model
{
    //
    protected $fillable = [
        'item_id',
        'price_list_name',
        'price',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemUnit extends Model
{
    //
    protected $fillable = [
        'item_id',
        'selling_same_as_stocking',
        'selling_unit',
        'selling_relationship',
        'buying_same_as_stocking',
        'buying_unit',
        'buying_relationship',
        'unit_of_measure'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}

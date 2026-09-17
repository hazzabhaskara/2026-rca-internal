<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemTaxes extends Model
{
    //
    protected $fillable = [
        'item_id',
        'tax_name',
        'is_exempt',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}

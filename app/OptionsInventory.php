<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OptionsInventory extends Model
{
    //

    protected $fillable =
    [
        'costing_method',
        'profit_eval_method',
        'sort_inventory_service',
        'allow_below_zero',
    ];
}

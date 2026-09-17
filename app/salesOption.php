<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class salesOption extends Model
{
    //
    protected $fillable = [
        'aging_second_period',
        'aging_third_period',
        'discount_type'
    ];
}

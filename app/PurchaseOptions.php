<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseOptions extends Model
{
    //
    protected $fillable = [
        'aging_first_period',
        'aging_second_period',
        'aging_third_period',
    ];
}

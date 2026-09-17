<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SettingDepartement extends Model
{
    //

    //
    protected $fillable = [
        'key',
        'value',
    ];

    public $timestamps = true;
}

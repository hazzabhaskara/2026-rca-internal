<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StartNewYear extends Model
{
    //
    protected $fillable = [
        'tahun',
        'awal_periode',
        'akhir_periode',
        'status'
    ];
}

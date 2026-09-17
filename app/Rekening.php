<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    //
    protected $fillable =
    [
        'atas_nama',
        'nama_bank',
        'no_rek'
    ];
}

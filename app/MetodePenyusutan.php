<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetodePenyusutan extends Model
{
    //
    protected $fillable = [
        'nama_metode',
        'deskripsi'
    ];
}

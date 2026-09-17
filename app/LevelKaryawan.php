<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LevelKaryawan extends Model
{
    //
    protected $fillable = [
        'nama_level',
        'deskripsi'
    ];
}

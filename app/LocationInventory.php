<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationInventory extends Model
{
    //
    protected $fillable = [
        'kode_lokasi',
        'deskripsi',
        'status'
    ];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    //

    protected $fillable = [
        'kode_kategori',
        'nama_kategori',
        'deskripsi',
        'status'
    ];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KategoriAsset extends Model
{
    //
    protected $fillable =
    [
        'kode_kategori',
        'nama_kategori',
        'deskripsi'
    ];
}

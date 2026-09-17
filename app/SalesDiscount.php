<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesDiscount extends Model
{
    //
    protected $fillable =
    [
        'nama_diskon',
        'jenis_diskon',
        'deskripsi',
        'aktif',
    ];

    public function details()
    {
        return $this->hasMany(SalesDiscountDetail::class);
    }
}

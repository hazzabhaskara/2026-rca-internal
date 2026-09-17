<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesDiscountDetail extends Model
{
    //
    protected $fillable = [
        'sales_discount_id',
        'hari_ke',
        'tipe_nilai',
        'nilai_diskon',
        'urutan'
    ];
    public function discount()
    {
        return $this->belongsTo(SalesDiscount::class, 'sales_discount_id');
    }
}

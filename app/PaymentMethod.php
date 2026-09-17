<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    //
    protected $fillable = [
        'kode_jenis',
        'nama_jenis',
        'status',
        // untuk membedakan tahapan dalam invoice, entah itu sales atau purchase
        'status_payment',
    ];
    public function details()
    {
        return $this->hasMany(PaymentMethodDetail::class, 'payment_method_id');
    }
}

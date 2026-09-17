<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentMethodDetail extends Model
{
    //
    protected $fillable = [
        'payment_method_id',
        'account_id',
        'deskripsi',
        'is_default',
    ];

    /**
     * Relasi ke PaymentMethod
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
     * Relasi ke Chart Of Account (COA)
     */
    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}

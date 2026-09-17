<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesDeposit extends Model
{
    //
    protected $fillable = [
        'deposit_no',
        'jenis_pembayaran_id',
        'account_id',
        'account_deposit',
        'deposit_date',
        'customer_id',
        'deposit_reference',
        'deposit_amount',
        'comment'
    ];
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }
    public function account()
    {
        return $this->belongsTo(chartOfAccount::class, 'account_id');
    }
    public function jenis_pembayaran()
    {
        return $this->belongsTo(PaymentMethod::class, 'jenis_pembayaran_id');
    }

    public function details()
    {
        return $this->hasMany(SalesDepositDetail::class, 'deposit_id');
    }
    public function accountDeposit()
    {
        return $this->belongsTo(chartOfAccount::class, 'account_deposit');
    }
}

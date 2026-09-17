<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //
    protected $fillable = [
        'jenis_pembayaran_id',
        'payment_method_account_id',
        'source',
        'vendor_id',
        'payment_date',
        'comment',
        'type',
    ];
    public function vendor()
    {
        return $this->belongsTo(Vendors::class);
    }
    public function jenis_pembayaran()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
    public function details()
    {
        return $this->hasMany(PaymentDetail::class, 'payment_id');
    }
    public function PaymentMethodAccount()
    {
        return $this->belongsTo(PaymentMethodDetail::class);
    }
    public function prepaymentAllocations()
    {
        return $this->hasMany(PrepaymentAllocation::class);
    }
}

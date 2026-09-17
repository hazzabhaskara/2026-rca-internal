<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Prepayment extends Model
{
    //
    protected $fillable = [
        'tanggal_prepayment',
        'reference',
        'vendor_id',
        'account_id',
        'account_prepayment',
        'amount',
        'comment',
    ];


    public function vendor()
    {
        return $this->belongsTo(Vendors::class, 'vendor_id');
    }
    public function account()
    {
        return $this->belongsTo(chartOfAccount::class, 'account_id');
    }
    public function allocations()
    {
        return $this->hasMany(PrepaymentAllocation::class);
    }
    public function accountPrepayment()
    {
        return $this->belongsTo(chartOfAccount::class, 'account_prepayment');
    }
    public function prepaymentAllocation()
    {
        return $this->hasMany(PrepaymentAllocation::class, 'prepayment_id');
    }
    public function getSisaPrepaymentAttribute()
    {
        $allocated = $this->prepaymentAllocation->sum('allocated_amount');
        return $this->amount - $allocated;
    }
}

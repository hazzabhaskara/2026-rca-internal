<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesDepositDetail extends Model
{
    //
    protected $fillable = [
        'invoice_date',
        'deposit_id',
        'sales_invoice_id',
        'original_amount',
        'discount_available',
        'discount_taken',
    ];

    public function deposit()
    {
        return $this->belongsTo(SalesDeposit::class, 'deposit_id');
    }

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }
}

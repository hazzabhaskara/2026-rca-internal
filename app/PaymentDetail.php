<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    //

    protected $fillable = [
        'payment_id',
        'invoice_number_id',
        'due_date',
        'original_amount',
        'amount_owing',
        'discount_available',
        'discount_taken',
        'payment_amount',
        'account_id',
        'description',
        'tax',
        'allocation'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function account()
    {
        return $this->belongsTo(chartOfAccount::class);
    }
    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'invoice_number_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PrepaymentAllocation extends Model
{
    //
    protected
        $fillable = [
            'payment_id',
            'prepayment_id',
            'purchase_invoice_id',
            'allocated_amount',
        ];

    public function prepayment()
    {
        return $this->belongsTo(Prepayment::class);
    }
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }
}

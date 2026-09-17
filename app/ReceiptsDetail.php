<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReceiptsDetail extends Model
{
    //
    protected $fillable = [
        'receipt_id',
        'sales_invoice_id',
        'invoice_date',
        'original_amount',
        'amount_owing',
        'discount_available',
        'discount_taken',
        'amount_received',
    ];


    public function receipt()
    {
        return $this->belongsTo(Receipts::class);
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }
}

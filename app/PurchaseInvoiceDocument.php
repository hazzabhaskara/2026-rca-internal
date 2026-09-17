<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceDocument extends Model
{
    //
    protected $fillable = [
        'purchase_invoice_id',
        'document_name',
        'file_path',
        'file_type',
        'file_size',
        'description',
    ];

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }
}

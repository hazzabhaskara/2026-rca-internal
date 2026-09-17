<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceDocument extends Model
{
    //
    protected $fillable = [
        'sales_invoice_id',
        'document_name',
        'file_path',
        'file_type',
        'file_size',
        'description',
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(salesInvoice::class);
    }
}

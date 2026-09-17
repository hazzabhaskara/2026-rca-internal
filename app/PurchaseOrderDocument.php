<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDocument extends Model
{
    //
    protected $fillable = [
        'purchase_order_id',
        'document_name',
        'file_path',
        'file_type',
        'file_size',
        'description',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}

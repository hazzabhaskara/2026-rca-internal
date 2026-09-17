<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesOrderDocument extends Model
{
    //
    protected $fillable = [
        'sales_order_id',
        'document_name',
        'file_path',
        'file_type',
        'file_size',
        'description',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }
}

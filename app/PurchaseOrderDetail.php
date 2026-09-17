<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    //

    protected $fillable = [

        'purchase_order_id',
        'item_id',
        'quantity',
        'order',
        'back_order',
        'unit',
        'item_description',
        'price',
        'tax_id',
        'tax_amount',
        'amount',
        'account_id',
        'discount'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    public function sales_taxes()
    {
        return $this->belongsTo(SalesTaxes::class, 'tax_id');
    }

    public function account()
    {
        return $this->belongsTo(chartOfAccount::class);
    }
}

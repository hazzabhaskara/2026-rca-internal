<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceDetail extends Model
{
    //
    protected $fillable = [
        'purchase_invoice_id',
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
        'project_id',
        'discount'
    ];

    public function PurchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function account()
    {
        return $this->belongsTo(chartOfAccount::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function sales_taxes()
    {
        return $this->belongsTo(SalesTaxes::class, 'tax_id');
    }
}

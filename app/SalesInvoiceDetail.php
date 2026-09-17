<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceDetail extends Model
{
    //
    protected $fillable
    = [
        'sales_invoice_id',
        'item_id',
        'quantity',
        'order_quantity',
        'back_order',
        'unit',
        'description',
        'base_price',
        'discount',
        'price',
        'amount',
        'tax',
        'status',
        'account_id',
        'project_id',
        'tax_id'
    ];

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function sales_taxes()
    {
        return $this->belongsTo(SalesTaxes::class, 'tax_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesTaxes extends Model
{
    //
    protected $fillable = [
        'name',
        'purchase_account_id',
        'sales_account_id',
        'active',
        'rate',
        'type',
        'kategori',
        'tax_code'
    ];

    public function purchaseAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'purchase_account_id');
    }
    public function salesAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'sales_account_id');
    }
}

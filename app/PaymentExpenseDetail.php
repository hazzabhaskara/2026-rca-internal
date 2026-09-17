<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentExpenseDetail extends Model
{
    //

    protected $fillable = [
        'payment_expense_id',
        'account_id',
        'amount',
        'tax',
        'deskripsi',
        'sales_taxes_id'
    ];

    public function Account()
    {
        return $this->belongsTo(chartOfAccount::class, 'account_id');
    }
    public function PaymentExpense()
    {
        return $this->belongsTo(PaymentExpense::class);
    }
    public function salesTaxes()
    {
        return $this->belongsTo(SalesTaxes::class, 'sales_taxes_id');
    }
}

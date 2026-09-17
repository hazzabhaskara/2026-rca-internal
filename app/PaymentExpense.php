<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentExpense extends Model
{
    //
    protected $fillable =
    [
        'from_account_id',
        'source',
        'date',
        'vendor_id',
        'notes',
    ];

    public function Vendor()
    {
        return $this->belongsTo(Vendors::class, 'vendor_id');
    }
    public function Account()
    {
        return $this->belongsTo(chartOfAccount::class, 'from_account_id');
    }

    public function details()
    {
        return $this->hasMany(PaymentExpenseDetail::class, 'payment_expense_id');
    }
}

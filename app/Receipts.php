<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Receipts extends Model
{
    //
    protected $fillable = [
        'receipt_number',
        'customer_id',
        'deposit_to_id',
        'date',
        'comment',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class);
    }

    public function depositTo()
    {
        return $this->belongsTo(ChartOfAccount::class, 'deposit_to_id');
    }

    public function details()
    {
        return $this->hasMany(ReceiptsDetail::class);
    }
}

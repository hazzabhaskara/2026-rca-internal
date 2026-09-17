<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    //
    protected $fillable = [
        'order_number',
        'date_order',
        'shipping_date',
        'customer_id',
        'jenis_pembayaran_id',
        'shipping_address',
        'sales_person_id',
        'freight',
        'early_payment_terms',
        'messages',
        'payment_method_account_id',
        'location_id',
        'status_sales'
    ];
    public function jenisPembayaran()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
    public function details()
    {
        return $this->hasMany(SalesOrderDetail::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customers::class);
    }

    public function salesPerson()
    {
        return $this->belongsTo(Employee::class, 'sales_person_id');
    }
    public function documents()
    {
        return $this->hasMany(SalesOrderDocument::class);
    }
    public function paymentMethodDetail()
    {
        return $this->belongsTo(PaymentMethodDetail::class);
    }
    public function invoices()
    {
        return $this->hasMany(SalesInvoice::class);
    }
    public function locationInventory()
    {
        return $this->belongsTo(LocationInventory::class, 'location_id');
    }
}

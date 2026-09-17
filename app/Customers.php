<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    //
    protected $fillable = [
        'kd_customers',
        'nama_customers',
        'contact_person',
        'alamat',
        'telepon',
        'email',
        'limit_kredit',
        'payment_terms',
    ];
    public function salesOrder()
    {
        return $this->hasMany(SalesOrder::class, 'customer_id');
    }
    public function salesInvoice()
    {
        return $this->hasMany(SalesInvoice::class, 'customers_id');
    }
}

<?php

namespace App;

use App\PurchaseOrder;
use App\PurchaseInvoice;


use Illuminate\Database\Eloquent\Model;

class Vendors extends Model
{
    //
    protected $fillable = [
        'kd_vendor',
        'nama_vendors',
        'contact_person',
        'alamat',
        'telepon',
        'email',
        'payment_terms',
    ];

    public function purchaseOrder()
    {
        return $this->hasMany(PurchaseOrder::class, 'vendor_id');
    }

    public function invoices()
    {
        return $this->hasMany(PurchaseInvoice::class, 'vendor_id');
    }
    public function prepayments()
    {
        return $this->hasMany(Prepayment::class, 'vendor_id');
    }
    public function payment()
    {
        return $this->hasMany(Payment::class, 'vendor_id');
    }
}

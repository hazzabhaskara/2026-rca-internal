<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesInvoice extends Model
{
    //
    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'sales_order_id',
        'customers_id',
        'jenis_pembayaran_id',
        'withholding_tax',
        'withholding_value',
        'shipping_address',
        'shipping_date',
        'sales_person_id',
        'location_id',
        'freight',
        'early_payment_terms',
        'messages',
        'payment_method_account_id',
        'status'
    ];
    public function jenisPembayaran()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
    public function withholding()
    {
        return $this->belongsTo(SalesTaxes::class, 'withholding_tax');
    }
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customers_id');
    }
    public function salesPerson()
    {
        return $this->belongsTo(Employee::class, 'sales_person_id');
    }
    public function details()
    {
        return $this->hasMany(SalesInvoiceDetail::class, 'sales_invoice_id');
    }
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }
    public function lokasi_inventory()
    {
        return $this->belongsTo(LocationInventory::class, 'location_id');
    }
    public function documents()
    {
        return $this->hasMany(SalesInvoiceDocument::class);
    }
    public function getOriginalAttribute()
    {
        $totalDetail = $this->details->sum('amount');
        $totalTax    = $this->details->sum('tax');
        $freight     = $this->freight ?? 0;
        $discount    = $this->details->sum('discount');

        return ($totalDetail + $totalTax + $freight) - $discount;
    }
}

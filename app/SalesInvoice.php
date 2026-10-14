<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesInvoice extends Model
{
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

    public function paymentMethodAccount()
    {
        return $this->belongsTo(PaymentMethodDetail::class, 'payment_method_account_id');
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

    public function receiptsDetails()
    {
        return $this->hasMany(ReceiptsDetail::class, 'sales_invoice_id');
    }

    public function getOriginalAttribute()
    {
        $totalDetail = (float) $this->details->sum('amount');
        $totalTax    = (float) $this->details->sum('tax');
        $freight     = (float) ($this->freight ?? 0);
        $withholding = (float) ($this->withholding_value ?? 0);

        return max(0, ($totalDetail + $totalTax + $freight) - $withholding);
    }

    public function getSisaTagihanAttribute()
    {
        $totalReceived = (float) $this->receiptsDetails()->sum('amount_received');
        $totalDiscount = (float) $this->receiptsDetails()->sum('discount_taken');

        return max(0, $this->original - ($totalReceived + $totalDiscount));
    }

    public function getDiscountInvoiceAttribute()
    {
        return 0;
    }

    public function updatePaymentStatus(): void
    {
        $totalSettled = (float) $this->receiptsDetails()->sum('amount_received')
            + (float) $this->receiptsDetails()->sum('discount_taken');
        $originalAmount = $this->original;

        if ($originalAmount > 0 && $totalSettled >= ($originalAmount - 0.01)) {
            $this->status = 3; // Lunas
        } elseif ($totalSettled > 0) {
            $this->status = 2; // Dibayar sebagian
        } else {
            $this->status = 1; // Belum dibayar (Open)
        }

        $this->save();

        if ($this->sales_order_id) {
            $salesOrder = SalesOrder::find($this->sales_order_id);
            if ($salesOrder) {
                $salesOrder->status_sales = $this->status;
                $salesOrder->save();
            }
        }
    }
}

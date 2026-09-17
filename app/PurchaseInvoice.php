<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    //
    protected $fillable = [
        'invoice_number',
        'date_invoice',
        'purchase_order_id',
        'jenis_pembayaran_id',
        'withholding_tax',
        'withholding_value',
        'shipping_date',
        'shipping_address',
        'freight',
        'early_payment_terms',
        'messages',
        'vendor_id',
        'payment_method_account_id',
        'location_id',
        'status_purchase',

    ];

    public function jenisPembayaran()
    {
        return $this->belongsTo(PaymentMethod::class, 'jenis_pembayaran_id');
    }
    public function withholding()
    {
        return $this->belongsTo(SalesTaxes::class, 'withholding_tax');
    }
    public function paymentmethodDetail()
    {
        return $this->belongsTo(PaymentMethodDetail::class, 'payment_method_account_id');
    }
    public function vendor()
    {
        return $this->belongsTo(Vendors::class, 'vendor_id');
    }
    public function details()
    {
        return $this->hasMany(PurchaseInvoiceDetail::class, 'purchase_invoice_id');
    }
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
    public function documents()
    {
        return $this->hasMany(PurchaseInvoiceDocument::class);
    }
    public function locationInventories()
    {
        return $this->belongsTo(LocationInventory::class, 'location_id');
    }
    public function prepaymentAllocations()
    {
        return $this->hasMany(PrepaymentAllocation::class, 'purchase_invoice_id');
    }
    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class, 'invoice_number_id');
    }
    public function getOriginalAmountAttribute()
    {
        $subtotal = 0;
        $total_tax = 0;
        $final = 0;

        foreach ($this->details as $item) {
            $amount = ($item->price - $item->discount) * $item->quantity;
            $total_tax += $item->tax_amount;
            $final = $amount + $total_tax;
            $subtotal += $final;
        }

        $withholding_tax = optional($this->withholding)->rate ?? 0;
        $withholding_value = $subtotal * ($withholding_tax / 100);

        return $subtotal - $withholding_value + ($this->freight ?? 0);
    }
    public function updatePaymentStatus()
    {
        $totalPaid = $this->paymentDetails()->sum('payment_amount');
        $totalAllocated = $this->prepaymentAllocations()->sum('allocated_amount');
        $total = $totalPaid + $totalAllocated;

        // 🔹 Tentukan status invoice
        if ($total >= $this->original_amount) {
            $this->status_purchase = 3; // Lunas
        } elseif ($total > 0) {
            $this->status_purchase = 1; // Sebagian
        } else {
            $this->status_purchase = 0; // Belum dibayar
        }

        $this->save();

        // 🔹 Cek apakah invoice ini punya purchase_order_id
        if ($this->purchase_order_id) {
            $purchaseOrder = \App\PurchaseOrder::find($this->purchase_order_id);
            if ($purchaseOrder) {
                // Mapping status invoice → status PO
                if ($this->status_purchase == 3) {
                    $purchaseOrder->status_purchase = 3; // Lunas juga
                } elseif ($this->status_purchase == 1) {
                    $purchaseOrder->status_purchase = 2; // Sebagian
                }

                $purchaseOrder->save();

                // Debugging log optional
                // dump('📦 Update Purchase Order:', [
                //     'purchase_order_id' => $purchaseOrder->id,
                //     'status_purchase' => $purchaseOrder->status_purchase,
                // ]);
            }
        }

        // Debugging tambahan
        // dump('💰 Update Invoice:', [
        //     'invoice_id' => $this->id,
        //     'status_purchase' => $this->status_purchase,
        //     'total_paid' => $totalPaid,
        //     'total_allocated' => $totalAllocated,
        //     'original_amount' => $this->original_amount,
        // ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\JournalEntry;
use App\PaymentMethod;
use App\Payment;
use App\PaymentDetail;
use App\PaymentMethodDetail;
use App\Prepayment;
use App\PrepaymentAllocation;
use App\PurchaseInvoice;
use App\Vendors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    //
    public function index()
    {
        $data = Payment::with(['jenis_pembayaran', 'vendor', 'PaymentMethodAccount'])->orderBy('payment_date')->paginate(10);
        return view('payment.index', compact('data'));
    }
    public function create()
    {
        $vendor = Vendors::all();
        $jenis_pembayaran = PaymentMethod::all();
        $account = chartOfAccount::all();
        return view('payment.create', compact('vendor', 'account', 'jenis_pembayaran'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_pembayaran_id' => 'required|exists:payment_methods,id',
            'payment_method_account_id' => 'required|exists:payment_method_details,id',
            'source' => 'nullable|string|max:255',
            'vendor_id' => 'required|exists:vendors,id',
            'payment_date' => 'required|date',
            'comment' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // 1️⃣ Simpan ke tabel payments (header)
            $payment = Payment::create([
                'jenis_pembayaran_id' => $validated['jenis_pembayaran_id'],
                'payment_method_account_id' => $validated['payment_method_account_id'],
                'source' => $validated['source'] ?? null,
                'vendor_id' => $validated['vendor_id'],
                'payment_date' => $validated['payment_date'],
                'comment' => $validated['comment'] ?? null,
                'type' => 'invoice', // bisa nanti diganti 'other' jika dibutuhkan
            ]);

            // dump('✅ Payment created:', $payment->toArray());
            // 2️⃣ Simpan detail pembayaran (PaymentDetail)
            if ($request->has('payment_amount')) {
                foreach ($request->payment_amount as $invoiceId => $amount) {
                    if ($amount > 0) {
                        $invoice = PurchaseInvoice::with('details')->find($invoiceId);

                        PaymentDetail::create([
                            'payment_id' => $payment->id,
                            'invoice_number_id' => $invoice->id,
                            'due_date' => $invoice->due_date ?? null,
                            'original_amount' => $invoice->original_amount, // computed accessor
                            'amount_owing' => $invoice->amount_owing ?? 0,
                            'discount_available' => $invoice->discount_available ?? 0,
                            'discount_taken' => 0,
                            'payment_amount' => $amount,
                        ]);

                        // 🔹 Update status invoice setelah pembayaran
                        $invoice->updatePaymentStatus();
                        // dump('🔄 Status invoice setelah update:', [
                        //     'invoice_id' => $invoice->id,
                        //     'status_purchase' => $invoice->status_purchase,

                        // ]);
                    }
                }
            }

            // 3️⃣ Simpan alokasi prepayment (jika ada)
            if ($request->has('prepayment_allocations')) {
                foreach ($request->prepayment_allocations as $prepaymentId => $amount) {
                    if ($amount > 0) {
                        $relatedInvoiceId = $request->invoice_for_prepayment[$prepaymentId] ?? null;
                        if ($relatedInvoiceId) {
                            PrepaymentAllocation::create([
                                'payment_id'         => $payment->id,
                                'prepayment_id'        => $prepaymentId,
                                'purchase_invoice_id'  => $relatedInvoiceId,
                                'allocated_amount'     => $amount,
                            ]);

                            // 🔹 Update status invoice juga
                            $invoice = PurchaseInvoice::find($relatedInvoiceId);
                            if ($invoice) {
                                $invoice->updatePaymentStatus();
                                // dump('🔄 Status invoice (dari prepayment):', [
                                //     'invoice_id' => $invoice->id,
                                //     'status_purchase' => $invoice->status_purchase,
                                // ]);
                            }
                        }
                    }
                }
            }

            // 4️⃣ Buat Journal Entry (header)
            $journal = JournalEntry::create([
                'source'  => 'Payment#' . $payment->id,
                'tanggal' => $validated['payment_date'],
                'comment' => $validated['comment'] ?? null,
            ]);

            // 4A️⃣ Akun Kas/Bank (Credit)
            $pmDetail = PaymentMethodDetail::with('chartOfAccount')->find($validated['payment_method_account_id']);
            $coaPayment = optional($pmDetail->chartOfAccount);

            $totalDebit = 0;

            // 4B️⃣ Debit untuk setiap invoice
            if ($request->has('payment_amount')) {
                foreach ($request->payment_amount as $invoiceId => $amount) {
                    if ($amount > 0) {
                        $invoice = PurchaseInvoice::with('paymentmethodDetail.chartOfAccount')->find($invoiceId);
                        $coaDebit = optional($invoice->paymentmethodDetail->chartOfAccount);

                        $journal->details()->create([
                            'kode_akun' => $coaDebit->kode_akun ?? null,
                            'debits' => $amount,
                            'credits' => 0,
                            'status' => 2,
                            'comment' => 'Pembayaran invoice ' . ($invoice->invoice_number ?? $invoiceId),
                        ]);

                        $totalDebit += $amount;
                    }
                }
            }

            // 4C️⃣ Debit untuk setiap prepayment
            if ($request->has('prepayment_allocations')) {
                foreach ($request->prepayment_allocations as $prepaymentId => $amount) {
                    if ($amount > 0) {
                        $prepayment = Prepayment::with('accountPrepayment')->find($prepaymentId);
                        $coaDebit = optional($prepayment->accountPrepayment);

                        $journal->details()->create([
                            'kode_akun' => $coaDebit->kode_akun ?? null,
                            'debits' => $amount,
                            'credits' => 0,
                            'comment' => 'Alokasi prepayment ' . ($prepayment->reference ?? $prepaymentId),
                            'status' => 2
                        ]);

                        $totalDebit += $amount;
                    }
                }
            }

            // 4D️⃣ Credit total Kas/Bank
            $journal->details()->create([
                'kode_akun' => $coaPayment->kode_akun ?? null,
                'debits' => 0,
                'credits' => $totalDebit,
                'comment' => 'Pembayaran melalui ' . ($coaPayment->nama_akun ?? 'Kas/Bank'),
                'status' => 2
            ]);

            DB::commit();

            return redirect()
                ->route('payment.index')
                ->with('success', 'Payment berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Gagal menyimpan payment: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $data = Payment::with(['vendor', 'jenis_pembayaran', 'PaymentMethodAccount', 'details'])->findOrFail($id);

        $purchase_invoice = db::table('payments')
            ->leftJoin('payment_details', 'payments.id', '=', 'payment_details.payment_id')
            ->leftJoin('purchase_invoices', 'payment_details.invoice_number_id', '=', 'purchase_invoices.id')
            ->where('payments.id', $id)
            ->select('purchase_invoices.invoice_number')
            ->first();


        $invoice_number = $purchase_invoice->invoice_number ?? null;

        return view('payment.show', compact('data', 'invoice_number'));
    }
    public function edit($id)
    {
        $data = Payment::with([
            'vendor',
            'jenis_pembayaran',
            'PaymentMethodAccount',
            'details.invoice.details',
            'prepaymentAllocations.prepayment',
            'prepaymentAllocations.purchaseInvoice',
        ])->findOrFail($id);

        $vendor = Vendors::all();
        $jenis_pembayaran = PaymentMethod::all();

        // 🧮 Hitung ulang amount untuk setiap detail invoice
        foreach ($data->details as $detail) {
            $totalAmount = 0;
            $amount = 0;
            $tax_amount = 0;

            if ($detail->invoice && $detail->invoice->details) {
                foreach ($detail->invoice->details as $invDetail) {
                    $price = $invDetail->price ?? 0;
                    $discount = $invDetail->discount ?? 0;
                    $qty = $invDetail->quantity ?? 0;
                    $tax_amount = $invDetail->tax_amount ?? 0;
                    $freight = $detail->invoice->freight ?? 0;

                    $amount += ($price - $discount) * $qty;
                    $totalAmount = $amount + $freight + $tax_amount;
                }
            }

            $totalPaid = PaymentDetail::where('invoice_number_id', $detail->invoice_number_id)
                ->sum('payment_amount');

            $detail->original_amount = $totalAmount;

            $detail->amount_owing = $totalAmount - $totalPaid;
        }


        $prepaymentAllocations = $data->prepaymentAllocations->map(function ($alloc) {
            $invoice = $alloc->purchaseInvoice;
            $prepayment = $alloc->prepayment;

            $totalAllocated = \App\PrepaymentAllocation::where('prepayment_id', $prepayment->id)
                ->sum('allocated_amount');

            $amountOwing = ($prepayment->amount ?? 0) - $totalAllocated;
            return (object) [
                'is_prepayment' => true,
                'tanggal' => $alloc->prepayment->tanggal_prepayment ?? null,
                'reference' => $alloc->prepayment->reference ?? null,
                'original_amount' => $alloc->prepayment->amount ?? 0,
                'allocated_amount' => $alloc->allocated_amount ?? 0,
                'invoice_number' => $invoice->invoice_number ?? '-',
                'date_invoice' => $invoice->date_invoice ?? null,
                'amount_owing' => $amountOwing,
            ];
        });

        return view('payment.edit', compact('data', 'vendor', 'jenis_pembayaran', 'prepaymentAllocations'));
    }


    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $payment = \App\Payment::with(['details.invoice', 'prepaymentAllocations'])->findOrFail($id);

            $affectedInvoices = collect();

            foreach ($payment->details as $detail) {
                if ($detail->invoice) {
                    $affectedInvoices->push($detail->invoice->id);
                }
            }

            foreach ($payment->prepaymentAllocations as $alloc) {
                if ($alloc->purchase_invoice_id) {
                    $affectedInvoices->push($alloc->purchase_invoice_id);
                }
            }

            $payment->prepaymentAllocations()->delete();

            if ($journal = JournalEntry::where('source', 'Payment#' . $payment->id)->first()) {
                $journal->details()->delete();
                $journal->delete();
            }

            $payment->details()->delete();

            $payment->delete();

            foreach ($affectedInvoices->unique() as $invoiceId) {
                $invoice = \App\PurchaseInvoice::find($invoiceId);
                if ($invoice) {
                    $invoice->updatePaymentStatus(); // 
                }
            }

            DB::commit();

            return redirect()->route('payment.index')
                ->with('success', 'Payment berhasil dihapus dan status invoice diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withErrors('Gagal menghapus payment: ' . $e->getMessage());
        }
    }
}

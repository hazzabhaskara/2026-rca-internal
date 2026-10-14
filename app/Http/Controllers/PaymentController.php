<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\JournalEntry;
use App\linkedAccounts;
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
    public function index()
    {
        $data = Payment::with(['jenis_pembayaran', 'vendor', 'PaymentMethodAccount'])->orderBy('payment_date')->paginate(10);
        return view('payment.index', compact('data'));
    }

    public function create()
    {
        $vendor = Vendors::all();
        $jenis_pembayaran = PaymentMethod::all();
        $account = ChartOfAccount::all();
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
                'type' => 'invoice',
            ]);

            // 2️⃣ Simpan detail pembayaran (PaymentDetail)
            if ($request->has('payment_amount')) {
                foreach ($request->payment_amount as $invoiceId => $amount) {
                    $cleanAmount = (float) str_replace(['.', ','], ['', '.'], (string) $amount);
                    if ($cleanAmount > 0) {
                        $invoice = PurchaseInvoice::with('details')->find($invoiceId);
                        if (!$invoice) {
                            continue;
                        }

                        PaymentDetail::create([
                            'payment_id' => $payment->id,
                            'invoice_number_id' => $invoice->id,
                            'due_date' => $invoice->due_date ?? null,
                            'original_amount' => $invoice->original_amount,
                            'amount_owing' => $invoice->amount_owing ?? 0,
                            'discount_available' => $invoice->discount_available ?? 0,
                            'discount_taken' => 0,
                            'payment_amount' => $cleanAmount,
                        ]);

                        $invoice->updatePaymentStatus();
                    }
                }
            }

            // 3️⃣ Simpan alokasi prepayment (jika ada)
            if ($request->has('prepayment_allocations')) {
                foreach ($request->prepayment_allocations as $prepaymentId => $amount) {
                    $cleanAlloc = (float) str_replace(['.', ','], ['', '.'], (string) $amount);
                    if ($cleanAlloc > 0) {
                        $relatedInvoiceId = $request->invoice_for_prepayment[$prepaymentId] ?? null;
                        if ($relatedInvoiceId) {
                            PrepaymentAllocation::create([
                                'payment_id'          => $payment->id,
                                'prepayment_id'       => $prepaymentId,
                                'purchase_invoice_id' => $relatedInvoiceId,
                                'allocated_amount'    => $cleanAlloc,
                            ]);

                            $invoice = PurchaseInvoice::find($relatedInvoiceId);
                            if ($invoice) {
                                $invoice->updatePaymentStatus();
                            }
                        }
                    }
                }
            }

            // 4️⃣ Buat Journal Entry (header)
            $journal = JournalEntry::create([
                'source'  => 'Payment#' . $payment->id,
                'tanggal' => $validated['payment_date'],
                'comment' => $validated['comment'] ?? ('Payment #' . $payment->id),
            ]);

            // 4A️⃣ Akun Kas/Bank (Credit hanya untuk porsi kas yang benar-benar keluar)
            $pmDetail = PaymentMethodDetail::with('chartOfAccount')->find($validated['payment_method_account_id']);
            $coaPayment = optional($pmDetail)->chartOfAccount;

            // Fallback akun Hutang Usaha (Account Payable) dari Linked Accounts
            $apLinked = linkedAccounts::with('akun')
                ->where('modul', 'purchases')
                ->where('kode', 'Account Payable')
                ->first();
            $defaultApCode = optional(optional($apLinked)->akun)->kode_akun;

            $prepayLinked = linkedAccounts::with('akun')
                ->where('modul', 'purchases')
                ->where('kode', 'Prepayments Prepaid Orders')
                ->first();
            $defaultPrepayCode = optional(optional($prepayLinked)->akun)->kode_akun;

            $totalCashPaid = 0.0;

            // 4B️⃣ Debit Hutang Usaha untuk setiap invoice yang dibayar via Kas/Bank
            if ($request->has('payment_amount')) {
                foreach ($request->payment_amount as $invoiceId => $amount) {
                    $cleanAmount = (float) str_replace(['.', ','], ['', '.'], (string) $amount);
                    if ($cleanAmount > 0) {
                        $invoice = PurchaseInvoice::with('paymentmethodDetail.chartOfAccount')->find($invoiceId);
                        $coaDebit = optional(optional($invoice)->paymentmethodDetail)->chartOfAccount;
                        $kodeAkunDebit = $coaDebit->kode_akun ?? $defaultApCode;

                        $journal->details()->create([
                            'kode_akun' => $kodeAkunDebit,
                            'debits'    => $cleanAmount,
                            'credits'   => 0,
                            'status'    => 2,
                            'comment'   => 'Pembayaran invoice ' . ($invoice->invoice_number ?? $invoiceId),
                        ]);

                        $totalCashPaid += $cleanAmount;
                    }
                }
            }

            // 4C️⃣ Alokasi Prepayment (REA Duality Fix: Debit Hutang Usaha Invoice, Credit Akun Uang Muka/Prepayment)
            // Tidak boleh menambah $totalCashPaid karena kas sudah keluar saat Prepayment dibuat.
            if ($request->has('prepayment_allocations')) {
                foreach ($request->prepayment_allocations as $prepaymentId => $amount) {
                    $cleanAlloc = (float) str_replace(['.', ','], ['', '.'], (string) $amount);
                    if ($cleanAlloc > 0) {
                        $relatedInvoiceId = $request->invoice_for_prepayment[$prepaymentId] ?? null;
                        $relatedInvoice = $relatedInvoiceId
                            ? PurchaseInvoice::with('paymentmethodDetail.chartOfAccount')->find($relatedInvoiceId)
                            : null;
                        $prepayment = Prepayment::with(['accountPrepayment', 'account'])->find($prepaymentId);

                        $apAccountCode = optional(optional($relatedInvoice)->paymentmethodDetail)->chartOfAccount->kode_akun
                            ?? $defaultApCode;

                        $prepayAccountCode = optional(optional($prepayment)->accountPrepayment)->kode_akun
                            ?? optional(optional($prepayment)->account)->kode_akun
                            ?? $defaultPrepayCode;

                        // Debit: Kurangi Hutang Usaha atas Invoice terkait
                        $journal->details()->create([
                            'kode_akun' => $apAccountCode,
                            'debits'    => $cleanAlloc,
                            'credits'   => 0,
                            'comment'   => 'Alokasi prepayment ke invoice ' . ($relatedInvoice->invoice_number ?? $relatedInvoiceId),
                            'status'    => 2,
                        ]);

                        // Credit: Kurangi saldo Uang Muka Pembelian (Prepayment Asset)
                        $journal->details()->create([
                            'kode_akun' => $prepayAccountCode,
                            'debits'    => 0,
                            'credits'   => $cleanAlloc,
                            'comment'   => 'Realisasi alokasi prepayment ' . ($prepayment->reference ?? $prepaymentId),
                            'status'    => 2,
                        ]);
                    }
                }
            }

            // 4D️⃣ Credit Kas/Bank hanya sebesar uang kas yang keluar pada pembayaran ini
            if ($totalCashPaid > 0) {
                $journal->details()->create([
                    'kode_akun' => $coaPayment->kode_akun ?? null,
                    'debits'    => 0,
                    'credits'   => $totalCashPaid,
                    'comment'   => 'Pembayaran melalui ' . ($coaPayment->nama_akun ?? 'Kas/Bank'),
                    'status'    => 2,
                ]);
            }

            if (function_exists('logActivity')) {
                logActivity("Create Payment #{$payment->id} (Source: " . ($payment->source ?? '-') . ")");
            }

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

        $purchase_invoice = DB::table('payments')
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
                    $invoice->updatePaymentStatus();
                }
            }

            if (function_exists('logActivity')) {
                logActivity("Delete Payment #{$id}");
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

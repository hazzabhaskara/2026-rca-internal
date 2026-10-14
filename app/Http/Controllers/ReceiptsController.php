<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\Customers;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\linkedAccounts;
use App\Receipts;
use App\ReceiptsDetail;
use App\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceiptsController extends Controller
{
    public function index()
    {
        $data = Receipts::with(['customer', 'depositTo'])->orderBy('date', 'desc')->paginate(10);
        return view('receipts.index', compact('data'));
    }

    public function create()
    {
        $customers = Customers::all();
        $accounts = ChartOfAccount::all();
        $invoices = SalesInvoice::all();

        return view('receipts.create', compact('customers', 'accounts', 'invoices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receipt_number' => 'required|unique:receipts,receipt_number',
            'customer_id' => 'required|exists:customers,id',
            'deposit_to_id' => 'required|exists:chart_of_accounts,id',
            'date' => 'required|date',
            'comment' => 'nullable|string',
            'details' => 'required|array',
            'details.*.sales_invoice_id' => 'required|exists:sales_invoices,id',
            'details.*.invoice_date' => 'nullable|date',
            'details.*.original_amount' => 'required|numeric',
            'details.*.amount_owing' => 'required|numeric',
            'details.*.discount_available' => 'nullable|numeric',
            'details.*.discount_taken' => 'nullable|numeric',
            'details.*.amount_received' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $receipt = Receipts::create([
                'receipt_number' => $request->receipt_number,
                'customer_id'    => $request->customer_id,
                'deposit_to_id'  => $request->deposit_to_id,
                'date'           => $request->date,
                'comment'        => $request->comment,
            ]);

            $depositCoa = ChartOfAccount::findOrFail($request->deposit_to_id);

            $arLinked = linkedAccounts::with('akun')
                ->where('modul', 'sales')
                ->where('kode', 'Account Receivable')
                ->first();
            $defaultArCode = optional(optional($arLinked)->akun)->kode_akun;

            $discountLinked = linkedAccounts::with('akun')
                ->where('modul', 'sales')
                ->where('kode', 'Early Payment Discount')
                ->first();
            $discountCode = optional(optional($discountLinked)->akun)->kode_akun;

            $journal = JournalEntry::create([
                'source'  => 'Receipt#' . $receipt->id,
                'tanggal' => $request->date,
                'comment' => $request->comment ?? ('Receipt #' . $receipt->receipt_number),
            ]);

            $totalCashReceived = 0.0;
            $totalDiscountTaken = 0.0;

            foreach ($request->details as $detail) {
                $amountReceived = (float) ($detail['amount_received'] ?? 0);
                $discountTaken  = (float) ($detail['discount_taken'] ?? 0);

                if ($amountReceived <= 0 && $discountTaken <= 0) {
                    continue;
                }

                $invoice = SalesInvoice::with(['paymentMethodAccount.chartOfAccount'])->findOrFail($detail['sales_invoice_id']);

                ReceiptsDetail::create([
                    'receipt_id'         => $receipt->id,
                    'sales_invoice_id'   => $invoice->id,
                    'invoice_date'       => $detail['invoice_date'] ?? $invoice->invoice_date,
                    'original_amount'    => $detail['original_amount'],
                    'amount_owing'       => $detail['amount_owing'],
                    'discount_available' => $detail['discount_available'] ?? 0,
                    'discount_taken'     => $discountTaken,
                    'amount_received'    => $amountReceived,
                ]);

                $invoice->updatePaymentStatus();

                // Credit Akun Piutang Usaha / Akun Header Sales Invoice
                $arAccountCode = optional(optional($invoice->paymentMethodAccount)->chartOfAccount)->kode_akun
                    ?? $defaultArCode;

                $settledAmount = $amountReceived + $discountTaken;
                if ($settledAmount > 0 && $arAccountCode) {
                    JournalEntryDetail::create([
                        'journal_entry_id' => $journal->id,
                        'kode_akun'        => $arAccountCode,
                        'debits'           => 0,
                        'credits'          => $settledAmount,
                        'status'           => 2,
                        'comment'          => 'Pelunasan Sales Invoice #' . $invoice->invoice_number,
                    ]);
                }

                $totalCashReceived += $amountReceived;
                $totalDiscountTaken += $discountTaken;
            }

            if ($totalCashReceived <= 0 && $totalDiscountTaken <= 0) {
                throw new \InvalidArgumentException('Minimal satu invoice harus memiliki nominal pembayaran (Paid) lebih dari 0.');
            }

            // Debit Kas/Bank (Deposit To)
            if ($totalCashReceived > 0) {
                JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $depositCoa->kode_akun,
                    'debits'           => $totalCashReceived,
                    'credits'          => 0,
                    'status'           => 2,
                    'comment'          => 'Penerimaan pembayaran Receipt #' . $receipt->receipt_number,
                ]);
            }

            // Debit Potongan Penjualan (Early Payment Discount) jika ada
            if ($totalDiscountTaken > 0 && $discountCode) {
                JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $discountCode,
                    'debits'           => $totalDiscountTaken,
                    'credits'          => 0,
                    'status'           => 2,
                    'comment'          => 'Potongan pelunasan Receipt #' . $receipt->receipt_number,
                ]);
            }

            if (function_exists('logActivity')) {
                logActivity("Create Receipt #{$receipt->receipt_number}");
            }

            DB::commit();
            return redirect()->route('receipts.index')->with('success', 'Receipt berhasil disimpan beserta jurnal buku besar.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan receipt: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $receipt = Receipts::with(['customer', 'depositTo', 'details.salesInvoice'])->findOrFail($id);
        return view('receipts.show', compact('receipt'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $receipt = Receipts::with('details')->findOrFail($id);
            $affectedInvoiceIds = $receipt->details->pluck('sales_invoice_id')->unique();

            if ($journal = JournalEntry::where('source', 'Receipt#' . $receipt->id)->first()) {
                $journal->details()->delete();
                $journal->delete();
            }

            $receipt->details()->delete();
            $receiptNo = $receipt->receipt_number;
            $receipt->delete();

            foreach ($affectedInvoiceIds as $invId) {
                if ($invoice = SalesInvoice::find($invId)) {
                    $invoice->updatePaymentStatus();
                }
            }

            if (function_exists('logActivity')) {
                logActivity("Delete Receipt #{$receiptNo}");
            }

            DB::commit();
            return redirect()->route('receipts.index')->with('success', 'Receipt berhasil dihapus dan status invoice diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus receipt: ' . $e->getMessage());
        }
    }

    public function getInvoices($customerId)
    {
        $invoices = SalesInvoice::with(['details', 'receiptsDetails'])
            ->where('customers_id', $customerId)
            ->whereIn('status', [0, 1, 2])
            ->get();

        return view('receipts.partials.invoice_table', compact('invoices'));
    }
}

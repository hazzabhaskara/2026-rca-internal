<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\Customers;
use App\jenis_pembayaran;
use App\JournalEntry;
use App\PaymentMethod;
use App\SalesDeposit;
use App\SalesDepositDetail;
use App\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesDepositController extends Controller
{
    //
    public function index()
    {
        $data = SalesDeposit::with(['account', 'jenis_pembayaran', 'customer'])->latest()->paginate(5);
        return view('sales_deposits.index', compact('data'));
    }
    public function create()
    {
        $account = chartOfAccount::all();
        $deposit = ChartOfAccount::all();
        $customer = Customers::all();
        $jenis_pembayaran = PaymentMethod::all();
        $sales_invoices = SalesInvoice::all();
        $paidAccount = \App\linkedAccounts::with('akun')
            ->where('kode', 'Prepaid Orders')
            ->first();

        return view('sales_deposits.create', compact('account', 'customer', 'jenis_pembayaran', 'sales_invoices', 'paidAccount', 'deposit'));
    }

    public function store(Request $request)
    {
        // Bersihkan format ribuan sebelum validasi
        $request->merge([
            'deposit_amount' => str_replace(',', '.', str_replace('.', '', $request->deposit_amount))
        ]);

        // Validasi input
        $validatedData = $request->validate([
            'jenis_pembayaran_id' => 'required|exists:payment_methods,id',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'account_deposit' => 'required|exists:chart_of_accounts,id',
            'customers_id' => 'required|exists:customers,id',
            'deposit_no' => 'required|string|unique:sales_deposits,deposit_no',
            'deposit_date' => 'required|date',
            'deposit_reference' => 'nullable|string',
            'comment' => 'nullable|string',
            'deposit_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Simpan ke sales_deposits
            $deposit = SalesDeposit::create([
                'deposit_no'             => $validatedData['deposit_no'],
                'deposit_date'           => $validatedData['deposit_date'],
                'jenis_pembayaran_id'    => $validatedData['jenis_pembayaran_id'],
                'account_id'             => $validatedData['account_id'],
                'account_deposit'             => $validatedData['account_deposit'],
                'customer_id'            => $validatedData['customers_id'],
                'deposit_reference'      => $validatedData['deposit_reference'] ?? null,
                'deposit_amount'         => $validatedData['deposit_amount'],
                'comment'                => $validatedData['comment'] ?? null,
            ]);
            // === 4) Buat Journal Entry ===
            $journal = JournalEntry::create([
                'source'  => 'Deposits#' . $deposit->id,
                'tanggal'  => $validatedData['deposit_date'],
                'comment'  => $validatedData['comment'] ?? null,
            ]);
            $totalCredit = 0;
            $totalDebit  = 0;

            $coaCode = fn($id) => $id ? \App\ChartOfAccount::whereKey($id)->value('kode_akun') : null;


            // Journal Debit Prepayment
            $accountPayment = ChartOfAccount::find($validatedData['account_id']);
            $journal->details()->create([
                'journal_entry_id' => $journal->id,
                'kode_akun' => $accountPayment->kode_akun,
                'debits'    => $deposit->deposit_amount,
                'credits'   => 0,
                'comment'   => "Deposit {$deposit->id}",
                'status' => 2
            ]);

            // Journal Credit Kas/Bank
            $accountDeposits = ChartOfAccount::find($validatedData['account_deposit']);
            $journal->details()->create([
                'journal_entry_id' => $journal->id,
                'kode_akun' => $accountDeposits->kode_akun,
                'debits'    => 0,
                'credits'   => $deposit->deposit_amount,
                'comment'   => "Pembayaran Deposit {$deposit->id}",
                'status' => 2
            ]);


            DB::commit();
            return redirect()->route('sales_deposits.index')->with('success', 'Sales Deposit berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors([
                'error' => 'Gagal menyimpan: ' . $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        $deposit = SalesDeposit::with(['customer', 'jenis_pembayaran', 'account'])->findOrFail($id);
        return view('sales_deposits.show', compact('deposit'));
    }

    public function edit($id)
    {
        $sales_deposits = SalesDeposit::findOrFail($id);
        $jenis_pembayaran = PaymentMethod::all();
        $account = ChartOfAccount::all();
        $deposit = ChartOfAccount::all();
        $customer = Customers::all();
        $sales_invoices = SalesInvoice::all();
        $paidAccount = \App\linkedAccounts::with('akun')
            ->where('kode', 'Prepaid Orders')
            ->first();

        return view('sales_deposits.edit', compact(
            'sales_deposits',
            'jenis_pembayaran',
            'account',
            'customer',
            'sales_invoices',
            'paidAccount',
            'deposit'
        ));
    }

    public function update(Request $request, $id)
    {


        $request->merge([
            'deposit_amount' => str_replace('.', '', $request->deposit_amount),
        ]);

        // Validasi
        $validated = $request->validate([
            'jenis_pembayaran_id' => 'required|exists:payment_methods,id',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'account_deposit' => 'required|exists:chart_of_accounts,id',
            'customers_id' => 'required|exists:customers,id',
            'deposit_no' => 'required|string|unique:sales_deposits,deposit_no,' . $id,
            'deposit_date' => 'required|date',
            'deposit_reference' => 'nullable|string',
            'comment' => 'nullable|string',
            'deposit_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $deposit = SalesDeposit::findOrFail($id);

            $deposit->update([
                'deposit_no'         => $request->deposit_no,
                'deposit_date'       => $request->deposit_date,
                'jenis_pembayaran_id' => $request->jenis_pembayaran_id,
                'account_id'         => $request->account_id,
                'account_deposit'         => $request->account_deposit,
                'customer_id'        => $request->customers_id,
                'deposit_reference'  => $request->deposit_reference,
                'deposit_amount'     => $request->deposit_amount, // Sudah diformat
                'comment'            => $request->comment,
            ]);

            // Log::debug('SalesDeposit updated', ['id' => $deposit->id]);

            // Log::debug('Deleted old details');

            $journalSource = 'Deposits#' . $deposit->id;

            // hapus jurnal lama
            $oldJournal = JournalEntry::where('source', $journalSource)->first();
            if ($oldJournal) {
                $oldJournal->details()->delete();
                $oldJournal->delete();
            }

            // buat jurnal baru
            $journal = JournalEntry::create([
                'source'   => $journalSource,
                'tanggal'  => $validated['deposit_date'],
                'comment'  => $validated['comment'] ?? null,
            ]);

            $coaCode = fn($id) => $id ? \App\ChartOfAccount::whereKey($id)->value('kode_akun') : null;

            if (!$coaCode) {
                throw new \Exception("Linked account 'Prepayments Prepaid Orders' tidak ditemukan");
            }
            // Journal Debit Prepayment
            $accountPayment = ChartOfAccount::find($validated['account_id']);
            $journal->details()->create([
                'journal_entry_id' => $journal->id,
                'kode_akun' => $accountPayment->kode_akun,
                'debits'    => $deposit->deposit_amount,
                'credits'   => 0,
                'comment'   => "Deposit {$deposit->id}",
                'status' => 2
            ]);

            // Journal Credit Kas/Bank
            $accountDeposit = ChartOfAccount::find($validated['account_deposit']);
            $journal->details()->create([
                'journal_entry_id' => $journal->id,
                'kode_akun' => $accountDeposit->kode_akun,
                'debits'    => 0,
                'credits'   => $deposit->deposit_amount,
                'comment'   => "Pembayaran Deposit {$deposit->id}",
                'status' => 2
            ]);

            DB::commit();
            return redirect()->route('sales_deposits.index')->with('success', 'Sales Deposit berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal update SalesDeposit: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->withErrors(['error' => 'Gagal memperbarui: ' . $e->getMessage()]);
        }
    }
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Ambil invoice beserta detail
            $deposits = SalesDeposit::findOrFail($id);

            // Hapus jurnal otomatis ikut terhapus kalau pakai onDelete('cascade')
            $journal = JournalEntry::where('source', 'Deposits#' . $deposits->id)->first();

            if ($journal) {
                $journal->details()->delete();
                $journal->delete();
            }
            // Hapus deposits utamanya
            $deposits->delete();

            DB::commit();

            return redirect()->route('sales_deposits.index')->with('success', 'Sales deposits berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}

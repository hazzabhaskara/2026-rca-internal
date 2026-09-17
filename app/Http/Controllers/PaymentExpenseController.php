<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use App\JournalEntry;
use App\PaymentExpense;
use App\PaymentExpenseDetail;
use App\SalesTaxes;
use App\Vendors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentExpenseController extends Controller
{
    //
    public function index()
    {
        $data = PaymentExpense::with(['Vendor', 'Account'])->orderBy('date')->get();
        return view('payment_expense.index', compact('data'));
    }
    public function create()
    {
        $account = chartOfAccount::all();
        $vendor = Vendors::all();
        $account_beban = chartOfAccount::where('tipe_akun', 'Beban')->get();
        $sales_taxes = SalesTaxes::all();
        return view('payment_expense.create', compact('account', 'vendor', 'account_beban', 'sales_taxes'));
    }
    public function show($id)
    {
        $data = PaymentExpense::with(['details', 'Vendor', 'Account', 'details.salesTaxes'])->findOrFail($id);
        return view('payment_expense.show', compact('data'));
    }
    public function edit($id)
    {
        $payment_expense = PaymentExpense::with(['details', 'Vendor', 'Account'])->findOrFail($id);
        $account = chartofaccount::all();
        $vendor = vendors::all();
        $account_beban = chartOfAccount::where('tipe_akun', 'Beban')->get();
        $sales_taxes = SalesTaxes::all();
        return view('payment_expense.edit', compact('payment_expense', 'account', 'vendor', 'account_beban', 'sales_taxes'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date'              => 'required|date',
            'account_header_id' => 'required|exists:chart_of_accounts,id',
            'source'            => 'required|string|max:255',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
            'items.*.deskripsi' => 'nullable|string',
            'items.*.amount'    => 'required|numeric|min:0',
            'items.*.tax_id'    => 'nullable|exists:sales_taxes,id',
        ]);

        DB::beginTransaction();
        try {
            // === 1) Update header PaymentExpense ===
            $expense = PaymentExpense::findOrFail($id);
            $expense->update([
                'date'             => $validated['date'],
                'from_account_id'  => $validated['account_header_id'],
                'source'           => $validated['source'],
                'notes'            => $validated['notes'] ?? null,
            ]);

            // === 2) Hapus detail lama ===
            $expense->details()->delete();

            // === 3) Insert detail baru ===
            foreach ($validated['items'] as $item) {
                $expense->details()->create([
                    'account_id' => $item['account_id'],
                    'deskripsi'  => $item['deskripsi'] ?? null,
                    'amount'     => $item['amount'],
                    'sales_taxes_id' => $item['tax_id'] ?? null,
                ]);
            }

            // === 4) Update jurnal ===
            $journalSource = 'PaymentExpense#' . $expense->id;

            // hapus jurnal lama
            $oldJournal = JournalEntry::where('source', $journalSource)->first();
            if ($oldJournal) {
                $oldJournal->details()->delete();
                $oldJournal->delete();
            }

            // buat jurnal baru
            $journal = JournalEntry::create([
                'source'   => $journalSource,
                'tanggal'  => $validated['date'],
                'comment'  => $validated['notes'] ?? null,
            ]);

            // isi detail jurnal
            foreach ($validated['items'] as $item) {
                $account = ChartOfAccount::find($item['account_id']);

                $journal->details()->create([
                    'kode_akun' => $account->kode_akun,
                    'debits'    => $item['amount'],
                    'credits'   => 0,
                    'comment'   => $item['deskripsi'] ?? null,
                ]);

                // kalau ada tax, tambahkan detail jurnal pajak
                if (!empty($item['tax_id'])) {
                    $tax = SalesTaxes::find($item['tax_id']);
                    $taxAmount = $item['amount'] * ($tax->rate / 100);

                    if ($tax->type === 'input_tax') {
                        $journal->details()->create([
                            'kode_akun' => $tax->purchaseAccount->kode_akun ?? $tax->salesAccount->kode_akun,
                            'debits'    => $taxAmount,
                            'credits'   => 0,
                            'comment'   => "Tax: {$tax->name}",
                        ]);
                    } elseif ($tax->type === 'withholding_tax') {
                        $journal->details()->create([
                            'kode_akun' => $tax->purchaseAccount->kode_akun ?? $tax->salesAccount->kode_akun,
                            'debits'    => 0,
                            'credits'   => $taxAmount,
                            'comment'   => "Tax: {$tax->name}",
                        ]);
                    }
                }
            }

            // credit kas/bank (from_account)
            $fromAccount = ChartOfAccount::find($validated['account_header_id']);
            $totalDebit  = $journal->details()->sum('debits');

            $journal->details()->create([
                'kode_akun' => $fromAccount->kode_akun,
                'debits'    => 0,
                'credits'   => $totalDebit,
                'comment'   => "Payment via {$fromAccount->nama_akun}",
            ]);

            DB::commit();
            return redirect()->route('payment_expense.index')->with('success', 'Payment Expense berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {

        // dd($request->all());
        // === 1) Validasi header ===
        $validated = $request->validate([
            'date'              => 'required|date',
            'account_header_id' => 'required|exists:chart_of_accounts,id',
            'source'            => 'required|string|max:255',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
            'items.*.deskripsi'  => 'nullable|string|max:255',
            'items.*.amount'     => 'required|numeric|min:0.01',
            'items.*.tax_id'     => 'nullable|exists:sales_taxes,id',
        ]);

        DB::beginTransaction();
        try {
            // === 2) Simpan header ===
            $expense = PaymentExpense::create([
                'date'              => $validated['date'],
                'from_account_id' => $validated['account_header_id'],
                'source'            => $validated['source'],
                'notes'             => $validated['notes'] ?? null,
            ]);

            // === 3) Simpan detail ===
            foreach ($validated['items'] as $item) {
                PaymentExpenseDetail::create([
                    'payment_expense_id' => $expense->id,
                    'account_id'         => $item['account_id'],
                    'deskripsi'          => $item['deskripsi'] ?? null,
                    'amount'             => $item['amount'],
                    'sales_taxes_id'             => $item['tax_id'] ?? null,
                ]);
            }

            // === 4) Buat Journal Entry ===
            $journal = JournalEntry::create([
                'source'  => 'PaymentExpense#' . $expense->id,
                'tanggal'  => $validated['date'],
                'comment'  => $validated['notes'] ?? null,
            ]);

            $totalCredit = 0;
            $totalDebit  = 0;

            // Loop detail untuk Debit
            foreach ($validated['items'] as $item) {
                $debit = $item['amount'];
                $credit = 0;

                // cari akun beban
                $account = ChartOfAccount::find($item['account_id']);

                $journal->details()->create([
                    'kode_akun' => $account->kode_akun,
                    'debits'    => $debit,
                    'credits'   => $credit,
                    'comment'   => $item['deskripsi'] ?? null,
                ]);

                $totalDebit += $debit;

                // jika ada tax
                if (!empty($item['tax_id'])) {
                    $tax = SalesTaxes::find($item['tax_id']);
                    $taxAmount = $item['amount'] * ($tax->rate / 100);

                    if ($tax->type === 'input_tax') {
                        // debit pajak masukan
                        $journal->details()->create([
                            'kode_akun' => $tax->purchaseAccount->kode_akun ?? 'TAX-IN',
                            'debits'    => $taxAmount,
                            'credits'   => 0,
                            'comment'   => "Input Tax {$tax->name}",
                        ]);
                        $totalDebit += $taxAmount;
                    } elseif ($tax->type === 'withholding_tax') {
                        // credit pajak potongan
                        $journal->details()->create([
                            'kode_akun' => $tax->salesAccount->kode_akun ?? 'TAX-WH',
                            'debits'    => 0,
                            'credits'   => $taxAmount,
                            'comment'   => "Withholding Tax {$tax->name}",
                        ]);
                        $totalCredit += $taxAmount;
                    }
                }
            }

            // Credit ke Kas/Bank
            $accountKas = ChartOfAccount::find($validated['account_header_id']);
            $journal->details()->create([
                'kode_akun' => $accountKas->kode_akun,
                'debits'    => 0,
                'credits'   => $totalDebit, // semua debit harus ditutup di credit kas
                'comment'   => "Pembayaran expense {$expense->id}",
            ]);

            $totalCredit += $totalDebit;


            DB::commit();

            return redirect()
                ->route('payment_expense.index')
                ->with('success', 'Payment expense berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            dd("gagal", $e->getMessage());
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $expense = PaymentExpense::findOrFail($id);

            // Hapus jurnal otomatis ikut terhapus kalau pakai onDelete('cascade')
            $journal = JournalEntry::where('source', 'PaymentExpense#' . $expense->id)->first();

            if ($journal) {
                $journal->details()->delete();
                $journal->delete();
            }

            // Hapus detail + header
            $expense->details()->delete();
            $expense->delete();
        });

        return redirect()
            ->route('payment_expense.index')
            ->with('success', 'Payment Expense berhasil dihapus.');
    }
}

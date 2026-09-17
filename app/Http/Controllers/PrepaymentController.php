<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use App\Item;
use App\JournalEntry;
use App\Prepayment;
use App\Vendors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrepaymentController extends Controller
{
    //

    public function index()
    {

        $data = Prepayment::with(['vendor', 'account', 'prepaymentAllocation'])->orderBy('tanggal_prepayment', 'asc')->paginate(20);
        return view('prepayment.index', compact('data'));
    }

    public function create()
    {
        $account = chartOfAccount::all();
        $vendor = Vendors::all();
        $paidAccount = \App\linkedAccounts::with('akun')
            ->where('kode', 'Prepayments Prepaid Orders')
            ->first();
        $prepaymentAccount = chartOfAccount::all();
        return view('prepayment.create', compact('vendor', 'account', 'paidAccount', 'prepaymentAccount'));
    }
    public function store(Request $request)
    {

        // hilangkan titik ribuan sebelum validasi
        $request->merge([
            'amount' => str_replace('.', '', $request->amount),
        ]);

        // dd($request->all());
        // === 1) Validasi header ===
        $validated = $request->validate([
            'tanggal_prepayment'              => 'required|date',
            'vendor_id' => 'required|exists:vendors,id',
            'account_header_id' => 'required|exists:chart_of_accounts,id',
            'account_prepayment' => 'required|exists:chart_of_accounts,id',
            'reference'            => 'required|string|max:255',
            'amount' => 'required|numeric',
            'comment'             => 'nullable|string',

        ]);

        DB::beginTransaction();
        try {
            // === 2) Simpan header ===
            $expense = Prepayment::create([
                'tanggal_prepayment'              => $validated['tanggal_prepayment'],
                'vendor_id' => $validated['vendor_id'],
                'account_id'            => $validated['account_header_id'],
                'account_prepayment'            => $validated['account_prepayment'],
                'amount'             => $validated['amount'],
                'reference' => $validated['reference'],
                'comment'             => $validated['comment'] ?? null,
            ]);

            // === 4) Buat Journal Entry ===
            $journal = JournalEntry::create([
                'source'  => 'Prepayment#' . $expense->id,
                'tanggal'  => $validated['tanggal_prepayment'],
                'comment'  => $validated['comment'] ?? null,
            ]);


            $totalCredit = 0;
            $totalDebit  = 0;

            $coaCode = fn($id) => $id ? \App\ChartOfAccount::whereKey($id)->value('kode_akun') : null;


            // Journal Debit Prepayment
            $accountPembayaran = ChartOfAccount::find($validated['account_header_id']);
            $journal->details()->create([
                'journal_entry_id' => $journal->id,
                'kode_akun' => $accountPembayaran->kode_akun,
                'debits'    => $expense->amount,
                'credits'   => 0,
                'comment'   => "Prepayment {$expense->id}",
                'status' => 2
            ]);

            // Journal Credit Kas/Bank
            $accountPrepayment = ChartOfAccount::find($validated['account_prepayment']);
            $journal->details()->create([
                'journal_entry_id' => $journal->id,
                'kode_akun' => $accountPrepayment->kode_akun,
                'debits'    => 0,
                'credits'   => $expense->amount,
                'comment'   => "Pembayaran Prepayment {$expense->id}",
                'status' => 2
            ]);
            DB::commit();

            return redirect()
                ->route('prepayment.index')
                ->with('success', 'Prepayment berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            dd("gagal", $e->getMessage());
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
    public function show($id)
    {
        $data = Prepayment::with(['vendor', 'account'])->findOrFail($id);
        return view('prepayment.show', compact('data'));
    }

    public function edit($id)
    {
        $prepayment = Prepayment::with(['vendor', 'account', 'accountPrepayment'])->findOrFail($id);
        $vendor = Vendors::all();
        $account = chartOfAccount::all();
        $prepaymentAccount = chartOfAccount::all();
        $paidAccount = \App\linkedAccounts::with('akun')
            ->where('kode', 'Prepayments Prepaid Orders')
            ->first();
        return view('prepayment.edit', compact('prepayment', 'vendor', 'account', 'paidAccount', 'prepaymentAccount'));
    }

    public function update(Request $request, $id)
    {

        // hilangkan titik ribuan sebelum validasi
        $request->merge([
            'amount' => str_replace('.', '', $request->amount),
        ]);
        $validated = $request->validate([
            'tanggal_prepayment'              => 'required|date',
            'vendor_id' => 'required|exists:vendors,id',
            'account_header_id' => 'required|exists:chart_of_accounts,id',
            'account_prepayment' => 'required|exists:chart_of_accounts,id',
            'reference'            => 'required|string|max:255',
            'amount' => 'required|numeric',
            'comment'             => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $expense = Prepayment::findOrFail($id);
            $expense->update([
                'tanggal_prepayment'              => $validated['tanggal_prepayment'],
                'vendor_id' => $validated['vendor_id'],
                'account_id'            => $validated['account_header_id'],
                'account_prepayment'            => $validated['account_prepayment'],
                'amount'             => $validated['amount'],
                'reference' => $validated['reference'],
                'comment'             => $validated['comment'] ?? null,
            ]);

            $journalSource = 'Prepayment#' . $expense->id;

            // hapus jurnal lama
            $oldJournal = JournalEntry::where('source', $journalSource)->first();
            if ($oldJournal) {
                $oldJournal->details()->delete();
                $oldJournal->delete();
            }

            // buat jurnal baru
            $journal = JournalEntry::create([
                'source'   => $journalSource,
                'tanggal'  => $validated['tanggal_prepayment'],
                'comment'  => $validated['comment'] ?? null,
            ]);

            $coaCode = fn($id) => $id ? \App\ChartOfAccount::whereKey($id)->value('kode_akun') : null;

            if (!$coaCode) {
                throw new \Exception("Linked account 'Prepayments Prepaid Orders' tidak ditemukan");
            }


            // Journal Debit Prepayment
            $accountPembayaran = ChartOfAccount::find($validated['account_header_id']);
            $journal->details()->create([
                'journal_entry_id' => $journal->id,
                'kode_akun' => $accountPembayaran->kode_akun,
                'debits'    => $expense->amount,
                'credits'   => 0,
                'comment'   => "Prepayment {$expense->id}",
                'status' => 2,
            ]);

            // Journal Credit Kas/Bank
            $accountPrepayment = ChartOfAccount::find($validated['account_prepayment']);
            $journal->details()->create([
                'journal_entry_id' => $journal->id,
                'kode_akun' => $accountPrepayment->kode_akun,
                'debits'    => 0,
                'credits'   => $expense->amount,
                'comment'   => "Pembayaran Prepayment {$expense->id}",
                'status' => 2
            ]);

            DB::commit();
            return redirect()->route('prepayment.index')->with('success', 'Prepayment berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Error: ' . $e->getMessage()]);
        }
    }
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $expense = Prepayment::findOrFail($id);

            // Hapus jurnal otomatis ikut terhapus kalau pakai onDelete('cascade')
            $journal = JournalEntry::where('source', 'Prepayment#' . $expense->id)->first();

            if ($journal) {
                $journal->details()->delete();
                $journal->delete();
            }

            // Hapus detail + header
            // $expense->details()->delete();
            $expense->delete();
        });

        return redirect()
            ->route('prepayment.index')
            ->with('success', 'Prepayment berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use App\Departement;
use App\DepartemenAkun;
use App\JournalEntry;
use App\PaymentExpense;
use App\PaymentExpenseDetail;
use App\SalesTaxes;
use App\User;
use App\Vendors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PaymentExpenseController extends Controller
{
    /**
     * Kategori sumber dana kas standar untuk Payment Expense
     */
    protected $categorySources = [
        'Kas Kecil (Petty Cash)',
        'Kas Operasional Divisi',
        'Kasbon / Cash Advance',
        'Reimbursement / Klaim Biaya',
        'Operasional Lapangan',
        'Lain-lain',
    ];

    /**
     * Periksa apakah user memiliki hak akses melihat seluruh pengeluaran
     * (Admin / Finance / Supervisor Keuangan)
     */
    protected function canAccessAllExpenses($user = null)
    {
        $user = $user ?: Auth::user();
        if (!$user) {
            return false;
        }

        // Role Admin Spatie atau izin khusus
        if ($user->hasRole('Admin') || $user->can('payment_expense.all') || $user->can('payment_expense.all_departments')) {
            return true;
        }

        // Pengecekan nama role
        $roleName = strtolower($user->role->name ?? '');
        if (in_array($roleName, ['admin', 'superadmin', 'finance', 'keuangan', 'accounting', 'akuntansi', 'supervisor'])) {
            return true;
        }

        return false;
    }

    /**
     * Tampilkan daftar payment expense dengan filter scoping user
     */
    public function index(Request $request)
    {
        $isAllAccess = $this->canAccessAllExpenses();
        $query = PaymentExpense::with(['Vendor', 'Account', 'user', 'departemen', 'details.salesTaxes']);

        // Data scoping: Jika bukan admin/finance, batasi HANYA milik user yang login
        if (!$isAllAccess) {
            $query->where('user_id', Auth::id());
        } else {
            // Filter tambahan untuk Admin / Finance
            if ($request->filled('filter_user_id')) {
                $query->where('user_id', $request->filter_user_id);
            }
            if ($request->filled('filter_departemen_id')) {
                $query->where('departemen_id', $request->filter_departemen_id);
            }
        }

        // Filter tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        // Filter kategori source
        if ($request->filled('filter_category')) {
            $query->where('category_source', $request->filter_category);
        }

        // Filter pencarian teks
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('voucher_number', 'LIKE', "%{$search}%")
                    ->orWhere('source', 'LIKE', "%{$search}%")
                    ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(20);
        $departements = Departement::where('status', 1)->get();
        $users = User::all();
        $categorySources = $this->categorySources;

        return view('payment_expense.index', compact('data', 'departements', 'users', 'isAllAccess', 'categorySources'));
    }

    /**
     * Form tambah payment expense baru
     */
    public function create()
    {
        $account = chartOfAccount::all();
        $vendor = Vendors::all();
        $account_beban = chartOfAccount::where('tipe_akun', 'Beban')->get();
        if ($account_beban->isEmpty()) {
            $account_beban = chartOfAccount::all();
        }
        $sales_taxes = SalesTaxes::all();
        $departements = Departement::where('status', 1)->get();
        $firstKasBank = chartOfAccount::where('kode_akun', 'LIKE', '1101%')
            ->orWhere('kode_akun', 'LIKE', '1102%')
            ->first();
        $defaultVoucher = PaymentExpense::generateVoucherNumber(optional($firstKasBank)->id, date('Y-m-d'));

        return view('payment_expense.create', compact(
            'account',
            'vendor',
            'account_beban',
            'sales_taxes',
            'departements',
            'categorySources',
            'defaultVoucher'
        ));
    }

    /**
     * Simpan payment expense baru + upload dokumen + buat voucher + posting jurnal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'              => 'required|date',
            'departemen_id'     => 'nullable|exists:departements,id',
            'category_source'   => 'required|string|max:100',
            'source'            => 'nullable|string|max:255',
            'account_header_id' => 'required|exists:chart_of_accounts,id',
            'vendor_id'         => 'nullable|exists:vendors,id',
            'notes'             => 'nullable|string',
            'attachment'        => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:5120',
            'items'             => 'required|array|min:1',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
            'items.*.deskripsi'  => 'nullable|string|max:255',
            'items.*.amount'     => 'required|numeric|min:0.01',
            'items.*.tax_id'     => 'nullable|exists:sales_taxes,id',
        ]);

        DB::beginTransaction();
        try {
            // 1. Generate nomor voucher otomatis berdasarkan akun kas/bank jika belum diisi manual
            $voucherNumber = $request->voucher_number ?: PaymentExpense::generateVoucherNumber($request->account_header_id, $request->date);

            // 2. Handle upload dokumen / bon pengeluaran
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $fileName = 'bon_' . date('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destination = public_path('uploads/payment_expenses');
                if (!File::exists($destination)) {
                    File::makeDirectory($destination, 0755, true);
                }
                $file->move($destination, $fileName);
                $attachmentPath = 'uploads/payment_expenses/' . $fileName;
            }

            // 3. Simpan header PaymentExpense dengan user_id yang login
            $expense = PaymentExpense::create([
                'user_id'          => Auth::id(),
                'departemen_id'    => $request->departemen_id ?: null,
                'voucher_number'   => $voucherNumber,
                'date'             => $request->date,
                'from_account_id'  => $request->account_header_id,
                'source'           => $request->source ?: $request->category_source,
                'category_source'  => $request->category_source,
                'vendor_id'        => $request->vendor_id ?: null,
                'notes'            => $request->notes ?: null,
                'attachment'       => $attachmentPath,
                'status'           => 'posted',
            ]);

            // 4. Simpan rincian pengeluaran (details)
            foreach ($validated['items'] as $item) {
                PaymentExpenseDetail::create([
                    'payment_expense_id' => $expense->id,
                    'account_id'         => $item['account_id'],
                    'deskripsi'          => $item['deskripsi'] ?? null,
                    'amount'             => $item['amount'],
                    'sales_taxes_id'     => $item['tax_id'] ?? null,
                ]);
            }

            // 5. Buat Journal Entry otomatis (source = voucher_number agar selaras dengan Buku Besar & Audit)
            $deptName = $expense->departemen ? ('[' . $expense->departemen->deskripsi . '] ') : '';
            $journalComment = "[{$voucherNumber}] {$deptName}" . ($request->notes ?: ($request->category_source));

            $journal = JournalEntry::create([
                'source'  => $voucherNumber ?: ('PaymentExpense#' . $expense->id),
                'tanggal' => $validated['date'],
                'comment' => $journalComment,
            ]);

            $totalDebit  = 0;

            // Detail Debit (Beban & Pajak Masukan)
            foreach ($validated['items'] as $item) {
                $debit = (float)$item['amount'];
                $account = chartOfAccount::find($item['account_id']);

                // Hubungkan departemen_akun_id jika tersedia
                $deptAkun = null;
                if ($expense->departemen_id) {
                    $deptAkun = DepartemenAkun::where('departemen_id', $expense->departemen_id)
                        ->where('akun_id', $item['account_id'])
                        ->first();
                }

                $journal->details()->create([
                    'kode_akun'          => $account->kode_akun,
                    'departemen_akun_id' => $deptAkun ? $deptAkun->id : null,
                    'debits'             => $debit,
                    'credits'            => 0,
                    'comment'            => $item['deskripsi'] ?: ($expense->category_source . ' - ' . $account->nama_akun),
                ]);

                $totalDebit += $debit;

                // Pajak jika ada
                if (!empty($item['tax_id'])) {
                    $tax = SalesTaxes::find($item['tax_id']);
                    if ($tax) {
                        $taxAmount = $debit * ($tax->rate / 100);
                        if ($tax->type === 'input_tax') {
                            $journal->details()->create([
                                'kode_akun' => $tax->purchaseAccount->kode_akun ?? 'TAX-IN',
                                'debits'    => $taxAmount,
                                'credits'   => 0,
                                'comment'   => "Pajak Masukan: {$tax->name}",
                            ]);
                            $totalDebit += $taxAmount;
                        } elseif ($tax->type === 'withholding_tax') {
                            $journal->details()->create([
                                'kode_akun' => $tax->salesAccount->kode_akun ?? 'TAX-WH',
                                'debits'    => 0,
                                'credits'   => $taxAmount,
                                'comment'   => "Pajak Pemotongan: {$tax->name}",
                            ]);
                            $totalDebit -= $taxAmount;
                        }
                    }
                }
            }

            // Credit ke Kas/Bank pengeluaran
            $accountKas = chartOfAccount::find($validated['account_header_id']);
            $journal->details()->create([
                'kode_akun' => $accountKas->kode_akun,
                'debits'    => 0,
                'credits'   => $totalDebit,
                'comment'   => "Pengeluaran Kas via {$accountKas->nama_akun} ({$voucherNumber})",
            ]);

            DB::commit();

            return redirect()
                ->route('payment_expense.index')
                ->with('success', "Payment Expense berhasil disimpan dengan No. Voucher: {$voucherNumber}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan pengeluaran: ' . $e->getMessage());
        }
    }

    /**
     * Detail payment expense
     */
    public function show($id)
    {
        $data = PaymentExpense::with(['details.Account', 'details.salesTaxes', 'Vendor', 'Account', 'user', 'departemen'])->findOrFail($id);

        // Scoping check: jika bukan admin/finance, tolak akses data user lain
        if (!$this->canAccessAllExpenses() && $data->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat pengeluaran pengguna lain.');
        }

        return view('payment_expense.show', compact('data'));
    }

    /**
     * Form edit payment expense
     */
    public function edit($id)
    {
        $payment_expense = PaymentExpense::with(['details', 'Vendor', 'Account', 'departemen'])->findOrFail($id);

        // Scoping check: jika bukan admin/finance, tolak edit data user lain
        if (!$this->canAccessAllExpenses() && $payment_expense->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengedit pengeluaran pengguna lain.');
        }

        $account = chartOfAccount::all();
        $vendor = Vendors::all();
        $account_beban = chartOfAccount::where('tipe_akun', 'Beban')->get();
        if ($account_beban->isEmpty()) {
            $account_beban = chartOfAccount::all();
        }
        $sales_taxes = SalesTaxes::all();
        $departements = Departement::where('status', 1)->get();
        $categorySources = $this->categorySources;

        return view('payment_expense.edit', compact(
            'payment_expense',
            'account',
            'vendor',
            'account_beban',
            'sales_taxes',
            'departements',
            'categorySources'
        ));
    }

    /**
     * Update payment expense
     */
    public function update(Request $request, $id)
    {
        $expense = PaymentExpense::findOrFail($id);

        // Scoping check: jika bukan admin/finance, tolak update data user lain
        if (!$this->canAccessAllExpenses() && $expense->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah pengeluaran pengguna lain.');
        }

        $validated = $request->validate([
            'date'              => 'required|date',
            'departemen_id'     => 'nullable|exists:departements,id',
            'category_source'   => 'required|string|max:100',
            'source'            => 'nullable|string|max:255',
            'account_header_id' => 'required|exists:chart_of_accounts,id',
            'vendor_id'         => 'nullable|exists:vendors,id',
            'notes'             => 'nullable|string',
            'attachment'        => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:5120',
            'items'             => 'required|array|min:1',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
            'items.*.deskripsi'  => 'nullable|string|max:255',
            'items.*.amount'     => 'required|numeric|min:0.01',
            'items.*.tax_id'     => 'nullable|exists:sales_taxes,id',
        ]);

        DB::beginTransaction();
        try {
            // Handle upload dokumen baru jika ada
            $attachmentPath = $expense->attachment;
            if ($request->hasFile('attachment')) {
                // Hapus file lama jika ada
                if ($attachmentPath && File::exists(public_path($attachmentPath))) {
                    File::delete(public_path($attachmentPath));
                }

                $file = $request->file('attachment');
                $fileName = 'bon_' . date('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destination = public_path('uploads/payment_expenses');
                if (!File::exists($destination)) {
                    File::makeDirectory($destination, 0755, true);
                }
                $file->move($destination, $fileName);
                $attachmentPath = 'uploads/payment_expenses/' . $fileName;
            }

            // Update header
            $expense->update([
                'departemen_id'    => $request->departemen_id ?: null,
                'date'             => $validated['date'],
                'from_account_id'  => $validated['account_header_id'],
                'source'           => $validated['source'] ?: $validated['category_source'],
                'category_source'  => $validated['category_source'],
                'vendor_id'        => $validated['vendor_id'] ?: null,
                'notes'            => $validated['notes'] ?? null,
                'attachment'       => $attachmentPath,
            ]);

            // Hapus detail lama & buat ulang
            $expense->details()->delete();
            foreach ($validated['items'] as $item) {
                $expense->details()->create([
                    'account_id'     => $item['account_id'],
                    'deskripsi'      => $item['deskripsi'] ?? null,
                    'amount'         => $item['amount'],
                    'sales_taxes_id' => $item['tax_id'] ?? null,
                ]);
            }

            // Update Journal Entry
            $oldJournal = JournalEntry::where('source', $expense->voucher_number)
                ->orWhere('source', 'PaymentExpense#' . $expense->id)
                ->first();
            if ($oldJournal) {
                $oldJournal->details()->delete();
                $oldJournal->delete();
            }

            $voucherNumber = $expense->voucher_number ?: ('EXP#' . $expense->id);
            $deptName = $expense->departemen ? ('[' . $expense->departemen->deskripsi . '] ') : '';
            $journalComment = "[{$voucherNumber}] {$deptName}" . ($request->notes ?: ($request->category_source));

            $journal = JournalEntry::create([
                'source'  => $voucherNumber,
                'tanggal' => $validated['date'],
                'comment' => $journalComment,
            ]);

            $totalDebit = 0;
            foreach ($validated['items'] as $item) {
                $debit = (float)$item['amount'];
                $account = chartOfAccount::find($item['account_id']);

                $deptAkun = null;
                if ($expense->departemen_id) {
                    $deptAkun = DepartemenAkun::where('departemen_id', $expense->departemen_id)
                        ->where('akun_id', $item['account_id'])
                        ->first();
                }

                $journal->details()->create([
                    'kode_akun'          => $account->kode_akun,
                    'departemen_akun_id' => $deptAkun ? $deptAkun->id : null,
                    'debits'             => $debit,
                    'credits'            => 0,
                    'comment'            => $item['deskripsi'] ?: ($expense->category_source . ' - ' . $account->nama_akun),
                ]);

                $totalDebit += $debit;

                if (!empty($item['tax_id'])) {
                    $tax = SalesTaxes::find($item['tax_id']);
                    if ($tax) {
                        $taxAmount = $debit * ($tax->rate / 100);
                        if ($tax->type === 'input_tax') {
                            $journal->details()->create([
                                'kode_akun' => $tax->purchaseAccount->kode_akun ?? 'TAX-IN',
                                'debits'    => $taxAmount,
                                'credits'   => 0,
                                'comment'   => "Pajak Masukan: {$tax->name}",
                            ]);
                            $totalDebit += $taxAmount;
                        } elseif ($tax->type === 'withholding_tax') {
                            $journal->details()->create([
                                'kode_akun' => $tax->salesAccount->kode_akun ?? 'TAX-WH',
                                'debits'    => 0,
                                'credits'   => $taxAmount,
                                'comment'   => "Pajak Pemotongan: {$tax->name}",
                            ]);
                            $totalDebit -= $taxAmount;
                        }
                    }
                }
            }

            $fromAccount = chartOfAccount::find($validated['account_header_id']);
            $journal->details()->create([
                'kode_akun' => $fromAccount->kode_akun,
                'debits'    => 0,
                'credits'   => $totalDebit,
                'comment'   => "Pengeluaran Kas via {$fromAccount->nama_akun} ({$voucherNumber})",
            ]);

            DB::commit();

            return redirect()
                ->route('payment_expense.index')
                ->with('success', 'Payment Expense berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui pengeluaran: ' . $e->getMessage());
        }
    }

    /**
     * Hapus payment expense
     */
    public function destroy($id)
    {
        $expense = PaymentExpense::findOrFail($id);

        // Scoping check: jika bukan admin/finance, tolak hapus data user lain
        if (!$this->canAccessAllExpenses() && $expense->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus pengeluaran pengguna lain.');
        }

        DB::transaction(function () use ($expense) {
            // Hapus file lampiran jika ada
            if ($expense->attachment && File::exists(public_path($expense->attachment))) {
                File::delete(public_path($expense->attachment));
            }

            // Hapus jurnal terkait
            $journal = JournalEntry::where('source', $expense->voucher_number)
                ->orWhere('source', 'PaymentExpense#' . $expense->id)
                ->first();
            if ($journal) {
                $journal->details()->delete();
                $journal->delete();
            }

            // Hapus detail & header
            $expense->details()->delete();
            $expense->delete();
        });

        return redirect()
            ->route('payment_expense.index')
            ->with('success', 'Payment Expense berhasil dihapus.');
    }

    /**
     * Cetak Bon / Bukti Kas Keluar (Expense Voucher)
     */
    public function printVoucher($id)
    {
        $data = PaymentExpense::with(['details.Account', 'details.salesTaxes', 'Vendor', 'Account', 'user', 'departemen'])->findOrFail($id);

        // Scoping check
        if (!$this->canAccessAllExpenses() && $data->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak bon pengeluaran pengguna lain.');
        }

        return view('payment_expense.print_voucher', compact('data'));
    }

    /**
     * AJAX Generator Nomor Voucher realtime berdasarkan akun kas/bank & tanggal
     */
    public function ajaxGetVoucherNumber(Request $request)
    {
        $accountId = $request->query('account_id') ?: $request->query('account_header_id');
        $date = $request->query('date', date('Y-m-d'));

        $voucher = PaymentExpense::generateVoucherNumber($accountId, $date);
        $prefix = PaymentExpense::getAccountPrefix($accountId);

        return response()->json([
            'voucher_number' => $voucher,
            'prefix'         => $prefix,
        ]);
    }
}

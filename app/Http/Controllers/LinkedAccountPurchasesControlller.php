<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use App\linkedAccounts;
use Illuminate\Http\Request;

class LinkedAccountPurchasesControlller extends Controller
{
    //
    public function index()
    {
        $linkedAccountPurchases = linkedAccounts::with('akun')
            ->where('modul', 'purchases')
            ->orderBy('kode') // opsional: urutkan berdasarkan kode
            ->get();

        return view('linkedAccountPurchases.index', compact('linkedAccountPurchases'));
    }
    public function create()
    {
        $akun1 = chartOfAccount::where('tipe_akun', 'Aset')->get();
        $akun2 = chartOfAccount::where('tipe_akun', 'Kewajiban')->get();
        $akun4 = chartOfAccount::where('tipe_akun', 'Pendapatan')->get();
        $akun5 = chartOfAccount::where('tipe_akun', 'Beban')->get();
        $accounts = chartOfAccount::where('tipe_akun', 'Ekuitas')->get();

        // Semua kode wajib
        $requiredCodes = [
            'Principal Bank Account',
            'Account Payable',
            'Freight Expense',
            'Early Payment Purchase Discount',
            'Prepayments Prepaid Orders',
        ];

        // Ambil kode yang sudah ada di DB
        $existingCodes = linkedAccounts::where('modul', 'purchases')
            ->pluck('kode')
            ->toArray();

        // Cari kode yang belum ada
        $missingCodes = array_diff($requiredCodes, $existingCodes);

        // Ambil akun bank dan sub-account (seperti kode kamu sebelumnya)
        $bankAccounts = chartOfAccount::whereRaw('LOWER(nama_akun) LIKE ?', ['%bank%'])
            ->whereRaw('LOWER(level_akun) = ?', ['account'])
            ->get();

        $bankPrefixes = $bankAccounts->pluck('kode_akun');
        $subBankAccounts = collect();
        foreach ($bankPrefixes as $prefix) {
            $subBankAccounts = $subBankAccounts->merge(
                chartOfAccount::whereRaw('LOWER(level_akun) = ?', ['sub account'])
                    ->where('kode_akun', 'like', $prefix . '%')
                    ->get()
            );
        }

        return view('linkedAccountPurchases.create', compact(
            'accounts',
            'akun1',
            'akun2',
            'akun4',
            'akun5',
            'subBankAccounts',
            'missingCodes'
        ));
    }

    public function store(Request $request)
    {
        $accounts = $request->input('accounts', []);

        foreach ($accounts as $kode => $akun_id) {
            linkedAccounts::updateOrInsert(
                ['modul' => 'purchases', 'kode' => $kode],
                ['akun_id' => $akun_id, 'updated_at' => now()]
            );
        }

        return redirect()->route('linkedAccountPurchases.index')
            ->with('success', 'Linked Account Purchases berhasil disimpan.');
    }

    public function destroy($id)
    {
        $linkedAccount = linkedAccounts::findOrFail($id);

        try {
            $linkedAccount->delete();
            return redirect()
                ->route('linkedAccountPurchases.index')
                ->with('success', 'Linked Account Purchases berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('linkedAccountPurchases.index')
                ->with('error', 'Terjadi kesalahan saat menghapus: ' . $e->getMessage());
        }
    }
}

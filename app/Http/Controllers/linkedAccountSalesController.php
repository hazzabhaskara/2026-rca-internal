<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use App\linkedAccounts;
use Illuminate\Http\Request;

class linkedAccountSalesController extends Controller
{
    public function index()
    {
        $linkedAccountSales = linkedAccounts::with('akun')
            ->where('modul', 'sales')
            ->orderBy('kode') // opsional: urutkan berdasarkan kode
            ->get();

        return view('linkedAccountSales.index', compact('linkedAccountSales'));
    }
    public function create()
    {
        $akun1 = chartOfAccount::where('tipe_akun', 'Aset')->get();
        $akun2 = chartOfAccount::where('tipe_akun', 'Kewajiban')->get();
        $akun4 = chartOfAccount::where('tipe_akun', 'Pendapatan')->get();
        $accounts = chartOfAccount::where('tipe_akun', 'Ekuitas')->get();
        $akun = chartOfAccount::all();

        // Ambil semua akun BANK dengan level ACCOUNT
        $bankAccounts = chartOfAccount::whereRaw('LOWER(nama_akun) LIKE ?', ['%bank%'])
            ->whereRaw('LOWER(level_akun) = ?', ['account'])
            ->get();

        // Ambil prefix dari kode_akun untuk digunakan sebagai acuan pencarian sub account
        $bankPrefixes = $bankAccounts->pluck('kode_akun');

        $requiredCodes = [
            'Principal Bank Account',
            'Account Receivable',
            'Default Revenue',
            'Freight Revenue',
            'Early Payment Discount',
            'Prepaid Orders',
        ];
        // Ambil kode yang sudah ada di DB
        $existingCodes = linkedAccounts::where('modul', 'sales')
            ->pluck('kode')
            ->toArray();

        // Cari kode yang belum ada
        $missingCodes = array_diff($requiredCodes, $existingCodes);

        // Ambil sub account berdasarkan prefix kode_akun
        $subBankAccounts = collect();
        foreach ($bankPrefixes as $prefix) {
            $subBankAccounts = $subBankAccounts->merge(
                chartOfAccount::whereRaw('LOWER(level_akun) = ?', ['sub account'])
                    ->where('kode_akun', 'like', $prefix . '%')
                    ->get()
            );
        }

        return view('linkedAccountSales.create', compact(
            'accounts',
            'akun1',
            'akun2',
            'akun4',
            'akun',
            'subBankAccounts',
            'missingCodes'
        ));
    }

    public function store(Request $request)
    {
        $accounts = $request->input('accounts', []);

        foreach ($accounts as $kode => $akun_id) {
            linkedAccounts::updateOrInsert(
                ['modul' => 'sales', 'kode' => $kode],
                ['akun_id' => $akun_id, 'updated_at' => now()]
            );
        }

        return redirect()->route('linkedAccountSales.index')
            ->with('success', 'Linked Account Sales berhasil disimpan.');
    }
    public function destroy($id)
    {
        $linkedAccount = linkedAccounts::findOrFail($id);

        try {
            $linkedAccount->delete();
            return redirect()
                ->route('linkedAccountSales.index')
                ->with('success', 'Linked Account Sales berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('linkedAccountSales.index')
                ->with('error', 'Terjadi kesalahan saat menghapus: ' . $e->getMessage());
        }
    }
}

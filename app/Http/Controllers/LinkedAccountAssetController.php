<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use App\linkedAccounts;
use Illuminate\Http\Request;

class LinkedAccountAssetController extends Controller
{
    //
    public function index()
    {
        $linkedAccountAsset = linkedAccounts::with('akun')
            ->where('modul', 'asset')
            ->orderBy('kode') // opsional: urutkan berdasarkan kode
            ->get();

        return view('linkedAccountAsset.index', compact('linkedAccountAsset'));
    }
    public function create()
    {
        $akun1 = chartOfAccount::where('tipe_akun', 'Aset')->get();
        $akun2 = chartOfAccount::where('tipe_akun', 'Kewajiban')->get();
        $akun4 = chartOfAccount::where('tipe_akun', 'Pendapatan')->get();
        $akun5 = chartOfAccount::where('tipe_akun', 'Beban')->get();

        // Ambil semua akun BANK dengan level ACCOUNT
        $bankAccounts = chartOfAccount::whereRaw('LOWER(nama_akun) LIKE ?', ['%bank%'])
            ->whereRaw('LOWER(level_akun) = ?', ['account'])
            ->get();

        // Ambil prefix dari kode_akun untuk digunakan sebagai acuan pencarian sub account
        $bankPrefixes = $bankAccounts->pluck('kode_akun');

        $requiredCodes = [
            'Expenses',
            'Accumulated Depreciation/Amortisation',
        ];

        // Ambil kode yang sudah ada di DB
        $existingCodes = linkedAccounts::where('modul', 'asset')
            ->pluck('kode')
            ->toArray();
        $missingCodes = array_diff($requiredCodes, $existingCodes);
        return view('linkedAccountAsset.create', compact(
            'akun1',
            'akun5',
            'missingCodes'

        ));
    }
    public function store(Request $request)
    {
        $accounts = $request->input('accounts', []);

        foreach ($accounts as $kode => $akun_id) {
            linkedAccounts::updateOrInsert(
                ['modul' => 'asset', 'kode' => $kode],
                ['akun_id' => $akun_id, 'updated_at' => now()]
            );
        }

        return redirect()->route('linkedAccountAsset.index')
            ->with('success', 'Linked Account Asset berhasil disimpan.');
    }
    public function destroy($id)
    {
        $linkedAccount = linkedAccounts::findOrFail($id);

        try {
            $linkedAccount->delete();
            return redirect()
                ->route('linkedAccountAsset.index')
                ->with('success', 'Linked Account Asset berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('linkedAccountAsset.index')
                ->with('error', 'Terjadi kesalahan saat menghapus: ' . $e->getMessage());
        }
    }
}

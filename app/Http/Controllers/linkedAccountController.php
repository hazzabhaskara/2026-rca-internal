<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use App\linkedAccounts;
use Illuminate\Http\Request;

class linkedAccountController extends Controller
{
    //
    public function index()
    {
        $linkedAccounts = linkedAccounts::with('akun')->where('modul', 'setup')->get();
        return view('linkedAccount.index', compact('linkedAccounts'));
    }
    public function edit()
    {
        $accounts = chartOfAccount::orderBy('kode_akun')->get();

        $selected = linkedAccounts::where('modul', 'sales')->pluck('akun_id', 'kode')->toArray();

        return view('linkedAccount.edit', compact('accounts', 'selected'));
    }

    public function create()
    {
        $accounts = ChartOfAccount::where('tipe_akun', 'Ekuitas')->get(); // kelompok akun 3
        return view('linkedAccount.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|in:RETAINED EARNINGS',
            'akun_id' => 'required|exists:chart_of_accounts,id',
        ]);

        linkedAccounts::updateOrCreate(
            ['modul' => 'setup', 'kode' => $request->kode],
            ['akun_id' => $request->akun_id]
        );

        return redirect()->route('linkedAccount.index')->with('success', 'Linked account berhasil disimpan.');
    }
    public function destroy($id)
    {
        $linkedAccount = linkedAccounts::findOrFail($id);

        try {
            $linkedAccount->delete();
            return redirect()
                ->route('linkedAccount.index')
                ->with('success', 'Linked Account berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('linkedAccount.index')
                ->with('error', 'Terjadi kesalahan saat menghapus: ' . $e->getMessage());
        }
    }
}

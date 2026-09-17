<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\FiscalAccount;
use App\KlasifikasiAkun;
use App\NumberingAccount;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = chartOfAccount::with('klasifikasiAkun');

        // 🔍 Search berdasarkan kode_akun atau nama_akun
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_akun', 'like', '%' . $request->search . '%')
                    ->orWhere('nama_akun', 'like', '%' . $request->search . '%');
            });
        }

        // 🧾 Filter tipe_akun
        if ($request->filled('filter_tipe')) {
            $query->where('tipe_akun', $request->filter_tipe);
        }

        // 📄 Pagination + tetap menyimpan parameter (search, filter_tipe) di URL
        $chartOfAccounts = $query->orderBy('kode_akun')->get();

        // 🔄 Ambil semua tipe akun unik untuk dropdown filter
        $tipeAkunOptions = ChartOfAccount::select('tipe_akun')->distinct()->pluck('tipe_akun');

        return view('chartOfAccount.index', compact('chartOfAccounts', 'tipeAkunOptions'));
    }

    public function create()
    {
        $tipe_akun = ['Aset', 'Kewajiban', 'Ekuitas', 'Pendapatan', 'Beban'];
        $numberings = NumberingAccount::all();
        $headers = ChartOfAccount::where('level_akun', 'header')->get();
        $parent_akun = KlasifikasiAkun::whereNull('parent_id')->get(); // hanya ambil induk klasifikasi

        $chartOfAccounts = null; // Tambahan agar blade tidak error

        $fiscalAccount = FiscalAccount::all();

        return view('chartOfAccount.create', compact('tipe_akun', 'parent_akun', 'headers', 'numberings', 'chartOfAccounts', 'fiscalAccount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_akun' => 'required|unique:chart_of_accounts,kode_akun',
            'nama_akun' => 'required|string',
            'tipe_akun' => 'required|in:Aset,Kewajiban,Ekuitas,Pendapatan,Beban',
            'level_akun' => 'nullable|string',
            'aktif' => 'nullable|in:on,1,0',
            'omit_zero_balance' => 'nullable|in:on,1,0',
            'allow_project_allocation' => 'nullable|in:on,1,0',
            'catatan' => 'nullable|string',
            'catatan_pajak' => 'nullable|string',
            'fiscal_account_id' => 'nullable|exists:fiscal_accounts,id',
            'klasifikasi_id' => 'nullable|exists:klasifikasi_akuns,id',
            'is_income_tax' => 'nullable|in:on,1,0',
        ]);

        // Ambil jumlah digit dari numbering_accounts berdasarkan tipe akun
        $numbering = numberingAccount::where('nama_grup', $request->tipe_akun)->first();

        if (!$numbering) {
            return back()->withErrors(['tipe_akun' => 'Tipe akun tidak ditemukan dalam pengaturan numbering.']);
        }

        if (strlen($request->kode_akun) != $numbering->jumlah_digit) {
            return back()->withErrors(['kode_akun' => 'Kode akun harus terdiri dari ' . $numbering->jumlah_digit . ' digit.'])->withInput();
        }

        // Validasi range
        if ($request->kode_akun < $numbering->nomor_akun_awal || $request->kode_akun > $numbering->nomor_akun_akhir) {
            return back()->withErrors([
                'kode_akun' => 'Kode akun tidak berada dalam range yang diizinkan.'
            ])->withInput();
        }

        // Simpan Chart of Account
        ChartOfAccount::create([
            'kode_akun' => $request->kode_akun,
            'nama_akun' => $request->nama_akun,
            'tipe_akun' => $request->tipe_akun,
            'level_akun' => $request->level_akun,
            'aktif' => $request->has('aktif'),
            'omit_zero_balance' => $request->has('omit_zero_balance'),
            'allow_project_allocation' => $request->has('allow_project_allocation'),
            'catatan' => $request->catatan,
            'catatan_pajak' => $request->catatan_pajak,
            'klasifikasi_id' => $request->klasifikasi_id,
            'fiscal_account_id' => $request->fiscal_account_id,
            'is_income_tax' => $request->is_income_tax
        ]);

        return redirect()->route('chartOfAccount.index')->with('success', 'Chart of Account berhasil ditambahkan.');
    }
    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {

        // dd($request->all());
        $request->validate([
            'kode_akun' => 'required|unique:chart_of_accounts,kode_akun,' . $chartOfAccount->id,
            'nama_akun' => 'required|string',
            'tipe_akun' => 'required|in:Aset,Kewajiban,Ekuitas,Pendapatan,Beban',
            'level_akun' => 'nullable|string',
            'fiscal_account_id' => 'nullable|exists:fiscal_accounts,id',
            'klasifikasi_id' => 'nullable|exists:klasifikasi_akuns,id',
            'aktif' => 'nullable|in:on,1,0',
            'omit_zero_balance' => 'nullable|boolean',
            'allow_project_allocation' => 'nullable|boolean',
            'catatan' => 'nullable|string',
            'catatan_pajak' => 'nullable|string',
            'is_income_tax' => 'nullable|in:on,1,0',
        ]);

        // Ambil jumlah digit dari numbering_accounts berdasarkan tipe akun
        $numbering = numberingAccount::where('nama_grup', $request->tipe_akun)->first();

        if (!$numbering) {
            return back()->withErrors(['tipe_akun' => 'Tipe akun tidak ditemukan dalam pengaturan numbering.']);
        }

        if (strlen($request->kode_akun) != $numbering->jumlah_digit) {
            return back()->withErrors(['kode_akun' => 'Kode akun harus terdiri dari ' . $numbering->jumlah_digit . ' digit.'])->withInput();
        }

        // Validasi range
        if ($request->kode_akun < $numbering->nomor_akun_awal || $request->kode_akun > $numbering->nomor_akun_akhir) {
            return back()->withErrors([
                'kode_akun' => 'Kode akun tidak berada dalam range yang diizinkan.'
            ])->withInput();
        }

        $chartOfAccount->update([
            'kode_akun' => $request->kode_akun,
            'nama_akun' => $request->nama_akun,
            'tipe_akun' => $request->tipe_akun,
            'level_akun' => $request->level_akun,
            'fiscal_account_id' => $request->fiscal_account_id,
            'klasifikasi_id' => $request->klasifikasi_id,
            'aktif' => $request->has('aktif'),
            'omit_zero_balance' => $request->has('omit_zero_balance'),
            'allow_project_allocation' => $request->has('allow_project_allocation'),
            'catatan' => $request->catatan,
            'catatan_pajak' => $request->catatan_pajak,
            'is_income_tax' => $request->is_income_tax
        ]);

        return redirect()->route('chartOfAccount.index')->with('success', 'Chart of Account berhasil diperbarui.');
    }
    public function edit(string $id): View
    {
        //get post by ID
        $chartOfAccounts = chartOfAccount::findOrFail($id);
        $tipe_akun = ['Aset', 'Kewajiban', 'Ekuitas', 'Pendapatan', 'Beban'];
        $parent_akun = ChartOfAccount::whereIn('level_akun', ['header', 'subheader'])->get();
        $klasifikasi = KlasifikasiAkun::all();
        $fiscalAccount = FiscalAccount::all();


        //render view with post
        return view('chartOfAccount.edit', compact('chartOfAccounts', 'tipe_akun', 'parent_akun', 'klasifikasi', 'fiscalAccount'));
    }
    public function show(string $id): View
    {
        //get post by ID
        $chartOfAccounts = chartOfAccount::findOrFail($id);

        //render view with post
        return view('chartOfAccount.show', compact('chartOfAccounts'));
    }
    public function destroy($id): RedirectResponse
    {
        //get post by ID
        $chartOfAccounts = chartOfAccount::findOrFail($id);

        //delete image


        //delete post
        $chartOfAccounts->delete();

        //redirect to index
        return redirect()->route('chartOfAccount.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

    public function truncate(): RedirectResponse
    {
        try {
            // Disable foreign key checks to allow truncating master data
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Truncate table
            chartOfAccount::truncate();
            
            // Re-enable foreign key checks
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return redirect()->route('chartOfAccount.index')
                ->with('success', 'Seluruh data Account List berhasil dikosongkan.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal truncate chart of account: ' . $e->getMessage());

            return redirect()->route('chartOfAccount.index')
                ->with('error', 'Gagal mengosongkan data: ' . $e->getMessage());
        }
    }
}

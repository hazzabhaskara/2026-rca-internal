<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use App\linkedAccounts;
use App\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LinkedAccountClosingController extends Controller
{
    //
    public function index()
    {
        $data = linkedAccounts::with(['unitKerja', 'departemen'])->where('modul', 'closing')->get();
        return view('linkedAccount_closing.index', compact('data'));
    }
    public function create()
    {

        // $akun = chartOfAccount::all();
        $unitKerja = UnitKerja::all();
        $akun = chartOfAccount::with('departemenAkun.departemen')->get();
        return view('linkedAccount_closing.create', compact(
            'akun',
            'unitKerja'
        ));
    }
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'kode' => 'required|array',
                'akun_id' => 'required|array',
            ]);

            foreach ($request->kode as $i => $kode) {
                // pisahkan kombinasi akun_id|departemen_id dari dropdown
                list($akun_id, $departemen_id) = explode('|', $request->akun_id[$i]);

                LinkedAccounts::create([
                    'modul' => 'closing',
                    'unit_kerja_id' => $request->unit_kerja_id,
                    'kode' => $kode,
                    'akun_id' => $akun_id,
                    'departemen_id' => $departemen_id == 0 ? null : $departemen_id,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('linkedAccount_closing.index')
                ->with('success', 'Semua linked account berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function destroy($id)
    {
        $linkedAccount = linkedAccounts::findOrFail($id);

        try {
            $linkedAccount->delete();
            return redirect()
                ->route('linkedAccount_closing.index')
                ->with('success', 'Linked Account Closing berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('linkedAccount_closing.index')
                ->with('error', 'Terjadi kesalahan saat menghapus: ' . $e->getMessage());
        }
    }
    public function getLinkedAccountByUnit($unitId)
    {
        $linkedAccounts = \App\linkedAccounts::with(['akun', 'departemen'])
            ->where('unit_kerja_id', $unitId)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'kode' => $item->kode,
                    'akun' => ($item->akun->kode_akun ?? '-') . ' - ' . ($item->akun->nama_akun ?? '-'),
                    'departemen' => $item->departemen->deskripsi ?? '-',
                ];
            });

        return response()->json(['linkedAccounts' => $linkedAccounts]);
    }
}

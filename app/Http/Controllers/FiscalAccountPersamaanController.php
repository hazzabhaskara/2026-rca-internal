<?php

namespace App\Http\Controllers;

use App\FiscalAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FiscalAccountPersamaanController extends Controller
{
    //
    public function index()
    {

        $data = FiscalAccount::orderBy('kode_akun', 'asc')->get();

        $detail = DB::table('fiscal_accounts')
            ->leftjoin('chart_of_accounts', 'fiscal_accounts.id', '=', 'chart_of_accounts.fiscal_account_id')
            ->select('chart_of_accounts.kode_akun', 'chart_of_accounts.nama_akun')
            ->get();

        return view('fiscal_account_persamaan.index', compact('data', 'detail'));
    }
    public function show($id)
    {
        $fiscal = DB::table('fiscal_accounts')
            ->where('id', $id)
            ->first();

        $detail = DB::table('chart_of_accounts')
            ->where('fiscal_account_id', $id)
            ->select('kode_akun', 'nama_akun', 'tipe_akun', 'level_akun')
            ->orderBy('kode_akun', 'asc')
            ->get();

        return view('fiscal_account_persamaan.show', compact('fiscal', 'detail'));
    }
}

<?php

namespace App\Http\Controllers;

use App\NumberingAccount;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class NumberingAccountController extends Controller
{
    //
    public function index()
    {
        $numberingAccount = NumberingAccount::latest()->paginate(5);
        return view('numbering_account.index', compact('numberingAccount'));
    }
    public function create(): View

    {
        return view('numbering_account.create');
    }
    public function store(Request $request)
    {

        if (NumberingAccount::count() > 0) {
            return redirect()->route('numbering_account.index')
                ->with('error', 'Numbering account sudah ada, tidak bisa tambah lagi.');
        }
        $request->validate([
            'digit' => 'required|integer|min:5|max:8',
            'nama_grup.*' => 'required',
            'nomor_akun_awal.*' => 'required',
            'nomor_akun_akhir.*' => 'required',
        ]);
        foreach ($request->nama_grup as $index => $grup) {
            numberingAccount::create([
                'nama_grup' => $grup,
                'jumlah_digit' => $request->digit,
                'nomor_akun_awal' => $request->nomor_akun_awal[$index],
                'nomor_akun_akhir' => $request->nomor_akun_akhir[$index],
            ]);
        }

        return redirect()->route('numbering_account.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }
    public function show(string $id): View
    {
        //get post by ID
        $numberingAccount = numberingAccount::findOrFail($id);

        //render view with post
        return view('numbering_account.show', compact('numberingAccount'));
    }
}

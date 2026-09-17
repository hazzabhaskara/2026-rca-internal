<?php

namespace App\Http\Controllers;

use App\FiscalAccount;
use Illuminate\Http\Request;

class FiscalAccountController extends Controller
{
    //
    public function index()
    {
        $data = FiscalAccount::orderBy('kode_akun', 'asc')->get();
        return view('fiscal_account.index', compact('data'));
    }
    public function create()
    {
        return view('fiscal_account.create');
    }
    public function store(Request $request)
    {
        // 1️⃣ Validasi array
        $validated = $request->validate([
            'kode_akun.*'          => 'required|string|max:255',
            'nama_akun.*'     => 'required|string',
        ]);

        // 3️⃣ Siapkan data untuk insert batch
        $data = [];
        for ($i = 0; $i < count($request->kode_akun); $i++) {
            if (empty($request->kode_akun[$i])) continue;

            $data[] = [
                'kode_akun' => $request->kode_akun[$i],
                'nama_akun'          => $request->nama_akun[$i],
                'created_at'    => now(),
                'updated_at'    => now()
            ];
        }

        // 4️⃣ Simpan data
        if (count($data)) {
            \App\FiscalAccount::insert($data);
            return redirect()->route('fiscal_account.index')->with('success', 'Data berhasil ditambahkan.');
        } else {
            return back()->with('error', 'Tidak ada data yang dimasukkan.');
        }
    }
    public function edit($id)
    {
        $data = FiscalAccount::findOrFail($id);
        return view('fiscal_account.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kode_akun' => 'required|string',
            'nama_akun' => 'required|string',

        ]);

        $fiscal_account = FiscalAccount::findOrFail($id);

        $fiscal_account->update($validated);

        return redirect()->route('fiscal_account.index')->with('success', 'FiscalAccount berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $fiscal_account = FiscalAccount::findOrFail($id);

        $fiscal_account->delete();

        return redirect()->route('fiscal_account.index')->with('success', ' Data berhasil dihapus.');
    }
}

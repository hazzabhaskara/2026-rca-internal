<?php

namespace App\Http\Controllers;

use App\KlasifikasiAkun;
use App\NumberingAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KlasifikasiAkunController extends Controller
{
    //
    public function index()
    {
        $klasifikasis = KlasifikasiAkun::with(['numberingAccount', 'parent'])->paginate(10);

        $numberingAccounts = KlasifikasiAkun::with('numberingAccount')
            ->get()
            ->pluck('numberingAccount.nama_grup')
            ->filter()
            ->unique()
            ->values();

        return view('klasifikasiAkun.index', compact('klasifikasis', 'numberingAccounts'));
    }
    public function create()
    {
        $numberingAccounts = NumberingAccount::all();
        $parentOptions = KlasifikasiAkun::all();

        return view('klasifikasiAkun.create', compact('numberingAccounts', 'parentOptions'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_klasifikasi' => 'required|string|unique:klasifikasi_akuns,kode_klasifikasi',
            'nama_klasifikasi' => 'required|string',
            'numbering_account_id' => 'required|exists:numbering_accounts,id',
            'parent_id' => 'nullable|exists:klasifikasi_akuns,id',
            'urutan' => 'nullable|integer',
            'deskripsi' => 'nullable|string',
            'aktif' => 'required|boolean',
        ]);

        KlasifikasiAkun::create($validated);

        return redirect()->route('klasifikasiAkun.index')->with('success', 'Klasifikasi Akun berhasil ditambahkan.');
    }
    public function show($id)
    {
        $klasifikasi = KlasifikasiAkun::with(['numberingAccount', 'parent'])->findOrFail($id);

        return view('klasifikasiAkun.show', compact('klasifikasi'));
    }
    public function edit($id)
    {
        $klasifikasi = KlasifikasiAkun::findOrFail($id);
        $numberingAccounts = NumberingAccount::all();
        $parentOptions = KlasifikasiAkun::where('id', '!=', $id)->get(); // hindari self-reference

        return view('klasifikasiAkun.edit', compact('klasifikasi', 'numberingAccounts', 'parentOptions'));
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kode_klasifikasi' => 'required|string|max:50',
            'nama_klasifikasi' => 'required|string|max:100',
            'numbering_account_id' => 'required|exists:numbering_accounts,id',
            'urutan' => 'nullable|integer',
            'aktif' => 'required|boolean',
            'parent_id' => 'nullable|exists:klasifikasi_akuns,id',
            'deskripsi' => 'nullable|string',
        ]);

        $klasifikasi = KlasifikasiAkun::findOrFail($id);
        $klasifikasi->update($validated);

        return redirect()->route('klasifikasiAkun.index')->with('success', 'Klasifikasi Akun berhasil diperbarui.');
    }
    public function destroy($id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($id) {
                $klasifikasi = KlasifikasiAkun::with(['account'])->findOrFail($id);

                // 🚫 Cek apakah sudah dipakai di Invoice
                if ($klasifikasi->account()->exists()) {
                    throw new \Exception("Klasifikasi ini sudah digunakan dalam Account tidak bisa dihapus.");
                }
                // ✅ Kalau aman, hapus (details ikut terhapus otomatis via cascade)
                $klasifikasi->delete();
            });

            return redirect()->route('klasifikasiAkun.index')->with('success', 'Klasifikasi Akun berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('klasifikasiAkun.index')->with('error', $e->getMessage());
        }
    }
}

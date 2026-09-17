<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use App\Models\PayementMethod;
use App\PaymentMethod;
use App\PaymentMethodDetail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentMethodController extends Controller
{
    //
    public function index()
    {
        $data = PaymentMethod::latest()->paginate(10);
        return view('PaymentMethod.index', compact('data'));
    }
    public function create()
    {
        // Ambil semua COA untuk options
        // Pakai ChartOfAccount::all(); sesuaikan jika modelmu bernama chartOfAccount
        $account = ChartOfAccount::orderBy('kode_akun')->get();

        return view('PaymentMethod.create', compact('account'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_jenis' => 'required|string|max:100',
            'nama_jenis' => 'required|string|max:150',
            'status'     => 'required|boolean',

            // sesuai Blade: name="account_id[]" dan name="deskripsi[]"
            'account_id'     => 'required|array|min:1',
            'account_id.*'   => 'nullable|integer|distinct|exists:chart_of_accounts,id',
            'deskripsi'  => 'nullable|array',
            'deskripsi.*' => 'nullable|string|max:255',
        ], [
            'account_id.required'   => 'Minimal pilih satu account/COA.',
            'account_id.*.exists'   => 'Ada account/COA yang tidak valid.',
            'account_id.*.distinct' => 'Terdapat duplikasi pilihan account.',
        ]);

        DB::transaction(function () use ($request) {
            // 1) Simpan header payment method
            $pm = PaymentMethod::create([
                'kode_jenis' => $request->kode_jenis,
                'nama_jenis' => $request->nama_jenis,
                'status_payment' => 0,
                'status'     => (int) $request->status, // pastikan boolean/int
            ]);

            // 2) Rangkai detail dari baris-baris form
            $coaIds = $request->input('account_id', []);      // array of id COA (boleh ada null)
            $desks  = $request->input('deskripsi', []);   // array of deskripsi (nullable)

            $rows = [];
            foreach ($coaIds as $i => $coaId) {
                // skip baris kosong (user belum pilih akun)
                if (empty($coaId)) {
                    continue;
                }
                $rows[] = [
                    'payment_method_id' => $pm->id,
                    'account_id'            => (int) $coaId,
                    'deskripsi'         => $desks[$i] ?? null,
                    'is_default'        => 0,          // sesuaikan bila punya flag default
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }

            // jika semua baris kosong, anggap invalid
            if (empty($rows)) {
                // paksa gagal agar old() terisi dan user diberi pesan
                abort(redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['account_id' => 'Minimal pilih satu account/COA.']));
            }

            PaymentMethodDetail::insert($rows);
        });

        return redirect()->route('PaymentMethod.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }
    public function show($id)
    {
        $paymentMethod = PaymentMethod::with(['details.chartOfAccount'])->findOrFail($id);
        return view('PaymentMethod.show', compact('paymentMethod'));
    }

    public function edit(string $id)
    {
        $paymentMethod = PaymentMethod::with(['details' => function ($q) {
            $q->orderBy('id');
        }])->findOrFail($id);

        // List COA untuk dropdown
        $account = ChartOfAccount::orderBy('kode_akun')->get();

        return view('PaymentMethod.edit', compact('paymentMethod', 'account'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_jenis' => 'required|string|max:100',
            'nama_jenis' => 'required|string|max:150',
            'status'     => 'required|boolean',

            // arrays (bisa kosong kalau semua baris dihapus, tapi kita cek minimal 1 terisi setelah filter)
            'detail_id'    => 'nullable|array',
            'detail_id.*'  => 'nullable|integer|exists:payment_method_details,id',
            'account_id'   => 'nullable|array',
            'account_id.*' => 'nullable|integer|exists:chart_of_accounts,id',
            'deskripsi'    => 'nullable|array',
            'deskripsi.*'  => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $id) {
            // 1) Update header
            $pm = PaymentMethod::findOrFail($id);
            $pm->update([
                'kode_jenis' => $request->kode_jenis,
                'nama_jenis' => $request->nama_jenis,
                'status_payment' => 0,
                'status'     => (int) $request->status,
            ]);

            // 2) Susun baris detail dari input
            $detailIds = $request->input('detail_id', []);   // id detail lama (nullable)
            $accIds    = $request->input('account_id', []);  // id COA
            $desks     = $request->input('deskripsi', []);   // deskripsi

            $rows = [];
            foreach ($accIds as $i => $accId) {
                // skip baris tanpa account
                if (empty($accId)) continue;

                $rows[] = [
                    'detail_id'         => $detailIds[$i] ?? null, // bisa null untuk baris baru
                    'payment_method_id' => $pm->id,
                    'account_id'            => (int) $accId,
                    'deskripsi'         => $desks[$i] ?? null,
                    'is_default'        => 0,
                ];
            }

            if (empty($rows)) {
                abort(redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['account_id' => 'Minimal pilih satu account/COA.']));
            }

            // 3) Ambil id detail lama untuk kebutuhan delete
            $existingIds = PaymentMethodDetail::where('payment_method_id', $pm->id)->pluck('id')->all();

            // 4) Upsert/update & kumpulkan id yang masih dipertahankan
            $keptIds = [];
            foreach ($rows as $r) {
                if (!empty($r['detail_id'])) {
                    // update baris lama
                    $detail = PaymentMethodDetail::where('payment_method_id', $pm->id)
                        ->where('id', $r['detail_id'])
                        ->first();

                    if ($detail) {
                        $detail->update([
                            'account_id'     => $r['account_id'],
                            'deskripsi'  => $r['deskripsi'],
                            'is_default' => $r['is_default'],
                        ]);
                        $keptIds[] = $detail->id;
                    }
                } else {
                    // insert baris baru
                    $new = PaymentMethodDetail::create([
                        'payment_method_id' => $pm->id,
                        'account_id'            => $r['account_id'],
                        'deskripsi'         => $r['deskripsi'],
                        'is_default'        => $r['is_default'],
                    ]);
                    $keptIds[] = $new->id;
                }
            }

            // 5) Hapus baris yang tidak lagi ada di form
            $toDelete = array_diff($existingIds, $keptIds);
            if (!empty($toDelete)) {
                PaymentMethodDetail::whereIn('id', $toDelete)->delete();
            }
        });

        return redirect()->route('PaymentMethod.index')->with('success', 'Data berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $PayementMethod = PaymentMethod::findOrFail($id);

        $PayementMethod->delete();

        return redirect()->route('PaymentMethod.index')->with('success', 'Data berhasil dihapus.');
    }
    public function accounts($id)
    {
        $pm = PaymentMethod::with(['details.chartOfAccount'])->findOrFail($id);

        $accounts = $pm->details->map(function ($d) {
            $coa = $d->chartOfAccount; // <— konsisten dengan with()
            return [
                'detail_id'  => $d->id,
                'account_id' => $d->account_id,     // pastikan FK ke tabel COA benar
                'kode_akun'  => $coa->kode_akun ?? '-',   // <— pakai $coa
                'nama_akun'  => $coa->nama_akun ?? '-',   // <— pakai $coa
                'deskripsi'  => $d->deskripsi,
                'is_default' => (bool) $d->is_default,
            ];
        })->values();

        return response()->json([
            'payment_method_id' => $pm->id,
            'payment_method'    => $pm->nama_jenis,
            'accounts'          => $accounts,
        ]);
    }
}

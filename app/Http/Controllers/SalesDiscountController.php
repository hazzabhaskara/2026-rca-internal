<?php

namespace App\Http\Controllers;

use App\SalesDiscount;
use App\SalesDiscountDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesDiscountController extends Controller
{
    //
    public function index()
    {
        $data = SalesDiscount::all();
        return view('sales_discount.index', compact('data'));
    }
    public function create()
    {
        return view('sales_discount.create');
    }
    public function store(Request $request)
    {
        // Validasi dasar
        $baseRules = [
            'nama_diskon' => 'required|string|max:255',
            'jenis_diskon' => 'required|in:normal,early_payment,berlapis',
            'deskripsi' => 'nullable|string',
            'aktif' => 'nullable|in:on', // karena checkbox kirim "on" saat dicentang
        ];

        // Validasi detail diskon (hanya untuk early_payment & berlapis)
        $detailRules = [
            'details' => 'required|array|min:1',
            'details.*.tipe_nilai' => 'required|in:persen,nominal',
            'details.*.nilai_diskon' => 'required|numeric|min:0',
            'details.*.hari_ke' => 'nullable|integer|min:0',
            'details.*.urutan' => 'nullable|integer|min:0',
        ];

        // Gabungkan validasi sesuai jenis diskon
        $rules = array_merge($baseRules, in_array($request->jenis_diskon, ['early_payment', 'berlapis']) ? $detailRules : []);

        $request->validate($rules);

        DB::beginTransaction();
        try {
            // Simpan data utama diskon
            $diskon = SalesDiscount::create([
                'nama_diskon' => $request->nama_diskon,
                'jenis_diskon' => $request->jenis_diskon,
                'deskripsi' => $request->deskripsi,
                'aktif' => $request->has('aktif'), // hasilnya true atau false
            ]);

            // Siapkan detail diskon
            $details = [];

            if (in_array($request->jenis_diskon, ['early_payment', 'berlapis'])) {
                // Multiple detail
                foreach ($request->details as $detail) {
                    $details[] = [
                        'sales_discount_id' => $diskon->id,
                        'hari_ke' => $detail['hari_ke'] ?? null,
                        'tipe_nilai' => $detail['tipe_nilai'],
                        'nilai_diskon' => $detail['nilai_diskon'],
                        'urutan' => $detail['urutan'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            } else {
                // Diskon normal hanya satu baris
                $details[] = [
                    'sales_discount_id' => $diskon->id,
                    'hari_ke' => null,
                    'tipe_nilai' => $request->details[0]['tipe_nilai'],
                    'nilai_diskon' => $request->details[0]['nilai_diskon'],
                    'urutan' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Simpan ke detail
            SalesDiscountDetail::insert($details);

            DB::commit();
            return redirect()->route('sales_discount.index')->with('success', 'Diskon berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan data diskon: ' . $e->getMessage()]);
        }
    }
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Ambil invoice beserta detail
            $invoice = SalesDiscount::with('details')->findOrFail($id);

            // Hapus semua detail terlebih dahulu
            $invoice->details()->delete();

            // Hapus invoice utamanya
            $invoice->delete();

            DB::commit();

            return redirect()->route('sales_discount.index')->with('success', 'Sales Discount berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
    public function show($id)
    {
        // Ambil sales order beserta relasi terkait
        $salesDiscount = SalesDiscount::with([
            'details'
        ])->findOrFail($id);

        return view('sales_discount.show', compact('salesDiscount'));
    }
    public function edit($id)
    {
        $salesDiscount = SalesDiscount::with('details')->findOrFail($id);


        return view('sales_discount.edit', compact('salesDiscount'));
    }
    public function update(Request $request, $id)
    {
        // Validasi dasar
        $baseRules = [
            'nama_diskon'   => 'required|string|max:255',
            'jenis_diskon'  => 'required|in:normal,early_payment,berlapis',
            'deskripsi'     => 'nullable|string',
            'aktif'         => 'nullable|in:on',
        ];

        $detailRules = [
            'details' => 'required|array|min:1',
            'details.*.tipe_nilai'   => 'required|in:persen,nominal',
            'details.*.nilai_diskon' => 'required|numeric|min:0',
            'details.*.hari_ke'      => 'nullable|integer|min:0',
            'details.*.urutan'       => 'nullable|integer|min:0',
        ];

        $rules = array_merge(
            $baseRules,
            in_array($request->jenis_diskon, ['early_payment', 'berlapis']) ? $detailRules : []
        );

        $request->validate($rules);

        DB::beginTransaction();
        try {
            // Cari diskon lama
            $diskon = SalesDiscount::findOrFail($id);

            // Update data utama
            $diskon->update([
                'nama_diskon'   => $request->nama_diskon,
                'jenis_diskon'  => $request->jenis_diskon,
                'deskripsi'     => $request->deskripsi,
                'aktif'         => $request->has('aktif'),
            ]);

            // Hapus detail lama
            $diskon->details()->delete();

            // Siapkan detail baru
            $details = [];

            if (in_array($request->jenis_diskon, ['early_payment', 'berlapis'])) {
                foreach ($request->details as $detail) {
                    $details[] = [
                        'sales_discount_id' => $diskon->id,
                        'hari_ke'           => $detail['hari_ke'] ?? null,
                        'tipe_nilai'        => $detail['tipe_nilai'],
                        'nilai_diskon'      => $detail['nilai_diskon'],
                        'urutan'            => $detail['urutan'] ?? null,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
            } else {
                $details[] = [
                    'sales_discount_id' => $diskon->id,
                    'hari_ke'           => null,
                    'tipe_nilai'        => $request->details[0]['tipe_nilai'],
                    'nilai_diskon'      => $request->details[0]['nilai_diskon'],
                    'urutan'            => null,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }

            SalesDiscountDetail::insert($details);

            DB::commit();
            return redirect()->route('sales_discount.index')->with('success', 'Diskon berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal mengupdate data diskon: ' . $e->getMessage()]);
        }
    }
}

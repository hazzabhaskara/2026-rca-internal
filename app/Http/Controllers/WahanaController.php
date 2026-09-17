<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use App\UnitKerja;
use App\Wahana;
use App\WahanaItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WahanaController extends Controller
{
    //
    public function index()
    {
        // Ambil query dasar
        $query = Wahana::select('wahanas.*')
            ->join('unit_kerjas', 'unit_kerjas.id', '=', 'wahanas.unit_kerja_id')
            ->orderBy('unit_kerjas.urutan', 'asc')
            ->orderBy('wahanas.urutan', 'asc');


        // Filter Unit
        if ($unit = request('filter_tipe')) {
            $query->whereHas('UnitKerja', function ($q) use ($unit) {
                $q->where('nama_unit', $unit);
            });
        }

        // Filter Status
        if ($status = request('filter_status')) {
            $query->where('status', $status);
            // pastikan di tabel Wahana ada kolom 'status'
            // misalnya nilainya 'aktif' / 'nonaktif' atau 1/0
        }

        // Filter Search
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_wahana', 'like', "%$search%")
                    ->orWhere('kode_wahana', 'like', "%$search%");
            });
        }

        // Eksekusi query
        $data = $query->get();

        // Ambil list unit unik
        $unitkerja = UnitKerja::select('nama_unit')->distinct()->pluck('nama_unit');

        return view('wahana.index', compact('data', 'unitkerja'));
    }

    public function create()
    {
        $unit_kerja = UnitKerja::all();
        $account = chartOfAccount::with('departemenAkun.departemen')->get();
        return view('wahana.create', compact('unit_kerja', 'account'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            // --- HEADER ---
            'kode_wahana'   => 'required|string|max:100',
            'nama_wahana'   => 'required|string|max:100',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'kategori'      => 'nullable|string|max:100',
            'kapasitas'     => 'nullable|numeric|min:0',
            'urutan'        => 'nullable|numeric|min:0',
            'status'        => 'required|string|in:Aktif,Non Aktif',

            // --- DETAIL ---
            'kode_item'     => 'required|array|min:1',
            'kode_item.*'   => 'nullable|string|max:100',
            'nama_item'     => 'required|array|min:1',
            'nama_item.*'   => 'nullable|string|max:100',
            'harga'         => 'required|array|min:1',
            'harga.*'       => 'nullable|string',
            'status_item'   => 'nullable|array',
            'status_item.*' => 'nullable|in:1,0',
            'dasar_perhitungan_titipan'   => 'nullable|array',
            'dasar_perhitungan_titipan.*' => 'nullable|in:1,0',
            'harga_perhitungan_titipan'         => 'required|array|min:1',
            'harga_perhitungan_titipan.*'       => 'nullable|string',
            'account_id'    => 'nullable|array',
            'account_id.*'  => 'nullable|string',
        ], [
            'nama_wahana.required' => 'Nama wahana wajib diisi.',
            'unit_kerja_id.exists' => 'Unit kerja tidak valid.',
            'kode_item.required'   => 'Minimal isi satu baris item.',
            'status.in'            => 'Status harus Aktif atau Non Aktif.',
        ]);

        DB::transaction(function () use ($validated) {

            $validated['harga'] = array_map(function ($v) {
                return (float) str_replace(',', '', $v);
            }, $validated['harga']);

            $validated['harga_perhitungan_titipan'] = array_map(function ($v) {
                return (float) str_replace(',', '', $v);
            }, $validated['harga_perhitungan_titipan']);
            // 1️⃣ Ambil header lama

            // 1️⃣ Simpan header (tabel wahana)
            $wahana = Wahana::create([
                'kode_wahana'   => $validated['kode_wahana'] ?? 0,
                'nama_wahana'   => $validated['nama_wahana'],
                'unit_kerja_id' => $validated['unit_kerja_id'],
                'kategori'      => $validated['kategori'] ?? null,
                'kapasitas'     => $validated['kapasitas'] ?? null,
                'urutan'        => $validated['urutan'] ?? null,
                'status'        => $validated['status'],
            ]);

            // 2️⃣ Susun detail (tabel wahana_items)
            $rows = [];
            $total_baris = count($validated['nama_item']);

            for ($i = 0; $i < $total_baris; $i++) {
                $nama = $validated['nama_item'][$i] ?? null;
                $kode = $validated['kode_item'][$i] ?? null;


                $akunPart = $validated['account_id'][$i] ?? null;
                $akunId = null;
                $departemenId = null;

                if ($akunPart && strpos($akunPart, '|') !== false) {
                    [$akunId, $departemenId] = explode('|', $akunPart);
                    $departemenId = $departemenId == 0 ? null : $departemenId;
                } else {
                    $akunId = $akunPart ?: null;
                }
                if (empty($nama) && empty($kode)) {
                    continue;
                }

                $rows[] = [
                    'wahana_id'  => $wahana->id,
                    'kode_item'  => $kode,
                    'nama_item'  => $nama,
                    'harga'      => $validated['harga'][$i] ?? 0,
                    'status'     => isset($validated['status_item'][$i]) ? (int) $validated['status_item'][$i] : 1,
                    'dasar_perhitungan_titipan'     => isset($validated['dasar_perhitungan_titipan'][$i]) ? (int) $validated['dasar_perhitungan_titipan'][$i] : 1,
                    'harga_perhitungan_titipan'      => $validated['harga_perhitungan_titipan'][$i] ?? 0,
                    'account_id' => $akunId,
                    'departemen_id' => $departemenId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($rows)) {
                return back()->withInput()->withErrors([
                    'nama_item' => 'Minimal isi satu item dengan kode atau nama.',
                ])->throwResponse();
            }

            WahanaItem::insert($rows);
        });

        return redirect()->route('wahana.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    public function show($id)
    {
        $wahana = Wahana::findOrFail($id);

        return view('wahana.show', compact('wahana'));
    }
    public function edit(string $id): View
    {
        //get post by ID
        $wahana = Wahana::with(['wahanaItem', 'wahanaItem.departemen'])->findOrFail($id);
        $unit = UnitKerja::all();
        $account = \App\ChartOfAccount::with('departemenAkun.departemen')->get();

        return view('wahana.edit', compact('wahana', 'unit', 'account'));
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            // --- HEADER ---
            'nama_wahana'   => 'required|string|max:100',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'kategori'      => 'nullable|string|max:100',
            'kapasitas'     => 'nullable|numeric|min:0',
            'urutan'        => 'nullable|numeric|min:0',
            'status'        => 'required|string|in:Aktif,Non Aktif',

            // --- DETAIL ---
            'kode_item'     => 'required|array|min:1',
            'kode_item.*'   => 'nullable|string|max:100',
            'nama_item'     => 'required|array|min:1',
            'nama_item.*'   => 'nullable|string|max:100',
            'harga'         => 'required|array|min:1',
            'harga.*'       => 'nullable|string', // masih string karena berisi "70,000"
            'harga_perhitungan_titipan'         => 'required|array|min:1',
            'harga_perhitungan_titipan.*'       => 'nullable|string', // masih string karena berisi "70,000"
            'status_item'   => 'nullable|array',
            'status_item.*' => 'nullable|in:1,0',
            'dasar_perhitungan_titipan'   => 'nullable|array',
            'dasar_perhitungan_titipan.*' => 'nullable|in:1,0',
            'account_id'    => 'nullable|array',
            'account_id.*'  => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $id) {

            $validated['harga'] = array_map(function ($v) {
                return (float) str_replace(',', '', $v);
            }, $validated['harga']);
            $validated['harga_perhitungan_titipan'] = array_map(function ($v) {
                return (float) str_replace(',', '', $v);
            }, $validated['harga_perhitungan_titipan']);

            $wahana = Wahana::findOrFail($id);

            $wahana->update([
                'nama_wahana'   => $validated['nama_wahana'],
                'unit_kerja_id' => $validated['unit_kerja_id'],
                'kategori'      => $validated['kategori'] ?? null,
                'kapasitas'     => $validated['kapasitas'] ?? null,
                'urutan'        => $validated['urutan'] ?? null,
                'status'        => $validated['status'],
            ]);

            $rows = [];
            $total = count($validated['nama_item']);

            for ($i = 0; $i < $total; $i++) {
                $nama = $validated['nama_item'][$i] ?? null;
                $kode = $validated['kode_item'][$i] ?? null;


                $akunPart = $validated['account_id'][$i] ?? null;
                $akunId = null;
                $departemenId = null;

                if ($akunPart && strpos($akunPart, '|') !== false) {
                    [$akunId, $departemenId] = explode('|', $akunPart);
                    $departemenId = $departemenId == 0 ? null : $departemenId;
                } else {
                    $akunId = $akunPart ?: null;
                }

                if (empty($nama) && empty($kode)) continue;

                $rows[] = [
                    'wahana_id'  => $wahana->id,
                    'kode_item'  => $kode,
                    'nama_item'  => $nama,
                    'harga'      => $validated['harga'][$i] ?? 0,
                    'status'     => isset($validated['status_item'][$i]) ? (int) $validated['status_item'][$i] : 1,
                    'dasar_perhitungan_titipan'     => isset($validated['dasar_perhitungan_titipan'][$i]) ? (int) $validated['dasar_perhitungan_titipan'][$i] : 1,
                    'harga_perhitungan_titipan'      => $validated['harga_perhitungan_titipan'][$i] ?? 0,
                    'account_id'    => $akunId,
                    'departemen_id' => $departemenId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($rows)) {
                return back()->withInput()->withErrors([
                    'nama_item' => 'Minimal isi satu item dengan kode atau nama.',
                ])->throwResponse();
            }

            WahanaItem::where('wahana_id', $wahana->id)->delete();

            WahanaItem::insert($rows);
        });

        return redirect()->route('wahana.index')
            ->with('success', 'Data wahana berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $wahana = Wahana::with(['wahanaItem'])->findOrFail($id);

        $wahana->delete();

        return redirect()->route('wahana.index')->with('success', ' Data berhasil dihapus.');
    }
    public function byUnit($unitId)
    {
        // asumsi kolom foreign key: wahanas.unit_kerja_id
        $wahanas = Wahana::where('unit_kerja_id', $unitId)
            ->orderBy('nama_wahana')
            ->get(['id', 'nama_wahana']);

        return response()->json($wahanas);
    }
}

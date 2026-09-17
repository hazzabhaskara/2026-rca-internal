<?php

namespace App\Http\Controllers;

use App\BonusKaryawan;
use App\JenisHari;
use App\ShiftKaryawanWahana;
use App\Targetunit;
use App\TargetWahana;
use App\TransaksiWahana;
use App\UnitKerja;
use App\Wahana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TransaksiWahanaController extends Controller
{
    //
    public function index()
    {
        $query = TransaksiWahana::with(['unitKerja', 'wahana'])
            ->join('unit_kerjas', 'transaksi_wahanas.unit_kerja_id', '=', 'unit_kerjas.id')
            ->orderBy('unit_kerjas.nama_unit')
            ->select('transaksi_wahanas.*');

        if ($unit = request('filter_tipe')) {
            $query->where('unit_kerjas.nama_unit', $unit);
        }

        // Filter Level Karyawan
        if ($wahana = request('filter_wahana')) {
            $query->whereHas('wahana', function ($q) use ($wahana) {
                $q->where('nama_wahana', $wahana);
            });
        }
        $searchable = ['tanggal', 'realisasi', 'jumlah_pengunjung'];

        if ($search = request('search')) {
            $query->where(function ($q) use ($search, $searchable) {
                foreach ($searchable as $col) {
                    $q->orWhere($col, 'like', "%{$search}%");
                }

                // tambahkan juga relasi
                $q->orWhereHas('unitKerja', function ($q2) use ($search) {
                    $q2->where('nama_unit', 'like', "%{$search}%");
                });
                // tambahkan juga relasi
                $q->orWhereHas('wahana', function ($q4) use ($search) {
                    $q4->where('nama_wahana', 'like', "%{$search}%");
                });
            });
        }


        // Eksekusi query sekali di akhir
        $data = $query->get();
        // Atau kalau mau paginasi:
        // $data = $query->paginate(20)->appends(request()->query());

        // Sumber data untuk dropdown
        $unitkerja = UnitKerja::select('nama_unit')->distinct()->orderBy('nama_unit')->pluck('nama_unit');
        $wahana = Wahana::select('nama_wahana')->distinct()->orderBy('nama_wahana')->pluck('nama_wahana');
        return view('transaksi_wahana.index', compact('data', 'unitkerja', 'wahana'));
    }
    public function create()
    {
        $unit = UnitKerja::all();
        $jenis_hari = JenisHari::all();
        return view('transaksi_wahana.create', compact('unit', 'jenis_hari'));
    }
    public function store(Request $request)
    {
        // Bersihkan format ribuan
        $request->merge([
            'realisasi' => str_replace('.', '', $request->realisasi),
        ]);

        // Validasi
        $request->validate([
            'wahana_id'      => ['required', 'exists:wahanas,id'],
            'unit_kerja_id'  => ['required', 'exists:unit_kerjas,id'],
            'jenis_hari_id'  => ['required', 'exists:jenis_haris,id'],
            'tanggal' => [
                'required',
                'date',
                Rule::unique('transaksi_wahanas')
                    ->where(
                        fn($q) => $q
                            ->where('wahana_id', $request->wahana_id)
                            ->where('unit_kerja_id', $request->unit_kerja_id)
                            ->where('tanggal', $request->tanggal) // ikut cek tanggal
                    ),
            ],
            'realisasi'        => ['required', 'numeric'],
            'jumlah_pengunjung' => ['nullable', 'integer'],
        ]);

        // Simpan transaksi
        $transaksi = TransaksiWahana::create($request->only([
            'wahana_id',
            'unit_kerja_id',
            'jenis_hari_id',
            'tanggal',
            'realisasi',
            'jumlah_pengunjung'
        ]));

        Log::info('Transaksi baru dibuat', $transaksi->toArray());

        // Cari target sesuai wahana + jenis hari + periode
        $target = TargetWahana::where('wahana_id', $request->wahana_id)
            ->where('jenis_hari_id', $request->jenis_hari_id)
            ->where('bulan', date('m', strtotime($request->tanggal)))
            ->where('tahun', date('Y', strtotime($request->tanggal)))
            ->first();

        Log::info('Target ditemukan', ['target' => $target]);

        // Cari shift karyawan
        $shifts = ShiftKaryawanWahana::with('karyawan') // penting: eager load relasi
            ->where('wahana_id', $request->wahana_id)
            ->where('unit_kerja_id', $request->unit_kerja_id)
            ->where('tanggal', $request->tanggal)
            ->get();

        Log::info('Shift ditemukan', ['shifts' => $shifts->toArray()]);

        foreach ($shifts as $shift) {
            $bonus = 0;
            $transport = 0;

            if ($target && $request->realisasi >= $target->target_harian) {
                Log::info("Hitung bonus untuk shift {$shift->id}", [
                    'employee_id' => $shift->employee_id,
                    'level_karyawan_id' => $shift->karyawan->level_kepegawaian_id ?? null,
                    'persentase_jam' => $shift->persentase_jam,
                ]);

                // Ambil komponen "Bonus"
                $targetUnitBonus = Targetunit::where('unit_kerja_id', $shift->unit_kerja_id)
                    ->where('level_karyawan_id', $shift->karyawan->level_kepegawaian_id ?? null)
                    ->whereHas('komponen', function ($q) {
                        $q->where('nama_komponen', 'like', '%Bonus%');
                    })
                    ->first();

                Log::info('Target unit bonus', ['data' => $targetUnitBonus]);

                if ($targetUnitBonus) {
                    $bonus = ($targetUnitBonus->besaran_nominal ?? 0) * ($shift->persentase_jam ?? 1);
                }

                // Ambil komponen "Transportasi"
                $targetUnitTransport = Targetunit::where('unit_kerja_id', $shift->unit_kerja_id)
                    ->where('level_karyawan_id', $shift->karyawan->level_karyawan_id ?? null)
                    ->whereHas('komponen', function ($q) {
                        $q->where('nama_komponen', 'like', '%Transport%');
                    })
                    ->first();

                Log::info('Target unit transport', ['data' => $targetUnitTransport]);

                if ($targetUnitTransport) {
                    $transport = $targetUnitTransport->besaran_nominal ?? 0;
                }
            }

            // Simpan/Update bonus karyawan
            $bonusData = BonusKaryawan::updateOrCreate(
                [
                    'employee_id' => $shift->employee_id,
                    'shift_id'    => $shift->id,
                ],
                [
                    'transaksi_wahana_id' => $transaksi->id,
                    'tanggal'             => $request->tanggal,
                    'jenis_hari_id'       => $request->jenis_hari_id,
                    'bonus'               => $bonus,
                    'transportasi'        => $transport,
                    'total'               => $bonus + $transport,
                    'status'              => 'Calculated',
                ]
            );

            Log::info('Bonus karyawan tersimpan', $bonusData->toArray());
        }

        return redirect()->route('transaksi_wahana.index')
            ->with('success', 'Data Transaksi berhasil diproses');
    }

    public function show($id)
    {
        $transaksi_wahana = TransaksiWahana::findOrFail($id);

        return view('transaksi_wahana.show', compact('transaksi_wahana'));
    }

    public function edit($id)
    {
        //get post by ID
        $transaksi_wahana = \App\TransaksiWahana::findOrFail($id);
        $unit = UnitKerja::all();

        return view('transaksi_wahana.edit', compact('transaksi_wahana', 'unit'));
    }
    public function update(Request $request, TransaksiWahana $transaksi_wahana)
    {
        $request->merge([
            'realisasi' => str_replace('.', '', $request->realisasi),
        ]);

        $request->validate([
            'wahana_id' => ['required', 'exists:wahanas,id'],
            'unit_kerja_id' => ['required', 'exists:unit_kerjas,id'],
            'tanggal' => [
                'required',
                'date',
                Rule::unique('transaksi_wahanas')
                    ->ignore($transaksi_wahana->id) // abaikan id yang sedang diupdate
                    ->where(function ($q) use ($request) {
                        return $q->where('wahana_id', $request->wahana_id)
                            ->where('unit_kerja_id', $request->unit_kerja_id);
                    }),
            ],
            'realisasi' => ['required', 'numeric'],
            'jumlah_pengunjung' => ['nullable', 'integer'],
        ]);

        $transaksi_wahana->update($request->only([
            'wahana_id',
            'unit_kerja_id',
            'tanggal',
            'realisasi',
            'jumlah_pengunjung'
        ]));

        return redirect()->route('transaksi_wahana.index')
            ->with('success', 'Data Transaksi berhasil diperbarui');
    }
    public function destroy($id)
    {
        $transaksi_wahana = TransaksiWahana::findOrFail($id);

        $transaksi_wahana->delete();

        return redirect()->route('transaksi_wahana.index')->with('success', ' Data berhasil dihapus.');
    }
}

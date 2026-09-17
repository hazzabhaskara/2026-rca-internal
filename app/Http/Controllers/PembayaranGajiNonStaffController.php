<?php

namespace App\Http\Controllers;

use App\Employee;
use App\LevelKaryawan;
use App\PembayaranGaji;
use App\PembayaranGajiDetail;
use App\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranGajiNonStaffController extends Controller
{
    //
    public function index()
    {
        $query = PembayaranGaji::whereHas('employee.levelkaryawan', function ($query) {
            $query->where('nama_level', '<>', 'STAFF'); // selain STAFF
        });
        // Filter Level Karyawan
        if ($level_karyawan = request('filter_tipe')) {
            $query->whereHas('employee.levelKaryawan', function ($q) use ($level_karyawan) {
                $q->where('nama_level', $level_karyawan);
            });
        }

        // Filter Unit
        if ($unit = request('filter_unit')) {
            $query->whereHas('employee.unitKerja', function ($q) use ($unit) {
                $q->where('nama_unit', $unit);
            });
        }

        // Kolom searchable ada di tabel employee
        $searchable = ['kode_karyawan', 'nama_karyawan', 'nik', 'tempat_lahir'];

        if ($search = request('search')) {
            $query->where(function ($q) use ($search, $searchable) {
                // cari di tabel employees
                $q->orWhereHas('employee', function ($qEmp) use ($search, $searchable) {
                    $qEmp->where(function ($qq) use ($search, $searchable) {
                        foreach ($searchable as $col) {
                            $qq->orWhere($col, 'like', "%{$search}%");
                        }
                    });
                });

                // cari di level karyawan
                $q->orWhereHas('employee.levelKaryawan', function ($q4) use ($search) {
                    $q4->where('nama_level', 'like', "%{$search}%");
                });

                // cari di unit kerja
                $q->orWhereHas('employee.unitKerja', function ($q1) use ($search) {
                    $q1->where('nama_unit', 'like', "%{$search}%");
                });
            });
        }


        $data = $query->paginate(10);
        $unit = UnitKerja::pluck('nama_unit')->filter()->unique()->values();
        $level_karyawan = LevelKaryawan::pluck('nama_level')->filter()->unique()->values();

        return view('pembayaran_gaji_nonstaff.index', compact('data', 'unit', 'level_karyawan'));
    }

    public function create()
    {
        $karyawan = Employee::whereHas('levelKaryawan', function ($query) {
            $query->where('nama_level', '!=', 'STAFF');
        })->get();

        return view('pembayaran_gaji_nonstaff.create', compact('karyawan'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'kode_karyawan' => 'required|exists:employees,id',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date|after_or_equal:periode_awal',
            'tanggal_pembayaran' => 'required|date',
            'komponen' => 'required|array',
            'komponen.*.kode_komponen' => 'required|exists:komponen_penghasilans,id',
            'komponen.*.nilai' => 'nullable|numeric',
            'komponen.*.jumlah_hari' => 'nullable|numeric',
            'komponen.*.potongan' => 'nullable|numeric',
            'komponen.*.total' => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            // Simpan pembayaran_gajis
            $pembayaran = PembayaranGaji::create([
                'kode_karyawan' => $request->kode_karyawan,
                'periode_awal' => $request->periode_awal,
                'periode_akhir' => $request->periode_akhir,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
            ]);

            // Simpan detailnya
            foreach ($request->komponen as $index => $komponen) {
                PembayaranGajiDetail::create([
                    'kode_pembayaran_id' => $pembayaran->id,
                    'kode_komponen' => $komponen['kode_komponen'],
                    'nilai' => $komponen['nilai'] ?? 0,
                    'jumlah_hari' => $komponen['jumlah_hari'] ?? 0,
                    'potongan' => $komponen['potongan'] ?? 0,
                    'urut' => $index + 1,
                ]);
            }

            DB::commit();

            return redirect()->route('pembayaran_gaji_nonstaff.index')
                ->with('success', 'Pembayaran gaji berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage())->withInput();
        }
    }
    public function show($id)
    {
        $pembayaran = PembayaranGaji::with('employee')->findOrFail($id);
        $details = PembayaranGajiDetail::where('kode_pembayaran_id', $id)->with('komponen')->get();

        return view('pembayaran_gaji_nonstaff.show', compact('pembayaran', 'details'));
    }
    public function edit($id)
    {
        // Ambil data pembayaran gaji berdasarkan ID
        $pembayaran = PembayaranGaji::findOrFail($id);

        // Ambil semua karyawan untuk dropdown
        $karyawan = Employee::whereHas('levelKaryawan', function ($query) {
            $query->where('nama_level', '!=', 'STAFF');
        })->get();

        // Ambil detail komponen penghasilan yang terkait
        $details = PembayaranGajiDetail::where('kode_pembayaran_id', $id)
            ->with('komponen') // pastikan ada relasi komponen di model
            ->get();

        return view(
            'pembayaran_gaji_nonstaff.edit',
            compact('pembayaran', 'karyawan', 'details')
        );
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date',
            'tanggal_pembayaran' => 'required|date',
            'komponen.*.nilai' => 'nullable|numeric',
            'komponen.*.jumlah_hari' => 'nullable|numeric',
            'komponen.*.potongan' => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            // Update data pembayaran utama
            $pembayaran = PembayaranGaji::findOrFail($id);
            $pembayaran->update([
                'periode_awal' => $request->periode_awal,
                'periode_akhir' => $request->periode_akhir,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
            ]);

            // Update detail komponen satu per satu
            if ($request->has('komponen')) {
                foreach ($request->komponen as $komponen) {
                    if (isset($komponen['id'])) {
                        $detail = PembayaranGajiDetail::find($komponen['id']);

                        if ($detail) {
                            $detail->update([
                                'kode_komponen' => $komponen['kode_komponen'],
                                'nilai' => $komponen['nilai'] ?? 0,
                                'jumlah_hari' => $komponen['jumlah_hari'] ?? 0,
                                'potongan' => $komponen['potongan'] ?? 0,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('pembayaran_gaji_nonstaff.index')->with('success', 'Data pembayaran gaji berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan saat memperbarui: ' . $e->getMessage());
        }
    }
    public function destroy($id)
    {
        $pembayaran = \App\pembayaranGaji::findOrFail($id);
        $pembayaran->delete();

        return redirect()->route('pembayaran_gaji_nonstaff.index')->with('success', 'Pembayaran gaji berhasil dihapus.');
    }
}

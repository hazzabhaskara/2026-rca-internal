<?php

namespace App\Http\Controllers;

use App\BonusKaryawan;
use App\CrewShiftKaryawan;
use App\Employee;
use App\EmployeeOffDay;
use App\Exports\EmployeeExport;
use App\JenisHari;
use App\ShiftKaryawanWahana;
use App\UnitKerja;
use App\Wahana;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftKaryawanWahanaController extends Controller
{
    //
    public function index()
    {
        $query = ShiftKaryawanWahana::with([
            'karyawan',
            'unitKerja',
            'wahana',
            'jenisHari'
        ])
            ->join('unit_kerjas', 'shift_karyawan_wahanas.unit_kerja_id', '=', 'unit_kerjas.id')
            ->orderBy('unit_kerjas.nama_unit')
            ->select('shift_karyawan_wahanas.*');

        if ($unit = request('filter_tipe')) {
            $query->where('unit_kerjas.nama_unit', $unit);
        }

        // Filter Level Karyawan
        if ($wahana = request('filter_wahana')) {
            $query->whereHas('wahana', function ($q) use ($wahana) {
                $q->where('nama_wahana', $wahana);
            });
        }
        if ($jenisHari = request('filter_jenis_hari')) {
            $query->whereHas('jenisHari', function ($q) use ($jenisHari) {
                $q->where('nama', $jenisHari);
            });
        }
        // Filter Status
        if ($status = request('filter_status')) {
            $query->where('status', $status);
            // pastikan di tabel Wahana ada kolom 'status'
            // misalnya nilainya 'aktif' / 'nonaktif' atau 1/0
        }
        $searchable = ['tanggal', 'jam_mulai', 'jam_selesai'];

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
                $q->orWhereHas('jenisHari', function ($q3) use ($search) {
                    $q3->where('nama', 'like', "%{$search}%");
                });
                $q->orWhereHas('wahana', function ($q4) use ($search) {
                    $q4->where('nama_wahana', 'like', "%{$search}%");
                });
                $q->orWhereHas('karyawan', function ($q4) use ($search) {
                    $q4->where('nama_karyawan', 'like', "%{$search}%");
                });
            });
        }


        // Eksekusi query sekali di akhir
        $data = $query->get();
        // Atau kalau mau paginasi:
        // $data = $query->paginate(20)->appends(request()->query());

        // Sumber data untuk dropdown
        $unitkerja = UnitKerja::select('nama_unit')->distinct()->orderBy('nama_unit')->pluck('nama_unit');
        $jenis_hari = JenisHari::select('nama')->distinct()->orderBy('nama')->pluck('nama');
        $wahana = Wahana::select('nama_wahana')->distinct()->orderBy('nama_wahana')->pluck('nama_wahana');
        $karyawan = Employee::all();
        $unitKerja = UnitKerja::all();
        $jenisHari = JenisHari::all();
        $crew_shift = CrewShiftKaryawan::orderBy('nama')->get();
        return view('shift_karyawan.index', compact('data', 'unitkerja', 'jenis_hari', 'wahana', 'unitKerja', 'karyawan', 'jenisHari', 'crew_shift'));
    }
    public function create()
    {
        $karyawan = collect(); // kosong dulu
        $unitKerja = UnitKerja::all();
        $crew = CrewShiftKaryawan::orderBy('nama')->get();
        return view('shift_karyawan.create', compact('karyawan', 'unitKerja', 'crew'));
    }
    public function getKaryawanAvailable(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $search = $request->input('search');

        $sudahShift = ShiftKaryawanWahana::whereDate('tanggal', $tanggal)
            ->pluck('employee_id')
            ->toArray();

        $query = Employee::select('id', 'nama_panggilan')
            ->whereNotIn('id', $sudahShift);

        if ($search) {
            $query->where('nama_panggilan', 'like', "%{$search}%");
        }

        $karyawan = $query->orderBy('nama_panggilan')->limit(20)->get();

        return response()->json($karyawan);
    }

    public function getJenisHari($unit_kerja_id)
    {
        $jenisHari = JenisHari::where('unit_kerja_id', $unit_kerja_id)->get(['id', 'nama']);
        return response()->json($jenisHari);
    }

    public function store(Request $request)
    {

        // dd($request->all());
        $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'wahana_id'     => 'required|exists:wahanas,id',
            'tanggal'       => 'required|date',
            'jenis_hari_id' => 'required|exists:jenis_haris,id',
            'crew_id'       => 'required|exists:crew_shift_karyawans,id',
            'jam_mulai'     => 'required|date_format:H:i',
            'jam_selesai'   => 'required|date_format:H:i|after:jam_mulai',
            'status'        => 'required|in:Penetapan,Perubahan,Tambahan',
            'keterangan'    => 'nullable|string',
        ]);

        $query = JenisHari::where('id', $request->jenis_hari_id)
            ->where('unit_kerja_id', $request->unit_kerja_id);

        // dd($query->toSql(), $query->getBindings());

        $jenisHari = $query->first();

        $errors = [];

        if ($jenisHari && $jenisHari->jam_selesai) {
            $jamSelesaiInput = Carbon::parse($request->jam_selesai);
            $jamSelesaiJenis = Carbon::parse($jenisHari->jam_selesai);

            if ($jamSelesaiInput->greaterThan($jamSelesaiJenis)) {
                $errors['jam_selesai'] = 'Jam selesai melebihi batas jam selesai untuk jenis hari tersebut.';
            }
        }

        if ($jenisHari && $jenisHari->jam_mulai) {
            $jamMulaiInput = Carbon::parse($request->jam_mulai);
            $jamMulaiJenis = Carbon::parse($jenisHari->jam_mulai);

            if ($jamMulaiInput->lessThan($jamMulaiJenis)) {
                $errors['jam_mulai'] = 'Jam mulai lebih awal dari batas jam mulai untuk jenis hari tersebut.';
            }
        }

        if (!empty($errors)) {
            return redirect()->back()->withInput()->withErrors($errors);
        }


        // hitung durasi jam
        $jamMulai   = Carbon::parse($request->jam_mulai);
        $jamSelesai = Carbon::parse($request->jam_selesai);
        $lamaJam    = $jamMulai->diffInMinutes($jamSelesai) / 60;

        // hitung persentase jika ada default jam di jenis hari
        $defaultJam = null;
        $persentase = null;

        if ($jenisHari && $jenisHari->jam_mulai && $jenisHari->jam_selesai) {
            $defaultMulai   = Carbon::parse($jenisHari->jam_mulai);
            $defaultSelesai = Carbon::parse($jenisHari->jam_selesai);
            $defaultJam     = $defaultMulai->diffInMinutes($defaultSelesai) / 60;

            if ($defaultJam > 0) {
                $persentase = $lamaJam / $defaultJam;
            }
        }

        $batasJamNormal = 8;
        $lemburJam = max(0, $lamaJam - $batasJamNormal);

        try {
            DB::beginTransaction();

            $shift = ShiftKaryawanWahana::create([
                'employee_id'    => $request->employee_id,
                'unit_kerja_id'  => $request->unit_kerja_id,
                'wahana_id'      => $request->wahana_id,
                'tanggal'        => $request->tanggal,
                'jenis_hari_id'  => $request->jenis_hari_id,
                'jam_mulai'      => $request->jam_mulai,
                'jam_selesai'    => $request->jam_selesai,
                'lama_jam'       => $lamaJam,
                'lembur_jam' => $lemburJam,
                'persentase_jam' => $persentase,
                'status'         => $request->status,
                'keterangan'     => $request->keterangan,
                'crew_id'        => $request->crew_id,
            ]);

            BonusKaryawan::create([
                'employee_id'   => $shift->employee_id,
                'shift_id'      => $shift->id,
                'tanggal'       => $shift->tanggal,
                'jenis_hari_id' => $shift->jenis_hari_id,
                'bonus'         => 0,
                'transportasi'  => 0,
                'total'         => 0,
                'status'        => 'Pending',
            ]);

            DB::commit();

            return redirect()->route('shift_karyawan.index')
                ->with('success', 'Shift karyawan + Bonus Pending berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan shift: ' . $e->getMessage());
        }
    }
    public function show($id)
    {
        $data = ShiftKaryawanWahana::findOrFail($id);
        return view('shift_karyawan.show', compact('data'));
    }
    public function edit($id)
    {
        $shift_karyawan = ShiftKaryawanWahana::findOrFail($id);
        $karyawan = Employee::all();
        $unitKerja = UnitKerja::all();
        $jenisHari = JenisHari::all();
        $crew = CrewShiftKaryawan::all();

        return view('shift_karyawan.edit', compact('shift_karyawan', 'karyawan', 'unitKerja', 'jenisHari', 'crew'));
    }
    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'wahana_id'     => 'required|exists:wahanas,id',
            'tanggal'       => 'required|date',
            'jenis_hari_id' => 'required|exists:jenis_haris,id',
            'jam_mulai'     => 'required|date_format:H:i',
            'jam_selesai'   => 'required|date_format:H:i|after:jam_mulai',
            'status'        => 'required|in:Penetapan,Perubahan,Tambahan',
            'keterangan'    => 'nullable|string',
            'posisi'        => 'required|in:petugas_1,petugas_2,petugas_3,petugas_4,pengganti'

        ]);

        // Hitung lama jam
        $jamMulai   = Carbon::parse($request->jam_mulai);
        $jamSelesai = Carbon::parse($request->jam_selesai);
        $lamaJam    = $jamMulai->diffInMinutes($jamSelesai) / 60;

        // Ambil default jam kerja dari jenis_haris
        $jenisHari   = JenisHari::find($request->jenis_hari_id);
        $defaultJam  = null;
        $persentase  = null;

        if ($jenisHari && $jenisHari->jam_mulai && $jenisHari->jam_selesai) {
            $defaultMulai   = Carbon::parse($jenisHari->jam_mulai);
            $defaultSelesai = Carbon::parse($jenisHari->jam_selesai);
            $defaultJam     = $defaultMulai->diffInMinutes($defaultSelesai) / 60;

            if ($defaultJam > 0) {
                $persentase = $lamaJam / $defaultJam;
            }
        }

        try {
            // Update shift
            $shift = ShiftKaryawanWahana::findOrFail($id);

            $shift->update([
                'employee_id'    => $request->employee_id,
                'unit_kerja_id'  => $request->unit_kerja_id,
                'wahana_id'      => $request->wahana_id,
                'tanggal'        => $request->tanggal,
                'jenis_hari_id'  => $request->jenis_hari_id,
                'jam_mulai'      => $request->jam_mulai,
                'jam_selesai'    => $request->jam_selesai,
                'lama_jam'       => $lamaJam,
                'persentase_jam' => $persentase,
                'status'         => $request->status,
                'keterangan'     => $request->keterangan,
                'posisi'     => $request->posisi
            ]);

            // Update bonus yang terkait shift ini (jika ada)
            $bonus = BonusKaryawan::where('shift_id', $shift->id)->first();
            if ($bonus) {
                $bonus->update([
                    'tanggal'       => $request->tanggal,
                    'jenis_hari_id' => $request->jenis_hari_id,
                    'status'        => 'Pending', // reset agar dihitung ulang
                    'keterangan'    => 'Bonus reset karena shift diperbarui'
                ]);
            }

            return redirect()->route('shift_karyawan.index')
                ->with('success', 'Shift karyawan berhasil diperbarui, bonus diset ke Pending.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal update shift: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $shift_karyawan = ShiftKaryawanWahana::findOrFail($id);

        $shift_karyawan->delete();

        return redirect()->route('shift_karyawan.index')->with('success', ' Data berhasil dihapus.');
    }
    public function listByUnitDate(Request $request)
    {
        $unitId  = $request->get('unit_id');
        $tanggal = $request->get('tanggal');

        if (!$unitId || !$tanggal) {
            return response()->json(['assignments' => []]); // aman-aman saja kembalikan kosong
        }

        $rows = ShiftKaryawanWahana::query()
            ->select([
                'shift_karyawan_wahanas.wahana_id',
                'shift_karyawan_wahanas.crew_id',
                'employees.id as employee_id',
                'employees.nama_karyawan as employee_name',
            ])
            ->join('employees', 'employees.id', '=', 'shift_karyawan_wahanas.employee_id')
            ->where('shift_karyawan_wahanas.unit_kerja_id', $unitId)
            ->whereDate('shift_karyawan_wahanas.tanggal', $tanggal)
            ->get();

        $assignments = [];
        foreach ($rows as $r) {
            $assignments[$r->wahana_id][$r->crew_id] = [
                'employee_id' => $r->employee_id,
                'name'        => $r->employee_name,
            ];
        }

        return response()->json(['assignments' => $assignments]);
    }
    public function exportShiftKaryawanPDF(Request $request)
    {
        $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date|after_or_equal:tgl_awal',
        ]);

        $tgl_awal = date('Y-m-d', strtotime($request->tgl_awal));
        $tgl_akhir = date('Y-m-d', strtotime($request->tgl_akhir));

        // Ambil data shift per tanggal
        $dataPerTanggal = ShiftKaryawanWahana::with(['wahana', 'karyawan', 'unitKerja'])
            ->whereBetween('tanggal', [$tgl_awal, $tgl_akhir])
            ->get()
            ->groupBy('tanggal');

        // Ambil data OFF per tanggal
        $offPerTanggal = EmployeeOffDay::with('employee')
            ->whereBetween('tanggal', [$tgl_awal, $tgl_akhir])
            ->get()
            ->groupBy('tanggal');

        // data crew
        $crewShift = CrewShiftKaryawan::orderBy('nama')->get();

        if ($dataPerTanggal->isEmpty()) {
            return back()->with('error', 'Tidak ada data shift pada rentang tanggal tersebut.');
        }

        $pdf = Pdf::loadView('shift_karyawan.shift_karyawan_pdf', [
            'dataPerTanggal' => $dataPerTanggal,
            'offPerTanggal' => $offPerTanggal,
            'crewShift' => $crewShift,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('shift_karyawan_tabel.pdf');
    }
}

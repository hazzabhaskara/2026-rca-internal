<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Jabatan;
use App\LevelKaryawan;
use App\Ptkp;
use App\UnitKerja;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SalesPersonController extends Controller
{
    //
    public function index()
    {
        $query = Employee::with(['jabatan', 'ptkp', 'levelKaryawan', 'unitKerja']);
        // Filter Level Karyawan
        if ($level_karyawan = request('filter_tipe')) {
            $query->whereHas('levelKaryawan', function ($q) use ($level_karyawan) {
                $q->where('nama_level', $level_karyawan);
            });
        }
        if ($unit = request('filter_unit')) {
            $query->whereHas('unitKerja', function ($q) use ($unit) {
                $q->where('nama_unit', $unit);
            });
        }


        if ($jenis_kelamin = request('filter_jenis_kelamin')) {
            $query->where('jenis_kelamin', $jenis_kelamin);
            // pastikan di tabel Wahana ada kolom 'status'
            // misalnya nilainya 'aktif' / 'nonaktif' atau 1/0
        }

        $searchable = ['kode_karyawan', 'nama_karyawan', 'nik', 'tempat_lahir'];

        if ($search = request('search')) {
            $query->where(function ($q) use ($search, $searchable) {
                foreach ($searchable as $col) {
                    $q->orWhere($col, 'like', "%{$search}%");
                }
                $q->orWhereHas('levelKaryawan', function ($q4) use ($search) {
                    $q4->where('nama_level', 'like', "%{$search}%");
                });
                $q->orWhereHas('unitKerja', function ($q1) use ($search) {
                    $q1->where('nama_unit', 'like', "%{$search}%");
                });
            });
        }

        $employees = $query->paginate(10); // tampilkan 10 per halaman




        $unit = UnitKerja::pluck('nama_unit')->filter()->unique()->values();
        $level_karyawan = LevelKaryawan::pluck('nama_level')->filter()->unique()->values();
        return view('sales_person.index', compact('employees', 'level_karyawan', 'unit'));
    }

    public function create()
    {
        $levels = LevelKaryawan::all();
        $ptkps = Ptkp::all();
        $jabatans = Jabatan::all();
        $units = UnitKerja::all();
        $employee = Employee::all();
        $user = User::all();
        return view('sales_person.create', compact('levels', 'ptkps', 'jabatans', 'units', 'employee', 'user'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_karyawan' => 'required|string',
            'nama_karyawan' => 'required|string',
            'nik' => 'required|string',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|string',
            'golongan_darah' => 'nullable|string',
            'tinggi_badan' => 'nullable|string',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string',
            'email' => 'nullable|email',
            'agama' => 'nullable|string',
            'kewarganegaraan' => 'nullable|string',
            'status_pernikahan' => 'nullable|string',
            'ptkp_id' => 'required|exists:ptkps,id',
            'supervisor_id' => 'required|exists:employees,id',
            'user_id' => 'nullable|exists:users,id',
            'jabatan_id' => 'required|exists:jabatans,id',
            'level_kepegawaian_id' => 'required|exists:level_karyawans,id',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'tanggal_masuk' => 'nullable|date',
            'tanggal_keluar' => 'nullable|date',
            'status_pegawai' => 'nullable|string',
            'sertifikat' => 'nullable|string',
            'photo' => 'nullable|mimes:jpg,jpeg,png,pdf',
            'foto_ktp' => 'nullable|mimes:jpg,jpeg,png,pdf',
            'rfid_code' => 'nullable|string|unique:employees,rfid_code',


        ]);

        // Simpan file jika ada
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        if ($request->hasFile('foto_ktp')) {
            $validated['foto_ktp'] = $request->file('foto_ktp')->store('ktp', 'public');
        }

        Employee::create($validated);

        return redirect()->route('sales_person.index')->with('success', 'Data karyawan berhasil ditambahkan.');
    }
    public function show($id)
    {
        $employee = Employee::with(['jabatan', 'ptkp', 'levelKaryawan', 'unitKerja', 'supervisor'])->findOrFail($id);

        return view('sales_person.show', compact('employee'));
    }
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $levels = LevelKaryawan::all();
        $ptkps = Ptkp::all();
        $jabatans = Jabatan::all();
        $units = UnitKerja::all();
        $atasan = Employee::all();
        $user = User::all();
        return view('sales_person.edit', compact('employee', 'ptkps', 'levels', 'jabatans', 'units', 'atasan', 'user'));
    }
    public function update(Request $request, $id): RedirectResponse
    {
        // Validasi form
        $this->validate($request, [
            'kode_karyawan' => 'required|string',
            'nama_karyawan' => 'required|string',
            'nik' => 'required|string',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|string',
            'golongan_darah' => 'nullable|string',
            'tinggi_badan' => 'nullable|string',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string',
            'email' => 'nullable|email',
            'agama' => 'nullable|string',
            'kewarganegaraan' => 'nullable|string',
            'status_pernikahan' => 'nullable|string',
            'ptkp_id' => 'required|exists:ptkps,id',
            'jabatan_id' => 'required|exists:jabatans,id',
            'level_kepegawaian_id' => 'required|exists:level_karyawans,id',
            'supervisor_id' => 'required|exists:employees,id',
            'user_id' => 'nullable|exists:users,id',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'tanggal_masuk' => 'nullable|date',
            'tanggal_keluar' => 'nullable|date',
            'status_pegawai' => 'nullable|string',
            'sertifikat' => 'nullable|string',
            'photo' => 'nullable|mimes:jpg,jpeg,png,pdf',
            'foto_ktp' => 'nullable|mimes:jpg,jpeg,png,pdf',
            'rfid_code' => 'nullable|string|unique:employees,rfid_code,' . $id,
        ]);

        $employee = Employee::findOrFail($id);

        // handle upload photo jika ada
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photo->storeAs('public/employee', $photo->hashName());

            // hapus foto lama
            if ($employee->photo) {
                Storage::delete('public/employee/' . $employee->photo);
            }

            $employee->photo = $photo->hashName();
        }

        if ($request->hasFile('foto_ktp')) {
            $ktp = $request->file('foto_ktp');
            $ktp->storeAs('public/employee', $ktp->hashName());

            // hapus KTP lama
            if ($employee->foto_ktp) {
                Storage::delete('public/employee/' . $employee->foto_ktp);
            }

            $employee->foto_ktp = $ktp->hashName();
        }

        // update data lain
        $employee->update($request->except(['photo', 'foto_ktp']));

        return redirect()->route('sales_person.show', $employee->id)
            ->with(['success' => 'Data Berhasil Diubah!']);
    }
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        $employee->delete();

        return redirect()->route('sales_person.index')->with('success', ' Data berhasil dihapus.');
    }
    public function search(Request $request)
    {
        $term = $request->q;
        $employee = Employee::where('nama_karyawan', 'like', "%$term%")
            ->select('id', 'nama_karyawan')
            ->limit(20)
            ->get();

        return response()->json($employee);
    }
}

<?php

namespace App\Http\Controllers;

use App\Employee;
use App\EmployeeOffDay;
use Illuminate\Http\Request;

class EmployeeOffDayController extends Controller
{
    //
    public function index(Request $req)
    {
        $req->validate(['tanggal' => 'required|date', 'unit_id' => 'nullable|integer']);
        $tanggal = $req->tanggal;
        $unitId  = $req->unit_id;

        $q = EmployeeOffDay::query()
            ->join('employees', 'employees.id', '=', 'employee_off_days.employee_id')
            ->select('employee_off_days.id', 'employee_off_days.employee_id', 'employee_off_days.tanggal', 'employees.nama_karyawan');

        if ($unitId) {
            // Sesuaikan kalau relasi unit karyawan berbeda
            $q->where('employees.unit_kerja_id', $unitId);
        }

        $rows = $q->whereDate('employee_off_days.tanggal', $tanggal)
            ->orderBy('employees.nama_karyawan')
            ->get();

        // Kembalikan juga list kandidat karyawan (untuk multi-select add)
        $offIds = $rows->pluck('employee_id')->all();
        $candidates = Employee::query()
            ->when($unitId, fn($qq) => $qq->where('unit_kerja_id', $unitId))
            ->orderBy('nama_karyawan')
            ->get(['id', 'nama_karyawan']);

        return response()->json([
            'offs'       => $rows,        // [{id, employee_id, tanggal, nama_karyawan}]
            'candidates' => $candidates,  // untuk multi-select
            'off_ids'    => $offIds,
        ]);
    }

    // POST /off-days  (bisa multi)
    public function store(Request $req)
    {
        $data = $req->validate([
            'tanggal'      => 'required|date',
            'employee_id'  => 'required|array|min:1',
            'employee_id.*' => 'required|exists:employees,id',
            'catatan'      => 'nullable|string',
        ]);

        $created = [];
        foreach ($data['employee_id'] as $eid) {
            $exists = EmployeeOffDay::where('employee_id', $eid)
                ->whereDate('tanggal', $data['tanggal'])
                ->exists();
            if (!$exists) {
                $created[] = EmployeeOffDay::create([
                    'employee_id' => $eid,
                    'tanggal'     => $data['tanggal'],
                    'catatan'     => $data['catatan'] ?? null,
                ])->id;
            }
        }

        return response()->json(['ok' => true, 'created_ids' => $created]);
    }

    // DELETE /off-days/{off}
    public function destroy(EmployeeOffDay $off)
    {
        $off->delete();
        return response()->json(['ok' => true]);
    }
}

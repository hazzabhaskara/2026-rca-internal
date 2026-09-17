<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\CompanyProfile;
use App\Customers;
use App\Employee;
use App\Vendors;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index(Request $request)
    {
        $totalCustomers = Customers::count();
        $totalVendors = Vendors::count();
        $totalAccount = chartOfAccount::count();
        $company = CompanyProfile::first();

        $jumlahLaki = Employee::where('jenis_kelamin', 'Laki-laki')->count();
        $jumlahPerempuan = Employee::where('jenis_kelamin', 'Perempuan')->count();

        // === Data Unit Kerja ===
        $unitKerjaData = Employee::join('unit_kerjas', 'employees.unit_kerja_id', '=', 'unit_kerjas.id')
            ->select('unit_kerjas.nama_unit', \DB::raw('count(*) as total'))
            ->groupBy('unit_kerjas.nama_unit')
            ->get();
        $unitKerjaLabels = $unitKerjaData->pluck('nama_unit');
        $unitKerjaCounts = $unitKerjaData->pluck('total');

        // === Data Level Karyawan ===
        $levelKaryawanData = Employee::join('level_karyawans', 'employees.level_kepegawaian_id', '=', 'level_karyawans.id')
            ->select('level_karyawans.nama_level', \DB::raw('count(*) as total'))
            ->groupBy('level_karyawans.nama_level')
            ->get();
        $levelKaryawanLabels = $levelKaryawanData->pluck('nama_level');
        $levelKaryawanCounts = $levelKaryawanData->pluck('total');

        return view('dashboard', compact(
            'totalCustomers',
            'totalVendors',
            'totalAccount',
            'jumlahLaki',
            'jumlahPerempuan',
            'unitKerjaLabels',
            'unitKerjaCounts',
            'levelKaryawanLabels',
            'levelKaryawanCounts',
            'company'
        ));
    }
}

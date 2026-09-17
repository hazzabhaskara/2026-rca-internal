<?php

namespace App\Http\Controllers;

use App\PembayaranGaji;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SlipGajiController extends Controller
{
    public function index()
    {
        $pembayarans = PembayaranGaji::whereHas('employee.levelkaryawan', function ($query) {
            $query->where('nama_level', 'STAFF');
        })
            ->orderBy('tanggal_pembayaran', 'desc') // urutkan berdasarkan tanggal pembayaran
            ->paginate(10);

        return view('slip.index', compact('pembayarans'));
    }
    public function indexNonStaff()
    {
        $pembayarans = PembayaranGaji::whereHas('employee.levelkaryawan', function ($query) {
            $query->where('nama_level', '<>',  'STAFF');
        })
            ->orderBy('tanggal_pembayaran', 'desc') // urutkan berdasarkan tanggal pembayaran
            ->paginate(10);

        return view('slip.nonStaff.index', compact('pembayarans'));
    }


    public function show($id)
    {
        $pembayaran = PembayaranGaji::with(['employee', 'details.komponen'])->findOrFail($id);

        return view('slip.show', compact('pembayaran'));
    }
    public function showNonSataff($id)
    {
        $pembayaran = PembayaranGaji::with(['employee', 'details.komponen'])->findOrFail($id);

        return view('slip.nonStaff.show', compact('pembayaran'));
    }

    public function download($id)
    {
        $pembayaran = PembayaranGaji::with(['employee', 'details.komponen'])->findOrFail($id);

        $pdf = Pdf::loadView('slip.show', compact('pembayaran'))
            ->setPaper('A4', 'portrait');

        return $pdf->download("slip-gaji-{$pembayaran->employee->nama_karyawan}-{$pembayaran->periode_awal}.pdf");
    }
    public function downloadNonStaff($id)
    {
        $pembayaran = PembayaranGaji::with(['employee', 'details.komponen'])->findOrFail($id);

        $pdf = Pdf::loadView('slip.nonStaff.show', compact('pembayaran'))
            ->setPaper('A4', 'portrait');

        return $pdf->download("slip-gaji-{$pembayaran->employee->nama_karyawan}-{$pembayaran->periode_awal}.pdf");
    }
}

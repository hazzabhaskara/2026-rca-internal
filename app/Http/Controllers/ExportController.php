<?php

namespace App\Http\Controllers;

use App\Exports\ChartOfAccountExport;
use App\Exports\CustomersExport;
use App\Exports\DepartemenExport;
use App\Exports\EmployeeExport;
use App\Exports\FiscalAccountExport;
use App\Exports\IntangibleAssetExport;
use App\Exports\ItemsExport;
use App\Exports\JournalEntryExport;
use App\Exports\KlasifikasiAkunExport;
use App\Exports\KomponenPenghasilanExport;
use App\Exports\ProjectExport;
use App\Exports\PtkpExport;
use App\Exports\ShiftKaryawanExport;
use App\Exports\ShiftKaryawanTabelExport;
use App\Exports\TangibleAssetExport;
use App\Exports\TargetUnitExport;
use App\Exports\TaxRatesExport;
use App\Exports\TransaksiWahanaExport;
use App\Exports\VendorsExport;
use App\Exports\WahanaExport;
use App\FiscalAccount;
use App\TransaksiWahana;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    //
    public function exportklasifikasiAkun()
    {
        return Excel::download(new KlasifikasiAkunExport, 'klasifikasiAkun.xlsx');
    }
    public function exportchartOfAccount()
    {
        return Excel::download(new ChartOfAccountExport, 'chartOfAccount.xlsx');
    }
    public function exportDepartemen()
    {
        return Excel::download(new DepartemenExport, 'departemen.xlsx');
    }
    public function exportcustomers()
    {
        return Excel::download(new CustomersExport, 'customers.xlsx');
    }
    public function exportItems()
    {
        return Excel::download(new ItemsExport, 'items.xlsx');
    }
    public function exportPtkp()
    {
        return Excel::download(new PtkpExport, 'ptkp.xlsx');
    }
    public function exportTaxRates()
    {
        return Excel::download(new TaxRatesExport, 'tax_rates.xlsx');
    }
    public function exportWahana()
    {
        return Excel::download(new WahanaExport, 'wahana.xlsx');
    }
    public function exportEmployee()
    {
        return Excel::download(new EmployeeExport, 'employee.xlsx');
    }
    public function exportTangibleAsset()
    {
        return Excel::download(new TangibleAssetExport, 'tangible_asset.xlsx');
    }
    public function exportIntangibleAsset()
    {
        return Excel::download(new IntangibleAssetExport, 'intangile_asset.xlsx');
    }
    public function exportProject()
    {
        return Excel::download(new ProjectExport, 'project.xlsx');
    }
    public function exportKomponenPenghasilan()
    {
        return Excel::download(new KomponenPenghasilanExport, 'komponen_penghasilan.xlsx');
    }
    public function exportTargetUnit()
    {
        return Excel::download(new TargetUnitExport, 'target_unit.xlsx');
    }
    public function exportTransaksiWahana()
    {
        return Excel::download(new TransaksiWahanaExport, 'transaksi_wahana.xlsx');
    }
    public function exportShiftKaryawan()
    {
        return Excel::download(new ShiftKaryawanExport, 'shift_karyawan.xlsx');
    }
    public function exportShiftKaryawanTabel(Request $request)
    {
        $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date|after_or_equal:tgl_awal',
        ]);

        $tgl_awal = date('Y-m-d', strtotime($request->tgl_awal));
        $tgl_akhir = date('Y-m-d', strtotime($request->tgl_akhir));

        $cekData = \App\ShiftKaryawanWahana::whereBetween('tanggal', [$tgl_awal, $tgl_akhir])->exists();

        // Jika tidak ada data, kembali ke halaman sebelumnya dengan alert
        if (!$cekData) {
            return back()->with('error', 'Tidak ada data shift pada rentang tanggal tersebut.');
        }

        // Jika ada data, lanjutkan export
        return Excel::download(
            new ShiftKaryawanTabelExport($tgl_awal, $tgl_akhir),
            'shift_karyawan_tabel.xlsx'
        );
    }

    public function exportJournalEntry()
    {
        return Excel::download(new JournalEntryExport, 'journal_entry.xlsx');
    }
    public function exportfiscal()
    {
        return Excel::download(new FiscalAccountExport, 'fiscal_account.xlsx');
    }

    public function exportDepartemenAkunTemplate()
    {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=template_departemen_akun.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['kode', 'kode_akun', 'nama_akun'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportDepartemenAkun()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DepartemenAkunExport, 'departemen_akun.xlsx');
    }
}

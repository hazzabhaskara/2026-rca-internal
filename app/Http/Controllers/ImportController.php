<?php

namespace App\Http\Controllers;

use App\Imports\ChartOfAccountImport;
use App\Imports\CustomersImport;
use App\Imports\DepartemenImport;
use App\Imports\EmployeeImport;
use App\Imports\FiscalAccountImport;
use App\Imports\IntangibleAssetImport;
use App\Imports\ItemsImport;
use App\Imports\JournalEntryImport;
use App\Imports\KlasifikasiAkunImport;
use App\Imports\KomponenPenghasilanImport;
use App\Imports\ProjectImport;
use App\Imports\PtkpImport;
use App\Imports\ShiftKaryawanImport;
use App\Imports\TangibleAssetImport;
use App\Imports\TargetUnitImport;
use App\Imports\TargetWahanaImport;
use App\Imports\TransaksiWahanaImport;
use App\Imports\VendorsImport;
use App\Imports\WahanaImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;

class ImportController extends Controller
{
    //
    public function importklasifikasiAkun(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new KlasifikasiAkunImport, $request->file('file'));

            return back()->with('success', 'Data klasifikasi akun berhasil diimpor.');
        } catch (\Exception $e) {
            // Tangkap error dari Import (misalnya numbering tidak ada)
            return back()->with('error', $e->getMessage());
        }
    }

    public function importchartOfAccount(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        Excel::import(new ChartOfAccountImport, $request->file('file'));

        return back()->with('success', 'Import berhasil!');
    }
    public function importDepartemen(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);
        Excel::import(new DepartemenImport, $request->file('file'));

        return back()->with('success', 'Data Departemen berhasil diimpor.');
    }
    public function importcustomers(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);
        Excel::import(new CustomersImport, $request->file('file'));

        return back()->with('success', 'Data Customers berhasil diimpor.');
    }
    public function importjournal_entry(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            $importer = new \App\Imports\JournalEntryImport();
            \Maatwebsite\Excel\Facades\Excel::import($importer, $request->file('file'));

            // Setelah raw data masuk ke staging, kita proses via IngestionService
            $service = new \App\Services\JournalIngestionService();
            $result = $service->processPendingEntries($importer->getBatchId());

            if ($result['failed_rows'] > 0) {
                // Jika ada yang gagal, arahkan user ke halaman Review Staging
                return redirect()->route('staging_journal.index')->with('error', "Import Selesai dengan error. Berhasil: {$result['processed_rows']} baris. Gagal: {$result['failed_rows']} baris. Silakan periksa di bawah.");
            }

            return back()->with('success', 'Semua data berhasil diimport dan divalidasi ke tabel utama.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function importItems(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);
        Excel::import(new ItemsImport, $request->file('file'));

        return back()->with('success', 'Data Items akun berhasil diimpor.');
    }
    public function ImportVendors(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);
        Excel::import(new VendorsImport, $request->file('file'));

        return back()->with('success', 'Data Vendors akun berhasil diimpor.');
    }
    public function importPtkp(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);
        Excel::import(new PtkpImport, $request->file('file'));

        return back()->with('success', 'Data PTKP berhasil diimpor.');
    }

    public function importWahana(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new WahanaImport, $request->file('file'));

            return back()->with('success', 'Data wahana berhasil diimpor.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat impor: ' . $e->getMessage());
        }
    }
    public function importEmployee(Request $request)
    {
        $import = new EmployeeImport;
        Excel::import($import, $request->file('file'));

        if (!empty($import->errors)) {
            return back()->with('errors_import', $import->errors);
        }

        return back()->with('success', 'Import berhasil tanpa error.');
    }
    public function importTangibleAsset(Request $request)
    {
        $import = new TangibleAssetImport;
        Excel::import($import, $request->file('file'));

        if (!empty($import->errors)) {
            return back()->with('errors_import', $import->errors);
        }

        return back()->with('success', 'Import berhasil tanpa error.');
    }
    public function importIntangibleAsset(Request $request)
    {
        $import = new IntangibleAssetImport;
        Excel::import($import, $request->file('file'));

        if (!empty($import->errors)) {
            return back()->with('errors_import', $import->errors);
        }

        return back()->with('success', 'Import berhasil tanpa error.');
    }
    public function importProject(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);
        Excel::import(new ProjectImport, $request->file('file'));

        return back()->with('success', 'Data project  berhasil diimpor.');
    }
    public function importKomponenPenghasilan(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new KomponenPenghasilanImport, $request->file('file'));

            return back()->with('success', 'Data Komponen Penghasilan berhasil diimpor.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat impor: ' . $e->getMessage());
        }
    }
    public function importTargetUnit(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new TargetUnitImport, $request->file('file'));

            return back()->with('success', 'Data target unit berhasil diimpor.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat impor: ' . $e->getMessage());
        }
    }
    public function importTargetWahana(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new TargetWahanaImport, $request->file('file'));

            return back()->with('success', 'Data target wahana berhasil diimpor.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat impor: ' . $e->getMessage());
        }
    }
    public function importTransaksiWahana(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new TransaksiWahanaImport, $request->file('file'));

            return back()->with('success', 'Data transaksi wahana berhasil diimpor.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat impor: ' . $e->getMessage());
        }
    }
    public function importShiftKaryawan(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new ShiftKaryawanImport, $request->file('file'));

            return back()->with('success', 'Data shift karyawan wahana berhasil diimpor.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat impor: ' . $e->getMessage());
        }
    }

    public function importfiscal(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);
        Excel::import(new FiscalAccountImport, $request->file('file'));

        return back()->with('success', 'Data Fiscal Account  berhasil diimpor.');
    }

    public function importDepartemenAkun(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        try {
            Excel::import(new \App\Imports\DepartemenAkunImport, $request->file('file'));
            return back()->with('success', 'Data Assign Account (Departemen Akun) berhasil diimpor.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat impor: ' . $e->getMessage());
        }
    }
}

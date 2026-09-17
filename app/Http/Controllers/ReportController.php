<?php

namespace App\Http\Controllers;

use App\Absensi;
use App\chartOfAccount;
use App\Exports\TargetWahanaExport;
use App\JenisHari;
use App\SalesTaxes;
use App\StartNewYear;
use App\TargetWahana;
use App\UnitKerja;
use App\Wahana;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use SebastianBergmann\CodeCoverage\Report\Xml\Unit;

class ReportController extends Controller
{
    /**
     * Mapping Klasifikasi Akun ke Aktivitas Arus Kas (Metode Langsung)
     */
    const AKTIVITAS_MAPPING = [
        // ===== AKTIVITAS OPERASIONAL =====
        'Cash' => 'Operasional',
        'Bank' => 'Operasional',
        'Operating Revenue' => 'Operasional',
        'Other Revenue' => 'Operasional',
        'Non-Operating Revenue' => 'Operasional',
        'Revenue' => 'Operasional',
        'Accounts Receivable' => 'Operasional',
        'Other Receivables' => 'Operasional',
        'Accounts Payable' => 'Operasional',
        'Other Payable' => 'Operasional',
        'Inventory' => 'Operasional',
        'Other Current Asset' => 'Operasional',
        'Current Asset' => 'Operasional',
        'Current Liability' => 'Operasional',
        'Other Current Liability' => 'Operasional',
        'Payroll Expense' => 'Operasional',
        'Operating Expense' => 'Operasional',
        'General & Admin. Expense' => 'Operasional',
        'Cost of Goods Sold' => 'Operasional',
        'Income Tax Expense' => 'Operasional',
        'Non-Operating Expense' => 'Operasional',
        'Credit Card Receivable' => 'Operasional',
        'Credit Card Payable' => 'Operasional',
        'Cash Equivalents' => 'Operasional',
        'Allowance for Bad Debts' => 'Operasional',
        'Sales Tax Payable' => 'Operasional',
        'Payroll Tax Payable' => 'Operasional',
        'Employee Deductions Payable' => 'Operasional',
        'Income Tax Payable' => 'Operasional',
        'Deferred Revenue' => 'Operasional',
        'Deferred Income Taxes' => 'Operasional',
        'Employee Benefits' => 'Operasional',
        'Expense' => 'Operasional',
        'Extraordinary Gain' => 'Operasional',
        'Extraordinary Loss' => 'Operasional',
        
        // ===== AKTIVITAS INVESTASI =====
        'Capital Asset' => 'Investasi',
        'Accum. Amort./Depreciation' => 'Investasi',
        'Other Non-Current Asset' => 'Investasi',
        'Other Asset' => 'Investasi',
        'Marketable Securities' => 'Investasi',
        'Long Term Receivables' => 'Investasi',
        'Other Long Term Investments' => 'Investasi',
        'Gain' => 'Investasi',
        'Loss' => 'Investasi',
        
        // ===== AKTIVITAS PENDANAAN =====
        'Share Capital' => 'Pendanaan',
        'Retained Earnings' => 'Pendanaan',
        'Current Earnings' => 'Pendanaan',
        'Owner/Partner Contributions' => 'Pendanaan',
        'Owner/Partner Withdrawals' => 'Pendanaan',
        'Dividends' => 'Pendanaan',
        'Long Term Debt' => 'Pendanaan',
        'Short Term Debt' => 'Pendanaan',
        'Debt' => 'Pendanaan',
        'Long Term Liability' => 'Pendanaan',
        'Other Non-Current Liability' => 'Pendanaan',
        'Other Liability' => 'Pendanaan',
        'Interest Expense' => 'Pendanaan',
    ];
    
    /**
     * Klasifikasi yang tidak menghasilkan arus kas (non-cash)
     */
    const NON_CASH_KLASIFIKASI = [
        'Amort./Depreciation Expense',
        'Bad Debt Expense',
    ];
    
    //

    public function reportAccount()
    {
        $chartOfAccounts = ChartOfAccount::with('klasifikasiAkun')
            ->orderBy('kode_akun')
            ->get();

        // DATA DUMMY (jika kosong)
        if ($chartOfAccounts->isEmpty()) {
            $chartOfAccounts = collect([
                (object)[
                    'kode_akun' => '10000',
                    'nama_akun' => 'AKTIVA',
                    'level_akun' => 'HEADER',
                    'tipe_akun' => 'ASET'
                ],
                (object)[
                    'kode_akun' => '11000',
                    'nama_akun' => 'AKTIVA LANCAR',
                    'level_akun' => 'GROUP ACCOUNT',
                    'tipe_akun' => 'ASET'
                ],
                (object)[
                    'kode_akun' => '11100',
                    'nama_akun' => 'KAS',
                    'level_akun' => 'ACCOUNT',
                    'tipe_akun' => 'ASET'
                ],
                (object)[
                    'kode_akun' => '11110',
                    'nama_akun' => 'KAS KECIL',
                    'level_akun' => 'SUB ACCOUNT',
                    'tipe_akun' => 'ASET'
                ],
            ]);
        }

        foreach ($chartOfAccounts as $coa) {
            // Normalisasi level akun (hapus isi dalam tanda kurung kalau ada)
            $coa->level_akun = strtoupper(trim(preg_replace('/\s*\(.*?\)/', '', $coa->level_akun)));

            // Hitung level indent dari panjang digit signifikan
            $coa->level_indent = $this->getLevelIndent($coa->kode_akun);

            // Tentukan parent prefix (berdasarkan level_indent - 1)
            $coa->parent_prefix = $this->getParentKode($coa->kode_akun, $coa->level_indent - 1);

            // Deteksi apakah punya child
            $coa->has_child = $chartOfAccounts->contains(function ($child) use ($coa) {
                // Header (level 0) tidak punya parent → skip
                if ($coa->level_indent < 0) {
                    return false;
                }

                // Prefix parent berdasarkan level indent
                $prefix = substr($coa->kode_akun, 0, $coa->level_indent);

                return $child->kode_akun !== $coa->kode_akun
                    && Str::startsWith($child->kode_akun, $prefix)
                    && $this->getLevelIndent($child->kode_akun) > $coa->level_indent;
            });
        }

        return view('report.account', compact('chartOfAccounts'));
    }

    /**
     * Hitung level akun berdasarkan jumlah digit signifikan (tanpa trailing zero)
     */
    private function getLevelIndent($kodeAkun)
    {
        $kodeSignifikan = rtrim($kodeAkun, '0');
        $panjang = strlen($kodeSignifikan);

        switch ($panjang) {
            case 1:
                return 0; // HEADER
            case 2:
                return 1; // GROUP
            case 3:
                return 2; // ACCOUNT
            default:
                return 3; // SUB ACCOUNT
        }
    }

    /**
     * Ambil kode parent berdasarkan prefix sesuai level indent
     */
    private function getParentKode($kodeAkun, $levelIndent)
    {
        if ($levelIndent <= 0) return null;

        return substr($kodeAkun, 0, $levelIndent);
    }


    public function reportKlasifikasi()
    {
        $data = chartOfAccount::with('klasifikasiAkun')->orderBy('kode_akun');
        return view('report.klasifikasi', compact('data'));
    }
    public function salesTaxes()
    {
        $data = SalesTaxes::with('purchaseAccount', 'salesAccount')->orderBy('name')->get();
        return view('report.sales_taxes', compact('data'));
    }
    public function reportDepartemenAkun()
    {
        $akuns = \App\ChartOfAccount::orderBy('kode_akun')->get();
        $departemens = \App\Departement::orderBy('deskripsi')->get();

        // Ambil data relasi departemen-akun
        $relasi = \App\departemenAkun::get();

        // Buat array untuk keperluan cek cepat
        $relasiMap = [];
        foreach ($relasi as $item) {
            $relasiMap[$item->akun_id][$item->departemen_id] = true;
        }

        return view('report.departemen-akun', compact('akuns', 'departemens', 'relasiMap'));
    }
    public function filter()
    {
        $units = UnitKerja::all();
        return view('report.absensi.filter', compact('units'));
    }
    public function hasil(Request $request)
    {
        $filterType = $request->get('filter', 'custom');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');
        $unitId    = $request->get('unit_id');

        // Logic tanggal otomatis
        if ($filterType === 'weekly') {
            $startDate = Carbon::now()->startOfWeek()->toDateString();
            $endDate   = Carbon::now()->endOfWeek()->toDateString();
        } elseif ($filterType === 'monthly') {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate   = Carbon::now()->endOfMonth()->toDateString();
        }

        // Fallback kalau custom tapi tidak isi tanggal
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate   = Carbon::now()->endOfMonth()->toDateString();
        }

        // Query absensi
        $query = Absensi::with(['employee.levelKaryawan', 'employee.unitKerja'])
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate]))
            ->when($unitId, fn($q) => $q->whereHas('employee', fn($q2) => $q2->where('unit_kerja_id', $unitId)));

        $rekap = $query->get()
            ->groupBy('employee_id')
            ->map(function ($rows) {
                $pegawai = $rows->first()->employee;

                // Hari kerja (Masuk + Terlambat)
                $totalHari = $rows->whereIn('status', ['Masuk', 'Terlambat'])->count();

                // Hitung lembur (Lembur Masuk + Lembur Pulang per tanggal)
                $totalLembur = $rows->groupBy('tanggal')
                    ->map(function ($absenPerTanggal) {
                        $masuk  = $absenPerTanggal->firstWhere('status', 'Lembur Masuk');
                        $pulang = $absenPerTanggal->firstWhere('status', 'Lembur Pulang'); // perbaikan di sini

                        if ($masuk && $pulang) {
                            $masukJam  = \Carbon\Carbon::parse($masuk->jam);
                            $pulangJam = \Carbon\Carbon::parse($pulang->jam);

                            return $pulangJam->diffInMinutes($masukJam);
                        }
                        return 0;
                    })
                    ->sum(); // jumlahkan hasil per tanggal

                return [
                    'pegawai'      => $pegawai->nama_karyawan ?? '-',
                    'level'        => $pegawai->levelKaryawan->nama_level ?? '-',
                    'unit'         => $pegawai->unitKerja->nama_unit ?? '-',
                    'total_hari'   => $totalHari,
                    'total_lembur' => round($totalLembur / 60, 2), // dalam jam
                ];
            });

        $unitName = $unitId ? UnitKerja::find($unitId)->nama_unit : 'Semua Unit';

        return view('report.absensi.hasil', compact('rekap', 'startDate', 'endDate', 'filterType', 'unitName'));
    }
    public function exportPdf(Request $request)
    {
        // Ambil rekap ulang dari hasil()
        $rekapData = $this->getRekapData($request);

        $pdf = Pdf::loadView('report.absensi.pdf', $rekapData)
            ->setPaper('A4', 'landscape');

        return $pdf->download("rekap-absensi-{$rekapData['startDate']}-{$rekapData['endDate']}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $rekapData = $this->getRekapData($request);

        return Excel::download(
            new \App\Exports\RekapAbsensiExport($rekapData['rekap']),
            "rekap-absensi-{$rekapData['startDate']}-{$rekapData['endDate']}.xlsx"
        );
    }

    // Helper untuk ambil data rekap biar tidak duplikasi
    private function getRekapData(Request $request)
    {
        $filterType = $request->get('filter', 'custom');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');
        $unitId    = $request->get('unit_id');

        // Logic tanggal otomatis
        if ($filterType === 'weekly') {
            $startDate = Carbon::now()->startOfWeek()->toDateString();
            $endDate   = Carbon::now()->endOfWeek()->toDateString();
        } elseif ($filterType === 'monthly') {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate   = Carbon::now()->endOfMonth()->toDateString();
        }

        // Fallback kalau custom tapi tidak isi tanggal
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate   = Carbon::now()->endOfMonth()->toDateString();
        }

        // Query absensi
        $query = Absensi::with(['employee.levelKaryawan', 'employee.unitKerja'])
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate]))
            ->when($unitId, fn($q) => $q->whereHas('employee', fn($q2) => $q2->where('unit_kerja_id', $unitId)));

        $rekap = $query->get()
            ->groupBy('employee_id')
            ->map(function ($rows) {
                $pegawai = $rows->first()->employee;

                $totalHari = $rows->whereIn('status', ['Masuk', 'Terlambat'])->count();

                $totalLembur = $rows->groupBy('tanggal')
                    ->map(function ($absenPerTanggal) {
                        $masuk  = $absenPerTanggal->firstWhere('status', 'Lembur Masuk');
                        $pulang = $absenPerTanggal->firstWhere('status', 'Lembur Pulang');

                        if ($masuk && $pulang) {
                            $masukJam  = \Carbon\Carbon::parse($masuk->jam);
                            $pulangJam = \Carbon\Carbon::parse($pulang->jam);

                            return $pulangJam->diffInMinutes($masukJam);
                        }
                        return 0;
                    })
                    ->sum();

                return [
                    'pegawai'      => $pegawai->nama_karyawan ?? '-',
                    'level'        => $pegawai->levelKaryawan->nama_level ?? '-',
                    'unit'         => $pegawai->unitKerja->nama_unit ?? '-',
                    'total_hari'   => $totalHari,
                    'total_lembur' => round($totalLembur / 60, 2), // dalam jam
                ];
            });

        $unitName = $unitId ? UnitKerja::find($unitId)->nama_unit : 'Semua Unit';

        return [
            'rekap'      => $rekap,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
            'filterType' => $filterType,
            'unitName'   => $unitName,
        ];
    }
    // Halaman Filter
    public function filterWahana()
    {
        $units = UnitKerja::orderBy('nama_unit')->get();
        $wahanas = Wahana::orderBy('nama_wahana')->get();
        $jenisHaris = JenisHari::orderBy('nama')->get();

        return view('report.target_wahana.filter', compact('units', 'wahanas', 'jenisHaris'));
    }

    // Hasil Report
    public function resultWahana(Request $request)
    {
        $query = TargetWahana::with(['wahana.unitKerja', 'jenis_hari']);

        if ($request->filled('unit_id')) {
            $query->whereHas('wahana', function ($q) use ($request) {
                $q->where('unit_kerja_id', $request->unit_id);
            });
        }

        if ($request->filled('wahana_id')) {
            $query->where('wahana_id', $request->wahana_id);
        }

        if ($request->filled('jenis_hari_id')) {
            $query->where('jenis_hari_id', $request->jenis_hari_id);
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }

        $results = $query->get();

        return view('report.target_wahana.result', compact('results'));
    }
    // ReportController.php
    public function getWahanaByUnit($unitId)
    {
        $wahanas = Wahana::where('unit_kerja_id', $unitId)->get(['id', 'nama_wahana']);
        return response()->json($wahanas);
    }

    public function exportPdfWahana(Request $request)
    {
        // Query ulang sesuai filter
        $query = TargetWahana::with(['wahana.unitKerja', 'jenis_hari']);

        if ($request->filled('unit_id')) {
            $query->whereHas('wahana', fn($q) => $q->where('unit_kerja_id', $request->unit_id));
        }
        if ($request->filled('wahana_id')) {
            $query->where('wahana_id', $request->wahana_id);
        }
        if ($request->filled('jenis_hari_id')) {
            $query->where('jenis_hari_id', $request->jenis_hari_id);
        }
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }
        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }

        $results = $query->get();

        $pdf = PDF::loadView('report.target_wahana.pdf', compact('results'));
        return $pdf->download('report.target_wahana.pdf');
    }

    public function exportExcelWahana(Request $request)
    {
        return Excel::download(new TargetWahanaExport($request), 'report_target_wahana.xlsx');
    }
    public function aruskasFilter()
    {
        $account = \App\ChartOfAccount::whereHas('klasifikasiAkun', function ($query) {
            $query->whereIn('nama_klasifikasi', ['Cash', 'Bank']);
        })->get();
        $tahun_buku = StartNewYear::all();

        return view('arus_kas.filter_arus_kas', compact('account', 'tahun_buku'));
    }
    public function reportArusKas(Request $request)
    {
        $tanggalAwal = $request->start_date;
        $tanggalAkhir = $request->end_date;
        $displayMode = $request->display_mode ?? 'source';

        // 1️⃣ Ambil akun kas/bank
        $selectedAccountsRaw = explode(',', $request->selected_accounts ?? '');
        $selectedAccountCodes = [];
        foreach ($selectedAccountsRaw as $item) {
            $parts = explode(' - ', $item);
            if (count($parts) > 0) {
                $selectedAccountCodes[] = trim($parts[0]);
            }
        }

        if (empty($selectedAccountCodes)) {
            $selectedAccountCodes = \App\ChartOfAccount::whereHas('klasifikasiAkun', function ($q) {
                $q->whereIn('nama_klasifikasi', ['Cash', 'Bank']);
            })->pluck('kode_akun')->toArray();
        }

        // 2️⃣ Ambil semua jurnal di rentang tanggal (dengan klasifikasi akun)
        $journalEntries = \App\JournalEntry::with(['details.chartOfAccount.klasifikasiAkun'])
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
            ->get();

        $rows = [];

        // 3️⃣ Loop tiap jurnal (group per source)
        foreach ($journalEntries as $entry) {
            $details = $entry->details;

            // pisahkan kas vs lawan
            $kasDetails = $details->whereIn('kode_akun', $selectedAccountCodes);
            $lawanDetails = $details->whereNotIn('kode_akun', $selectedAccountCodes);

            if ($kasDetails->isEmpty() || $lawanDetails->isEmpty()) continue;

            // total lawan untuk proporsi
            $totalDebetLawan = $lawanDetails->sum('debits');
            $totalKreditLawan = $lawanDetails->sum('credits');

            foreach ($kasDetails as $kas) {
                $isCashIn = $kas->debits > 0;
                $totalKas = $isCashIn ? $kas->debits : $kas->credits;

                foreach ($lawanDetails as $lawan) {
                    // ambil nilai proporsi berdasarkan sisi lawan
                    $nilaiLawan = $isCashIn ? $lawan->credits : $lawan->debits;
                    $totalLawan = $isCashIn ? $totalKreditLawan : $totalDebetLawan;

                    if ($totalLawan == 0) $totalLawan = 1; // hindari div/0
                    $proporsi = $nilaiLawan / $totalLawan;
                    $nilaiKasProporsional = $totalKas * $proporsi;

                    // Get klasifikasi akun dari lawan akun
                    $klasifikasiAkun = $lawan->chartOfAccount->klasifikasiAkun->nama_klasifikasi ?? 'Lainnya';
                    $aktivitas = self::AKTIVITAS_MAPPING[$klasifikasiAkun] ?? 'Operasional';
                    
                    // Skip non-cash items
                    if (in_array($klasifikasiAkun, self::NON_CASH_KLASIFIKASI)) {
                        continue;
                    }

                    $rows[] = [
                        'tanggal'          => $entry->tanggal,
                        'source'           => $entry->source,
                        'akun_kas'         => $kas->kode_akun . ' - ' . ($kas->chartOfAccount->nama_akun ?? ''),
                        'kode_kas'         => $kas->kode_akun,
                        'lawan_akun'       => $lawan->kode_akun . ' - ' . ($lawan->chartOfAccount->nama_akun ?? ''),
                        'kode_lawan'       => $lawan->kode_akun,
                        'nama_lawan'       => $lawan->chartOfAccount->nama_akun ?? '',
                        'klasifikasi_akun' => $klasifikasiAkun,
                        'aktivitas'        => $aktivitas,
                        'line_comment'     => $lawan->comment ?? $kas->comment ?? '',
                        'keterangan'       => $entry->comment ?? '',
                        'cash_in'          => $isCashIn ? $nilaiKasProporsional : 0,
                        'cash_out'         => !$isCashIn ? $nilaiKasProporsional : 0,
                    ];
                }
            }
        }
        
        // 4️⃣ Jika mode direct, group by aktivitas dan klasifikasi
        $aktivitasData = [];
        if ($displayMode === 'direct') {
            $aktivitasData = $this->groupByAktivitas($rows);
        }

        return view('arus_kas.report_arus_kas', compact('rows', 'tanggalAwal', 'tanggalAkhir', 'displayMode', 'aktivitasData'));
    }
    
    /**
     * Group rows by aktivitas for Direct Method display
     */
    private function groupByAktivitas($rows)
    {
        // Get klasifikasi id (kode_klasifikasi) from database for sorting
        $klasifikasiUrutan = \App\KlasifikasiAkun::pluck('id', 'nama_klasifikasi')->toArray();
        
        $aktivitas = [
            'Operasional' => [
                'label' => 'Arus Kas dari Aktivitas Operasional',
                'items' => [],
                'cash_in' => 0,
                'cash_out' => 0,
            ],
            'Investasi' => [
                'label' => 'Arus Kas dari Aktivitas Investasi',
                'items' => [],
                'cash_in' => 0,
                'cash_out' => 0,
            ],
            'Pendanaan' => [
                'label' => 'Arus Kas dari Aktivitas Pendanaan',
                'items' => [],
                'cash_in' => 0,
                'cash_out' => 0,
            ],
        ];
        
        // Group by klasifikasi within each aktivitas
        foreach ($rows as $row) {
            $akt = $row['aktivitas'];
            $klasifikasi = $row['klasifikasi_akun'];
            
            if (!isset($aktivitas[$akt]['items'][$klasifikasi])) {
                $aktivitas[$akt]['items'][$klasifikasi] = [
                    'label' => $klasifikasi,
                    'urutan' => $klasifikasiUrutan[$klasifikasi] ?? 999,
                    'cash_in' => 0,
                    'cash_out' => 0,
                ];
            }
            
            $aktivitas[$akt]['items'][$klasifikasi]['cash_in'] += $row['cash_in'];
            $aktivitas[$akt]['items'][$klasifikasi]['cash_out'] += $row['cash_out'];
            $aktivitas[$akt]['cash_in'] += $row['cash_in'];
            $aktivitas[$akt]['cash_out'] += $row['cash_out'];
        }
        
        // Sort items within each aktivitas by urutan
        foreach ($aktivitas as $key => &$akt) {
            uasort($akt['items'], function($a, $b) {
                return ($a['urutan'] ?? 999) <=> ($b['urutan'] ?? 999);
            });
        }
        
        return $aktivitas;
    }

    /**
     * Export Cash Flow to Excel or PDF
     */
    public function exportArusKas(Request $request)
    {
        $tanggalAwal = $request->start_date;
        $tanggalAkhir = $request->end_date;
        $displayMode = $request->display_mode ?? 'source';
        $format = $request->format ?? 'excel';

        // Get data using same logic as reportArusKas
        $selectedAccountsRaw = explode(',', $request->selected_accounts ?? '');
        $selectedAccountCodes = [];
        foreach ($selectedAccountsRaw as $item) {
            $parts = explode(' - ', $item);
            if (count($parts) > 0) {
                $selectedAccountCodes[] = trim($parts[0]);
            }
        }

        if (empty($selectedAccountCodes)) {
            $selectedAccountCodes = \App\ChartOfAccount::whereHas('klasifikasiAkun', function ($q) {
                $q->whereIn('nama_klasifikasi', ['Cash', 'Bank']);
            })->pluck('kode_akun')->toArray();
        }

        $journalEntries = \App\JournalEntry::with(['details.chartOfAccount'])
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
            ->get();

        $rows = [];

        foreach ($journalEntries as $entry) {
            $details = $entry->details;
            $kasDetails = $details->whereIn('kode_akun', $selectedAccountCodes);
            $lawanDetails = $details->whereNotIn('kode_akun', $selectedAccountCodes);

            if ($kasDetails->isEmpty() || $lawanDetails->isEmpty()) continue;

            $totalDebetLawan = $lawanDetails->sum('debits');
            $totalKreditLawan = $lawanDetails->sum('credits');

            foreach ($kasDetails as $kas) {
                $isCashIn = $kas->debits > 0;
                $totalKas = $isCashIn ? $kas->debits : $kas->credits;

                foreach ($lawanDetails as $lawan) {
                    $nilaiLawan = $isCashIn ? $lawan->credits : $lawan->debits;
                    $totalLawan = $isCashIn ? $totalKreditLawan : $totalDebetLawan;

                    if ($totalLawan == 0) $totalLawan = 1;
                    $proporsi = $nilaiLawan / $totalLawan;
                    $nilaiKasProporsional = $totalKas * $proporsi;

                    $rows[] = [
                        'tanggal'      => $entry->tanggal,
                        'source'       => $entry->source,
                        'akun_kas'     => $kas->kode_akun . ' - ' . ($kas->chartOfAccount->nama_akun ?? ''),
                        'kode_kas'     => $kas->kode_akun,
                        'lawan_akun'   => $lawan->kode_akun . ' - ' . ($lawan->chartOfAccount->nama_akun ?? ''),
                        'line_comment' => $lawan->comment ?? $kas->comment ?? '',
                        'keterangan'   => $entry->comment ?? '',
                        'cash_in'      => $isCashIn ? $nilaiKasProporsional : 0,
                        'cash_out'     => !$isCashIn ? $nilaiKasProporsional : 0,
                    ];
                }
            }
        }

        $filename = "cash_flow_{$displayMode}_{$tanggalAwal}_{$tanggalAkhir}";

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('arus_kas.export_pdf', compact('rows', 'tanggalAwal', 'tanggalAkhir', 'displayMode'))
                ->setPaper('A4', 'landscape');
            return $pdf->download("{$filename}.pdf");
        }

        // Excel export
        return Excel::download(
            new \App\Exports\ArusKasExport($rows, $tanggalAwal, $tanggalAkhir, $displayMode),
            "{$filename}.xlsx"
        );
    }
}

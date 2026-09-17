<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\Departement;
use App\Exports\NeracaDepartemenExport;
use App\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class NeracaDepartemenController extends Controller
{
    /**
     * Display the filter form for Balance Sheet Departemen.
     */
    public function filterNeracaDepartemen()
    {
        $departments = Departement::orderBy('kode')->get();
        return view('neraca_departemen.filter_neraca_departemen', compact('departments'));
    }

    /**
     * Display the Neraca Departemen report (Matrix or Single Department).
     */
    public function neracaDepartemenReport(Request $request)
    {
        $data = $this->buildReportData($request);
        return view('neraca_departemen.neraca_departemen_report', $data);
    }

    /**
     * Export Neraca Departemen to Excel or PDF.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');
        $data = $this->buildReportData($request);
        $dateStr = Carbon::parse($data['tanggalAkhir'])->format('Y-m-d');
        $modeStr = $data['mode'];

        if ($format === 'excel') {
            $filename = "neraca_departemen_{$modeStr}_{$dateStr}.xlsx";
            return Excel::download(new NeracaDepartemenExport($data), $filename);
        }

        if ($format === 'pdf') {
            $filename = "neraca_departemen_{$modeStr}_{$dateStr}.pdf";
            $viewName = $data['mode'] === 'single'
                ? 'neraca_departemen.export_pdf_single'
                : 'neraca_departemen.export_pdf_matrix';

            $pdf = Pdf::loadView($viewName, $data);
            $pdf->setPaper('a4', $data['mode'] === 'single' ? 'portrait' : 'landscape');
            return $pdf->download($filename);
        }

        return back()->with('error', 'Format export tidak dikenali.');
    }

    /**
     * AJAX endpoint returning cross-branch transactions contributing to R/K.
     */
    public function rkAuditDetails(Request $request)
    {
        $deptParam = $request->get('departemen_id');
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $isUnassigned = ($deptParam === 'unassigned' || $deptParam === '' || $deptParam === null || $deptParam === '0');

        if ($isUnassigned) {
            $deptName = 'Non-Departemen / Unassigned';
            $query = DB::table('journal_entry_details as jed')
                ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
                ->whereNull('jed.departemen_akun_id')
                ->where('je.tanggal', '<=', $endDate)
                ->where('je.source', '!=', 'START NEW YEAR')
                ->groupBy('je.id', 'je.tanggal', 'je.source', 'je.comment')
                ->havingRaw('ROUND(SUM(jed.debits) - SUM(jed.credits), 2) != 0')
                ->select(
                    'je.id',
                    'je.tanggal',
                    'je.source',
                    'je.comment',
                    DB::raw('SUM(jed.debits) as dept_debit'),
                    DB::raw('SUM(jed.credits) as dept_credit'),
                    DB::raw('SUM(jed.debits) - SUM(jed.credits) as dept_net')
                )
                ->orderBy('je.tanggal', 'desc');
        } else {
            $deptId = (int)$deptParam;
            $dept = Departement::find($deptId);
            $deptName = $dept ? ($dept->kode . ' - ' . $dept->deskripsi) : "Departemen #{$deptId}";

            $query = DB::table('journal_entry_details as jed')
                ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
                ->join('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')
                ->where('da.departemen_id', $deptId)
                ->where('je.tanggal', '<=', $endDate)
                ->where('je.source', '!=', 'START NEW YEAR')
                ->groupBy('je.id', 'je.tanggal', 'je.source', 'je.comment')
                ->havingRaw('ROUND(SUM(jed.debits) - SUM(jed.credits), 2) != 0')
                ->select(
                    'je.id',
                    'je.tanggal',
                    'je.source',
                    'je.comment',
                    DB::raw('SUM(jed.debits) as dept_debit'),
                    DB::raw('SUM(jed.credits) as dept_credit'),
                    DB::raw('SUM(jed.debits) - SUM(jed.credits) as dept_net')
                )
                ->orderBy('je.tanggal', 'desc');
        }

        $limit = (int)$request->get('limit', 2000);
        $journals = $query->limit($limit)->get();

        // Attach counterpart departments for each journal
        $journalIds = $journals->pluck('id')->toArray();
        $counterpartsByJournal = [];

        if (!empty($journalIds)) {
            $cQuery = DB::table('journal_entry_details as jed2')
                ->leftJoin('departemen_akuns as da2', 'da2.id', '=', 'jed2.departemen_akun_id')
                ->leftJoin('departements as d2', 'd2.id', '=', 'da2.departemen_id')
                ->whereIn('jed2.journal_entry_id', $journalIds);

            if ($isUnassigned) {
                $cQuery->whereNotNull('jed2.departemen_akun_id');
            } else {
                $cQuery->where(function ($w) use ($deptId) {
                    $w->whereNull('da2.departemen_id')
                      ->orWhere('da2.departemen_id', '!=', $deptId);
                });
            }

            $cRows = $cQuery->select(
                'jed2.journal_entry_id',
                DB::raw('COALESCE(d2.deskripsi, "Non-Departemen") as counterpart_name')
            )->distinct()->get();

            foreach ($cRows as $crow) {
                $counterpartsByJournal[$crow->journal_entry_id][] = $crow->counterpart_name;
            }
        }

        $formattedJournals = [];
        $totalNet = 0;
        foreach ($journals as $j) {
            $net = (float)$j->dept_net;
            $totalNet += $net;
            $formattedJournals[] = [
                'id' => $j->id,
                'tanggal' => Carbon::parse($j->tanggal)->format('d/m/Y'),
                'source' => $j->source ?? '-',
                'comment' => $j->comment ?? '-',
                'dept_debit' => (float)$j->dept_debit,
                'dept_credit' => (float)$j->dept_credit,
                'dept_net' => $net,
                'counterparts' => array_values(array_unique($counterpartsByJournal[$j->id] ?? []))
            ];
        }

        return response()->json([
            'status' => 'success',
            'departemen' => $deptName,
            'departemen_id' => $deptParam,
            'end_date' => $endDate,
            'total_rk' => $totalNet,
            'count' => count($formattedJournals),
            'journals' => $formattedJournals
        ]);
    }

    /**
     * Build the complete data array for Balance Sheet Departemen (Report & Export).
     */
    public function buildReportData(Request $request): array
    {
        $tanggalAkhir = $request->get('end_date', now()->format('Y-m-d'));
        $tanggalAwalTahun = Carbon::parse($tanggalAkhir)->startOfYear()->toDateString();
        $mode = $request->get('mode', 'matrix'); // 'matrix' or 'single'
        $selectedDeptId = $request->get('departemen_id');

        $showAccountNumber = $request->has('show_account_number') || $request->get('show_account_number') === 'show_account_number';
        $hideAccountWithZero = $request->has('hide_account_with_zero') || $request->get('hide_account_with_zero') === 'hide_account_with_zero';

        $siteTitle = Setting::where('key', 'site_title')->value('value') ?? 'RCA Internal';
        $siteLogo = Setting::where('key', 'logo')->value('value');

        $departments = Departement::orderBy('kode')->get();
        $deptIds = $departments->pluck('id')->toArray();
        $allColKeys = array_merge($deptIds, ['unassigned']);

        // Default department for single mode
        if ($mode === 'single') {
            if (!$selectedDeptId || !in_array((int)$selectedDeptId, $deptIds)) {
                $selectedDeptId = $deptIds[0] ?? null;
            } else {
                $selectedDeptId = (int)$selectedDeptId;
            }
            $singleDept = Departement::find($selectedDeptId);
        } else {
            $singleDept = null;
        }

        // 1. Bulk Balance Sheet Ending Balances (All Accounts x All Departments)
        $jedBalances = DB::table('journal_entry_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
            ->leftJoin('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')
            ->where('je.tanggal', '<=', $tanggalAkhir)
            ->select(
                'jed.kode_akun',
                'da.departemen_id',
                DB::raw('SUM(jed.debits) as tot_debit'),
                DB::raw('SUM(jed.credits) as tot_credit')
            )
            ->groupBy('jed.kode_akun', 'da.departemen_id')
            ->get();

        $bsMap = [];
        foreach ($jedBalances as $r) {
            $deptKey = $r->departemen_id !== null ? (int)$r->departemen_id : 'unassigned';
            $bsMap[$r->kode_akun][$deptKey] = [
                'debit' => (float)$r->tot_debit,
                'credit' => (float)$r->tot_credit
            ];
        }

        // 2. Bulk Nominal Movements for Laba Tahun Berjalan (excluding START NEW YEAR)
        $jedNominal = DB::table('journal_entry_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.kode_akun', '=', 'jed.kode_akun')
            ->leftJoin('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')
            ->whereBetween('je.tanggal', [$tanggalAwalTahun, $tanggalAkhir])
            ->where('je.source', '!=', 'START NEW YEAR')
            ->whereIn('coa.tipe_akun', ['Pendapatan', 'Beban'])
            ->select(
                'jed.kode_akun',
                'coa.tipe_akun',
                'coa.is_income_tax',
                'da.departemen_id',
                DB::raw('SUM(jed.debits) as tot_debit'),
                DB::raw('SUM(jed.credits) as tot_credit')
            )
            ->groupBy('jed.kode_akun', 'coa.tipe_akun', 'coa.is_income_tax', 'da.departemen_id')
            ->get();

        $pdpMap = [];
        $bbnMap = [];
        $pjkMap = [];

        foreach ($jedNominal as $r) {
            $deptKey = $r->departemen_id !== null ? (int)$r->departemen_id : 'unassigned';
            $deb = (float)$r->tot_debit;
            $crd = (float)$r->tot_credit;
            if ($r->is_income_tax == 1) {
                $pjkMap[$deptKey] = ($pjkMap[$deptKey] ?? 0) + ($deb - $crd);
            } elseif ($r->tipe_akun === 'Pendapatan') {
                $pdpMap[$deptKey] = ($pdpMap[$deptKey] ?? 0) + ($crd - $deb);
            } elseif ($r->tipe_akun === 'Beban') {
                $bbnMap[$deptKey] = ($bbnMap[$deptKey] ?? 0) + ($deb - $crd);
            }
        }

        $labaBerjalanPerDept = [];
        $totalLabaKonsolidasi = 0;
        foreach ($allColKeys as $k) {
            $p = $pdpMap[$k] ?? 0;
            $b = $bbnMap[$k] ?? 0;
            $t = $pjkMap[$k] ?? 0;
            $val = ($p - $b) - $t;
            $labaBerjalanPerDept[$k] = $val;
            $totalLabaKonsolidasi += $val;
        }
        $labaBerjalanPerDept['eliminasi'] = 0.0;
        $labaBerjalanPerDept['total'] = $totalLabaKonsolidasi;

        // 3. Chart of Accounts Tree (Aset, Kewajiban, Ekuitas)
        $accounts = ChartOfAccount::whereIn('tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas'])
            ->orderBy('kode_akun')
            ->get();

        // Precompute pure self-balances for each account and department
        $saldoSelfByKodeAndDept = [];
        foreach ($accounts as $a) {
            if ($a->level_akun === 'X') {
                continue;
            }
            $rowSum = 0;
            foreach ($allColKeys as $k) {
                $deb = $bsMap[$a->kode_akun][$k]['debit'] ?? 0;
                $crd = $bsMap[$a->kode_akun][$k]['credit'] ?? 0;
                $saldo = ($a->tipe_akun === 'Aset') ? ($deb - $crd) : ($crd - $deb);
                $saldoSelfByKodeAndDept[$a->kode_akun][$k] = $saldo;
                $rowSum += $saldo;
            }
            $saldoSelfByKodeAndDept[$a->kode_akun]['eliminasi'] = 0.0;
            $saldoSelfByKodeAndDept[$a->kode_akun]['total'] = $rowSum;
        }

        // Initialize grandTotals per Tipe
        $allKeysWithElimTotal = array_merge($allColKeys, ['eliminasi', 'total']);
        $grandTotals = [
            'Aset'      => array_fill_keys($allKeysWithElimTotal, 0.0),
            'Kewajiban' => array_fill_keys($allKeysWithElimTotal, 0.0),
            'Ekuitas'   => array_fill_keys($allKeysWithElimTotal, 0.0),
            'Pasiva'    => array_fill_keys($allKeysWithElimTotal, 0.0),
        ];

        // 4. Build Hierarchical Structure
        $neraca = ['Aset' => [], 'Kewajiban' => [], 'Ekuitas' => []];

        foreach ($accounts as $akun) {
            if ($akun->level_akun === 'X') {
                continue;
            }

            $selfBalances = $saldoSelfByKodeAndDept[$akun->kode_akun] ?? array_fill_keys($allKeysWithElimTotal, 0.0);

            $record = [
                'kode_akun'     => $akun->kode_akun,
                'nama_akun'     => $akun->nama_akun,
                'level_akun'    => $akun->level_akun,
                'tipe_akun'     => $akun->tipe_akun,
                'balances_self' => $selfBalances,
                'balances'      => $selfBalances,
                'has_child'     => false,
            ];

            // Parent ACCOUNT level: aggregate child SUB ACCOUNT balances
            if ($akun->level_akun === 'ACCOUNT') {
                $prefix = substr((string)$akun->kode_akun, 0, 4);

                $childAccounts = $accounts->filter(function ($sub) use ($prefix) {
                    return $sub->level_akun === 'SUB ACCOUNT' &&
                        \Illuminate\Support\Str::startsWith((string)$sub->kode_akun, $prefix);
                });

                $record['has_child'] = $childAccounts->isNotEmpty();

                $aggBalances = [];
                foreach ($allKeysWithElimTotal as $k) {
                    $agg = $selfBalances[$k] ?? 0;
                    foreach ($childAccounts as $sub) {
                        $agg += $saldoSelfByKodeAndDept[$sub->kode_akun][$k] ?? 0;
                    }
                    $aggBalances[$k] = $agg;
                }

                $record['balances'] = $aggBalances;
                $record['balances_agg'] = $aggBalances;

                // Add to Grand Totals (parent ACCOUNT only to prevent double counting)
                foreach ($allKeysWithElimTotal as $k) {
                    $grandTotals[$akun->tipe_akun][$k] += $aggBalances[$k] ?? 0;
                }
            }

            $neraca[$akun->tipe_akun][] = $record;
        }

        // 5. Compute Automatic R/K Balancing Pos per Department
        // RK_d = Total Aktiva_d - (Total Kewajiban_d + Total Ekuitas_Murni_d + Laba_Berjalan_d)
        $rkBalances = [];
        $sumRk = 0;
        foreach ($allColKeys as $k) {
            $asetK = $grandTotals['Aset'][$k] ?? 0;
            $kewK = $grandTotals['Kewajiban'][$k] ?? 0;
            $ekMurniK = $grandTotals['Ekuitas'][$k] ?? 0;
            $labaK = $labaBerjalanPerDept[$k] ?? 0;
            $pasivaSebK = $kewK + $ekMurniK + $labaK;
            $rkK = $asetK - $pasivaSebK;

            $rkBalances[$k] = $rkK;
            $sumRk += $rkK;
        }
        // Elimination nets reciprocal balances to exactly 0.00
        $rkBalances['eliminasi'] = -$sumRk;
        $rkBalances['total'] = 0.0;

        // 6. Append Laba Tahun Berjalan (Level X) and R/K to Ekuitas
        $akunLaba = ChartOfAccount::where('level_akun', 'X')->first();
        $neraca['Ekuitas'][] = [
            'kode_akun'     => $akunLaba ? $akunLaba->kode_akun : '3104000',
            'nama_akun'     => $akunLaba ? $akunLaba->nama_akun : 'LABA TAHUN BERJALAN',
            'level_akun'    => 'X',
            'tipe_akun'     => 'Ekuitas',
            'balances_self' => $labaBerjalanPerDept,
            'balances'      => $labaBerjalanPerDept,
            'has_child'     => false,
        ];

        $neraca['Ekuitas'][] = [
            'kode_akun'     => '3200999',
            'nama_akun'     => 'Rekening Koran (R/K) Kantor Pusat / Dana Antar Unit',
            'level_akun'    => 'RK',
            'tipe_akun'     => 'Ekuitas',
            'balances_self' => $rkBalances,
            'balances'      => $rkBalances,
            'has_child'     => false,
        ];

        // 7. Finalize Ekuitas & Pasiva Grand Totals
        foreach ($allKeysWithElimTotal as $k) {
            $grandTotals['Ekuitas'][$k] += ($labaBerjalanPerDept[$k] ?? 0) + ($rkBalances[$k] ?? 0);
            $grandTotals['Pasiva'][$k] = $grandTotals['Kewajiban'][$k] + $grandTotals['Ekuitas'][$k];
        }

        // 8. Prepare Single Mode Data (if single department selected)
        $singleData = [];
        if ($mode === 'single' && $selectedDeptId) {
            $singleData = [
                'grandTotalAset'      => $grandTotals['Aset'][$selectedDeptId] ?? 0,
                'grandTotalKewajiban' => $grandTotals['Kewajiban'][$selectedDeptId] ?? 0,
                'grandTotalEkuitas'   => $grandTotals['Ekuitas'][$selectedDeptId] ?? 0,
                'totalPasiva'         => $grandTotals['Pasiva'][$selectedDeptId] ?? 0,
                'labaTahunBerjalan'   => $labaBerjalanPerDept[$selectedDeptId] ?? 0,
                'rkSaldo'             => $rkBalances[$selectedDeptId] ?? 0,
                'isBalanced'          => abs(($grandTotals['Aset'][$selectedDeptId] ?? 0) - ($grandTotals['Pasiva'][$selectedDeptId] ?? 0)) < 0.01,
            ];
        }

        return compact(
            'mode',
            'tanggalAkhir',
            'tanggalAwalTahun',
            'showAccountNumber',
            'hideAccountWithZero',
            'departments',
            'selectedDeptId',
            'singleDept',
            'singleData',
            'neraca',
            'grandTotals',
            'rkBalances',
            'labaBerjalanPerDept',
            'siteTitle',
            'siteLogo'
        );
    }
}

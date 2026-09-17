<?php

namespace App\Http\Controllers;

use App\Departement;
use App\Exports\RekonsiliasiClosingExport;
use App\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RekonsiliasiClosingController extends Controller
{
    /**
     * Compute and assemble full reconciliation dataset between Closing Harian and Kas & Bank inflows.
     */
    private function getReconciliationData(Request $request): array
    {
        // 1. Determine Date Range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        } else {
            // Check latest transaction date from CH entries
            $maxDate = DB::table('journal_entries')->where('source', 'LIKE', 'CH-%')->max('tanggal');
            if ($maxDate) {
                // Default to the full month of the latest closing record
                $startDate = $request->input('start_date', Carbon::parse($maxDate)->startOfMonth()->toDateString());
                $endDate = $request->input('end_date', $maxDate);
            } else {
                $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
                $endDate = $request->input('end_date', Carbon::now()->toDateString());
            }
        }

        // 2. Department & Status Filters
        $departemenId = $request->input('departemen_id');
        $statusFilter = strtoupper($request->input('status', 'all'));

        // All active departments for filter select
        $departments = Departement::orderBy('kode')->get();

        // 3. Query Closing Omset (from journal_entries source LIKE 'CH-%')
        // Summing debits to 1101002 (Cash) and 1103001 (QRIS)
        $closingQuery = DB::table('journal_entries as je')
            ->join('journal_entry_details as jed', 'jed.journal_entry_id', '=', 'je.id')
            ->join('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')
            ->join('departements as d', 'd.id', '=', 'da.departemen_id')
            ->where('je.source', 'LIKE', 'CH-%')
            ->whereIn('jed.kode_akun', ['1101002', '1103001'])
            ->whereBetween('je.tanggal', [$startDate, $endDate]);

        if (!empty($departemenId) && $departemenId !== 'all') {
            $closingQuery->where('da.departemen_id', $departemenId);
        }

        $closingData = $closingQuery->select(
            'je.tanggal',
            'da.departemen_id',
            'd.kode as dept_kode',
            'd.deskripsi as dept_nama',
            DB::raw("SUM(CASE WHEN jed.kode_akun = '1101002' THEN jed.debits ELSE 0 END) as omset_cash"),
            DB::raw("SUM(CASE WHEN jed.kode_akun = '1103001' THEN jed.debits ELSE 0 END) as omset_qris"),
            DB::raw("COUNT(DISTINCT je.id) as closing_count")
        )
            ->groupBy('je.tanggal', 'da.departemen_id', 'd.kode', 'd.deskripsi')
            ->get();

        // 4. Query Kas & Bank Inflows (from non-closing vouchers: NOT source LIKE 'CH-%')
        // Summing credits to 1101002 (Cash Deposit) and 1103001 (QRIS Settlement)
        $kasQuery = DB::table('journal_entries as je')
            ->join('journal_entry_details as jed', 'jed.journal_entry_id', '=', 'je.id')
            ->join('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')
            ->join('departements as d', 'd.id', '=', 'da.departemen_id')
            ->where('je.source', 'NOT LIKE', 'CH-%')
            ->whereIn('jed.kode_akun', ['1101002', '1103001'])
            ->where('jed.credits', '>', 0)
            ->whereBetween('je.tanggal', [$startDate, $endDate]);

        if (!empty($departemenId) && $departemenId !== 'all') {
            $kasQuery->where('da.departemen_id', $departemenId);
        }

        $kasData = $kasQuery->select(
            'je.tanggal',
            'da.departemen_id',
            'd.kode as dept_kode',
            'd.deskripsi as dept_nama',
            DB::raw("SUM(CASE WHEN jed.kode_akun = '1101002' THEN jed.credits ELSE 0 END) as kas_masuk_cash"),
            DB::raw("SUM(CASE WHEN jed.kode_akun = '1103001' THEN jed.credits ELSE 0 END) as kas_masuk_qris"),
            DB::raw("COUNT(DISTINCT je.id) as kas_voucher_count")
        )
            ->groupBy('je.tanggal', 'da.departemen_id', 'd.kode', 'd.deskripsi')
            ->get();

        // 5. Merge Closing Omset and Kas Inflow into Map
        $reconciliationMap = [];

        // Insert Closing Omset rows
        foreach ($closingData as $c) {
            $key = $c->tanggal . '_' . $c->departemen_id;
            $reconciliationMap[$key] = [
                'tanggal' => $c->tanggal,
                'departemen_id' => $c->departemen_id,
                'dept_kode' => $c->dept_kode,
                'dept_nama' => $c->dept_nama,
                'omset_cash' => (float)$c->omset_cash,
                'omset_qris' => (float)$c->omset_qris,
                'total_omset' => (float)$c->omset_cash + (float)$c->omset_qris,
                'kas_masuk_cash' => 0.0,
                'kas_masuk_qris' => 0.0,
                'total_kas_masuk' => 0.0,
                'closing_count' => (int)$c->closing_count,
                'kas_voucher_count' => 0,
            ];
        }

        // Support standalone unposted closing_harians records if present
        if (DB::table('closing_harians')->count() > 0) {
            $chRows = DB::table('closing_harians as ch')
                ->leftJoin('unit_kerjas as uk', 'uk.id', '=', 'ch.unit_kerja_id')
                ->whereBetween('ch.tanggal', [$startDate, $endDate])
                ->select('ch.*', 'uk.kode_unit', 'uk.nama_unit')
                ->get();

            foreach ($chRows as $chRow) {
                $matchedDept = Departement::where('kode', $chRow->kode_unit)
                    ->orWhere('deskripsi', 'LIKE', '%' . $chRow->kode_unit . '%')
                    ->orWhere('deskripsi', 'LIKE', '%' . $chRow->nama_unit . '%')
                    ->first();
                $targetDeptId = $matchedDept ? $matchedDept->id : 1;

                if (!empty($departemenId) && $departemenId !== 'all' && (int)$departemenId !== (int)$targetDeptId) {
                    continue;
                }

                $key = $chRow->tanggal . '_' . $targetDeptId;
                if (!isset($reconciliationMap[$key]) || $reconciliationMap[$key]['closing_count'] === 0) {
                    if (!isset($reconciliationMap[$key])) {
                        $reconciliationMap[$key] = [
                            'tanggal' => $chRow->tanggal,
                            'departemen_id' => $targetDeptId,
                            'dept_kode' => $matchedDept ? $matchedDept->kode : '0000',
                            'dept_nama' => $matchedDept ? $matchedDept->deskripsi : ($chRow->nama_unit ?? 'Unit'),
                            'omset_cash' => 0.0,
                            'omset_qris' => 0.0,
                            'total_omset' => 0.0,
                            'kas_masuk_cash' => 0.0,
                            'kas_masuk_qris' => 0.0,
                            'total_kas_masuk' => 0.0,
                            'closing_count' => 0,
                            'kas_voucher_count' => 0,
                        ];
                    }
                    $reconciliationMap[$key]['omset_cash'] += (float)($chRow->total_cash ?? 0);
                    $reconciliationMap[$key]['omset_qris'] += (float)($chRow->total_qris ?? 0);
                    $reconciliationMap[$key]['total_omset'] = $reconciliationMap[$key]['omset_cash'] + $reconciliationMap[$key]['omset_qris'];
                    $reconciliationMap[$key]['closing_count'] += 1;
                }
            }
        }

        // Merge Kas & Bank Inflow rows
        foreach ($kasData as $k) {
            $key = $k->tanggal . '_' . $k->departemen_id;
            if (!isset($reconciliationMap[$key])) {
                $reconciliationMap[$key] = [
                    'tanggal' => $k->tanggal,
                    'departemen_id' => $k->departemen_id,
                    'dept_kode' => $k->dept_kode,
                    'dept_nama' => $k->dept_nama,
                    'omset_cash' => 0.0,
                    'omset_qris' => 0.0,
                    'total_omset' => 0.0,
                    'kas_masuk_cash' => 0.0,
                    'kas_masuk_qris' => 0.0,
                    'total_kas_masuk' => 0.0,
                    'closing_count' => 0,
                    'kas_voucher_count' => 0,
                ];
            }
            $reconciliationMap[$key]['kas_masuk_cash'] += (float)$k->kas_masuk_cash;
            $reconciliationMap[$key]['kas_masuk_qris'] += (float)$k->kas_masuk_qris;
            $reconciliationMap[$key]['total_kas_masuk'] = $reconciliationMap[$key]['kas_masuk_cash'] + $reconciliationMap[$key]['kas_masuk_qris'];
            $reconciliationMap[$key]['kas_voucher_count'] += (int)$k->kas_voucher_count;
        }

        // 6. Calculate Net Selisih and Status Categorization
        $allItems = [];
        $kpi = [
            'total_omset' => 0.0,
            'omset_cash' => 0.0,
            'omset_qris' => 0.0,
            'total_kas_masuk' => 0.0,
            'kas_cash' => 0.0,
            'kas_qris' => 0.0,
            'net_selisih' => 0.0,
            'count_match' => 0,
            'count_pending' => 0,
            'count_selisih' => 0,
            'count_total' => 0,
        ];

        foreach ($reconciliationMap as $item) {
            $selisih = $item['total_kas_masuk'] - $item['total_omset'];
            $item['selisih'] = $selisih;

            // Status rules:
            // PENDING: one side is zero
            // MATCH: abs(selisih) <= 1000
            // SELISIH: abs(selisih) > 1000
            if ($item['total_omset'] == 0 || $item['total_kas_masuk'] == 0) {
                $status = 'PENDING';
                $kpi['count_pending']++;
            } elseif (abs($selisih) <= 1000) {
                $status = 'MATCH';
                $kpi['count_match']++;
            } else {
                $status = 'SELISIH';
                $kpi['count_selisih']++;
            }

            $item['status'] = $status;

            // Update KPI totals
            $kpi['total_omset'] += $item['total_omset'];
            $kpi['omset_cash'] += $item['omset_cash'];
            $kpi['omset_qris'] += $item['omset_qris'];
            $kpi['total_kas_masuk'] += $item['total_kas_masuk'];
            $kpi['kas_cash'] += $item['kas_masuk_cash'];
            $kpi['kas_qris'] += $item['kas_masuk_qris'];
            $kpi['count_total']++;

            $allItems[] = $item;
        }

        $kpi['net_selisih'] = $kpi['total_kas_masuk'] - $kpi['total_omset'];

        // 7. Sort items: Descending date, then Department code ascending
        usort($allItems, function ($a, $b) {
            if ($a['tanggal'] === $b['tanggal']) {
                return strcmp($a['dept_kode'], $b['dept_kode']);
            }
            return strcmp($b['tanggal'], $a['tanggal']);
        });

        // 8. Filter by Status if specified
        $filteredItems = $allItems;
        if (!empty($statusFilter) && $statusFilter !== 'ALL') {
            $filteredItems = array_values(array_filter($allItems, function ($row) use ($statusFilter) {
                return $row['status'] === $statusFilter;
            }));
        }

        // Active Department Model if filtered
        $activeDepartment = (!empty($departemenId) && $departemenId !== 'all')
            ? Departement::find($departemenId)
            : null;

        $siteTitle = Setting::where('key', 'site_title')->value('value') ?? 'PT Rembulan Citra Abadi';

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'departemenId' => $departemenId,
            'activeDepartment' => $activeDepartment,
            'statusFilter' => $statusFilter,
            'departments' => $departments,
            'kpi' => $kpi,
            'items' => $filteredItems,
            'allItemsCount' => count($allItems),
            'siteTitle' => $siteTitle,
        ];
    }

    /**
     * Display the Rekonsiliasi Closing dashboard with KPI summary, filter, and table.
     */
    public function index(Request $request)
    {
        $data = $this->getReconciliationData($request);

        // Paginate table items (default 25 items per page)
        $perPage = (int)$request->input('per_page', 25);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $collection = collect($data['items']);
        $currentPageItems = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        $paginatedItems = new LengthAwarePaginator(
            $currentPageItems,
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        $data['paginatedItems'] = $paginatedItems;

        return view('rekonsiliasi_closing.index', $data);
    }

    /**
     * AJAX endpoint returning drilldown voucher line items for a date & department.
     */
    public function detail(Request $request)
    {
        $tanggal = $request->get('tanggal');
        $deptId = (int)$request->get('departemen_id');

        if (empty($tanggal) || empty($deptId)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tanggal dan departemen_id wajib diisi.',
            ], 400);
        }

        $dept = Departement::find($deptId);
        $deptName = $dept ? ($dept->kode . ' - ' . $dept->deskripsi) : "Departemen #{$deptId}";

        // 1. Fetch Closing Vouchers (CH-)
        $chVoucherIds = DB::table('journal_entries as je')
            ->join('journal_entry_details as jed', 'jed.journal_entry_id', '=', 'je.id')
            ->join('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')
            ->where('je.source', 'LIKE', 'CH-%')
            ->where('je.tanggal', $tanggal)
            ->where('da.departemen_id', $deptId)
            ->pluck('je.id')
            ->unique()
            ->toArray();

        $closingVouchers = [];
        $totalOmsetCash = 0.0;
        $totalOmsetQris = 0.0;

        if (!empty($chVoucherIds)) {
            $chRows = DB::table('journal_entries as je')
                ->join('journal_entry_details as jed', 'jed.journal_entry_id', '=', 'je.id')
                ->leftJoin('chart_of_accounts as coa', 'coa.kode_akun', '=', 'jed.kode_akun')
                ->whereIn('je.id', $chVoucherIds)
                ->select(
                    'je.id as je_id',
                    'je.source',
                    'je.tanggal',
                    'je.comment as je_comment',
                    'jed.id as jed_id',
                    'jed.kode_akun',
                    'coa.nama_akun',
                    'jed.debits',
                    'jed.credits',
                    'jed.comment as jed_comment'
                )
                ->orderBy('je.id')
                ->orderBy('jed.id')
                ->get();

            foreach ($chRows->groupBy('je_id') as $jeId => $rows) {
                $first = $rows->first();
                $items = [];
                $vCash = 0.0;
                $vQris = 0.0;

                foreach ($rows as $r) {
                    if ($r->kode_akun === '1101002') {
                        $vCash += (float)$r->debits;
                        $totalOmsetCash += (float)$r->debits;
                    }
                    if ($r->kode_akun === '1103001') {
                        $vQris += (float)$r->debits;
                        $totalOmsetQris += (float)$r->debits;
                    }
                    $items[] = [
                        'id' => $r->jed_id,
                        'kode_akun' => $r->kode_akun,
                        'nama_akun' => $r->nama_akun ?? '-',
                        'debits' => (float)$r->debits,
                        'credits' => (float)$r->credits,
                        'comment' => $r->jed_comment ?? '',
                    ];
                }

                $closingVouchers[] = [
                    'id' => $jeId,
                    'source' => $first->source,
                    'tanggal' => $first->tanggal,
                    'comment' => $first->je_comment ?? '',
                    'omset_cash' => $vCash,
                    'omset_qris' => $vQris,
                    'total_omset' => $vCash + $vQris,
                    'items' => $items,
                ];
            }
        }

        // 2. Fetch Kas & Bank Inflow Vouchers (non-CH)
        $kasVoucherIds = DB::table('journal_entries as je')
            ->join('journal_entry_details as jed', 'jed.journal_entry_id', '=', 'je.id')
            ->join('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')
            ->where('je.source', 'NOT LIKE', 'CH-%')
            ->where('je.tanggal', $tanggal)
            ->where('da.departemen_id', $deptId)
            ->whereIn('jed.kode_akun', ['1101002', '1103001'])
            ->where('jed.credits', '>', 0)
            ->pluck('je.id')
            ->unique()
            ->toArray();

        $kasVouchers = [];
        $totalKasCash = 0.0;
        $totalKasQris = 0.0;

        if (!empty($kasVoucherIds)) {
            $kasRows = DB::table('journal_entries as je')
                ->join('journal_entry_details as jed', 'jed.journal_entry_id', '=', 'je.id')
                ->leftJoin('chart_of_accounts as coa', 'coa.kode_akun', '=', 'jed.kode_akun')
                ->whereIn('je.id', $kasVoucherIds)
                ->select(
                    'je.id as je_id',
                    'je.source',
                    'je.tanggal',
                    'je.comment as je_comment',
                    'jed.id as jed_id',
                    'jed.kode_akun',
                    'coa.nama_akun',
                    'jed.debits',
                    'jed.credits',
                    'jed.comment as jed_comment'
                )
                ->orderBy('je.id')
                ->orderBy('jed.id')
                ->get();

            foreach ($kasRows->groupBy('je_id') as $jeId => $rows) {
                $first = $rows->first();
                $items = [];
                $vCash = 0.0;
                $vQris = 0.0;

                foreach ($rows as $r) {
                    if ($r->kode_akun === '1101002' && $r->credits > 0) {
                        $vCash += (float)$r->credits;
                        $totalKasCash += (float)$r->credits;
                    }
                    if ($r->kode_akun === '1103001' && $r->credits > 0) {
                        $vQris += (float)$r->credits;
                        $totalKasQris += (float)$r->credits;
                    }
                    $items[] = [
                        'id' => $r->jed_id,
                        'kode_akun' => $r->kode_akun,
                        'nama_akun' => $r->nama_akun ?? '-',
                        'debits' => (float)$r->debits,
                        'credits' => (float)$r->credits,
                        'comment' => $r->jed_comment ?? '',
                    ];
                }

                $kasVouchers[] = [
                    'id' => $jeId,
                    'source' => $first->source,
                    'tanggal' => $first->tanggal,
                    'comment' => $first->je_comment ?? '',
                    'kas_cash' => $vCash,
                    'kas_qris' => $vQris,
                    'total_kas' => $vCash + $vQris,
                    'items' => $items,
                ];
            }
        }

        $totalOmset = $totalOmsetCash + $totalOmsetQris;
        $totalKasMasuk = $totalKasCash + $totalKasQris;
        $selisih = $totalKasMasuk - $totalOmset;

        if ($totalOmset == 0 || $totalKasMasuk == 0) {
            $status = 'PENDING';
        } elseif (abs($selisih) <= 1000) {
            $status = 'MATCH';
        } else {
            $status = 'SELISIH';
        }

        return response()->json([
            'success' => true,
            'tanggal' => $tanggal,
            'tanggal_formatted' => Carbon::parse($tanggal)->translatedFormat('d F Y'),
            'departemen' => [
                'id' => $deptId,
                'kode' => $dept ? $dept->kode : '',
                'deskripsi' => $dept ? $dept->deskripsi : "Dept #{$deptId}",
                'nama' => $deptName,
            ],
            'summary' => [
                'omset_cash' => $totalOmsetCash,
                'omset_qris' => $totalOmsetQris,
                'total_omset' => $totalOmset,
                'kas_cash' => $totalKasCash,
                'kas_qris' => $totalKasQris,
                'total_kas_masuk' => $totalKasMasuk,
                'selisih' => $selisih,
                'status' => $status,
            ],
            'closing_vouchers' => $closingVouchers,
            'kas_bank_vouchers' => $kasVouchers,
        ]);
    }

    /**
     * Export the reconciliation report to Excel or PDF.
     */
    public function export(Request $request)
    {
        $format = strtolower($request->get('format', 'excel'));
        $data = $this->getReconciliationData($request);

        $startDateStr = Carbon::parse($data['startDate'])->format('Ymd');
        $endDateStr = Carbon::parse($data['endDate'])->format('Ymd');
        $deptSuffix = $data['activeDepartment'] ? '_' . $data['activeDepartment']->kode : '_all';

        if ($format === 'excel' || $format === 'xlsx') {
            $filename = "rekonsiliasi_closing_{$startDateStr}_{$endDateStr}{$deptSuffix}.xlsx";
            return Excel::download(new RekonsiliasiClosingExport($data), $filename);
        }

        if ($format === 'pdf') {
            $filename = "rekonsiliasi_closing_{$startDateStr}_{$endDateStr}{$deptSuffix}.pdf";
            $pdf = Pdf::loadView('rekonsiliasi_closing.export_pdf', $data);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download($filename);
        }

        return back()->with('error', "Format export '{$format}' tidak didukung. Pilih 'excel' atau 'pdf'.");
    }
}

<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;
use App\Exports\UnassignedWatchdogExport;
use App\JournalEntry;
use App\JournalEntryDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class UnassignedWatchdogController extends Controller
{
    /**
     * Display the Unassigned Watchdog control center and filtered transactions.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Retrieve filter parameters
        $startDate    = $request->get('start_date');
        $endDate      = $request->get('end_date');
        $kategori     = $request->get('kategori', 'all'); // 'all', 'real', 'nominal'
        $tipeAkun     = $request->get('tipe_akun', 'all');
        $sourcePrefix = $request->get('source_prefix', 'all');
        $search       = $request->get('search');
        $perPage      = (int) $request->get('per_page', 50);
        if ($perPage <= 0 || $perPage > 500) {
            $perPage = 50;
        }

        // Build query for journal entry details without department relation
        $query = JournalEntryDetail::with(['journalEntry', 'chartOfAccount'])
            ->select('journal_entry_details.*')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id')
            ->join('chart_of_accounts', 'chart_of_accounts.kode_akun', '=', 'journal_entry_details.kode_akun')
            ->whereNull('journal_entry_details.departemen_akun_id');

        // Filter by date range
        if (!empty($startDate)) {
            $query->whereDate('journal_entries.tanggal', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('journal_entries.tanggal', '<=', $endDate);
        }

        // Filter by category: Real (Neraca) vs Nominal (Laba Rugi)
        if ($kategori === 'real') {
            $query->whereIn('chart_of_accounts.tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas']);
        } elseif ($kategori === 'nominal') {
            $query->whereIn('chart_of_accounts.tipe_akun', ['Pendapatan', 'Beban']);
        }

        // Filter by specific tipe_akun
        if (!empty($tipeAkun) && $tipeAkun !== 'all') {
            $query->where('chart_of_accounts.tipe_akun', $tipeAkun);
        }

        // Filter by voucher source prefix (e.g. KASIMAN, KASSQP, REFUND, etc.)
        if (!empty($sourcePrefix) && $sourcePrefix !== 'all') {
            $query->where('journal_entries.source', 'LIKE', $sourcePrefix . '%');
        }

        // Filter by search keyword
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('journal_entry_details.kode_akun', 'LIKE', "%{$search}%")
                    ->orWhere('chart_of_accounts.nama_akun', 'LIKE', "%{$search}%")
                    ->orWhere('journal_entries.source', 'LIKE', "%{$search}%")
                    ->orWhere('journal_entries.comment', 'LIKE', "%{$search}%")
                    ->orWhere('journal_entry_details.comment', 'LIKE', "%{$search}%");
            });
        }

        // Aggregations for current filter scope
        $filteredCount = (clone $query)->count();
        $filteredTotalDebit = (clone $query)->sum('journal_entry_details.debits');
        $filteredTotalCredit = (clone $query)->sum('journal_entry_details.credits');

        // Order and paginate results
        $details = $query->orderBy('journal_entries.tanggal', 'desc')
            ->orderBy('journal_entries.source', 'asc')
            ->orderBy('journal_entry_details.id', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        // Global KPI counts (regardless of active filters)
        $kpis = $this->getKpiCounts();

        // Reference departments for assignment dropdown
        $departments = Departement::orderBy('kode')->get();

        // Distinct voucher source prefixes for filter dropdown
        $sourcePrefixes = DB::table('journal_entry_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
            ->whereNull('jed.departemen_akun_id')
            ->select(DB::raw("SUBSTRING_INDEX(je.source, '-', 1) as prefix"), DB::raw('count(*) as count'))
            ->groupBy('prefix')
            ->orderBy('prefix')
            ->get();

        $tipeAkunList = ['Aset', 'Kewajiban', 'Ekuitas', 'Pendapatan', 'Beban'];

        // Optional JSON response for AJAX dashboard polling
        if ($request->ajax() && $request->get('format') === 'json') {
            return response()->json([
                'success'             => true,
                'kpis'                => $kpis,
                'filteredCount'       => $filteredCount,
                'filteredTotalDebit'  => (float) $filteredTotalDebit,
                'filteredTotalCredit' => (float) $filteredTotalCredit,
            ]);
        }

        return view('unassigned_watchdog.index', compact(
            'details',
            'departments',
            'kpis',
            'filteredCount',
            'filteredTotalDebit',
            'filteredTotalCredit',
            'startDate',
            'endDate',
            'kategori',
            'tipeAkun',
            'tipeAkunList',
            'sourcePrefix',
            'sourcePrefixes',
            'search',
            'perPage'
        ));
    }

    /**
     * Quick Action: Assign department to a single journal entry detail row.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function assign(Request $request)
    {
        $request->validate([
            'detail_id'     => 'required|integer|exists:journal_entry_details,id',
            'departemen_id' => 'required|integer|exists:departements,id',
        ]);

        DB::beginTransaction();
        try {
            $detail = JournalEntryDetail::findOrFail($request->detail_id);
            $account = ChartOfAccount::where('kode_akun', $detail->kode_akun)->firstOrFail();
            $dept = Departement::findOrFail($request->departemen_id);

            // Ensure pivot link exists between COA and department
            $da = DepartemenAkun::firstOrCreate([
                'akun_id'       => $account->id,
                'departemen_id' => $dept->id,
            ]);

            // Assign department to the journal detail
            $detail->departemen_akun_id = $da->id;
            $detail->save();

            DB::commit();

            $kpis = $this->getKpiCounts();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'            => true,
                    'message'            => "Detail transaksi #{$detail->id} ({$detail->kode_akun} - {$account->nama_akun}) berhasil ditugaskan ke {$dept->deskripsi}.",
                    'detail_id'          => $detail->id,
                    'departemen_id'      => $dept->id,
                    'departemen_nama'    => $dept->deskripsi,
                    'departemen_kode'    => $dept->kode,
                    'departemen_akun_id' => $da->id,
                    'kpis'               => $kpis,
                ]);
            }

            return redirect()->back()->with('success', "Transaksi #{$detail->id} berhasil ditugaskan ke departemen {$dept->deskripsi}.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[UnassignedWatchdog] Assign error: ' . $e->getMessage(), [
                'detail_id'     => $request->detail_id,
                'departemen_id' => $request->departemen_id,
                'trace'         => $e->getTraceAsString(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menugaskan departemen: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menugaskan departemen: ' . $e->getMessage());
        }
    }

    /**
     * Bulk Action: Assign department to multiple journal entry detail rows in a single transaction.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function assignBulk(Request $request)
    {
        $request->validate([
            'detail_ids'    => 'required|array|min:1',
            'detail_ids.*'  => 'integer|exists:journal_entry_details,id',
            'departemen_id' => 'required|integer|exists:departements,id',
        ]);

        $detailIds = $request->input('detail_ids', []);
        $departemenId = $request->input('departemen_id');

        DB::beginTransaction();
        try {
            $dept = Departement::findOrFail($departemenId);

            // In-memory cache to prevent redundant queries
            $accountCache = [];
            $daCache = [];
            $assignedIds = [];

            $details = JournalEntryDetail::whereIn('id', $detailIds)
                ->whereNull('departemen_akun_id')
                ->get();

            foreach ($details as $detail) {
                if (!isset($accountCache[$detail->kode_akun])) {
                    $account = ChartOfAccount::where('kode_akun', $detail->kode_akun)->first();
                    $accountCache[$detail->kode_akun] = $account;
                } else {
                    $account = $accountCache[$detail->kode_akun];
                }

                if (!$account) {
                    continue;
                }

                if (!isset($daCache[$account->id])) {
                    $da = DepartemenAkun::firstOrCreate([
                        'akun_id'       => $account->id,
                        'departemen_id' => $dept->id,
                    ]);
                    $daCache[$account->id] = $da->id;
                }

                $detail->departemen_akun_id = $daCache[$account->id];
                $detail->save();
                $assignedIds[] = $detail->id;
            }

            DB::commit();

            $kpis = $this->getKpiCounts();
            $count = count($assignedIds);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'         => true,
                    'message'         => "Sebanyak {$count} transaksi berhasil ditugaskan ke departemen {$dept->deskripsi}.",
                    'assigned_count'  => $count,
                    'assigned_ids'    => $assignedIds,
                    'departemen_id'   => $dept->id,
                    'departemen_nama' => $dept->deskripsi,
                    'departemen_kode' => $dept->kode,
                    'kpis'            => $kpis,
                ]);
            }

            return redirect()->back()->with('success', "Sebanyak {$count} transaksi berhasil ditugaskan ke departemen {$dept->deskripsi}.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[UnassignedWatchdog] AssignBulk error: ' . $e->getMessage(), [
                'detail_ids'    => $detailIds,
                'departemen_id' => $departemenId,
                'trace'         => $e->getTraceAsString(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal melakukan bulk assign: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal melakukan bulk assign: ' . $e->getMessage());
        }
    }

    /**
     * Export unassigned watchdog transactions to Excel (.xlsx) or PDF (Landscape A4).
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $format = strtolower($request->get('format', 'excel'));

        $startDate    = $request->get('start_date');
        $endDate      = $request->get('end_date');
        $kategori     = $request->get('kategori', 'all');
        $tipeAkun     = $request->get('tipe_akun', 'all');
        $sourcePrefix = $request->get('source_prefix', 'all');
        $search       = $request->get('search');

        // Query unassigned details without pagination for export
        $query = JournalEntryDetail::with(['journalEntry', 'chartOfAccount'])
            ->select('journal_entry_details.*')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id')
            ->join('chart_of_accounts', 'chart_of_accounts.kode_akun', '=', 'journal_entry_details.kode_akun')
            ->whereNull('journal_entry_details.departemen_akun_id');

        if (!empty($startDate)) {
            $query->whereDate('journal_entries.tanggal', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('journal_entries.tanggal', '<=', $endDate);
        }
        if ($kategori === 'real') {
            $query->whereIn('chart_of_accounts.tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas']);
        } elseif ($kategori === 'nominal') {
            $query->whereIn('chart_of_accounts.tipe_akun', ['Pendapatan', 'Beban']);
        }
        if (!empty($tipeAkun) && $tipeAkun !== 'all') {
            $query->where('chart_of_accounts.tipe_akun', $tipeAkun);
        }
        if (!empty($sourcePrefix) && $sourcePrefix !== 'all') {
            $query->where('journal_entries.source', 'LIKE', $sourcePrefix . '%');
        }
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('journal_entry_details.kode_akun', 'LIKE', "%{$search}%")
                    ->orWhere('chart_of_accounts.nama_akun', 'LIKE', "%{$search}%")
                    ->orWhere('journal_entries.source', 'LIKE', "%{$search}%")
                    ->orWhere('journal_entries.comment', 'LIKE', "%{$search}%")
                    ->orWhere('journal_entry_details.comment', 'LIKE', "%{$search}%");
            });
        }

        $details = $query->orderBy('journal_entries.tanggal', 'desc')
            ->orderBy('journal_entries.source', 'asc')
            ->orderBy('journal_entry_details.id', 'asc')
            ->get();

        $totalDebit = $details->sum('debits');
        $totalCredit = $details->sum('credits');
        $kpis = $this->getKpiCounts();

        $exportData = [
            'details'      => $details,
            'kpis'         => $kpis,
            'totalDebit'   => $totalDebit,
            'totalCredit'  => $totalCredit,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'kategori'     => $kategori,
            'tipeAkun'     => $tipeAkun,
            'sourcePrefix' => $sourcePrefix,
            'search'       => $search,
            'exportDate'   => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        $timestamp = Carbon::now()->format('Ymd_His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('unassigned_watchdog.export_pdf', $exportData);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download("unassigned_watchdog_{$timestamp}.pdf");
        }

        return Excel::download(new UnassignedWatchdogExport($exportData), "unassigned_watchdog_{$timestamp}.xlsx");
    }

    /**
     * Compute KPI summary counts for all unassigned transactions.
     *
     * @return array
     */
    public function getKpiCounts()
    {
        $baseQuery = DB::table('journal_entry_details as jed')
            ->join('chart_of_accounts as coa', 'coa.kode_akun', '=', 'jed.kode_akun')
            ->whereNull('jed.departemen_akun_id');

        $totalUnassigned = (clone $baseQuery)->count();

        $realCount = (clone $baseQuery)
            ->whereIn('coa.tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas'])
            ->count();

        $nominalCount = (clone $baseQuery)
            ->whereIn('coa.tipe_akun', ['Pendapatan', 'Beban'])
            ->count();

        $totalDebit = (clone $baseQuery)->sum('jed.debits');
        $totalCredit = (clone $baseQuery)->sum('jed.credits');

        return [
            'total'        => (int) $totalUnassigned,
            'real'         => (int) $realCount,
            'nominal'      => (int) $nominalCount,
            'total_debit'  => (float) $totalDebit,
            'total_credit' => (float) $totalCredit,
        ];
    }
}

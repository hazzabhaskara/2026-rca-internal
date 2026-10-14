<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;
use App\DepartmentAssignmentLog;
use App\Exports\AssignedWatchdogExport;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\Services\AnomalyDetectionEngine;
use App\Services\DepartmentAssignmentService;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AssignedWatchdogController extends Controller
{
    /**
     * @var DepartmentAssignmentService
     */
    protected $departmentAssignmentService;

    /**
     * @var AnomalyDetectionEngine
     */
    protected $anomalyDetectionEngine;

    /**
     * AssignedWatchdogController constructor.
     *
     * @param DepartmentAssignmentService $departmentAssignmentService
     * @param AnomalyDetectionEngine $anomalyDetectionEngine
     */
    public function __construct(
        DepartmentAssignmentService $departmentAssignmentService,
        AnomalyDetectionEngine $anomalyDetectionEngine
    ) {
        $this->departmentAssignmentService = $departmentAssignmentService;
        $this->anomalyDetectionEngine = $anomalyDetectionEngine;
    }

    /**
     * Display the Assigned Watchdog Control Center with filters, KPIs, and department pills.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        if (!auth()->user()->can('assigned_watchdog.access')) {
            abort(403, 'Unauthorized access to Assigned Watchdog.');
        }

        $startDate      = $request->get('start_date');
        $endDate        = $request->get('end_date');
        $departemenId   = $request->get('departemen_id');
        $kategori       = $request->get('kategori', 'all'); // 'all', 'real', 'nominal'
        $tipeAkun       = $request->get('tipe_akun', 'all');
        $sourcePrefix   = $request->get('source_prefix', 'all');
        $search         = $request->get('search');
        $anomaliesOnly  = $request->boolean('anomalies_only') || $request->get('anomalies_only') == '1';
        $perPage        = (int) $request->get('per_page', 50);
        if ($perPage <= 0 || $perPage > 500) {
            $perPage = 50;
        }

        // Base Query: assigned journal entry details
        $query = JournalEntryDetail::whereNotNull('departemen_akun_id')
            ->with(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);

        // Filter: Department
        if (!empty($departemenId)) {
            $query->whereHas('departemenAkun', function ($q) use ($departemenId) {
                $q->where('departemen_id', $departemenId);
            });
        }

        // Filter: Date range
        if (!empty($startDate)) {
            $query->whereHas('journalEntry', function ($q) use ($startDate) {
                $q->whereDate('tanggal', '>=', $startDate);
            });
        }
        if (!empty($endDate)) {
            $query->whereHas('journalEntry', function ($q) use ($endDate) {
                $q->whereDate('tanggal', '<=', $endDate);
            });
        }

        // Filter: Category Real vs Nominal
        if ($kategori === 'real') {
            $query->whereHas('chartOfAccount', function ($q) {
                $q->whereIn('tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas']);
            });
        } elseif ($kategori === 'nominal') {
            $query->whereHas('chartOfAccount', function ($q) {
                $q->whereIn('tipe_akun', ['Pendapatan', 'Beban']);
            });
        }

        // Filter: Tipe Akun
        if (!empty($tipeAkun) && $tipeAkun !== 'all') {
            $query->whereHas('chartOfAccount', function ($q) use ($tipeAkun) {
                $q->where('tipe_akun', $tipeAkun);
            });
        }

        // Filter: Source prefix
        if (!empty($sourcePrefix) && $sourcePrefix !== 'all') {
            $query->whereHas('journalEntry', function ($q) use ($sourcePrefix) {
                $q->where('source', 'LIKE', $sourcePrefix . '%');
            });
        }

        // Filter: Search Keyword
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_akun', 'LIKE', "%{$search}%")
                    ->orWhereHas('chartOfAccount', function ($coaQ) use ($search) {
                        $coaQ->where('nama_akun', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('journalEntry', function ($jeQ) use ($search) {
                        $jeQ->where('source', 'LIKE', "%{$search}%")
                            ->orWhere('comment', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('comment', 'LIKE', "%{$search}%");
            });
        }

        // Filter: Anomalies Only
        if ($anomaliesOnly) {
            $this->anomalyDetectionEngine->applyAnomalyScope($query, true);
        }

        // KPI Calculations
        $totalAssigned = (clone $query)->count();
        $realAccounts = (clone $query)->whereHas('chartOfAccount', function ($q) {
            $q->whereIn('tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas']);
        })->count();
        $nominalAccounts = $totalAssigned - $realAccounts;
        $totalDebit = (float) (clone $query)->sum('debits');
        $totalCredit = (float) (clone $query)->sum('credits');

        // Total anomalies across the current filter scope
        $totalAnomalies = $this->anomalyDetectionEngine->calculateAnomalyCounts($request->all());

        $kpis = [
            'total'            => $totalAssigned,
            'total_assigned'   => $totalAssigned,
            'total_anomalies'  => $totalAnomalies,
            'real_accounts'    => $realAccounts,
            'total_real'       => $realAccounts,
            'nominal_accounts' => $nominalAccounts,
            'total_nominal'    => $nominalAccounts,
            'total_debit'      => $totalDebit,
            'total_credit'     => $totalCredit,
        ];

        // Department Quick-Filter Pills
        // The pills show counts per department under all other active filters (except departemen_id itself)
        $pillsBaseQuery = JournalEntryDetail::whereNotNull('departemen_akun_id');
        if (!empty($startDate)) {
            $pillsBaseQuery->whereHas('journalEntry', function ($q) use ($startDate) {
                $q->whereDate('tanggal', '>=', $startDate);
            });
        }
        if (!empty($endDate)) {
            $pillsBaseQuery->whereHas('journalEntry', function ($q) use ($endDate) {
                $q->whereDate('tanggal', '<=', $endDate);
            });
        }
        if ($kategori === 'real') {
            $pillsBaseQuery->whereHas('chartOfAccount', function ($q) {
                $q->whereIn('tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas']);
            });
        } elseif ($kategori === 'nominal') {
            $pillsBaseQuery->whereHas('chartOfAccount', function ($q) {
                $q->whereIn('tipe_akun', ['Pendapatan', 'Beban']);
            });
        }
        if (!empty($tipeAkun) && $tipeAkun !== 'all') {
            $pillsBaseQuery->whereHas('chartOfAccount', function ($q) use ($tipeAkun) {
                $q->where('tipe_akun', $tipeAkun);
            });
        }
        if (!empty($sourcePrefix) && $sourcePrefix !== 'all') {
            $pillsBaseQuery->whereHas('journalEntry', function ($q) use ($sourcePrefix) {
                $q->where('source', 'LIKE', $sourcePrefix . '%');
            });
        }
        if ($anomaliesOnly) {
            $this->anomalyDetectionEngine->applyAnomalyScope($pillsBaseQuery, true);
        }

        $activeDepts = Departement::where('status', 1)->orderBy('kode')->get();
        if ($activeDepts->count() < 9) {
            $activeDepts = Departement::orderBy('kode')->get();
        }

        $deptPills = [];
        foreach ($activeDepts as $dept) {
            $count = (clone $pillsBaseQuery)->whereHas('departemenAkun', function ($q) use ($dept) {
                $q->where('departemen_id', $dept->id);
            })->count();

            $deptPills[] = [
                'id'        => $dept->id,
                'kode'      => $dept->kode,
                'nama'      => $dept->deskripsi,
                'deskripsi' => $dept->deskripsi,
                'count'     => $count,
            ];
        }

        // Paginate slice
        $details = $query->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Attach anomaly detection & closed period status for paginated slice
        foreach ($details as $detail) {
            $entryDate = $detail->journalEntry ? $detail->journalEntry->tanggal : null;
            $detail->is_closed_period = $entryDate ? $this->departmentAssignmentService->isPeriodClosed($entryDate) : false;
            $detail->anomaly = $this->anomalyDetectionEngine->detectAnomalies($detail);
        }

        // Additional View Data
        $departments = Departement::orderBy('kode')->get();
        $tipeAkunList = ['Aset', 'Kewajiban', 'Ekuitas', 'Pendapatan', 'Beban'];

        $sourcePrefixes = DB::table('journal_entry_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
            ->whereNotNull('jed.departemen_akun_id')
            ->select(DB::raw("SUBSTRING_INDEX(je.source, '-', 1) as prefix"), DB::raw('count(*) as count'))
            ->groupBy('prefix')
            ->orderBy('prefix')
            ->get();

        $filteredCount       = $details->total();
        $filteredTotalDebit  = $totalDebit;
        $filteredTotalCredit = $totalCredit;
        $selectedDeptId      = $departemenId;

        if ($request->ajax() && $request->get('format') === 'json') {
            return response()->json([
                'status'              => 'success',
                'kpis'                => $kpis,
                'deptPills'           => $deptPills,
                'filteredCount'       => $filteredCount,
                'filteredTotalDebit'  => $filteredTotalDebit,
                'filteredTotalCredit' => $filteredTotalCredit,
            ]);
        }

        return view('assigned_watchdog.index', compact(
            'details',
            'kpis',
            'deptPills',
            'departments',
            'tipeAkunList',
            'sourcePrefixes',
            'startDate',
            'endDate',
            'kategori',
            'tipeAkun',
            'sourcePrefix',
            'search',
            'selectedDeptId',
            'anomaliesOnly',
            'filteredCount',
            'filteredTotalDebit',
            'filteredTotalCredit',
            'perPage'
        ));
    }

    /**
     * Reassign a single journal entry detail to a new department.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reassign(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!auth()->user()->can('assigned_watchdog.update')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'detail_id'     => 'required|integer|exists:journal_entry_details,id',
            'departemen_id' => 'required|integer|exists:departements,id',
            'reason'        => 'required|string',
        ]);

        if (trim($request->reason) === '') {
            return response()->json(['message' => 'Reason required'], 422);
        }

        try {
            $result = $this->departmentAssignmentService->reassign(
                (int) $request->detail_id,
                (int) $request->departemen_id,
                $request->reason,
                auth()->user(),
                $request->boolean('override_closed')
            );

            return response()->json([
                'status'        => 'success',
                'message'       => $result['message'],
                'detail'        => $result,
                'departemen_id' => $result['new_departemen_id'],
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            Log::error("AssignedWatchdogController@reassign error: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Detach (unassign) a single journal entry detail to NULL department.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unassign(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!auth()->user()->can('assigned_watchdog.update')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'detail_id' => 'required|integer|exists:journal_entry_details,id',
            'reason'    => 'required|string',
        ]);

        if (trim($request->reason) === '') {
            return response()->json(['message' => 'Reason required'], 422);
        }

        try {
            $result = $this->departmentAssignmentService->unassign(
                (int) $request->detail_id,
                $request->reason,
                auth()->user(),
                $request->boolean('override_closed')
            );

            return response()->json([
                'status'  => 'success',
                'message' => $result['message'],
                'detail'  => $result,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            Log::error("AssignedWatchdogController@unassign error: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Atomically reassign multiple journal entry details in bulk.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reassignBulk(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!auth()->user()->can('assigned_watchdog.update')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'detail_ids'    => 'required|array|min:1',
            'departemen_id' => 'required|integer|exists:departements,id',
            'reason'        => 'required|string',
        ]);

        if (trim($request->reason) === '') {
            return response()->json(['message' => 'Reason required'], 422);
        }

        try {
            $result = $this->departmentAssignmentService->reassignBulk(
                $request->detail_ids,
                (int) $request->departemen_id,
                $request->reason,
                auth()->user(),
                $request->boolean('override_closed')
            );

            return response()->json([
                'status'        => 'success',
                'updated_count' => $result['reassigned_count'] ?? count($request->detail_ids),
                'count'         => $result['reassigned_count'] ?? count($request->detail_ids),
                'message'       => $result['message'],
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            Log::error("AssignedWatchdogController@reassignBulk error: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Atomically unassign multiple journal entry details in bulk.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unassignBulk(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!auth()->user()->can('assigned_watchdog.update')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'detail_ids' => 'required|array|min:1',
            'reason'     => 'required|string',
        ]);

        if (trim($request->reason) === '') {
            return response()->json(['message' => 'Reason required'], 422);
        }

        try {
            $result = $this->departmentAssignmentService->unassignBulk(
                $request->detail_ids,
                $request->reason,
                auth()->user(),
                $request->boolean('override_closed')
            );

            return response()->json([
                'status'        => 'success',
                'updated_count' => $result['unassigned_count'] ?? count($request->detail_ids),
                'count'         => $result['unassigned_count'] ?? count($request->detail_ids),
                'message'       => $result['message'],
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            Log::error("AssignedWatchdogController@unassignBulk error: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Retrieve audit history for a specific journal entry detail.
     *
     * @param Request $request
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function auditHistory(Request $request, $id)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!auth()->user()->can('assigned_watchdog.access')) {
            abort(403, 'Unauthorized access to audit history.');
        }

        $detailId = (int) $id;
        $detail = JournalEntryDetail::with(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount'])->findOrFail($detailId);

        $logs = DepartmentAssignmentLog::with(['user', 'oldDepartemen', 'newDepartemen'])
            ->where('journal_entry_detail_id', $detailId)
            ->orderBy('id', 'desc')
            ->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'detail' => $detail,
                'logs'   => $logs,
                'html'   => view('assigned_watchdog.audit_modal', compact('detail', 'logs'))->render(),
            ]);
        }

        return view('assigned_watchdog.audit_modal', compact('detail', 'logs'));
    }

    /**
     * Display centralized audit trail view for all department assignment adjustments.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function auditLogs(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        if (!auth()->user()->can('assigned_watchdog.access')) {
            abort(403, 'Unauthorized access to audit logs.');
        }

        $query = DepartmentAssignmentLog::with([
            'user',
            'oldDepartemen',
            'newDepartemen',
            'journalEntryDetail.chartOfAccount',
            'journalEntry'
        ]);

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('departemen_id')) {
            $deptId = $request->departemen_id;
            $query->where(function ($q) use ($deptId) {
                $q->where('old_departemen_id', $deptId)
                  ->orWhere('new_departemen_id', $deptId);
            });
        }
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        $logs = $query->orderBy('id', 'desc')->paginate(50)->withQueryString();
        $departments = Departement::orderBy('kode')->get();
        $users = User::orderBy('name')->get();

        return view('assigned_watchdog.audit_logs', compact('logs', 'departments', 'users'));
    }

    /**
     * Export assigned transactions to Excel or PDF.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        if (!auth()->user()->can('assigned_watchdog.access')) {
            abort(403, 'Unauthorized access to export.');
        }

        $format         = strtolower($request->get('format', 'excel'));
        $startDate      = $request->get('start_date');
        $endDate        = $request->get('end_date');
        $departemenId   = $request->get('departemen_id');
        $kategori       = $request->get('kategori', 'all');
        $tipeAkun       = $request->get('tipe_akun', 'all');
        $sourcePrefix   = $request->get('source_prefix', 'all');
        $search         = $request->get('search');
        $anomaliesOnly  = $request->boolean('anomalies_only') || $request->get('anomalies_only') == '1';

        $query = JournalEntryDetail::whereNotNull('departemen_akun_id')
            ->with(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);

        // Filter: Department
        if (!empty($departemenId)) {
            $query->whereHas('departemenAkun', function ($q) use ($departemenId) {
                $q->where('departemen_id', $departemenId);
            });
        }

        // Filter: Date range
        if (!empty($startDate)) {
            $query->whereHas('journalEntry', function ($q) use ($startDate) {
                $q->whereDate('tanggal', '>=', $startDate);
            });
        }
        if (!empty($endDate)) {
            $query->whereHas('journalEntry', function ($q) use ($endDate) {
                $q->whereDate('tanggal', '<=', $endDate);
            });
        }

        // Filter: Category Real vs Nominal
        if ($kategori === 'real') {
            $query->whereHas('chartOfAccount', function ($q) {
                $q->whereIn('tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas']);
            });
        } elseif ($kategori === 'nominal') {
            $query->whereHas('chartOfAccount', function ($q) {
                $q->whereIn('tipe_akun', ['Pendapatan', 'Beban']);
            });
        }

        // Filter: Tipe Akun
        if (!empty($tipeAkun) && $tipeAkun !== 'all') {
            $query->whereHas('chartOfAccount', function ($q) use ($tipeAkun) {
                $q->where('tipe_akun', $tipeAkun);
            });
        }

        // Filter: Source prefix
        if (!empty($sourcePrefix) && $sourcePrefix !== 'all') {
            $query->whereHas('journalEntry', function ($q) use ($sourcePrefix) {
                $q->where('source', 'LIKE', $sourcePrefix . '%');
            });
        }

        // Filter: Search Keyword
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_akun', 'LIKE', "%{$search}%")
                    ->orWhereHas('chartOfAccount', function ($coaQ) use ($search) {
                        $coaQ->where('nama_akun', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('journalEntry', function ($jeQ) use ($search) {
                        $jeQ->where('source', 'LIKE', "%{$search}%")
                            ->orWhere('comment', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('comment', 'LIKE', "%{$search}%");
            });
        }

        // Filter: Anomalies Only
        if ($anomaliesOnly) {
            $this->anomalyDetectionEngine->applyAnomalyScope($query, true);
        }

        // KPI Calculations across the full filtered scope
        $totalDebit = (float) (clone $query)->sum('debits');
        $totalCredit = (float) (clone $query)->sum('credits');
        $totalAssigned = (clone $query)->count();
        $totalAnomalies = $this->anomalyDetectionEngine->calculateAnomalyCounts($request->all());

        $kpis = [
            'total_assigned'  => $totalAssigned,
            'total'           => $totalAssigned,
            'total_anomalies' => $totalAnomalies,
            'total_debit'     => $totalDebit,
            'total_credit'    => $totalCredit,
        ];

        $departemenNama = null;
        if (!empty($departemenId)) {
            $dept = Departement::find($departemenId);
            $departemenNama = $dept ? ($dept->deskripsi ?? $dept->nama) : null;
        }

        // Fetch details with ordering and memory safety limit
        $defaultLimit = $format === 'pdf' ? 200 : 500;
        $limit = $request->has('limit')
            ? ($request->get('limit') === 'all' ? null : (int) $request->get('limit'))
            : $defaultLimit;

        $orderedQuery = $query->orderBy('id', 'desc');
        if ($limit !== null && $limit > 0) {
            $orderedQuery->limit($limit);
        }
        $details = $orderedQuery->get();

        // Batch precompute voucher sibling department counts to eliminate N+1 queries during anomaly detection
        $voucherIds = $details->pluck('journal_entry_id')->filter()->unique()->values()->all();
        $voucherDeptCounts = [];
        if (!empty($voucherIds)) {
            $siblingRows = DB::table('journal_entry_details as jed')
                ->join('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')
                ->whereIn('jed.journal_entry_id', $voucherIds)
                ->whereNotNull('jed.departemen_akun_id')
                ->select('jed.journal_entry_id', 'da.departemen_id', DB::raw('COUNT(*) as count'))
                ->groupBy('jed.journal_entry_id', 'da.departemen_id')
                ->get();

            foreach ($siblingRows as $sr) {
                $voucherDeptCounts[$sr->journal_entry_id][$sr->departemen_id] = (int) $sr->count;
            }
        }

        // Attach anomaly detection for each exported row
        foreach ($details as $detail) {
            $voucherContext = [];
            if ($detail->journal_entry_id && isset($voucherDeptCounts[$detail->journal_entry_id])) {
                $voucherContext = ['dept_counts' => $voucherDeptCounts[$detail->journal_entry_id]];
            }
            $detail->anomaly = $this->anomalyDetectionEngine->detectAnomalies($detail, $voucherContext);
        }

        $exportData = [
            'details'        => $details,
            'kpis'           => $kpis,
            'totalDebit'     => $totalDebit,
            'totalCredit'    => $totalCredit,
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'departemenId'   => $departemenId,
            'departemenNama' => $departemenNama,
            'kategori'       => $kategori,
            'tipeAkun'       => $tipeAkun,
            'sourcePrefix'   => $sourcePrefix,
            'search'         => $search,
            'anomaliesOnly'  => $anomaliesOnly,
            'exportDate'     => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        $timestamp = Carbon::now()->format('Ymd_His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('assigned_watchdog.export_pdf', $exportData);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download("assigned_watchdog_{$timestamp}.pdf");
        }

        return Excel::download(new AssignedWatchdogExport($exportData), "assigned_watchdog_{$timestamp}.xlsx");
    }
}

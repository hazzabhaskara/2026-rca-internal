<?php

namespace App\Http\Controllers;

use App\Departement;
use App\Exports\LabaRugiDepartemenExport;
use App\Services\LabaRugiDepartemenService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LabaRugiDepartemenController extends Controller
{
    /** @var LabaRugiDepartemenService */
    protected $service;

    public function __construct(LabaRugiDepartemenService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the filter form for Departmental Income Statement.
     */
    public function filter(Request $request)
    {
        $departments = Departement::orderBy('kode')->get();
        return view('laba_rugi_departemen.filter_laba_rugi_departemen', compact('departments'));
    }

    /**
     * Backward-compatible alias for existing menu.
     */
    public function filterLabaRugiDepartemen(Request $request)
    {
        return $this->filter($request);
    }

    /**
     * Display the report view (Matrix or Single Department mode).
     */
    public function report(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        // Defaults to current year YTD if not specified
        if (empty($startDate)) {
            $startDate = Carbon::now()->startOfYear()->toDateString();
        }
        if (empty($endDate)) {
            $endDate = Carbon::now()->toDateString();
        }

        $mode = $request->get('mode', 'matrix');
        $departemenId = $request->get('departemen_id');

        $options = [
            'hide_account_with_zero' => $request->has('hide_account_with_zero') || $request->get('hide_account_with_zero') === 'hide_account_with_zero',
            'show_account_number'   => $request->has('show_account_number') || $request->get('show_account_number') === 'show_account_number',
        ];

        $data = $this->service->buildReportData($startDate, $endDate, $mode, $departemenId, $options);

        return view('laba_rugi_departemen.laba_rugi_departemen_report', $data);
    }

    /**
     * Export report to Excel or PDF.
     */
    public function export(Request $request)
    {
        $format    = $request->get('format', 'excel');
        $startDate = $request->get('start_date') ?: Carbon::now()->startOfYear()->toDateString();
        $endDate   = $request->get('end_date') ?: Carbon::now()->toDateString();
        $mode      = $request->get('mode', 'matrix');
        $departemenId = $request->get('departemen_id');

        $options = [
            'hide_account_with_zero' => $request->has('hide_account_with_zero') || $request->get('hide_account_with_zero') === 'hide_account_with_zero',
            'show_account_number'   => $request->has('show_account_number') || $request->get('show_account_number') === 'show_account_number',
        ];

        $data = $this->service->buildReportData($startDate, $endDate, $mode, $departemenId, $options);

        $dateStr = Carbon::parse($endDate)->format('Y-m-d');
        $modeStr = $data['mode'] ?? 'matrix';
        $deptSuffix = ($data['mode'] === 'single' && !empty($data['singleDept']))
            ? '_' . strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $data['singleDept']->kode ?? 'dept'))
            : '';

        if ($format === 'excel') {
            $filename = "laba_rugi_departemen_{$modeStr}{$deptSuffix}_{$dateStr}.xlsx";
            return Excel::download(new LabaRugiDepartemenExport($data), $filename);
        }

        if ($format === 'pdf') {
            $filename = "laba_rugi_departemen_{$modeStr}{$deptSuffix}_{$dateStr}.pdf";
            $viewName = ($data['mode'] === 'single')
                ? 'laba_rugi_departemen.export_pdf_single'
                : 'laba_rugi_departemen.export_pdf_matrix';

            $pdf = Pdf::loadView($viewName, $data);
            $pdf->setPaper('a4', $data['mode'] === 'single' ? 'portrait' : 'landscape');
            return $pdf->download($filename);
        }

        return back()->with('error', 'Format export tidak dikenali.');
    }

    /**
     * AJAX endpoint returning reconciliation audit details with Balance Sheet.
     */
    public function reconcileAudit(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfYear()->toDateString());
        $endDate   = $request->get('end_date', Carbon::now()->toDateString());

        $audit = $this->service->getReconciliationAudit($startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data'   => $audit,
        ]);
    }
}

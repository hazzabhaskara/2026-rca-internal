<?php

namespace App\Http\Controllers;

use App\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $data = $query->get();
        return view('activity_log.index', compact('data', 'request'));
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $data = $query->get();

        ActivityLog::create([
            'user_id'    => auth()->id(),
            'activity'   => 'Export PDF Activity Log',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        $pdf = \PDF::loadView('activity_log.pdf', compact('data', 'request'));
        return $pdf->download('Laporan_Aktivitas_Karyawan_' . date('Ymd_His') . '.pdf');
    }
}

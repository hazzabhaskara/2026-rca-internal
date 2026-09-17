<?php

namespace App\Http\Controllers;

use App\PengajuanApproval;
use Illuminate\Http\Request;

class PengajuanApprovalController extends Controller
{
    //
    public function index()
    {
        $employee = auth()->user()->employee;

        // Ambil pengajuan yang membutuhkan approval dari employee ini
        $approvals = PengajuanApproval::query()
            ->select('pengajuan_approvals.*')
            ->join('approval_steps as current_steps', 'pengajuan_approvals.approval_step_id', '=', 'current_steps.id')
            ->where('pengajuan_approvals.approver_id', $employee->id)
            ->where('pengajuan_approvals.status', 'pending')
            ->whereNotExists(function ($sub) {
                $sub->selectRaw(1)
                    ->from('pengajuan_approvals as prev')
                    ->join('approval_steps as prev_steps', 'prev.approval_step_id', '=', 'prev_steps.id')
                    ->whereColumn('prev.pengajuan_id', 'pengajuan_approvals.pengajuan_id')
                    ->whereColumn('prev_steps.step_order', '<', 'current_steps.step_order')
                    ->where('prev.status', '!=', 'approved');
            })
            ->with(['pengajuan.rekening', 'pengajuan.employee'])
            ->get();

        return view('pengajuan.approval.index', compact('approvals'));
    }
}

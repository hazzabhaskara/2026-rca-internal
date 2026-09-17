<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PengajuanApproval extends Model
{
    //
    protected $fillable = [
        'pengajuan_id',
        'approval_step_id',
        'approver_id',
        'status',
        'note',
        'approved_at',
        'step_order'
    ];

    // 🔗 Relasi
    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }

    public function step()
    {
        return $this->belongsTo(ApprovalStep::class, 'approval_step_id');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    public function jabatan()
    {
        return $this->hasOneThrough(
            Jabatan::class,
            ApprovalStep::class,
            'id',           // FK di ApprovalStep
            'id',           // PK di Jabatan
            'approval_step_id', // FK di PengajuanApproval
            'jabatan_id'        // FK di ApprovalStep
        );
    }
}

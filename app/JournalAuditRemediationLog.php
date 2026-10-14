<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JournalAuditRemediationLog extends Model
{
    protected $table = 'journal_audit_remediation_logs';

    protected $fillable = [
        'journal_entry_detail_id',
        'journal_audit_anomaly_id',
        'voucher',
        'old_kode_akun',
        'new_kode_akun',
        'old_departemen_akun_id',
        'new_departemen_akun_id',
        'amount',
        'comment',
        'alasan',
        'user_id',
        'batch_id',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function anomaly()
    {
        return $this->belongsTo(JournalAuditAnomaly::class, 'journal_audit_anomaly_id');
    }

    public function detail()
    {
        return $this->belongsTo(JournalEntryDetail::class, 'journal_entry_detail_id');
    }
}

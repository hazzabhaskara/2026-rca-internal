<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JournalAuditAnomaly extends Model
{
    protected $table = 'journal_audit_anomalies';

    protected $fillable = [
        'journal_entry_detail_id',
        'journal_entry_id',
        'voucher',
        'tanggal',
        'departemen_id',
        'current_kode_akun',
        'current_nama_akun',
        'recommended_kode_akun',
        'recommended_nama_akun',
        'alternate_options',
        'debits',
        'credits',
        'comment',
        'status',
        'alasan_diagnosa',
        'rule_id',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'resolved_action',
    ];

    protected $casts = [
        'alternate_options' => 'array',
        'debits' => 'float',
        'credits' => 'float',
        'is_resolved' => 'boolean',
        'tanggal' => 'date',
    ];

    public function journalEntryDetail()
    {
        return $this->belongsTo(JournalEntryDetail::class, 'journal_entry_detail_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function rule()
    {
        return $this->belongsTo(KamusIndikatorAkun::class, 'rule_id');
    }

    public function currentAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'current_kode_akun', 'kode_akun');
    }

    public function recommendedAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'recommended_kode_akun', 'kode_akun');
    }

    public function departemen()
    {
        return $this->belongsTo(Departement::class, 'departemen_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function remediationLogs()
    {
        return $this->hasMany(JournalAuditRemediationLog::class, 'journal_audit_anomaly_id');
    }
}

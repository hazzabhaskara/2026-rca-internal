<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepartmentAssignmentLog extends Model
{
    protected $table = 'department_assignment_logs';

    protected $fillable = [
        'journal_entry_detail_id',
        'journal_entry_id',
        'voucher',
        'kode_akun',
        'old_departemen_id',
        'new_departemen_id',
        'old_departemen_akun_id',
        'new_departemen_akun_id',
        'action_type',
        'reason',
        'user_id',
        'batch_id',
        'is_override_closed',
        'ip_address',
    ];

    protected $casts = [
        'is_override_closed' => 'boolean',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    /**
     * Relationship: The journal entry detail that was modified.
     */
    public function journalEntryDetail()
    {
        return $this->belongsTo(JournalEntryDetail::class, 'journal_entry_detail_id');
    }

    /**
     * Relationship: The parent journal entry voucher.
     */
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    /**
     * Relationship: The previously assigned department (if any).
     */
    public function oldDepartemen()
    {
        return $this->belongsTo(Departement::class, 'old_departemen_id');
    }

    /**
     * Relationship: The newly assigned department (if any).
     */
    public function newDepartemen()
    {
        return $this->belongsTo(Departement::class, 'new_departemen_id');
    }

    /**
     * Relationship: The old pivot record in departemen_akuns.
     */
    public function oldDepartemenAkun()
    {
        return $this->belongsTo(DepartemenAkun::class, 'old_departemen_akun_id');
    }

    /**
     * Relationship: The new pivot record in departemen_akuns.
     */
    public function newDepartemenAkun()
    {
        return $this->belongsTo(DepartemenAkun::class, 'new_departemen_akun_id');
    }

    /**
     * Relationship: Chart of Account by kode_akun.
     */
    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'kode_akun', 'kode_akun');
    }

    /**
     * Relationship: The user who performed this adjustment.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes

    public function scopeReassigned($query)
    {
        return $query->where('action_type', 'reassign');
    }

    public function scopeUnassigned($query)
    {
        return $query->where('action_type', 'unassign');
    }

    public function scopeInitialAssigned($query)
    {
        return $query->where('action_type', 'initial_assign');
    }

    public function scopeOverrideClosed($query)
    {
        return $query->where('is_override_closed', true);
    }

    public function scopeForDetail($query, $detailId)
    {
        return $query->where('journal_entry_detail_id', $detailId);
    }

    public function scopeForVoucher($query, $voucher)
    {
        return $query->where('voucher', $voucher);
    }

    public function scopeForBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }
}

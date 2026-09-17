<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    //
    protected $fillable = [
        'no_pengajuan',
        'tgl_pengajuan',
        'keterangan',
        'no_rek_id',
        'employee_id',
        'tgl_proses',
        'status'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function rekening()
    {
        return $this->belongsTo(Rekening::class, 'no_rek_id');
    }

    public function details()
    {
        return $this->hasMany(PengajuanDetail::class, 'pengajuan_id');
    }

    public function approvals()
    {
        return $this->hasMany(PengajuanApproval::class, 'pengajuan_id')
            ->orderBy('step_order');
    }
}

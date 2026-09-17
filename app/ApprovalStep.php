<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    //
    protected $fillable =
    [
        'jabatan_id',
        'step_order'
    ];


    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }
    public function approvals()
    {
        return $this->hasMany(PengajuanApproval::class, 'approval_step_id');
    }
}

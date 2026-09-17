<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PengajuanDetail extends Model
{
    //
    protected $fillable = ['pengajuan_id', 'account_id', 'uraian', 'qty', 'harga', 'discount'];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}

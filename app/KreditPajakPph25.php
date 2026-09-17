<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KreditPajakPph25 extends Model
{
    //
    protected $fillable = ['kredit_pajak_id', 'bulan', 'nilai'];

    public function kreditPajak()
    {
        return $this->belongsTo(KreditPajak::class);
    }
}

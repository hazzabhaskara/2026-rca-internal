<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NumberingAccount extends Model
{
    //
    protected $fillable = [
        'nama_grup',
        'jumlah_digit',
        'nomor_akun_awal',
        'nomor_akun_akhir'
    ];
    public function klasifikasiAkun()
    {
        return $this->hasMany(KlasifikasiAkun::class, 'numbering_account_id');
    }
    public function account()
    {
        return $this->belongsTo(chartOfAccount::class);
    }
}

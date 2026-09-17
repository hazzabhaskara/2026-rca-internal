<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FiscalAccount extends Model
{
    //

    protected $fillable =
    ['kode_akun', 'nama_akun'];

    public function chartOfAccount()
    {
        return $this->hasMany(chartOfAccount::class);
    }
}

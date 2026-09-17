<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class linkedAccounts extends Model
{
    //
    // use HasFactory;
    protected $fillable = ['modul', 'kode', 'akun_id', 'unit_kerja_id', 'departemen_id'];

    public function akun()
    {
        return $this->belongsTo(ChartOfAccount::class, 'akun_id');
    }
    public function departemen()
    {
        return $this->belongsTo(Departement::class, 'departemen_id');
    }
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }
}

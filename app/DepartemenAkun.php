<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepartemenAkun extends Model
{
    //
    protected $fillable = [
        'departemen_id',
        'akun_id',
    ];

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'akun_id');
    }

    public function departemen()
    {
        return $this->belongsTo(Departement::class);
    }
}

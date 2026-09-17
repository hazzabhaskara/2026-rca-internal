<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KomposisiGaji extends Model
{
    //
    protected $fillable = [
        'kode_karyawan',
    ];

    // Relasi: 1 komposisi gaji dimiliki oleh 1 karyawan
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'kode_karyawan');
    }

    // Relasi: 1 komposisi gaji punya banyak detail
    public function details()
    {
        return $this->hasMany(KomposisiGajiDetail::class, 'kode_komposisi_id');
    }
    public function pembayaranDetails()
    {
        return $this->hasMany(PembayaranGajiDetail::class, 'komposisi_gaji_id');
    }
}

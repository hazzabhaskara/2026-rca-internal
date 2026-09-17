<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PembayaranGaji extends Model
{
    //

    protected $fillable = [
        'kode_karyawan',
        'periode_awal',
        'periode_akhir',
        'tanggal_pembayaran',
    ];

    // Relasi: 1 komposisi gaji dimiliki oleh 1 karyawan
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'kode_karyawan');
    }

    public function details()
    {
        return $this->hasMany(PembayaranGajiDetail::class, 'kode_pembayaran_id');
    }
}

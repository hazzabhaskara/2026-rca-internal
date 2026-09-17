<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PembayaranGajiDetail extends Model
{
    //
    protected $fillable = [
        'kode_pembayaran_id',
        'kode_komponen',
        'nilai',
        'jumlah_hari',
        'potongan',
        'urut',
    ];

    // Relasi: detail milik satu komposisi gaji
    public function pembayaran()
    {
        return $this->belongsTo(PembayaranGaji::class, 'kode_pembayaran');
    }

    // Relasi: detail terhubung dengan 1 komponen penghasilan
    public function komponen()
    {
        return $this->belongsTo(KomponenPenghasilan::class, 'kode_komponen');
    }
}

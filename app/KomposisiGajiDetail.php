<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KomposisiGajiDetail extends Model
{
    //
    protected $fillable = [
        'komposisi_gaji_id',
        'kode_komponen',
        'nilai',
        'jumlah_hari',
        'potongan',
        'urut',
    ];

    // Relasi: detail milik satu komposisi gaji
    public function komposisi()
    {
        return $this->belongsTo(KomposisiGaji::class, 'kd_komposisi');
    }

    // Relasi: detail terhubung dengan 1 komponen penghasilan
    public function komponen()
    {
        return $this->belongsTo(KomponenPenghasilan::class, 'kode_komponen');
    }
}

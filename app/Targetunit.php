<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Targetunit extends Model
{
    //
    protected $fillable =
    [
        'id',
        'unit_kerja_id',
        'komponen_penghasilan_id',
        'target_bulanan',
        'level_karyawan_id',
        'besaran_nominal',
        'bulan',
        'tahun'
    ];

    public function unit()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id', 'id');
    }

    // 🔗 Relasi ke Komponen Penghasilan
    public function komponen()
    {
        return $this->belongsTo(KomponenPenghasilan::class, 'komponen_penghasilan_id', 'id');
    }

    // 🔗 Relasi ke Level Karyawan (opsional)
    public function levelKaryawan()
    {
        return $this->belongsTo(LevelKaryawan::class, 'level_karyawan_id', 'id');
    }
}

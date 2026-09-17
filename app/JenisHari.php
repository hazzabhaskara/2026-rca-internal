<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JenisHari extends Model
{
    //
    protected $fillable =
    [
        'nama',
        'unit_kerja_id',
        'deskripsi',
        'jam_mulai',
        'jam_selesai',
        'jumlah_pengunjung_min',
        'jumlah_pengunjung_max'
    ];

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }
    public function BonusKaryawan()
    {
        return $this->hasMany(BonusKaryawan::class, 'jenis_hari_id');
    }
    public function scheduling()
    {
        return $this->hasMany(ShiftKaryawanWahana::class, 'jenis_hari_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TargetWahana extends Model
{
    //

    protected $fillable = [
        'wahana_id',
        'unit_kerja_id',
        'jenis_hari_id',
        'target_harian',
        'tahun',
        'bulan',
        'keterangan'
    ];

    public function wahana()
    {
        return $this->belongsTo(Wahana::class);
    }

    public function unit()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }
    public function jenis_hari()
    {
        return $this->belongsTo(JenisHari::class);
    }
}

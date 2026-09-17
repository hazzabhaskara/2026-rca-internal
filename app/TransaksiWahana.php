<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransaksiWahana extends Model
{
    //
    protected $fillable = [
        'unit_kerja_id',
        'wahana_id',
        'jenis_hari_id',
        'tanggal',
        'realisasi',
        'jumlah_pengunjung',
    ];

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function wahana()
    {
        return $this->belongsTo(Wahana::class, 'wahana_id');
    }
    public function jenisHari()
    {
        return $this->belongsTo(JenisHari::class, 'jenis_hari_id');
    }
}

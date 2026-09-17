<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wahana extends Model
{
    //
    protected $fillable = [
        'unit_kerja_id',
        'kode_wahana',
        'nama_wahana',
        'status',
        'kapasitas',
        'kategori',
        'urutan',
    ];

    public function UnitKerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }
    public function wahanaItem()
    {
        return $this->hasMany(WahanaItem::class, 'wahana_id');
    }
}

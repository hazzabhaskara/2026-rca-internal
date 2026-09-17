<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    //

    protected $fillable = [
        'group_unit_id',
        'kode_unit',
        'nama_unit',
        'deskripsi',
        'urutan',
        'format_closing',
    ];

    public function groupUnit()
    {
        return $this->belongsTo(GroupUnit::class, 'group_unit_id');
    }
    public function jenisHari()
    {
        return $this->hasMany(JenisHari::class, 'unit_kerja_id');
    }
    public function employee()
    {
        return $this->hasMany(Employee::class, 'unit_kerja_id');
    }
    public function targetUnit()
    {
        return $this->hasMany(Targetunit::class, 'unit_kerja_id');
    }
}

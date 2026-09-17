<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Jamkerja extends Model
{
    //

    protected $fillable = [
        'unit_kerja_id',
        'jam_masuk',
        'jam_keluar',

    ];

    public function unitKerja()
    {
        return $this->belongsTo(unitKerja::class, 'unit_kerja_id');
    }
}

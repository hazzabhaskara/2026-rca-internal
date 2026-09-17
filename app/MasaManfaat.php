<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasaManfaat extends Model
{
    //
    protected $fillable = [
        'jenis',
        'masa_tahun',
        'kelompok_harta',
        'golongan',
        'nama_golongan',
        'tarif_penyusutan',
    ];
}

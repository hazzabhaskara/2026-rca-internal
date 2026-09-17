<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Taxes extends Model
{
    //

    protected $fillable = [
        'bulan',
        'tahun',
        'jenis_pajak',
        'jenis_dokumen',
        'file_path',
    ];
}

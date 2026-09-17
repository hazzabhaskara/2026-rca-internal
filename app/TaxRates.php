<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxRates extends Model
{
    //
    protected $fillable = [
        'ptkp_id',
        'min_penghasilan',
        'max_penghasilan',
        'tarif_ter'
    ];
    // Relasi ke golongan PTKP
    public function ptkp()
    {
        return $this->belongsTo(Ptkp::class, 'ptkp_id');
    }
}

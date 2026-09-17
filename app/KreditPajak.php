<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KreditPajak extends Model
{
    //
    protected $fillable = ['tahun', 'pph_22', 'pph_23', 'pph_24'];

    public function pph25()
    {
        return $this->hasMany(KreditPajakPph25::class);
    }
}

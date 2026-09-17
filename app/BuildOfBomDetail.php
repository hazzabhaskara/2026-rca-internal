<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BuildOfBomDetail extends Model
{
    //
    protected $fillable = [
        'build_of_bom_id',
        'component_item_id',
        'unit',
        'qty_per_unit',
        'qty_total',
        'cost_component',
    ];

    /**
     * Relasi ke header build (BuildOfBom)
     */
    public function build()
    {
        return $this->belongsTo(BuildOfBom::class, 'build_of_bom_id');
    }

    /**
     * Komponen yang digunakan (referensi ke tabel items)
     */
    public function component()
    {
        return $this->belongsTo(Item::class, 'component_item_id');
    }
}

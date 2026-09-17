<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BuildOfBom extends Model
{
    //
    protected $fillable = [
        'date',
        'item_id',
        'qty_to_build',
        'total_cost',
        'status',
        'notes',
        'from_location_id'
    ];

    /**
     * Produk jadi yang dihasilkan dari build ini
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    public function LocationInventory()
    {
        return $this->belongsTo(LocationInventory::class, 'from_location_id');
    }

    /**
     * Daftar komponen detail build
     */
    public function details()
    {
        return $this->hasMany(BuildOfBomDetail::class, 'build_of_bom_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransferInventory extends Model
{
    //
    protected $fillable = [
        'from_location_id',
        'to_location_id',
        'source',
        'date',
        'notes',
    ];
    public function fromInventory()
    {
        return $this->belongsTo(LocationInventory::class, 'from_location_id');
    }
    public function toInventory()
    {
        return $this->belongsTo(LocationInventory::class, 'to_location_id');
    }

    /**
     * Relasi ke detail komponen.
     */
    public function details()
    {
        return $this->hasMany(TransferInventoryDetail::class, 'transfer_inventory_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryAccount extends Model
{
    //
    protected $fillable = [
        'inventory_id',
        'asset_account_id',
        'revenue_account_id',
        'cogs_account_id',
        'variance_account_id',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
    public function account()
    {
        return $this->belongsTo(chartOfAccount::class);
    }
}

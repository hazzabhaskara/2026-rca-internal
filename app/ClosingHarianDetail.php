<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClosingHarianDetail extends Model
{
    //
    protected $fillable =
    ['closing_harian_id', 'wahana_item_id', 'qty', 'harga', 'jumlah', 'omset_total', 'qris', 'cash', 'merch', 'rca', 'titipan', 'lebih_kurang'];

    public function closingHarian()
    {
        return $this->belongsTo(ClosingHarian::class);
    }
    public function wahanaItem()
    {
        return $this->belongsTo(WahanaItem::class, 'wahana_item_id');
    }
}

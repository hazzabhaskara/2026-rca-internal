<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClosingHarian extends Model
{
    //
    protected $fillable =
    [
        'unit_kerja_id',
        'tanggal',
        'total_omset',
        'total_qris',
        'total_cash',
        'total_merch',
        'total_rca',
        'total_titipan',
        'mdr_rate',
        'mdr_amount',
        'subtotal_after_mdr',
        'total_lebih_kurang',
        'jumlah_pengunjung'
    ];
    public function UnitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }
    public function details()
    {
        return $this->hasMany(ClosingHarianDetail::class, 'closing_harian_id');
    }
}

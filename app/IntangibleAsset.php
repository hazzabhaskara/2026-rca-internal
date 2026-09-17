<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IntangibleAsset extends Model
{
    //
    protected $fillable = [
        'kode_asset',
        'nama_asset',
        'brand',
        'deskripsi',
        'lokasi_id',
        'golongan_id',
        'dalam_tahun',
        'metode_penyusutan_id',
        'tarif_amortisasi',
        'kategori_id',

    ];
    public function kategori()
    {
        return $this->belongsTo(KategoriAsset::class, 'kategori_id');
    }

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }
    public function golongan()
    {
        return $this->belongsTo(MasaManfaat::class, 'golongan_id');
    }
    public function metode_penyusutan()
    {
        return $this->belongsTo(MetodePenyusutan::class, 'metode_penyusutan_id');
    }
}

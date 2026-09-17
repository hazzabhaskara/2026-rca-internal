<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TangibleAsset extends Model
{
    //
    protected $fillable = [
        'kode_asset',
        'nama_asset',
        'kategori_id',
        'merk',
        'tanggal_perolehan',
        'nilai_perolehan',
        'lokasi_id',
        'foto',
        'components',
        'capacity',
        'type',
        'golongan_id',
        'metode_penyusutan_id',
        'dalam_tahun',
        'tarif_penyusutan',
        'asset_full_name',
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

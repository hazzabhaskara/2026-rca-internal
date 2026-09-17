<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WahanaItem extends Model
{
    //

    protected $fillable =
    ['wahana_id', 'kode_item', 'nama_item', 'harga', 'status', 'account_id', 'departemen_id', 'dasar_perhitungan_titipan', 'harga_perhitungan_titipan'];


    public function wahana()
    {
        return $this->belongsTo(Wahana::class, 'wahana_id');
    }
    public function account()
    {
        return $this->belongsTo(chartOfAccount::class, 'account_id');
    }
    public function departemen()
    {
        return $this->belongsTo(Departement::class, 'departemen_id');
    }
}

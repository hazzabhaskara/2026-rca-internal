<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KlasifikasiAkun extends Model
{
    //
    protected $fillable = [
        'kode_klasifikasi',
        'nama_klasifikasi',
        'numbering_account_id',
        'parent_id',
        'urutan',
        'deskripsi',
        'aktif',
    ];

    // Relasi ke tipe akun
    public function numberingAccount()
    {
        return $this->belongsTo(NumberingAccount::class, 'numbering_account_id');
    }
    // Relasi ke parent klasifikasi (self-referencing)
    public function parent()
    {
        return $this->belongsTo(KlasifikasiAkun::class, 'parent_id');
    }

    // Relasi ke anak klasifikasi (untuk menampilkan anak-anaknya)
    public function children()
    {
        return $this->hasMany(KlasifikasiAkun::class, 'parent_id');
    }
    public function account()
    {
        return $this->hasMany(chartOfAccount::class, 'klasifikasi_id');
    }
}

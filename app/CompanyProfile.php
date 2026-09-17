<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    //
    protected $fillable = [
        'nama_perusahaan',
        'jalan',
        'id_kelurahan',
        'id_kecamatan',
        'id_kota',
        'id_provinsi',
        'kode_pos',
        'logo',
        'phone_number',
        'email'
    ];
    public function legalDocuments()
    {
        return $this->hasMany(LegalDocumentCompanyProfile::class);
    }
    public function provinsi()
    {
        return $this->belongsTo(ProvinceIndonesia::class, 'id_provinsi');
    }
    public function kota()
    {
        return $this->belongsTo(KotaIndonesia::class, 'id_kota');
    }
    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan');
    }
    public function kelurahan()
    {
        return $this->belongsTo(Kelurahan::class, 'id_kelurahan');
    }
}

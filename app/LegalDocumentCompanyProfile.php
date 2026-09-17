<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LegalDocumentCompanyProfile extends Model
{
    //
    protected $fillable = [
        'company_profile_id',
        'jenis_dokumen',
        'file_path'
    ];
    public function perusahaan()
    {
        return $this->belongsTo(CompanyProfile::class, 'company_profile_id');
    }
}

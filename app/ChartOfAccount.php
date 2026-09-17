<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class chartOfAccount extends Model
{
    //

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'tipe_akun',
        'level_akun',
        'parent_id',
        'aktif',
        'omit_zero_balance',
        'allow_project_allocation',
        'catatan',
        'catatan_pajak',
        'klasifikasi_id',
        'fiscal_account_id',
        'is_income_tax'
    ];
    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }
    public function klasifikasiAkun()
    {
        return $this->belongsTo(KlasifikasiAkun::class, 'klasifikasi_id');
    }
    public function numberingGrup()
    {
        return NumberingAccount::where('nomor_akun_awal', '<=', $this->kode_akun)
            ->where('nomor_akun_akhir', '>=', $this->kode_akun)
            ->first();
    }

    // ✅ Ambil nama grup (aset, kewajiban, dll)
    public function getNamaGrupAttribute()
    {
        return optional($this->numberingGrup())->nama_grup;
    }

    // ✅ Hitung level akun berdasarkan kode_akun
    public function getLevelAttribute()
    {
        // Level berdasarkan jumlah digit signifikan (tidak termasuk trailing zero)
        return strlen(rtrim($this->kode_akun, '0'));
    }
    public function departemen()
    {
        return $this->belongsTo(Departement::class, 'departemen_id');
    }
    public function departemenAkun()
    {
        return $this->hasMany(DepartemenAkun::class, 'akun_id');
    }
    public function journalEntryDetails()
    {
        return $this->hasMany(JournalEntryDetail::class, 'kode_akun');
    }
    public function paymentMethodDetails()
    {
        return $this->hasMany(PaymentMethodDetail::class, 'coa_id');
    }
    public function fiscalAccount()
    {
        return $this->belongsTo(FiscalAccount::class, 'fiscal_account_id');
    }
    public function wahanaItem()
    {
        return $this->hasMany(WahanaItem::class);
    }
}

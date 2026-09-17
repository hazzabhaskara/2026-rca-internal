<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BonusKaryawan extends Model
{


    protected $fillable = [
        'employee_id',
        'shift_id',
        'transaksi_wahana_id',
        'tanggal',
        'jenis_hari_id',
        'bonus',
        'transportasi',
        'total',
        'status',
        'keterangan',
    ];

    // Relasi ke Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Relasi ke Shift Karyawan Wahana
    public function shift()
    {
        return $this->belongsTo(ShiftKaryawanWahana::class, 'shift_id');
    }

    // Relasi ke Transaksi Wahana (opsional, bisa null)
    public function transaksiWahana()
    {
        return $this->belongsTo(TransaksiWahana::class, 'transaksi_wahana_id');
    }

    // Relasi ke Jenis Hari
    public function jenisHari()
    {
        return $this->belongsTo(JenisHari::class, 'jenis_hari_id');
    }

    // Accessor untuk format rupiah
    public function getTotalFormattedAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }
}

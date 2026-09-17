<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ShiftKaryawanWahana extends Model
{
    //
    protected $fillable =
    [
        'employee_id',
        'unit_kerja_id',
        'wahana_id',
        'tanggal',
        'jenis_hari_id',
        'jam_mulai',
        'jam_selesai',
        'lama_jam',
        'persentase_jam',
        'lembur_jam',
        'status',
        'keterangan',
        'crew_id'
    ];
    // Relasi ke Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Relasi ke Unit Kerja
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    // Relasi ke Wahana
    public function wahana()
    {
        return $this->belongsTo(Wahana::class, 'wahana_id');
    }

    // Relasi ke Jenis Hari
    public function jenisHari()
    {
        return $this->belongsTo(JenisHari::class, 'jenis_hari_id');
    }

    public function crewShift()
    {
        return $this->belongsTo(CrewShiftKaryawan::class, 'crew_id');
    }
    // Accessor untuk menghitung lama_jam otomatis
    public function getLamaJamAttribute($value)
    {
        if ($value !== null) {
            return $value; // kalau sudah ada di database, pakai itu
        }

        if ($this->jam_mulai && $this->jam_selesai) {
            $mulai = Carbon::parse($this->jam_mulai);
            $selesai = Carbon::parse($this->jam_selesai);
            return $mulai->diffInMinutes($selesai) / 60;
        }

        return null;
    }

    // Accessor untuk persentase jam (butuh default jam dari jenis_hari)
    public function getPersentaseJamAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }

        if ($this->jenisHari && $this->jam_mulai && $this->jam_selesai) {
            $defaultMulai = Carbon::parse($this->jenisHari->jam_mulai);
            $defaultSelesai = Carbon::parse($this->jenisHari->jam_selesai);
            $defaultJam = $defaultMulai->diffInMinutes($defaultSelesai) / 60;

            return $defaultJam > 0 ? $this->lama_jam / $defaultJam : null;
        }

        return null;
    }
}

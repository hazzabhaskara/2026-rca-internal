<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    //
    protected $fillable =
    [
        'employee_id',
        'tanggal',
        'jam',
        'status'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function getDurasiLemburAttribute()
    {
        if ($this->jam_lembur_masuk && $this->jam_lembur_pulang) {
            $masuk = \Carbon\Carbon::parse($this->jam_lembur_masuk);
            $pulang = \Carbon\Carbon::parse($this->jam_lembur_pulang);

            return $pulang->diffInHours($masuk) . ' jam ' .
                $pulang->diff($masuk)->format('%I menit');
        }
        return null;
    }
}

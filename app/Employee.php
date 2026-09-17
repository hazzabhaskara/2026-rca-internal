<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    //

    protected $fillable = [
        'kode_karyawan',
        'nama_karyawan',
        'nama_panggilan',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'golongan_darah',
        'tinggi_badan',
        'alamat',
        'telepon',
        'email',
        'agama',
        'kewarganegaraan',
        'status_pernikahan',
        'ptkp_id',
        'jabatan_id',
        'level_kepegawaian_id',
        'unit_kerja_id',
        'tanggal_masuk',
        'tanggal_keluar',
        'status_pegawai',
        'sertifikat',
        'photo',
        'foto_ktp',
        'rfid_code',
        'supervisor_id',
        'user_id'
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function ptkp()
    {
        return $this->belongsTo(Ptkp::class, 'ptkp_id');
    }

    public function levelKaryawan()
    {
        return $this->belongsTo(LevelKaryawan::class, 'level_kepegawaian_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }
    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

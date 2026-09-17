<?php

namespace App\Exports;

use App\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = Employee::with('jabatan', 'ptkp', 'levelKaryawan', 'unitKerja')->get();

        return $data->map(function ($item) {
            return [
                $item->kode_karyawan,
                $item->nama_karyawan,
                $item->nik,
                $item->tempat_lahir,
                $item->tanggal_lahir,
                $item->jenis_kelamin,
                $item->golongan_darah,
                $item->tinggi_badan,
                $item->alamat,
                $item->telepon,
                $item->email,
                $item->agama,
                $item->kewarganegaraan,
                $item->status_pernikahan,
                $item->ptkp->nama,
                $item->jabatan->nama_jabatan,
                $item->levelKaryawan->nama_level,
                $item->unitKerja->nama_unit,
                $item->tanggal_masuk,
                $item->tanggal_keluar,
                $item->status_pegawai,
                $item->sertifikat
            ];
        });
    }
    public function headings(): array
    {
        return [
            'kode_karyawan',
            'nama_karyawan',
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
        ];
    }
}

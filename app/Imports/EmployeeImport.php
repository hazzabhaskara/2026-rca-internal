<?php

namespace App\Imports;

use App\Employee;
use App\Jabatan;
use App\LevelKaryawan;
use App\Ptkp;
use App\UnitKerja;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class EmployeeImport implements ToCollection, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 karena heading row di baris 1
            // Cari ID unit kerja dari nama
            $unitKerja = UnitKerja::where('nama_unit', $row['unit_kerja'])->first();
            $ptkp = Ptkp::where('nama', $row['ptkp'])->first();
            $jabatan = Jabatan::where('nama_jabatan', $row['jabatan'])->first();
            $level_karyawan = LevelKaryawan::where('nama_level', $row['level_karyawan'])->first();

            // Kumpulkan error jika data tidak ditemukan
            if (!$unitKerja || !$ptkp || !$jabatan || !$level_karyawan) {
                $this->errors[] = [
                    'baris' => $rowNumber,
                    'unit_kerja' => !$unitKerja ? "unit_kerja '{$row['unit_kerja']}' tidak ditemukan" : null,
                    'ptkp' => !$ptkp ? "ptkp '{$row['ptkp']}' tidak ditemukan" : null,
                    'jabatan' => !$jabatan ? "jabatan '{$row['jabatan']}' tidak ditemukan" : null,
                    'level_karyawan' => !$level_karyawan ? "Metode penyusutan '{$row['level_karyawan']}' tidak ditemukan" : null,
                ];
                continue; // Lewati insert untuk baris ini
            }
            Employee::create([
                'kode_karyawan'   => $row['kode_karyawan'],
                'nama_karyawan'   => $row['nama_karyawan'],
                'nik'        => $row['nik'],
                'tempat_lahir'     => $row['tempat_lahir'] ?? null,
                'tanggal_lahir'      => $this->transformDate($row['tanggal_lahir']) ?? null,
                'jenis_kelamin'   => $row['jenis_kelamin'] ?? null,
                'golongan_darah'   => $row['golongan_darah'] ?? null,
                'tinggi_badan'        => $row['tinggi_badan'] ?? null,
                'alamat'     => $row['alamat'] ?? null,
                'telepon'      => $row['telepon'] ?? null,
                'email'   => $row['email'] ?? null,
                'agama'        => $row['agama'] ?? null,
                'kewarganegaraan'     => $row['kewarganegaraan'] ?? null,
                'status_pernikahan'      => $row['status_pernikahan'] ?? null,
                'ptkp_id' => $ptkp->id ?? null, // bisa pakai null/0 jika tidak ditemukan
                'jabatan_id' => $jabatan->id ?? null, // bisa pakai null/0 jika tidak ditemukan
                'level_kepegawaian_id' => $level_karyawan->id ?? null, // bisa pakai null/0 jika tidak ditemukan
                'unit_kerja_id' => $unitKerja->id ?? null, // bisa pakai null/0 jika tidak ditemukan
                'tanggal_masuk'   => $this->transformDate($row['tanggal_masuk']) ?? null,
                'tanggal_keluar'   => $this->transformDate($row['tanggal_keluar']) ?? null,
                'status_pegawai'   => $row['status_pegawai'] ?? null,
                'sertifikat'        => $row['sertifikat'] ?? null,

            ]);
        }
    }
    private function transformDate($value)
    {
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}

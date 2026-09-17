<?php

namespace App\Imports;

use App\ChartOfAccount;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ChartOfAccountImport implements ToModel,  WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Lewati baris jika data penting kosong
        if (empty($row['kode_akun']) || empty($row['nama_akun']) || empty($row['tipe_akun'])) {
            return null;
        }

        $kodeAkun = $row['kode_akun'];
        $tipeAkun = $row['tipe_akun'];

        // Ambil konfigurasi numbering akun berdasarkan tipe
        $numbering = \App\NumberingAccount::where('nama_grup', $tipeAkun)->first();

        if (!$numbering) {
            throw new \Exception("Tipe akun `{$tipeAkun}` tidak ditemukan dalam konfigurasi numbering.");
        }

        $jumlahDigit = $numbering->jumlah_digit;

        // Validasi jumlah digit
        if (strlen($kodeAkun) != $jumlahDigit) {
            throw new \Exception("Kode akun `{$kodeAkun}` tidak sesuai jumlah digit yang ditentukan ({$jumlahDigit} digit).");
        }

        // Validasi range
        if ($kodeAkun < $numbering->nomor_akun_awal || $kodeAkun > $numbering->nomor_akun_akhir) {
            throw new \Exception("Kode akun `{$kodeAkun}` berada di luar range yang diizinkan ({$numbering->nomor_akun_awal} - {$numbering->nomor_akun_akhir}).");
        }

        // ✅ Cek apakah user mengisi level_akun manual di Excel
        $manualLevel = $row['level_akun'] ?? null;

        if ($manualLevel) {
            $level_akun = $manualLevel;
        } else {
            // Otomatis tentukan level berdasarkan trailing zero & panjang kode
            $zeroCount = strspn(strrev($kodeAkun), '0');

            if ($zeroCount >= 3) {
                $level_akun = 'Header';
            } elseif ($zeroCount === 2) {
                $level_akun = 'Grup';
            } elseif ($zeroCount === 1) {
                $level_akun = 'Account';
            } else {
                $level_akun = 'Sub Account';
            }
        }

        // Cari klasifikasi akun jika ada
        $klasifikasi = null;
        if (!empty($row['klasifikasi_kode'])) {
            $klasifikasi = \App\KlasifikasiAkun::where('kode_klasifikasi', $row['klasifikasi_kode'])->first();
        }

        return new ChartOfAccount([
            'kode_akun' => $kodeAkun,
            'nama_akun' => $row['nama_akun'],
            'tipe_akun' => $tipeAkun,
            'level_akun' => $level_akun,
            'klasifikasi_id' => $klasifikasi ? $klasifikasi->id : null,
            'omit_zero_balance' => isset($row['omit_zero_balance']) ? (bool) $row['omit_zero_balance'] : false,
            'allow_project_allocation' => isset($row['allow_project_allocation']) ? (bool) $row['allow_project_allocation'] : false,
            'aktif' => isset($row['aktif']) ? (bool) $row['aktif'] : true,
            'catatan' => $row['catatan'] ?? null,
            'catatan_pajak' => $row['catatan_pajak'] ?? null,
        ]);
    }
}

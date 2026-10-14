<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedAssetMasterDataAndLinkedAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Metode Penyusutan
        $metodes = [
            ['nama_metode' => 'Garis Lurus', 'deskripsi' => 'Metode Penyusutan Garis Lurus (Straight Line Method)'],
            ['nama_metode' => 'Saldo Menurun', 'deskripsi' => 'Metode Penyusutan Saldo Menurun (Declining Balance Method)'],
        ];

        foreach ($metodes as $m) {
            DB::table('metode_penyusutans')->updateOrInsert(
                ['nama_metode' => $m['nama_metode']],
                array_merge($m, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // 2. Masa Manfaat (Berdasarkan Ketentuan Perpajakan / PSAK)
        $masaManfaats = [
            ['jenis' => 'Bukan Bangunan', 'masa_tahun' => '4', 'kelompok_harta' => 'Kelompok 1', 'golongan' => 1, 'nama_golongan' => 'Golongan 1', 'tarif_penyusutan' => 25.00],
            ['jenis' => 'Bukan Bangunan', 'masa_tahun' => '8', 'kelompok_harta' => 'Kelompok 2', 'golongan' => 2, 'nama_golongan' => 'Golongan 2', 'tarif_penyusutan' => 12.50],
            ['jenis' => 'Bukan Bangunan', 'masa_tahun' => '16', 'kelompok_harta' => 'Kelompok 3', 'golongan' => 3, 'nama_golongan' => 'Golongan 3', 'tarif_penyusutan' => 6.25],
            ['jenis' => 'Bukan Bangunan', 'masa_tahun' => '20', 'kelompok_harta' => 'Kelompok 4', 'golongan' => 4, 'nama_golongan' => 'Golongan 4', 'tarif_penyusutan' => 5.00],
            ['jenis' => 'Bangunan', 'masa_tahun' => '20', 'kelompok_harta' => 'Bangunan Permanen', 'golongan' => 5, 'nama_golongan' => 'Permanen', 'tarif_penyusutan' => 5.00],
            ['jenis' => 'Bangunan', 'masa_tahun' => '10', 'kelompok_harta' => 'Bangunan Tidak Permanen', 'golongan' => 6, 'nama_golongan' => 'Tidak Permanen', 'tarif_penyusutan' => 10.00],
        ];

        foreach ($masaManfaats as $mm) {
            DB::table('masa_manfaats')->updateOrInsert(
                ['nama_golongan' => $mm['nama_golongan']],
                array_merge($mm, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // 3. Kategori Assets
        $kategoriAssets = [
            ['kode_kategori' => 'KAT-001', 'nama_kategori' => 'Bangunan', 'deskripsi' => 'Gedung dan Bangunan Operasional'],
            ['kode_kategori' => 'KAT-002', 'nama_kategori' => 'Kendaraan', 'deskripsi' => 'Kendaraan Operasional dan Transportasi'],
            ['kode_kategori' => 'KAT-003', 'nama_kategori' => 'Inventaris Kantor', 'deskripsi' => 'Peralatan, Komputer, dan Inventaris Kantor'],
            ['kode_kategori' => 'KAT-004', 'nama_kategori' => 'Aset Tak Berwujud', 'deskripsi' => 'Software, Lisensi Sistem, dan Hak Kekayaan Intelektual'],
        ];

        foreach ($kategoriAssets as $ka) {
            DB::table('kategori_assets')->updateOrInsert(
                ['kode_kategori' => $ka['kode_kategori']],
                array_merge($ka, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // 4. Linked Accounts untuk Modul Asset (Expenses & Accumulated Depreciation)
        $expenseCoa = DB::table('chart_of_accounts')->where('kode_akun', '5313000')->first()
            ?? DB::table('chart_of_accounts')->where('kode_akun', 'like', '5313%')->first();

        $accumCoa = DB::table('chart_of_accounts')->where('kode_akun', '1202002')->first()
            ?? DB::table('chart_of_accounts')->whereBetween('kode_akun', ['1210000', '1299999'])->first()
            ?? DB::table('chart_of_accounts')->where('nama_akun', 'like', '%Akumulasi%')->first();

        if ($expenseCoa) {
            DB::table('linked_accounts')->updateOrInsert(
                ['modul' => 'asset', 'kode' => 'Expenses'],
                [
                    'akun_id' => $expenseCoa->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        if ($accumCoa) {
            DB::table('linked_accounts')->updateOrInsert(
                ['modul' => 'asset', 'kode' => 'Accumulated Depreciation/Amortisation'],
                [
                    'akun_id' => $accumCoa->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('linked_accounts')->where('modul', 'asset')->delete();
        DB::table('kategori_assets')->whereIn('kode_kategori', ['KAT-001', 'KAT-002', 'KAT-003', 'KAT-004'])->delete();
        DB::table('masa_manfaats')->whereIn('golongan', [1, 2, 3, 4, 5, 6])->delete();
        DB::table('metode_penyusutans')->whereIn('nama_metode', ['Garis Lurus', 'Saldo Menurun'])->delete();
    }
}

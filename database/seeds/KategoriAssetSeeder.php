<?php

use App\ChartOfAccount;
use App\KategoriAsset;
use App\linkedAccounts;
use Illuminate\Database\Seeder;

class KategoriAssetSeeder extends Seeder
{
    /**
     * Seed tabel kategori_assets dan default linked_accounts modul 'asset'
     * selaras dengan COA rembulancitraabadi_2026_db (1201001 - 1302002 & 5313001 - 5314002).
     */
    public function run(): void
    {
        $categories = [
            [
                'kode_kategori' => 'KAT-TANAH',
                'nama_kategori' => 'TANAH',
                'deskripsi'     => 'Aset Tetap Tanah (COA Perolehan: 1201001 - Tidak Disusutkan)',
            ],
            [
                'kode_kategori' => 'KAT-BANGUNAN',
                'nama_kategori' => 'BANGUNAN',
                'deskripsi'     => 'Aset Tetap Bangunan (Perolehan: 1202001 | Ak. Penyusutan: 1202002 | Beban: 5313001)',
            ],
            [
                'kode_kategori' => 'KAT-KENDARAAN',
                'nama_kategori' => 'KENDARAAN',
                'deskripsi'     => 'Aset Tetap Kendaraan (Perolehan: 1203001 | Ak. Penyusutan: 1203002 | Beban: 5313002)',
            ],
            [
                'kode_kategori' => 'KAT-INVENTARIS',
                'nama_kategori' => 'INVENTARIS',
                'deskripsi'     => 'Aset Tetap Inventaris & Peralatan (Perolehan: 1204001 | Ak. Penyusutan: 1204002 | Beban: 5313003)',
            ],
            [
                'kode_kategori' => 'KAT-SISTEM',
                'nama_kategori' => 'SISTEM',
                'deskripsi'     => 'Aset Tak Berwujud Sistem & Software (Perolehan: 1302001 | Ak. Amortisasi: 1302002 | Beban: 5314002)',
            ],
        ];

        foreach ($categories as $cat) {
            KategoriAsset::updateOrCreate(
                ['kode_kategori' => $cat['kode_kategori']],
                [
                    'nama_kategori' => $cat['nama_kategori'],
                    'deskripsi'     => $cat['deskripsi'],
                ]
            );
        }

        // Seed default linked_accounts untuk modul 'asset' jika belum ada
        $defaultExpense = ChartOfAccount::where('kode_akun', '5313003')->first();
        $defaultAccum = ChartOfAccount::where('kode_akun', '1204002')->first();

        if ($defaultExpense) {
            linkedAccounts::updateOrInsert(
                ['modul' => 'asset', 'kode' => 'Expenses'],
                ['akun_id' => $defaultExpense->id, 'updated_at' => now()]
            );
        }

        if ($defaultAccum) {
            linkedAccounts::updateOrInsert(
                ['modul' => 'asset', 'kode' => 'Accumulated Depreciation/Amortisation'],
                ['akun_id' => $defaultAccum->id, 'updated_at' => now()]
            );
        }
    }
}

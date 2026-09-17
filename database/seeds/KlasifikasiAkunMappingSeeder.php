<?php

use Illuminate\Database\Seeder;
use App\ChartOfAccount;
use App\KlasifikasiAkun;
use Illuminate\Support\Facades\DB;

/**
 * Seeder untuk mapping Klasifikasi Akun ke Chart of Account
 * 
 * Untuk menjalankan: php artisan db:seed --class=KlasifikasiAkunMappingSeeder
 */
class KlasifikasiAkunMappingSeeder extends Seeder
{
    public function run()
    {
        // Get klasifikasi IDs
        $klasifikasi = KlasifikasiAkun::pluck('id', 'nama_klasifikasi')->toArray();
        
        // STEP 1: Reset klasifikasi untuk akun beban (5xxxxxx) agar bisa di-update ulang
        $resetCount = ChartOfAccount::where('kode_akun', '>=', '5000000')
            ->where('kode_akun', '<=', '5999999')
            ->whereIn('level_akun', ['SUB ACCOUNT', 'ACCOUNT'])
            ->update(['klasifikasi_id' => null]);
        echo "Reset {$resetCount} akun beban untuk update ulang...\n";
        
        // STEP 1b: Reset klasifikasi untuk akun piutang (1105xxx dan 1106xxx) 
        $resetPiutang = ChartOfAccount::where(function($q) {
                $q->whereBetween('kode_akun', ['1105000', '1106999']);
            })
            ->whereIn('level_akun', ['SUB ACCOUNT', 'ACCOUNT'])
            ->update(['klasifikasi_id' => null]);
        echo "Reset {$resetPiutang} akun piutang untuk update ulang...\n\n";
        
        // STEP 2: Mapping berdasarkan range kode akun
        // Format: [kode_awal, kode_akhir, nama_klasifikasi]
        // Nama klasifikasi sesuai dengan database (English names)
        $mappings = [
            // =============================================
            // ASET LANCAR (11xxxxx)
            // =============================================
            // Kas (termasuk parent 1101000)
            ['1101000', '1101999', 'Cash'],
            
            // Bank
            ['1102000', '1102999', 'Bank'],
            
            // Piutang Usaha (Accounts Receivable)
            ['1103000', '1103999', 'Accounts Receivable'],
            
            // Piutang Lain-lain (Other Receivables)
            ['1104000', '1104999', 'Other Receivables'],
            
            // Piutang Pihak Ketiga Lainnya (Other Receivables)
            ['1105000', '1105999', 'Other Receivables'],
            
            // Piutang Pihak Hub. Istimewa (Other Receivables)
            ['1106000', '1106999', 'Other Receivables'],
            
            // Piutang terhadap Yayasan (Other Receivables)
            ['1107000', '1107999', 'Other Receivables'],
            
            // Persediaan (Inventory) - 1108xxx
            ['1108000', '1108999', 'Inventory'],
            
            // Uang Muka (Other Current Asset)
            ['1109000', '1109999', 'Other Current Asset'],
            
            // Biaya Dibayar Dimuka (Other Current Asset) - 1110xxx
            ['1110000', '1110999', 'Other Current Asset'],
            
            // Pajak Dibayar Dimuka (Other Current Asset)
            ['1111000', '1111999', 'Other Current Asset'],
            
            // =============================================
            // ASET TETAP (12xxxxx)
            // =============================================
            // Aset Tetap (Capital Asset)
            ['1200000', '1209999', 'Capital Asset'],
            
            // Akumulasi Penyusutan (Accum. Amort./Depreciation)
            ['1210000', '1299999', 'Accum. Amort./Depreciation'],
            
            // =============================================
            // ASET TAK BERWUJUD / LAINNYA (13xxxxx)
            // =============================================
            // Biaya Ditangguhkan, Sistem, Aset Tak Berwujud, Proyek
            ['1300000', '1309999', 'Other Non-Current Asset'],
            
            // =============================================
            // KEWAJIBAN JANGKA PENDEK (21xxxxx)
            // =============================================
            // Hutang Usaha (Accounts Payable)
            ['2101000', '2101999', 'Accounts Payable'],
            
            // Hutang Lain-lain (Other Payable)
            ['2102000', '2199999', 'Other Payable'],
            
            // =============================================
            // KEWAJIBAN JANGKA PANJANG (22xxxxx)
            // =============================================
            // Kewajiban Bank/Pembiayaan/Lain JK Panjang
            ['2200000', '2299999', 'Long Term Debt'],
            
            // =============================================
            // MODAL / EKUITAS (3xxxxxx)
            // =============================================
            ['3100000', '3199999', 'Share Capital'],
            ['3200000', '3299999', 'Retained Earnings'],
            ['3300000', '3399999', 'Current Earnings'],
            ['3400000', '3999999', 'Owner/Partner Withdrawals'],
            
            // =============================================
            // PENDAPATAN (4xxxxxx)
            // =============================================
            ['4100000', '4199999', 'Operating Revenue'],
            
            // Pendapatan Lain (Other Revenue)
            ['4200000', '4999999', 'Other Revenue'],
            
            // =============================================
            // BEBAN (5xxxxxx) - DETAIL
            // =============================================
            
            // 5201xxx - Beban Penghasilan Karyawan (Gaji, THR, Bonus, Upah)
            ['5201000', '5201999', 'Payroll Expense'],
            
            // 5202xxx - Beban Keperluan Kantor (ATK, Fotocopy, Rumah Tangga)
            ['5202000', '5202999', 'General & Admin. Expense'],
            
            // 5203xxx - Biaya Utilitas (Listrik, Air, Telepon)
            ['5203000', '5203999', 'Operating Expense'],
            
            // 5204xxx - Biaya Transportasi (BBM, Parkir, Perjalanan Dinas)
            ['5204000', '5204999', 'Operating Expense'],
            
            // 5205xxx - Biaya Pemeliharaan & Perbaikan
            ['5205000', '5205999', 'Operating Expense'],
            
            // 5206xxx - Biaya Sewa
            ['5206000', '5206999', 'Operating Expense'],
            
            // 5208xxx - Beban Operasional Lainnya
            ['5208000', '5208999', 'Operating Expense'],
            
            // 5300xxx - Beban Administrasi dan Umum
            ['5300000', '5300999', 'General & Admin. Expense'],
            
            // 5301xxx - Beban Retribusi (Kebersihan, Keamanan, Iuran)
            ['5301000', '5301999', 'General & Admin. Expense'],
            
            // 5302xxx - Beban Perijinan & Pajak Daerah (PBB, Pajak Kendaraan)
            ['5302000', '5302999', 'General & Admin. Expense'],
            
            // 5303xxx - Beban Penyusutan
            ['5303000', '5303999', 'Amort./Depreciation Expense'],
            
            // 5304xxx - Beban Amortisasi
            ['5304000', '5304999', 'Amort./Depreciation Expense'],
            
            // 5305xxx - Beban Sosial (Zakat, Sedekah)
            ['5305000', '5305999', 'Non-Operating Expense'],
            
            // 5306xxx - Beban Keuangan (Admin Bank, Bunga Pinjaman)
            ['5306000', '5306999', 'Interest Expense'],
            
            // 5307xxx - Beban Pajak (PPH Final, PPH 21, PPN)
            ['5307000', '5307999', 'Income Tax Expense'],
            
            // 5308xxx - Jasa Profesional
            ['5308000', '5308999', 'General & Admin. Expense'],
            
            // 5309xxx - Rugi Atas Penjualan Aktiva
            ['5309000', '5309999', 'Loss'],
            
            // 5800xxx - Beban Pajak Penghasilan Badan
            ['5800000', '5899999', 'Income Tax Expense'],
            
            // =============================================
            // BEBAN OPERASIONAL (6xxxxxx) - jika ada
            // =============================================
            ['6000000', '6999999', 'Operating Expense'],
            
            // =============================================
            // BEBAN / PENDAPATAN LAIN (7xxxxxx - 8xxxxxx)
            // =============================================
            ['7000000', '7999999', 'Non-Operating Expense'],
            ['8000000', '8999999', 'Non-Operating Revenue'],
        ];
        
        $updated = 0;
        $skipped = 0;
        $notFound = [];
        
        foreach ($mappings as $map) {
            [$kodeAwal, $kodeAkhir, $namaKlasifikasi] = $map;
            
            if (!isset($klasifikasi[$namaKlasifikasi])) {
                $notFound[] = $namaKlasifikasi;
                continue;
            }
            
            $klasifikasiId = $klasifikasi[$namaKlasifikasi];
            
            // Update akun dalam range yang belum memiliki klasifikasi atau masih null
            $count = ChartOfAccount::where('kode_akun', '>=', $kodeAwal)
                ->where('kode_akun', '<=', $kodeAkhir)
                ->whereIn('level_akun', ['SUB ACCOUNT', 'ACCOUNT']) // Hanya update yang detail
                ->where(function($q) {
                    $q->whereNull('klasifikasi_id')
                      ->orWhere('klasifikasi_id', 0);
                })
                ->update(['klasifikasi_id' => $klasifikasiId]);
            
            $updated += $count;
        }
        
        // Report
        echo "=== MAPPING KLASIFIKASI AKUN SELESAI ===\n";
        echo "Total akun yang diupdate: {$updated}\n";
        
        if (!empty($notFound)) {
            echo "\nKlasifikasi tidak ditemukan di database:\n";
            foreach ($notFound as $nama) {
                echo "- {$nama}\n";
            }
        }
        
        // Show accounts still without classification
        $emptyCount = ChartOfAccount::whereNull('klasifikasi_id')
            ->whereIn('level_akun', ['SUB ACCOUNT', 'ACCOUNT'])
            ->count();
        echo "\nAkun yang masih belum terklasifikasi: {$emptyCount}\n";
    }
}

<?php
/**
 * Script Simulasi & Remediasi Rekonsiliasi Kas Riski (1101004) vs Web Internal 2026
 * Jalankan dengan:
 * php simulate_kas_riski_remediation.php --dry-run
 * php simulate_kas_riski_remediation.php --execute
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$isDryRun = !in_array('--execute', $argv);

echo "=======================================================================\n";
echo "  REMEDIASI REKONSILIASI KAS RISKI (1101004) - WEB INTERNAL 2026\n";
echo "  Mode: " . ($isDryRun ? "DRY-RUN (SIMULASI - Tidak Mengubah Data)" : "EXECUTE (MENGUBAH DATABASE)") . "\n";
echo "=======================================================================\n\n";

// 1. Cek Saldo Saat Ini
$initialStats = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
    ->where('jed.kode_akun', '1101004')
    ->selectRaw("
        SUM(jed.debits) as dr_all,
        SUM(jed.credits) as cr_all
    ")
    ->first();

$saldoAwal = $initialStats->dr_all - $initialStats->cr_all;
$targetSaldoExcel = -2114844.0;

echo "[STATUS SAAT INI]\n";
echo "  - Total Debit di Web     : Rp " . number_format($initialStats->dr_all, 2, ',', '.') . "\n";
echo "  - Total Kredit di Web    : Rp " . number_format($initialStats->cr_all, 2, ',', '.') . "\n";
echo "  - Saldo Live Web Saat Ini: Rp " . number_format($saldoAwal, 2, ',', '.') . "\n";
echo "  - Target Saldo Excel     : Rp " . number_format($targetSaldoExcel, 2, ',', '.') . "\n";
echo "  - Selisih yang Dibenahi  : Rp " . number_format($saldoAwal - $targetSaldoExcel, 2, ',', '.') . "\n\n";

DB::beginTransaction();
try {
    // Helper function untuk resolve departemen_akun_id (PUSAT BANDUNG)
    $deptPusat = DB::table('departements')->where('deskripsi', 'PUSAT BANDUNG')->first();
    $deptId = $deptPusat ? $deptPusat->id : 1;

    $getDeptAkunId = function($kodeAkun) use ($deptId) {
        $coa = DB::table('chart_of_accounts')->where('kode_akun', $kodeAkun)->first();
        if (!$coa) return null;
        $da = DB::table('departemen_akuns')
            ->where('departemen_id', $deptId)
            ->where('akun_id', $coa->id)
            ->first();
        if ($da) return $da->id;
        return DB::table('departemen_akuns')->insertGetId([
            'departemen_id' => $deptId,
            'akun_id' => $coa->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    };

    // A. REKLASIFIKASI BEBAN PRIBADI & NON-OPERASIONAL (Rp 32.958.100,00)
    echo "[1] Reklasifikasi Pos Pribadi & Non-Operasional (Gaji, THR, Uang Makan, Kasbon, Admin)...\n";
    
    // 1. Gaji Riski (5301001)
    $gajiVouchers = ['BCA5366-260201-004', 'BCA5366-260301-003'];
    $daGaji = $getDeptAkunId('5301001');
    $updGaji = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->whereIn('je.source', $gajiVouchers)
        ->where('jed.kode_akun', '1101004')
        ->update(['jed.kode_akun' => '5301001', 'jed.departemen_akun_id' => $daGaji, 'jed.updated_at' => now()]);
    echo "    -> Gaji Riski (5301001): {$updGaji} transaksi (Rp 10.380.600,00).\n";

    // 2. THR Riski (5301004)
    $thrVouchers = ['BCA5366-260314-001'];
    $daThr = $getDeptAkunId('5301004');
    $updThr = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->whereIn('je.source', $thrVouchers)
        ->where('jed.kode_akun', '1101004')
        ->update(['jed.kode_akun' => '5301004', 'jed.departemen_akun_id' => $daThr, 'jed.updated_at' => now()]);
    echo "    -> THR Riski (5301004): {$updThr} transaksi (Rp 5.500.000,00).\n";

    // 3. Kasbon Sekolah Riski (1105002 - Piutang Karyawan)
    $kasbonVouchers = ['BCA5366-260707-001'];
    $daKasbon = $getDeptAkunId('1105002');
    $updKasbon = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->whereIn('je.source', $kasbonVouchers)
        ->where('jed.kode_akun', '1101004')
        ->update(['jed.kode_akun' => '1105002', 'jed.departemen_akun_id' => $daKasbon, 'jed.updated_at' => now()]);
    echo "    -> Kasbon Sekolah Riski (1105002): {$updKasbon} transaksi (Rp 15.000.000,00).\n";

    // 4. Uang Makan Mingguan Riski (5301001)
    $uangMakanVouchers = [
        'BCA5366-260113-001', 'BCA5366-260120-001', 'BCA5366-260126-003',
        'BCA5366-260209-001', 'BCA5366-260216-001', 'BCA5366-260223-001',
        'BCA5366-260309-001', 'BCA5366-260316-001'
    ];
    $updMakan = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->whereIn('je.source', $uangMakanVouchers)
        ->where('jed.kode_akun', '1101004')
        ->update(['jed.kode_akun' => '5301001', 'jed.departemen_akun_id' => $daGaji, 'jed.updated_at' => now()]);
    echo "    -> Uang Makan Riski (5301001): {$updMakan} transaksi (Rp 2.060.000,00).\n";

    // 5. Biaya Admin BI-Fast (5316002)
    $bifastVouchers = [
        'KASFIR-260103-005', 'KASFIR-260108-002', 'KASFIR-260110-015',
        'KASFIR-260323-002', 'KASFIR-260504-002', 'KASFIR-260615-002',
        'KASFIR-260704-003'
    ];
    $daAdmin = $getDeptAkunId('5316002');
    $updAdmin = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->whereIn('je.source', $bifastVouchers)
        ->where('jed.kode_akun', '1101004')
        ->update(['jed.kode_akun' => '5316002', 'jed.departemen_akun_id' => $daAdmin, 'jed.updated_at' => now()]);
    echo "    -> Admin BI-Fast (5316002): {$updAdmin} transaksi (Rp 17.500,00).\n";

    // B. ELIMINASI DUPLIKASI MUTASI ANTAR KAS/BANK (Rp 16.000.000,00)
    echo "\n[2] Menghapus Jurnal Duplikasi Transfer Antar Kas/Bank (9 Voucher)...\n";
    $dupVouchers = [
        'KASRIS-260122-001', // 22/01: Rp 1.000.000 (sudah di BCA5366-260121-001)
        'KASBDG-260124-001', // 24/01: Rp 500.000 (sudah di KASRIS-260124-002)
        'BCA5366-260214-001', // 14/02: Rp 1.500.000 (tidak ada di Excel Kas Riski Feb)
        'KASRIS-260414-001', // 14/04: Rp 5.000.000 (sudah di BCA5366-260414-001)
        'KASRIS-260512-001', // 12/05: Rp 1.000.000 (sudah di BCA5366-260512-002)
        'KASRIS-260519-001', // 19/05: Rp 1.500.000 (sudah di BCA5366-260519-001)
        'KASRIS-260525-001', // 25/05: Rp 2.000.000 (sudah di BCA5366-260525-005)
        'KASRIS-260728-001', // 28/07: Rp 1.500.000 (sudah di BCA5366-260728-001)
        'KASRIS-260804-005', // 04/08: Rp 2.000.000 (sudah di BCA5366-260804-001)
    ];

    foreach ($dupVouchers as $src) {
        $entry = DB::table('journal_entries')->where('source', $src)->first();
        if ($entry) {
            $cDetails = DB::table('journal_entry_details')->where('journal_entry_id', $entry->id)->delete();
            DB::table('journal_entries')->where('id', $entry->id)->delete();
            echo "    -> Dieliminasi: {$src} ({$cDetails} baris detail).\n";
        }
    }

    // C. VALIDASI SALDO PASCA REMEDIASI
    $afterStats = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->where('jed.kode_akun', '1101004')
        ->selectRaw("
            SUM(jed.debits) as dr_all,
            SUM(jed.credits) as cr_all
        ")
        ->first();

    $saldoAkhir = $afterStats->dr_all - $afterStats->cr_all;

    echo "\n=======================================================================\n";
    echo "  HASIL PASCA REMEDIASI:\n";
    echo "=======================================================================\n";
    echo "  - Total Debit di Web     : Rp " . number_format($afterStats->dr_all, 2, ',', '.') . "\n";
    echo "  - Total Kredit di Web    : Rp " . number_format($afterStats->cr_all, 2, ',', '.') . "\n";
    echo "  - Saldo Live Web Akhir   : Rp " . number_format($saldoAkhir, 2, ',', '.') . "\n";
    echo "  - Target Saldo Excel     : Rp " . number_format($targetSaldoExcel, 2, ',', '.') . "\n";
    echo "  - Sisa Selisih           : Rp " . number_format($saldoAkhir - $targetSaldoExcel, 2, ',', '.') . " ";
    if (abs($saldoAkhir - $targetSaldoExcel) < 0.01) {
        echo "[PERFECT MATCH! (0,00)]\n";
    } else {
        echo "[MASIH ADA SELISIH]\n";
    }
    echo "=======================================================================\n";

    if ($isDryRun) {
        DB::rollBack();
        echo "\n[SIMULASI SELESAI] Semua perubahan telah di-ROLLBACK. Tidak ada data yang berubah.\n";
        echo "Untuk menerapkan perubahan secara permanen ke database, jalankan:\n";
        echo "  & \"C:\\laragon\\bin\\php\\php-7.4.33-Win32-vc15-x64\\php.exe\" simulate_kas_riski_remediation.php --execute\n";
    } else {
        DB::commit();
        echo "\n[EKSEKUSI BERHASIL] Database telah berhasil diperbarui dan disinkronkan!\n";
    }
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n[ERROR] Terjadi kegagalan: " . $e->getMessage() . "\n";
}

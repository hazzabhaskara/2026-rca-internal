<?php
/**
 * Script Simulasi & Remediasi Rekonsiliasi Kas Firman (1101003) vs Web Internal 2026
 * Jalankan dengan:
 * php simulate_kas_firman_remediation.php --dry-run
 * php simulate_kas_firman_remediation.php --execute
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$isDryRun = !in_array('--execute', $argv);

echo "=======================================================================\n";
echo "  REMEDIASI REKONSILIASI KAS FIRMAN (1101003) - WEB INTERNAL 2026\n";
echo "  Mode: " . ($isDryRun ? "DRY-RUN (SIMULASI - Tidak Mengubah Data)" : "EXECUTE (MENGUBAH DATABASE)") . "\n";
echo "=======================================================================\n\n";

// 1. Cek Saldo Awal & Saldo Berjalan Saat Ini
$initialStats = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
    ->where('jed.kode_akun', '1101003')
    ->selectRaw("
        SUM(CASE WHEN je.tanggal <= '2026-07-31' THEN jed.debits ELSE 0 END) as dr_jul,
        SUM(CASE WHEN je.tanggal <= '2026-07-31' THEN jed.credits ELSE 0 END) as cr_jul,
        SUM(jed.debits) as dr_all,
        SUM(jed.credits) as cr_all
    ")
    ->first();

$saldoJulAwal = $initialStats->dr_jul - $initialStats->cr_jul;
$saldoAllAwal = $initialStats->dr_all - $initialStats->cr_all;

echo "[STATUS SAAT INI]\n";
echo "  - Saldo s/d 31 Juli 2026 : Rp " . number_format($saldoJulAwal, 2, ',', '.') . "\n";
echo "  - Target Saldo Excel     : Rp 33.040.889,00\n";
echo "  - Selisih                : Rp " . number_format($saldoJulAwal - 33040889, 2, ',', '.') . "\n";
echo "  - Saldo Live Web Semua   : Rp " . number_format($saldoAllAwal, 2, ',', '.') . "\n\n";

DB::beginTransaction();
try {
    // A. Hapus Duplikat Voucher Sistem: BCA5366-260101-011 (ID 85321)
    echo "[1] Menghapus Voucher Duplikat Sistem (BCA5366-260101-011 ID 85321)...\n";
    $delDetailsA = DB::table('journal_entry_details')->where('journal_entry_id', 85321)->delete();
    $delEntryA   = DB::table('journal_entries')->where('id', 85321)->delete();
    echo "    -> Berhasil menghapus {$delDetailsA} details dan {$delEntryA} entry.\n";

    // B. Eliminasi Duplikasi Mutasi Antar Kas/Bank
    echo "[2] Menghapus Jurnal Duplikasi Transfer Antar Kas/Bank...\n";
    $dupVouchers = [
        'MDR9999-260201-001', // Transfer Mandiri Pesona -> Kas Firman (10jt ganda)
        'MDR9999-260201-005', 
        'BCA5366-260201-001', // Transfer Kas Firman -> BCA Yerry (25jt ganda)
        'KASRIS-260301-004',  // Transfer Kas Firman -> Kas Riski (1.5jt ganda)
        'BCA5366-260602-001', // Transfer BCA Yerry -> Kas Firman (130rb ganda)
    ];

    foreach ($dupVouchers as $src) {
        $entry = DB::table('journal_entries')->where('source', $src)->first();
        if ($entry) {
            $cDetails = DB::table('journal_entry_details')->where('journal_entry_id', $entry->id)->delete();
            DB::table('journal_entries')->where('id', $entry->id)->delete();
            echo "    -> Dieliminasi: {$src} ({$cDetails} baris detail).\n";
        }
    }

    // C. Reklasifikasi Salah Akun KASBDG (Dividen & Gaji)
    echo "[3] Reklasifikasi Jurnal Salah Kamar dari KASBDG ke Dividen & Gaji...\n";
    $coaDividen = DB::table('chart_of_accounts')->where('kode_akun', '3103000')->first();
    $coaGaji    = DB::table('chart_of_accounts')->where('kode_akun', '5301001')->first();
    $coaOps     = DB::table('chart_of_accounts')->where('kode_akun', '5305001')->first();

    // 1. Dividen
    $dividenVouchers = [
        'KASBDG-260228-007', 'KASBDG-260502-010', 'KASBDG-260602-007',
        'KASBDG-260630-008', 'KASBDG-260727-009', 'KASBDG-260905-002'
    ];
    $updDiv = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->whereIn('je.source', $dividenVouchers)
        ->where('jed.kode_akun', '1101003')
        ->update(['jed.kode_akun' => '3103000', 'jed.updated_at' => now()]);
    echo "    -> Reklasifikasi ke 3103000 (DIVIDEN): {$updDiv} transaksi.\n";

    // 2. Gaji & Honor
    $gajiVouchers = [
        'KASBDG-260228-008', 'KASBDG-260228-009', 'KASBDG-260602-008',
        'KASBDG-260630-009', 'KASBDG-260727-010', 'BCA5366-260701-001'
    ];
    $updGaji = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->whereIn('je.source', $gajiVouchers)
        ->where('jed.kode_akun', '1101003')
        ->update(['jed.kode_akun' => '5301001', 'jed.updated_at' => now()]);
    echo "    -> Reklasifikasi ke 5301001 (GAJI): {$updGaji} transaksi.\n";

    // 3. Tiket Masuk Dufan & Ancol (BNI7884)
    $tiketVouchers = ['BNI7884-260614-001', 'BNI7884-260614-003'];
    $updTiket = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->whereIn('je.source', $tiketVouchers)
        ->where('jed.kode_akun', '1101003')
        ->update(['jed.kode_akun' => '5305001', 'jed.updated_at' => now()]);
    echo "    -> Reklasifikasi ke 5305001 (BEBAN OPS): {$updTiket} transaksi.\n";

    // Hitung Saldo Pasca Perbaikan
    $afterStats = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->where('jed.kode_akun', '1101003')
        ->selectRaw("
            SUM(CASE WHEN je.tanggal <= '2026-07-31' THEN jed.debits ELSE 0 END) as dr_jul,
            SUM(CASE WHEN je.tanggal <= '2026-07-31' THEN jed.credits ELSE 0 END) as cr_jul,
            SUM(jed.debits) as dr_all,
            SUM(jed.credits) as cr_all
        ")
        ->first();

    $saldoJulAkhir = $afterStats->dr_jul - $afterStats->cr_jul;
    $saldoAllAkhir = $afterStats->dr_all - $afterStats->cr_all;

    echo "\n=======================================================================\n";
    echo "  HASIL PASCA REMEDIASI:\n";
    echo "=======================================================================\n";
    echo "  - Saldo s/d 31 Juli 2026 : Rp " . number_format($saldoJulAkhir, 2, ',', '.') . "\n";
    echo "  - Target Saldo Excel     : Rp 33.040.889,00\n";
    echo "  - Sisa Selisih           : Rp " . number_format($saldoJulAkhir - 33040889, 2, ',', '.') . " ";
    if (abs($saldoJulAkhir - 33040889) < 0.01) {
        echo "[PERFECT MATCH! (0,00)]\n";
    } else {
        echo "[MASIH ADA SELISIH]\n";
    }
    echo "  - Saldo Live Web Semua   : Rp " . number_format($saldoAllAkhir, 2, ',', '.') . "\n";
    echo "=======================================================================\n";

    if ($isDryRun) {
        DB::rollBack();
        echo "\n[SIMULASI SELESAI] Semua perubahan telah di-ROLLBACK. Tidak ada data yang berubah.\n";
        echo "Untuk menerapkan perubahan secara permanen, jalankan:\n";
        echo "  php simulate_kas_firman_remediation.php --execute\n";
    } else {
        DB::commit();
        echo "\n[EKSEKUSI BERHASIL] Database telah berhasil diperbarui dan disinkronkan!\n";
    }
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n[ERROR] Terjadi kegagalan: " . $e->getMessage() . "\n";
}

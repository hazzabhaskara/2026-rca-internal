<?php
/**
 * Script Simulasi & Remediasi Rekonsiliasi Mandiri Pesona (1102008) vs Web Internal 2026
 *
 * Jalankan dengan:
 * C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe simulate_mandiri_pesona_remediation.php --dry-run
 * C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe simulate_mandiri_pesona_remediation.php --execute
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$isDryRun = !in_array('--execute', $argv);

echo "=======================================================================\n";
echo "  REMEDIASI REKONSILIASI MANDIRI PESONA (1102008) - WEB INTERNAL 2026\n";
echo "  Mode: " . ($isDryRun ? "DRY-RUN (SIMULASI - Tidak Mengubah Data)" : "EXECUTE (MENGUBAH DATABASE)") . "\n";
echo "=======================================================================\n\n";

function getDeptAkunId($kodeAkun, $deptId = 1) {
    $val = DB::table('departemen_akuns as da')
        ->join('chart_of_accounts as ca', 'da.akun_id', '=', 'ca.id')
        ->where('ca.kode_akun', $kodeAkun)
        ->where('da.departemen_id', $deptId)
        ->value('da.id');
    if (!$val) {
        throw new \Exception("departemen_akuns.id tidak ditemukan untuk kode_akun {$kodeAkun} dan departemen {$deptId}!");
    }
    return $val;
}

// 1. Cek Saldo Awal & Saldo Berjalan Saat Ini
$initialStats = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
    ->where('jed.kode_akun', '1102008')
    ->selectRaw("
        SUM(jed.debits) as total_debits,
        SUM(jed.credits) as total_credits,
        SUM(jed.debits - jed.credits) as net_balance
    ")
    ->first();

$saldoAwal = $initialStats->net_balance;
$targetExcel = 56587811.60;
$selisihAwal = $saldoAwal - $targetExcel;

echo "[STATUS AWAL LIVE DATABASE]\n";
echo "  - Total Debits        : Rp " . number_format($initialStats->total_debits, 2, ',', '.') . "\n";
echo "  - Total Credits       : Rp " . number_format($initialStats->total_credits, 2, ',', '.') . "\n";
echo "  - Saldo Live Saat Ini : Rp " . number_format($saldoAwal, 2, ',', '.') . "\n";
echo "  - Target Saldo Excel  : Rp " . number_format($targetExcel, 2, ',', '.') . "\n";
echo "  - Selisih Awal        : Rp " . number_format($selisihAwal, 2, ',', '.') . "\n\n";

DB::beginTransaction();
try {
    // -------------------------------------------------------------------------
    // PILAR 1: Koreksi Saldo Awal Neraca 2025 (NRC-20251231-01)
    // -------------------------------------------------------------------------
    echo "[1] Mengoreksi Saldo Awal Neraca 2025 (NRC-20251231-01)...\n";
    $updSA = DB::table('journal_entry_details')
        ->where('id', 272054)
        ->where('kode_akun', '1102008')
        ->update(['debits' => 1123216.22, 'updated_at' => now()]);

    $updContraSA = DB::table('journal_entry_details')
        ->where('id', 272088)
        ->where('kode_akun', '3102000')
        ->update(['credits' => 2491176226.33, 'updated_at' => now()]);
    echo "    -> Saldo awal 1102008 disesuaikan menjadi Rp 1.123.216,22 ({$updSA} baris, contra {$updContraSA} baris).\n";

    // -------------------------------------------------------------------------
    // PILAR 2: Eliminasi Duplikasi Sistem BCA Yerry Januari (ID 85319)
    // -------------------------------------------------------------------------
    echo "[2] Menghapus Voucher Duplikat Sistem (BCA5366-260101-009 ID 85319)...\n";
    $delD2 = DB::table('journal_entry_details')->where('journal_entry_id', 85319)->delete();
    $delE2 = DB::table('journal_entries')->where('id', 85319)->delete();
    echo "    -> Dieliminasi ID 85319 ({$delD2} detail, {$delE2} entry).\n";

    // -------------------------------------------------------------------------
    // PILAR 3: Reklasifikasi Salah Akun Contra Omset LZ Kas Firman (Januari)
    // -------------------------------------------------------------------------
    echo "[3] Reklasifikasi Kredit Siluman Omset LZ di Kas Firman (KASFIR Jan)...\n";
    $deptAkun1108001 = getDeptAkunId('1108001', 1);
    $kasfirVouchers = [
        'KASFIR-260110-001', 'KASFIR-260110-002', 'KASFIR-260110-003',
        'KASFIR-260110-004', 'KASFIR-260110-005',
        'KASFIR-260123-001', 'KASFIR-260123-002'
    ];
    $updKasfir = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->whereIn('je.source', $kasfirVouchers)
        ->where('jed.kode_akun', '1102008')
        ->where('jed.credits', '>', 0)
        ->update([
            'jed.kode_akun' => '1108001',
            'jed.departemen_akun_id' => $deptAkun1108001,
            'jed.updated_at' => now()
        ]);
    echo "    -> {$updKasfir} baris kredit direklasifikasi dari 1102008 ke 1108001 (Total: Rp 40.700.000,00).\n";

    // -------------------------------------------------------------------------
    // PILAR 4: Koreksi Rekening Mutasi Pinjaman Iman (KASFIR-260126-001 ID 68183)
    // -------------------------------------------------------------------------
    echo "[4] Memperbaiki Alokasi Mutasi Pinjaman Iman (KASFIR-260126-001)...\n";
    $deptAkun1102008 = getDeptAkunId('1102008', 1);
    $updPinjaman = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->where('je.source', 'KASFIR-260126-001')
        ->where('jed.kode_akun', '1104001')
        ->where('jed.debits', 20000000.00)
        ->update([
            'jed.kode_akun' => '1102008',
            'jed.departemen_akun_id' => $deptAkun1102008,
            'jed.updated_at' => now()
        ]);
    echo "    -> {$updPinjaman} baris debit Rp 20.000.000 dialokasikan ke 1102008.\n";

    // -------------------------------------------------------------------------
    // PILAR 5: Reklasifikasi Biaya BI-FAST Kas Firman (Maret & Juli)
    // -------------------------------------------------------------------------
    echo "[5] Reklasifikasi Biaya BI-FAST Kas Firman ke Beban Admin Bank (5316002)...\n";
    $deptAkun5316002 = getDeptAkunId('5316002', 1);
    $bifastVouchers = [
        'KASFIR-260305-002',
        'KASFIR-260705-002',
        'KASFIR-260715-003',
        'KASFIR-260718-002'
    ];
    $updBifast = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->whereIn('je.source', $bifastVouchers)
        ->where('jed.kode_akun', '1102008')
        ->where('jed.debits', 2500.00)
        ->update([
            'jed.kode_akun' => '5316002',
            'jed.departemen_akun_id' => $deptAkun5316002,
            'jed.updated_at' => now()
        ]);
    echo "    -> {$updBifast} transaksi BI-FAST @Rp 2.500 dipindahkan ke 5316002.\n";

    // -------------------------------------------------------------------------
    // PILAR 6: Eliminasi Duplikasi Mutasi BCA Yerry April (BCA5366-260406-006 ID 73027)
    // -------------------------------------------------------------------------
    echo "[6] Menghapus Duplikasi Mutasi BCA Yerry April (BCA5366-260406-006)...\n";
    $delD6 = DB::table('journal_entry_details')->where('journal_entry_id', 73027)->delete();
    $delE6 = DB::table('journal_entries')->where('id', 73027)->delete();
    echo "    -> Dieliminasi ID 73027 ({$delD6} detail, {$delE6} entry) - Rp 10.000.000,00.\n";

    // -------------------------------------------------------------------------
    // PILAR 7: Eliminasi Duplikasi Mutasi BCA Yerry Mei (MDR9999-260523-001 ID 76766)
    // -------------------------------------------------------------------------
    echo "[7] Menghapus Duplikasi Mutasi BCA Yerry Mei (MDR9999-260523-001)...\n";
    $delD7 = DB::table('journal_entry_details')->where('journal_entry_id', 76766)->delete();
    $delE7 = DB::table('journal_entries')->where('id', 76766)->delete();
    echo "    -> Dieliminasi ID 76766 ({$delD7} detail, {$delE7} entry) - Rp 30.000.000,00.\n";

    // -------------------------------------------------------------------------
    // PILAR 8: Koreksi Alokasi Kas Bandung Juni (KASBDG-260602-009 ID 77563)
    // -------------------------------------------------------------------------
    echo "[8] Memperbaiki Alokasi Kas Bandung Juni (KASBDG-260602-009)...\n";
    $updKasbdgJun = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->where('je.source', 'KASBDG-260602-009')
        ->where('jed.kode_akun', '1102008')
        ->where('jed.debits', 20000000.00)
        ->update([
            'jed.kode_akun' => '1108001',
            'jed.departemen_akun_id' => $deptAkun1108001,
            'jed.updated_at' => now()
        ]);
    echo "    -> {$updKasbdgJun} baris debit Rp 20.000.000 dialihkan ke 1108001 (Kas Mumu LZ).\n";

    // -------------------------------------------------------------------------
    // PILAR 9: Eliminasi Duplikasi Setoran Kas Dewi Juli (MDR9999-260727-003 ID 82064)
    // -------------------------------------------------------------------------
    echo "[9] Menghapus Duplikasi Setoran Kas Dewi Juli (MDR9999-260727-003)...\n";
    $delD9 = DB::table('journal_entry_details')->where('journal_entry_id', 82064)->delete();
    $delE9 = DB::table('journal_entries')->where('id', 82064)->delete();
    echo "    -> Dieliminasi ID 82064 ({$delD9} detail, {$delE9} entry) - Rp 69.000.000,00.\n";

    // -------------------------------------------------------------------------
    // PILAR 10: Eliminasi Duplikasi Setoran Kas Dewi September (MDR9999-260901-001 ID 84921)
    // -------------------------------------------------------------------------
    echo "[10] Menghapus Duplikasi Setoran Kas Dewi September (MDR9999-260901-001)...\n";
    $delD10 = DB::table('journal_entry_details')->where('journal_entry_id', 84921)->delete();
    $delE10 = DB::table('journal_entries')->where('id', 84921)->delete();
    echo "     -> Dieliminasi ID 84921 ({$delD10} detail, {$delE10} entry) - Rp 52.000.000,00.\n";

    // -------------------------------------------------------------------------
    // VERIFIKASI HASIL PASCA REMEDIASI
    // -------------------------------------------------------------------------
    $afterStats = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'jed.journal_entry_id', '=', 'je.id')
        ->where('jed.kode_akun', '1102008')
        ->selectRaw("
            SUM(jed.debits) as total_debits,
            SUM(jed.credits) as total_credits,
            SUM(jed.debits - jed.credits) as net_balance
        ")
        ->first();

    $saldoAkhir = $afterStats->net_balance;
    $sisaSelisih = $saldoAkhir - $targetExcel;

    echo "\n[HASIL PASCA REMEDIASI]\n";
    echo "  - Total Debits        : Rp " . number_format($afterStats->total_debits, 2, ',', '.') . "\n";
    echo "  - Total Credits       : Rp " . number_format($afterStats->total_credits, 2, ',', '.') . "\n";
    echo "  - Saldo Akhir Web     : Rp " . number_format($saldoAkhir, 2, ',', '.') . "\n";
    echo "  - Target Saldo Excel  : Rp " . number_format($targetExcel, 2, ',', '.') . "\n";
    echo "  - Sisa Selisih        : Rp " . number_format($sisaSelisih, 2, ',', '.') . "\n";

    if (abs($sisaSelisih) < 0.01) {
        echo "  >>> STATUS: PERFECT MATCH! Selisih = Rp 0,00 (100% KLOP) <<<\n\n";
    } else {
        throw new \Exception("Perhitungan selisih belum Rp 0,00! Masih ada selisih: " . $sisaSelisih);
    }

    // Cek Keseimbangan Jurnal Seluruh Database (Double-entry balance check)
    $dbBalance = DB::table('journal_entry_details')
        ->selectRaw("
            SUM(debits) as total_dr,
            SUM(credits) as total_cr,
            ROUND(SUM(debits) - SUM(credits), 2) as diff
        ")
        ->first();

    echo "[AUDIT TRIAL BALANCE SELURUH DATABASE]\n";
    echo "  - Total Debits  : Rp " . number_format($dbBalance->total_dr, 2, ',', '.') . "\n";
    echo "  - Total Credits : Rp " . number_format($dbBalance->total_cr, 2, ',', '.') . "\n";
    echo "  - Balance Diff  : Rp " . number_format($dbBalance->diff, 2, ',', '.') . "\n";

    if (abs($dbBalance->diff) > 0.01) {
        throw new \Exception("Database tidak seimbang! Balance diff = " . $dbBalance->diff);
    }
    echo "  >>> STATUS: SELURUH DATABASE SEIMBANG (DR == CR) <<<\n\n";

    // Cek Departemen Assignment untuk akun hasil reklasifikasi
    $reclassAkuns = ['1108001', '5316002', '1102008'];
    echo "[AUDIT DEPARTEMEN ASSIGNMENT]\n";
    foreach ($reclassAkuns as $acc) {
        $unassigned = DB::table('journal_entry_details')
            ->where('kode_akun', $acc)
            ->where(function($q) {
                $q->whereNull('departemen_akun_id')->orWhere('departemen_akun_id', 0);
            })
            ->count();
        echo "  - Akun {$acc} unassigned count: {$unassigned}\n";
        if ($unassigned > 0) {
            throw new \Exception("Ada akun {$acc} yang belum memiliki departemen!");
        }
    }
    echo "  >>> STATUS: SEMUA BARIS TER-ASSIGN KE DEPARTEMEN DENGAN BENAR <<<\n\n";

    if ($isDryRun) {
        DB::rollBack();
        echo "[DRY-RUN SELESAI] Transaksi di-rollback. Tidak ada data yang diubah di database.\n";
    } else {
        DB::commit();
        echo "[EXECUTE BERHASIL] Seluruh perbaikan telah disimpan permanen ke database.\n";
    }

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n[ERROR] Remediasi Gagal: " . $e->getMessage() . "\n";
    exit(1);
}

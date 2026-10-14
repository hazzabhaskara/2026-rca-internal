<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->whereIn('jed.kode_akun', ['5305000', '5305001'])
    ->whereYear('je.tanggal', 2026)
    ->get(['jed.id', 'je.tanggal', 'jed.comment', 'je.comment as je_comment', 'jed.debits']);

$remaining = [];
foreach ($rows as $r) {
    $desc = strtolower(($r->comment ?? '') . ' ' . ($r->je_comment ?? ''));

    // Check if matched previous categories
    if (preg_match('/setor\s+omset|setoran\s+omset/i', $desc)) continue;
    if (preg_match('/keluar\s+ke\s+(bni|bri|bca|mandiri|cash\s*ops)/i', $desc)) continue;
    if (preg_match('/listrik|pln|token/i', $desc)) continue;
    if (preg_match('/b\.\s*transfer|biaya\s*transfer|cetak\s*rek|cetak\s*koran|ganti\s*kartu|charge\s*kartu|insuf\s*fund/i', $desc)) continue;
    if (preg_match('/kartu\s*ancol|kartu\s*bulanan/i', $desc)) continue;

    $remaining[] = $r;
}

echo "Total remaining transactions in 5305001: " . count($remaining) . "\n";
$totalDebit = array_sum(array_map(fn($x) => (float)$x->debits, $remaining));
echo "Total Debit of remaining: Rp " . number_format($totalDebit, 0) . "\n\n";

// Group remaining by keyword clusters
$clusters = [];
foreach ($remaining as $r) {
    $desc = strtolower(trim($r->comment));
    $cluster = 'Lain-lain';

    if (preg_match('/ribbon|frame|kertas|tinta|catridge/i', $desc)) {
        $cluster = 'Bahan Baku / Supplies Cetak Foto (Ribbon/Frame/Kertas)';
    } elseif (preg_match('/kamera|lensa|printer|pc|komputer|laptop|monitor|booth|moving\s*booth|photobox/i', $desc)) {
        $cluster = 'Peralatan / Aset / Mesin (Kamera/Lensa/Printer/PC/Booth)';
    } elseif (preg_match('/kabel|paku|baut|lem|pipa|tplink|tp-link|saklar|stopkontak|engsel|grendel|cat|kuas|lakban/i', $desc)) {
        $cluster = 'Perlengkapan Teknis / Alat / Sparepart Kecil';
    } elseif (preg_match('/service|servis|perbaikan|pemeliharaan|ganti\s*sparepart/i', $desc)) {
        $cluster = 'Pemeliharaan & Perbaikan';
    } elseif (preg_match('/sampah|iuran|kebersihan|keamanan|retribusi/i', $desc)) {
        $cluster = 'Iuran, Sampah & Retribusi';
    } elseif (preg_match('/gaji|honor|u\.\s*mkn|uang\s*makan|fee|bonus|lembur/i', $desc)) {
        $cluster = 'Gaji, Honor & Uang Makan';
    } elseif (preg_match('/sewa/i', $desc)) {
        $cluster = 'Sewa';
    } elseif (preg_match('/aplikasi|software|domain|hosting|rumah\s*web|server/i', $desc)) {
        $cluster = 'Software, Domain & Langganan IT';
    } elseif (preg_match('/atk|fotokopi|buku|map|spidol|pulpen|kuitansi/i', $desc)) {
        $cluster = 'ATK & Fotokopi';
    } elseif (preg_match('/baju|seragam|rompi|id\s*card/i', $desc)) {
        $cluster = 'Seragam & Perlengkapan Karyawan';
    }

    if (!isset($clusters[$cluster])) {
        $clusters[$cluster] = ['count' => 0, 'sum' => 0, 'samples' => []];
    }
    $clusters[$cluster]['count']++;
    $clusters[$cluster]['sum'] += (float)$r->debits;
    if (count($clusters[$cluster]['samples']) < 3) {
        $clusters[$cluster]['samples'][] = "[{$r->tanggal}] Rp " . number_format($r->debits, 0) . " - " . $r->comment;
    }
}

uasort($clusters, fn($a, $b) => $b['sum'] <=> $a['sum']);
foreach ($clusters as $k => $v) {
    echo "=== {$k} ===\n";
    echo "Count: {$v['count']} | Total: Rp " . number_format($v['sum'], 0) . "\n";
    echo "Samples:\n  - " . implode("\n  - ", $v['samples']) . "\n\n";
}

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
    ->get(['jed.id', 'je.source', 'je.tanggal', 'jed.comment', 'jed.debits']);

$lainLain = [];
foreach ($rows as $r) {
    $desc = strtolower(trim($r->comment));
    if (preg_match('/setor\s+omset|setoran\s+omset/i', $desc)) continue;
    if (preg_match('/keluar\s+ke\s+(bni|bri|bca|mandiri|cash\s*ops)/i', $desc)) continue;
    if (preg_match('/listrik|pln|token/i', $desc)) continue;
    if (preg_match('/b\.\s*transfer|biaya\s*transfer|cetak\s*rek|cetak\s*koran|ganti\s*kartu|charge\s*kartu|insuf\s*fund/i', $desc)) continue;
    if (preg_match('/kartu\s*ancol|kartu\s*bulanan/i', $desc)) continue;
    if (preg_match('/ribbon|frame|kertas|tinta|catridge/i', $desc)) continue;
    if (preg_match('/kamera|lensa|printer|pc|komputer|laptop|monitor|booth|moving\s*booth|photobox/i', $desc)) continue;
    if (preg_match('/kabel|paku|baut|lem|pipa|tplink|tp-link|saklar|stopkontak|engsel|grendel|cat|kuas|lakban/i', $desc)) continue;
    if (preg_match('/service|servis|perbaikan|pemeliharaan|ganti\s*sparepart/i', $desc)) continue;
    if (preg_match('/sampah|iuran|kebersihan|keamanan|retribusi/i', $desc)) continue;
    if (preg_match('/gaji|honor|u\.\s*mkn|uang\s*makan|fee|bonus|lembur/i', $desc)) continue;
    if (preg_match('/sewa/i', $desc)) continue;
    if (preg_match('/aplikasi|software|domain|hosting|rumah\s*web|server/i', $desc)) continue;
    if (preg_match('/atk|fotokopi|buku|map|spidol|pulpen|kuitansi/i', $desc)) continue;
    if (preg_match('/baju|seragam|rompi|id\s*card/i', $desc)) continue;

    $lainLain[] = $r;
}

// Sort by debit descending
usort($lainLain, fn($a, $b) => (float)$b->debits <=> (float)$a->debits);

echo "Total Lain-lain: " . count($lainLain) . "\n";
echo "=== TOP 30 LARGEST IN LAIN-LAIN ===\n";
for ($i = 0; $i < min(30, count($lainLain)); $i++) {
    $item = $lainLain[$i];
    echo sprintf("[%s] ID: %d | Source: %s | Rp %12s | %s\n", 
        $item->tanggal, $item->id, $item->source, number_format($item->debits, 0), $item->comment);
}

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
    ->get(['jed.id', 'jed.journal_entry_id', 'je.source', 'je.tanggal', 'jed.comment', 'jed.debits']);

$filtered = [];
foreach ($rows as $r) {
    if (in_array($r->journal_entry_id, [79786, 79790, 79794, 79801, 80996])) continue; // totalan
    $desc = strtolower(trim($r->comment));
    if (preg_match('/setor\s+omset|setoran\s+omset/i', $desc)) continue;
    if (preg_match('/keluar\s+ke\s+(bni|bri|bca|mandiri|cash\s*ops)/i', $desc)) continue;
    if (preg_match('/listrik|pln|token/i', $desc)) continue;
    if (preg_match('/b\.\s*transfer|biaya\s*transfer|cetak\s*rek|cetak\s*koran|ganti\s*kartu|charge\s*kartu|insuf\s*fund/i', $desc)) continue;
    if (preg_match('/kartu\s*ancol|kartu\s*bulanan/i', $desc)) continue;

    $filtered[] = $r;
}

echo "Remaining count after excluding 5 totalan: " . count($filtered) . "\n";
$sum = array_sum(array_map(fn($x) => (float)$x->debits, $filtered));
echo "Remaining sum: Rp " . number_format($sum, 0) . "\n\n";

// Let's inspect top 40 remaining by debit
usort($filtered, fn($a, $b) => (float)$b->debits <=> (float)$a->debits);
echo "=== TOP 40 LARGEST REMAINING TRANSACTIONS ===\n";
for ($i = 0; $i < min(40, count($filtered)); $i++) {
    $x = $filtered[$i];
    echo sprintf("[%s] ID: %d | Source: %s | Rp %12s | %s\n",
        $x->tanggal, $x->id, $x->source, number_format($x->debits, 0), $x->comment);
}

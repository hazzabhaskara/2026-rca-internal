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
    ->where(function($q) {
        $q->where('jed.comment', 'like', '%keluar ke%')
          ->orWhere('jed.comment', 'like', '%transfer%')
          ->orWhere('jed.comment', 'like', '%mutasi%')
          ->orWhere('je.comment', 'like', '%keluar ke%')
          ->orWhere('je.comment', 'like', '%transfer%')
          ->orWhere('je.comment', 'like', '%mutasi%');
    })
    ->get(['jed.id', 'je.source', 'jed.comment', 'jed.debits']);

$patterns = [];
foreach ($rows as $r) {
    $desc = strtolower(trim($r->comment));
    // extract key phrase
    if (preg_match('/keluar ke ([a-z0-9\s]+)/i', $desc, $m)) {
        $key = 'keluar ke ' . trim($m[1]);
    } elseif (preg_match('/transfer ([a-z0-9\s\/\'\-]+)/i', $desc, $m)) {
        $key = 'transfer ' . substr(trim($m[1]), 0, 30);
    } else {
        $key = substr($desc, 0, 30);
    }
    if (!isset($patterns[$key])) $patterns[$key] = ['count' => 0, 'sum' => 0];
    $patterns[$key]['count']++;
    $patterns[$key]['sum'] += (float)$r->debits;
}

arsort($patterns);
echo "=== TOP PATTERNS IN ANTAR BANK / MUTASI ===\n";
$i = 0;
foreach ($patterns as $k => $v) {
    if (++$i > 25) break;
    echo sprintf("%-40s | %4d trx | Rp %s\n", $k, $v['count'], number_format($v['sum'], 0));
}

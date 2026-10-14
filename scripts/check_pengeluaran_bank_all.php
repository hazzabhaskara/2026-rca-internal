<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->whereYear('je.tanggal', 2026)
    ->where('jed.comment', 'like', '%Pengeluaran Bank%')
    ->groupBy('jed.kode_akun')
    ->select(['jed.kode_akun', DB::raw('count(*) as cnt'), DB::raw('sum(jed.debits) as deb'), DB::raw('sum(jed.credits) as cred')])
    ->get();

echo "=== ALL ACCOUNTS WITH 'Pengeluaran Bank' IN 2026 ===\n";
foreach ($rows as $r) {
    $coa = DB::table('chart_of_accounts')->where('kode_akun', $r->kode_akun)->first();
    echo "Akun: {$r->kode_akun} ({$coa->nama_akun}) | Trx: {$r->cnt} | Deb: " . number_format($r->deb, 0) . " | Cred: " . number_format($r->cred, 0) . "\n";
}

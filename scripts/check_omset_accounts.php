<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

foreach (['1101002', '1101007', '1101011'] as $k) {
    $c = DB::table('chart_of_accounts')->where('kode_akun', $k)->first();
    $cnt = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
        ->where('jed.kode_akun', $k)
        ->whereYear('je.tanggal', 2026)
        ->count();
    $deb = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
        ->where('jed.kode_akun', $k)
        ->whereYear('je.tanggal', 2026)
        ->sum('jed.debits');
    $cred = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
        ->where('jed.kode_akun', $k)
        ->whereYear('je.tanggal', 2026)
        ->sum('jed.credits');
    echo "Akun: {$k} ({$c->nama_akun}) | Trx: {$cnt} | Total Deb: " . number_format($deb, 0) . " | Total Cred: " . number_format($cred, 0) . "\n";
}

<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->whereYear('je.tanggal', 2026)
    ->where(function($q) {
        $q->where('jed.comment', 'like', '%kartu ancol%')
          ->orWhere('jed.comment', 'like', '%kartu bulanan%');
    })
    ->groupBy('jed.kode_akun')
    ->select(['jed.kode_akun', DB::raw('count(*) as cnt'), DB::raw('sum(jed.debits) as deb')])
    ->get();

echo "Accounts used for Kartu Ancol / Kartu Bulanan in 2026:\n";
foreach ($rows as $r) {
    $coa = DB::table('chart_of_accounts')->where('kode_akun', $r->kode_akun)->first();
    $nama = $coa ? $coa->nama_akun : 'UNKNOWN';
    echo "Akun: {$r->kode_akun} ({$nama}) | Count: {$r->cnt} | Debit: " . number_format($r->deb, 0) . "\n";
}

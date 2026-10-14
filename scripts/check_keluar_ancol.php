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
    ->where('jed.comment', 'like', '%keluar ke ancol%')
    ->take(20)
    ->get(['jed.id', 'je.tanggal', 'jed.comment', 'jed.debits']);

foreach ($rows as $r) {
    echo "ID: {$r->id} | {$r->tanggal} | Rp " . number_format($r->debits, 0) . " | {$r->comment}\n";
}

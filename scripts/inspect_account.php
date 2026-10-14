<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$entries = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->whereIn('jed.kode_akun', ['5305000', '5305001'])
    ->whereYear('je.tanggal', 2026)
    ->select([
        'je.id as je_id',
        'je.source',
        'je.tanggal',
        'je.comment as je_comment',
        'jed.id as jed_id',
        'jed.kode_akun',
        'jed.comment as jed_comment',
        'jed.debits',
        'jed.credits',
        'jed.departemen_akun_id'
    ])
    ->orderBy('je.tanggal')
    ->orderBy('jed.id')
    ->get();

echo "Total transactions in 2026 for 5305000/5305001: " . $entries->count() . "\n";

$byAccount = $entries->groupBy('kode_akun');
foreach ($byAccount as $kode => $items) {
    echo "- Kode $kode: " . $items->count() . " records, Total Debits: " . number_format($items->sum('debits'), 2) . ", Total Credits: " . number_format($items->sum('credits'), 2) . "\n";
}

echo "\n--- All entries details ---\n";
foreach ($entries as $e) {
    echo "ID: {$e->jed_id} | Date: {$e->tanggal} | Kode: {$e->kode_akun} | Deb: " . number_format($e->debits, 0) . " | Cred: " . number_format($e->credits, 0) . " | jed_comment: [{$e->jed_comment}] | je_comment: [{$e->je_comment}]\n";
}

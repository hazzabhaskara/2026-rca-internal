<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->where('jed.kode_akun', 'like', '%3105%')
    ->take(10)
    ->get(['je.id', 'je.source', 'je.tanggal', 'jed.kode_akun', 'jed.debits', 'jed.credits', 'jed.comment']);

echo "Transactions using 3105xxx:\n";
foreach ($rows as $r) {
    echo "JE: {$r->id} | {$r->source} | {$r->tanggal} | {$r->kode_akun} | Deb: {$r->debits} | Cred: {$r->credits} | {$r->comment}\n";
}

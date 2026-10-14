<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$jes = DB::table('journal_entries as je')
    ->where('je.source', 'like', 'KASDFN-260101%')
    ->get();

foreach ($jes as $je) {
    echo "JE: {$je->id} | {$je->source} | {$je->tanggal} | {$je->comment}\n";
    $jeds = DB::table('journal_entry_details')->where('journal_entry_id', $je->id)->get();
    foreach ($jeds as $d) {
        echo "   -> Akun: {$d->kode_akun} | Deb: {$d->debits} | Cred: {$d->credits} | Desc: {$d->comment}\n";
    }
}

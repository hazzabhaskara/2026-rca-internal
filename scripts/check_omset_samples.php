<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== SAMPLES IN 1101002 (KAS OMSET) ===\n";
$s1 = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->where('jed.kode_akun', '1101002')
    ->take(5)
    ->get(['je.tanggal', 'jed.debits', 'jed.credits', 'jed.comment']);
foreach ($s1 as $s) echo "[{$s->tanggal}] Deb: {$s->debits} | Cred: {$s->credits} | {$s->comment}\n";

echo "\n=== SAMPLES IN 1101011 (KAS SETOR RETAIL) ===\n";
$s2 = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->where('jed.kode_akun', '1101011')
    ->take(5)
    ->get(['je.tanggal', 'jed.debits', 'jed.credits', 'jed.comment']);
foreach ($s2 as $s) echo "[{$s->tanggal}] Deb: {$s->debits} | Cred: {$s->credits} | {$s->comment}\n";

<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECK OTHER SETOR OMSET ENTRIES (NOT 5305001) ===\n";
$omsetEntries = DB::table('journal_entries as je')
    ->join('journal_entry_details as jed', 'je.id', '=', 'jed.journal_entry_id')
    ->where('jed.kode_akun', '!=', '5305001')
    ->where('jed.kode_akun', '!=', '5305000')
    ->where(function($q) {
        $q->where('jed.comment', 'like', '%Setor Omset%')
          ->orWhere('je.comment', 'like', '%Setor Omset%');
    })
    ->whereYear('je.tanggal', 2026)
    ->take(10)
    ->get(['je.id', 'je.source', 'je.tanggal', 'je.comment as je_comment', 'jed.kode_akun', 'jed.debits', 'jed.credits', 'jed.comment as jed_comment']);

foreach ($omsetEntries as $oe) {
    echo "JE: {$oe->id} | {$oe->source} | {$oe->tanggal} | Akun: {$oe->kode_akun} | Deb: {$oe->debits} | Cred: {$oe->credits} | {$oe->jed_comment}\n";
}

echo "\n=== CHECK MUTASI / TRANSFER ANTAR BANK (NOT 5305001) ===\n";
$transferEntries = DB::table('journal_entries as je')
    ->join('journal_entry_details as jed', 'je.id', '=', 'jed.journal_entry_id')
    ->where('jed.kode_akun', '!=', '5305001')
    ->where('jed.kode_akun', '!=', '5305000')
    ->where(function($q) {
        $q->where('jed.comment', 'like', '%Keluar ke BNI%')
          ->orWhere('jed.comment', 'like', '%Keluar ke Mandiri%')
          ->orWhere('jed.comment', 'like', '%Keluar ke BRI%');
    })
    ->whereYear('je.tanggal', 2026)
    ->take(10)
    ->get(['je.id', 'je.source', 'je.tanggal', 'je.comment as je_comment', 'jed.kode_akun', 'jed.debits', 'jed.credits', 'jed.comment as jed_comment']);

foreach ($transferEntries as $te) {
    echo "JE: {$te->id} | {$te->source} | {$te->tanggal} | Akun: {$te->kode_akun} | Deb: {$te->debits} | Cred: {$te->credits} | {$te->jed_comment}\n";
}

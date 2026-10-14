<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ids = [179730, 177310, 177318, 177326, 177340];
$rows = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->whereIn('jed.id', $ids)
    ->get(['je.id as je_id', 'je.source', 'je.tanggal', 'je.comment as je_comment', 'jed.id', 'jed.comment', 'jed.debits']);

foreach ($rows as $r) {
    echo "=== JE ID: {$r->je_id} | Source: {$r->source} | Date: {$r->tanggal} | Comment: {$r->comment} | Deb: " . number_format($r->debits, 2) . " ===\n";
    $all = DB::table('journal_entry_details')->where('journal_entry_id', $r->je_id)->get();
    foreach ($all as $a) {
        $coa = DB::table('chart_of_accounts')->where('kode_akun', $a->kode_akun)->first();
        echo "   Akun: {$a->kode_akun} ({$coa->nama_akun}) | Deb: " . number_format($a->debits, 2) . " | Cred: " . number_format($a->credits, 2) . " | Desc: {$a->comment}\n";
    }
}

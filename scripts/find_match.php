<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->where(function($q) {
        $q->where('jed.debits', 4544383)
          ->orWhere('jed.credits', 4544383);
    })
    ->get(['je.id', 'je.source', 'je.tanggal', 'je.comment', 'jed.kode_akun', 'jed.debits', 'jed.credits', 'jed.comment as jed_desc']);

echo "Matching 4544383:\n";
foreach ($rows as $r) {
    echo "JE: {$r->id} | {$r->source} | {$r->tanggal} | {$r->kode_akun} | Deb: {$r->debits} | Cred: {$r->credits} | {$r->jed_desc}\n";
}

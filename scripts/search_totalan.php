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
        $q->where('jed.comment', 'like', '%total%')
          ->orWhere('je.comment', 'like', '%total%');
    })
    ->whereIn('jed.kode_akun', ['5305000', '5305001'])
    ->get(['je.id', 'je.source', 'je.tanggal', 'jed.comment', 'jed.debits', 'jed.credits']);

echo "Transactions in 5305001 with 'total': " . $rows->count() . "\n";
foreach ($rows->take(15) as $r) {
    echo "JE: {$r->id} | {$r->source} | {$r->tanggal} | Rp " . number_format($r->debits, 0) . " | {$r->comment}\n";
}

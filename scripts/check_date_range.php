<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$res = DB::table('journal_entries')
    ->whereYear('tanggal', 2026)
    ->selectRaw('MIN(tanggal) as min_date, MAX(tanggal) as max_date, COUNT(*) as total_entries')
    ->first();

echo "Date range 2026: {$res->min_date} to {$res->max_date} | Total JE: {$res->total_entries}\n";

$detailsCount = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->whereYear('je.tanggal', 2026)
    ->count();

echo "Total JED in 2026: {$detailsCount}\n";

<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Get all journal_entry_id where jed.kode_akun in ('5305000', '5305001') in 2026
$jeIds = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->whereIn('jed.kode_akun', ['5305000', '5305001'])
    ->whereYear('je.tanggal', 2026)
    ->pluck('jed.journal_entry_id')
    ->unique();

echo "Unique Journal Entries involving 5305000/5305001 in 2026: " . $jeIds->count() . "\n";

// Let's get total count of jed records in 5305000 / 5305001
$count = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->whereIn('jed.kode_akun', ['5305000', '5305001'])
    ->whereYear('je.tanggal', 2026)
    ->count();

echo "Total JED records with 5305000/5305001 in 2026: " . $count . "\n";

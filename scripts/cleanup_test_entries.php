<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$maxIdBefore = 85387; // The maximum JE id before the tests

$testEntries = DB::table('journal_entries')->where('id', '>', $maxIdBefore)->get();
echo "Test journal entries created with ID > {$maxIdBefore}: " . $testEntries->count() . "\n";

if ($testEntries->count() > 0) {
    $ids = $testEntries->pluck('id')->toArray();
    $delD = DB::table('journal_entry_details')->whereIn('journal_entry_id', $ids)->delete();
    $delE = DB::table('journal_entries')->whereIn('id', $ids)->delete();
    echo "Cleaned up: {$delE} test journal_entries and {$delD} test journal_entry_details.\n";
}

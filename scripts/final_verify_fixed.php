<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

ini_set('memory_limit', '2048M');
set_time_limit(0);

use App\Imports\JournalEntryImport;
use App\Services\JournalIngestionService;
use App\StagingJournalEntry;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

$file = 'D:/Hazza/REMBULAN CITRA ABADI/2026/2026-RCA-INTERNAL/Import_Transaksi_2026_Pasca_Adjustment_FIXED.xlsx';
$testBatchId = (string) Str::uuid();

echo "Final Verification of Import_Transaksi_2026_Pasca_Adjustment_FIXED.xlsx...\n";
$importer = new JournalEntryImport($testBatchId);

try {
    Excel::import($importer, $file);
    $staged = StagingJournalEntry::where('batch_id', $testBatchId)->count();
    echo "Staged rows: {$staged}\n";

    $service = new JournalIngestionService();
    $result = $service->processPendingEntries($testBatchId);

    echo "Result status: " . ($result['status'] ?? 'unknown') . "\n";
    echo "Processed: " . ($result['processed_rows'] ?? 0) . "\n";
    echo "Failed: " . ($result['failed_rows'] ?? 0) . "\n";

    StagingJournalEntry::where('batch_id', $testBatchId)->delete();
    if (($result['status'] ?? '') === 'success' && ($result['failed_rows'] ?? 0) === 0) {
        echo "✅ VERIFIKASI FINAL SUKSES 100% TANPA KENDALA!\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    StagingJournalEntry::where('batch_id', $testBatchId)->delete();
}

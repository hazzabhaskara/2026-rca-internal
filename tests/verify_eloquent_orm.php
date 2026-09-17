<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\JournalEntry;
use App\JournalEntryDetail;
use App\StagingJournalEntry;
use App\StartNewYear;
use Illuminate\Support\Facades\DB;

echo "===============================================================\n";
echo "       ELOQUENT ORM & DATABASE ISOLATION VERIFICATION          \n";
echo "===============================================================\n";

try {
    // 1. Connection verification
    $dbConnection = DB::connection();
    $dbName = $dbConnection->getDatabaseName();
    $host = config('database.connections.mysql.host');
    $port = config('database.connections.mysql.port');
    $user = config('database.connections.mysql.username');

    echo "[1] Connection Config:\n";
    echo "    - Driver:   " . $dbConnection->getDriverName() . "\n";
    echo "    - Host:     " . $host . ":" . $port . "\n";
    echo "    - Database: " . $dbName . "\n";
    echo "    - User:     " . $user . "\n";

    if ($dbName !== 'rembulancitraabadi_2025_db') {
        throw new RuntimeException("DB_DATABASE mismatch! Expected 'rembulancitraabadi_2025_db', got '{$dbName}'");
    }
    echo "    => Connection verification PASSED.\n\n";

    // 2. Table count verification in rembulancitraabadi_2025_db
    $tableCountResult = DB::select("SELECT count(*) as cnt FROM information_schema.tables WHERE table_schema = 'rembulancitraabadi_2025_db'");
    $tableCount = (int)$tableCountResult[0]->cnt;
    echo "[2] Schema Inspection:\n";
    echo "    - Total tables in rembulancitraabadi_2025_db: " . $tableCount . "\n";
    if ($tableCount !== 126) {
        throw new RuntimeException("Table count mismatch! Expected 126, got {$tableCount}");
    }
    echo "    => 126 tables verified PASSED.\n\n";

    // 3. Eloquent ORM: JournalEntry & JournalEntryDetail
    echo "[3] Eloquent ORM: JournalEntry Model:\n";
    $journalCount = JournalEntry::count();
    echo "    - Total JournalEntry records: " . $journalCount . "\n";
    if ($journalCount === 0) {
        throw new RuntimeException("JournalEntry table is empty!");
    }
    $firstJournal = JournalEntry::first();
    echo "    - Sample ID:       " . $firstJournal->id . "\n";
    echo "    - Source:          " . $firstJournal->source . "\n";
    echo "    - Tanggal:         " . $firstJournal->tanggal . "\n";
    echo "    - Comment:         " . $firstJournal->comment . "\n";
    $detailCount = $firstJournal->details()->count();
    echo "    - Related Details: " . $detailCount . "\n";
    echo "    => JournalEntry Eloquent query PASSED.\n\n";

    // 4. Eloquent ORM: StagingJournalEntry
    echo "[4] Eloquent ORM: StagingJournalEntry Model:\n";
    $stagingCount = StagingJournalEntry::count();
    echo "    - Total StagingJournalEntry records: " . $stagingCount . "\n";
    if ($stagingCount === 0) {
        throw new RuntimeException("StagingJournalEntry table is empty!");
    }
    $firstStaging = StagingJournalEntry::first();
    echo "    - Sample ID:       " . $firstStaging->id . "\n";
    echo "    - Batch ID:        " . $firstStaging->batch_id . "\n";
    echo "    - Source:          " . $firstStaging->source . "\n";
    echo "    - Tanggal:         " . $firstStaging->tanggal . "\n";
    echo "    - Kode Akun:       " . $firstStaging->kode_akun . "\n";
    echo "    - Nama Akun:       " . $firstStaging->nama_akun . "\n";
    echo "    - Debit:           " . number_format((float)$firstStaging->debit, 2) . "\n";
    echo "    - Kredit:          " . number_format((float)$firstStaging->kredit, 2) . "\n";
    echo "    - Status:          " . $firstStaging->status . "\n";
    echo "    => StagingJournalEntry Eloquent query PASSED.\n\n";

    // 5. Eloquent ORM: StartNewYear
    echo "[5] Eloquent ORM: StartNewYear Model:\n";
    $newYearCount = StartNewYear::count();
    echo "    - Total StartNewYear records: " . $newYearCount . "\n";
    echo "    => StartNewYear Eloquent query PASSED.\n\n";

    echo "===============================================================\n";
    echo "       ALL ELOQUENT ORM & DATABASE TESTS PASSED SUCCESSFULLY   \n";
    echo "===============================================================\n";
    exit(0);

} catch (Exception $e) {
    echo "\n[ERROR]: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

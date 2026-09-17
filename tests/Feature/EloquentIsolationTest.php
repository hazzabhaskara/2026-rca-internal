<?php

namespace Tests\Feature;

use App\JournalEntry;
use App\JournalEntryDetail;
use App\StagingJournalEntry;
use App\StartNewYear;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EloquentIsolationTest extends TestCase
{
    public function testDatabaseConnectionIsIsolatedTo2025Db()
    {
        $database = DB::connection()->getDatabaseName();
        $this->assertEquals('rembulancitraabadi_2025_db', $database);

        $pdo = DB::connection()->getPdo();
        $this->assertNotNull($pdo);

        $tableCount = DB::select("SELECT count(*) as cnt FROM information_schema.tables WHERE table_schema = 'rembulancitraabadi_2025_db'")[0]->cnt;
        $this->assertEquals(126, (int)$tableCount);
    }

    public function testEloquentJournalEntryModel()
    {
        $count = JournalEntry::count();
        $this->assertGreaterThan(0, $count);

        $first = JournalEntry::first();
        $this->assertNotNull($first);
        $this->assertNotEmpty($first->id);

        // Test details relationship
        $details = $first->details;
        $this->assertNotNull($details);
    }

    public function testEloquentStagingJournalEntryModel()
    {
        $count = StagingJournalEntry::count();
        $this->assertGreaterThan(0, $count);

        $first = StagingJournalEntry::first();
        $this->assertNotNull($first);
        $this->assertNotEmpty($first->batch_id);
    }

    public function testEloquentStartNewYearModel()
    {
        $this->assertTrue(class_exists(StartNewYear::class));
        $count = StartNewYear::count();
        $this->assertGreaterThanOrEqual(0, $count);
    }
}

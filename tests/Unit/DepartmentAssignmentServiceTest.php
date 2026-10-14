<?php

namespace Tests\Unit;

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;
use App\DepartmentAssignmentLog;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\Services\DepartmentAssignmentService;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DepartmentAssignmentServiceTest extends TestCase
{
    /** @var DepartmentAssignmentService */
    protected $service;

    /** @var User */
    protected $adminUser;

    /** @var User */
    protected $staffUser;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        $this->service = new DepartmentAssignmentService();
        $this->adminUser = User::where('name', 'Admin')->first() ?? User::find(1);
        $this->staffUser = User::where('name', '!=', 'Admin')->whereHas('roles', function ($q) {
            $q->where('name', 'Staff');
        })->first() ?? User::find(2);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    protected function getClosedPeriodDetail(): JournalEntryDetail
    {
        $detail = JournalEntryDetail::whereHas('journalEntry', function ($q) {
            $q->where('tanggal', '<=', '2024-12-31');
        })->whereNotNull('departemen_akun_id')->first();

        if (!$detail) {
            $je = JournalEntry::create([
                'source'  => 'CLOSING-2024-TEST',
                'tanggal' => '2024-10-15',
                'comment' => 'Test Closed Entry',
            ]);
            $defaultAkunId = \App\ChartOfAccount::value('id') ?? 1;
            $da = DepartemenAkun::firstOrCreate(['akun_id' => $defaultAkunId, 'departemen_id' => 1]);
            $detail = JournalEntryDetail::create([
                'journal_entry_id'   => $je->id,
                'departemen_akun_id' => $da->id,
                'kode_akun'          => '1101001',
                'debits'             => 100000,
                'credits'            => 0,
            ]);
        }

        return $detail;
    }

    protected function getOpenYear(): int
    {
        return DB::table('start_new_years')->where('status', 'Opening')->value('tahun') ?? 2026;
    }

    // =========================================================================
    // 1. CLOSED PERIOD DETECTION (isPeriodClosed)
    // =========================================================================

    public function testIsPeriodClosedReturnsTrueForClosedPeriodDates()
    {
        // 2024 is closing in start_new_years
        $this->assertTrue($this->service->isPeriodClosed('2024-05-15'));
        $this->assertTrue($this->service->isPeriodClosed('2024-12-31'));
        $this->assertTrue($this->service->isPeriodClosed('2023-01-01'));
    }

    public function testIsPeriodClosedReturnsFalseForOpenPeriodDates()
    {
        $openYear = $this->getOpenYear();
        $this->assertFalse($this->service->isPeriodClosed("{$openYear}-06-15"));
        $this->assertFalse($this->service->isPeriodClosed("{$openYear}-12-31"));
    }

    // =========================================================================
    // 2. VALIDATION & ERROR HANDLING
    // =========================================================================

    public function testReassignThrowsExceptionIfReasonIsEmpty()
    {
        $detail = JournalEntryDetail::first();
        $dept = Departement::first();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Alasan penyesuaian (reason) wajib diisi.');

        $this->service->reassign($detail->id, $dept->id, '   ', $this->adminUser);
    }

    public function testUnassignThrowsExceptionIfReasonIsEmpty()
    {
        $detail = JournalEntryDetail::whereNotNull('departemen_akun_id')->first();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Alasan pelepasan (unassign reason) wajib diisi.');

        $this->service->unassign($detail->id, '', $this->adminUser);
    }

    public function testReassignBulkThrowsExceptionIfDetailIdsEmpty()
    {
        $dept = Departement::first();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Daftar ID transaksi tidak boleh kosong.');

        $this->service->reassignBulk([], $dept->id, 'Reassign test', $this->adminUser);
    }

    public function testReassignBulkThrowsExceptionIfReasonIsEmpty()
    {
        $detail = JournalEntryDetail::firstOrFail();
        $dept = Departement::firstOrFail();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Alasan penyesuaian masal (bulk reason) wajib diisi.');

        $this->service->reassignBulk([$detail->id], $dept->id, '   ', $this->adminUser);
    }

    public function testUnassignBulkThrowsExceptionIfDetailIdsEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Daftar ID transaksi tidak boleh kosong.');

        $this->service->unassignBulk([], 'Unassign test', $this->adminUser);
    }

    public function testUnassignBulkThrowsExceptionIfReasonIsEmpty()
    {
        $detail = JournalEntryDetail::whereNotNull('departemen_akun_id')->firstOrFail();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Alasan pelepasan masal (bulk reason) wajib diisi.');

        $this->service->unassignBulk([$detail->id], '   ', $this->adminUser);
    }

    // =========================================================================
    // 3. SINGLE REASSIGN & INITIAL ASSIGN
    // =========================================================================

    public function testReassignUpdatesDepartemenAkunIdAndCreatesAuditLog()
    {
        $openYear = $this->getOpenYear();
        // Find an open period detail that is already assigned
        $detail = JournalEntryDetail::whereHas('journalEntry', function ($q) use ($openYear) {
            $q->where('tanggal', '>=', "{$openYear}-01-01");
        })->whereNotNull('departemen_akun_id')->firstOrFail();

        $currentDeptId = $detail->departemenAkun->departemen_id;

        // Choose a different department
        $targetDept = Departement::where('id', '!=', $currentDeptId)->where('status', 1)->firstOrFail();

        $reason = 'Testing reassign audit log creation for M1';
        $result = $this->service->reassign($detail->id, $targetDept->id, $reason, $this->adminUser);

        $this->assertTrue($result['success']);
        $this->assertEquals('reassign', $result['action']);
        $this->assertEquals($detail->id, $result['detail_id']);
        $this->assertEquals($targetDept->id, $result['new_departemen_id']);

        // Verify in-place sync on detail
        $detail->refresh();
        $newDa = DepartemenAkun::find($detail->departemen_akun_id);
        $this->assertNotNull($newDa);
        $this->assertEquals($targetDept->id, $newDa->departemen_id);

        // Verify immutable audit log
        $log = DepartmentAssignmentLog::find($result['log_id']);
        $this->assertNotNull($log);
        $this->assertEquals($detail->id, $log->journal_entry_detail_id);
        $this->assertEquals($detail->journal_entry_id, $log->journal_entry_id);
        $this->assertEquals($detail->journalEntry->source, $log->voucher);
        $this->assertEquals($detail->kode_akun, $log->kode_akun);
        $this->assertEquals($currentDeptId, $log->old_departemen_id);
        $this->assertEquals($targetDept->id, $log->new_departemen_id);
        $this->assertEquals('reassign', $log->action_type);
        $this->assertEquals($reason, $log->reason);
        $this->assertEquals($this->adminUser->id, $log->user_id);
        $this->assertFalse($log->is_override_closed);
    }

    public function testReassignSetsInitialAssignActionTypeWhenPreviouslyUnassigned()
    {
        $openYear = $this->getOpenYear();
        // Find or create an unassigned detail in open period
        $detail = JournalEntryDetail::whereHas('journalEntry', function ($q) use ($openYear) {
            $q->where('tanggal', '>=', "{$openYear}-01-01");
        })->whereNull('departemen_akun_id')->first();

        if (!$detail) {
            // Pick an assigned detail and set departemen_akun_id to null for test
            $detail = JournalEntryDetail::whereHas('journalEntry', function ($q) use ($openYear) {
                $q->where('tanggal', '>=', "{$openYear}-01-01");
            })->firstOrFail();
            $detail->departemen_akun_id = null;
            $detail->save();
        }

        $targetDept = Departement::where('status', 1)->firstOrFail();
        $reason = 'Initial assignment test for M1';

        $result = $this->service->reassign($detail->id, $targetDept->id, $reason, $this->adminUser);

        $this->assertTrue($result['success']);
        $this->assertEquals('initial_assign', $result['action']);

        // Verify audit log action_type
        $log = DepartmentAssignmentLog::find($result['log_id']);
        $this->assertNotNull($log);
        $this->assertEquals('initial_assign', $log->action_type);
        $this->assertNull($log->old_departemen_id);
        $this->assertEquals($targetDept->id, $log->new_departemen_id);
    }

    // =========================================================================
    // 4. CLOSED PERIOD GUARDRAILS
    // =========================================================================

    public function testReassignOnClosedPeriodWithoutOverrideThrowsAuthorizationException()
    {
        // Find a transaction detail in 2024 (closed period)
        $detail = $this->getClosedPeriodDetail();

        $originalDaId = $detail->departemen_akun_id;
        $targetDept = Departement::where('id', '!=', $detail->departemenAkun->departemen_id)->firstOrFail();

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('periode akuntansi tertutup');

        $this->service->reassign($detail->id, $targetDept->id, 'Attempting closed reassign without override', $this->adminUser, false);

        // Verification that state remained unmodified
        $detail->refresh();
        $this->assertEquals($originalDaId, $detail->departemen_akun_id);
    }

    public function testReassignOnClosedPeriodByUnauthorizedUserThrowsAuthorizationException()
    {
        // Find a transaction detail in 2024 (closed period)
        $detail = $this->getClosedPeriodDetail();

        $targetDept = Departement::where('id', '!=', $detail->departemenAkun->departemen_id)->firstOrFail();

        // Staff user does not have assigned_watchdog.override_closed
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('assigned_watchdog.override_closed');

        $this->service->reassign($detail->id, $targetDept->id, 'Staff trying to override closed period', $this->staffUser, true);
    }

    public function testReassignOnClosedPeriodByAuthorizedUserSucceedsAndSetsOverrideFlag()
    {
        // Find a transaction detail in 2024 (closed period)
        $detail = $this->getClosedPeriodDetail();

        $entryYear = (int) date('Y', strtotime($detail->journalEntry->tanggal));

        // Mock StartNewYearService to verify updateLabaTahunBerjalan is called on closed period override
        $mockStartNewYear = $this->mock(\App\Services\StartNewYearService::class);
        $mockStartNewYear->shouldReceive('updateLabaTahunBerjalan')
            ->once()
            ->with($entryYear);

        $currentDeptId = $detail->departemenAkun->departemen_id;
        $targetDept = Departement::where('id', '!=', $currentDeptId)->where('status', 1)->firstOrFail();

        $reason = 'Auditor justification for historical 2024 adjustment';
        $result = $this->service->reassign($detail->id, $targetDept->id, $reason, $this->adminUser, true);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['is_override_closed']);

        $log = DepartmentAssignmentLog::find($result['log_id']);
        $this->assertNotNull($log);
        $this->assertTrue($log->is_override_closed);
        $this->assertEquals($reason, $log->reason);
    }

    public function testUnassignOnClosedPeriodByAuthorizedUserSucceedsAndCallsStartNewYearService()
    {
        // Find a transaction detail in 2024 (closed period)
        $detail = $this->getClosedPeriodDetail();

        $entryYear = (int) date('Y', strtotime($detail->journalEntry->tanggal));

        // Mock StartNewYearService to verify updateLabaTahunBerjalan is called on closed period override
        $mockStartNewYear = $this->mock(\App\Services\StartNewYearService::class);
        $mockStartNewYear->shouldReceive('updateLabaTahunBerjalan')
            ->once()
            ->with($entryYear);

        $reason = 'Auditor justification for historical unassign in closed period';
        $result = $this->service->unassign($detail->id, $reason, $this->adminUser, true);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['is_override_closed']);

        $log = DepartmentAssignmentLog::find($result['log_id']);
        $this->assertNotNull($log);
        $this->assertTrue($log->is_override_closed);
        $this->assertEquals('unassign', $log->action_type);
        $this->assertEquals($reason, $log->reason);
    }

    // =========================================================================
    // 5. SINGLE UNASSIGN
    // =========================================================================

    public function testUnassignDetachesDepartmentAndCreatesAuditLog()
    {
        $openYear = $this->getOpenYear();
        // Find an open period detail that is currently assigned
        $detail = JournalEntryDetail::whereHas('journalEntry', function ($q) use ($openYear) {
            $q->where('tanggal', '>=', "{$openYear}-01-01");
        })->whereNotNull('departemen_akun_id')->firstOrFail();

        $oldDeptId = $detail->departemenAkun->departemen_id;
        $oldDaId = $detail->departemen_akun_id;
        $reason = 'Auditor detachment to unassigned queue';

        $result = $this->service->unassign($detail->id, $reason, $this->adminUser);

        $this->assertTrue($result['success']);
        $this->assertEquals('unassign', $result['action']);
        $this->assertNull($result['new_departemen_id']);

        // Verify in-place detachment on detail
        $detail->refresh();
        $this->assertNull($detail->departemen_akun_id);

        // Verify audit log
        $log = DepartmentAssignmentLog::find($result['log_id']);
        $this->assertNotNull($log);
        $this->assertEquals('unassign', $log->action_type);
        $this->assertEquals($oldDeptId, $log->old_departemen_id);
        $this->assertNull($log->new_departemen_id);
        $this->assertEquals($oldDaId, $log->old_departemen_akun_id);
        $this->assertNull($log->new_departemen_akun_id);
        $this->assertEquals($reason, $log->reason);
    }

    public function testUnassignOnClosedPeriodWithoutOverrideThrowsAuthorizationException()
    {
        $detail = $this->getClosedPeriodDetail();

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('periode akuntansi tertutup');

        $this->service->unassign($detail->id, 'Unassign closed without override', $this->adminUser, false);
    }

    // =========================================================================
    // 6. BULK REASSIGN & BULK UNASSIGN
    // =========================================================================

    public function testReassignBulkUpdatesMultipleDetailsAtomically()
    {
        $openYear = $this->getOpenYear();
        // Get 3 assigned details from open period
        $details = JournalEntryDetail::whereHas('journalEntry', function ($q) use ($openYear) {
            $q->where('tanggal', '>=', "{$openYear}-01-01");
        })->whereNotNull('departemen_akun_id')->limit(3)->get();

        $this->assertCount(3, $details);
        $detailIds = $details->pluck('id')->toArray();

        // Target department: DUFAN (ID: 2) or PUSAT (ID: 1)
        $targetDept = Departement::where('status', 1)->firstOrFail();
        $reason = 'Bulk reassign test for M1';

        $result = $this->service->reassignBulk($detailIds, $targetDept->id, $reason, $this->adminUser);

        $this->assertTrue($result['success']);
        $this->assertEquals('reassign_bulk', $result['action']);
        $this->assertEquals(3, $result['updated_count']);
        $this->assertNotEmpty($result['batch_id']);
        $this->assertCount(3, $result['log_ids']);

        // Verify each detail has new department
        foreach ($detailIds as $id) {
            $d = JournalEntryDetail::find($id);
            $this->assertEquals($targetDept->id, $d->departemenAkun->departemen_id);
        }

        // Verify all 3 logs share the exact same batch_id
        $logs = DepartmentAssignmentLog::whereIn('id', $result['log_ids'])->get();
        $this->assertCount(3, $logs);
        foreach ($logs as $log) {
            $this->assertEquals($result['batch_id'], $log->batch_id);
            $this->assertEquals($targetDept->id, $log->new_departemen_id);
            $this->assertEquals($reason, $log->reason);
        }
    }

    public function testReassignBulkRollsBackAtomicallyWhenClosedPeriodEncountered()
    {
        $openYear = $this->getOpenYear();
        // 1 open detail and 1 closed detail
        $openDetail = JournalEntryDetail::whereHas('journalEntry', function ($q) use ($openYear) {
            $q->where('tanggal', '>=', "{$openYear}-01-01");
        })->whereNotNull('departemen_akun_id')->firstOrFail();

        $closedDetail = $this->getClosedPeriodDetail();

        $originalOpenDaId = $openDetail->departemen_akun_id;
        $originalClosedDaId = $closedDetail->departemen_akun_id;

        $targetDept = Departement::where('id', '!=', $openDetail->departemenAkun->departemen_id)->firstOrFail();

        try {
            $this->service->reassignBulk(
                [$openDetail->id, $closedDetail->id],
                $targetDept->id,
                'Bulk should fail due to closed period item',
                $this->adminUser,
                false // no override
            );
            $this->fail('Expected AuthorizationException was not thrown.');
        } catch (AuthorizationException $e) {
            $this->assertStringContainsString('periode akuntansi tertutup', $e->getMessage());
        }

        // Verify atomic rollback: neither detail changed
        $openDetail->refresh();
        $closedDetail->refresh();
        $this->assertEquals($originalOpenDaId, $openDetail->departemen_akun_id);
        $this->assertEquals($originalClosedDaId, $closedDetail->departemen_akun_id);
    }

    public function testUnassignBulkDetachesMultipleDetailsAtomically()
    {
        $openYear = $this->getOpenYear();
        // Get 2 assigned details from open period
        $details = JournalEntryDetail::whereHas('journalEntry', function ($q) use ($openYear) {
            $q->where('tanggal', '>=', "{$openYear}-01-01");
        })->whereNotNull('departemen_akun_id')->limit(2)->get();

        $this->assertCount(2, $details);
        $detailIds = $details->pluck('id')->toArray();
        $reason = 'Bulk unassign test for M1';

        $result = $this->service->unassignBulk($detailIds, $reason, $this->adminUser);

        $this->assertTrue($result['success']);
        $this->assertEquals('unassign_bulk', $result['action']);
        $this->assertEquals(2, $result['unassigned_count']);
        $this->assertNotEmpty($result['batch_id']);

        // Verify both details now have null departemen_akun_id
        foreach ($detailIds as $id) {
            $d = JournalEntryDetail::find($id);
            $this->assertNull($d->departemen_akun_id);
        }

        // Verify logs
        $logs = DepartmentAssignmentLog::whereIn('id', $result['log_ids'])->get();
        $this->assertCount(2, $logs);
        foreach ($logs as $log) {
            $this->assertEquals('unassign', $log->action_type);
            $this->assertNull($log->new_departemen_id);
            $this->assertEquals($result['batch_id'], $log->batch_id);
        }
    }

    public function testUnassignBulkRollsBackAtomicallyWhenClosedPeriodEncountered()
    {
        $openYear = $this->getOpenYear();
        $openDetail = JournalEntryDetail::whereHas('journalEntry', function ($q) use ($openYear) {
            $q->where('tanggal', '>=', "{$openYear}-01-01");
        })->whereNotNull('departemen_akun_id')->firstOrFail();

        $closedDetail = $this->getClosedPeriodDetail();

        $originalOpenDaId = $openDetail->departemen_akun_id;
        $originalClosedDaId = $closedDetail->departemen_akun_id;

        try {
            $this->service->unassignBulk(
                [$openDetail->id, $closedDetail->id],
                'Bulk unassign should fail due to closed period item',
                $this->adminUser,
                false // no override
            );
            $this->fail('Expected AuthorizationException was not thrown.');
        } catch (AuthorizationException $e) {
            $this->assertStringContainsString('periode akuntansi tertutup', $e->getMessage());
        }

        // Verify atomic rollback: neither detail changed
        $openDetail->refresh();
        $closedDetail->refresh();
        $this->assertEquals($originalOpenDaId, $openDetail->departemen_akun_id);
        $this->assertEquals($originalClosedDaId, $closedDetail->departemen_akun_id);
    }

    // =========================================================================
    // 7. ELOQUENT MODEL RELATIONSHIPS & SCOPES
    // =========================================================================

    public function testDepartmentAssignmentLogRelationshipsAndScopes()
    {
        $openYear = $this->getOpenYear();
        $detail = JournalEntryDetail::whereNotNull('departemen_akun_id')
            ->whereHas('journalEntry', function ($q) use ($openYear) {
                $q->where('tanggal', '>=', "{$openYear}-01-01");
            })
            ->whereNotIn('id', DepartmentAssignmentLog::pluck('journal_entry_detail_id'))
            ->first();

        if (!$detail) {
            $je = JournalEntry::create([
                'source'  => 'OPEN-SCOPE-TEST',
                'tanggal' => "{$openYear}-05-01",
                'comment' => 'Test Open Entry for scopes',
            ]);
            $defaultAkunId = \App\ChartOfAccount::value('id') ?? 1;
            $da = DepartemenAkun::firstOrCreate(['akun_id' => $defaultAkunId, 'departemen_id' => 1]);
            $detail = JournalEntryDetail::create([
                'journal_entry_id'   => $je->id,
                'departemen_akun_id' => $da->id,
                'kode_akun'          => '1101001',
                'debits'             => 50000,
                'credits'            => 0,
            ]);
        }

        $targetDept = Departement::where('id', '!=', $detail->departemenAkun->departemen_id)->firstOrFail();

        $result = $this->service->reassign($detail->id, $targetDept->id, 'Model relationship test', $this->adminUser);
        $log = DepartmentAssignmentLog::findOrFail($result['log_id']);

        // Test relationships
        $this->assertInstanceOf(JournalEntryDetail::class, $log->journalEntryDetail);
        $this->assertEquals($detail->id, $log->journalEntryDetail->id);

        $this->assertInstanceOf(JournalEntry::class, $log->journalEntry);
        $this->assertEquals($detail->journal_entry_id, $log->journalEntry->id);

        $this->assertInstanceOf(Departement::class, $log->newDepartemen);
        $this->assertEquals($targetDept->id, $log->newDepartemen->id);

        $this->assertInstanceOf(DepartemenAkun::class, $log->newDepartemenAkun);

        $this->assertInstanceOf(ChartOfAccount::class, $log->chartOfAccount);
        $this->assertEquals($detail->kode_akun, $log->chartOfAccount->kode_akun);

        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals($this->adminUser->id, $log->user->id);

        // Test scopes
        $this->assertEquals(1, DepartmentAssignmentLog::forDetail($detail->id)->count());
        $this->assertGreaterThan(0, DepartmentAssignmentLog::reassigned()->count());
        $this->assertGreaterThanOrEqual(0, DepartmentAssignmentLog::unassigned()->count());
    }
}

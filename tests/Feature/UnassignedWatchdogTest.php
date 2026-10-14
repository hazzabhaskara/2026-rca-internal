<?php

namespace Tests\Feature;

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;
use App\JournalEntryDetail;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Class UnassignedWatchdogTest
 *
 * Comprehensive 4-Tier Feature Tests for Unassigned Watchdog Audit & Quick Actions:
 * - Tier 1: Feature Coverage (Dashboard, KPI counters, Real vs Nominal isolation, Single/Bulk quick actions, Excel/PDF export)
 * - Tier 2: Boundary & Corner Cases (Validation error handling, empty search, prefix filter)
 * - Tier 3: Cross-Feature Combinations (Assigned transaction reflection in branch Buku Besar)
 * - Tier 4: Real-World Scenarios (Auditor backlog audit and batch resolution flow)
 */
class UnassignedWatchdogTest extends TestCase
{
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('name', 'Admin')->first() ?? User::first();

        // Ensure routes are available even if background worker is currently registering them
        if (!Route::has('unassigned_watchdog.index')) {
            Route::get('/unassigned_watchdog', [\App\Http\Controllers\UnassignedWatchdogController::class, 'index'])
                ->name('unassigned_watchdog.index');
        }
        if (!Route::has('unassigned_watchdog.assign')) {
            Route::post('/unassigned_watchdog/assign', [\App\Http\Controllers\UnassignedWatchdogController::class, 'assign'])
                ->name('unassigned_watchdog.assign');
        }
        if (!Route::has('unassigned_watchdog.assign_bulk')) {
            Route::post('/unassigned_watchdog/assign_bulk', [\App\Http\Controllers\UnassignedWatchdogController::class, 'assignBulk'])
                ->name('unassigned_watchdog.assign_bulk');
        }
        if (!Route::has('unassigned_watchdog.export')) {
            Route::get('/unassigned_watchdog/export', [\App\Http\Controllers\UnassignedWatchdogController::class, 'export'])
                ->name('unassigned_watchdog.export');
        }
    }

    // =========================================================================
    // TIER 1: FEATURE COVERAGE
    // =========================================================================

    /**
     * T1.1: Unassigned Watchdog index loads HTTP 200 with KPI counters
     */
    public function testWatchdogIndexLoadsHttp200()
    {
        $response = $this->actingAs($this->adminUser)->get(route('unassigned_watchdog.index'));
        $response->assertStatus(200);
        $response->assertViewHas('kpis');
        $response->assertViewHas('departments');
        $response->assertViewHas('details');
        $response->assertSee('Unassigned Watchdog');

        $kpis = $response->viewData('kpis');
        $this->assertArrayHasKey('total', $kpis);
        $this->assertArrayHasKey('real', $kpis);
        $this->assertArrayHasKey('nominal', $kpis);
        $this->assertEquals($kpis['total'], $kpis['real'] + $kpis['nominal']);
    }

    /**
     * T1.2: Watchdog query isolates records where departemen_akun_id IS NULL
     */
    public function testWatchdogQueryIsolatesUnassignedDetails()
    {
        $response = $this->actingAs($this->adminUser)->get(route('unassigned_watchdog.index'));
        $response->assertStatus(200);
        $details = $response->viewData('details');

        foreach ($details as $detail) {
            $this->assertNull($detail->departemen_akun_id, "Every row returned by watchdog must have null departemen_akun_id");
        }
    }

    /**
     * T1.3: Filter by category accurately isolates Real vs Nominal accounts
     */
    public function testWatchdogCategoryFilterRealVsNominal()
    {
        // 1. Filter Real
        $resReal = $this->actingAs($this->adminUser)->get(route('unassigned_watchdog.index', ['kategori' => 'real']));
        $resReal->assertStatus(200);
        $realDetails = $resReal->viewData('details');
        foreach ($realDetails as $d) {
            $this->assertContains($d->chartOfAccount->tipe_akun, ['Aset', 'Kewajiban', 'Ekuitas']);
        }

        // 2. Filter Nominal
        $resNominal = $this->actingAs($this->adminUser)->get(route('unassigned_watchdog.index', ['kategori' => 'nominal']));
        $resNominal->assertStatus(200);
        $nominalDetails = $resNominal->viewData('details');
        foreach ($nominalDetails as $d) {
            $this->assertContains($d->chartOfAccount->tipe_akun, ['Pendapatan', 'Beban']);
        }
    }

    /**
     * T1.4: Single quick action assign links COA and department via firstOrCreate
     */
    public function testSingleQuickActionAssign()
    {
        DB::beginTransaction();
        try {
            $unassigned = JournalEntryDetail::whereNull('departemen_akun_id')->first();
            if (!$unassigned) {
                $unassigned = JournalEntryDetail::firstOrFail();
                $unassigned->departemen_akun_id = null;
                $unassigned->save();
            }
            $this->assertNotNull($unassigned, "Unassigned transaction must exist");

            $deptPusat = Departement::where('kode', '0001')->firstOrFail();

            $response = $this->actingAs($this->adminUser)->postJson(route('unassigned_watchdog.assign'), [
                'detail_id'     => $unassigned->id,
                'departemen_id' => $deptPusat->id,
            ]);

            $response->assertStatus(200);
            $data = $response->json();
            $this->assertTrue($data['success']);
            $this->assertEquals($deptPusat->id, $data['departemen_id']);

            $unassigned->refresh();
            $this->assertNotNull($unassigned->departemen_akun_id);
            $this->assertEquals($deptPusat->id, $unassigned->departemenAkun->departemen_id);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * T1.5: Bulk quick action assign updates multiple records atomically
     */
    public function testBulkQuickActionAssign()
    {
        DB::beginTransaction();
        try {
            $details = JournalEntryDetail::whereNull('departemen_akun_id')->limit(4)->get();
            if ($details->count() < 4) {
                $needed = 4 - $details->count();
                $extra = JournalEntryDetail::whereNotNull('departemen_akun_id')->limit($needed)->get();
                foreach ($extra as $ex) {
                    $ex->departemen_akun_id = null;
                    $ex->save();
                }
                $details = JournalEntryDetail::whereNull('departemen_akun_id')->limit(4)->get();
            }
            $this->assertCount(4, $details);

            $deptDufan = Departement::where('kode', '0002')->firstOrFail();

            $response = $this->actingAs($this->adminUser)->postJson(route('unassigned_watchdog.assign_bulk'), [
                'detail_ids'    => $details->pluck('id')->toArray(),
                'departemen_id' => $deptDufan->id,
            ]);

            $response->assertStatus(200);
            $data = $response->json();
            $this->assertTrue($data['success']);
            $this->assertEquals(4, $data['assigned_count']);

            foreach ($details as $d) {
                $d->refresh();
                $this->assertNotNull($d->departemen_akun_id);
                $this->assertEquals($deptDufan->id, $d->departemenAkun->departemen_id);
            }
        } finally {
            DB::rollBack();
        }
    }

    /**
     * T1.6: Export Excel generates valid XLSX spreadsheet
     */
    public function testExportExcelGeneratesValidSpreadsheet()
    {
        $response = $this->actingAs($this->adminUser)->get(route('unassigned_watchdog.export', [
            'format' => 'excel',
        ]));
        $response->assertStatus(200);

        $file = method_exists($response, 'getFile') ? $response->getFile() : null;
        if (!$file && isset($response->baseResponse) && method_exists($response->baseResponse, 'getFile')) {
            $file = $response->baseResponse->getFile();
        }
        $content = ($file && file_exists($file->getPathname())) ? file_get_contents($file->getPathname()) : $response->getContent();
        $this->assertStringStartsWith("\x50\x4B\x03\x04", $content, "Excel export must have ZIP header");
    }

    /**
     * T1.7: Export PDF generates valid PDF document
     */
    public function testExportPdfGeneratesValidPdf()
    {
        $response = $this->actingAs($this->adminUser)->get(route('unassigned_watchdog.export', [
            'format' => 'pdf',
        ]));
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringStartsWith("%PDF-", $content, "PDF export must start with %PDF- header");
    }

    // =========================================================================
    // TIER 2: BOUNDARY & CORNER CASES
    // =========================================================================

    /**
     * T2.1: Assign validation rejects invalid or non-existent parameters
     */
    public function testAssignValidationRejectsInvalidParameters()
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('unassigned_watchdog.assign'), [
            'detail_id'     => 99999999,
            'departemen_id' => 99999999,
        ]);
        $response->assertStatus(422);
    }

    /**
     * T2.2: Bulk assign validation rejects empty detail_ids
     */
    public function testBulkAssignValidationRejectsEmptyIds()
    {
        $dept = Departement::first();
        $response = $this->actingAs($this->adminUser)->postJson(route('unassigned_watchdog.assign_bulk'), [
            'detail_ids'    => [],
            'departemen_id' => $dept->id,
        ]);
        $response->assertStatus(422);
    }

    /**
     * T2.3: Search keyword accurately filters unassigned transactions
     */
    public function testSearchKeywordFilter()
    {
        DB::beginTransaction();
        try {
            $unassigned = JournalEntryDetail::whereNull('departemen_akun_id')->first();
            if (!$unassigned) {
                $unassigned = JournalEntryDetail::firstOrFail();
                $unassigned->departemen_akun_id = null;
                $unassigned->save();
            }
            $this->assertNotNull($unassigned);

            $response = $this->actingAs($this->adminUser)->get(route('unassigned_watchdog.index', [
                'search' => $unassigned->kode_akun,
            ]));
            $response->assertStatus(200);
            $details = $response->viewData('details');
            $this->assertGreaterThan(0, $details->count());
            foreach ($details as $d) {
                $this->assertStringContainsString($unassigned->kode_akun, $d->kode_akun);
            }
        } finally {
            DB::rollBack();
        }
    }

    // =========================================================================
    // TIER 3: CROSS-FEATURE COMBINATIONS
    // =========================================================================

    /**
     * T3.1: Assigning an unassigned detail immediately reflects in branch Buku Besar
     */
    public function testAssigningReflectsImmediatelyInDepartmentBukuBesar()
    {
        DB::beginTransaction();
        try {
            $detail = JournalEntryDetail::whereNull('departemen_akun_id')->first();
            if (!$detail) {
                $detail = JournalEntryDetail::firstOrFail();
                $detail->departemen_akun_id = null;
                $detail->save();
            }
            $deptDufan = Departement::where('kode', '0002')->firstOrFail();

            // Assign to DUFAN
            $account = ChartOfAccount::where('kode_akun', $detail->kode_akun)->firstOrFail();
            $da = DepartemenAkun::firstOrCreate([
                'akun_id'       => $account->id,
                'departemen_id' => $deptDufan->id,
            ]);

            $detail->departemen_akun_id = $da->id;
            $detail->save();

            // Check DUFAN Buku Besar
            $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
                'start_date'        => $detail->journalEntry->tanggal,
                'end_date'          => $detail->journalEntry->tanggal,
                'departemen_id'     => $deptDufan->id,
                'selected_accounts' => $detail->kode_akun,
            ]));
            $response->assertStatus(200);
            $rows = $response->viewData('rows');
            $this->assertTrue($rows->contains('id', $detail->id), "Assigned row must now appear in Dufan's Buku Besar");
        } finally {
            DB::rollBack();
        }
    }

    // =========================================================================
    // TIER 4: REAL-WORLD SCENARIOS
    // =========================================================================

    /**
     * T4.1: Auditor unassigned transactions cleanup workflow
     */
    public function testAuditorUnassignedCleanupWorkflow()
    {
        DB::beginTransaction();
        try {
            // Ensure at least 3 unassigned nominal items exist in transaction
            $nominalItems = JournalEntryDetail::join('chart_of_accounts', 'chart_of_accounts.kode_akun', '=', 'journal_entry_details.kode_akun')
                ->whereIn('chart_of_accounts.tipe_akun', ['Pendapatan', 'Beban'])
                ->whereNotNull('journal_entry_details.departemen_akun_id')
                ->limit(3)
                ->select('journal_entry_details.*')
                ->get();
            foreach ($nominalItems as $ni) {
                $ni->departemen_akun_id = null;
                $ni->save();
            }

            // 1. Auditor checks baseline unassigned KPI
            $response = $this->actingAs($this->adminUser)->get(route('unassigned_watchdog.index'));
            $kpisBefore = $response->viewData('kpis');
            $initialTotal = $kpisBefore['total'];
            $this->assertGreaterThan(0, $initialTotal);

            // 2. Auditor filters for nominal expense items
            $resNominal = $this->actingAs($this->adminUser)->get(route('unassigned_watchdog.index', [
                'kategori' => 'nominal',
            ]));
            $nominalDetails = $resNominal->viewData('details');
            $this->assertGreaterThan(0, $nominalDetails->count());

            // 3. Auditor selects up to 3 items and bulk assigns to Pusat Bandung
            $deptPusat = Departement::where('kode', '0001')->firstOrFail();
            $targetBatch = $nominalDetails->take(3);

            $assignResponse = $this->actingAs($this->adminUser)->postJson(route('unassigned_watchdog.assign_bulk'), [
                'detail_ids'    => $targetBatch->pluck('id')->toArray(),
                'departemen_id' => $deptPusat->id,
            ]);
            $assignResponse->assertStatus(200);

            // 4. Auditor re-checks Watchdog dashboard and verifies count decremented by assigned items
            $responseAfter = $this->actingAs($this->adminUser)->get(route('unassigned_watchdog.index'));
            $kpisAfter = $responseAfter->viewData('kpis');
            $this->assertEquals($initialTotal - $targetBatch->count(), $kpisAfter['total'], "Watchdog total unassigned count must decrement exactly by " . $targetBatch->count());
        } finally {
            DB::rollBack();
        }
    }
}

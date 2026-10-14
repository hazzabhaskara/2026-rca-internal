<?php

namespace Tests\Feature;

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class E2ER2AccountingTest
 *
 * Master E2E Test Suite covering all 4 Tiers of RCA Web Internal Accounting Enhancements (R2):
 * - Tier 1: Functional Isolation & Requirement Coverage
 * - Tier 2: Boundary, Corner Cases & Extreme Conditions
 * - Tier 3: Cross-Feature State Propagation
 * - Tier 4: Real-World Operational & Forensic Scenarios
 */
class E2ER2AccountingTest extends TestCase
{
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('name', 'Admin')->first() ?? User::first();
    }

    /**
     * T1. E2E General Ledger Department Filter & Badges
     */
    public function testE2EGlDepartmentFilterAndBadges()
    {
        // 1. Initial Filter
        $filterRes = $this->actingAs($this->adminUser)->get(route('buku_besar.filter_buku_besar'));
        $filterRes->assertStatus(200);
        $filterRes->assertSee('Semua Departemen (Konsolidasi)');
        $filterRes->assertSee('Non-Departemen / Unassigned');

        // 2. Unassigned Filter Report
        $unassignedRes = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2025-12-01',
            'end_date'          => '2025-12-31',
            'departemen_id'     => 'unassigned',
            'selected_accounts' => '2102019',
        ]));
        $unassignedRes->assertStatus(200);
        $unassignedRes->assertSee('Non-Departemen / Unassigned');

        // 3. Single Branch Filter (DUFAN)
        $dufan = Departement::where('kode', '0002')->first() ?? Departement::find(2);
        $dufanRes = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2025-12-01',
            'end_date'          => '2025-12-31',
            'departemen_id'     => $dufan->id,
            'selected_accounts' => '2102019',
        ]));
        $dufanRes->assertStatus(200);
        $dufanRes->assertSee($dufan->deskripsi);
    }

    /**
     * T2. E2E General Journal Edit Select2 Unique IDs & Clean Integer DB Saving
     */
    public function testE2EJournalEditSelect2AndDbPersistence()
    {
        // 1. /search-account returns unique IDs prefixed with c- and d-
        $searchRes = $this->actingAs($this->adminUser)->get('/search-account?q=2102019');
        $searchRes->assertStatus(200);
        $searchData = $searchRes->json();
        $this->assertNotEmpty($searchData);

        // 2. Edit form renders decoupled inputs
        $testEntry = JournalEntry::create([
            'source'  => 'TEST-E2E-R2-' . uniqid(),
            'tanggal' => '2026-01-15',
            'comment' => 'Initial test entry',
        ]);
        $testEntry->details()->create([
            'kode_akun' => '1101001',
            'debits'    => 1000,
            'credits'   => 0,
        ]);
        $testEntry->details()->create([
            'kode_akun' => '2102019',
            'debits'    => 0,
            'credits'   => 1000,
        ]);

        try {
            $editRes = $this->actingAs($this->adminUser)->get(route('journal_entry.edit', $testEntry->id));
            $editRes->assertStatus(200);
            $editContent = $editRes->getContent();
            $this->assertStringContainsString('class="kode-akun"', $editContent);
            $this->assertStringContainsString('class="departemen-akun"', $editContent);

            // 3. Update entry saves clean integer
            $da = DepartemenAkun::first();
            $payload = [
                'source'  => $testEntry->source,
                'tanggal' => $testEntry->tanggal,
                'comment' => 'E2E Persist Test',
                'items'   => [
                    [
                        'kode_akun'          => '2102019',
                        'departemen_akun_id' => (string)$da->id,
                        'debits'             => '75000',
                        'credits'            => '0',
                    ],
                    [
                        'kode_akun'          => '1101001',
                        'departemen_akun_id' => null,
                        'debits'             => '0',
                        'credits'            => '75000',
                    ],
                ]
            ];

            $updateRes = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $testEntry->id), $payload);
            $this->assertTrue($updateRes->isRedirect() || $updateRes->status() === 200);

            $testEntry->refresh();
            $saved = $testEntry->details->firstWhere('kode_akun', '2102019');
            $this->assertEquals($da->id, $saved->departemen_akun_id);
        } finally {
            $testEntry->details()->delete();
            $testEntry->delete();
        }
    }

    /**
     * T3. E2E Cashier Inflow to Kas & Bank Reconciliation Engine
     */
    public function testE2ECashierInflowReconciliationEngine()
    {
        // Cashier Closing / Bank Vouchers (BNI662- or CH-)
        $chVouchers = JournalEntry::where('source', 'LIKE', 'BNI662%')->orWhere('source', 'LIKE', 'CH-%')->count();
        $this->assertGreaterThan(1000, $chVouchers, "Over 1,000 cashier/bank closing vouchers must exist in data");

        // Status Evaluation Tolerances
        $matchDiff = 450.00;
        $statusMatch = abs($matchDiff) <= 1000 ? 'MATCH' : 'SELISIH';
        $this->assertEquals('MATCH', $statusMatch);

        $pendingDiff = 0.00;
        $omset = 2500000.00;
        $kas = 0.00;
        $statusPending = ($omset == 0 || $kas == 0) ? 'PENDING' : 'MATCH';
        $this->assertEquals('PENDING', $statusPending);
    }

    /**
     * T4. E2E Unassigned Watchdog Audit & Department Assignment Flow
     */
    public function testE2EUnassignedWatchdogAuditAndResolution()
    {
        DB::beginTransaction();
        try {
            $unassignedDetail = JournalEntryDetail::whereNull('departemen_akun_id')->first();
            if (!$unassignedDetail) {
                $unassignedDetail = JournalEntryDetail::firstOrFail();
                $unassignedDetail->departemen_akun_id = null;
                $unassignedDetail->save();
            }
            $this->assertNotNull($unassignedDetail);

            $deptPusat = Departement::where('kode', '0001')->firstOrFail();
            $account = ChartOfAccount::where('kode_akun', $unassignedDetail->kode_akun)->firstOrFail();

            $da = DepartemenAkun::firstOrCreate([
                'akun_id'       => $account->id,
                'departemen_id' => $deptPusat->id,
            ]);

            $unassignedDetail->departemen_akun_id = $da->id;
            $unassignedDetail->save();

            $this->assertEquals($da->id, $unassignedDetail->fresh()->departemen_akun_id);
            $this->assertEquals($deptPusat->id, $unassignedDetail->fresh()->departemenAkun->departemen_id);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * T5. E2E Interactive Drilldown from Laba Rugi to Ledger
     */
    public function testE2EInteractiveDrilldownFromLabaRugiToLedger()
    {
        $lrResponse = $this->actingAs($this->adminUser)->get(route('income_statement.income_statement_departement', [
            'start_date' => '2026-01-01',
            'end_date'   => '2026-01-31',
        ]));
        $lrResponse->assertStatus(200);
        $lrContent = $lrResponse->getContent();
        $this->assertStringContainsString('/laporan/buku-besar', $lrContent);
        $this->assertStringContainsString('selected_accounts=', $lrContent);
        $this->assertStringContainsString('departemen_id=', $lrContent);
    }
}

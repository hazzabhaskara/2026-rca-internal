<?php

namespace Tests\Feature;

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class ChallengerAdversarialTest
 *
 * Empirical Challenge & Stress Test Suite covering:
 * 1. Buku Besar boundary conditions, department filters, non-existent department, and opening balance additivity
 * 2. Journal Entry edit department switching, back-and-forth swapping, dynamic rows, and number formatting
 * 3. Rekonsiliasi Closing empty periods, variance status rules, aggregation math, and drilldown JSON
 * 4. Unassigned Watchdog filtering, and single/bulk assign atomic transactions rollback on failure vs commit on success
 */
class ChallengerAdversarialTest extends TestCase
{
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('name', 'Admin')->first() ?? User::first();
    }

    // =========================================================================
    // 1. BUKU BESAR BOUNDARY & ADVERSARIAL TESTS
    // =========================================================================

    /**
     * Test Buku Besar with empty string selected_accounts does NOT crash and returns data.
     */
    public function testBukuBesarEmptyStringAccountFilterDoesNotCrash()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2025-12-01',
            'end_date'          => '2025-12-31',
            'selected_accounts' => '',
            'departemen_id'     => '',
        ]));

        $response->assertStatus(200);
        $rows = $response->viewData('rows');
        $this->assertNotNull($rows);
        $this->assertGreaterThan(0, $rows->count(), 'Empty selected_accounts must return all accounts, not 0 rows');
    }

    /**
     * Test Buku Besar with multi-account list with formatting.
     */
    public function testBukuBesarMultiAccountListFilter()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2025-12-01',
            'end_date'          => '2025-12-31',
            'selected_accounts' => '2102019 - HUTANG LAIN-LAIN, 1101001 - KAS BESAR',
            'departemen_id'     => 'all',
        ]));

        $response->assertStatus(200);
        $rows = $response->viewData('rows');
        $this->assertNotNull($rows);
        foreach ($rows as $row) {
            $this->assertContains($row->kode_akun, ['2102019', '1101001']);
        }
    }

    /**
     * Test Buku Besar with non-existent / unknown account code (boundary condition).
     */
    public function testBukuBesarUnknownAccountCodeReturnsZeroRowsGracefully()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2025-12-01',
            'end_date'          => '2025-12-31',
            'selected_accounts' => '9999999',
            'departemen_id'     => 'all',
        ]));

        $response->assertStatus(200);
        $rows = $response->viewData('rows');
        $this->assertEquals(0, $rows->count(), 'Unknown account code must return 0 rows without exception');
        $saldoAwal = $response->viewData('saldoAwalPerAkun');
        $this->assertEmpty($saldoAwal);
    }

    /**
     * Test Buku Besar department filter: unassigned vs all vs specific.
     */
    public function testBukuBesarDepartmentFilterIsolation()
    {
        // 1. Unassigned
        $unassignedRes = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'    => '2025-01-01',
            'end_date'      => '2025-12-31',
            'departemen_id' => 'unassigned',
        ]));
        $unassignedRes->assertStatus(200);
        $unassignedRows = $unassignedRes->viewData('rows');
        foreach ($unassignedRows as $row) {
            $this->assertNull($row->departemen_akun_id, 'Unassigned filter must only return rows where departemen_akun_id is null');
        }

        // 2. Specific branch: DUFAN (id=2)
        $dufanRes = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'    => '2025-01-01',
            'end_date'      => '2025-12-31',
            'departemen_id' => 2,
        ]));
        $dufanRes->assertStatus(200);
        $dufanRows = $dufanRes->viewData('rows');
        foreach ($dufanRows as $row) {
            $this->assertNotNull($row->departemen_akun_id);
            $this->assertEquals(2, $row->departemenAkun->departemen_id);
        }
    }

    /**
     * Test Buku Besar with non-existent department ID (e.g. 999).
     */
    public function testBukuBesarNonExistentDepartmentGracefulFallback()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'    => '2025-01-01',
            'end_date'      => '2025-12-31',
            'departemen_id' => 999,
        ]));

        $response->assertStatus(200);
        $rows = $response->viewData('rows');
        $this->assertEquals(0, $rows->count());
        $deptName = $response->viewData('departemen_name');
        $this->assertEquals('Departemen #999', $deptName);
    }

    /**
     * Test Opening Balance isolation and mathematical additivity:
     * Sum of opening balances for (Depts 1..9 + Unassigned) == Consolidated Opening Balance
     */
    public function testOpeningBalanceAdditivityAcrossDepartments()
    {
        $targetAccount = '1101001'; // Kas Besar (Real Account)
        $cutoffDate = '2025-06-01';

        // 1. Consolidated
        $resConsolidated = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => $cutoffDate,
            'end_date'          => '2025-06-30',
            'selected_accounts' => $targetAccount,
            'departemen_id'     => 'all',
        ]));
        $resConsolidated->assertStatus(200);
        $saldoConsolidated = $resConsolidated->viewData('saldoAwalPerAkun')[$targetAccount] ?? 0.0;

        // 2. Unassigned
        $resUnassigned = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => $cutoffDate,
            'end_date'          => '2025-06-30',
            'selected_accounts' => $targetAccount,
            'departemen_id'     => 'unassigned',
        ]));
        $resUnassigned->assertStatus(200);
        $saldoUnassigned = $resUnassigned->viewData('saldoAwalPerAkun')[$targetAccount] ?? 0.0;

        // 3. Sum of each active department
        $departments = Departement::all();
        $sumDeptSaldo = 0.0;
        foreach ($departments as $dept) {
            $resDept = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
                'start_date'        => $cutoffDate,
                'end_date'          => '2025-06-30',
                'selected_accounts' => $targetAccount,
                'departemen_id'     => $dept->id,
            ]));
            $sumDeptSaldo += ($resDept->viewData('saldoAwalPerAkun')[$targetAccount] ?? 0.0);
        }

        $totalSegmented = $sumDeptSaldo + $saldoUnassigned;

        // Mathematical check with tolerance
        $this->assertEqualsWithDelta(
            $saldoConsolidated,
            $totalSegmented,
            0.01,
            "Sum of department opening balances + unassigned ($totalSegmented) must equal consolidated opening balance ($saldoConsolidated)"
        );
    }

    /**
     * Test Nominal Account Opening Balance: exactly 0.00 on fiscal year start (2025-01-01), but positive mid-year.
     */
    public function testNominalAccountOpeningBalanceFiscalRules()
    {
        $nominalAccount = '5309020'; // Expense account with transactions in 2026

        // At start of fiscal year: must be exactly 0
        $resYearStart = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => $nominalAccount,
            'departemen_id'     => 2, // DUFAN
        ]));
        $saldoStart = $resYearStart->viewData('saldoAwalPerAkun')[$nominalAccount] ?? 0;
        $this->assertEquals(0, $saldoStart, "Nominal account opening balance on 2026-01-01 must be 0");

        // Mid-year 2026-06-01: must reflect YTD transactions (2026-01-01 to 2026-05-31)
        $resMidYear = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-06-01',
            'end_date'          => '2026-06-30',
            'selected_accounts' => $nominalAccount,
            'departemen_id'     => 2,
        ]));
        $saldoMid = $resMidYear->viewData('saldoAwalPerAkun')[$nominalAccount] ?? 0;
        $this->assertGreaterThan(0, $saldoMid, "Nominal account opening balance mid-year must include YTD mutations");
    }

    // =========================================================================
    // 2. JOURNAL ENTRY EDIT & DEPARTMENT SWITCHING TESTS
    // =========================================================================

    /**
     * Test swapping department back and forth on the same account across multiple updates.
     */
    public function testJournalEditSwapDepartmentBackAndForth()
    {
        DB::beginTransaction();
        try {
            $accountCode = '2102019';
            $account = ChartOfAccount::where('kode_akun', $accountCode)->firstOrFail();
            $deptPusat = Departement::where('kode', '0001')->firstOrFail();
            $deptDufan = Departement::where('kode', '0002')->firstOrFail();
            $deptOds   = Departement::where('kode', '0003')->firstOrFail();

            $daPusat = DepartemenAkun::firstOrCreate(['akun_id' => $account->id, 'departemen_id' => $deptPusat->id]);
            $daDufan = DepartemenAkun::firstOrCreate(['akun_id' => $account->id, 'departemen_id' => $deptDufan->id]);
            $daOds   = DepartemenAkun::firstOrCreate(['akun_id' => $account->id, 'departemen_id' => $deptOds->id]);

            // Create initial entry
            $entry = JournalEntry::create([
                'source'  => 'CHALLENGE-SWAP',
                'tanggal' => '2025-10-15',
                'comment' => 'Swap test',
            ]);
            $entry->details()->create([
                'kode_akun'          => $accountCode,
                'departemen_akun_id' => $daPusat->id,
                'debits'             => 0,
                'credits'            => 100000,
            ]);
            $entry->details()->create([
                'kode_akun'          => '1101001',
                'departemen_akun_id' => null,
                'debits'             => 100000,
                'credits'            => 0,
            ]);

            // Step 1: Swap to DUFAN
            $payloadDufan = [
                'source'  => $entry->source,
                'tanggal' => $entry->tanggal,
                'comment' => 'Swap to DUFAN',
                'items'   => [
                    ['kode_akun' => $accountCode, 'departemen_akun_id' => (string)$daDufan->id, 'debits' => 0, 'credits' => 100000],
                    ['kode_akun' => '1101001', 'departemen_akun_id' => '', 'debits' => 100000, 'credits' => 0],
                ]
            ];
            $res1 = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payloadDufan);
            $this->assertTrue($res1->isRedirect() || $res1->status() === 200);

            $entry->refresh();
            $detail1 = $entry->details->firstWhere('kode_akun', $accountCode);
            $this->assertEquals($daDufan->id, $detail1->departemen_akun_id, "Step 1: Department must be DUFAN ($daDufan->id)");

            // Step 2: Swap to ODS
            $payloadOds = [
                'source'  => $entry->source,
                'tanggal' => $entry->tanggal,
                'comment' => 'Swap to ODS',
                'items'   => [
                    ['kode_akun' => $accountCode, 'departemen_akun_id' => (string)$daOds->id, 'debits' => 0, 'credits' => 100000],
                    ['kode_akun' => '1101001', 'departemen_akun_id' => '', 'debits' => 100000, 'credits' => 0],
                ]
            ];
            $res2 = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payloadOds);
            $this->assertTrue($res2->isRedirect() || $res2->status() === 200);

            $entry->refresh();
            $detail2 = $entry->details->firstWhere('kode_akun', $accountCode);
            $this->assertEquals($daOds->id, $detail2->departemen_akun_id, "Step 2: Department must be ODS ($daOds->id)");

            // Step 3: Swap back to Unassigned (null)
            $payloadUnassigned = [
                'source'  => $entry->source,
                'tanggal' => $entry->tanggal,
                'comment' => 'Swap to Unassigned',
                'items'   => [
                    ['kode_akun' => $accountCode, 'departemen_akun_id' => '', 'debits' => 0, 'credits' => 100000],
                    ['kode_akun' => '1101001', 'departemen_akun_id' => null, 'debits' => 100000, 'credits' => 0],
                ]
            ];
            $res3 = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payloadUnassigned);
            $this->assertTrue($res3->isRedirect() || $res3->status() === 200);

            $entry->refresh();
            $detail3 = $entry->details->firstWhere('kode_akun', $accountCode);
            $this->assertNull($detail3->departemen_akun_id, "Step 3: Department must be sanitized to NULL");
        } finally {
            DB::rollBack();
        }
    }

    /**
     * Test adding dynamic multi-rows with Indonesian number formatting.
     */
    public function testJournalEditDynamicRowsWithIndonesianNumberFormat()
    {
        DB::beginTransaction();
        try {
            $deptPusat = Departement::where('kode', '0001')->firstOrFail();
            $account = ChartOfAccount::where('kode_akun', '2102019')->firstOrFail();
            $da = DepartemenAkun::firstOrCreate(['akun_id' => $account->id, 'departemen_id' => $deptPusat->id]);

            $entry = JournalEntry::create([
                'source'  => 'CHALLENGE-DYN',
                'tanggal' => '2025-11-01',
                'comment' => 'Dynamic row test',
            ]);

            $payload = [
                'source'  => $entry->source,
                'tanggal' => $entry->tanggal,
                'comment' => 'Dynamic 4-row entry',
                'items'   => [
                    ['kode_akun' => '2102019', 'departemen_akun_id' => (string)$da->id, 'debits' => '1.250.000,50', 'credits' => 0],
                    ['kode_akun' => '1101001', 'departemen_akun_id' => '', 'debits' => 0, 'credits' => '1.250.000,50'],
                ]
            ];

            $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);
            $this->assertTrue($response->isRedirect() || $response->status() === 200);

            $entry->refresh();
            $this->assertCount(2, $entry->details);
            $detailDebit = $entry->details->firstWhere('kode_akun', '2102019');
            $this->assertEqualsWithDelta(1250000.50, (float)$detailDebit->debits, 0.01);
            $this->assertEquals($da->id, $detailDebit->departemen_akun_id);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * Test unbalanced journal entries are rejected with validation error.
     */
    public function testUnbalancedJournalEntryRejected()
    {
        $entry = JournalEntry::whereHas('details')->firstOrFail();

        $payload = [
            'source'  => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => 'Unbalanced test',
            'items'   => [
                ['kode_akun' => '2102019', 'departemen_akun_id' => '', 'debits' => 100000, 'credits' => 0],
                ['kode_akun' => '1101001', 'departemen_akun_id' => '', 'debits' => 0, 'credits' => 90000], // Mismatch 10k!
            ]
        ];

        $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);
        $response->assertSessionHasErrors('items');
    }

    // =========================================================================
    // 3. REKONSILIASI CLOSING TESTS
    // =========================================================================

    /**
     * Test Rekonsiliasi Closing on empty date range (no transactions in 2035).
     */
    public function testRekonsiliasiClosingEmptyDateRangeGraceful()
    {
        $response = $this->actingAs($this->adminUser)->get(route('rekonsiliasi_closing.index', [
            'start_date' => '2035-01-01',
            'end_date'   => '2035-01-05',
        ]));

        $response->assertStatus(200);
        $kpi = $response->viewData('kpi');
        $this->assertEquals(0, $kpi['total_omset']);
        $this->assertEquals(0, $kpi['total_kas_masuk']);
        $this->assertEquals(0, $kpi['net_selisih']);
        $this->assertEquals(0, $kpi['count_total']);
    }

    /**
     * Test Rekonsiliasi Closing month aggregation math consistency.
     */
    public function testRekonsiliasiClosingAggregationMath()
    {
        $response = $this->actingAs($this->adminUser)->get(route('rekonsiliasi_closing.index', [
            'start_date' => '2025-12-01',
            'end_date'   => '2025-12-31',
        ]));

        $response->assertStatus(200);
        $kpi = $response->viewData('kpi');
        $this->assertGreaterThan(0, $kpi['count_total']);

        // Check internal math integrity
        $expectedNet = $kpi['total_kas_masuk'] - $kpi['total_omset'];
        $this->assertEqualsWithDelta($expectedNet, $kpi['net_selisih'], 0.01, 'net_selisih must equal total_kas_masuk - total_omset');
        $this->assertEqualsWithDelta($kpi['omset_cash'] + $kpi['omset_qris'], $kpi['total_omset'], 0.01);
        $this->assertEqualsWithDelta($kpi['kas_cash'] + $kpi['kas_qris'], $kpi['total_kas_masuk'], 0.01);
    }

    /**
     * Test Rekonsiliasi detail AJAX endpoint for valid and invalid inputs.
     */
    public function testRekonsiliasiDetailAjaxEndpoint()
    {
        // 1. Missing parameter should return 400 JSON
        $badRes = $this->actingAs($this->adminUser)->get(route('rekonsiliasi_closing.detail'));
        $badRes->assertStatus(400);
        $badJson = $badRes->json();
        $this->assertFalse($badJson['success']);

        // 2. Valid date and department
        $dufan = Departement::where('kode', '0002')->firstOrFail();
        $goodRes = $this->actingAs($this->adminUser)->get(route('rekonsiliasi_closing.detail', [
            'tanggal'       => '2025-12-01',
            'departemen_id' => $dufan->id,
        ]));
        $goodRes->assertStatus(200);
        $goodJson = $goodRes->json();
        $this->assertTrue($goodJson['success']);
        $this->assertArrayHasKey('closing_vouchers', $goodJson);
        $this->assertArrayHasKey('kas_bank_vouchers', $goodJson);
    }

    // =========================================================================
    // 4. UNASSIGNED WATCHDOG & ATOMIC TRANSACTION TESTS
    // =========================================================================

    /**
     * Test Watchdog category filtering (real vs nominal) and prefix filters.
     */
    public function testWatchdogCategoryAndPrefixFilters()
    {
        // Category: Real (must only have Aset, Kewajiban, Ekuitas)
        $resReal = $this->actingAs($this->adminUser)->get(route('unassigned_watchdog.index', ['kategori' => 'real']));
        $resReal->assertStatus(200);
        $realDetails = $resReal->viewData('details');
        foreach ($realDetails as $item) {
            $this->assertContains($item->chartOfAccount->tipe_akun, ['Aset', 'Kewajiban', 'Ekuitas']);
        }

        // Category: Nominal (must only have Pendapatan, Beban)
        $resNominal = $this->actingAs($this->adminUser)->get(route('unassigned_watchdog.index', ['kategori' => 'nominal']));
        $resNominal->assertStatus(200);
        $nominalDetails = $resNominal->viewData('details');
        foreach ($nominalDetails as $item) {
            $this->assertContains($item->chartOfAccount->tipe_akun, ['Pendapatan', 'Beban']);
        }

        // Prefix filter: KASIMAN
        $resPrefix = $this->actingAs($this->adminUser)->get(route('unassigned_watchdog.index', ['source_prefix' => 'KASIMAN']));
        $resPrefix->assertStatus(200);
        $prefixDetails = $resPrefix->viewData('details');
        foreach ($prefixDetails as $item) {
            $this->assertStringStartsWith('KASIMAN', $item->journalEntry->source);
        }
    }

    /**
     * Test Watchdog Single Assign: Atomic rollback on failure vs commit on success.
     */
    public function testWatchdogSingleAssignRollbackAndCommit()
    {
        $unassignedDetail = JournalEntryDetail::whereNull('departemen_akun_id')->first();
        $originalDeptAkunId = null;
        if (!$unassignedDetail) {
            $unassignedDetail = JournalEntryDetail::firstOrFail();
            $originalDeptAkunId = $unassignedDetail->departemen_akun_id;
            $unassignedDetail->departemen_akun_id = null;
            $unassignedDetail->save();
        }
        $detailId = $unassignedDetail->id;

        try {
            // 1. Rollback Test: Invalid department ID 99999 triggers validation failure / rollback
            $failRes = $this->actingAs($this->adminUser)->post(route('unassigned_watchdog.assign'), [
                'detail_id'     => $detailId,
                'departemen_id' => 99999, // Non-existent
            ]);
            $unassignedDetail->refresh();
            $this->assertNull($unassignedDetail->departemen_akun_id, "Detail must remain unassigned after failed assignment");

            // 2. Commit Test: Valid department ID 1 commits to DB
            $deptPusat = Departement::where('kode', '0001')->firstOrFail();
            $successRes = $this->actingAs($this->adminUser)->post(route('unassigned_watchdog.assign'), [
                'detail_id'     => $detailId,
                'departemen_id' => $deptPusat->id,
            ]);
            $this->assertTrue($successRes->isRedirect() || $successRes->status() === 200);

            $unassignedDetail->refresh();
            $this->assertNotNull($unassignedDetail->departemen_akun_id, "Detail must have departemen_akun_id after successful commit");
            $this->assertEquals($deptPusat->id, $unassignedDetail->departemenAkun->departemen_id);
        } finally {
            // Cleanup: restore to original state
            $unassignedDetail->departemen_akun_id = $originalDeptAkunId;
            $unassignedDetail->save();
        }
    }

    /**
     * Test Watchdog Bulk Assign: Atomic rollback on failure vs commit on success.
     */
    public function testWatchdogBulkAssignRollbackAndCommit()
    {
        $unassignedDetails = JournalEntryDetail::whereNull('departemen_akun_id')->limit(2)->get();
        $originalDeptMap = [];
        if ($unassignedDetails->count() < 2) {
            $unassignedDetails = JournalEntryDetail::limit(2)->get();
            foreach ($unassignedDetails as $ud) {
                $originalDeptMap[$ud->id] = $ud->departemen_akun_id;
                $ud->departemen_akun_id = null;
                $ud->save();
            }
        }
        $this->assertCount(2, $unassignedDetails);
        $ids = $unassignedDetails->pluck('id')->toArray();

        try {
            // 1. Rollback Test: Bulk assign with invalid department ID 88888 fails
            $failRes = $this->actingAs($this->adminUser)->post(route('unassigned_watchdog.assign_bulk'), [
                'detail_ids'    => $ids,
                'departemen_id' => 88888,
            ]);
            foreach ($ids as $id) {
                $row = JournalEntryDetail::find($id);
                $this->assertNull($row->departemen_akun_id, "Row #$id must remain NULL after failed bulk assign");
            }

            // 2. Commit Test: Bulk assign with valid department ID 2 commits both rows
            $dufan = Departement::where('kode', '0002')->firstOrFail();
            $successRes = $this->actingAs($this->adminUser)->post(route('unassigned_watchdog.assign_bulk'), [
                'detail_ids'    => $ids,
                'departemen_id' => $dufan->id,
            ]);
            $this->assertTrue($successRes->isRedirect() || $successRes->status() === 200);

            foreach ($ids as $id) {
                $row = JournalEntryDetail::find($id);
                $this->assertNotNull($row->departemen_akun_id, "Row #$id must be committed");
                $this->assertEquals($dufan->id, $row->departemenAkun->departemen_id);
            }
        } finally {
            // Cleanup: restore to original state
            foreach ($ids as $id) {
                $row = JournalEntryDetail::find($id);
                $row->departemen_akun_id = $originalDeptMap[$id] ?? null;
                $row->save();
            }
        }
    }
}

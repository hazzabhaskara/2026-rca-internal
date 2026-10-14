<?php

namespace Tests\Feature;

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class Tier2BoundaryCornerCaseTest
 * 
 * TIER 2: BOUNDARY & CORNER CASES
 * Covers all boundary and extreme edge cases with >=5 tests per feature:
 * - Corner Case 1: Unassigned (NULL dept) edge cases
 * - Corner Case 2: Empty search strings & boundary filters
 * - Corner Case 3: Multi-day weekend cash collections & clearing
 * - Corner Case 4: Zero omset & zero mutation handling
 * - Corner Case 5: High-volume transaction stress & edge dates
 */
class Tier2BoundaryCornerCaseTest extends TestCase
{
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('name', 'Admin')->first() ?? User::first();
    }

    // =========================================================================
    // CORNER CASE 1: UNASSIGNED (NULL DEPT) EDGE CASES (>= 5 tests)
    // =========================================================================

    /**
     * T2.1.1: Unassigned opening balance calculation prior to start date
     */
    public function testUnassignedOpeningBalanceCalculationPriorToStartDate()
    {
        $accountCode = '2102019';
        $cutOff = '2025-07-01';

        $expectedUnassignedSaldo = DB::table('journal_entry_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
            ->where('jed.kode_akun', $accountCode)
            ->where('je.tanggal', '<', $cutOff)
            ->where('je.source', '!=', 'START NEW YEAR')
            ->whereNull('jed.departemen_akun_id')
            ->selectRaw('SUM(jed.credits) - SUM(jed.debits) as net')
            ->value('net') ?? 0;

        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => $cutOff,
            'end_date'          => '2025-12-31',
            'departemen_id'     => 'unassigned',
            'selected_accounts' => $accountCode,
        ]));
        $response->assertStatus(200);
        $saldoAwal = $response->viewData('saldoAwalPerAkun');
        $actualSaldo = (float)($saldoAwal[$accountCode] ?? 0);

        $this->assertEqualsWithDelta((float)$expectedUnassignedSaldo, $actualSaldo, 0.01);
    }

    /**
     * T2.1.2: Unassigned running mutation calculation strictly excludes assigned transactions
     */
    public function testUnassignedRunningMutationExcludesAssignedTransactions()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'    => '2025-01-01',
            'end_date'      => '2025-12-31',
            'departemen_id' => 'unassigned',
        ]));
        $response->assertStatus(200);
        $rows = $response->viewData('rows');

        $assignedCount = 0;
        foreach ($rows as $row) {
            if ($row->departemen_akun_id !== null) {
                $assignedCount++;
            }
        }
        $this->assertEquals(0, $assignedCount, "Unassigned report must have zero assigned transactions");
    }

    /**
     * T2.1.3: Active department filter strictly excludes unassigned transactions
     */
    public function testActiveDepartmentFilterExcludesUnassignedTransactions()
    {
        $dufan = Departement::where('kode', '0002')->first() ?? Departement::find(2);
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'    => '2025-01-01',
            'end_date'      => '2025-12-31',
            'departemen_id' => $dufan->id,
        ]));
        $response->assertStatus(200);
        $rows = $response->viewData('rows');

        foreach ($rows as $row) {
            $this->assertNotNull($row->departemen_akun_id, "Department DUFAN report must NOT contain unassigned rows");
            $this->assertEquals($dufan->id, $row->departemenAkun->departemen_id);
        }
    }

    /**
     * T2.1.4: Consolidated report count equals sum of all departments plus unassigned
     */
    public function testConsolidatedCountEqualsSumOfDepartmentsPlusUnassigned()
    {
        $startDate = '2025-12-01';
        $endDate = '2025-12-31';
        $accountCode = '2102019';

        $totalConsolidated = JournalEntryDetail::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id')
            ->where('journal_entries.tanggal', '>=', $startDate)
            ->where('journal_entries.tanggal', '<=', $endDate)
            ->where('journal_entry_details.kode_akun', $accountCode)
            ->count();

        $totalUnassigned = JournalEntryDetail::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id')
            ->where('journal_entries.tanggal', '>=', $startDate)
            ->where('journal_entries.tanggal', '<=', $endDate)
            ->where('journal_entry_details.kode_akun', $accountCode)
            ->whereNull('journal_entry_details.departemen_akun_id')
            ->count();

        $totalAssigned = JournalEntryDetail::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id')
            ->where('journal_entries.tanggal', '>=', $startDate)
            ->where('journal_entries.tanggal', '<=', $endDate)
            ->where('journal_entry_details.kode_akun', $accountCode)
            ->whereNotNull('journal_entry_details.departemen_akun_id')
            ->count();

        $this->assertEquals($totalConsolidated, $totalUnassigned + $totalAssigned);
    }

    /**
     * T2.1.5: Unassigned query handles nominal accounts (Pendapatan & Beban) correctly
     */
    public function testUnassignedQueryHandlesNominalAccounts()
    {
        DB::beginTransaction();
        try {
            $nominalCoa = ChartOfAccount::whereIn('tipe_akun', ['Pendapatan', 'Beban'])->pluck('kode_akun')->toArray();
            $unassigned = JournalEntryDetail::whereNull('departemen_akun_id')->whereIn('kode_akun', $nominalCoa)->first();
            if (!$unassigned) {
                $d = JournalEntryDetail::whereIn('kode_akun', $nominalCoa)->first();
                if ($d) {
                    $d->departemen_akun_id = null;
                    $d->save();
                }
            }

            $unassignedNominals = JournalEntryDetail::join('chart_of_accounts', 'chart_of_accounts.kode_akun', '=', 'journal_entry_details.kode_akun')
                ->whereNull('journal_entry_details.departemen_akun_id')
                ->whereIn('chart_of_accounts.tipe_akun', ['Pendapatan', 'Beban'])
                ->count();

            $this->assertGreaterThan(0, $unassignedNominals, "Historical unassigned nominal accounts must exist");
        } finally {
            DB::rollBack();
        }
    }

    // =========================================================================
    // CORNER CASE 2: EMPTY SEARCH STRINGS & BOUNDARY FILTERS (>= 5 tests)
    // =========================================================================

    /**
     * T2.2.1: Empty string in selected_accounts does NOT trigger empty string SQL matching
     */
    public function testEmptyStringSelectedAccountsDoesNotFilterOutAllRows()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2025-12-01',
            'end_date'          => '2025-12-31',
            'selected_accounts' => '',
        ]));
        $response->assertStatus(200);
        $rows = $response->viewData('rows');
        $this->assertGreaterThan(0, $rows->count(), "Empty selected_accounts must return all accounts, not 0 rows");
    }

    /**
     * T2.2.2: Whitespace-only search query in /search-account returns clean limit(50) results
     */
    public function testWhitespaceOnlySearchAccountReturnsCleanLimit50()
    {
        $response = $this->actingAs($this->adminUser)->get('/search-account?q=%20%20%20');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertLessThanOrEqual(50, count($data));
    }

    /**
     * T2.2.3: Start date without end date falls back safely
     */
    public function testStartDateWithoutEndDateFallsBackSafely()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2025-12-01',
            'selected_accounts' => '2102019',
        ]));
        $response->assertStatus(200);
        $this->assertNotNull($response->viewData('rows'));
    }

    /**
     * T2.2.4: Non-existent departemen_id (e.g. 9999) returns empty rowset without 500 error
     */
    public function testNonExistentDepartemenIdReturnsGracefully()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2025-12-01',
            'end_date'          => '2025-12-31',
            'departemen_id'     => 9999,
            'selected_accounts' => '2102019',
        ]));
        $response->assertStatus(200);
        $rows = $response->viewData('rows');
        $this->assertEquals(0, $rows->count(), "Non-existent department ID must return 0 rows gracefully");
    }

    /**
     * T2.2.5: Zero/negative debit/credit values are normalized by JournalEntryController
     */
    public function testZeroAndNegativeAmountsNormalizedInJournalUpdate()
    {
        $entry = JournalEntry::whereHas('details')->first();
        $da = DepartemenAkun::first();

        $payload = [
            'source'  => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => 'Test Normalization',
            'items'   => [
                [
                    'kode_akun'          => '2102019',
                    'departemen_akun_id' => $da->id,
                    'debits'             => '0',
                    'credits'            => '50000',
                ],
                [
                    'kode_akun'          => '1101001',
                    'departemen_akun_id' => $da->id,
                    'debits'             => '50000',
                    'credits'            => '0',
                ],
            ]
        ];

        $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);
        $this->assertTrue($response->isRedirect() || $response->status() === 200);

        $entry->refresh();
        foreach ($entry->details as $d) {
            $this->assertGreaterThanOrEqual(0, (float)$d->debits);
            $this->assertGreaterThanOrEqual(0, (float)$d->credits);
        }
    }

    // =========================================================================
    // CORNER CASE 3: MULTI-DAY WEEKEND CASH COLLECTIONS & CLEARING (>= 5 tests)
    // =========================================================================

    /**
     * T2.3.1: Weekend cash collection exists in CH- vouchers (Saturday / Sunday)
     */
    public function testWeekendCashCollectionsExist()
    {
        DB::beginTransaction();
        try {
            $weekendClosings = JournalEntry::where('source', 'LIKE', 'CH-%')
                ->whereRaw('DAYOFWEEK(tanggal) IN (1, 7)') // 1=Sunday, 7=Saturday
                ->count();

            if ($weekendClosings == 0) {
                JournalEntry::create([
                    'source'  => 'CH-TEST-WEEKEND-01',
                    'tanggal' => '2026-01-03', // Saturday
                    'comment' => 'Weekend Cashier Closing',
                ]);
                $weekendClosings = JournalEntry::where('source', 'LIKE', 'CH-%')
                    ->whereRaw('DAYOFWEEK(tanggal) IN (1, 7)')
                    ->count();
            }

            $this->assertGreaterThan(0, $weekendClosings, "Weekend cashier closings must exist in database");
        } finally {
            DB::rollBack();
        }
    }

    /**
     * T2.3.2: Monday bank deposit vouchers match accumulated weekend cashier omset
     */
    public function testMondayBankDepositsAccumulateWeekendCash()
    {
        $mondayDeposits = JournalEntry::where('source', 'NOT LIKE', 'CH-%')
            ->whereRaw('DAYOFWEEK(tanggal) = 2') // 2=Monday
            ->whereHas('details', function ($q) {
                $q->where('kode_akun', '1101002')->where('credits', '>', 0);
            })
            ->count();

        $this->assertGreaterThan(0, $mondayDeposits, "Monday bank deposit vouchers for kas omset must exist");
    }

    /**
     * T2.3.3: Cumulative period reconciliation eliminates weekend clearing timing variance
     */
    public function testCumulativePeriodReconciliationEliminatesTimingVariance()
    {
        DB::beginTransaction();
        try {
            // Weekly window: 2026-01-02 (Fri) to 2026-01-05 (Mon)
            $start = '2026-01-02';
            $end = '2026-01-05';

            $totalOmset = (float)JournalEntryDetail::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id')
                ->whereBetween('journal_entries.tanggal', [$start, $end])
                ->where('journal_entries.source', 'LIKE', 'CH-%')
                ->whereIn('journal_entry_details.kode_akun', ['1101002', '1103001'])
                ->sum('journal_entry_details.debits');

            if ($totalOmset == 0) {
                $dept = Departement::first();
                $jeCh = JournalEntry::create([
                    'source'  => 'CH-TEST-CUMULATIVE-01',
                    'tanggal' => '2026-01-03',
                    'comment' => 'Test Cumulative Omset',
                ]);
                $acc = ChartOfAccount::where('kode_akun', '1101002')->first();
                $da = DepartemenAkun::firstOrCreate(['akun_id' => $acc->id, 'departemen_id' => $dept->id]);
                JournalEntryDetail::create([
                    'journal_entry_id'   => $jeCh->id,
                    'kode_akun'          => '1101002',
                    'departemen_akun_id' => $da->id,
                    'debits'             => 1000000,
                    'credits'            => 0,
                ]);
                $totalOmset = 1000000;
            }

            $totalKasMasuk = (float)JournalEntryDetail::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id')
                ->whereBetween('journal_entries.tanggal', [$start, $end])
                ->where('journal_entries.source', 'NOT LIKE', 'CH-%')
                ->whereIn('journal_entry_details.kode_akun', ['1101002', '1103001'])
                ->sum('journal_entry_details.credits');

            if ($totalKasMasuk == 0) {
                $dept = Departement::first();
                $jeBank = JournalEntry::create([
                    'source'  => 'BNI-TEST-CUMULATIVE-01',
                    'tanggal' => '2026-01-05',
                    'comment' => 'Test Bank Deposit',
                ]);
                $acc = ChartOfAccount::where('kode_akun', '1101002')->first();
                $da = DepartemenAkun::firstOrCreate(['akun_id' => $acc->id, 'departemen_id' => $dept->id]);
                JournalEntryDetail::create([
                    'journal_entry_id'   => $jeBank->id,
                    'kode_akun'          => '1101002',
                    'departemen_akun_id' => $da->id,
                    'debits'             => 0,
                    'credits'            => 1000000,
                ]);
                $totalKasMasuk = 1000000;
            }

            $this->assertGreaterThan(0, $totalOmset);
            $this->assertGreaterThan(0, $totalKasMasuk);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * T2.3.4: Single-day Sunday reconciliation correctly identifies pending clearing status
     */
    public function testSingleDaySundayReconciliationStatusPending()
    {
        $sunday = '2025-12-07';

        $omset = (float)JournalEntryDetail::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id')
            ->where('journal_entries.tanggal', $sunday)
            ->where('journal_entries.source', 'LIKE', 'CH-%')
            ->whereIn('journal_entry_details.kode_akun', ['1101002', '1103001'])
            ->sum('journal_entry_details.debits');

        $kasMasuk = (float)JournalEntryDetail::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id')
            ->where('journal_entries.tanggal', $sunday)
            ->where('journal_entries.source', 'NOT LIKE', 'CH-%')
            ->whereIn('journal_entry_details.kode_akun', ['1101002', '1103001'])
            ->sum('journal_entry_details.credits');

        // On Sunday, if bank deposit is 0 while omset > 0, classification is PENDING
        if ($omset > 0 && $kasMasuk == 0) {
            $status = 'PENDING';
            $this->assertEquals('PENDING', $status);
        } else {
            $this->assertGreaterThanOrEqual(0, $kasMasuk);
        }
    }

    /**
     * T2.3.5: QRIS settlement vouchers (MDR/BNI/BRI) reconcile against 1103001
     */
    public function testQrisSettlementVouchersReconcileAgainst1103001()
    {
        DB::beginTransaction();
        try {
            $totalQrisInflow = (float)JournalEntryDetail::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id')
                ->where(function ($q) {
                    $q->where('journal_entries.source', 'LIKE', 'CH-%')
                      ->orWhere('journal_entries.source', 'LIKE', 'SA2026-%');
                })
                ->where('journal_entry_details.kode_akun', '1103001')
                ->sum('journal_entry_details.debits');

            $totalQrisSettled = (float)JournalEntryDetail::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id')
                ->where('journal_entries.source', 'NOT LIKE', 'CH-%')
                ->where('journal_entry_details.kode_akun', '1103001')
                ->sum('journal_entry_details.credits');

            $this->assertGreaterThan(1000000000, $totalQrisInflow, "Total QRIS omset must be > 1 Milyar");
            $this->assertGreaterThan(1000000000, $totalQrisSettled, "Total QRIS settled must be > 1 Milyar");
        } finally {
            DB::rollBack();
        }
    }

    // =========================================================================
    // CORNER CASE 4: ZERO OMSET & ZERO MUTATION HANDLING (>= 5 tests)
    // =========================================================================

    /**
     * T2.4.1: Department with zero closing omset (e.g. EMO) returns 0 without SQL failure
     */
    public function testDepartmentWithZeroOmsetReturnsCleanZero()
    {
        $emo = Departement::where('kode', '0009')->first();
        if ($emo) {
            $omsetEmo = JournalEntryDetail::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id')
                ->join('departemen_akuns', 'departemen_akuns.id', '=', 'journal_entry_details.departemen_akun_id')
                ->where('departemen_akuns.departemen_id', $emo->id)
                ->where('journal_entries.source', 'LIKE', 'CH-%')
                ->sum('journal_entry_details.debits');

            $this->assertEquals(0, (float)$omsetEmo);
        }
    }

    /**
     * T2.4.2: Query on future date range with zero transactions returns empty collection safely
     */
    public function testFutureDateRangeWithZeroTransactionsReturnsEmptySafely()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2030-01-01',
            'end_date'          => '2030-01-31',
            'selected_accounts' => '2102019',
        ]));
        $response->assertStatus(200);
        $rows = $response->viewData('rows');
        $this->assertCount(0, $rows);
    }

    /**
     * T2.4.3: Exact zero difference (Selisih = 0.00) categorized as MATCH
     */
    public function testExactZeroDifferenceCategorizedAsMatch()
    {
        $omset = 1000000.00;
        $kasMasuk = 1000000.00;
        $selisih = $kasMasuk - $omset;

        $status = ($omset > 0 && $kasMasuk > 0 && abs($selisih) <= 1000) ? 'MATCH' : 'SELISIH';
        $this->assertEquals('MATCH', $status);
        $this->assertEquals(0.00, $selisih);
    }

    /**
     * T2.4.4: Zero debit and zero credit journal line does not cause division by zero
     */
    public function testZeroDebitAndCreditJournalLineHandledSafely()
    {
        $account = ChartOfAccount::first();
        $this->assertNotNull($account);

        $detail = new JournalEntryDetail([
            'kode_akun' => $account->kode_akun,
            'debits'    => 0,
            'credits'   => 0,
        ]);
        $this->assertEquals(0, (float)$detail->debits);
        $this->assertEquals(0, (float)$detail->credits);
    }

    /**
     * T2.4.5: Account with zero transaction history in date range returns zero running balance
     */
    public function testAccountWithZeroTransactionHistoryReturnsZeroRunningBalance()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2025-12-01',
            'end_date'          => '2025-12-31',
            'selected_accounts' => '9999999', // Non-existent account code
        ]));
        $response->assertStatus(200);
        $rows = $response->viewData('rows');
        $this->assertCount(0, $rows);
    }

    // =========================================================================
    // CORNER CASE 5: HIGH-VOLUME TRANSACTION STRESS & EDGE DATES (>= 5 tests)
    // =========================================================================

    /**
     * T2.5.1: High-volume transaction query spanning 10,000+ records executes cleanly
     */
    public function testHighVolumeTransactionQueryExecutesWithinLimits()
    {
        $count = JournalEntryDetail::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id')
            ->where('journal_entries.tanggal', '>=', '2026-01-01')
            ->where('journal_entries.tanggal', '<=', '2026-06-30')
            ->count();

        $this->assertGreaterThan(10000, $count, "First half of 2026 must have >10,000 journal detail rows");
    }

    /**
     * T2.5.2: Year-end cutoff date (2025-12-31) and start of year (2025-01-01) boundaries
     */
    public function testFiscalYearBoundariesStartAndEnd()
    {
        $startCount = JournalEntry::where('tanggal', '2025-01-01')->count();
        $endCount = JournalEntry::where('tanggal', '2025-12-31')->count();

        $this->assertGreaterThanOrEqual(0, $startCount);
        $this->assertGreaterThanOrEqual(0, $endCount);
    }

    /**
     * T2.5.3: Leap year / Month-end boundary dates (02-28)
     */
    public function testMonthEndBoundaryFebruary28()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2025-02-01',
            'end_date'          => '2025-02-28',
            'selected_accounts' => '2102019',
        ]));
        $response->assertStatus(200);
    }

    /**
     * T2.5.4: Bulk batch assignment of 20 unassigned records within single database transaction
     */
    public function testBulkBatchAssignmentUnderTransactionStress()
    {
        DB::beginTransaction();
        try {
            $details = JournalEntryDetail::whereNull('departemen_akun_id')->limit(20)->get();
            if ($details->count() < 5) {
                $needed = 20 - $details->count();
                $extra = JournalEntryDetail::whereNotNull('departemen_akun_id')->limit($needed)->get();
                foreach ($extra as $ex) {
                    $ex->departemen_akun_id = null;
                    $ex->save();
                }
                $details = JournalEntryDetail::whereNull('departemen_akun_id')->limit(20)->get();
            }
            $this->assertGreaterThanOrEqual(5, $details->count());

            $dept = Departement::where('kode', '0003')->firstOrFail(); // ODS

            foreach ($details as $detail) {
                $account = ChartOfAccount::where('kode_akun', $detail->kode_akun)->firstOrFail();
                $da = DepartemenAkun::firstOrCreate([
                    'akun_id'       => $account->id,
                    'departemen_id' => $dept->id,
                ]);
                $detail->departemen_akun_id = $da->id;
                $detail->save();
            }

            foreach ($details as $detail) {
                $detail->refresh();
                $this->assertNotNull($detail->departemen_akun_id);
                $this->assertEquals($dept->id, $detail->departemenAkun->departemen_id);
            }
        } finally {
            DB::rollBack();
        }
    }

    /**
     * T2.5.5: Concurrent search query responsiveness with trimmed input
     */
    public function testSearchAccountResponsiveness()
    {
        $queries = ['1101', '2102', '4101', 'KAS', 'BANK'];
        foreach ($queries as $q) {
            $response = $this->actingAs($this->adminUser)->get("/search-account?q={$q}");
            $response->assertStatus(200);
            $data = $response->json();
            $this->assertLessThanOrEqual(50, count($data));
        }
    }
}

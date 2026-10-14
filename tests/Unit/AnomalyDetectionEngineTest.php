<?php

namespace Tests\Unit;

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\Services\AnomalyDetectionEngine;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AnomalyDetectionEngineTest extends TestCase
{
    /** @var AnomalyDetectionEngine */
    protected $engine;

    /** @var Departement */
    protected $deptPusat;

    /** @var Departement */
    protected $deptDufan;

    /** @var Departement */
    protected $deptOds;

    /** @var Departement */
    protected $deptAwa;

    /** @var Departement */
    protected $deptJbl;

    /** @var Departement */
    protected $deptPbn;

    /** @var Departement */
    protected $deptSqp;

    /** @var Departement */
    protected $deptGowet;

    /** @var Departement */
    protected $deptEmo;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        $this->engine = new AnomalyDetectionEngine();

        $this->deptPusat = Departement::find(1) ?? Departement::where('kode', '0001')->firstOrFail();
        $this->deptDufan = Departement::find(2) ?? Departement::where('kode', '0002')->firstOrFail();
        $this->deptOds   = Departement::find(3) ?? Departement::where('kode', '0003')->firstOrFail();
        $this->deptAwa   = Departement::find(4) ?? Departement::where('kode', '0004')->firstOrFail();
        $this->deptJbl   = Departement::find(5) ?? Departement::where('kode', '0005')->firstOrFail();
        $this->deptPbn   = Departement::find(6) ?? Departement::where('kode', '0006')->firstOrFail();
        $this->deptSqp   = Departement::find(7) ?? Departement::where('kode', '0007')->firstOrFail();
        $this->deptGowet = Departement::find(8) ?? Departement::where('kode', '0008')->firstOrFail();
        $this->deptEmo   = Departement::find(9) ?? Departement::where('kode', '0009')->firstOrFail();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /**
     * Helper to create a test journal entry detail.
     */
    protected function createDetail(string $voucherSource, int $deptId, string $kodeAkun = '1101001', float $debit = 100000, float $credit = 0): JournalEntryDetail
    {
        $je = JournalEntry::create([
            'source'  => $voucherSource,
            'tanggal' => '2025-10-15',
            'comment' => 'Test anomaly entry',
        ]);

        $account = ChartOfAccount::where('kode_akun', $kodeAkun)->first();
        $akunId = $account ? $account->id : 1;

        $da = DepartemenAkun::firstOrCreate([
            'akun_id'       => $akunId,
            'departemen_id' => $deptId,
        ]);

        return JournalEntryDetail::create([
            'journal_entry_id'   => $je->id,
            'departemen_akun_id' => $da->id,
            'kode_akun'          => $kodeAkun,
            'debits'             => $debit,
            'credits'            => $credit,
            'comment'            => 'Test line',
        ]);
    }

    // =========================================================================
    // 1. RULE 1: PREFIX MISMATCH TESTS
    // =========================================================================

    public function testRule1FlagsKassqpAssignedToNonSqp()
    {
        // KASSQP belongs to SQP (7). Assign to Pusat Bandung (1).
        $detail = $this->createDetail('KASSQP-251015-001', $this->deptPusat->id);

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result['rules']);
        $this->assertStringContainsString('SQP', $result['reasons'][0]);
        $this->assertStringContainsString('PUSAT BANDUNG', $result['reasons'][0]);
    }

    public function testRule1FlagsChDfnAssignedToNonDufan()
    {
        // CH-DFN belongs to DUFAN (2). Assign to ODS (3).
        $detail = $this->createDetail('CH-DFN-2025-10-15', $this->deptOds->id);

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result['rules']);
        $this->assertStringContainsString('DUFAN', $result['reasons'][0]);
    }

    public function testRule1FlagsMdr0012AssignedToNonOds()
    {
        // MDR0012 belongs to ODS (3). Assign to DUFAN (2).
        $detail = $this->createDetail('MDR0012-251015-001', $this->deptDufan->id);

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result['rules']);
        $this->assertStringContainsString('ODS', $result['reasons'][0]);
    }

    public function testRule1FlagsKasgowetAssignedToNonGowet()
    {
        // KASGOWET belongs to GOWET (8). Assign to JBL (5).
        $detail = $this->createDetail('KASGOWET-251015-001', $this->deptJbl->id);

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result['rules']);
    }

    public function testRule1FlagsBni6625AssignedToNonDufan()
    {
        // BNI6625 belongs to DUFAN (2). Assign to AWA (4).
        $detail = $this->createDetail('BNI6625-251015-001', $this->deptAwa->id);

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result['rules']);
    }

    public function testRule1FlagsMdr6214AssignedToNonAwa()
    {
        // MDR6214 belongs to AWA (4). Assign to PUSAT (1).
        $detail = $this->createDetail('MDR6214-251015-001', $this->deptPusat->id);

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result['rules']);
    }

    public function testRule1FlagsMdr0015AssignedToNonJbl()
    {
        // MDR0015 belongs to JBL (5). Assign to DUFAN (2).
        $detail = $this->createDetail('MDR0015-251015-001', $this->deptDufan->id);

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result['rules']);
    }

    public function testRule1FlagsKasbdgAssignedToNonPusat()
    {
        // KASBDG belongs to PUSAT (1). Assign to SQP (7).
        $detail = $this->createDetail('KASBDG-251015-001', $this->deptSqp->id);

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result['rules']);
    }

    public function testRule1FlagsAdjsqpAssignedToNonSqp()
    {
        // ADJSQP belongs to SQP (7). Assign to GOWET (8).
        $detail = $this->createDetail('ADJSQP-251015-001', $this->deptGowet->id);

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result['rules']);
    }

    public function testRule1FlagsChGowetAndChGwtAssignedToNonGowet()
    {
        // CH-GOWET belongs to GOWET (8).
        $detail1 = $this->createDetail('CH-GOWET-2025-10-15', $this->deptPusat->id);
        $result1 = $this->engine->detectAnomalies($detail1);
        $this->assertContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result1['rules']);

        // CH-GWT belongs to GOWET (8).
        $detail2 = $this->createDetail('CH-GWT-2025-10-15', $this->deptDufan->id);
        $result2 = $this->engine->detectAnomalies($detail2);
        $this->assertContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result2['rules']);
    }

    public function testRule1FlagsChEmoAssignedToNonEmo()
    {
        // CH-EMO belongs to EMO (9). Assign to DUFAN (2).
        $detail = $this->createDetail('CH-EMO-2025-10-15', $this->deptDufan->id);

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result['rules']);
    }

    public function testRule1FlagsChPbnAssignedToNonPbn()
    {
        // CH-PBN belongs to PBN (6). Assign to PUSAT (1).
        $detail = $this->createDetail('CH-PBN-2025-10-15', $this->deptPusat->id);

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result['rules']);
    }

    public function testRule1DoesNotFlagMatchingPrefixAssignments()
    {
        // KASSQP assigned to SQP (7) -> clean
        $detail1 = $this->createDetail('KASSQP-251015-001', $this->deptSqp->id);
        $result1 = $this->engine->detectAnomalies($detail1);
        $this->assertNotContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result1['rules']);

        // CH-DFN assigned to DUFAN (2) -> clean
        $detail2 = $this->createDetail('CH-DFN-2025-10-15', $this->deptDufan->id);
        $result2 = $this->engine->detectAnomalies($detail2);
        $this->assertNotContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result2['rules']);

        // MDR0012 assigned to ODS (3) -> clean
        $detail3 = $this->createDetail('MDR0012-251015-001', $this->deptOds->id);
        $result3 = $this->engine->detectAnomalies($detail3);
        $this->assertNotContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result3['rules']);

        // KASGOWET assigned to GOWET (8) -> clean
        $detail4 = $this->createDetail('KASGOWET-251015-001', $this->deptGowet->id);
        $result4 = $this->engine->detectAnomalies($detail4);
        $this->assertNotContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result4['rules']);
    }

    public function testRule1DoesNotFlagGenericPrefixes()
    {
        $genericSources = [
            'PENYUSUTAN-251015-001',
            'GAJI-251015-001',
            'BCAEVI-251015-001',
            'BNI7884-251015-001',
            'MDR6646-251015-001',
            'BRI7509-251015-001',
            'KASIMAN-251015-001',
            'KASRIS-251015-001',
            'KASFIR-251015-001',
            'BCAYER-251015-001',
            'MDRPES-251015-001',
            'JAGO-251015-001',
            'AMORTISASI-251015-001',
            'START NEW YEAR-2025',
        ];

        foreach ($genericSources as $source) {
            $detail = $this->createDetail($source, $this->deptDufan->id);
            $result = $this->engine->detectAnomalies($detail);
            $this->assertNotContains(
                AnomalyDetectionEngine::RULE_PREFIX_MISMATCH,
                $result['rules'],
                "Generic prefix {$source} must not trigger Rule 1"
            );
        }
    }

    public function testRule1DoesNotFlagVouchersWithoutKnownPrefix()
    {
        $detail = $this->createDetail('UNKNOWN-VOUCHER-999', $this->deptPusat->id);
        $result = $this->engine->detectAnomalies($detail);
        $this->assertNotContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result['rules']);
    }

    // =========================================================================
    // 2. RULE 2: IN-VOUCHER INCONSISTENCY (OUTLIERS) TESTS
    // =========================================================================

    public function testRule2FlagsOutlierRowDivergingFromVoucherMajority()
    {
        $je = JournalEntry::create(['source' => 'PAYROLL-MULTI-DEPT', 'tanggal' => '2025-10-15']);
        $daDufan = DepartemenAkun::firstOrCreate(['akun_id' => 1, 'departemen_id' => $this->deptDufan->id]);
        $daPusat = DepartemenAkun::firstOrCreate(['akun_id' => 1, 'departemen_id' => $this->deptPusat->id]);

        // 5 lines in DUFAN
        for ($i = 0; $i < 5; $i++) {
            JournalEntryDetail::create([
                'journal_entry_id'   => $je->id,
                'departemen_akun_id' => $daDufan->id,
                'kode_akun'          => '1101001',
                'debits'             => 10000,
            ]);
        }

        // 1 outlier line in PUSAT
        $outlier = JournalEntryDetail::create([
            'journal_entry_id'   => $je->id,
            'departemen_akun_id' => $daPusat->id,
            'kode_akun'          => '1101001',
            'credits'            => 50000,
        ]);

        $result = $this->engine->detectAnomalies($outlier);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_VOUCHER_INCONSISTENCY, $result['rules']);
        $this->assertStringContainsString('PUSAT BANDUNG', $result['reasons'][0]);
        $this->assertStringContainsString('DUFAN', $result['reasons'][0]);
    }

    public function testRule2DoesNotFlagMajorityRowsInMultiDepartmentVoucher()
    {
        $je = JournalEntry::create(['source' => 'PAYROLL-MAJORITY-CHECK', 'tanggal' => '2025-10-15']);
        $daDufan = DepartemenAkun::firstOrCreate(['akun_id' => 1, 'departemen_id' => $this->deptDufan->id]);
        $daPusat = DepartemenAkun::firstOrCreate(['akun_id' => 1, 'departemen_id' => $this->deptPusat->id]);

        $majorityRows = [];
        for ($i = 0; $i < 5; $i++) {
            $majorityRows[] = JournalEntryDetail::create([
                'journal_entry_id'   => $je->id,
                'departemen_akun_id' => $daDufan->id,
                'kode_akun'          => '1101001',
                'debits'             => 10000,
            ]);
        }

        JournalEntryDetail::create([
            'journal_entry_id'   => $je->id,
            'departemen_akun_id' => $daPusat->id,
            'kode_akun'          => '1101001',
            'credits'            => 50000,
        ]);

        foreach ($majorityRows as $row) {
            $result = $this->engine->detectAnomalies($row);
            $this->assertNotContains(
                AnomalyDetectionEngine::RULE_VOUCHER_INCONSISTENCY,
                $result['rules'],
                "Majority lines must not be flagged as outliers"
            );
        }
    }

    public function testRule2DoesNotFlagHomogeneousSingleDepartmentVouchers()
    {
        $je = JournalEntry::create(['source' => 'VOUCHER-HOMOGENEOUS-ODS', 'tanggal' => '2025-10-15']);
        $daOds = DepartemenAkun::firstOrCreate(['akun_id' => 1, 'departemen_id' => $this->deptOds->id]);

        $rows = [];
        for ($i = 0; $i < 4; $i++) {
            $rows[] = JournalEntryDetail::create([
                'journal_entry_id'   => $je->id,
                'departemen_akun_id' => $daOds->id,
                'kode_akun'          => '1101001',
                'debits'             => 25000,
            ]);
        }

        foreach ($rows as $row) {
            $result = $this->engine->detectAnomalies($row);
            $this->assertNotContains(
                AnomalyDetectionEngine::RULE_VOUCHER_INCONSISTENCY,
                $result['rules'],
                "Single department voucher lines must not be flagged as outliers"
            );
        }
    }

    public function testRule2DoesNotFlagEqualTieVouchers()
    {
        $je = JournalEntry::create(['source' => 'EQUAL-TIE-2-VS-2', 'tanggal' => '2025-10-15']);
        $daDufan = DepartemenAkun::firstOrCreate(['akun_id' => 1, 'departemen_id' => $this->deptDufan->id]);
        $daOds   = DepartemenAkun::firstOrCreate(['akun_id' => 1, 'departemen_id' => $this->deptOds->id]);

        $rows = [];
        for ($i = 0; $i < 2; $i++) {
            $rows[] = JournalEntryDetail::create([
                'journal_entry_id'   => $je->id,
                'departemen_akun_id' => $daDufan->id,
                'kode_akun'          => '1101001',
                'debits'             => 10000,
            ]);
            $rows[] = JournalEntryDetail::create([
                'journal_entry_id'   => $je->id,
                'departemen_akun_id' => $daOds->id,
                'kode_akun'          => '1101001',
                'credits'            => 10000,
            ]);
        }

        foreach ($rows as $row) {
            $result = $this->engine->detectAnomalies($row);
            $this->assertNotContains(
                AnomalyDetectionEngine::RULE_VOUCHER_INCONSISTENCY,
                $result['rules'],
                "Equal ties (2 vs 2) must not flag any line as an outlier"
            );
        }
    }

    public function testRule2HandlesThreeWayEqualTie()
    {
        $je = JournalEntry::create(['source' => 'THREE-WAY-TIE', 'tanggal' => '2025-10-15']);
        $da1 = DepartemenAkun::firstOrCreate(['akun_id' => 1, 'departemen_id' => $this->deptDufan->id]);
        $da2 = DepartemenAkun::firstOrCreate(['akun_id' => 1, 'departemen_id' => $this->deptOds->id]);
        $da3 = DepartemenAkun::firstOrCreate(['akun_id' => 1, 'departemen_id' => $this->deptJbl->id]);

        $rows = [
            JournalEntryDetail::create(['journal_entry_id' => $je->id, 'departemen_akun_id' => $da1->id, 'kode_akun' => '1101001', 'debits' => 10000]),
            JournalEntryDetail::create(['journal_entry_id' => $je->id, 'departemen_akun_id' => $da2->id, 'kode_akun' => '1101001', 'debits' => 10000]),
            JournalEntryDetail::create(['journal_entry_id' => $je->id, 'departemen_akun_id' => $da3->id, 'kode_akun' => '1101001', 'debits' => 10000]),
        ];

        foreach ($rows as $row) {
            $result = $this->engine->detectAnomalies($row);
            $this->assertNotContains(
                AnomalyDetectionEngine::RULE_VOUCHER_INCONSISTENCY,
                $result['rules'],
                "Three-way equal tie (1 vs 1 vs 1) must not flag any line as an outlier"
            );
        }
    }

    public function testRule2FlagsMultipleMinorityDepartmentsInPluralityVoucher()
    {
        $je = JournalEntry::create(['source' => 'PLURALITY-CHECK', 'tanggal' => '2025-10-15']);
        $daDufan = DepartemenAkun::firstOrCreate(['akun_id' => 1, 'departemen_id' => $this->deptDufan->id]);
        $daOds   = DepartemenAkun::firstOrCreate(['akun_id' => 1, 'departemen_id' => $this->deptOds->id]);
        $daJbl   = DepartemenAkun::firstOrCreate(['akun_id' => 1, 'departemen_id' => $this->deptJbl->id]);

        // Dominant: DUFAN has 4 lines
        for ($i = 0; $i < 4; $i++) {
            JournalEntryDetail::create(['journal_entry_id' => $je->id, 'departemen_akun_id' => $daDufan->id, 'kode_akun' => '1101001', 'debits' => 10000]);
        }

        // Minorities: ODS has 1 line, JBL has 1 line
        $outlierOds = JournalEntryDetail::create(['journal_entry_id' => $je->id, 'departemen_akun_id' => $daOds->id, 'kode_akun' => '1101001', 'credits' => 20000]);
        $outlierJbl = JournalEntryDetail::create(['journal_entry_id' => $je->id, 'departemen_akun_id' => $daJbl->id, 'kode_akun' => '1101001', 'credits' => 20000]);

        $resOds = $this->engine->detectAnomalies($outlierOds);
        $resJbl = $this->engine->detectAnomalies($outlierJbl);

        $this->assertContains(AnomalyDetectionEngine::RULE_VOUCHER_INCONSISTENCY, $resOds['rules']);
        $this->assertContains(AnomalyDetectionEngine::RULE_VOUCHER_INCONSISTENCY, $resJbl['rules']);
    }

    public function testRule2WorksWithPrecomputedVoucherContext()
    {
        $detail = $this->createDetail('CONTEXT-OPT-TEST', $this->deptPusat->id);

        // Supply precomputed dominant analysis
        $voucherContext = [
            'dominant_analysis' => [
                'dominant_dept_id' => $this->deptDufan->id,
                'max_count'        => 8,
                'total_assigned'   => 9,
                'is_homogeneous'   => false,
                'is_tied'          => false,
            ],
        ];

        $result = $this->engine->detectAnomalies($detail, $voucherContext);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_VOUCHER_INCONSISTENCY, $result['rules']);
    }

    // =========================================================================
    // 3. RULE 3: UNIT ACCOUNT MISMATCH TESTS
    // =========================================================================

    public function testRule3FlagsQrisOdsAssignedToNonOds()
    {
        // 1102002 (MANDIRI QRIS ODS) assigned to Pusat Bandung (1)
        $detail = $this->createDetail('GENERIC-251015-001', $this->deptPusat->id, '1102002');

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $result['rules']);
        $this->assertStringContainsString('ODS', $result['reasons'][0]);
    }

    public function testRule3FlagsQrisDufanAssignedToNonDufan()
    {
        // 1102005 (MANDIRI QRIS DUFAN) assigned to AWA (4)
        $detail = $this->createDetail('GENERIC-251015-001', $this->deptAwa->id, '1102005');

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $result['rules']);
        $this->assertStringContainsString('DUFAN', $result['reasons'][0]);
    }

    public function testRule3FlagsBniQrisDufanAssignedToNonDufan()
    {
        // 1102036 (BNI TAMPUNGAN QRIS DUFAN) assigned to ODS (3)
        $detail = $this->createDetail('GENERIC-251015-001', $this->deptOds->id, '1102036');

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $result['rules']);
    }

    public function testRule3FlagsQrisAwaAssignedToNonAwa()
    {
        // 1102003 (MANDIRI QRIS AWA) assigned to DUFAN (2)
        $detail = $this->createDetail('GENERIC-251015-001', $this->deptDufan->id, '1102003');

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $result['rules']);
    }

    public function testRule3FlagsQrisJblAssignedToNonJbl()
    {
        // 1102004 (MANDIRI QRIS JBL) assigned to PUSAT (1)
        $detail = $this->createDetail('GENERIC-251015-001', $this->deptPusat->id, '1102004');

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $result['rules']);
    }

    public function testRule3FlagsKasGowetAssignedToNonGowet()
    {
        // 1101007 (KAS PESONA - GOWET) assigned to DUFAN (2)
        $detail = $this->createDetail('GENERIC-251015-001', $this->deptDufan->id, '1101007');

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $result['rules']);
    }

    public function testRule3FlagsPiutangSqpAssignedToNonSqp()
    {
        // 1104011 (PIUTANG SQP) assigned to DUFAN (2)
        $detail = $this->createDetail('GENERIC-251015-001', $this->deptDufan->id, '1104011');

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $result['rules']);
        $this->assertStringContainsString('SQP', $result['reasons'][0]);
    }

    public function testRule3FlagsPiutangGowetAssignedToNonGowet()
    {
        // 1104012 (PIUTANG GO WET) assigned to PUSAT (1)
        $detail = $this->createDetail('GENERIC-251015-001', $this->deptPusat->id, '1104012');

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $result['rules']);
        $this->assertStringContainsString('GOWET', $result['reasons'][0]);
    }

    public function testRule3FlagsPiutangEmoAssignedToNonEmo()
    {
        // 1104016 (PIUTANG EMO) assigned to DUFAN (2)
        $detail = $this->createDetail('GENERIC-251015-001', $this->deptDufan->id, '1104016');

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $result['rules']);
        $this->assertStringContainsString('EMO', $result['reasons'][0]);
    }

    public function testRule3FlagsUnitRevenueAccountMismatch()
    {
        // 4160100 (PENDAPATAN SQP) assigned to Pusat Bandung (1)
        $detail = $this->createDetail('GENERIC-251015-001', $this->deptPusat->id, '4160100', 0, 500000);

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $result['rules']);
        $this->assertStringContainsString('SQP', $result['reasons'][0]);
    }

    public function testRule3DoesNotFlagUnitAccountsAssignedToMatchingDepartment()
    {
        // 1102002 (ODS QRIS) assigned to ODS (3) -> clean
        $detailOds = $this->createDetail('GENERIC-251015-001', $this->deptOds->id, '1102002');
        $resOds = $this->engine->detectAnomalies($detailOds);
        $this->assertNotContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $resOds['rules']);

        // 1104011 (PIUTANG SQP) assigned to SQP (7) -> clean
        $detailSqp = $this->createDetail('GENERIC-251015-001', $this->deptSqp->id, '1104011');
        $resSqp = $this->engine->detectAnomalies($detailSqp);
        $this->assertNotContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $resSqp['rules']);

        // 1102005 (DUFAN QRIS) assigned to DUFAN (2) -> clean
        $detailDfn = $this->createDetail('GENERIC-251015-001', $this->deptDufan->id, '1102005');
        $resDfn = $this->engine->detectAnomalies($detailDfn);
        $this->assertNotContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $resDfn['rules']);
    }

    public function testRule3AvoidsFalsePositiveForPiutangKaryawan()
    {
        // 1104001 (PIUTANG KARYAWAN) contains substring "AWA"
        // It must NOT be flagged as AWA unit account mismatch when assigned to Pusat Bandung
        $detail = $this->createDetail('GENERIC-251015-001', $this->deptPusat->id, '1104001');

        $result = $this->engine->detectAnomalies($detail);

        $this->assertNotContains(
            AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH,
            $result['rules'],
            "Substring 'AWA' inside PIUTANG KARYAWAN must NOT trigger Rule 3"
        );
    }

    public function testRule3DoesNotFlagGenericNonUnitAccounts()
    {
        // 5101001 (Beban Gaji) has no unit designation
        $detail = $this->createDetail('GENERIC-251015-001', $this->deptPusat->id, '5101001');

        $result = $this->engine->detectAnomalies($detail);

        $this->assertNotContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $result['rules']);
    }

    // =========================================================================
    // 4. CLEAN ASSIGNMENT & MULTI-RULE INTERACTIONS
    // =========================================================================

    public function testCleanAssignmentHasNoAnomaliesAndEmptyArrays()
    {
        // Clean KASSQP voucher assigned to SQP with generic cash account
        $detail = $this->createDetail('KASSQP-251015-001', $this->deptSqp->id, '1101002');

        $result = $this->engine->detectAnomalies($detail);

        $this->assertFalse($result['is_anomaly']);
        $this->assertIsArray($result['rules']);
        $this->assertEmpty($result['rules']);
        $this->assertIsArray($result['reasons']);
        $this->assertEmpty($result['reasons']);
    }

    public function testMultipleRulesCanTriggerSimultaneously()
    {
        // Voucher KASSQP (Rule 1 expects SQP)
        // Detail has account 1102002 (Rule 3 expects ODS)
        // Detail is assigned to PUSAT (1)
        $detail = $this->createDetail('KASSQP-251015-001', $this->deptPusat->id, '1102002');

        $result = $this->engine->detectAnomalies($detail);

        $this->assertTrue($result['is_anomaly']);
        $this->assertContains(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $result['rules']);
        $this->assertContains(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $result['rules']);
        $this->assertCount(2, $result['rules']);
        $this->assertCount(2, $result['reasons']);
    }

    public function testDetectAnomaliesReturnsExpectedStructure()
    {
        $detail = $this->createDetail('TEST-STRUCT-001', $this->deptPusat->id);

        $result = $this->engine->detectAnomalies($detail);

        $this->assertArrayHasKey('is_anomaly', $result);
        $this->assertArrayHasKey('rules', $result);
        $this->assertArrayHasKey('reasons', $result);
        $this->assertIsBool($result['is_anomaly']);
        $this->assertIsArray($result['rules']);
        $this->assertIsArray($result['reasons']);
    }

    // =========================================================================
    // 5. QUERY SCOPE AND ANOMALY COUNT CALCULATIONS
    // =========================================================================

    public function testApplyAnomalyScopeFiltersAnomaliesCorrectly()
    {
        // 1. Create a clean detail (KASSQP assigned to SQP)
        $clean = $this->createDetail('KASSQP-251015-CLEAN', $this->deptSqp->id, '1101001');

        // 2. Create an anomaly detail (KASSQP assigned to Pusat)
        $anomaly = $this->createDetail('KASSQP-251015-ANOMALY', $this->deptPusat->id, '1101001');

        $query = JournalEntryDetail::whereIn('id', [$clean->id, $anomaly->id]);
        $this->engine->applyAnomalyScope($query, true);

        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals($anomaly->id, $results->first()->id);
    }

    public function testApplyAnomalyScopeReturnsUnmodifiedQueryWhenFalse()
    {
        $clean   = $this->createDetail('KASSQP-251015-C1', $this->deptSqp->id, '1101001');
        $anomaly = $this->createDetail('KASSQP-251015-A1', $this->deptPusat->id, '1101001');

        $query = JournalEntryDetail::whereIn('id', [$clean->id, $anomaly->id]);
        $this->engine->applyAnomalyScope($query, false);

        $this->assertEquals(2, $query->count());
    }

    public function testCalculateAnomalyCountsReturnsInteger()
    {
        $count = $this->engine->calculateAnomalyCounts();
        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);
    }

    public function testCalculateAnomalyCountsWithDepartmentFilter()
    {
        $countPusat = $this->engine->calculateAnomalyCounts(['departemen_id' => $this->deptPusat->id]);
        $this->assertIsInt($countPusat);

        $countSqp = $this->engine->calculateAnomalyCounts(['departemen_id' => $this->deptSqp->id]);
        $this->assertIsInt($countSqp);
    }

    public function testCalculateAnomalyCountsWithKategoriFilter()
    {
        $realCount = $this->engine->calculateAnomalyCounts(['kategori' => 'real']);
        $nominalCount = $this->engine->calculateAnomalyCounts(['kategori' => 'nominal']);

        $this->assertIsInt($realCount);
        $this->assertIsInt($nominalCount);
    }

    // =========================================================================
    // 6. UI BADGE & LABEL HELPERS
    // =========================================================================

    public function testGetBadgeHtmlReturnsNormalBadgeForCleanResult()
    {
        $html = $this->engine->getBadgeHtml([
            'is_anomaly' => false,
            'rules'      => [],
            'reasons'    => [],
        ]);

        $this->assertStringContainsString('Normal', $html);
        $this->assertStringContainsString('bg-emerald-100', $html);
    }

    public function testGetBadgeHtmlReturnsFormattedBadgesForAnomalies()
    {
        $html = $this->engine->getBadgeHtml([
            'is_anomaly' => true,
            'rules'      => [
                AnomalyDetectionEngine::RULE_PREFIX_MISMATCH,
                AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH,
            ],
            'reasons'    => [
                'Voucher prefix mismatch detected',
                'Unit account allocated to wrong branch',
            ],
        ]);

        $this->assertStringContainsString('Prefix Mismatch', $html);
        $this->assertStringContainsString('Akun Unit Mismatch', $html);
        $this->assertStringContainsString('bg-red-100', $html);
        $this->assertStringContainsString('bg-purple-100', $html);
    }

    public function testGetRuleLabelAndDescription()
    {
        $label1 = $this->engine->getRuleLabel(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH);
        $label2 = $this->engine->getRuleLabel(AnomalyDetectionEngine::RULE_VOUCHER_INCONSISTENCY);
        $label3 = $this->engine->getRuleLabel(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH);

        $this->assertEquals('Prefix Mismatch', $label1);
        $this->assertEquals('Inkonsistensi Voucher', $label2);
        $this->assertEquals('Akun Unit Mismatch', $label3);

        $desc1 = $this->engine->getRuleDescription(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH);
        $this->assertNotEmpty($desc1);
    }
}

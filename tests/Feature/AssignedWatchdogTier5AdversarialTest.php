<?php

namespace Tests\Feature;

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;
use App\DepartmentAssignmentLog;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\Services\AnomalyDetectionEngine;
use App\Services\DepartmentAssignmentService;
use App\StartNewYear;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Class AssignedWatchdogTier5AdversarialTest
 *
 * Tier 5 Adversarial Coverage Hardening & White-Box Stress Test Suite for Assigned Watchdog:
 *
 * 1. Extreme Multi-Department Vouchers:
 *    - 3-way, 4-way, and 5-way ties (equal distribution stability)
 *    - 50+ lines scale stress test (majority vs minority outlier accuracy)
 *    - Single-line vouchers (homogeneous, never outlier)
 *    - All 9 departments represented (equal 9-way tie vs single dominant with 8 outliers)
 *    - SQL query scope (applyAnomalyScope/applyRule2Scope) validation
 *
 * 2. Malformed / Unusual Voucher Sources:
 *    - Lowercase prefix normalization
 *    - Extra hyphens and non-standard delimiter spacing
 *    - Unicode/whitespace padding and empty inputs
 *    - Numbers matching unit codes (avoiding false prefix detection)
 *    - Generic operational prefixes (never triggering Rule 1)
 *
 * 3. Account Name Regex Fuzzing & Word Boundary Protection:
 *    - Substrings of unit codes: BIAWA, JAWABAN, KAWAN, SEWA, KARYAWAN, EMOSIONAL
 *    - Additional potential false-match substrings: PERIODS, METHODS, DEFINISI, JABAL, PABRIK, BENDUNGAN
 *    - Genuine unit account name validation
 *    - Integration with JournalEntryDetail anomaly detection
 *
 * 4. Transaction Boundary Stress Testing:
 *    - Bulk reassign 10+ lines: mid-batch non-existent ID rollback
 *    - Bulk unassign 10+ lines: mid-batch non-existent ID rollback
 *    - Bulk reassign 10+ lines: mid-batch closed period without override rollback
 *    - Bulk unassign 10+ lines: mid-batch closed period without override rollback
 *
 * 5. Date Boundary Stress Testing:
 *    - Year transitions: 2024-12-31 23:59:59 (closed) vs 2025-01-01 00:00:00 (open)
 *    - Leap day: 2024-02-29 (closed)
 *    - Historical closed dates prior to closing year
 *    - Future open dates
 *    - Authorization check on closed period for staff vs admin override
 *
 * 6. Export Pipeline Stress Testing:
 *    - Special characters and SQL/XSS injections in search term
 *    - Extreme amounts: zero (0.00), large (999,999,999.99), and negative (-50,000.00)
 *    - Empty result set handling in both Excel and PDF
 *    - Custom limit parameter boundary stress
 */
class AssignedWatchdogTier5AdversarialTest extends TestCase
{
    /** @var User */
    protected $adminUser;

    /** @var User */
    protected $staffUser;

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

    /** @var DepartmentAssignmentService */
    protected $assignmentService;

    /** @var AnomalyDetectionEngine */
    protected $anomalyEngine;

    /** @var ChartOfAccount */
    protected $baseAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assignmentService = app(DepartmentAssignmentService::class);
        $this->anomalyEngine     = app(AnomalyDetectionEngine::class);

        // 1. Resolve Users
        $this->adminUser = User::where('email', 'admin@example.com')->first()
            ?? User::where('name', 'Admin')->first()
            ?? User::first();

        $this->staffUser = User::where('email', 'hazzabhaskara@gmail.com')->first()
            ?? User::where('name', 'Hazza')->first()
            ?? User::where('id', '!=', $this->adminUser->id)->first();

        // 2. Setup Permissions
        if (class_exists(Permission::class)) {
            $perms = [
                'assigned_watchdog.access',
                'assigned_watchdog.update',
                'assigned_watchdog.override_closed',
            ];
            foreach ($perms as $p) {
                Permission::findOrCreate($p, 'web');
            }

            if ($this->adminUser) {
                $this->adminUser->givePermissionTo($perms);
            }

            if ($this->staffUser) {
                $this->staffUser->givePermissionTo(['assigned_watchdog.access', 'assigned_watchdog.update']);
                $this->staffUser->revokePermissionTo('assigned_watchdog.override_closed');
            }

            if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
                app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            }
        }

        // 3. Resolve Departments (1 through 9)
        $this->deptPusat = Departement::find(1) ?? Departement::where('kode', '0001')->firstOrFail();
        $this->deptDufan = Departement::find(2) ?? Departement::where('kode', '0002')->firstOrFail();
        $this->deptOds   = Departement::find(3) ?? Departement::where('kode', '0003')->firstOrFail();
        $this->deptAwa   = Departement::find(4) ?? Departement::where('kode', '0004')->firstOrFail();
        $this->deptJbl   = Departement::find(5) ?? Departement::where('kode', '0005')->firstOrFail();
        $this->deptPbn   = Departement::find(6) ?? Departement::where('kode', '0006')->firstOrFail();
        $this->deptSqp   = Departement::find(7) ?? Departement::where('kode', '0007')->firstOrFail();
        $this->deptGowet = Departement::find(8) ?? Departement::where('kode', '0008')->firstOrFail();
        $this->deptEmo   = Departement::find(9) ?? Departement::where('kode', '0009')->firstOrFail();

        // 4. Resolve Base COA Account
        $this->baseAccount = ChartOfAccount::where('kode_akun', '1101001')->first()
            ?? ChartOfAccount::first();
    }

    /**
     * Helper to create a journal entry with a specific source and date.
     */
    protected function createJournalEntry(string $source, string $tanggal = '2026-06-15', string $comment = 'Tier 5 Stress Test'): JournalEntry
    {
        return JournalEntry::create([
            'source'  => $source,
            'tanggal' => $tanggal,
            'comment' => $comment,
        ]);
    }

    /**
     * Helper to create an assigned journal entry detail.
     */
    protected function createDetail(
        JournalEntry $je,
        Departement $dept,
        ?ChartOfAccount $account = null,
        float $debits = 10000.00,
        float $credits = 0.00,
        string $comment = 'Stress test detail'
    ): JournalEntryDetail {
        $account = $account ?? $this->baseAccount;
        $da = DepartemenAkun::firstOrCreate([
            'akun_id'       => $account->id,
            'departemen_id' => $dept->id,
        ]);

        return JournalEntryDetail::create([
            'journal_entry_id'   => $je->id,
            'departemen_akun_id' => $da->id,
            'kode_akun'          => $account->kode_akun,
            'debits'             => $debits,
            'credits'            => $credits,
            'comment'            => $comment,
        ]);
    }

    // =========================================================================
    // GROUP 1: EXTREME MULTI-DEPARTMENT VOUCHERS
    // =========================================================================

    /**
     * ADV-01: Multi-department voucher with 3-way tie produces NO false Rule 2 outliers.
     */
    public function test_adv_01_multi_dept_3_way_equal_tie_produces_no_rule2_outliers()
    {
        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('GEN-3WAY-TIE-001', '2025-06-15');
            $depts = [$this->deptPusat, $this->deptDufan, $this->deptOds];

            $lines = [];
            foreach ($depts as $dept) {
                for ($i = 0; $i < 2; $i++) {
                    $lines[] = $this->createDetail($je, $dept);
                }
            }
            $this->assertCount(6, $lines);

            // 1. PHP Engine test
            foreach ($lines as $line) {
                $line->load(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);
                $res = $this->anomalyEngine->detectAnomalies($line);
                $this->assertNotContains(
                    AnomalyDetectionEngine::RULE_VOUCHER_INCONSISTENCY,
                    $res['rules'],
                    "Line #{$line->id} in 3-way tie must NOT be flagged as Rule 2 outlier"
                );
            }

            // 2. SQL Query Scope test
            $query = JournalEntryDetail::where('journal_entry_id', $je->id);
            $this->anomalyEngine->applyAnomalyScope($query, true);
            $this->assertEquals(0, $query->count(), "SQL scope must return 0 anomalies for 3-way equal tie");
        } finally {
            DB::rollBack();
        }
    }

    /**
     * ADV-02: Multi-department voucher with 4-way tie produces NO false Rule 2 outliers.
     */
    public function test_adv_02_multi_dept_4_way_equal_tie_produces_no_rule2_outliers()
    {
        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('GEN-4WAY-TIE-002', '2025-06-15');
            $depts = [$this->deptPusat, $this->deptDufan, $this->deptOds, $this->deptAwa];

            $lines = [];
            foreach ($depts as $dept) {
                for ($i = 0; $i < 3; $i++) {
                    $lines[] = $this->createDetail($je, $dept);
                }
            }
            $this->assertCount(12, $lines);

            foreach ($lines as $line) {
                $line->load(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);
                $r2 = $this->anomalyEngine->evaluateRule2($line);
                $this->assertNull($r2, "Line #{$line->id} in 4-way tie must return null for Rule 2");
            }

            $query = JournalEntryDetail::where('journal_entry_id', $je->id);
            $this->anomalyEngine->applyAnomalyScope($query, true);
            $this->assertEquals(0, $query->count(), "SQL scope must return 0 anomalies for 4-way tie");
        } finally {
            DB::rollBack();
        }
    }

    /**
     * ADV-03: Multi-department voucher with 5-way tie produces NO false Rule 2 outliers.
     */
    public function test_adv_03_multi_dept_5_way_equal_tie_produces_no_rule2_outliers()
    {
        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('GEN-5WAY-TIE-003', '2025-06-15');
            $depts = [$this->deptPusat, $this->deptDufan, $this->deptOds, $this->deptAwa, $this->deptJbl];

            $lines = [];
            foreach ($depts as $dept) {
                for ($i = 0; $i < 2; $i++) {
                    $lines[] = $this->createDetail($je, $dept);
                }
            }
            $this->assertCount(10, $lines);

            foreach ($lines as $line) {
                $line->load(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);
                $r2 = $this->anomalyEngine->evaluateRule2($line);
                $this->assertNull($r2, "Line #{$line->id} in 5-way tie must return null for Rule 2");
            }

            $query = JournalEntryDetail::where('journal_entry_id', $je->id);
            $this->anomalyEngine->applyAnomalyScope($query, true);
            $this->assertEquals(0, $query->count(), "SQL scope must return 0 anomalies for 5-way tie");
        } finally {
            DB::rollBack();
        }
    }

    /**
     * ADV-04: Multi-department voucher with 50+ lines scale stress test (majority vs minority).
     */
    public function test_adv_04_multi_dept_voucher_50_plus_lines_scale_stress()
    {
        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('GEN-SCALE-50PLUS-004', '2025-06-15');

            // 45 lines in Pusat (dominant)
            $pusatLines = [];
            for ($i = 0; $i < 45; $i++) {
                $pusatLines[] = $this->createDetail($je, $this->deptPusat);
            }

            // 10 lines in Dufan (minority outlier)
            $dufanLines = [];
            for ($i = 0; $i < 10; $i++) {
                $dufanLines[] = $this->createDetail($je, $this->deptDufan);
            }

            // 5 lines in ODS (minority outlier)
            $odsLines = [];
            for ($i = 0; $i < 5; $i++) {
                $odsLines[] = $this->createDetail($je, $this->deptOds);
            }

            $this->assertCount(60, array_merge($pusatLines, $dufanLines, $odsLines));

            // Verify dominant department lines are NOT flagged
            $startMicro = microtime(true);
            foreach ($pusatLines as $line) {
                $line->load(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);
                $r2 = $this->anomalyEngine->evaluateRule2($line);
                $this->assertNull($r2, "Dominant department lines (45 of 60) must NOT be flagged as Rule 2");
            }

            // Verify all minority lines (15) ARE flagged
            foreach (array_merge($dufanLines, $odsLines) as $line) {
                $line->load(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);
                $r2 = $this->anomalyEngine->evaluateRule2($line);
                $this->assertNotNull($r2, "Minority line #{$line->id} must be flagged as Rule 2");
                $this->assertEquals(AnomalyDetectionEngine::RULE_VOUCHER_INCONSISTENCY, $r2['rule']);
                $this->assertEquals($this->deptPusat->id, $r2['dominant_dept_id']);
                $this->assertStringContainsString('45 dari 60 baris', $r2['reason']);
            }
            $duration = microtime(true) - $startMicro;
            $this->assertLessThan(5.0, $duration, "Processing 60 lines must complete in under 5 seconds");

            // Verify SQL scope returns exactly the 15 minority lines
            $query = JournalEntryDetail::where('journal_entry_id', $je->id);
            $this->anomalyEngine->applyAnomalyScope($query, true);
            $this->assertEquals(15, $query->count(), "SQL scope must return exactly 15 outliers for 60-line voucher");
        } finally {
            DB::rollBack();
        }
    }

    /**
     * ADV-05: Single-line voucher is homogeneous and never flagged as outlier.
     */
    public function test_adv_05_single_line_voucher_is_never_flagged_as_outlier()
    {
        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('GEN-SINGLE-005', '2025-06-15');
            $line = $this->createDetail($je, $this->deptDufan);

            $line->load(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);
            $r2 = $this->anomalyEngine->evaluateRule2($line);
            $this->assertNull($r2, "Single-line voucher must never be flagged as Rule 2 outlier");

            $query = JournalEntryDetail::where('journal_entry_id', $je->id);
            $this->anomalyEngine->applyAnomalyScope($query, true);
            $this->assertEquals(0, $query->count(), "Single line voucher must produce 0 anomalies in SQL scope");
        } finally {
            DB::rollBack();
        }
    }

    /**
     * ADV-06: Voucher spanning all 9 departments with 1 line each (9-way tie) produces 0 outliers.
     */
    public function test_adv_06_voucher_spanning_all_9_departments_equal_tie()
    {
        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('GEN-ALL9-TIE-006', '2025-06-15');
            $allDepts = [
                $this->deptPusat, $this->deptDufan, $this->deptOds,
                $this->deptAwa, $this->deptJbl, $this->deptPbn,
                $this->deptSqp, $this->deptGowet, $this->deptEmo,
            ];

            $lines = [];
            foreach ($allDepts as $dept) {
                $lines[] = $this->createDetail($je, $dept);
            }
            $this->assertCount(9, $lines);

            foreach ($lines as $line) {
                $line->load(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);
                $r2 = $this->anomalyEngine->evaluateRule2($line);
                $this->assertNull($r2, "All-9-departments equal tie line #{$line->id} must return null for Rule 2");
            }

            $query = JournalEntryDetail::where('journal_entry_id', $je->id);
            $this->anomalyEngine->applyAnomalyScope($query, true);
            $this->assertEquals(0, $query->count(), "SQL scope must return 0 outliers for 9-way equal tie");
        } finally {
            DB::rollBack();
        }
    }

    /**
     * ADV-07: Voucher spanning all 9 departments with clear dominant department.
     */
    public function test_adv_07_voucher_spanning_all_9_departments_with_clear_majority()
    {
        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('GEN-ALL9-DOMINANT-007', '2025-06-15');

            // Dominant: Pusat (9 lines)
            $pusatLines = [];
            for ($i = 0; $i < 9; $i++) {
                $pusatLines[] = $this->createDetail($je, $this->deptPusat);
            }

            // Other 8 departments: 1 line each (8 lines)
            $minorityDepts = [
                $this->deptDufan, $this->deptOds, $this->deptAwa,
                $this->deptJbl, $this->deptPbn, $this->deptSqp,
                $this->deptGowet, $this->deptEmo,
            ];

            $minorityLines = [];
            foreach ($minorityDepts as $dept) {
                $minorityLines[] = $this->createDetail($je, $dept);
            }

            $this->assertCount(17, array_merge($pusatLines, $minorityLines));

            // Majority lines: clean
            foreach ($pusatLines as $line) {
                $line->load(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);
                $r2 = $this->anomalyEngine->evaluateRule2($line);
                $this->assertNull($r2, "Dominant Pusat lines in 9-dept voucher must not be flagged");
            }

            // Minority lines: all 8 flagged
            foreach ($minorityLines as $line) {
                $line->load(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);
                $r2 = $this->anomalyEngine->evaluateRule2($line);
                $this->assertNotNull($r2, "Minority line in 9-dept voucher must be flagged as Rule 2");
                $this->assertEquals($this->deptPusat->id, $r2['dominant_dept_id']);
                $this->assertStringContainsString('9 dari 17 baris', $r2['reason']);
            }

            // SQL Scope
            $query = JournalEntryDetail::where('journal_entry_id', $je->id);
            $this->anomalyEngine->applyAnomalyScope($query, true);
            $this->assertEquals(8, $query->count(), "SQL scope must return exactly 8 outliers");
        } finally {
            DB::rollBack();
        }
    }

    // =========================================================================
    // GROUP 2: MALFORMED / UNUSUAL VOUCHER SOURCES
    // =========================================================================

    /**
     * ADV-08: Malformed voucher sources with lowercase prefixes normalize correctly.
     */
    public function test_adv_08_malformed_voucher_source_lowercase_prefixes()
    {
        $testCases = [
            'ch-gowet-20250101-01' => $this->deptGowet->id,
            'ch-dfn-999'          => $this->deptDufan->id,
            'kasgowet-001'        => $this->deptGowet->id,
            'kasdfn-123'          => $this->deptDufan->id,
            'bni6625-dep'         => $this->deptDufan->id,
            'adjsqp-01'           => $this->deptSqp->id,
            'ch-ods-20250615-01'  => $this->deptOds->id,
            'ch-awa-001'          => $this->deptAwa->id,
        ];

        foreach ($testCases as $source => $expectedDeptId) {
            $resolved = $this->anomalyEngine->getExpectedDepartmentForPrefix($source);
            $this->assertEquals(
                $expectedDeptId,
                $resolved,
                "Lowercase source '{$source}' must resolve to expected department ID {$expectedDeptId}"
            );
        }

        // Integration with detail evaluation: lowercase prefix assigned to wrong dept triggers Rule 1
        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('ch-gowet-20250101-01', '2025-06-15');
            $lineWrong = $this->createDetail($je, $this->deptPusat); // Wrong dept
            $lineRight = $this->createDetail($je, $this->deptGowet); // Correct dept

            $lineWrong->load(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);
            $r1Wrong = $this->anomalyEngine->evaluateRule1($lineWrong);
            $this->assertNotNull($r1Wrong, "Lowercase source assigned to wrong dept must trigger Rule 1");
            $this->assertEquals(AnomalyDetectionEngine::RULE_PREFIX_MISMATCH, $r1Wrong['rule']);

            $lineRight->load(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);
            $r1Right = $this->anomalyEngine->evaluateRule1($lineRight);
            $this->assertNull($r1Right, "Lowercase source assigned to correct dept must NOT trigger Rule 1");
        } finally {
            DB::rollBack();
        }
    }

    /**
     * ADV-09: Malformed voucher sources with extra hyphens and non-standard delimiters.
     */
    public function test_adv_09_malformed_voucher_source_prefixes_with_extra_hyphens()
    {
        // Valid prefix followed by extra hyphens
        $this->assertEquals(
            $this->deptGowet->id,
            $this->anomalyEngine->getExpectedDepartmentForPrefix('CH-GOWET--001'),
            "Prefix with trailing double hyphen must still resolve correctly"
        );
        $this->assertEquals(
            $this->deptDufan->id,
            $this->anomalyEngine->getExpectedDepartmentForPrefix('CH-DFN---999'),
            "Prefix with trailing triple hyphen must still resolve correctly"
        );

        // Corrupted prefix with extra hyphen inside prefix (CH--GOWET is not CH-GOWET)
        $this->assertNull(
            $this->anomalyEngine->getExpectedDepartmentForPrefix('CH--GOWET-01'),
            "Malformed prefix 'CH--GOWET-01' must safely return null without throwing exception"
        );
        $this->assertNull(
            $this->anomalyEngine->getExpectedDepartmentForPrefix('---CH-DFN'),
            "Prefix with leading hyphens must safely return null"
        );
    }

    /**
     * ADV-10: Voucher sources with unicode spaces, tabs, and padding handle gracefully.
     */
    public function test_adv_10_malformed_voucher_source_unicode_and_whitespace_padding()
    {
        $this->assertEquals(
            $this->deptDufan->id,
            $this->anomalyEngine->getExpectedDepartmentForPrefix("  CH-DFN-001  "),
            "Leading and trailing spaces must be trimmed"
        );
        $this->assertEquals(
            $this->deptOds->id,
            $this->anomalyEngine->getExpectedDepartmentForPrefix("\tCH-ODS-01\n"),
            "Tabs and newlines must be trimmed"
        );

        // Empty and whitespace-only inputs
        $this->assertNull($this->anomalyEngine->getExpectedDepartmentForPrefix(""));
        $this->assertNull($this->anomalyEngine->getExpectedDepartmentForPrefix("   "));
        $this->assertNull($this->anomalyEngine->getExpectedDepartmentForPrefix(null));

        // Special characters in source suffix
        $this->assertEquals(
            $this->deptGowet->id,
            $this->anomalyEngine->getExpectedDepartmentForPrefix("CH-GOWET-#@!$%^&*()"),
            "Special characters in suffix must not break prefix matching"
        );
    }

    /**
     * ADV-11: Numbers matching unit codes or COA codes are NOT mistaken for voucher prefixes.
     */
    public function test_adv_11_malformed_voucher_source_numbers_matching_unit_codes()
    {
        $numberSources = [
            '0001-VOUCHER',
            '0002',
            '0008-INVOICE',
            '1102002',
            '20250101-0007',
            '99999999',
            '1',
            '2',
        ];

        foreach ($numberSources as $src) {
            $this->assertNull(
                $this->anomalyEngine->getExpectedDepartmentForPrefix($src),
                "Number-like voucher source '{$src}' must NOT be treated as a unit prefix"
            );
        }
    }

    /**
     * ADV-12: Generic operational prefixes never trigger Rule 1 regardless of department.
     */
    public function test_adv_12_malformed_voucher_source_generic_prefixes_never_trigger_rule1()
    {
        $genericSources = [
            'GAJI-2025-01',
            'PENYUSUTAN-2025-12',
            'BCAEVI-TRX-001',
            'BNI7884-DEP-002',
            'MDR6646-PAYMENT',
            'HPP-20250101',
            'PURCHASE_INVOICE-001',
            'SALES_INVOICE-002',
            'PAYMENT-999',
            'START NEW YEAR 2025',
        ];

        foreach ($genericSources as $src) {
            $this->assertNull(
                $this->anomalyEngine->getExpectedDepartmentForPrefix($src),
                "Generic source '{$src}' must return null from prefix matcher"
            );
        }

        // Integration with detail: assigned to ANY department should never produce Rule 1
        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('GAJI-2025-01-ALL', '2025-06-15');
            $line = $this->createDetail($je, $this->deptDufan);

            $line->load(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);
            $r1 = $this->anomalyEngine->evaluateRule1($line);
            $this->assertNull($r1, "Generic GAJI voucher must not trigger Rule 1");
        } finally {
            DB::rollBack();
        }
    }

    // =========================================================================
    // GROUP 3: ACCOUNT NAME REGEX FUZZING & WORD BOUNDARY PROTECTION
    // =========================================================================

    /**
     * ADV-13: Account name regex fuzzing: substrings of AWA (BIAWA, JAWABAN, KAWAN, SEWA, KARYAWAN).
     */
    public function test_adv_13_account_name_regex_fuzzing_strict_word_boundaries_awa_substrings()
    {
        $falsePositiveCandidates = [
            'BIAWAK TAMAN SAFARI',
            'SURAT JAWABAN AUDIT EKSTERNAL',
            'DANA KAWAN SEJATI',
            'BIAYA SEWA KANTOR PUSAT',
            'PIUTANG KARYAWAN',
            'BEBAN GAJI KARYAWAN TETAP',
            'PINJAMAN KARYAWAN DIVISI',
            'KAWANAN BURUNG',
            'JAWABAN KONFIRMASI BANK',
        ];

        foreach ($falsePositiveCandidates as $accountName) {
            $dept = $this->anomalyEngine->getExpectedDepartmentForAccount('9999999', $accountName);
            $this->assertNull(
                $dept,
                "Account name '{$accountName}' containing 'AWA' substring must NOT trigger AWA department (4)"
            );
        }

        // Genuine AWA account names must correctly match Dept 4
        $genuineAwa = [
            'PENDAPATAN TIKET AWA',
            'KAS OPERASIONAL ATLANTIS',
            'AWA_REVENUE_WATERPARK',
            'BIAYA PROMOSI AWA',
            'PENDAPATAN AWA 2025',
        ];

        foreach ($genuineAwa as $accountName) {
            $dept = $this->anomalyEngine->getExpectedDepartmentForAccount('9999999', $accountName);
            $this->assertEquals(
                $this->deptAwa->id,
                $dept,
                "Genuine AWA account name '{$accountName}' must resolve to AWA department (4)"
            );
        }
    }

    /**
     * ADV-14: Account name regex fuzzing: substrings of EMO (EMOSIONAL, EMOSI).
     */
    public function test_adv_14_account_name_regex_fuzzing_strict_word_boundaries_emo_substrings()
    {
        $falsePositiveCandidates = [
            'BIAYA EMOSIONAL KARYAWAN',
            'PELATIHAN KECERDASAN EMOSIONAL',
            'PENGENDALIAN EMOSI',
            'DEMONSTRASI PRODUK', // contains EMO in middle
            'MEMORANDUM OF UNDERSTANDING', // contains EMO
        ];

        foreach ($falsePositiveCandidates as $accountName) {
            $dept = $this->anomalyEngine->getExpectedDepartmentForAccount('9999999', $accountName);
            $this->assertNull(
                $dept,
                "Account name '{$accountName}' containing 'EMO' substring must NOT trigger EMO department (9)"
            );
        }

        $genuineEmo = [
            'PENDAPATAN TIKET EMO',
            'KAS OMSET EMO',
            'EMO_REVENUE',
            'BIAYA OPERASIONAL EMO',
        ];

        foreach ($genuineEmo as $accountName) {
            $dept = $this->anomalyEngine->getExpectedDepartmentForAccount('9999999', $accountName);
            $this->assertEquals(
                $this->deptEmo->id,
                $dept,
                "Genuine EMO account name '{$accountName}' must resolve to EMO department (9)"
            );
        }
    }

    /**
     * ADV-15: Account name regex fuzzing across all other department tokens (ODS, DFN, JBL, PBN, BDG, SQP, GOWET).
     */
    public function test_adv_15_account_name_regex_fuzzing_strict_word_boundaries_all_other_depts()
    {
        $fuzzTests = [
            // ODS (3) false positives vs genuine
            ['name' => 'PERIODS OF ACCOUNTING', 'expected' => null],
            ['name' => 'METHODS AND PROCEDURES', 'expected' => null],
            ['name' => 'MODS OF PAYMENT', 'expected' => null],
            ['name' => 'KAS OPERASIONAL ODS', 'expected' => 3],

            // DFN / DUFAN (2) false positives vs genuine
            ['name' => 'DEFINISI BIAYA FISKAL', 'expected' => null],
            ['name' => 'KAS OMSET DFN', 'expected' => 2],
            ['name' => 'PENDAPATAN TIKET DUFAN', 'expected' => 2],

            // JBL (5) false positives vs genuine
            ['name' => 'JABAL TURSINA SAFARI', 'expected' => null],
            ['name' => 'PENDAPATAN JBL', 'expected' => 5],

            // PBN (6) false positives vs genuine
            ['name' => 'PABRIK KELAPA SAWIT', 'expected' => null],
            ['name' => 'PEMBANGUNAN UNIT PBN', 'expected' => 6],

            // BDG (1) false positives vs genuine
            ['name' => 'BENDUNGAN KARET', 'expected' => null],
            ['name' => 'KAS KANTOR PUSAT BANDUNG', 'expected' => 1],
            ['name' => 'OPERASIONAL BDG', 'expected' => 1],

            // SQP (7) genuine
            ['name' => 'PENDAPATAN SQP', 'expected' => 7],
            ['name' => 'PASAR SQP REVENUE', 'expected' => 7],

            // GOWET (8) genuine
            ['name' => 'PENDAPATAN GO WET WATERPARK', 'expected' => 8],
            ['name' => 'KAS GWT', 'expected' => 8],
        ];

        foreach ($fuzzTests as $case) {
            $actual = $this->anomalyEngine->getExpectedDepartmentForAccount('9999999', $case['name']);
            $this->assertEquals(
                $case['expected'],
                $actual,
                "Account name '{$case['name']}' expected " . ($case['expected'] ?? 'null') . " but got " . ($actual ?? 'null')
            );
        }
    }

    /**
     * ADV-16: Integration with JournalEntryDetail: substring account does not trigger Rule 3.
     */
    public function test_adv_16_account_name_regex_fuzzing_integration_with_journal_entry_details()
    {
        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('GEN-REGEX-INTEG-016', '2025-06-15');

            // 1. Create a custom COA with substring name 'BEBAN GAJI KARYAWAN'
            $accountSubstring = ChartOfAccount::create([
                'kode_akun' => '5109991',
                'nama_akun' => 'BEBAN GAJI KARYAWAN',
                'tipe_akun' => 'Beban',
            ]);

            // Assign to Pusat (1)
            $detailClean = $this->createDetail($je, $this->deptPusat, $accountSubstring);
            $detailClean->load(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);

            $r3Clean = $this->anomalyEngine->evaluateRule3($detailClean);
            $this->assertNull(
                $r3Clean,
                "Account 'BEBAN GAJI KARYAWAN' assigned to Pusat must NOT trigger Rule 3 false positive"
            );

            // 2. Create a genuine unit account 'KAS OMSET DUFAN' assigned to Pusat (1) -> SHOULD trigger Rule 3
            $accountGenuine = ChartOfAccount::create([
                'kode_akun' => '1109992',
                'nama_akun' => 'KAS OMSET DUFAN',
                'tipe_akun' => 'Aset',
            ]);

            $detailAnomaly = $this->createDetail($je, $this->deptPusat, $accountGenuine);
            $detailAnomaly->load(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount']);

            $r3Anomaly = $this->anomalyEngine->evaluateRule3($detailAnomaly);
            $this->assertNotNull(
                $r3Anomaly,
                "Account 'KAS OMSET DUFAN' assigned to Pusat must trigger Rule 3 (Unit Account Mismatch)"
            );
            $this->assertEquals(AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH, $r3Anomaly['rule']);
            $this->assertEquals($this->deptDufan->id, $r3Anomaly['expected_dept_id']);
        } finally {
            DB::rollBack();
        }
    }

    // =========================================================================
    // GROUP 4: TRANSACTION BOUNDARY STRESS TESTING
    // =========================================================================

    /**
     * ADV-17: Bulk reassign with 10+ lines: mid-batch failure forces complete atomic rollback.
     */
    public function test_adv_17_bulk_reassign_atomic_rollback_on_mid_batch_failure_10_plus_lines()
    {
        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('GEN-ATOMIC-REASSIGN-017', '2026-06-15');
            $lines = [];
            for ($i = 0; $i < 12; $i++) {
                $lines[] = $this->createDetail($je, $this->deptPusat);
            }
            $this->assertCount(12, $lines);

            $detailIds = array_map(function ($l) { return $l->id; }, $lines);

            // Inject non-existent ID at index 10
            $corruptedIds = $detailIds;
            $corruptedIds[10] = 999999999;

            $exceptionThrown = false;
            try {
                $this->assignmentService->reassignBulk(
                    $corruptedIds,
                    $this->deptDufan->id,
                    'Adversarial bulk reassign failure test',
                    $this->adminUser
                );
            } catch (ModelNotFoundException $e) {
                $exceptionThrown = true;
            }

            $this->assertTrue($exceptionThrown, "ModelNotFoundException must be thrown on invalid detail ID");

            // Verify COMPLETE atomic rollback: all 12 lines must still be in deptPusat
            foreach ($lines as $line) {
                $line->refresh();
                $this->assertEquals(
                    $this->deptPusat->id,
                    $line->departemenAkun->departemen_id,
                    "Line #{$line->id} must NOT have been reassigned after mid-batch failure"
                );
            }

            // Verify no audit logs were committed for any of the detail IDs
            $logCount = DepartmentAssignmentLog::whereIn('journal_entry_detail_id', $detailIds)->count();
            $this->assertEquals(0, $logCount, "Zero audit logs must be recorded when bulk reassign rolls back");
        } finally {
            DB::rollBack();
        }
    }

    /**
     * ADV-18: Bulk unassign with 10+ lines: mid-batch failure forces complete atomic rollback.
     */
    public function test_adv_18_bulk_unassign_atomic_rollback_on_mid_batch_failure_10_plus_lines()
    {
        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('GEN-ATOMIC-UNASSIGN-018', '2026-06-15');
            $lines = [];
            for ($i = 0; $i < 12; $i++) {
                $lines[] = $this->createDetail($je, $this->deptDufan);
            }
            $this->assertCount(12, $lines);

            $detailIds = array_map(function ($l) { return $l->id; }, $lines);

            // Inject non-existent ID at index 8
            $corruptedIds = $detailIds;
            $corruptedIds[8] = 999999999;

            $exceptionThrown = false;
            try {
                $this->assignmentService->unassignBulk(
                    $corruptedIds,
                    'Adversarial bulk unassign failure test',
                    $this->adminUser
                );
            } catch (ModelNotFoundException $e) {
                $exceptionThrown = true;
            }

            $this->assertTrue($exceptionThrown, "ModelNotFoundException must be thrown on invalid detail ID");

            // Verify COMPLETE atomic rollback: all 12 lines must still be attached to Dufan
            foreach ($lines as $line) {
                $line->refresh();
                $this->assertNotNull($line->departemen_akun_id, "Line #{$line->id} must still be assigned");
                $this->assertEquals($this->deptDufan->id, $line->departemenAkun->departemen_id);
            }

            $logCount = DepartmentAssignmentLog::whereIn('journal_entry_detail_id', $detailIds)->count();
            $this->assertEquals(0, $logCount, "Zero audit logs must be recorded when bulk unassign rolls back");
        } finally {
            DB::rollBack();
        }
    }

    /**
     * ADV-19: Bulk reassign: mid-batch item in closed period without permission triggers atomic rollback.
     */
    public function test_adv_19_bulk_reassign_atomic_rollback_when_mid_batch_item_in_closed_period_without_override()
    {
        DB::beginTransaction();
        try {
            $jeOpen = $this->createJournalEntry('GEN-OPEN-BATCH-019', '2026-06-15');
            $jeClosed = $this->createJournalEntry('GEN-CLOSED-BATCH-019', '2024-05-15'); // 2024 is closed

            $lines = [];
            // Lines 0-6: open
            for ($i = 0; $i < 7; $i++) {
                $lines[] = $this->createDetail($jeOpen, $this->deptPusat);
            }
            // Line 7: closed
            $lines[] = $this->createDetail($jeClosed, $this->deptPusat);
            // Lines 8-9: open
            for ($i = 0; $i < 2; $i++) {
                $lines[] = $this->createDetail($jeOpen, $this->deptPusat);
            }
            $this->assertCount(10, $lines);

            $detailIds = array_map(function ($l) { return $l->id; }, $lines);

            $exceptionThrown = false;
            try {
                // Staff user lacks 'assigned_watchdog.override_closed'
                $this->assignmentService->reassignBulk(
                    $detailIds,
                    $this->deptSqp->id,
                    'Staff attempt bulk reassign spanning closed period',
                    $this->staffUser,
                    false
                );
            } catch (AuthorizationException $e) {
                $exceptionThrown = true;
                $this->assertStringContainsString('periode akuntansi tertutup', $e->getMessage());
            }

            $this->assertTrue($exceptionThrown, "AuthorizationException must be thrown for unauthorized closed period item");

            // Verify lines 0-6 were NOT modified (transaction rollback)
            foreach ($lines as $line) {
                $line->refresh();
                $this->assertEquals(
                    $this->deptPusat->id,
                    $line->departemenAkun->departemen_id,
                    "Line #{$line->id} must NOT have changed department"
                );
            }

            $logCount = DepartmentAssignmentLog::whereIn('journal_entry_detail_id', $detailIds)->count();
            $this->assertEquals(0, $logCount, "No audit logs must be persisted after rollback");
        } finally {
            DB::rollBack();
        }
    }

    /**
     * ADV-20: Bulk unassign: mid-batch item in closed period without permission triggers atomic rollback.
     */
    public function test_adv_20_bulk_unassign_atomic_rollback_when_mid_batch_item_in_closed_period_without_override()
    {
        DB::beginTransaction();
        try {
            $jeOpen = $this->createJournalEntry('GEN-OPEN-UNASSIGN-020', '2026-06-15');
            $jeClosed = $this->createJournalEntry('GEN-CLOSED-UNASSIGN-020', '2024-05-15');

            $lines = [];
            for ($i = 0; $i < 7; $i++) {
                $lines[] = $this->createDetail($jeOpen, $this->deptDufan);
            }
            $lines[] = $this->createDetail($jeClosed, $this->deptDufan);
            for ($i = 0; $i < 2; $i++) {
                $lines[] = $this->createDetail($jeOpen, $this->deptDufan);
            }
            $this->assertCount(10, $lines);

            $detailIds = array_map(function ($l) { return $l->id; }, $lines);

            $exceptionThrown = false;
            try {
                $this->assignmentService->unassignBulk(
                    $detailIds,
                    'Staff attempt bulk unassign spanning closed period',
                    $this->staffUser,
                    false
                );
            } catch (AuthorizationException $e) {
                $exceptionThrown = true;
                $this->assertStringContainsString('periode akuntansi tertutup', $e->getMessage());
            }

            $this->assertTrue($exceptionThrown, "AuthorizationException must be thrown on closed period item");

            foreach ($lines as $line) {
                $line->refresh();
                $this->assertNotNull($line->departemen_akun_id, "Line #{$line->id} must remain assigned");
            }

            $logCount = DepartmentAssignmentLog::whereIn('journal_entry_detail_id', $detailIds)->count();
            $this->assertEquals(0, $logCount, "No audit logs must be persisted after rollback");
        } finally {
            DB::rollBack();
        }
    }

    // =========================================================================
    // GROUP 5: DATE BOUNDARY STRESS TESTING
    // =========================================================================

    /**
     * ADV-21: Year transition boundary: 2025-12-31 23:59:59 (closed) vs 2026-01-01 00:00:00 (open).
     */
    public function test_adv_21_date_boundary_year_transition_closed_vs_open()
    {
        // Check service date evaluation directly
        $this->assertTrue(
            $this->assignmentService->isPeriodClosed('2025-12-31 23:59:59'),
            "2025-12-31 23:59:59 must be recognized as CLOSED period"
        );
        $this->assertTrue(
            $this->assignmentService->isPeriodClosed('2025-12-31'),
            "2025-12-31 must be recognized as CLOSED period"
        );

        $this->assertFalse(
            $this->assignmentService->isPeriodClosed('2026-01-01 00:00:00'),
            "2026-01-01 00:00:00 must be recognized as OPEN period"
        );
        $this->assertFalse(
            $this->assignmentService->isPeriodClosed('2026-01-01'),
            "2026-01-01 must be recognized as OPEN period"
        );

        // Integration with API endpoints
        DB::beginTransaction();
        try {
            $jeClosed = $this->createJournalEntry('TRANSITION-CLOSED', '2025-12-31 23:59:59');
            $lineClosed = $this->createDetail($jeClosed, $this->deptPusat);

            $jeOpen = $this->createJournalEntry('TRANSITION-OPEN', '2026-01-01 00:00:00');
            $lineOpen = $this->createDetail($jeOpen, $this->deptPusat);

            // 1. Staff cannot reassign closed period without override
            $resClosedStaff = $this->actingAs($this->staffUser)->postJson(route('assigned_watchdog.reassign'), [
                'detail_id'     => $lineClosed->id,
                'departemen_id' => $this->deptDufan->id,
                'reason'        => 'Staff reassign year-end boundary',
            ]);
            $resClosedStaff->assertStatus(403);

            // 2. Staff CAN reassign open period without override
            $resOpenStaff = $this->actingAs($this->staffUser)->postJson(route('assigned_watchdog.reassign'), [
                'detail_id'     => $lineOpen->id,
                'departemen_id' => $this->deptDufan->id,
                'reason'        => 'Staff reassign new-year boundary',
            ]);
            $resOpenStaff->assertStatus(200);

            $lineOpen->refresh();
            $this->assertEquals($this->deptDufan->id, $lineOpen->departemenAkun->departemen_id);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * ADV-22: Leap day date boundary (2024-02-29) in closed period.
     */
    public function test_adv_22_date_boundary_leap_day_2024_02_29()
    {
        $this->assertTrue(
            $this->assignmentService->isPeriodClosed('2024-02-29'),
            "Leap day 2024-02-29 must be recognized as closed"
        );
        $this->assertTrue(
            $this->assignmentService->isPeriodClosed('2024-02-29 12:00:00'),
            "Leap day with timestamp must be recognized as closed"
        );

        DB::beginTransaction();
        try {
            $jeLeap = $this->createJournalEntry('LEAP-YEAR-022', '2024-02-29');
            $lineLeap = $this->createDetail($jeLeap, $this->deptPusat);

            // Staff unauthorized
            $resStaff = $this->actingAs($this->staffUser)->postJson(route('assigned_watchdog.reassign'), [
                'detail_id'     => $lineLeap->id,
                'departemen_id' => $this->deptDufan->id,
                'reason'        => 'Staff adjustment leap day',
            ]);
            $resStaff->assertStatus(403);

            // Admin override
            $resAdmin = $this->actingAs($this->adminUser)->postJson(route('assigned_watchdog.reassign'), [
                'detail_id'       => $lineLeap->id,
                'departemen_id'   => $this->deptDufan->id,
                'reason'          => 'Admin adjustment leap day with override',
                'override_closed' => true,
            ]);
            $resAdmin->assertStatus(200);

            $log = DepartmentAssignmentLog::where('journal_entry_detail_id', $lineLeap->id)->first();
            $this->assertNotNull($log);
            $this->assertTrue((bool) $log->is_override_closed, "Log must record is_override_closed = 1");
        } finally {
            DB::rollBack();
        }
    }

    /**
     * ADV-23: Historical closed dates prior to the closed closing year.
     */
    public function test_adv_23_date_boundary_historical_closed_dates()
    {
        $historicalDates = [
            '2020-01-01',
            '2021-06-15',
            '2022-12-31',
            '2023-05-20',
            '2023-12-31',
        ];

        foreach ($historicalDates as $date) {
            $this->assertTrue(
                $this->assignmentService->isPeriodClosed($date),
                "Historical date '{$date}' prior to closed period must be recognized as CLOSED"
            );
        }

        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('HISTORICAL-2020', '2020-05-15');
            $line = $this->createDetail($je, $this->deptPusat);

            $resStaff = $this->actingAs($this->staffUser)->postJson(route('assigned_watchdog.unassign'), [
                'detail_id' => $line->id,
                'reason'    => 'Staff detachment of 2020 transaction',
            ]);
            $resStaff->assertStatus(403);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * ADV-24: Future and active open dates remain accessible without override.
     */
    public function test_adv_24_date_boundary_future_open_dates()
    {
        $openDates = [
            '2026-01-02',
            '2026-06-30',
            '2026-12-31',
            '2027-01-01',
        ];

        foreach ($openDates as $date) {
            $this->assertFalse(
                $this->assignmentService->isPeriodClosed($date),
                "Date '{$date}' must be recognized as OPEN period"
            );
        }

        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('OPEN-FUTURE-024', '2026-07-20');
            $line = $this->createDetail($je, $this->deptPusat);

            $resStaff = $this->actingAs($this->staffUser)->postJson(route('assigned_watchdog.unassign'), [
                'detail_id' => $line->id,
                'reason'    => 'Staff detachment in open period',
            ]);
            $resStaff->assertStatus(200);

            $line->refresh();
            $this->assertNull($line->departemen_akun_id, "Line must be detached successfully in open period");
        } finally {
            DB::rollBack();
        }
    }

    // =========================================================================
    // GROUP 6: EXPORT PIPELINE STRESS TESTING
    // =========================================================================

    /**
     * ADV-25: Export pipeline handles special characters and injection payloads in search query.
     */
    public function test_adv_25_export_pipeline_special_characters_in_search_term()
    {
        $payloads = [
            "'; DROP TABLE users; --",
            "%_%",
            "<script>alert('xss')</script>",
            "VOUCHER & CO #100%",
            "\\\"; OR 1=1 --",
            "测试-UNICODE-TEST",
        ];

        foreach ($payloads as $search) {
            // 1. Excel Export
            $resExcel = $this->actingAs($this->adminUser)->get(route('assigned_watchdog.export', [
                'format' => 'excel',
                'search' => $search,
            ]));
            $resExcel->assertStatus(200);
            $this->assertStringContainsString(
                'spreadsheet',
                $resExcel->headers->get('content-type'),
                "Excel export must return spreadsheet header for search: {$search}"
            );

            // 2. PDF Export
            $resPdf = $this->actingAs($this->adminUser)->get(route('assigned_watchdog.export', [
                'format' => 'pdf',
                'search' => $search,
            ]));
            $resPdf->assertStatus(200);
            $this->assertStringContainsString(
                'pdf',
                $resPdf->headers->get('content-type'),
                "PDF export must return pdf header for search: {$search}"
            );
        }
    }

    /**
     * ADV-26: Export pipeline with extreme amounts: zero, large trillions, and negative amounts.
     */
    public function test_adv_26_export_pipeline_extreme_amounts_zero_and_negative()
    {
        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('EXP-EXTREME-AMOUNTS-026', '2025-06-15');

            // Line 1: Zero amounts
            $this->createDetail($je, $this->deptPusat, $this->baseAccount, 0.00, 0.00, 'Zero amount line');

            // Line 2: Extreme large amount
            $this->createDetail($je, $this->deptDufan, $this->baseAccount, 999999999.99, 0.00, 'Extreme large amount line');

            // Line 3: Negative credit (reversal)
            $this->createDetail($je, $this->deptOds, $this->baseAccount, 0.00, -50000.00, 'Negative credit reversal line');

            // Line 4: Negative debit
            $this->createDetail($je, $this->deptAwa, $this->baseAccount, -123456.78, 0.00, 'Negative debit line');

            // Request Excel export filtered to this voucher
            $resExcel = $this->actingAs($this->adminUser)->get(route('assigned_watchdog.export', [
                'format' => 'excel',
                'search' => 'EXP-EXTREME-AMOUNTS-026',
            ]));
            $resExcel->assertStatus(200);

            // Request PDF export filtered to this voucher
            $resPdf = $this->actingAs($this->adminUser)->get(route('assigned_watchdog.export', [
                'format' => 'pdf',
                'search' => 'EXP-EXTREME-AMOUNTS-026',
            ]));
            $resPdf->assertStatus(200);

            // Verify KPI calculation with negative values
            $controller = app(\App\Http\Controllers\AssignedWatchdogController::class);
            $req = new \Illuminate\Http\Request([
                'search' => 'EXP-EXTREME-AMOUNTS-026',
                'format' => 'json',
            ]);
            $jsonRes = $controller->index($req);
            $data = $jsonRes->getData(true);

            $this->assertEquals(4, $data['kpis']['total_assigned']);
            $this->assertGreaterThan(0, $data['kpis']['total_debit']);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * ADV-27: Export pipeline handles empty result set cleanly without view rendering crash.
     */
    public function test_adv_27_export_pipeline_empty_result_set()
    {
        $emptyParams = [
            'departemen_id' => 999999, // Impossible department
            'search'        => 'NON_EXISTENT_SEARCH_STRING_12345',
        ];

        // 1. Excel Export on 0 rows
        $resExcel = $this->actingAs($this->adminUser)->get(route('assigned_watchdog.export', array_merge($emptyParams, ['format' => 'excel'])));
        $resExcel->assertStatus(200);

        // 2. PDF Export on 0 rows
        $resPdf = $this->actingAs($this->adminUser)->get(route('assigned_watchdog.export', array_merge($emptyParams, ['format' => 'pdf'])));
        $resPdf->assertStatus(200);
    }

    /**
     * ADV-28: Export pipeline handles edge-case limit parameter values.
     */
    public function test_adv_28_export_pipeline_extreme_limits()
    {
        DB::beginTransaction();
        try {
            $je = $this->createJournalEntry('ADV28-LIMIT-TEST', '2025-06-15');
            for ($i = 0; $i < 5; $i++) {
                $this->createDetail($je, $this->deptPusat, $this->baseAccount, 1000.00, 0.00, "Limit line {$i}");
            }

            $limits = ['1', '2', '5', 'all'];

            foreach ($limits as $lim) {
                $res = $this->actingAs($this->adminUser)->get(route('assigned_watchdog.export', [
                    'format' => 'excel',
                    'search' => 'ADV28-LIMIT-TEST',
                    'limit'  => $lim,
                ]));
                $res->assertStatus(200);
            }
        } finally {
            DB::rollBack();
        }
    }
}

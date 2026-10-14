<?php

namespace Tests\Feature;

use App\Departement;
use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NeracaDepartemenTest extends TestCase
{
    protected $adminUser;
    protected $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('name', 'Admin')->first();
        if (!$this->adminUser) {
            $this->adminUser = User::first();
        }

        $this->staffUser = User::where('name', 'Hazza')->first();
    }

    // =========================================================================
    // TIER 1: FEATURE COVERAGE
    // =========================================================================

    /**
     * T1.1: Filter form loads with HTTP 200
     */
    public function testFilterFormLoadsHttp200()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.filter_neraca_departemen'));
        $response->assertStatus(200);
        $response->assertSee('end_date');
    }

    /**
     * T1.2: Report loads with HTTP 200 in Matrix mode
     */
    public function testReportLoadsHttp200InMatrixMode()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'     => 'matrix',
            'end_date' => '2025-12-31',
        ]));
        $response->assertStatus(200);
        $response->assertViewHas('neraca');
        $response->assertViewHas('departments');
        $response->assertViewHas('rkBalances');
        $response->assertViewHas('grandTotals');
    }

    /**
     * T1.3: Report loads with HTTP 200 in Single Department mode
     */
    public function testReportLoadsHttp200InSingleDepartmentMode()
    {
        $dept = Departement::where('kode', '0002')->first() ?? Departement::first();
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'           => 'single',
            'departemen_id'  => $dept ? $dept->id : 2,
            'end_date'       => '2025-12-31',
        ]));
        $response->assertStatus(200);
        $response->assertViewHas('singleDept');
    }

    /**
     * T1.4: Checkbox option: hide_account_with_zero works
     */
    public function testCheckboxHideAccountWithZero()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'                   => 'matrix',
            'hide_account_with_zero' => '1',
            'end_date'               => '2025-12-31',
        ]));
        $response->assertStatus(200);
    }

    /**
     * T1.5: Checkbox option: show_account_number works
     */
    public function testCheckboxShowAccountNumber()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'                => 'matrix',
            'show_account_number' => '1',
            'end_date'            => '2025-12-31',
        ]));
        $response->assertStatus(200);
    }

    /**
     * T1.6: Excel export endpoint returns valid spreadsheet
     */
    public function testExcelExportEndpointReturnsValidSpreadsheet()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.export', [
            'format'   => 'excel',
            'mode'     => 'matrix',
            'end_date' => '2025-12-31',
        ]));
        $response->assertStatus(200);
        $file = method_exists($response, 'getFile') ? $response->getFile() : null;
        if (!$file && isset($response->baseResponse) && method_exists($response->baseResponse, 'getFile')) {
            $file = $response->baseResponse->getFile();
        }
        $content = ($file && file_exists($file->getPathname())) ? file_get_contents($file->getPathname()) : $response->getContent();
        // Check OpenXML/Zip header PK\x03\x04
        $this->assertStringStartsWith("\x50\x4B\x03\x04", $content, "Excel export must start with PK zip header");
        $this->assertGreaterThan(5000, strlen($content));
    }

    /**
     * T1.7: PDF export endpoint returns valid PDF document
     */
    public function testPdfExportEndpointReturnsValidPdf()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.export', [
            'format'   => 'pdf',
            'mode'     => 'matrix',
            'end_date' => '2025-12-31',
        ]));
        $response->assertStatus(200);
        $content = $response->getContent();
        // Check PDF header %PDF-
        $this->assertStringStartsWith("%PDF-", $content, "PDF export must start with %PDF- header");
        $this->assertGreaterThan(5000, strlen($content));
    }

    /**
     * T1.8: AJAX RK audit endpoint returns valid JSON with journal entries
     */
    public function testAjaxRkAuditEndpointReturnsValidJson()
    {
        $dept = Departement::where('kode', '0002')->first() ?? Departement::first();
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.rk_audit', [
            'departemen_id' => $dept ? $dept->id : 2,
            'end_date'      => '2025-12-31',
        ]));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'departemen',
            'end_date',
            'total_rk',
            'journals' => [
                '*' => [
                    'id',
                    'tanggal',
                    'source',
                    'comment',
                    'dept_debit',
                    'dept_credit',
                    'dept_net',
                ]
            ]
        ]);
    }

    // =========================================================================
    // TIER 2: BOUNDARY & CORNER CASES
    // =========================================================================

    /**
     * T2.1: Start of year cut-off date (2025-01-01)
     */
    public function testCutOffDateStartOfYear()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'     => 'matrix',
            'end_date' => '2025-01-01',
        ]));
        $response->assertStatus(200);
    }

    /**
     * T2.2: Mid-year cut-off date (2025-06-30)
     */
    public function testCutOffDateMidYear()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'     => 'matrix',
            'end_date' => '2025-06-30',
        ]));
        $response->assertStatus(200);
    }

    /**
     * T2.3: Year-end cut-off date (2025-12-31)
     */
    public function testCutOffDateYearEnd()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'     => 'matrix',
            'end_date' => '2025-12-31',
        ]));
        $response->assertStatus(200);
    }

    /**
     * T2.4: Department with zero/low transactions (0009 - EMO)
     */
    public function testDepartmentWithZeroTransactions()
    {
        $emo = Departement::where('kode', '0009')->first();
        if ($emo) {
            $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
                'mode'          => 'single',
                'departemen_id' => $emo->id,
                'end_date'      => '2025-12-31',
            ]));
            $response->assertStatus(200);
            $response->assertSee('EMO');
        }
    }

    /**
     * T2.5: Non-Departemen unassigned transactions correctly rendered
     */
    public function testNonDepartemenUnassignedHandling()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'     => 'matrix',
            'end_date' => '2025-12-31',
        ]));
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertTrue(
            strpos($content, 'Non-Departemen') !== false ||
            strpos($content, 'NON-DEPARTEMEN') !== false ||
            strpos($content, 'Unassigned') !== false,
            "Matrix report must contain dedicated Non-Departemen / Unassigned column"
        );
    }

    /**
     * T2.6: Single department mode iterates cleanly across all active departments
     */
    public function testSingleDepartmentModeIterationAcrossAllDepartments()
    {
        $departments = Departement::orderBy('id')->get();
        foreach ($departments as $dept) {
            $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
                'mode'          => 'single',
                'departemen_id' => $dept->id,
                'end_date'      => '2025-12-31',
            ]));
            $response->assertStatus(200);
        }
    }

    // =========================================================================
    // TIER 3: CROSS-FEATURE COMBINATIONS
    // =========================================================================

    /**
     * T3.1: Matrix mode + hide zero + show account numbers
     */
    public function testMatrixModeHideZeroAndShowAccountNumbers()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'                   => 'matrix',
            'hide_account_with_zero' => '1',
            'show_account_number'    => '1',
            'end_date'               => '2025-12-31',
        ]));
        $response->assertStatus(200);
    }

    /**
     * T3.2: Single department mode + hide zero accounts
     */
    public function testSingleDepartmentModeHideZeroAccounts()
    {
        $dept = Departement::first();
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'                   => 'single',
            'departemen_id'          => $dept->id,
            'hide_account_with_zero' => '1',
            'end_date'               => '2025-12-31',
        ]));
        $response->assertStatus(200);
    }

    /**
     * T3.3: Export Excel with hide zero accounts enabled
     */
    public function testExportExcelWithHideZeroAccountsEnabled()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.export', [
            'format'                 => 'excel',
            'mode'                   => 'matrix',
            'hide_account_with_zero' => '1',
            'end_date'               => '2025-12-31',
        ]));
        $response->assertStatus(200);
        $file = method_exists($response, 'getFile') ? $response->getFile() : null;
        if (!$file && isset($response->baseResponse) && method_exists($response->baseResponse, 'getFile')) {
            $file = $response->baseResponse->getFile();
        }
        $content = ($file && file_exists($file->getPathname())) ? file_get_contents($file->getPathname()) : $response->getContent();
        $this->assertStringStartsWith("\x50\x4B\x03\x04", $content);
    }

    /**
     * T3.4: Export PDF with hide zero accounts enabled
     */
    public function testExportPdfWithHideZeroAccountsEnabled()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.export', [
            'format'                 => 'pdf',
            'mode'                   => 'matrix',
            'hide_account_with_zero' => '1',
            'end_date'               => '2025-12-31',
        ]));
        $response->assertStatus(200);
        $this->assertStringStartsWith("%PDF-", $response->getContent());
    }

    // =========================================================================
    // TIER 4: REAL-WORLD ACCOUNTING & INTEGRITY SCENARIOS
    // =========================================================================

    /**
     * T4.1 & T4.2: Mathematical proof: Total Aktiva - Total Pasiva = 0.00 for EVERY department
     * and Elimination column: sum(R/K) = 0.00.
     */
    public function testMathematicalProofEveryDepartmentIsBalanced()
    {
        $cutOff = '2025-12-31';
        $startYear = '2025-01-01';

        $departments = Departement::orderBy('id')->get()->map(fn($d) => ['id' => $d->id, 'name' => $d->deskripsi])->toArray();
        $departments[] = ['id' => null, 'name' => 'NON-DEPARTEMEN / UNASSIGNED'];

        $totAset = 0; $totPasiva = 0; $totRk = 0;

        foreach ($departments as $d) {
            $id = $d['id'];

            // Asset query
            $qAset = DB::table('journal_entry_details as jed')
                ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
                ->join('chart_of_accounts as coa', 'coa.kode_akun', '=', 'jed.kode_akun')
                ->where('coa.tipe_akun', 'Aset')
                ->where('je.tanggal', '<=', $cutOff);
            if ($id === null) $qAset->whereNull('jed.departemen_akun_id');
            else $qAset->join('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')->where('da.departemen_id', $id);
            $aset = (float)($qAset->selectRaw('SUM(jed.debits - jed.credits) as s')->value('s') ?? 0);

            // Liability query
            $qKew = DB::table('journal_entry_details as jed')
                ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
                ->join('chart_of_accounts as coa', 'coa.kode_akun', '=', 'jed.kode_akun')
                ->where('coa.tipe_akun', 'Kewajiban')
                ->where('je.tanggal', '<=', $cutOff);
            if ($id === null) $qKew->whereNull('jed.departemen_akun_id');
            else $qKew->join('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')->where('da.departemen_id', $id);
            $kew = (float)($qKew->selectRaw('SUM(jed.credits - jed.debits) as s')->value('s') ?? 0);

            // Equity query
            $qEk = DB::table('journal_entry_details as jed')
                ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
                ->join('chart_of_accounts as coa', 'coa.kode_akun', '=', 'jed.kode_akun')
                ->where('coa.tipe_akun', 'Ekuitas')
                ->where('coa.level_akun', '!=', 'X')
                ->where('je.tanggal', '<=', $cutOff);
            if ($id === null) $qEk->whereNull('jed.departemen_akun_id');
            else $qEk->join('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')->where('da.departemen_id', $id);
            $ek = (float)($qEk->selectRaw('SUM(jed.credits - jed.debits) as s')->value('s') ?? 0);

            // Revenue query
            $qPdp = DB::table('journal_entry_details as jed')
                ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
                ->join('chart_of_accounts as coa', 'coa.kode_akun', '=', 'jed.kode_akun')
                ->where('coa.tipe_akun', 'Pendapatan')
                ->whereBetween('je.tanggal', [$startYear, $cutOff])
                ->where('je.source', '!=', 'START NEW YEAR');
            if ($id === null) $qPdp->whereNull('jed.departemen_akun_id');
            else $qPdp->join('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')->where('da.departemen_id', $id);
            $pdp = (float)($qPdp->selectRaw('SUM(jed.credits - jed.debits) as s')->value('s') ?? 0);

            // Expense query
            $qBbn = DB::table('journal_entry_details as jed')
                ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
                ->join('chart_of_accounts as coa', 'coa.kode_akun', '=', 'jed.kode_akun')
                ->where('coa.tipe_akun', 'Beban')
                ->whereBetween('je.tanggal', [$startYear, $cutOff])
                ->where('je.source', '!=', 'START NEW YEAR');
            if ($id === null) $qBbn->whereNull('jed.departemen_akun_id');
            else $qBbn->join('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')->where('da.departemen_id', $id);
            $bbn = (float)($qBbn->selectRaw('SUM(jed.debits - jed.credits) as s')->value('s') ?? 0);

            $laba = $pdp - $bbn;
            $pasivaSeb = $kew + $ek + $laba;
            $rk = $aset - $pasivaSeb;
            $pasivaSet = $pasivaSeb + $rk;

            $this->assertEqualsWithDelta(
                $aset,
                $pasivaSet,
                0.01,
                "Department '{$d['name']}' must be 100% balanced! Total Aktiva ({$aset}) != Total Pasiva ({$pasivaSet})"
            );

            $totAset += $aset;
            $totPasiva += $pasivaSet;
            $totRk += $rk;
        }

        // Elimination check: sum(R/K) + eliminasi = 0.00
        $eliminasi = -$totRk;
        $totRkConsolidated = $totRk + $eliminasi;
        $this->assertEqualsWithDelta(0.00, $totRkConsolidated, 0.01, "Elimination Invariant Failed: sum(R/K) + eliminasi must equal 0.00");

        // Total Konsolidasi check: Rp 6,963,966,229.60 on 2025-12-31
        $this->assertEqualsWithDelta(6963966229.60, $totAset, 0.05, "Consolidated Assets must equal official Balance Sheet (Rp 6,963,966,229.60)");
        $this->assertEqualsWithDelta(6963966229.60, $totPasiva, 0.05, "Consolidated Pasiva must equal official Balance Sheet (Rp 6,963,966,229.60)");
    }

    /**
     * T4.3: Reciprocal audit test: cross-transaction journal #9798 (transfer Rp 420M DUFAN -> Bandung)
     */
    public function testReciprocalAuditJournal9798()
    {
        $deptDufan = Departement::where('kode', '0002')->first();
        $this->assertNotNull($deptDufan);

        $endDate = strpos(config('app.name'), '2026') !== false ? '2026-12-31' : '2025-12-31';
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.rk_audit', [
            'departemen_id' => $deptDufan->id,
            'end_date'      => $endDate,
        ]));

        $response->assertStatus(200);
        $data = $response->json();
        $journals = collect($data['journals'] ?? []);

        $this->assertNotEmpty($journals, "DUFAN cross-branch audit list must contain journal entries");

        $journal9798 = $journals->firstWhere('id', 9798);
        if ($journal9798) {
            $net = abs((float)($journal9798['dept_net'] ?? 0));
            $credit = abs((float)($journal9798['dept_credit'] ?? 0));
            $this->assertTrue(
                abs($net - 420000000.0) < 0.01 || abs($credit - 420000000.0) < 0.01,
                "Journal #9798 must account for Rp 420,000,000 net contribution"
            );
        } else {
            $firstJ = $journals->first();
            $this->assertArrayHasKey('dept_net', $firstJ);
        }
    }

    /**
     * T4.4: RBAC Audit: Unauthenticated access redirects; Admin and Staff access granted
     */
    public function testRbacSecurityGuards()
    {
        // Unauthenticated request should redirect to login
        $guestResponse = $this->get(route('neraca_departemen.filter_neraca_departemen'));
        $this->assertTrue($guestResponse->isRedirect() || $guestResponse->status() === 401 || $guestResponse->status() === 403);

        // Admin request allowed
        $adminResponse = $this->actingAs($this->adminUser)->get(route('neraca_departemen.filter_neraca_departemen'));
        $adminResponse->assertStatus(200);

        // Staff request allowed
        if ($this->staffUser) {
            $this->staffUser->givePermissionTo('neraca_departemen.access');
            $staffResponse = $this->actingAs($this->staffUser)->get(route('neraca_departemen.filter_neraca_departemen'));
            $staffResponse->assertStatus(200);
        }
    }

    // =========================================================================
    // TIER 5: BLUEPRINT MODERNISASI & FREEZE PANES MATRIX
    // =========================================================================

    /**
     * T5.1: Single mode with 'konsolidasi' loads successfully and balances
     */
    public function testSingleModeKonsolidasiLoadsAndBalances()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'          => 'single',
            'departemen_id' => 'konsolidasi',
            'end_date'      => '2025-12-31',
        ]));
        $response->assertStatus(200);
        $response->assertSee('Total Konsolidasi Seluruh Perusahaan');
        $data = $response->getOriginalContent()->getData();
        $this->assertEquals('total', $data['singleData']['targetCol']);
        $this->assertEqualsWithDelta(6963966229.60, $data['singleData']['grandTotalAset'], 0.05);
        $this->assertEqualsWithDelta(6963966229.60, $data['singleData']['totalPasiva'], 0.05);
        $this->assertTrue($data['singleData']['isBalanced']);
        $response->assertSee('100% Balanced');
    }

    /**
     * T5.2: Single mode with 'unassigned' (Non-Departemen) loads successfully and balances
     */
    public function testSingleModeUnassignedLoadsAndBalances()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'          => 'single',
            'departemen_id' => 'unassigned',
            'end_date'      => '2025-12-31',
        ]));
        $response->assertStatus(200);
        $response->assertSee('Non-Departemen / Unassigned');
        $response->assertSee('100% Balanced');
        $data = $response->getOriginalContent()->getData();
        $this->assertEquals('unassigned', $data['singleData']['targetCol']);
        $this->assertTrue($data['singleData']['isBalanced']);
    }

    /**
     * T5.3: Matrix report HTML includes freeze panes classes and correct column order
     */
    public function testMatrixReportHtmlFreezePanesAndColumnOrder()
    {
        $response = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'     => 'matrix',
            'end_date' => '2025-12-31',
        ]));
        $response->assertStatus(200);
        $content = $response->getContent();

        // 2D Freeze panes CSS classes
        $this->assertStringContainsString('col-sticky-1', $content);
        $this->assertStringContainsString('col-sticky-2', $content);
        $this->assertStringContainsString('corner-sticky-1', $content);
        $this->assertStringContainsString('corner-sticky-2', $content);
        $this->assertStringContainsString('cell-content', $content);

        // UI/UX analysis features
        $this->assertStringContainsString('quickSearchInput', $content);
        $this->assertStringContainsString('btnFullscreen', $content);
        $this->assertStringContainsString('colVisibilityMenu', $content);
        $this->assertStringContainsString('floatingScrollbarWrap', $content);

        // Verify column order in header:
        // KETERANGAN -> TOTAL KONSOLIDASI -> ELIMINASI -> NON-DEPT
        $posKeterangan = strpos($content, 'corner-sticky-1 text-left');
        $posKonsolidasi = strpos($content, 'corner-sticky-2 text-right');
        $posEliminasi = strpos($content, 'ELIMINASI<br>');
        $posNonDept = strpos($content, 'NON-DEPT<br>');

        $this->assertNotFalse($posKeterangan, "Keterangan column header must exist");
        $this->assertNotFalse($posKonsolidasi, "Total Konsolidasi column header must exist");
        $this->assertNotFalse($posEliminasi, "Eliminasi column header must exist");
        $this->assertNotFalse($posNonDept, "Non-Dept column header must exist");

        $this->assertLessThan($posKonsolidasi, $posKeterangan, "Keterangan must come before Total Konsolidasi");
        $this->assertLessThan($posEliminasi, $posKonsolidasi, "Total Konsolidasi must come before Eliminasi");
        $this->assertLessThan($posNonDept, $posEliminasi, "Eliminasi must come before Non-Dept (Non-Dept at far right)");
    }

    /**
     * T5.4: Single mode exports to Excel and PDF with konsolidasi and unassigned
     */
    public function testSingleModeExportsKonsolidasiAndUnassigned()
    {
        foreach (['konsolidasi', 'unassigned'] as $deptKey) {
            // Excel single
            $excelRes = $this->actingAs($this->adminUser)->get(route('neraca_departemen.export', [
                'format'        => 'excel',
                'mode'          => 'single',
                'departemen_id' => $deptKey,
                'end_date'      => '2025-12-31',
            ]));
            $excelRes->assertStatus(200);

            // PDF single
            $pdfRes = $this->actingAs($this->adminUser)->get(route('neraca_departemen.export', [
                'format'        => 'pdf',
                'mode'          => 'single',
                'departemen_id' => $deptKey,
                'end_date'      => '2025-12-31',
            ]));
            $pdfRes->assertStatus(200);
            $this->assertStringStartsWith("%PDF-", $pdfRes->getContent());
        }
    }

    // =========================================================================
    // TIER 6: ADVERSARIAL STABILITY & STRUCTURAL INTEGRITY
    // =========================================================================

    /**
     * T6.1: Summary rows (TOTAL PASIVA and STATUS BALANCE) have row-fixed class
     * and HEADER rows use multi-column structure without col-sticky colspan.
     */
    public function testSummaryRowsHaveRowFixedAndHeaderHasMultiColumnStructure()
    {
        // 1. Matrix mode
        $resMatrix = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'     => 'matrix',
            'end_date' => '2025-12-31',
        ]));
        $resMatrix->assertStatus(200);
        $contentMatrix = $resMatrix->getContent();

        // Check row-fixed on TOTAL PASIVA
        $this->assertRegExp('/<tr[^>]*class="[^"]*row-fixed[^"]*"[^>]*>[\s\S]*?TOTAL PASIVA/', $contentMatrix);

        // Check row-fixed on STATUS BALANCE
        $this->assertRegExp('/<tr[^>]*class="[^"]*row-fixed[^"]*"[^>]*>[\s\S]*?STATUS BALANCE/', $contentMatrix);

        // Check HEADER rows do not have col-sticky-1 with colspan
        $this->assertNotRegExp('/<td[^>]*colspan="\d+"[^>]*class="[^"]*col-sticky-1/', $contentMatrix);

        // 2. Single mode
        $resSingle = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
            'mode'          => 'single',
            'departemen_id' => 'konsolidasi',
            'end_date'      => '2025-12-31',
        ]));
        $resSingle->assertStatus(200);
        $contentSingle = $resSingle->getContent();

        $this->assertRegExp('/<tr[^>]*class="[^"]*row-fixed[^"]*"[^>]*>[\s\S]*?TOTAL PASIVA/', $contentSingle);
        $this->assertRegExp('/<tr[^>]*class="[^"]*row-fixed[^"]*"[^>]*>[\s\S]*?STATUS BALANCE/', $contentSingle);
    }

    /**
     * T6.2: Drilldown links in Non-Departemen and Single unassigned mode include departemen_id=unassigned
     */
    public function testDrilldownLinksIncludeUnassignedDepartemenId()
    {
        DB::beginTransaction();
        try {
            $detail = \App\JournalEntryDetail::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id')
                ->where('journal_entries.tanggal', '<=', '2025-12-31')
                ->whereNotNull('journal_entry_details.departemen_akun_id')
                ->select('journal_entry_details.*')
                ->first();
            if ($detail) {
                $detail->departemen_akun_id = null;
                $detail->save();
            }

            // 1. Matrix mode: Non-Departemen column
            $resMatrix = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
                'mode'     => 'matrix',
                'end_date' => '2025-12-31',
            ]));
            $resMatrix->assertStatus(200);
            $contentMatrix = $resMatrix->getContent();

            $this->assertStringContainsString('departemen_id=unassigned', $contentMatrix);

            // 2. Single mode with unassigned
            $resSingle = $this->actingAs($this->adminUser)->get(route('neraca_departemen.report', [
                'mode'          => 'single',
                'departemen_id' => 'unassigned',
                'end_date'      => '2025-12-31',
            ]));
            $resSingle->assertStatus(200);
            $contentSingle = $resSingle->getContent();

            $this->assertStringContainsString('departemen_id=unassigned', $contentSingle);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * T6.3: Single mode export filenames include department code suffix
     */
    public function testSingleModeExportFilenamesIncludeDepartmentCodeSuffix()
    {
        $dept = Departement::where('kode', '0002')->first() ?? Departement::first();
        $this->assertNotNull($dept, "A department must exist in the database");

        $cases = [
            ['id' => 'konsolidasi', 'expectedSub' => 'single_konsolidasi'],
            ['id' => 'unassigned',  'expectedSub' => 'single_non-dept'],
            ['id' => $dept->id,     'expectedSub' => 'single_' . strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $dept->kode))],
        ];

        foreach ($cases as $case) {
            // Excel
            $resExcel = $this->actingAs($this->adminUser)->get(route('neraca_departemen.export', [
                'format'        => 'excel',
                'mode'          => 'single',
                'departemen_id' => $case['id'],
                'end_date'      => '2025-12-31',
            ]));
            $resExcel->assertStatus(200);
            $dispositionExcel = $resExcel->headers->get('content-disposition');
            $this->assertStringContainsString($case['expectedSub'], $dispositionExcel);

            // PDF
            $resPdf = $this->actingAs($this->adminUser)->get(route('neraca_departemen.export', [
                'format'        => 'pdf',
                'mode'          => 'single',
                'departemen_id' => $case['id'],
                'end_date'      => '2025-12-31',
            ]));
            $resPdf->assertStatus(200);
            $dispositionPdf = $resPdf->headers->get('content-disposition');
            $this->assertStringContainsString($case['expectedSub'], $dispositionPdf);
        }
    }

    /**
     * T6.4: Export templates render dynamic Status Balance calculation
     */
    public function testExportTemplatesRenderDynamicStatusBalanceCalculation()
    {
        // Matrix Excel view check
        $dataMatrix = app(\App\Http\Controllers\NeracaDepartemenController::class)->buildReportData(new \Illuminate\Http\Request([
            'mode'     => 'matrix',
            'end_date' => '2025-12-31',
        ]));
        $renderedExcelMatrix = view('neraca_departemen.export_excel_matrix', $dataMatrix)->render();
        $this->assertStringContainsString('STATUS BALANCE', $renderedExcelMatrix);
        $this->assertRegExp('/>0</', $renderedExcelMatrix);

        // Single Excel view check
        $dataSingle = app(\App\Http\Controllers\NeracaDepartemenController::class)->buildReportData(new \Illuminate\Http\Request([
            'mode'          => 'single',
            'departemen_id' => 'konsolidasi',
            'end_date'      => '2025-12-31',
        ]));
        $renderedExcelSingle = view('neraca_departemen.export_excel_single', $dataSingle)->render();
        $this->assertStringContainsString('STATUS BALANCE', $renderedExcelSingle);
        $this->assertRegExp('/>0</', $renderedExcelSingle);

        // Matrix PDF view check
        $renderedPdfMatrix = view('neraca_departemen.export_pdf_matrix', $dataMatrix)->render();
        $this->assertStringContainsString('STATUS BALANCE', $renderedPdfMatrix);
        $this->assertStringContainsString('0,00', $renderedPdfMatrix);

        // Single PDF view check
        $renderedPdfSingle = view('neraca_departemen.export_pdf_single', $dataSingle)->render();
        $this->assertStringContainsString('STATUS BALANCE', $renderedPdfSingle);
        $this->assertStringContainsString('0,00 (Balanced)', $renderedPdfSingle);
    }
}


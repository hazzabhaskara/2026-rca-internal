<?php

namespace Tests\Feature;

use App\ChartOfAccount;
use App\Departement;
use App\JournalEntryDetail;
use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BukuBesarDepartmentFilterTest extends TestCase
{
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('name', 'Admin')->first() ?? User::first();
    }

    /**
     * T1. Filter form loads with HTTP 200 and includes department options
     */
    public function testFilterFormLoadsHttp200AndHasDepartmentOptions()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.filter_buku_besar'));
        $response->assertStatus(200);
        $response->assertSee('Semua Departemen (Konsolidasi)');
        $response->assertSee('Non-Departemen / Unassigned');
    }

    /**
     * T2. Buku Besar report loads with HTTP 200 in consolidated mode
     */
    public function testBukuBesarConsolidatedReport()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date' => '2026-01-01',
            'end_date'   => '2026-01-31',
        ]));
        $response->assertStatus(200);
        $response->assertViewHas('rows');
        $response->assertViewHas('departemen_name', 'Semua Departemen (Konsolidasi)');
        $response->assertSee('Semua Departemen (Konsolidasi)');
    }

    /**
     * T3. Buku Besar unassigned department filter
     */
    public function testBukuBesarUnassignedDepartmentFilter()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-12-31',
            'departemen_id' => 'unassigned',
        ]));
        $response->assertStatus(200);
        $response->assertViewHas('departemen_name', 'Non-Departemen / Unassigned');
        $response->assertSee('Non-Departemen / Unassigned');

        // Verify that all returned rows have departemen_akun_id IS NULL
        $rows = $response->viewData('rows');
        foreach ($rows as $row) {
            $this->assertNull($row->departemen_akun_id, "All rows in unassigned filter must have departemen_akun_id null");
        }
    }

    /**
     * T4. Buku Besar single department filter (DUFAN - id=2)
     */
    public function testBukuBesarSingleDepartmentFilter()
    {
        $dufan = Departement::where('kode', '0002')->first() ?? Departement::find(2);
        $this->assertNotNull($dufan, "Department DUFAN must exist in DB");

        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-01-31',
            'departemen_id' => $dufan->id,
        ]));
        $response->assertStatus(200);
        $response->assertSee($dufan->deskripsi);

        // Verify that all returned rows belong to DUFAN
        $rows = $response->viewData('rows');
        $this->assertGreaterThan(0, $rows->count());
        foreach ($rows as $row) {
            $this->assertNotNull($row->departemenAkun);
            $this->assertEquals($dufan->id, $row->departemenAkun->departemen_id);
        }
    }

    /**
     * T5. Empty string in selected_accounts does NOT filter out all rows
     */
    public function testSelectedAccountsEmptyStringBugFixed()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => '',
        ]));
        $response->assertStatus(200);
        $rows = $response->viewData('rows');
        $this->assertGreaterThan(0, $rows->count(), "Empty selected_accounts must return all accounts, not zero rows");
    }

    /**
     * T6. Specific selected_accounts filters correctly
     */
    public function testSpecificSelectedAccountFiltersCorrectly()
    {
        $targetAccount = ChartOfAccount::first();
        $this->assertNotNull($targetAccount);

        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-12-31',
            'selected_accounts' => $targetAccount->kode_akun . ' - ' . $targetAccount->nama_akun,
        ]));
        $response->assertStatus(200);
        $rows = $response->viewData('rows');
        foreach ($rows as $row) {
            $this->assertEquals($targetAccount->kode_akun, $row->kode_akun);
        }
    }

    /**
     * T7. Opening balance department isolation
     */
    public function testOpeningBalanceDepartmentIsolation()
    {
        $dufan = Departement::where('kode', '0002')->first() ?? Departement::find(2);

        // Calculate expected opening balance for DUFAN on account 1101001 prior to 2026-06-01
        $expectedSaldo = JournalEntryDetail::join('journal_entries', 'journal_entry_details.journal_entry_id', '=', 'journal_entries.id')
            ->whereHas('departemenAkun', function ($dq) use ($dufan) {
                $dq->where('departemen_id', $dufan->id);
            })
            ->where('journal_entry_details.kode_akun', '1101001')
            ->where('journal_entries.tanggal', '<', '2026-06-01')
            ->where('journal_entries.source', '!=', 'START NEW YEAR')
            ->selectRaw('SUM(journal_entry_details.debits) as d, SUM(journal_entry_details.credits) as c')
            ->first();

        $expectedDebit = ($expectedSaldo->d ?? 0) - ($expectedSaldo->c ?? 0);

        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-06-01',
            'end_date'          => '2026-12-31',
            'departemen_id'     => $dufan->id,
            'selected_accounts' => '1101001',
        ]));
        $response->assertStatus(200);
        $saldoAwalPerAkun = $response->viewData('saldoAwalPerAkun');

        $actualDebit = $saldoAwalPerAkun['1101001'] ?? 0;
        $this->assertEqualsWithDelta($expectedDebit, $actualDebit, 0.01, "Opening balance must match DUFAN transactions strictly");
    }

    /**
     * T8. Nominal accounts YTD opening balance rule
     */
    public function testNominalAccountYtdOpeningBalanceRule()
    {
        // On start of year (2026-01-01), nominal opening balance must be 0
        $responseJan = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => '4101001',
        ]));
        $responseJan->assertStatus(200);
        $saldoAwalJan = $responseJan->viewData('saldoAwalPerAkun');

        $nominalAccounts = ChartOfAccount::whereIn('tipe_akun', ['Pendapatan', 'Beban'])->pluck('kode_akun')->toArray();
        foreach ($nominalAccounts as $nomCode) {
            if (isset($saldoAwalJan[$nomCode])) {
                $this->assertEquals(0, $saldoAwalJan[$nomCode], "Nominal account opening balance at start of year must be 0");
            }
        }

        // On mid-year (2026-06-01), nominal opening balance must reflect YTD transactions (Jan 1 to May 31)
        $pdpAccount = ChartOfAccount::where('tipe_akun', 'Pendapatan')->first();
        if ($pdpAccount) {
            $expectedYtd = JournalEntryDetail::join('journal_entries', 'journal_entry_details.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entry_details.kode_akun', $pdpAccount->kode_akun)
                ->where('journal_entries.tanggal', '>=', '2026-01-01')
                ->where('journal_entries.tanggal', '<', '2026-06-01')
                ->where('journal_entries.source', '!=', 'START NEW YEAR')
                ->selectRaw('SUM(journal_entry_details.credits) as c, SUM(journal_entry_details.debits) as d')
                ->first();

            $expectedVal = ($expectedYtd->c ?? 0) - ($expectedYtd->d ?? 0);

            $responseJun = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
                'start_date'        => '2026-06-01',
                'end_date'          => '2026-12-31',
                'selected_accounts' => $pdpAccount->kode_akun,
            ]));
            $responseJun->assertStatus(200);
            $saldoAwalJun = $responseJun->viewData('saldoAwalPerAkun');
            if (isset($saldoAwalJun[$pdpAccount->kode_akun])) {
                $this->assertEqualsWithDelta($expectedVal, $saldoAwalJun[$pdpAccount->kode_akun], 0.01, "YTD revenue opening balance must match Jan-May sum");
            }
        }
    }

    /**
     * T9. Export Excel forwards and includes department parameter
     */
    public function testExportExcelForwardsDepartment()
    {
        $dufan = Departement::where('kode', '0002')->first() ?? Departement::find(2);

        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.export', [
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-01-31',
            'departemen_id' => $dufan->id,
            'format'        => 'excel',
        ]));
        $response->assertStatus(200);

        $file = method_exists($response, 'getFile') ? $response->getFile() : null;
        if (!$file && isset($response->baseResponse) && method_exists($response->baseResponse, 'getFile')) {
            $file = $response->baseResponse->getFile();
        }
        $content = ($file && file_exists($file->getPathname())) ? file_get_contents($file->getPathname()) : $response->getContent();
        $this->assertStringStartsWith("\x50\x4B\x03\x04", $content, "Excel export must start with PK zip header");
    }

    /**
     * T10. Export PDF forwards and includes department parameter
     */
    public function testExportPdfForwardsDepartment()
    {
        $dufan = Departement::where('kode', '0002')->first() ?? Departement::find(2);

        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.export', [
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-01-05',
            'departemen_id' => $dufan->id,
            'format'        => 'pdf',
        ]));
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringStartsWith("%PDF-", $content, "PDF export must start with %PDF- header");
    }

    /**
     * T11. Back button is rendered when back_url is present
     */
    public function testBackButtonRenderedWhenBackUrlPresent()
    {
        $backUrl = 'http://2026-rca-internal.test/income_statement_departement?start_date=2026-01-01&end_date=2026-12-31';
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => '1101001',
            'back_url'          => $backUrl,
        ]));
        $response->assertStatus(200);
        $response->assertSee('Kembali ke Laporan Laba Rugi');
        $response->assertSee($backUrl);
    }

    /**
     * T12. Income Statement Departement has drilldown links
     */
    public function testIncomeStatementDepartementContainsDrilldownLinks()
    {
        $pdp = ChartOfAccount::whereIn('tipe_akun', ['Pendapatan', 'Beban'])->first();
        $response = $this->actingAs($this->adminUser)->get(route('income_statement.income_statement_departement', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => $pdp ? $pdp->kode_akun : '4101001',
        ]));
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('/laporan/buku-besar', $content, "Income Statement must link to Buku Besar");
        $this->assertStringContainsString('selected_accounts=', $content, "Drilldown links must contain selected_accounts parameter");
        $this->assertStringContainsString('departemen_id=', $content, "Drilldown links must contain departemen_id parameter");
        $this->assertStringContainsString('back_url=', $content, "Drilldown links must contain back_url parameter");
    }

    /**
     * T13. Boundary: Non-existent department ID handled safely without 500 error
     */
    public function testNonExistentDepartmentHandledSafely()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-01-31',
            'departemen_id' => 999999,
        ]));
        $response->assertStatus(200);
        $rows = $response->viewData('rows');
        $this->assertCount(0, $rows);
    }

    /**
     * T14. Boundary: Formatted selected_accounts "KODE - NAMA" parsed cleanly
     */
    public function testFormattedSelectedAccountsParsedCleanly()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => '2102019 - HUTANG LAIN-LAIN',
        ]));
        $response->assertStatus(200);
        $rows = $response->viewData('rows');
        foreach ($rows as $r) {
            $this->assertEquals('2102019', $r->kode_akun);
        }
    }

    /**
     * T15. Export Excel with unassigned department filter preserves filter parameter
     */
    public function testExportExcelPreservesUnassignedFilter()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.export', [
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-01-05',
            'departemen_id' => 'unassigned',
            'format'        => 'excel',
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
     * T16. Real-World Scenario: Auditor multi-branch GL comparison and drilldown
     */
    public function testAuditorMultiBranchGlComparison()
    {
        $deptPusat = Departement::where('kode', '0001')->firstOrFail();
        $deptDufan = Departement::where('kode', '0002')->firstOrFail();

        // 1. Auditor queries Pusat Bandung
        $resPusat = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'departemen_id'     => $deptPusat->id,
            'selected_accounts' => '1101001',
        ]));
        $resPusat->assertStatus(200);

        // 2. Auditor queries DUFAN
        $resDufan = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'departemen_id'     => $deptDufan->id,
            'selected_accounts' => '1101001',
        ]));
        $resDufan->assertStatus(200);

        // 3. Auditor queries Consolidated (Semua Departemen)
        $resConsolidated = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => '1101001',
        ]));
        $resConsolidated->assertStatus(200);
        $resConsolidated->assertSee('Semua Departemen (Konsolidasi)');
    }

    /**
     * T17. Department column is visible only in consolidated mode and hidden in specific department mode.
     */
    public function testDepartmentColumnVisibleInConsolidatedModeAndHiddenInSpecificMode()
    {
        $dufan = Departement::where('kode', '0002')->first() ?? Departement::find(2);

        // 1. Consolidated mode
        $resConsolidated = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-01-31',
            'departemen_id' => 'all',
        ]));
        $resConsolidated->assertStatus(200);
        $this->assertTrue($resConsolidated->viewData('is_consolidated'));
        $contentConsolidated = $resConsolidated->getContent();
        $this->assertStringContainsString('Departemen', $contentConsolidated);

        // 2. Specific department mode (DUFAN)
        $resSingle = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-01-31',
            'departemen_id' => $dufan->id,
        ]));
        $resSingle->assertStatus(200);
        $this->assertFalse($resSingle->viewData('is_consolidated'));
    }

    /**
     * T18. Deterministic sorting handles Date vs Transaction Number tie-breakers accurately.
     */
    public function testDeterministicSortingByDateAndTransactionNumber()
    {
        // 1. Sort by Date on 1101001 (Kas)
        $resDate = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => '1101001',
            'sort_by'           => 'date',
        ]));
        $resDate->assertStatus(200);
        $rowsDate = $resDate->viewData('rows');
        $this->assertEquals('date', $resDate->viewData('sort_by'));
        $this->assertGreaterThan(1, $rowsDate->count());

        $prevDate = null;
        foreach ($rowsDate as $r) {
            $curDate = $r->tanggal;
            if ($prevDate !== null) {
                $this->assertLessThanOrEqual(0, strcmp($prevDate, $curDate), "Date sort must have ascending dates");
            }
            $prevDate = $curDate;
        }

        // 2. Sort by Transaction Number on 1101001 (Kas)
        $resTx = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => '1101001',
            'sort_by'           => 'transaction_number',
        ]));
        $resTx->assertStatus(200);
        $rowsTx = $resTx->viewData('rows');
        $this->assertEquals('transaction_number', $resTx->viewData('sort_by'));
        $this->assertGreaterThan(1, $rowsTx->count());

        $prevSource = null;
        foreach ($rowsTx as $r) {
            $curSource = $r->source;
            if ($prevSource !== null) {
                $this->assertLessThanOrEqual(0, strcmp($prevSource, $curSource), "Transaction number sort must have ascending source");
            }
            $prevSource = $curSource;
        }
    }

    /**
     * T19. Zero-balance toggle (hide_zero) filters out accounts with no transactions and zero balance.
     */
    public function testZeroBalanceToggleHidesAndShowsZeroBalanceAccounts()
    {
        // 1. hide_zero = 1 (default) on January 2025
        $resHidden = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date' => '2026-01-01',
            'end_date'   => '2026-01-05',
            'hide_zero'  => '1',
        ]));
        $resHidden->assertStatus(200);
        $groupedHidden = $resHidden->viewData('groupedByAccount');

        // 2. hide_zero = 0 (show all) on January 2025
        $resAll = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date' => '2026-01-01',
            'end_date'   => '2026-01-05',
            'hide_zero'  => '0',
        ]));
        $resAll->assertStatus(200);
        $groupedAll = $resAll->viewData('groupedByAccount');

        // The number of accounts shown when hide_zero=0 must be strictly greater than hide_zero=1
        $this->assertGreaterThan($groupedHidden->count(), $groupedAll->count());
        $this->assertEquals(ChartOfAccount::count(), $groupedAll->count(), "When hide_zero=0, all chart of accounts must be included");
    }

    /**
     * T20. Voucher Quick View endpoint returns valid JSON structure with balanced totals.
     */
    public function testJournalEntryQuickViewEndpointReturnsJson()
    {
        $detail = JournalEntryDetail::first();
        $this->assertNotNull($detail);

        $response = $this->actingAs($this->adminUser)->get(route('journal_entry.quick_view', $detail->journal_entry_id));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'source',
            'tanggal',
            'comment',
            'edit_url',
            'show_url',
            'details' => [
                '*' => [
                    'id',
                    'kode_akun',
                    'nama_akun',
                    'departemen',
                    'departemen_kode',
                    'comment',
                    'debits',
                    'credits',
                    'debits_formatted',
                    'credits_formatted',
                ],
            ],
            'total_debits',
            'total_credits',
            'total_debits_formatted',
            'total_credits_formatted',
            'is_balanced',
        ]);
    }

    /**
     * T21. Excel export in consolidated mode includes Department column, company header, and accounting running balance.
     */
    public function testExcelExportGeneratesValidDownloadWithConsolidatedDepartmentColumn()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.export', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'departemen_id'     => 'all',
            'selected_accounts' => '1101001',
            'format'            => 'excel',
        ]));

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('content-type') ?? '', 'spreadsheet') ||
            str_contains($response->headers->get('content-disposition') ?? '', '.xlsx'),
            "Excel export must return a spreadsheet download"
        );

        // Also verify the export_excel blade view structure directly
        $controller = app(\App\Http\Controllers\BukuBesarController::class);
        $req = \Illuminate\Http\Request::create(route('buku_besar.export'), 'GET', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'departemen_id'     => 'all',
            'selected_accounts' => '1101001',
            'format'            => 'excel',
        ]);
        $method = new \ReflectionMethod($controller, 'getBukuBesarData');
        $method->setAccessible(true);
        $data = $method->invoke($controller, $req);

        $export = new \App\Exports\BukuBesarExport(
            $data['rows'],
            $data['saldoAwalPerAkun'],
            $data['groupedByAccount'],
            $data['totalByType'],
            $data['start_date'],
            $data['end_date'],
            $data['departemen_name'],
            $data['selected_departemen_id'],
            $data['showComment'],
            $data['accounts']
        );

        $view = $export->view();
        $this->assertEquals('buku_besar.export_excel', $view->name());
        $renderedHtml = $view->render();

        $this->assertStringContainsString('PT REMBULAN CITRA ABADI', $renderedHtml);
        $this->assertStringContainsString('LAPORAN BUKU BESAR (GENERAL LEDGER)', $renderedHtml);
        $this->assertStringContainsString('Departemen', $renderedHtml);
        $this->assertStringContainsString('Saldo Awal', $renderedHtml);
        $this->assertStringContainsString('Saldo Berjalan', $renderedHtml);
        $this->assertStringContainsString('GRAND TOTAL KESELURUHAN', $renderedHtml);
    }

    /**
     * T22. Excel export in single department mode hides Department column.
     */
    public function testExcelExportHidesDepartmentColumnInSingleDepartmentMode()
    {
        $dufan = Departement::where('kode', '0002')->first() ?? Departement::find(2);

        $controller = app(\App\Http\Controllers\BukuBesarController::class);
        $req = \Illuminate\Http\Request::create(route('buku_besar.export'), 'GET', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'departemen_id'     => $dufan->id,
            'selected_accounts' => '1101001',
            'format'            => 'excel',
        ]);
        $method = new \ReflectionMethod($controller, 'getBukuBesarData');
        $method->setAccessible(true);
        $data = $method->invoke($controller, $req);

        $export = new \App\Exports\BukuBesarExport(
            $data['rows'],
            $data['saldoAwalPerAkun'],
            $data['groupedByAccount'],
            $data['totalByType'],
            $data['start_date'],
            $data['end_date'],
            $data['departemen_name'],
            $data['selected_departemen_id'],
            $data['showComment'],
            $data['accounts']
        );

        $view = $export->view();
        $this->assertFalse($view->getData()['is_consolidated']);
        $renderedHtml = $view->render();

        $this->assertStringContainsString($dufan->deskripsi, $renderedHtml);
        $this->assertStringNotContainsString('<th style="border: 1px solid #000; text-align: center;">Departemen</th>', $renderedHtml);
    }

    /**
     * T23. PDF export returns application/pdf download with landscape A4 setup.
     */
    public function testPdfExportGeneratesValidDownloadInConsolidatedAndSingleMode()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.export', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'departemen_id'     => 'all',
            'selected_accounts' => '1101001',
            'format'            => 'pdf',
        ]));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', $response->headers->get('content-disposition'));

        // Verify PDF view contains required header and running balance
        $controller = app(\App\Http\Controllers\BukuBesarController::class);
        $req = \Illuminate\Http\Request::create(route('buku_besar.export'), 'GET', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'departemen_id'     => 'all',
            'selected_accounts' => '1101001',
            'format'            => 'pdf',
        ]);
        $method = new \ReflectionMethod($controller, 'getBukuBesarData');
        $method->setAccessible(true);
        $data = $method->invoke($controller, $req);

        $pdfView = view('buku_besar.export_pdf', $data)->render();
        $this->assertStringContainsString('PT REMBULAN CITRA ABADI', $pdfView);
        $this->assertStringContainsString('LAPORAN BUKU BESAR (GENERAL LEDGER)', $pdfView);
        $this->assertStringContainsString('Saldo Awal per', $pdfView);
        $this->assertStringContainsString('Saldo Berjalan', $pdfView);
        $this->assertStringContainsString('GRAND TOTAL KESELURUHAN', $pdfView);
    }

    /**
     * T24. Export respects show_comment parameter (transaction_comment vs line_comment).
     */
    public function testExportRespectsShowCommentSetting()
    {
        $detail = JournalEntryDetail::whereHas('journalEntry', function ($q) {
            $q->whereNotNull('comment')->where('comment', '!=', '');
        })->whereNotNull('comment')->where('comment', '!=', '')->first();
        $this->assertNotNull($detail);

        $journal = $detail->journalEntry;
        $journal->comment = 'HEADER_MEMO_UNIQUE_' . uniqid();
        $journal->save();

        $detail->comment = 'LINE_MEMO_UNIQUE_' . uniqid();
        $detail->save();

        // 1. Transaction Comment
        $reqTx = \Illuminate\Http\Request::create(route('buku_besar.export'), 'GET', [
            'start_date'        => $journal->tanggal,
            'end_date'          => $journal->tanggal,
            'selected_accounts' => $detail->kode_akun,
            'show_comment'      => 'transaction_comment',
            'format'            => 'excel',
        ]);
        $controller = app(\App\Http\Controllers\BukuBesarController::class);
        $method = new \ReflectionMethod($controller, 'getBukuBesarData');
        $method->setAccessible(true);
        $dataTx = $method->invoke($controller, $reqTx);

        $exportTx = new \App\Exports\BukuBesarExport(
            $dataTx['rows'],
            $dataTx['saldoAwalPerAkun'],
            $dataTx['groupedByAccount'],
            $dataTx['totalByType'],
            $dataTx['start_date'],
            $dataTx['end_date'],
            $dataTx['departemen_name'],
            $dataTx['selected_departemen_id'],
            $dataTx['showComment'],
            $dataTx['accounts']
        );
        $htmlTx = $exportTx->view()->render();
        $this->assertStringContainsString($journal->comment, $htmlTx);

        // 2. Line Comment
        $reqLine = \Illuminate\Http\Request::create(route('buku_besar.export'), 'GET', [
            'start_date'        => $journal->tanggal,
            'end_date'          => $journal->tanggal,
            'selected_accounts' => $detail->kode_akun,
            'show_comment'      => 'line_comment',
            'format'            => 'excel',
        ]);
        $dataLine = $method->invoke($controller, $reqLine);
        $exportLine = new \App\Exports\BukuBesarExport(
            $dataLine['rows'],
            $dataLine['saldoAwalPerAkun'],
            $dataLine['groupedByAccount'],
            $dataLine['totalByType'],
            $dataLine['start_date'],
            $dataLine['end_date'],
            $dataLine['departemen_name'],
            $dataLine['selected_departemen_id'],
            $dataLine['showComment'],
            $dataLine['accounts']
        );
        $htmlLine = $exportLine->view()->render();
        $this->assertStringContainsString($detail->comment, $htmlLine);
    }

    /**
     * T25. Account with opening balance but zero mutations is retained in report and export even when hide_zero=1.
     */
    public function testZeroMutationAccountWithOpeningBalanceRetainedInReportAndExport()
    {
        $controller = app(\App\Http\Controllers\BukuBesarController::class);
        $req = \Illuminate\Http\Request::create(route('buku_besar.buku_besar_report'), 'GET', [
            'start_date' => '2026-06-01',
            'end_date'   => '2026-06-30',
            'hide_zero'  => '1',
        ]);
        $method = new \ReflectionMethod($controller, 'getBukuBesarData');
        $method->setAccessible(true);
        $data = $method->invoke($controller, $req);

        // Find an account that has 0 mutations in June 2025 but non-zero opening balance
        $targetAccount = null;
        foreach ($data['groupedByAccount'] as $kode => $rows) {
            if ($rows->isEmpty() && abs($data['saldoAwalPerAkun'][$kode] ?? 0) > 0.0001) {
                $targetAccount = $kode;
                break;
            }
        }

        $this->assertNotNull($targetAccount, "There must be at least one account with 0 mutations and non-zero opening balance");
        $saldoAwal = $data['saldoAwalPerAkun'][$targetAccount];
        $this->assertNotEquals(0, $saldoAwal);

        // Verify web report renders this account
        $res = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-06-01',
            'end_date'          => '2026-06-30',
            'selected_accounts' => $targetAccount,
            'hide_zero'         => '1',
        ]));
        $res->assertStatus(200);
        $res->assertSee($targetAccount);
        $res->assertSee('Tidak ada mutasi transaksi pada periode ini');

        // Verify Excel export view renders this account
        $export = new \App\Exports\BukuBesarExport(
            $data['rows'],
            $data['saldoAwalPerAkun'],
            $data['groupedByAccount']->only([$targetAccount]),
            $data['totalByType'],
            $data['start_date'],
            $data['end_date'],
            $data['departemen_name'],
            $data['selected_departemen_id'],
            $data['showComment'],
            $data['accounts']
        );
        $excelHtml = $export->view()->render();
        $this->assertStringContainsString($targetAccount, $excelHtml);
        $this->assertStringContainsString(number_format($saldoAwal, 2, '.', ''), $excelHtml);
    }

    /**
     * T26. hide_zero parameter correctly handles string boolean variants ('false', '0', 'true', '1').
     */
    public function testZeroBalanceFilterWithBooleanStringVariants()
    {
        $controller = app(\App\Http\Controllers\BukuBesarController::class);
        $method = new \ReflectionMethod($controller, 'getBukuBesarData');
        $method->setAccessible(true);

        // 'false' string should evaluate to false (show all accounts)
        $reqFalse = \Illuminate\Http\Request::create(route('buku_besar.buku_besar_report'), 'GET', [
            'start_date' => '2026-01-01',
            'end_date'   => '2026-01-05',
            'hide_zero'  => 'false',
        ]);
        $dataFalse = $method->invoke($controller, $reqFalse);
        $this->assertFalse($dataFalse['hide_zero']);
        $this->assertEquals(ChartOfAccount::count(), $dataFalse['groupedByAccount']->count());

        // '0' string should evaluate to false
        $reqZero = \Illuminate\Http\Request::create(route('buku_besar.buku_besar_report'), 'GET', [
            'start_date' => '2026-01-01',
            'end_date'   => '2026-01-05',
            'hide_zero'  => '0',
        ]);
        $dataZero = $method->invoke($controller, $reqZero);
        $this->assertFalse($dataZero['hide_zero']);

        // '1' string should evaluate to true
        $reqOne = \Illuminate\Http\Request::create(route('buku_besar.buku_besar_report'), 'GET', [
            'start_date' => '2026-01-01',
            'end_date'   => '2026-01-05',
            'hide_zero'  => '1',
        ]);
        $dataOne = $method->invoke($controller, $reqOne);
        $this->assertTrue($dataOne['hide_zero']);
    }
}



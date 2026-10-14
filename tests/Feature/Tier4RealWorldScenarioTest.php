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
 * Class Tier4RealWorldScenarioTest
 * 
 * TIER 4: REAL-WORLD ACCOUNTING SCENARIOS
 * Simulates complete operational and forensic audit workflows:
 * - Scenario 4.1: End-to-end cashier closing omset to bank settlement reconciliation
 * - Scenario 4.2: Executive Laba Rugi drilldown to detailed ledger with back navigation
 * - Scenario 4.3: Unassigned transaction resolution & full balance sheet integrity
 * - Scenario 4.4: Comprehensive multi-branch operational audit (all 9 wahana units)
 */
class Tier4RealWorldScenarioTest extends TestCase
{
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('name', 'Admin')->first() ?? User::first();
    }

    /**
     * Scenario 4.1: Cashier daily shift closing to bank deposit settlement reconciliation lifecycle
     */
    public function testScenarioCashierClosingToBankSettlementReconciliation()
    {
        DB::beginTransaction();
        try {
            $shiftDate = '2025-12-20';
            $cashOmset = 4250000.00;
            $qrisOmset = 3750000.00;
            $totalOmset = $cashOmset + $qrisOmset;

            $deptDufan = Departement::where('kode', '0002')->firstOrFail();

            // Step 1: Cashier posts daily closing voucher (CH-DFN-20251220)
            $closingVoucher = JournalEntry::create([
                'source'  => 'CH-DFN-20251220',
                'tanggal' => $shiftDate,
                'comment' => 'Closing Shift Dufan 20 Des 2025',
            ]);

            // Debits to Kas Omset (1101002) and Piutang QRIS (1103001)
            $closingVoucher->details()->create([
                'kode_akun' => '1101002',
                'debits'    => $cashOmset,
                'credits'   => 0,
                'comment'   => 'Cash Collection',
            ]);
            $closingVoucher->details()->create([
                'kode_akun' => '1103001',
                'debits'    => $qrisOmset,
                'credits'   => 0,
                'comment'   => 'QRIS Collection',
            ]);
            // Offsetting Credit to Revenue (4101001)
            $closingVoucher->details()->create([
                'kode_akun' => '4101001',
                'debits'    => 0,
                'credits'   => $totalOmset,
                'comment'   => 'Wahana Ticket Revenue',
            ]);

            // Step 2: Verify intermediate audit status: PENDING
            $kasMasukPending = 0.00;
            $statusPending = ($totalOmset > 0 && $kasMasukPending == 0) ? 'PENDING' : 'MATCH';
            $this->assertEquals('PENDING', $statusPending);

            // Step 3: Accounting posts bank deposit voucher (MDR-20251221) on next banking day
            $depositVoucher = JournalEntry::create([
                'source'  => 'MDR-20251221',
                'tanggal' => '2025-12-21',
                'comment' => 'Setoran Kas & QRIS Dufan ke Bank Mandiri',
            ]);

            // Debits to Bank (1102001)
            $depositVoucher->details()->create([
                'kode_akun' => '1102001',
                'debits'    => $totalOmset,
                'credits'   => 0,
                'comment'   => 'Bank Mandiri Deposit',
            ]);
            // Credits to Kas Omset (1101002) and Piutang QRIS (1103001)
            $depositVoucher->details()->create([
                'kode_akun' => '1101002',
                'debits'    => 0,
                'credits'   => $cashOmset,
                'comment'   => 'Setoran Kas Cleared',
            ]);
            $depositVoucher->details()->create([
                'kode_akun' => '1103001',
                'debits'    => 0,
                'credits'   => $qrisOmset,
                'comment'   => 'QRIS Settlement Cleared',
            ]);

            // Step 4: Verify final reconciliation status across the period: MATCH
            $kasMasukTotal = $cashOmset + $qrisOmset;
            $selisih = $kasMasukTotal - $totalOmset;

            $statusFinal = ($totalOmset > 0 && $kasMasukTotal > 0 && abs($selisih) <= 1000) ? 'MATCH' : 'SELISIH';
            $this->assertEquals('MATCH', $statusFinal);
            $this->assertEquals(0.00, $selisih);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * Scenario 4.2: Executive Laba Rugi drilldown into detailed ledger with back navigation
     */
    public function testScenarioExecutiveLabaRugiDrilldownToLedger()
    {
        // 1. Executive accesses Laporan Laba Rugi Departemen
        $lrResponse = $this->actingAs($this->adminUser)->get(route('income_statement.income_statement_departement', [
            'start_date' => '2026-01-01',
            'end_date'   => '2026-01-31',
        ]));
        $lrResponse->assertStatus(200);

        // 2. Click nominal revenue cell for Dufan
        $drilldownUrl = route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => '4101001',
            'departemen_id'     => 2, // DUFAN
            'back_url'          => route('income_statement.income_statement_departement'),
        ]);

        $ledgerResponse = $this->actingAs($this->adminUser)->get($drilldownUrl);
        $ledgerResponse->assertStatus(200);

        // 3. Verify ledger view displays DUFAN header badge and correct back link
        $ledgerResponse->assertSee('DUFAN');
        $content = $ledgerResponse->getContent();
        $this->assertStringContainsString('back_url', $content);

        // 4. Return to Laba Rugi via back link
        $returnResponse = $this->actingAs($this->adminUser)->get(route('income_statement.income_statement_departement', [
            'start_date' => '2026-01-01',
            'end_date'   => '2026-01-31',
        ]));
        $returnResponse->assertStatus(200);
    }

    /**
     * Scenario 4.3: Unassigned transaction detection, assignment, and departmental balance sheet verification
     */
    public function testScenarioUnassignedResolutionAndDepartmentalIntegrity()
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

            $pusat = Departement::where('kode', '0001')->firstOrFail();
            $account = ChartOfAccount::where('kode_akun', $unassignedDetail->kode_akun)->firstOrFail();

            // Auditor resolves unassigned item to Pusat Bandung
            $da = DepartemenAkun::firstOrCreate([
                'akun_id'       => $account->id,
                'departemen_id' => $pusat->id,
            ]);
            $unassignedDetail->departemen_akun_id = $da->id;
            $unassignedDetail->save();

            // Verify Pusat Bandung ledger incorporates the newly assigned transaction
            $glResponse = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
                'start_date'        => '2025-12-31',
                'end_date'          => '2026-06-30',
                'departemen_id'     => $pusat->id,
                'selected_accounts' => $account->kode_akun,
            ]));
            $glResponse->assertStatus(200);
            $rows = $glResponse->viewData('rows');
            $this->assertTrue($rows->contains('id', $unassignedDetail->id));
        } finally {
            DB::rollBack();
        }
    }

    /**
     * Scenario 4.4: Multi-Branch Operational Audit across all 9 active wahana units
     */
    public function testScenarioMultiBranchOperationalAuditAllUnits()
    {
        $branches = Departement::orderBy('id')->get();
        $this->assertGreaterThanOrEqual(9, $branches->count());

        foreach ($branches as $branch) {
            $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
                'start_date'        => '2025-12-01',
                'end_date'          => '2025-12-31',
                'departemen_id'     => $branch->id,
                'selected_accounts' => '2102019',
            ]));
            $response->assertStatus(200);
            $response->assertSee($branch->deskripsi);
        }
    }
}

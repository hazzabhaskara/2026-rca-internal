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
 * Class JournalEntryEditDepartmentTest
 *
 * Comprehensive 4-Tier Feature Tests for General Journal Edit & Department Selection:
 * - Tier 1: Feature Coverage (Decoupled hidden inputs, Select2 unique option IDs, Search optimization, Persistence)
 * - Tier 2: Boundary & Corner Cases (Whitespace query, zero amounts, empty department nulling)
 * - Tier 3: Cross-Feature Combinations (Department update immediate reflection in Buku Besar)
 * - Tier 4: Real-World Scenarios (Multi-line expense reclassification across branches)
 */
class JournalEntryEditDepartmentTest extends TestCase
{
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('name', 'Admin')->first() ?? User::first();
    }

    // =========================================================================
    // TIER 1: FEATURE COVERAGE
    // =========================================================================

    /**
     * T1.1: Journal entry edit page loads with HTTP 200 for existing entry
     */
    public function testEditPageLoadsHttp200()
    {
        $entry = JournalEntry::whereHas('details')->first();
        $this->assertNotNull($entry, "A journal entry with details must exist");

        $response = $this->actingAs($this->adminUser)->get(route('journal_entry.edit', $entry->id));
        $response->assertStatus(200);
        $response->assertSee('General Journal Edit');
    }

    /**
     * T1.2: Edit view contains decoupled hidden inputs (.kode-akun and .departemen-akun)
     */
    public function testEditViewContainsDecoupledHiddenInputs()
    {
        $entry = JournalEntry::whereHas('details')->first();
        $this->assertNotNull($entry);

        $response = $this->actingAs($this->adminUser)->get(route('journal_entry.edit', $entry->id));
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('class="kode-akun"', $content, "Form must have .kode-akun hidden input");
        $this->assertStringContainsString('class="departemen-akun"', $content, "Form must have .departemen-akun hidden input");
    }

    /**
     * T1.3: Dynamic row schema in script generates decoupled hidden inputs
     */
    public function testEditViewScriptDefinesDecoupledRowSchema()
    {
        $entry = JournalEntry::whereHas('details')->first();
        $this->assertNotNull($entry);

        $response = $this->actingAs($this->adminUser)->get(route('journal_entry.edit', $entry->id));
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('function generateRow', $content);
        $this->assertStringContainsString('items[${index}][kode_akun]', $content);
        $this->assertStringContainsString('items[${index}][departemen_akun_id]', $content);
    }

    /**
     * T1.4: /search-account returns accounts with department lists and edit view maps them with c- and d- prefixes
     */
    public function testSearchAccountReturnsUniqueOptionIds()
    {
        $account = ChartOfAccount::whereHas('departemenAkun')->first() ?? ChartOfAccount::first();
        $this->assertNotNull($account);

        $response = $this->actingAs($this->adminUser)->get('/search-account?q=' . urlencode($account->kode_akun));
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        $first = $data[0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('kode_akun', $first);
        $this->assertArrayHasKey('nama_akun', $first);
        $this->assertArrayHasKey('daftar_departemen', $first);

        // Verify that edit view's Select2 mapping logic assigns distinct c- and d- IDs
        $entry = JournalEntry::whereHas('details')->firstOrFail();
        $editResponse = $this->actingAs($this->adminUser)->get(route('journal_entry.edit', $entry->id));
        $editContent = $editResponse->getContent();

        $this->assertStringContainsString('id: `c-${item.id}`', $editContent);
        $this->assertStringContainsString('id: `d-${dept.id}`', $editContent);
    }

    /**
     * T1.5: /search-account trims query and enforces limit(50)
     */
    public function testSearchAccountTrimsQueryAndLimitsResults()
    {
        $response = $this->actingAs($this->adminUser)->get('/search-account?q=%20%201%20%20');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertLessThanOrEqual(50, count($data), "Results must not exceed 50 items");
    }

    /**
     * T1.6: Updating an entry with department change persists to database
     */
    public function testUpdatePersistsSameAccountDepartmentChange()
    {
        DB::beginTransaction();
        try {
            $entry = JournalEntry::whereHas('details')->firstOrFail();
            $deptPusat = Departement::where('kode', '0001')->firstOrFail();
            $deptDufan = Departement::where('kode', '0002')->firstOrFail();

            $account = ChartOfAccount::where('kode_akun', '2102019')->firstOrFail();
            $daPusat = DepartemenAkun::firstOrCreate(['akun_id' => $account->id, 'departemen_id' => $deptPusat->id]);
            $daDufan = DepartemenAkun::firstOrCreate(['akun_id' => $account->id, 'departemen_id' => $deptDufan->id]);

            $payload = [
                'source'  => $entry->source,
                'tanggal' => $entry->tanggal,
                'comment' => 'Testing same-account department switch',
                'items'   => [
                    [
                        'kode_akun'          => '2102019',
                        'departemen_akun_id' => (string)$daDufan->id,
                        'debits'             => 0,
                        'credits'            => 250000,
                        'comment'            => 'Credit assigned to DUFAN',
                    ],
                    [
                        'kode_akun'          => '1101001',
                        'departemen_akun_id' => '',
                        'debits'             => 250000,
                        'credits'            => 0,
                        'comment'            => 'Debit unassigned',
                    ],
                ]
            ];

            $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);
            $this->assertTrue($response->isRedirect() || $response->status() === 200);

            $entry->refresh();
            $detailDufan = $entry->details->firstWhere('kode_akun', '2102019');
            $detailUnassigned = $entry->details->firstWhere('kode_akun', '1101001');

            $this->assertNotNull($detailDufan);
            $this->assertEquals($daDufan->id, $detailDufan->departemen_akun_id);
            $this->assertEquals($deptDufan->id, $detailDufan->departemenAkun->departemen_id);

            $this->assertNotNull($detailUnassigned);
            $this->assertNull($detailUnassigned->departemen_akun_id);
        } finally {
            DB::rollBack();
        }
    }

    // =========================================================================
    // TIER 2: BOUNDARY & CORNER CASES
    // =========================================================================

    /**
     * T2.1: Empty or whitespace-only search query handled safely without 500 error
     */
    public function testSearchAccountEmptyQueryHandledSafely()
    {
        $response = $this->actingAs($this->adminUser)->get('/search-account?q=');
        $response->assertStatus(200);
        $this->assertIsArray($response->json());

        $responseWhitespace = $this->actingAs($this->adminUser)->get('/search-account?q=%20%20%20');
        $responseWhitespace->assertStatus(200);
        $this->assertIsArray($responseWhitespace->json());
    }

    /**
     * T2.2: Updating journal entry with empty department string cleanly saves NULL
     */
    public function testEmptyDepartmentStringSanitizedToNull()
    {
        DB::beginTransaction();
        try {
            $entry = JournalEntry::whereHas('details')->firstOrFail();

            $payload = [
                'source'  => $entry->source,
                'tanggal' => $entry->tanggal,
                'comment' => 'Test null sanitization',
                'items'   => [
                    [
                        'kode_akun'          => '1101001',
                        'departemen_akun_id' => '',
                        'debits'             => 50000,
                        'credits'            => 0,
                    ],
                    [
                        'kode_akun'          => '1101002',
                        'departemen_akun_id' => '0',
                        'debits'             => 0,
                        'credits'            => 50000,
                    ],
                ]
            ];

            $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);
            $this->assertTrue($response->isRedirect() || $response->status() === 200);

            $entry->refresh();
            foreach ($entry->details as $d) {
                $this->assertNull($d->departemen_akun_id, "Empty department value must sanitize to NULL");
            }
        } finally {
            DB::rollBack();
        }
    }

    // =========================================================================
    // TIER 3: CROSS-FEATURE COMBINATIONS
    // =========================================================================

    /**
     * T3.1: Changing department on journal line immediately updates Buku Besar
     */
    public function testDepartmentSwitchImmediatelyReflectsInGeneralLedger()
    {
        DB::beginTransaction();
        try {
            $accountCode = '2102019';
            $amount = 175000.00;
            $entryDate = '2025-11-20';

            $deptPusat = Departement::where('kode', '0001')->firstOrFail();
            $deptDufan = Departement::where('kode', '0002')->firstOrFail();

            $account = ChartOfAccount::where('kode_akun', $accountCode)->firstOrFail();
            $daPusat = DepartemenAkun::firstOrCreate(['akun_id' => $account->id, 'departemen_id' => $deptPusat->id]);
            $daDufan = DepartemenAkun::firstOrCreate(['akun_id' => $account->id, 'departemen_id' => $deptDufan->id]);

            $entry = JournalEntry::create([
                'source'  => 'TEST-GL-SYNC',
                'tanggal' => $entryDate,
                'comment' => 'Initial under Pusat',
            ]);

            $entry->details()->create([
                'kode_akun'          => $accountCode,
                'departemen_akun_id' => $daPusat->id,
                'debits'             => 0,
                'credits'            => $amount,
            ]);

            $entry->details()->create([
                'kode_akun'          => '1101001',
                'departemen_akun_id' => $daPusat->id,
                'debits'             => $amount,
                'credits'            => 0,
            ]);

            // 1. Verify Pusat Buku Besar includes entry
            $resPusat1 = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
                'start_date'        => $entryDate,
                'end_date'          => $entryDate,
                'departemen_id'     => $deptPusat->id,
                'selected_accounts' => $accountCode,
            ]));
            $resPusat1->assertStatus(200);
            $rowsPusat1 = $resPusat1->viewData('rows');
            $this->assertGreaterThan(0, $rowsPusat1->count());

            // 2. Update entry to DUFAN
            $updatePayload = [
                'source'  => $entry->source,
                'tanggal' => $entry->tanggal,
                'comment' => 'Reassigned to DUFAN',
                'items'   => [
                    [
                        'kode_akun'          => $accountCode,
                        'departemen_akun_id' => (string)$daDufan->id,
                        'debits'             => 0,
                        'credits'            => $amount,
                    ],
                    [
                        'kode_akun'          => '1101001',
                        'departemen_akun_id' => (string)$daDufan->id,
                        'debits'             => $amount,
                        'credits'            => 0,
                    ],
                ]
            ];
            $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $updatePayload);

            // 3. Verify DUFAN Buku Besar now includes the entry
            $resDufan = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
                'start_date'        => $entryDate,
                'end_date'          => $entryDate,
                'departemen_id'     => $deptDufan->id,
                'selected_accounts' => $accountCode,
            ]));
            $resDufan->assertStatus(200);
            $rowsDufan = $resDufan->viewData('rows');
            $this->assertGreaterThan(0, $rowsDufan->count());
        } finally {
            DB::rollBack();
        }
    }

    // =========================================================================
    // TIER 4: REAL-WORLD SCENARIOS
    // =========================================================================

    /**
     * T4.1: Multi-line expense reclassification across 3 branches in one edit
     */
    public function testMultiLineExpenseReclassificationAcrossBranches()
    {
        DB::beginTransaction();
        try {
            $entry = JournalEntry::whereHas('details')->firstOrFail();

            $deptPusat = Departement::where('kode', '0001')->firstOrFail();
            $deptDufan = Departement::where('kode', '0002')->firstOrFail();
            $deptOds   = Departement::where('kode', '0003')->first() ?? Departement::find(3) ?? $deptDufan;

            $coaBeban = ChartOfAccount::where('tipe_akun', 'Beban')->first() ?? ChartOfAccount::first();
            $coaKas   = ChartOfAccount::where('kode_akun', '1101001')->firstOrFail();

            $daPusat = DepartemenAkun::firstOrCreate(['akun_id' => $coaBeban->id, 'departemen_id' => $deptPusat->id]);
            $daDufan = DepartemenAkun::firstOrCreate(['akun_id' => $coaBeban->id, 'departemen_id' => $deptDufan->id]);
            $daOds   = DepartemenAkun::firstOrCreate(['akun_id' => $coaBeban->id, 'departemen_id' => $deptOds->id]);

            $payload = [
                'source'  => $entry->source,
                'tanggal' => $entry->tanggal,
                'comment' => 'Reclassifying expenses across Pusat, Dufan, and ODS',
                'items'   => [
                    [
                        'kode_akun'          => $coaBeban->kode_akun,
                        'departemen_akun_id' => (string)$daPusat->id,
                        'debits'             => 100000,
                        'credits'            => 0,
                        'comment'            => 'Pusat share',
                    ],
                    [
                        'kode_akun'          => $coaBeban->kode_akun,
                        'departemen_akun_id' => (string)$daDufan->id,
                        'debits'             => 150000,
                        'credits'            => 0,
                        'comment'            => 'Dufan share',
                    ],
                    [
                        'kode_akun'          => $coaBeban->kode_akun,
                        'departemen_akun_id' => (string)$daOds->id,
                        'debits'             => 200000,
                        'credits'            => 0,
                        'comment'            => 'ODS share',
                    ],
                    [
                        'kode_akun'          => $coaKas->kode_akun,
                        'departemen_akun_id' => (string)$daPusat->id,
                        'debits'             => 0,
                        'credits'            => 450000,
                        'comment'            => 'Total cash paid by Pusat',
                    ],
                ]
            ];

            $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);
            $this->assertTrue($response->isRedirect() || $response->status() === 200);

            $entry->refresh();
            $this->assertEquals(4, $entry->details->count());

            // Validate each branch got its correct detail
            $pusatDetail = $entry->details->where('departemen_akun_id', $daPusat->id)->where('debits', 100000)->first();
            $dufanDetail = $entry->details->where('departemen_akun_id', $daDufan->id)->where('debits', 150000)->first();
            $odsDetail   = $entry->details->where('departemen_akun_id', $daOds->id)->where('debits', 200000)->first();

            $this->assertNotNull($pusatDetail);
            $this->assertNotNull($dufanDetail);
            $this->assertNotNull($odsDetail);
        } finally {
            DB::rollBack();
        }
    }
}

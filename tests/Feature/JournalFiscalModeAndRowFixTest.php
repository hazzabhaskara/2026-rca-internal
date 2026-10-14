<?php

namespace Tests\Feature;

use App\ChartOfAccount;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\Setting;
use App\User;
use Tests\TestCase;

class JournalFiscalModeAndRowFixTest extends TestCase
{
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('name', 'Admin')->first() ?? User::first();
    }

    /**
     * Test 1: Fiscal Settings page loads with 200 and shows options
     */
    public function testFiscalSettingPageLoadsAndDisplaysOptions()
    {
        $response = $this->actingAs($this->adminUser)->get(route('fiscal_setting.index'));
        $response->assertStatus(200);
        $response->assertSee('Fiscal Settings (Opsi Fisrec)');
        $response->assertSee('Ditiadakan Sama Sekali');
        $response->assertSee('Sembunyikan Saja (Hide Only)');
        $response->assertSee('Diadakan / Aktif Penuh');
    }

    /**
     * Test 2: Fiscal Settings update successfully updates the database setting
     */
    public function testFiscalSettingUpdatesCorrectly()
    {
        $response = $this->actingAs($this->adminUser)->post(route('fiscal_setting.update'), [
            'fiscal_journal_mode' => 'hide',
        ]);
        $response->assertRedirect(route('fiscal_setting.index'));
        $this->assertEquals('hide', Setting::get('fiscal_journal_mode'));

        // Revert to disabled (default)
        $this->actingAs($this->adminUser)->post(route('fiscal_setting.update'), [
            'fiscal_journal_mode' => 'disabled',
        ]);
        $this->assertEquals('disabled', Setting::get('fiscal_journal_mode'));
    }

    /**
     * Test 3: When fiscal_journal_mode is disabled, Edit page does NOT display Fiscorr headers or columns
     */
    public function testEditPageWithFiscalDisabledOmitsFiscalColumns()
    {
        Setting::set('fiscal_journal_mode', 'disabled');

        $entry = JournalEntry::whereHas('details')->first();
        $this->assertNotNull($entry);

        $response = $this->actingAs($this->adminUser)->get(route('journal_entry.edit', $entry->id));
        $response->assertStatus(200);
        $content = $response->getContent();

        // In the table header, Fiscorr should not be shown
        $this->assertStringNotContainsString('<th class="border px-4 py-3 text-center w-[7%]">Fiscorr</th>', $content);
        $this->assertStringNotContainsString('<th class="border px-4 py-3 text-center w-[15%]">Penyesuaian Fiskal</th>', $content);

        // In generateRow, specpose and fiscal columns must NOT be present
        $this->assertStringNotContainsString('name="items[${index}][project_id]"', $content);
        $this->assertStringNotContainsString('<td class="border px-2 py-1 penyesuaian-col text-center">-</td>', $content);

        // Auto-Balance button should be present in actions
        $this->assertStringContainsString('auto-balance', $content);
        $this->assertStringContainsString('balance-badge', $content);
    }

    /**
     * Test 4: When fiscal_journal_mode is enabled, Edit page DOES display Fiscorr headers
     */
    public function testEditPageWithFiscalEnabledDisplaysFiscalColumns()
    {
        Setting::set('fiscal_journal_mode', 'enabled');

        $entry = JournalEntry::whereHas('details')->first();
        $this->assertNotNull($entry);

        $response = $this->actingAs($this->adminUser)->get(route('journal_entry.edit', $entry->id));
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Fiscorr', $content);
        $this->assertStringContainsString('Penyesuaian Fiskal', $content);

        // Restore default
        Setting::set('fiscal_journal_mode', 'disabled');
    }

    /**
     * Test 5: Saving journal when fiscal mode is disabled resets fiscal data to 0/null
     */
    public function testJournalUpdateWithDisabledFiscalForcesFiscalNull()
    {
        Setting::set('fiscal_journal_mode', 'disabled');

        $entry = JournalEntry::whereHas('details')->first();
        $coa1 = ChartOfAccount::first();
        $coa2 = ChartOfAccount::skip(1)->first();

        $payload = [
            'source' => $entry->source,
            'tanggal' => $entry->tanggal,
            'comment' => 'Test update disabled fiscal',
            'items' => [
                [
                    'kode_akun' => $coa1->kode_akun,
                    'debits' => '100.000,00',
                    'credits' => '0,00',
                    'comment' => 'Baris 1',
                    'pajak' => 1,
                    'penyesuaian_fiskal' => 'pph_final',
                    'kode_fiscal' => 'FISC-01',
                ],
                [
                    'kode_akun' => $coa2->kode_akun,
                    'debits' => '0,00',
                    'credits' => '100.000,00',
                    'comment' => 'Baris 2',
                    'pajak' => 0,
                ]
            ]
        ];

        $response = $this->actingAs($this->adminUser)->put(route('journal_entry.update', $entry->id), $payload);
        $response->assertRedirect();

        $updatedDetails = JournalEntryDetail::where('journal_entry_id', $entry->id)->get();
        foreach ($updatedDetails as $d) {
            $this->assertEquals(0, $d->pajak, "Pajak must be 0 when fiscal is disabled");
            $this->assertNull($d->penyesuaian_fiskal, "Penyesuaian fiskal must be null when fiscal is disabled");
            $this->assertNull($d->kode_fiscal, "Kode fiscal must be null when fiscal is disabled");
        }
    }
}

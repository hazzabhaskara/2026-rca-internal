<?php

namespace Tests\Feature;

use App\ChartOfAccount;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BukuBesarDeleteTest extends TestCase
{
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('name', 'Admin')->first() ?? User::first();
    }

    /**
     * Test 1: Buku Besar view renders all delete UI elements for authorized admin:
     * - Checkbox column in the table header (.check-all-account)
     * - Row checkboxes (.gl-row-checkbox)
     * - Row delete button (.delete-voucher-btn)
     * - Quick Preview modal delete button (#qvDeleteBtn)
     * - Floating bulk action toolbar (#bulkDeleteToolbar)
     */
    public function testBukuBesarRendersDeleteControlsForAuthorizedUser()
    {
        $detail = JournalEntryDetail::whereHas('journalEntry')->first();
        $this->assertNotNull($detail, "A journal entry detail must exist in DB for testing.");
        $entry = $detail->journalEntry;

        $bukuBesarUrl = route('buku_besar.buku_besar_report', [
            'start_date' => $entry->tanggal,
            'end_date' => $entry->tanggal,
            'departemen_id' => 'all',
            'selected_accounts' => $detail->kode_akun,
        ]);

        $response = $this->actingAs($this->adminUser)->get($bukuBesarUrl);
        $response->assertStatus(200);

        $content = $response->getContent();

        $this->assertStringContainsString('check-all-account', $content, "Table header should render select-all checkbox");
        $this->assertStringContainsString('gl-row-checkbox', $content, "Table rows should render row checkboxes");
        $this->assertStringContainsString('delete-voucher-btn', $content, "Table rows should render delete button next to voucher");
        $this->assertStringContainsString('qvDeleteBtn', $content, "Quick Preview modal footer should render Hapus Voucher button");
        $this->assertStringContainsString('bulkDeleteToolbar', $content, "Page should render sticky floating bulk delete toolbar");
        $this->assertStringContainsString('executeBulkDelete', $content, "Page should include executeBulkDelete JS function");
    }

    /**
     * Test 2: Single delete via AJAX deletes entry & details, recalculates, and returns JSON success.
     */
    public function testSingleDeleteViaAjaxSuccessWithTransactionRollback()
    {
        DB::beginTransaction();
        try {
            $account = ChartOfAccount::first();
            $this->assertNotNull($account);

            $entry = JournalEntry::create([
                'source' => 'TEST-DELETE-001',
                'tanggal' => '2026-05-15',
                'comment' => 'Test single delete journal entry',
            ]);

            $detail1 = JournalEntryDetail::create([
                'journal_entry_id' => $entry->id,
                'kode_akun' => $account->kode_akun,
                'debits' => 500000,
                'credits' => 0,
                'comment' => 'Test debit',
            ]);

            $detail2 = JournalEntryDetail::create([
                'journal_entry_id' => $entry->id,
                'kode_akun' => $account->kode_akun,
                'debits' => 0,
                'credits' => 500000,
                'comment' => 'Test credit',
            ]);

            $this->assertDatabaseHas('journal_entries', ['id' => $entry->id]);
            $this->assertDatabaseHas('journal_entry_details', ['id' => $detail1->id]);

            $response = $this->actingAs($this->adminUser)->json('DELETE', route('journal_entry.destroy', $entry->id));

            $response->assertStatus(200);
            $response->assertJson([
                'success' => true,
            ]);

            $this->assertDatabaseMissing('journal_entries', ['id' => $entry->id]);
            $this->assertDatabaseMissing('journal_entry_details', ['id' => $detail1->id]);
            $this->assertDatabaseMissing('journal_entry_details', ['id' => $detail2->id]);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * Test 3: Bulk delete via AJAX deletes multiple entries & details atomically.
     */
    public function testBulkDeleteViaAjaxSuccessWithTransactionRollback()
    {
        DB::beginTransaction();
        try {
            $account = ChartOfAccount::first();
            $this->assertNotNull($account);

            $entry1 = JournalEntry::create([
                'source' => 'TEST-BULK-001',
                'tanggal' => '2026-05-16',
                'comment' => 'Test bulk 1',
            ]);
            $d1 = JournalEntryDetail::create([
                'journal_entry_id' => $entry1->id,
                'kode_akun' => $account->kode_akun,
                'debits' => 100000,
                'credits' => 0,
                'comment' => 'D1',
            ]);

            $entry2 = JournalEntry::create([
                'source' => 'TEST-BULK-002',
                'tanggal' => '2026-05-16',
                'comment' => 'Test bulk 2',
            ]);
            $d2 = JournalEntryDetail::create([
                'journal_entry_id' => $entry2->id,
                'kode_akun' => $account->kode_akun,
                'debits' => 0,
                'credits' => 100000,
                'comment' => 'D2',
            ]);

            $response = $this->actingAs($this->adminUser)->json('DELETE', route('journal_entry.bulk_delete'), [
                'ids' => [$entry1->id, $entry2->id],
            ]);

            $response->assertStatus(200);
            $response->assertJson([
                'success' => true,
                'deleted_count' => 2,
            ]);

            $this->assertDatabaseMissing('journal_entries', ['id' => $entry1->id]);
            $this->assertDatabaseMissing('journal_entries', ['id' => $entry2->id]);
            $this->assertDatabaseMissing('journal_entry_details', ['id' => $d1->id]);
            $this->assertDatabaseMissing('journal_entry_details', ['id' => $d2->id]);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * Test 4: Bulk delete returns 422 if no IDs are provided.
     */
    public function testBulkDeleteValidationFailsWhenEmpty()
    {
        $response = $this->actingAs($this->adminUser)->json('DELETE', route('journal_entry.bulk_delete'), [
            'ids' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }
}

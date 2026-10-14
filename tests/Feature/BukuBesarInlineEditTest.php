<?php

namespace Tests\Feature;

use App\ChartOfAccount;
use App\DepartemenAkun;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\User;
use Tests\TestCase;

class BukuBesarInlineEditTest extends TestCase
{
    protected $adminUser;
    protected $testJournalIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('name', 'Admin')->first() ?? User::first();
    }

    protected function tearDown(): void
    {
        // Bersihkan seluruh data dummy test agar database tetap bersih
        if (!empty($this->testJournalIds)) {
            JournalEntryDetail::whereIn('journal_entry_id', $this->testJournalIds)->delete();
            JournalEntry::whereIn('id', $this->testJournalIds)->delete();
        }
        parent::tearDown();
    }

    /**
     * Test tampilan Buku Besar memiliki elemen Resizable Table dan Modal Inline Edit
     */
    public function testReportContainsResizableTableAndInlineEditElements()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => '1101001',
            'show_lawan'        => 1,
        ]));

        $response->assertStatus(200);
        $response->assertSee('gl-resizable-table', false);
        $response->assertSee('col-resize-handle', false);
        $response->assertSee('id="glContraModal"', false);
        $response->assertSee('id="glDeptModal"', false);
        $response->assertSee('id="glConfirmModal"', false);
        $response->assertSee('glOpenContraModal', false);
        $response->assertSee('glOpenDeptModal', false);
    }

    /**
     * Test Update Lawan Transaksi (Single Contra) sukses via PATCH API
     */
    public function testUpdateContraSingleContraSuccessfully()
    {
        // 1. Buat dummy voucher transaksi sederhana (1 debit, 1 credit)
        $entry = JournalEntry::create([
            'source'  => 'TEST-INLINE-' . uniqid(),
            'tanggal' => '2026-01-15',
            'comment' => 'Test Dummy Voucher Single Contra',
        ]);
        $this->testJournalIds[] = $entry->id;

        $debitLine = JournalEntryDetail::create([
            'journal_entry_id' => $entry->id,
            'kode_akun'        => '1101001',
            'debits'           => 100000,
            'credits'          => 0,
            'comment'          => 'Line Debit',
        ]);

        $creditLine = JournalEntryDetail::create([
            'journal_entry_id' => $entry->id,
            'kode_akun'        => '1101002',
            'debits'           => 0,
            'credits'          => 100000,
            'comment'          => 'Line Credit Lawan',
        ]);

        // 2. Request PATCH untuk ganti lawan dari debitLine menjadi 1101003
        $response = $this->actingAs($this->adminUser)->patchJson(
            route('buku_besar.update_contra', $debitLine->id),
            ['new_kode_akun' => '1101003']
        );

        $response->assertStatus(200);
        $response->assertJson([
            'success'   => true,
            'kode_akun' => '1101003',
        ]);

        // 3. Pastikan baris lawan (creditLine) kode_akunnya berubah menjadi 1101003
        $creditLine->refresh();
        $this->assertEquals('1101003', $creditLine->kode_akun);
    }

    /**
     * Test Update Lawan Transaksi pada Multi-Contra ditolak demi keamanan
     */
    public function testUpdateContraMultiContraRejected()
    {
        // 1. Buat dummy voucher multi-contra (1 debit, 2 credit)
        $entry = JournalEntry::create([
            'source'  => 'TEST-MULTI-' . uniqid(),
            'tanggal' => '2026-01-16',
            'comment' => 'Test Dummy Voucher Multi Contra',
        ]);
        $this->testJournalIds[] = $entry->id;

        $debitLine = JournalEntryDetail::create([
            'journal_entry_id' => $entry->id,
            'kode_akun'        => '1101001',
            'debits'           => 200000,
            'credits'          => 0,
            'comment'          => 'Debit Main Line',
        ]);

        JournalEntryDetail::create([
            'journal_entry_id' => $entry->id,
            'kode_akun'        => '1101002',
            'debits'           => 0,
            'credits'          => 100000,
            'comment'          => 'Credit Contra 1',
        ]);

        JournalEntryDetail::create([
            'journal_entry_id' => $entry->id,
            'kode_akun'        => '1101003',
            'debits'           => 0,
            'credits'          => 100000,
            'comment'          => 'Credit Contra 2',
        ]);

        // 2. Request PATCH harus menghasilkan error 422 karena multi-contra
        $response = $this->actingAs($this->adminUser)->patchJson(
            route('buku_besar.update_contra', $debitLine->id),
            ['new_kode_akun' => '1101004']
        );

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /**
     * Test Update Departemen Akun sukses dan dapat di-unset menjadi non-dept
     */
    public function testUpdateDepartemenSuccessfullyAndUnset()
    {
        $deptAkun = DepartemenAkun::first();
        $this->assertNotNull($deptAkun, 'Harus ada data DepartemenAkun untuk testing');

        // 1. Buat dummy detail
        $entry = JournalEntry::create([
            'source'  => 'TEST-DEPT-' . uniqid(),
            'tanggal' => '2026-01-17',
            'comment' => 'Test Dummy Voucher Dept',
        ]);
        $this->testJournalIds[] = $entry->id;

        $detail = JournalEntryDetail::create([
            'journal_entry_id'   => $entry->id,
            'departemen_akun_id' => null,
            'kode_akun'          => '1101001',
            'debits'             => 50000,
            'credits'            => 0,
            'comment'            => 'Test Dept Line',
        ]);

        // 2. Set departemen ke ID valid
        $responseSet = $this->actingAs($this->adminUser)->patchJson(
            route('buku_besar.update_departemen', $detail->id),
            ['departemen_akun_id' => $deptAkun->id]
        );

        $responseSet->assertStatus(200);
        $responseSet->assertJson([
            'success' => true,
        ]);
        $detail->refresh();
        $this->assertEquals($deptAkun->id, $detail->departemen_akun_id);

        // 3. Unset departemen (jadikan Non-Dept / null)
        $responseUnset = $this->actingAs($this->adminUser)->patchJson(
            route('buku_besar.update_departemen', $detail->id),
            ['departemen_akun_id' => null]
        );

        $responseUnset->assertStatus(200);
        $responseUnset->assertJson([
            'success' => true,
        ]);
        $detail->refresh();
        $this->assertNull($detail->departemen_akun_id);
    }

    /**
     * Test data-search-text pada baris transaksi memuat Lawan Transaksi, Departemen, serta nominal Debit & Kredit
     */
    public function testReportSearchIndexContainsLawanTransaksiDeptDebitAndCredit()
    {
        $deptAkun = DepartemenAkun::first();

        // 1. Buat dummy voucher dengan debit & credit spesifik
        $entry = JournalEntry::create([
            'source'  => 'TEST-SEARCH-' . uniqid(),
            'tanggal' => '2026-01-20',
            'comment' => 'Uji Coba Search Keyword Unik',
        ]);
        $this->testJournalIds[] = $entry->id;

        $debitLine = JournalEntryDetail::create([
            'journal_entry_id'   => $entry->id,
            'departemen_akun_id' => $deptAkun ? $deptAkun->id : null,
            'kode_akun'          => '1101001',
            'debits'             => 35000000,
            'credits'            => 0,
            'comment'            => 'Baris debit tes pencarian',
        ]);

        $creditLine = JournalEntryDetail::create([
            'journal_entry_id'   => $entry->id,
            'departemen_akun_id' => null,
            'kode_akun'          => '1101002',
            'debits'             => 0,
            'credits'            => 35000000,
            'comment'            => 'Baris credit lawan tes pencarian',
        ]);

        // 2. Akses halaman laporan buku besar
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => '1101001',
            'show_lawan'        => 1,
        ]));

        $response->assertStatus(200);

        // Pastikan placeholder search terupdate
        $response->assertSee('placeholder="Cari voucher, lawan transaksi, dept, ket, debit, kredit..."', false);

        // Ambil nama akun lawan dari kredit
        $contraAccountName = strtolower(optional($creditLine->chartOfAccount)->nama_akun ?? '');

        $content = $response->getContent();

        // Pastikan data-search-text memuat voucher source
        $this->assertStringContainsString(strtolower($entry->source), $content);

        // Pastikan data-search-text memuat angka polos debit (35000000) dan format ribuan (35.000.000)
        $this->assertStringContainsString('35000000', $content);
        $this->assertStringContainsString('35.000.000', $content);

        // Pastikan nama akun lawan ada dalam search text
        if (!empty($contraAccountName)) {
            $this->assertStringContainsString($contraAccountName, $content);
        }
    }

    /**
     * Test Bulk Update Departemen serentak untuk baris terpilih
     */
    public function testBulkUpdateDepartemenSuccessfully()
    {
        $deptAkun = DepartemenAkun::first();
        $this->assertNotNull($deptAkun, 'Harus ada data DepartemenAkun untuk testing');

        // Buat 2 dummy voucher
        $entry1 = JournalEntry::create([
            'source'  => 'TEST-BULKDEPT-1-' . uniqid(),
            'tanggal' => '2026-01-21',
            'comment' => 'Dummy 1 Bulk Dept',
        ]);
        $entry2 = JournalEntry::create([
            'source'  => 'TEST-BULKDEPT-2-' . uniqid(),
            'tanggal' => '2026-01-21',
            'comment' => 'Dummy 2 Bulk Dept',
        ]);
        $this->testJournalIds[] = $entry1->id;
        $this->testJournalIds[] = $entry2->id;

        $line1 = JournalEntryDetail::create([
            'journal_entry_id'   => $entry1->id,
            'departemen_akun_id' => null,
            'kode_akun'          => '1101001',
            'debits'             => 150000,
            'credits'            => 0,
        ]);
        $line2 = JournalEntryDetail::create([
            'journal_entry_id'   => $entry2->id,
            'departemen_akun_id' => null,
            'kode_akun'          => '1101001',
            'debits'             => 250000,
            'credits'            => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->patchJson(
            route('buku_besar.bulk_update_departemen'),
            [
                'detail_ids'         => [$line1->id, $line2->id],
                'departemen_akun_id' => $deptAkun->id,
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'success'       => true,
            'updated_count' => 2,
        ]);

        $line1->refresh();
        $line2->refresh();
        $this->assertEquals($deptAkun->id, $line1->departemen_akun_id);
        $this->assertEquals($deptAkun->id, $line2->departemen_akun_id);
    }

    /**
     * Test Bulk Update Lawan Transaksi: berhasil update single-contra dan lewati (skip) multi-contra
     */
    public function testBulkUpdateContraSuccessfullyAndSkipsMultiContra()
    {
        // 1. Voucher single-contra (1 debit, 1 credit)
        $entrySingle = JournalEntry::create([
            'source'  => 'TEST-BULK-SINGLE-' . uniqid(),
            'tanggal' => '2026-01-22',
            'comment' => 'Single Contra Voucher',
        ]);
        $this->testJournalIds[] = $entrySingle->id;

        $debitSingle = JournalEntryDetail::create([
            'journal_entry_id' => $entrySingle->id,
            'kode_akun'        => '1101001',
            'debits'           => 100000,
            'credits'          => 0,
        ]);
        $creditSingle = JournalEntryDetail::create([
            'journal_entry_id' => $entrySingle->id,
            'kode_akun'        => '1101002',
            'debits'           => 0,
            'credits'          => 100000,
        ]);

        // 2. Voucher multi-contra (1 debit, 2 credits)
        $entryMulti = JournalEntry::create([
            'source'  => 'TEST-BULK-MULTI-' . uniqid(),
            'tanggal' => '2026-01-22',
            'comment' => 'Multi Contra Voucher',
        ]);
        $this->testJournalIds[] = $entryMulti->id;

        $debitMulti = JournalEntryDetail::create([
            'journal_entry_id' => $entryMulti->id,
            'kode_akun'        => '1101001',
            'debits'           => 200000,
            'credits'          => 0,
        ]);
        $creditMulti1 = JournalEntryDetail::create([
            'journal_entry_id' => $entryMulti->id,
            'kode_akun'        => '1101002',
            'debits'           => 0,
            'credits'          => 100000,
        ]);
        $creditMulti2 = JournalEntryDetail::create([
            'journal_entry_id' => $entryMulti->id,
            'kode_akun'        => '1101004',
            'debits'           => 0,
            'credits'          => 100000,
        ]);

        // 3. Request bulk update lawan transaksi menjadi '1101003'
        $response = $this->actingAs($this->adminUser)->patchJson(
            route('buku_besar.bulk_update_contra'),
            [
                'detail_ids'    => [$debitSingle->id, $debitMulti->id],
                'new_kode_akun' => '1101003',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'success'       => true,
            'updated_count' => 1,
            'skipped_count' => 1,
            'kode_akun'     => '1101003',
        ]);

        // Baris credit single berhasil diubah ke 1101003
        $creditSingle->refresh();
        $this->assertEquals('1101003', $creditSingle->kode_akun);

        // Baris credit multi TIDAK berubah (dilewati)
        $creditMulti1->refresh();
        $creditMulti2->refresh();
        $this->assertEquals('1101002', $creditMulti1->kode_akun);
        $this->assertEquals('1101004', $creditMulti2->kode_akun);
    }
}



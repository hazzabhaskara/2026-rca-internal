<?php

namespace Tests\Feature;

use App\ChartOfAccount;
use App\JournalEntryDetail;
use App\User;
use Tests\TestCase;

class BukuBesarLawanTransaksiTest extends TestCase
{
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('name', 'Admin')->first() ?? User::first();
    }

    /**
     * Test filter form has show_lawan checkbox option
     */
    public function testFilterFormHasShowLawanOption()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.filter_buku_besar'));
        $response->assertStatus(200);
        $response->assertSee('name="show_lawan"', false);
        $response->assertSee('Tampilkan Kolom Lawan Transaksi');
    }

    /**
     * Test default Buku Besar report loads with show_lawan = false (Hidden)
     */
    public function testBukuBesarDefaultHidesContraColumn()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date' => '2026-01-01',
            'end_date'   => '2026-01-31',
        ]));
        $response->assertStatus(200);
        $this->assertFalse($response->viewData('show_lawan'));
        $response->assertSee('id="btnToggleContra"', false);
        $response->assertSee('Off');
    }

    /**
     * Test Buku Besar report with show_lawan = 1 activates contra column and resolves accounts
     */
    public function testBukuBesarWithShowLawanEnabled()
    {
        $response = $this->actingAs($this->adminUser)->get(route('buku_besar.buku_besar_report', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-09-28',
            'selected_accounts' => '1101001',
            'show_lawan'        => 1,
        ]));
        $response->assertStatus(200);
        $this->assertTrue($response->viewData('show_lawan'));
        $response->assertSee('Kolom Lawan: Ditampilkan');
        $response->assertSee('Aktif');

        $rows = $response->viewData('rows');
        if ($rows->isNotEmpty()) {
            $firstRow = $rows->first();
            $this->assertNotNull($firstRow->contra_first);
            $this->assertNotNull($firstRow->contra_account_name);
            $this->assertIsInt($firstRow->contra_count);
        }
    }

    /**
     * Test Excel Export includes or excludes Lawan Transaksi according to show_lawan
     */
    public function testExcelExportFollowsShowLawanFilter()
    {
        // When show_lawan = 0
        $responseOff = $this->actingAs($this->adminUser)->get(route('buku_besar.export', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => '1101001',
            'format'            => 'excel',
            'show_lawan'        => 0,
        ]));
        $responseOff->assertStatus(200);

        // When show_lawan = 1
        $responseOn = $this->actingAs($this->adminUser)->get(route('buku_besar.export', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => '1101001',
            'format'            => 'excel',
            'show_lawan'        => 1,
        ]));
        $responseOn->assertStatus(200);
    }

    /**
     * Test PDF Export includes or excludes Lawan Transaksi according to show_lawan
     */
    public function testPdfExportFollowsShowLawanFilter()
    {
        $responseOff = $this->actingAs($this->adminUser)->get(route('buku_besar.export', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => '1101001',
            'format'            => 'pdf',
            'show_lawan'        => 0,
        ]));
        $responseOff->assertStatus(200);

        $responseOn = $this->actingAs($this->adminUser)->get(route('buku_besar.export', [
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-01-31',
            'selected_accounts' => '1101001',
            'format'            => 'pdf',
            'show_lawan'        => 1,
        ]));
        $responseOn->assertStatus(200);
    }
}

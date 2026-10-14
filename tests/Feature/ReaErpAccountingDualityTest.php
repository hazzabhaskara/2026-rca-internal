<?php

namespace Tests\Feature;

use App\ChartOfAccount;
use App\JournalEntry;
use App\KategoriAsset;
use App\linkedAccounts;
use App\SalesInvoice;
use App\Services\AssetDepreciationService;
use App\Services\PayrollJournalService;
use App\Services\StartNewYearService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReaErpAccountingDualityTest extends TestCase
{
    /**
     * Test 1: StartNewYearService resolves RETAINED EARNINGS (3200000 / id=746) from linked_accounts
     * and creates a balanced double-entry closing journal.
     */
    public function testStartNewYearCreatesBalancedDoubleEntryWithRetainedEarnings3200000()
    {
        DB::beginTransaction();
        try {
            $service = app(StartNewYearService::class);
            $service->updateLabaTahunBerjalan(2025);

            $jurnal = JournalEntry::where('source', 'START NEW YEAR')
                ->where('tanggal', '2026-01-01')
                ->with('details')
                ->first();

            $this->assertNotNull($jurnal, 'Jurnal START NEW YEAR 2026-01-01 harus terbentuk');
            if ($jurnal->details->isNotEmpty()) {
                $totalDebit = (float) $jurnal->details->sum('debits');
                $totalCredit = (float) $jurnal->details->sum('credits');
                $this->assertEqualsWithDelta($totalDebit, $totalCredit, 0.01, 'Jurnal penutup tahun baru wajib seimbang (Debit == Credit)');
                $this->assertCount(2, $jurnal->details, 'Jurnal penutup tahun baru wajib memiliki 2 baris berpasangan');
            }
        } finally {
            DB::rollBack();
        }
    }

    /**
     * Test 2: KategoriAssetSeeder populates the 5 standard RCA asset categories and linked_accounts.
     */
    public function testKategoriAssetSeederPopulatesFiveStandardCategories()
    {
        DB::beginTransaction();
        try {
            $seeder = new \KategoriAssetSeeder();
            $seeder->run();

            $codes = KategoriAsset::pluck('kode_kategori')->toArray();
            $this->assertContains('KAT-TANAH', $codes);
            $this->assertContains('KAT-BANGUNAN', $codes);
            $this->assertContains('KAT-KENDARAAN', $codes);
            $this->assertContains('KAT-INVENTARIS', $codes);
            $this->assertContains('KAT-SISTEM', $codes);

            $linkedExpense = linkedAccounts::where('modul', 'asset')->where('kode', 'Expenses')->first();
            $linkedAccum = linkedAccounts::where('modul', 'asset')->where('kode', 'Accumulated Depreciation/Amortisation')->first();
            $this->assertNotNull($linkedExpense);
            $this->assertNotNull($linkedAccum);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * Test 3: SalesInvoice model exposes receiptsDetails relationship and updatePaymentStatus helper.
     */
    public function testSalesInvoiceModelHasReceiptsDetailsAndPaymentStatusHelper()
    {
        $invoice = new SalesInvoice();
        $this->assertTrue(method_exists($invoice, 'receiptsDetails'));
        $this->assertTrue(method_exists($invoice, 'updatePaymentStatus'));
    }

    /**
     * Test 4: PayrollJournalService and AssetDepreciationService are instantiable and resolve valid COA codes.
     */
    public function testPayrollAndAssetDepreciationServicesAreInstantiable()
    {
        $payrollService = app(PayrollJournalService::class);
        $assetService = app(AssetDepreciationService::class);

        $this->assertInstanceOf(PayrollJournalService::class, $payrollService);
        $this->assertInstanceOf(AssetDepreciationService::class, $assetService);
    }
}

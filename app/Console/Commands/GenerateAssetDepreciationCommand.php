<?php

namespace App\Console\Commands;

use App\Services\AssetDepreciationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateAssetDepreciationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asset:depreciate {--period= : Periode penyusutan dalam format YYYY-MM (default: bulan berjalan)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghitung dan mem-posting jurnal penyusutan TangibleAsset & amortisasi IntangibleAsset bulanan ke JournalEntry.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(AssetDepreciationService $service)
    {
        $period = $this->option('period') ?: now()->format('Y-m');
        $this->info("Memproses penyusutan & amortisasi aset untuk periode: {$period}...");

        try {
            $result = $service->generateMonthlyDepreciation($period);
            $this->info($result['message']);
            Log::info('[AssetDepreciation] Completed', $result);
            return 0;
        } catch (\Throwable $e) {
            $this->error('Gagal memproses penyusutan aset: ' . $e->getMessage());
            Log::error('[AssetDepreciation] Failed: ' . $e->getMessage());
            return 1;
        }
    }
}

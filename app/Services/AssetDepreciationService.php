<?php

namespace App\Services;

use App\ChartOfAccount;
use App\DepartemenAkun;
use App\Departement;
use App\IntangibleAsset;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\linkedAccounts;
use App\Lokasi;
use App\TangibleAsset;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssetDepreciationService
{
    /**
     * Map Location keywords to Department codes (0001 - 0009).
     */
    public const LOCATION_DEPT_MAP = [
        'BANDUNG' => '0001',
        'PUSAT'   => '0001',
        'BDG'     => '0001',
        'DUFAN'   => '0002',
        'DFN'     => '0002',
        'ODS'     => '0003',
        'SAMUDRA' => '0003',
        'AWA'     => '0004',
        'ATLANTIS'=> '0004',
        'JBL'     => '0005',
        'BIRD'    => '0005',
        'PBN'     => '0006',
        'PBS'     => '0006',
        'SQP'     => '0007',
        'SCIENTIA'=> '0007',
        'GOWET'   => '0008',
        'GWT'     => '0008',
        'EMO'     => '0009',
    ];

    /**
     * Generate and post monthly depreciation & amortization journal entries for a given YYYY-MM period.
     *
     * @param string|null $period Format 'YYYY-MM' (defaults to current month)
     * @return array Summary of posted depreciation/amortization lines
     */
    public function generateMonthlyDepreciation(?string $period = null): array
    {
        $periodObj = $period ? Carbon::createFromFormat('Y-m', $period)->endOfMonth() : Carbon::now()->endOfMonth();
        $postingDate = $periodObj->format('Y-m-d');
        $periodKey = $periodObj->format('Ym');
        $source = "DEP-{$periodKey}";

        // Cek apakah periode sudah tutup buku (Closing)
        $deptService = app(DepartmentAssignmentService::class);
        if ($deptService->isPeriodClosed($postingDate)) {
            throw new \RuntimeException("Periode akuntansi untuk tanggal {$postingDate} sudah ditutup (Closed).");
        }

        $tangibleExpenseCoa = $this->resolveDepreciationExpenseAccount(false);
        $intangibleExpenseCoa = $this->resolveDepreciationExpenseAccount(true);
        $accumCoa = $this->resolveAccumulatedDepreciationAccount();

        if (!$tangibleExpenseCoa || !$accumCoa) {
            throw new \RuntimeException('Akun Beban Penyusutan (5313xxx) atau Akumulasi Penyusutan (121xxxx) belum tersedia di Chart of Accounts / Linked Accounts Asset.');
        }

        return DB::transaction(function () use (
            $periodObj,
            $postingDate,
            $source,
            $tangibleExpenseCoa,
            $intangibleExpenseCoa,
            $accumCoa
        ) {
            // Hapus jurnal penyusutan bulan yang sama jika sudah pernah digenerate (idempotent)
            $existing = JournalEntry::where('source', $source)->first();
            if ($existing) {
                $existing->details()->delete();
                $existing->delete();
            }

            $lines = [];
            $totalAmount = 0.0;

            // 1. Proses Tangible Assets (Aset Tetap Berwujud)
            $tangibleAssets = TangibleAsset::with(['lokasi', 'golongan', 'metode_penyusutan'])->get();
            foreach ($tangibleAssets as $asset) {
                $monthlyDep = $this->calculateAssetMonthlyAmount($asset, $periodObj, false);
                if ($monthlyDep <= 0) {
                    continue;
                }

                $deptId = $this->resolveDepartemenIdFromLokasi($asset->lokasi);
                $lines[] = [
                    'expense_coa' => $tangibleExpenseCoa,
                    'accum_coa'   => $accumCoa,
                    'dept_id'     => $deptId,
                    'amount'      => $monthlyDep,
                    'comment'     => "Penyusutan Aset Tetap {$asset->kode_asset} - {$asset->nama_asset} ({$periodObj->format('M Y')})",
                ];
                $totalAmount += $monthlyDep;
            }

            // 2. Proses Intangible Assets (Aset Tak Berwujud)
            $intangibleAssets = IntangibleAsset::with(['lokasi', 'golongan', 'metode_penyusutan'])->get();
            foreach ($intangibleAssets as $asset) {
                $monthlyAmort = $this->calculateAssetMonthlyAmount($asset, $periodObj, true);
                if ($monthlyAmort <= 0) {
                    continue;
                }

                $deptId = $this->resolveDepartemenIdFromLokasi($asset->lokasi);
                $lines[] = [
                    'expense_coa' => $intangibleExpenseCoa ?? $tangibleExpenseCoa,
                    'accum_coa'   => $accumCoa,
                    'dept_id'     => $deptId,
                    'amount'      => $monthlyAmort,
                    'comment'     => "Amortisasi Aset Tak Berwujud {$asset->kode_asset} - {$asset->nama_asset} ({$periodObj->format('M Y')})",
                ];
                $totalAmount += $monthlyAmort;
            }

            if (empty($lines)) {
                return [
                    'status'       => 'skipped',
                    'source'       => $source,
                    'tanggal'      => $postingDate,
                    'assets_count' => 0,
                    'total_amount' => 0.0,
                    'message'      => 'Tidak ada aset aktif dengan nilai perolehan > 0 untuk disusutkan pada periode ini.',
                ];
            }

            $journal = JournalEntry::create([
                'source'  => $source,
                'tanggal' => $postingDate,
                'comment' => "Penyusutan & Amortisasi Aset Otomatis Periode " . $periodObj->format('F Y'),
            ]);

            foreach ($lines as $line) {
                $expCoa = $line['expense_coa'];
                $accCoa = $line['accum_coa'];
                $deptId = $line['dept_id'];

                $expDeptAkunId = $this->ensureDepartemenAkun($expCoa->id, $deptId);
                $accDeptAkunId = $this->ensureDepartemenAkun($accCoa->id, $deptId);

                // Debit Beban Penyusutan / Amortisasi (5313xxx / 5314xxx)
                JournalEntryDetail::create([
                    'journal_entry_id'   => $journal->id,
                    'kode_akun'          => $expCoa->kode_akun,
                    'departemen_akun_id' => $expDeptAkunId,
                    'debits'             => $line['amount'],
                    'credits'            => 0,
                    'status'             => 2,
                    'comment'            => $line['comment'],
                ]);

                // Credit Akumulasi Penyusutan / Amortisasi (121xxxx)
                JournalEntryDetail::create([
                    'journal_entry_id'   => $journal->id,
                    'kode_akun'          => $accCoa->kode_akun,
                    'departemen_akun_id' => $accDeptAkunId,
                    'debits'             => 0,
                    'credits'            => $line['amount'],
                    'status'             => 2,
                    'comment'            => 'Akumulasi ' . $line['comment'],
                ]);
            }

            if (function_exists('logActivity')) {
                logActivity("Generate Jurnal Penyusutan Aset {$source} (Total: Rp " . number_format($totalAmount, 2, ',', '.') . ")");
            }

            return [
                'status'           => 'posted',
                'journal_entry_id' => $journal->id,
                'source'           => $source,
                'tanggal'          => $postingDate,
                'assets_count'     => count($lines),
                'total_amount'     => round($totalAmount, 2),
                'message'          => "Berhasil mem-posting jurnal penyusutan {$source} untuk " . count($lines) . " aset.",
            ];
        });
    }

    /**
     * Calculate monthly depreciation/amortization amount for a single asset.
     */
    public function calculateAssetMonthlyAmount($asset, Carbon $periodEnd, bool $isIntangible = false): float
    {
        $rawCost = $asset->nilai_perolehan ?? 0;
        $cost = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string) $rawCost));
        if ($cost <= 0) {
            return 0.0;
        }

        if (!empty($asset->tanggal_perolehan)) {
            try {
                $acqDate = Carbon::parse($asset->tanggal_perolehan)->startOfMonth();
                if ($acqDate->gt($periodEnd)) {
                    return 0.0; // Belum diperoleh pada periode ini
                }

                $usefulYears = (int) ($asset->dalam_tahun ?? optional($asset->golongan)->masa_tahun ?? 0);
                if ($usefulYears > 0) {
                    $monthsElapsed = $acqDate->diffInMonths($periodEnd->copy()->startOfMonth());
                    if ($monthsElapsed >= ($usefulYears * 12)) {
                        return 0.0; // Masa manfaat sudah habis
                    }
                }
            } catch (\Throwable $e) {
                // Lanjutkan jika format tanggal_perolehan non-standar
            }
        }

        $ratePercent = (float) ($isIntangible
            ? ($asset->tarif_amortisasi ?? optional($asset->golongan)->tarif_penyusutan ?? 0)
            : ($asset->tarif_penyusutan ?? optional($asset->golongan)->tarif_penyusutan ?? 0));

        $usefulYears = (float) ($asset->dalam_tahun ?? optional($asset->golongan)->masa_tahun ?? 0);

        if ($ratePercent <= 0 && $usefulYears > 0) {
            $ratePercent = 100.0 / $usefulYears;
        }

        if ($ratePercent <= 0) {
            return 0.0;
        }

        $metodeName = strtolower(optional($asset->metode_penyusutan)->nama_metode ?? 'garis lurus');
        $isDeclining = strpos($metodeName, 'menurun') !== false || strpos($metodeName, 'declining') !== false;

        if ($isDeclining && !empty($asset->tanggal_perolehan)) {
            try {
                $acqYear = Carbon::parse($asset->tanggal_perolehan)->year;
                $yearsElapsed = max(0, $periodEnd->year - $acqYear);
                $bookValueStartOfYear = $cost * pow(max(0.0, 1.0 - ($ratePercent / 100.0)), $yearsElapsed);
                $annualDep = $bookValueStartOfYear * ($ratePercent / 100.0);
                return round($annualDep / 12.0, 2);
            } catch (\Throwable $e) {
                // Fallback ke garis lurus
            }
        }

        $annualDep = $cost * ($ratePercent / 100.0);
        return round($annualDep / 12.0, 2);
    }

    protected function resolveDepreciationExpenseAccount(bool $isIntangible = false): ?ChartOfAccount
    {
        $linked = linkedAccounts::with('akun')
            ->where('modul', 'asset')
            ->where('kode', 'Expenses')
            ->first();

        if (!$isIntangible && $linked && $linked->akun) {
            return $linked->akun;
        }

        if ($isIntangible) {
            $amortCoa = ChartOfAccount::where(function ($q) {
                $q->where('kode_akun', 'like', '5314%')
                  ->orWhere('kode_akun', '5313999')
                  ->orWhere('kode_akun', 'like', '5304%');
            })->orderBy('kode_akun')->first();

            if ($amortCoa) {
                return $amortCoa;
            }
        }

        return ChartOfAccount::where(function ($q) {
            $q->where('kode_akun', 'like', '5313%')
              ->orWhere('kode_akun', 'like', '5303%');
        })->orderBy('kode_akun')->first()
            ?? optional($linked)->akun
            ?? ChartOfAccount::where('tipe_akun', 'Beban')->orderBy('kode_akun')->first();
    }

    protected function resolveAccumulatedDepreciationAccount(): ?ChartOfAccount
    {
        $linked = linkedAccounts::with('akun')
            ->where('modul', 'asset')
            ->where('kode', 'Accumulated Depreciation/Amortisation')
            ->first();

        if ($linked && $linked->akun) {
            return $linked->akun;
        }

        return ChartOfAccount::whereBetween('kode_akun', ['1210000', '1299999'])
            ->orderBy('kode_akun')
            ->first()
            ?? ChartOfAccount::where('nama_akun', 'like', '%Akumulasi Penyusutan%')
                ->orderBy('kode_akun')
                ->first();
    }

    public function resolveDepartemenIdFromLokasi(?Lokasi $lokasi): ?int
    {
        if ($lokasi) {
            $haystack = strtoupper(trim(($lokasi->kode_lokasi ?? '') . ' ' . ($lokasi->nama_lokasi ?? '')));
            foreach (self::LOCATION_DEPT_MAP as $keyword => $deptCode) {
                if (strpos($haystack, $keyword) !== false) {
                    $deptId = Departement::where('kode', $deptCode)->value('id');
                    if ($deptId) {
                        return (int) $deptId;
                    }
                }
            }
        }

        return Departement::where('kode', '0001')->value('id') ?? Departement::value('id');
    }

    protected function ensureDepartemenAkun(?int $akunId, ?int $departemenId): ?int
    {
        if (!$akunId || !$departemenId) {
            return null;
        }

        $da = DepartemenAkun::firstOrCreate([
            'akun_id'       => $akunId,
            'departemen_id' => $departemenId,
        ]);

        return $da->id;
    }
}

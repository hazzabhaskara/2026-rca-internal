<?php

namespace App\Services;

use App\ChartOfAccount;
use App\DepartemenAkun;
use App\Departement;
use App\IntangibleAsset;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\KategoriAsset;
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
        'BANDUNG'  => '0001',
        'PUSAT'    => '0001',
        'BDG'      => '0001',
        'DUFAN'    => '0002',
        'DFN'      => '0002',
        'ODS'      => '0003',
        'SAMUDRA'  => '0003',
        'AWA'      => '0004',
        'ATLANTIS' => '0004',
        'JBL'      => '0005',
        'BIRD'     => '0005',
        'PBN'      => '0006',
        'PBS'      => '0006',
        'SQP'      => '0007',
        'SCIENTIA' => '0007',
        'GOWET'    => '0008',
        'GWT'      => '0008',
        'EMO'      => '0009',
    ];

    /**
     * Map Asset Category keywords to exact COA codes in rembulancitraabadi_2026_db:
     * - BANGUNAN   : Expense 5313001 (PENYUSUTAN BANGUNAN), Accum 1202002 (AK PENYUSUTAN BANGUNAN)
     * - KENDARAAN  : Expense 5313002 (PENYUSUTAN KENDARAAN), Accum 1203002 (AK PENYUSUTAN KENDARAAN)
     * - INVENTARIS : Expense 5313003 (PENYUSUTAN INVENTARIS), Accum 1204002 (AK PENYUSUTAN INVENTARIS)
     * - SISTEM     : Expense 5314002 (AMORTISASI SISTEM), Accum 1302002 (AK AMORTISASI SISTEM)
     */
    public const CATEGORY_COA_MAP = [
        'BANGUNAN'   => ['expense' => '5313001', 'accum' => '1202002'],
        'GEDUNG'     => ['expense' => '5313001', 'accum' => '1202002'],
        'KENDARAAN'  => ['expense' => '5313002', 'accum' => '1203002'],
        'MOBIL'      => ['expense' => '5313002', 'accum' => '1203002'],
        'MOTOR'      => ['expense' => '5313002', 'accum' => '1203002'],
        'INVENTARIS' => ['expense' => '5313003', 'accum' => '1204002'],
        'PERALATAN'  => ['expense' => '5313003', 'accum' => '1204002'],
        'MESIN'      => ['expense' => '5313003', 'accum' => '1204002'],
        'SISTEM'     => ['expense' => '5314002', 'accum' => '1302002'],
        'SOFTWARE'   => ['expense' => '5314002', 'accum' => '1302002'],
        'LISENSI'    => ['expense' => '5314002', 'accum' => '1302002'],
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

        $defaultTangibleExpense = $this->resolveDepreciationExpenseAccount(false);
        $defaultIntangibleExpense = $this->resolveDepreciationExpenseAccount(true);
        $defaultTangibleAccum = $this->resolveAccumulatedDepreciationAccount(false);
        $defaultIntangibleAccum = $this->resolveAccumulatedDepreciationAccount(true);

        if (!$defaultTangibleExpense || !$defaultTangibleAccum) {
            throw new \RuntimeException('Akun Beban Penyusutan (5313xxx) atau Akumulasi Penyusutan (1202002/1203002/1204002) belum tersedia di Chart of Accounts.');
        }

        return DB::transaction(function () use (
            $periodObj,
            $postingDate,
            $source,
            $defaultTangibleExpense,
            $defaultIntangibleExpense,
            $defaultTangibleAccum,
            $defaultIntangibleAccum
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
            $tangibleAssets = TangibleAsset::with(['kategori', 'lokasi', 'golongan', 'metode_penyusutan'])->get();
            foreach ($tangibleAssets as $asset) {
                if ($this->isNonDepreciableLand($asset->kategori)) {
                    continue; // Tanah (1201001) tidak disusutkan
                }

                $monthlyDep = $this->calculateAssetMonthlyAmount($asset, $periodObj, false);
                if ($monthlyDep <= 0) {
                    continue;
                }

                [$expCoa, $accCoa] = $this->resolveAccountsForCategory(
                    $asset->kategori,
                    false,
                    $defaultTangibleExpense,
                    $defaultTangibleAccum
                );

                $deptId = $this->resolveDepartemenIdFromLokasi($asset->lokasi);
                $lines[] = [
                    'expense_coa' => $expCoa,
                    'accum_coa'   => $accCoa,
                    'dept_id'     => $deptId,
                    'amount'      => $monthlyDep,
                    'comment'     => "Penyusutan Aset Tetap {$asset->kode_asset} - {$asset->nama_asset} ({$periodObj->format('M Y')})",
                ];
                $totalAmount += $monthlyDep;
            }

            // 2. Proses Intangible Assets (Aset Tak Berwujud)
            $intangibleAssets = IntangibleAsset::with(['kategori', 'lokasi', 'golongan', 'metode_penyusutan'])->get();
            foreach ($intangibleAssets as $asset) {
                $monthlyAmort = $this->calculateAssetMonthlyAmount($asset, $periodObj, true);
                if ($monthlyAmort <= 0) {
                    continue;
                }

                [$expCoa, $accCoa] = $this->resolveAccountsForCategory(
                    $asset->kategori,
                    true,
                    $defaultIntangibleExpense ?? $defaultTangibleExpense,
                    $defaultIntangibleAccum ?? $defaultTangibleAccum
                );

                $deptId = $this->resolveDepartemenIdFromLokasi($asset->lokasi);
                $lines[] = [
                    'expense_coa' => $expCoa,
                    'accum_coa'   => $accCoa,
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

                // Debit Beban Penyusutan / Amortisasi (5313001/5313002/5313003/5314002)
                JournalEntryDetail::create([
                    'journal_entry_id'   => $journal->id,
                    'kode_akun'          => $expCoa->kode_akun,
                    'departemen_akun_id' => $expDeptAkunId,
                    'debits'             => $line['amount'],
                    'credits'            => 0,
                    'status'             => 2,
                    'comment'            => $line['comment'],
                ]);

                // Credit Akumulasi Penyusutan / Amortisasi (1202002/1203002/1204002/1302002)
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

    protected function isNonDepreciableLand(?KategoriAsset $kategori): bool
    {
        if (!$kategori) {
            return false;
        }
        $haystack = strtoupper(trim(($kategori->kode_kategori ?? '') . ' ' . ($kategori->nama_kategori ?? '')));
        return strpos($haystack, 'TANAH') !== false;
    }

    /**
     * Resolve specific Expense & Accumulated Depreciation COA based on KategoriAsset name/code.
     */
    protected function resolveAccountsForCategory(
        ?KategoriAsset $kategori,
        bool $isIntangible,
        ChartOfAccount $defaultExpense,
        ChartOfAccount $defaultAccum
    ): array {
        if ($kategori) {
            $haystack = strtoupper(trim(($kategori->kode_kategori ?? '') . ' ' . ($kategori->nama_kategori ?? '')));
            foreach (self::CATEGORY_COA_MAP as $keyword => $codes) {
                if (strpos($haystack, $keyword) !== false) {
                    $exp = ChartOfAccount::where('kode_akun', $codes['expense'])->first() ?? $defaultExpense;
                    $acc = ChartOfAccount::where('kode_akun', $codes['accum'])->first() ?? $defaultAccum;
                    return [$exp, $acc];
                }
            }
        }

        return [$defaultExpense, $defaultAccum];
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
                    return 0.0;
                }

                $usefulYears = (int) ($asset->dalam_tahun ?? optional($asset->golongan)->masa_tahun ?? 0);
                if ($usefulYears > 0) {
                    $monthsElapsed = $acqDate->diffInMonths($periodEnd->copy()->startOfMonth());
                    if ($monthsElapsed >= ($usefulYears * 12)) {
                        return 0.0;
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
        if ($isIntangible) {
            $amortCoa = ChartOfAccount::whereIn('kode_akun', ['5314002', '5314001', '5313999'])
                ->orderBy('kode_akun', 'desc')
                ->first();
            if ($amortCoa) {
                return $amortCoa;
            }
        }

        $linked = linkedAccounts::with('akun')
            ->where('modul', 'asset')
            ->where('kode', 'Expenses')
            ->first();

        if (!$isIntangible && $linked && $linked->akun) {
            return $linked->akun;
        }

        return ChartOfAccount::whereIn('kode_akun', ['5313003', '5313002', '5313001'])
            ->orderBy('kode_akun', 'desc')
            ->first()
            ?? ChartOfAccount::where('kode_akun', 'like', '5313%')
                ->where('level_akun', '!=', 'HEADER')
                ->orderBy('kode_akun', 'desc')
                ->first()
            ?? optional($linked)->akun
            ?? ChartOfAccount::where('tipe_akun', 'Beban')->orderBy('kode_akun')->first();
    }

    protected function resolveAccumulatedDepreciationAccount(bool $isIntangible = false): ?ChartOfAccount
    {
        if ($isIntangible) {
            $amortAccum = ChartOfAccount::where('kode_akun', '1302002')->first()
                ?? ChartOfAccount::where('nama_akun', 'like', '%AK AMORTISASI%')->first();
            if ($amortAccum) {
                return $amortAccum;
            }
        }

        $linked = linkedAccounts::with('akun')
            ->where('modul', 'asset')
            ->where('kode', 'Accumulated Depreciation/Amortisation')
            ->first();

        if ($linked && $linked->akun) {
            return $linked->akun;
        }

        // Sesuai COA rembulancitraabadi_2026_db: 1204002 (AK PENYUSUTAN INVENTARIS), 1203002 (KENDARAAN), 1202002 (BANGUNAN)
        return ChartOfAccount::whereIn('kode_akun', ['1204002', '1203002', '1202002', '1302002'])
            ->orderBy('kode_akun', 'desc')
            ->first()
            ?? ChartOfAccount::where(function ($q) {
                $q->where('nama_akun', 'like', '%AK PENYUSUTAN%')
                  ->orWhere('nama_akun', 'like', '%Akumulasi Penyusutan%')
                  ->orWhere('nama_akun', 'like', '%AK AMORTISASI%');
            })->orderBy('kode_akun')->first();
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

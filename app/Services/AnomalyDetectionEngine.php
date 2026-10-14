<?php

namespace App\Services;

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;
use App\JournalEntry;
use App\JournalEntryDetail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnomalyDetectionEngine
{
    /**
     * Rule key identifiers.
     */
    public const RULE_PREFIX_MISMATCH       = 'rule_1_prefix_mismatch';
    public const RULE_VOUCHER_INCONSISTENCY = 'rule_2_voucher_inconsistency';
    public const RULE_UNIT_ACCOUNT_MISMATCH = 'rule_3_unit_account_mismatch';

    /**
     * Department ID to Master Name mapping.
     */
    public const DEPARTMENT_MAP = [
        1 => 'PUSAT BANDUNG',
        2 => 'DUFAN',
        3 => 'ODS',
        4 => 'AWA',
        5 => 'JBL',
        6 => 'PBN',
        7 => 'SQP',
        8 => 'GOWET',
        9 => 'EMO',
    ];

    /**
     * Voucher prefix to expected Department ID mapping.
     * Longer prefixes should take precedence during matching.
     */
    public const PREFIX_DEPARTMENT_MAP = [
        // Daily Closing (CH-{UNIT}-{DATE})
        'CH-GOWET'  => 8, // GOWET
        'CH-GWT'    => 8, // GOWET
        'CH-DFN'    => 2, // DUFAN
        'CH-ODS'    => 3, // ODS
        'CH-AWA'    => 4, // AWA
        'CH-JBL'    => 5, // JBL
        'CH-PBN'    => 6, // PBN
        'CH-SQP'    => 7, // SQP
        'CH-EMO'    => 9, // EMO
        'CH-BDG'    => 1, // PUSAT BANDUNG

        // Unit-Specific Kas
        'KASGOWET'  => 8, // GOWET
        'KASSQP'    => 7, // SQP
        'KASDFN'    => 2, // DUFAN
        'KASOMSET'  => 2, // DUFAN
        'KASBDG'    => 1, // PUSAT BANDUNG

        // Unit-Specific Bank / QRIS Tampungan
        'BNI6625'   => 2, // DUFAN
        'MDR6213'   => 2, // DUFAN
        'MDR0012'   => 3, // ODS
        'MDR6214'   => 4, // AWA
        'MDR0015'   => 5, // JBL
        'MDR6215'   => 1, // PUSAT BANDUNG

        // Unit-Specific Adjustments & Closing
        'ADJSQP'    => 7, // SQP
        'ADJGWT'    => 8, // GOWET
        'LRDFN'     => 2, // DUFAN
        'NRDFN'     => 2, // DUFAN
        'LRODS'     => 3, // ODS
        'NRODS'     => 3, // ODS
        'LRAWA'     => 4, // AWA
        'LRJBL'     => 5, // JBL
        'NRJBL'     => 5, // JBL
        'LRBDG'     => 1, // PUSAT BANDUNG
        'NRBDG'     => 1, // PUSAT BANDUNG
    ];

    /**
     * Generic/operational prefixes that must NEVER trigger Rule 1.
     */
    public const GENERIC_PREFIXES = [
        'PENYUSUTAN',
        'GAJI',
        'BCAEVI',
        'BNI7884',
        'MDR6646',
        'BRI7509',
        'KASIMAN',
        'KASRIS',
        'KASFIR',
        'BCAYER',
        'MDRPES',
        'JAGO',
        'AMORTISASI',
        'START NEW YEAR',
        'HPP',
        'PURCHASE_INVOICE',
        'SALES_INVOICE',
        'PAYMENT',
        'PAYMENTEXPENSE',
        'DEPOSITS',
        'PREPAYMENT',
        'ITEMASSEMBLY',
    ];

    /**
     * Unit-Specific COA Account Codes.
     */
    public const UNIT_ACCOUNT_CODES = [
        // Bank QRIS / Unit Kas
        '1102002' => 3, // MANDIRI QRIS ODS -> ODS (3)
        '1102003' => 4, // MANDIRI QRIS AWA -> AWA (4)
        '1102004' => 5, // MANDIRI QRIS JBL -> JBL (5)
        '1102005' => 2, // MANDIRI QRIS DUFAN -> DUFAN (2)
        '1102036' => 2, // BNI TAMPUNGAN QRIS DUFAN -> DUFAN (2)
        '1101007' => 8, // KAS PESONA - GOWET -> GOWET (8)

        // Inter-Unit Clearing / RK Unit (Piutang Unit)
        '1104011' => 7, // PIUTANG SQP -> SQP (7)
        '1104012' => 8, // PIUTANG GO WET -> GOWET (8)
        '1104016' => 9, // PIUTANG EMO -> EMO (9)

        // Unit Revenue Accounts (Explicit Codes)
        '4160100' => 7, // PENDAPATAN SQP
        '4160101' => 7, // PENDAPATAN SQP
        '4170100' => 8, // PENDAPATAN GOWET
        '4170101' => 8, // PENDAPATAN GOWET
        '4180100' => 9, // PENDAPATAN EMO
        '4180101' => 9, // PENDAPATAN EMO
    ];

    /**
     * Regex patterns for department identification in account names.
     * Uses strict word boundary matching (\b) to prevent false positives.
     */
    public const UNIT_NAME_PATTERNS = [
        '/\b(DUFAN|DFN)\b/i'                   => 2, // DUFAN
        '/\bODS\b/i'                           => 3, // ODS
        '/\b(AWA|ATLANTIS)\b/i'                => 4, // AWA
        '/\bJBL\b/i'                           => 5, // JBL
        '/\bPBN\b/i'                           => 6, // PBN
        '/\bSQP\b/i'                           => 7, // SQP
        '/\b(GOWET|GO\s+WET|GWT)\b/i'          => 8, // GOWET
        '/\bEMO\b/i'                           => 9, // EMO
        '/\b(BDG|BANDUNG)\b/i'                 => 1, // PUSAT BANDUNG
    ];

    /**
     * Detect anomalies for a single journal entry detail.
     *
     * @param JournalEntryDetail $detail
     * @param array $voucherContext Precomputed context (optional, optimizes bulk scanning)
     * @return array ['is_anomaly' => bool, 'rules' => string[], 'reasons' => string[]]
     */
    public function detectAnomalies(JournalEntryDetail $detail, array $voucherContext = []): array
    {
        $rules = [];
        $reasons = [];

        $assignedDeptId = $this->getAssignedDepartmentId($detail);

        // If the transaction has no assigned department, it is handled by Unassigned Watchdog
        if ($assignedDeptId === null) {
            return [
                'is_anomaly' => false,
                'rules'      => [],
                'reasons'    => [],
            ];
        }

        // 1. Evaluate Rule 1: Voucher Prefix Mismatch
        $rule1 = $this->evaluateRule1($detail, $assignedDeptId);
        if ($rule1 !== null) {
            $rules[] = self::RULE_PREFIX_MISMATCH;
            $reasons[] = $rule1['reason'];
        }

        // 2. Evaluate Rule 2: In-Voucher Inconsistency (Outliers)
        $rule2 = $this->evaluateRule2($detail, $assignedDeptId, $voucherContext);
        if ($rule2 !== null) {
            $rules[] = self::RULE_VOUCHER_INCONSISTENCY;
            $reasons[] = $rule2['reason'];
        }

        // 3. Evaluate Rule 3: Unit Account Mismatch
        $rule3 = $this->evaluateRule3($detail, $assignedDeptId);
        if ($rule3 !== null) {
            $rules[] = self::RULE_UNIT_ACCOUNT_MISMATCH;
            $reasons[] = $rule3['reason'];
        }

        return [
            'is_anomaly' => count($rules) > 0,
            'rules'      => $rules,
            'reasons'    => $reasons,
        ];
    }

    /**
     * Evaluate Rule 1 (Prefix Mismatch).
     *
     * @param JournalEntryDetail $detail
     * @param int|null $assignedDeptId
     * @return array|null Null if no anomaly, or ['rule' => ..., 'reason' => ...]
     */
    public function evaluateRule1(JournalEntryDetail $detail, ?int $assignedDeptId = null): ?array
    {
        $assignedDeptId = $assignedDeptId ?? $this->getAssignedDepartmentId($detail);
        if ($assignedDeptId === null) {
            return null;
        }

        $source = $this->getVoucherSource($detail);
        if (empty($source)) {
            return null;
        }

        $expectedDeptId = $this->getExpectedDepartmentForPrefix($source);
        if ($expectedDeptId === null) {
            return null;
        }

        if ($assignedDeptId !== $expectedDeptId) {
            $expectedDeptName = $this->getDepartmentName($expectedDeptId);
            $actualDeptName   = $this->getDepartmentName($assignedDeptId);

            return [
                'rule'             => self::RULE_PREFIX_MISMATCH,
                'expected_dept_id' => $expectedDeptId,
                'actual_dept_id'   => $assignedDeptId,
                'reason'           => "Prefix voucher '{$source}' mengindikasikan unit {$expectedDeptName}, namun dialokasikan ke {$actualDeptName}.",
            ];
        }

        return null;
    }

    /**
     * Evaluate Rule 2 (In-Voucher Inconsistency).
     *
     * @param JournalEntryDetail $detail
     * @param int|null $assignedDeptId
     * @param array $voucherContext
     * @return array|null Null if no anomaly, or ['rule' => ..., 'reason' => ...]
     */
    public function evaluateRule2(JournalEntryDetail $detail, ?int $assignedDeptId = null, array $voucherContext = []): ?array
    {
        $assignedDeptId = $assignedDeptId ?? $this->getAssignedDepartmentId($detail);
        if ($assignedDeptId === null || !$detail->journal_entry_id) {
            return null;
        }

        // 1. Resolve dominant department analysis
        $analysis = $this->resolveVoucherDominantDepartment($detail, $voucherContext);
        if ($analysis === null || $analysis['is_homogeneous'] || $analysis['is_tied']) {
            // Single-department vouchers and equal ties must NOT be flagged as outliers
            return null;
        }

        $dominantDeptId = $analysis['dominant_dept_id'];
        $maxCount       = $analysis['max_count'];
        $totalAssigned  = $analysis['total_assigned'];

        // If this row matches the dominant majority department, it is not an outlier
        if ($assignedDeptId === $dominantDeptId) {
            return null;
        }

        // Outlier row assigned to minority department
        $dominantDeptName = $this->getDepartmentName($dominantDeptId);
        $actualDeptName   = $this->getDepartmentName($assignedDeptId);

        return [
            'rule'             => self::RULE_VOUCHER_INCONSISTENCY,
            'dominant_dept_id' => $dominantDeptId,
            'actual_dept_id'   => $assignedDeptId,
            'reason'           => "Baris dialokasikan ke departemen minoritas ({$actualDeptName}) pada voucher yang didominasi oleh {$dominantDeptName} ({$maxCount} dari {$totalAssigned} baris).",
        ];
    }

    /**
     * Evaluate Rule 3 (Unit Account Mismatch).
     *
     * @param JournalEntryDetail $detail
     * @param int|null $assignedDeptId
     * @return array|null Null if no anomaly, or ['rule' => ..., 'reason' => ...]
     */
    public function evaluateRule3(JournalEntryDetail $detail, ?int $assignedDeptId = null): ?array
    {
        $assignedDeptId = $assignedDeptId ?? $this->getAssignedDepartmentId($detail);
        if ($assignedDeptId === null) {
            return null;
        }

        $kodeAkun = $detail->kode_akun;
        $namaAkun = $this->getAccountName($detail);

        $expectedDeptId = $this->getExpectedDepartmentForAccount($kodeAkun, $namaAkun);
        if ($expectedDeptId === null) {
            return null;
        }

        if ($assignedDeptId !== $expectedDeptId) {
            $expectedDeptName = $this->getDepartmentName($expectedDeptId);
            $actualDeptName   = $this->getDepartmentName($assignedDeptId);

            return [
                'rule'             => self::RULE_UNIT_ACCOUNT_MISMATCH,
                'expected_dept_id' => $expectedDeptId,
                'actual_dept_id'   => $assignedDeptId,
                'reason'           => "Akun unit-spesifik {$kodeAkun}" . ($namaAkun ? " ({$namaAkun})" : "") . " terkait dengan {$expectedDeptName}, namun dialokasikan ke {$actualDeptName}.",
            ];
        }

        return null;
    }

    /**
     * Map a voucher source string to its expected department ID.
     * Returns null if generic or no matching prefix.
     *
     * @param string|null $source
     * @return int|null
     */
    public function getExpectedDepartmentForPrefix(?string $source): ?int
    {
        if (empty($source)) {
            return null;
        }

        $upperSource = strtoupper(trim($source));

        // Generic prefixes must NOT trigger Rule 1
        foreach (self::GENERIC_PREFIXES as $generic) {
            if (strpos($upperSource, $generic) === 0) {
                return null;
            }
        }

        // Sort prefixes by length descending to ensure longest prefix matches first
        $prefixes = self::PREFIX_DEPARTMENT_MAP;
        uksort($prefixes, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($prefixes as $prefix => $deptId) {
            if (strpos($upperSource, $prefix) === 0) {
                return $deptId;
            }
        }

        return null;
    }

    /**
     * Map a COA account code and/or account name to its expected department ID.
     * Uses regex word boundaries (\b) to avoid false positives.
     *
     * @param string|null $kodeAkun
     * @param string|null $namaAkun
     * @return int|null
     */
    public function getExpectedDepartmentForAccount(?string $kodeAkun, ?string $namaAkun = null): ?int
    {
        if (empty($kodeAkun)) {
            return null;
        }

        $cleanKode = trim($kodeAkun);

        // 1. Explicit account code mapping
        if (isset(self::UNIT_ACCOUNT_CODES[$cleanKode])) {
            return self::UNIT_ACCOUNT_CODES[$cleanKode];
        }

        // 2. Explicit numeric account ranges
        $numericCode = is_numeric($cleanKode) ? (int) $cleanKode : 0;
        if ($numericCode > 0) {
            // DUFAN photo revenue
            if ($numericCode >= 4110100 && $numericCode <= 4115302) {
                return 2; // DUFAN
            }
            // ODS photo revenue
            if ($numericCode >= 4120101 && $numericCode <= 4120602) {
                return 3; // ODS
            }
            // AWA (Atlantis) photo revenue
            if ($numericCode >= 4130100 && $numericCode <= 4130102) {
                return 4; // AWA
            }
            // JBL photo revenue
            if ($numericCode >= 4140100 && $numericCode <= 4140102) {
                return 5; // JBL
            }
            // SQP revenue
            if ($numericCode >= 4160100 && $numericCode <= 4160101) {
                return 7; // SQP
            }
            // GOWET revenue
            if ($numericCode >= 4170100 && $numericCode <= 4170101) {
                return 8; // GOWET
            }
            // EMO revenue
            if ($numericCode >= 4180100 && $numericCode <= 4180101) {
                return 9; // EMO
            }
        }

        // 3. Regex word boundary matching on account name
        if (!empty($namaAkun)) {
            // Normalize underscores and hyphens into spaces so \b handles tokens properly
            $normalizedName = strtoupper(str_replace(['_', '-'], ' ', $namaAkun));

            // Explicitly guard generic terms like "PIUTANG KARYAWAN"
            if (strpos($normalizedName, 'KARYAWAN') !== false && strpos($normalizedName, 'PIUTANG') !== false) {
                return null;
            }

            foreach (self::UNIT_NAME_PATTERNS as $pattern => $deptId) {
                if (preg_match($pattern, $normalizedName)) {
                    return $deptId;
                }
            }
        }

        return null;
    }

    /**
     * Compute dominant department and distribution for a voucher.
     *
     * @param JournalEntryDetail $detail
     * @param array $voucherContext
     * @return array|null ['dominant_dept_id' => int|null, 'max_count' => int, 'total_assigned' => int, 'is_homogeneous' => bool, 'is_tied' => bool]
     */
    protected function resolveVoucherDominantDepartment(JournalEntryDetail $detail, array $voucherContext = []): ?array
    {
        // Check if precomputed in voucherContext
        if (isset($voucherContext['dominant_analysis'])) {
            return $voucherContext['dominant_analysis'];
        }

        // Check if sibling dept counts are provided in context
        if (isset($voucherContext['dept_counts'])) {
            $counts = $voucherContext['dept_counts'];
        } else {
            // Query sibling lines for this voucher
            $siblingDetails = null;
            if ($detail->relationLoaded('journalEntry') && $detail->journalEntry && $detail->journalEntry->relationLoaded('details')) {
                $siblingDetails = $detail->journalEntry->details;
            } else {
                $siblingDetails = JournalEntryDetail::where('journal_entry_id', $detail->journal_entry_id)
                    ->whereNotNull('departemen_akun_id')
                    ->with('departemenAkun')
                    ->get();
            }

            $counts = [];
            foreach ($siblingDetails as $sibling) {
                if ($sibling->departemen_akun_id === null) {
                    continue;
                }
                $deptId = $sibling->relationLoaded('departemenAkun') && $sibling->departemenAkun
                    ? $sibling->departemenAkun->departemen_id
                    : DepartemenAkun::where('id', $sibling->departemen_akun_id)->value('departemen_id');

                if ($deptId) {
                    $counts[$deptId] = ($counts[$deptId] ?? 0) + 1;
                }
            }
        }

        $totalAssigned = array_sum($counts);
        $distinctCount = count($counts);

        if ($distinctCount <= 1) {
            return [
                'dominant_dept_id' => $distinctCount === 1 ? array_key_first($counts) : null,
                'max_count'        => $totalAssigned,
                'total_assigned'   => $totalAssigned,
                'is_homogeneous'   => true,
                'is_tied'          => false,
            ];
        }

        $maxCount = max($counts);
        $topDepts = array_keys(array_filter($counts, function ($cnt) use ($maxCount) {
            return $cnt === $maxCount;
        }));

        $isTied = count($topDepts) > 1;

        return [
            'dominant_dept_id' => $isTied ? null : $topDepts[0],
            'max_count'        => $maxCount,
            'total_assigned'   => $totalAssigned,
            'is_homogeneous'   => false,
            'is_tied'          => $isTied,
        ];
    }

    /**
     * Apply anomaly scope filtering to an Eloquent or Query Builder query.
     *
     * @param Builder|\Illuminate\Database\Query\Builder $query
     * @param bool $anomaliesOnly
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function applyAnomalyScope($query, bool $anomaliesOnly = false)
    {
        if (!$anomaliesOnly) {
            return $query;
        }

        return $query->where(function ($anomQuery) {
            // Rule 1: Prefix Mismatch
            $anomQuery->where(function ($q1) {
                $this->applyRule1Scope($q1);
            });

            // Rule 2: In-Voucher Inconsistency (Outliers)
            $anomQuery->orWhere(function ($q2) {
                $this->applyRule2Scope($q2);
            });

            // Rule 3: Unit Account Mismatch
            $anomQuery->orWhere(function ($q3) {
                $this->applyRule3Scope($q3);
            });
        });
    }

    /**
     * Calculate total anomalies matching the specified filter criteria.
     *
     * @param array $filters
     * @return int
     */
    public function calculateAnomalyCounts(array $filters = []): int
    {
        $query = JournalEntryDetail::whereNotNull('departemen_akun_id');

        // Filter: Department
        if (!empty($filters['departemen_id'])) {
            $query->whereHas('departemenAkun', function ($q) use ($filters) {
                $q->where('departemen_id', $filters['departemen_id']);
            });
        }

        // Filter: Date range
        $startDate = $filters['start_date'] ?? $filters['tanggal_mulai'] ?? null;
        $endDate   = $filters['end_date'] ?? $filters['tanggal_selesai'] ?? null;

        if (!empty($startDate)) {
            $query->whereHas('journalEntry', function ($q) use ($startDate) {
                $q->whereDate('tanggal', '>=', $startDate);
            });
        }
        if (!empty($endDate)) {
            $query->whereHas('journalEntry', function ($q) use ($endDate) {
                $q->whereDate('tanggal', '<=', $endDate);
            });
        }

        // Filter: Account Category (Real vs Nominal)
        $kategori = $filters['kategori'] ?? null;
        if ($kategori === 'real') {
            $query->whereHas('chartOfAccount', function ($q) {
                $q->whereIn('tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas']);
            });
        } elseif ($kategori === 'nominal') {
            $query->whereHas('chartOfAccount', function ($q) {
                $q->whereIn('tipe_akun', ['Pendapatan', 'Beban']);
            });
        }

        // Filter: Specific Tipe Akun
        if (!empty($filters['tipe_akun']) && $filters['tipe_akun'] !== 'all') {
            $query->whereHas('chartOfAccount', function ($q) use ($filters) {
                $q->where('tipe_akun', $filters['tipe_akun']);
            });
        }

        // Filter: Voucher Source Prefix
        if (!empty($filters['source_prefix']) && $filters['source_prefix'] !== 'all') {
            $query->whereHas('journalEntry', function ($q) use ($filters) {
                $q->where('source', 'LIKE', $filters['source_prefix'] . '%');
            });
        }

        // Filter: Search Keyword
        $search = $filters['search'] ?? $filters['q'] ?? null;
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('journal_entry_details.kode_akun', 'LIKE', "%{$search}%")
                    ->orWhereHas('chartOfAccount', function ($coaQ) use ($search) {
                        $coaQ->where('nama_akun', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('journalEntry', function ($jeQ) use ($search) {
                        $jeQ->where('source', 'LIKE', "%{$search}%")
                            ->orWhere('comment', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('journal_entry_details.comment', 'LIKE', "%{$search}%");
            });
        }

        $this->applyAnomalyScope($query, true);

        return $query->count();
    }

    /**
     * Apply Rule 1 scope: Voucher Prefix Mismatch.
     *
     * @param Builder|\Illuminate\Database\Query\Builder $query
     */
    protected function applyRule1Scope($query)
    {
        $query->whereExists(function ($sub) {
            $sub->select(DB::raw(1))
                ->from('journal_entries as je')
                ->join('departemen_akuns as da', 'da.id', '=', 'journal_entry_details.departemen_akun_id')
                ->whereColumn('je.id', 'journal_entry_details.journal_entry_id')
                ->where(function ($filter) {
                    $groups = $this->getDepartmentPrefixGroups();
                    $first = true;
                    foreach ($groups as $deptId => $prefixes) {
                        $clause = function ($prefixGroup) use ($deptId, $prefixes) {
                            $prefixGroup->where(function ($pQ) use ($prefixes) {
                                foreach ($prefixes as $p) {
                                    $pQ->orWhere('je.source', 'LIKE', $p . '%');
                                }
                            })->where('da.departemen_id', '!=', $deptId);
                        };

                        if ($first) {
                            $filter->where($clause);
                            $first = false;
                        } else {
                            $filter->orWhere($clause);
                        }
                    }
                });
        });
    }

    /**
     * Apply Rule 2 scope: In-Voucher Inconsistency (Statistical Outlier).
     *
     * @param Builder|\Illuminate\Database\Query\Builder $query
     */
    protected function applyRule2Scope($query)
    {
        $dominantSubquery = DB::table('journal_entry_details as d')
            ->join('departemen_akuns as a', 'a.id', '=', 'd.departemen_akun_id')
            ->whereNotNull('d.departemen_akun_id')
            ->select(
                'd.journal_entry_id',
                'a.departemen_id as dominant_dept_id',
                DB::raw('COUNT(*) as dept_count'),
                DB::raw('RANK() OVER (PARTITION BY d.journal_entry_id ORDER BY COUNT(*) DESC) as rnk'),
                DB::raw('COUNT(*) OVER (PARTITION BY d.journal_entry_id) as num_distinct_depts')
            )
            ->groupBy('d.journal_entry_id', 'a.departemen_id');

        $strictDominant = DB::table(DB::raw("({$dominantSubquery->toSql()}) as v_sub"))
            ->mergeBindings($dominantSubquery)
            ->select('v_sub.journal_entry_id', DB::raw('ANY_VALUE(v_sub.dominant_dept_id) as dominant_dept_id'))
            ->where('v_sub.rnk', 1)
            ->where('v_sub.num_distinct_depts', '>', 1)
            ->groupBy('v_sub.journal_entry_id')
            ->havingRaw('COUNT(*) = 1');

        $query->whereExists(function ($sub) use ($strictDominant) {
            $sub->select(DB::raw(1))
                ->from('departemen_akuns as da')
                ->joinSub($strictDominant, 'dom', function ($join) {
                    $join->on('dom.journal_entry_id', '=', 'journal_entry_details.journal_entry_id');
                })
                ->whereColumn('da.id', 'journal_entry_details.departemen_akun_id')
                ->whereColumn('da.departemen_id', '!=', 'dom.dominant_dept_id');
        });
    }

    /**
     * Apply Rule 3 scope: Unit Account Mismatch.
     *
     * @param Builder|\Illuminate\Database\Query\Builder $query
     */
    protected function applyRule3Scope($query)
    {
        $query->whereExists(function ($sub) {
            $sub->select(DB::raw(1))
                ->from('departemen_akuns as da')
                ->whereColumn('da.id', 'journal_entry_details.departemen_akun_id')
                ->where(function ($filter) {
                    // ODS (3)
                    $filter->where(function ($q) {
                        $q->where(function ($accQ) {
                            $accQ->where('journal_entry_details.kode_akun', '1102002')
                                 ->orWhereBetween('journal_entry_details.kode_akun', ['4120101', '4120602']);
                        })->where('da.departemen_id', '!=', 3);
                    });

                    // AWA (4)
                    $filter->orWhere(function ($q) {
                        $q->where(function ($accQ) {
                            $accQ->where('journal_entry_details.kode_akun', '1102003')
                                 ->orWhereBetween('journal_entry_details.kode_akun', ['4130100', '4130102']);
                        })->where('da.departemen_id', '!=', 4);
                    });

                    // JBL (5)
                    $filter->orWhere(function ($q) {
                        $q->where(function ($accQ) {
                            $accQ->where('journal_entry_details.kode_akun', '1102004')
                                 ->orWhereBetween('journal_entry_details.kode_akun', ['4140100', '4140102']);
                        })->where('da.departemen_id', '!=', 5);
                    });

                    // DUFAN (2)
                    $filter->orWhere(function ($q) {
                        $q->where(function ($accQ) {
                            $accQ->whereIn('journal_entry_details.kode_akun', ['1102005', '1102036'])
                                 ->orWhereBetween('journal_entry_details.kode_akun', ['4110100', '4115302']);
                        })->where('da.departemen_id', '!=', 2);
                    });

                    // GOWET (8)
                    $filter->orWhere(function ($q) {
                        $q->where(function ($accQ) {
                            $accQ->whereIn('journal_entry_details.kode_akun', ['1101007', '1104012', '4170100', '4170101']);
                        })->where('da.departemen_id', '!=', 8);
                    });

                    // SQP (7)
                    $filter->orWhere(function ($q) {
                        $q->where(function ($accQ) {
                            $accQ->whereIn('journal_entry_details.kode_akun', ['1104011', '4160100', '4160101']);
                        })->where('da.departemen_id', '!=', 7);
                    });

                    // EMO (9)
                    $filter->orWhere(function ($q) {
                        $q->where(function ($accQ) {
                            $accQ->whereIn('journal_entry_details.kode_akun', ['1104016', '4180100', '4180101']);
                        })->where('da.departemen_id', '!=', 9);
                    });
                });
        });
    }

    /**
     * Group voucher prefixes by expected department ID.
     *
     * @return array [deptId => [prefixes]]
     */
    public function getDepartmentPrefixGroups(): array
    {
        $groups = [];
        foreach (self::PREFIX_DEPARTMENT_MAP as $prefix => $deptId) {
            $groups[$deptId][] = $prefix;
        }

        return $groups;
    }

    /**
     * Resolve assigned department ID from detail model or relations.
     *
     * @param JournalEntryDetail $detail
     * @return int|null
     */
    protected function getAssignedDepartmentId(JournalEntryDetail $detail): ?int
    {
        if ($detail->relationLoaded('departemenAkun') && $detail->departemenAkun) {
            return (int) $detail->departemenAkun->departemen_id;
        }

        if ($detail->departemen_akun_id) {
            $da = DepartemenAkun::find($detail->departemen_akun_id);
            if ($da) {
                return (int) $da->departemen_id;
            }
        }

        return null;
    }

    /**
     * Resolve voucher source string from detail model or relations.
     *
     * @param JournalEntryDetail $detail
     * @return string|null
     */
    protected function getVoucherSource(JournalEntryDetail $detail): ?string
    {
        if ($detail->relationLoaded('journalEntry') && $detail->journalEntry) {
            return $detail->journalEntry->source;
        }

        if ($detail->journal_entry_id) {
            return JournalEntry::where('id', $detail->journal_entry_id)->value('source');
        }

        return null;
    }

    /**
     * Resolve account name string from detail model or relations.
     *
     * @param JournalEntryDetail $detail
     * @return string|null
     */
    protected function getAccountName(JournalEntryDetail $detail): ?string
    {
        if ($detail->relationLoaded('chartOfAccount') && $detail->chartOfAccount) {
            return $detail->chartOfAccount->nama_akun;
        }

        if ($detail->kode_akun) {
            return ChartOfAccount::where('kode_akun', $detail->kode_akun)->value('nama_akun');
        }

        return null;
    }

    /**
     * Helper to get department name from ID.
     *
     * @param int $deptId
     * @return string
     */
    public function getDepartmentName(int $deptId): string
    {
        return self::DEPARTMENT_MAP[$deptId] ?? "Departemen #{$deptId}";
    }

    /**
     * Get human-readable label for an anomaly rule.
     *
     * @param string $ruleKey
     * @return string
     */
    public function getRuleLabel(string $ruleKey): string
    {
        switch ($ruleKey) {
            case self::RULE_PREFIX_MISMATCH:
                return 'Prefix Mismatch';
            case self::RULE_VOUCHER_INCONSISTENCY:
                return 'Inkonsistensi Voucher';
            case self::RULE_UNIT_ACCOUNT_MISMATCH:
                return 'Akun Unit Mismatch';
            default:
                return 'Anomali';
        }
    }

    /**
     * Get detailed description for an anomaly rule.
     *
     * @param string $ruleKey
     * @return string
     */
    public function getRuleDescription(string $ruleKey): string
    {
        switch ($ruleKey) {
            case self::RULE_PREFIX_MISMATCH:
                return 'Prefix voucher mengindikasikan unit kerja berbeda dari departemen yang dialokasikan.';
            case self::RULE_VOUCHER_INCONSISTENCY:
                return 'Baris transaksi dialokasikan ke departemen minoritas dalam voucher multi-departemen.';
            case self::RULE_UNIT_ACCOUNT_MISMATCH:
                return 'Akun COA unit-spesifik atau clearing RK Unit dialokasikan ke departemen yang salah.';
            default:
                return 'Potensi ketidakcocokan alokasi departemen terdeteksi.';
        }
    }

    /**
     * Get Tailwind badge CSS classes for an anomaly rule.
     *
     * @param string $ruleKey
     * @return string
     */
    public function getRuleBadgeClass(string $ruleKey): string
    {
        switch ($ruleKey) {
            case self::RULE_PREFIX_MISMATCH:
                return 'bg-red-100 text-red-800 border-red-300';
            case self::RULE_VOUCHER_INCONSISTENCY:
                return 'bg-amber-100 text-amber-800 border-amber-300';
            case self::RULE_UNIT_ACCOUNT_MISMATCH:
                return 'bg-purple-100 text-purple-800 border-purple-300';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-300';
        }
    }

    /**
     * Generate HTML badges for anomaly result rendering in Blade views.
     *
     * @param array $anomalyResult
     * @return string
     */
    public function getBadgeHtml(array $anomalyResult): string
    {
        if (empty($anomalyResult['is_anomaly']) || empty($anomalyResult['rules'])) {
            return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300"><i class="fas fa-check-circle mr-1"></i> Normal</span>';
        }

        $html = '<div class="flex flex-wrap gap-1">';
        foreach ($anomalyResult['rules'] as $idx => $rule) {
            $label = htmlspecialchars($this->getRuleLabel($rule));
            $reason = htmlspecialchars($anomalyResult['reasons'][$idx] ?? $this->getRuleDescription($rule));
            $classes = $this->getRuleBadgeClass($rule);

            $icon = 'fa-exclamation-triangle';
            if ($rule === self::RULE_PREFIX_MISMATCH) {
                $icon = 'fa-tag';
            } elseif ($rule === self::RULE_VOUCHER_INCONSISTENCY) {
                $icon = 'fa-code-branch';
            } elseif ($rule === self::RULE_UNIT_ACCOUNT_MISMATCH) {
                $icon = 'fa-landmark';
            }

            $html .= "<span class=\"inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {$classes}\" title=\"{$reason}\" data-toggle=\"tooltip\"><i class=\"fas {$icon} mr-1\"></i> {$label}</span>";
        }
        $html .= '</div>';

        return $html;
    }
}

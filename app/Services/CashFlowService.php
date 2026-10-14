<?php

namespace App\Services;

use App\ChartOfAccount;
use App\JournalEntry;
use App\KlasifikasiAkun;
use Illuminate\Support\Facades\DB;

/**
 * Class CashFlowService
 *
 * Core service for PT Rembulan Citra Abadi's Cash Flow Statement (Laporan Arus Kas)
 * Adhering to PSAK 2 / SAK EMKM standards.
 *
 * Features:
 * - Direct Method (Aktivitas Operasional, Investasi, Pendanaan) with Drill-Down
 * - Account-Level Grouping (Mode 2) with Internal Cash Transfer support
 * - Source-Level Grouping (Mode 1) & Universal Listing (Mode 3)
 * - Beginning & Ending Cash Reconciliation against Balance Sheet (Neraca)
 * - Export-ready data structures for Excel and PDF
 */
class CashFlowService
{
    /**
     * Mapping Klasifikasi Akun ke Aktivitas Arus Kas (Metode Langsung)
     */
    public const AKTIVITAS_MAPPING = [
        // ===== AKTIVITAS OPERASIONAL =====
        'Cash'                        => 'Operasional',
        'Bank'                        => 'Operasional',
        'Operating Revenue'           => 'Operasional',
        'Other Revenue'               => 'Operasional',
        'Non-Operating Revenue'       => 'Operasional',
        'Revenue'                     => 'Operasional',
        'Accounts Receivable'         => 'Operasional',
        'Other Receivables'           => 'Operasional',
        'Accounts Payable'            => 'Operasional',
        'Other Payable'               => 'Operasional',
        'Inventory'                   => 'Operasional',
        'Other Current Asset'         => 'Operasional',
        'Current Asset'               => 'Operasional',
        'Current Liability'           => 'Operasional',
        'Other Current Liability'     => 'Operasional',
        'Payroll Expense'             => 'Operasional',
        'Operating Expense'           => 'Operasional',
        'General & Admin. Expense'    => 'Operasional',
        'Cost of Goods Sold'          => 'Operasional',
        'Income Tax Expense'          => 'Operasional',
        'Non-Operating Expense'       => 'Operasional',
        'Credit Card Receivable'      => 'Operasional',
        'Credit Card Payable'         => 'Operasional',
        'Cash Equivalents'            => 'Operasional',
        'Allowance for Bad Debts'     => 'Operasional',
        'Sales Tax Payable'           => 'Operasional',
        'Payroll Tax Payable'         => 'Operasional',
        'Employee Deductions Payable' => 'Operasional',
        'Income Tax Payable'          => 'Operasional',
        'Deferred Revenue'            => 'Operasional',
        'Deferred Income Taxes'       => 'Operasional',
        'Employee Benefits'           => 'Operasional',
        'Expense'                     => 'Operasional',
        'Extraordinary Gain'          => 'Operasional',
        'Extraordinary Loss'          => 'Operasional',

        // ===== AKTIVITAS INVESTASI =====
        'Capital Asset'               => 'Investasi',
        'Accum. Amort./Depreciation'  => 'Investasi',
        'Other Non-Current Asset'     => 'Investasi',
        'Other Asset'                 => 'Investasi',
        'Marketable Securities'       => 'Investasi',
        'Long Term Receivables'       => 'Investasi',
        'Other Long Term Investments' => 'Investasi',
        'Gain'                        => 'Investasi',
        'Loss'                        => 'Investasi',

        // ===== AKTIVITAS PENDANAAN =====
        'Share Capital'               => 'Pendanaan',
        'Retained Earnings'           => 'Pendanaan',
        'Current Earnings'            => 'Pendanaan',
        'Owner/Partner Contributions' => 'Pendanaan',
        'Owner/Partner Withdrawals'   => 'Pendanaan',
        'Dividends'                   => 'Pendanaan',
        'Long Term Debt'              => 'Pendanaan',
        'Short Term Debt'             => 'Pendanaan',
        'Debt'                        => 'Pendanaan',
        'Long Term Liability'         => 'Pendanaan',
        'Other Non-Current Liability' => 'Pendanaan',
        'Other Liability'             => 'Pendanaan',
        'Interest Expense'            => 'Pendanaan',
    ];

    /**
     * Klasifikasi non-cash (beban non-kas yang tidak mempengaruhi arus kas)
     */
    public const NON_CASH_KLASIFIKASI = [
        'Amort./Depreciation Expense',
        'Bad Debt Expense',
    ];

    /**
     * Ambil daftar akun kas & bank untuk form filter
     */
    public function getAvailableCashBankAccounts()
    {
        return ChartOfAccount::whereHas('klasifikasiAkun', function ($query) {
            $query->whereIn('nama_klasifikasi', ['Cash', 'Bank']);
        })->orderBy('kode_akun')->get();
    }

    /**
     * Parse input selected accounts dari request (string dipisahkan koma)
     * Menjamin fallback ke seluruh akun Cash & Bank jika kosong atau tidak valid.
     *
     * @param string|null $selectedAccountsRaw
     * @return array
     */
    public function parseSelectedAccountCodes(?string $selectedAccountsRaw): array
    {
        $selectedAccountCodes = [];

        if (!empty($selectedAccountsRaw)) {
            $items = explode(',', $selectedAccountsRaw);
            foreach ($items as $item) {
                $trimmed = trim($item);
                if ($trimmed === '') {
                    continue;
                }
                $parts = explode(' - ', $trimmed);
                $code = trim($parts[0]);
                if ($code !== '') {
                    $selectedAccountCodes[] = $code;
                }
            }
        }

        $selectedAccountCodes = array_values(array_unique($selectedAccountCodes));

        if (empty($selectedAccountCodes)) {
            $selectedAccountCodes = ChartOfAccount::whereHas('klasifikasiAkun', function ($q) {
                $q->whereIn('nama_klasifikasi', ['Cash', 'Bank']);
            })->pluck('kode_akun')->toArray();
        }

        return $selectedAccountCodes;
    }

    /**
     * Hitung Saldo Awal Kas & Setara Kas sebelum tanggal awal periode
     *
     * @param array $accountCodes
     * @param string $startDate
     * @return float
     */
    public function calculateBeginningBalance(array $accountCodes, string $startDate): float
    {
        if (empty($accountCodes)) {
            return 0.0;
        }

        $balance = DB::table('journal_entry_details')
            ->join('journal_entries', 'journal_entry_details.journal_entry_id', '=', 'journal_entries.id')
            ->whereIn('journal_entry_details.kode_akun', $accountCodes)
            ->where('journal_entries.tanggal', '<', $startDate)
            ->selectRaw('COALESCE(SUM(journal_entry_details.debits - journal_entry_details.credits), 0) as balance')
            ->value('balance');

        return (float) ($balance ?? 0.0);
    }

    /**
     * Ekstraksi dan kalkulasi lengkap transaksi arus kas
     *
     * @param string $startDate
     * @param string $endDate
     * @param string|null $selectedAccountsRaw
     * @param string $displayMode ('source', 'account', 'universal', 'direct')
     * @return array
     */
    public function getCashFlowData(
        string $startDate,
        string $endDate,
        ?string $selectedAccountsRaw = null,
        string $displayMode = 'source'
    ): array {
        $selectedAccountCodes = $this->parseSelectedAccountCodes($selectedAccountsRaw);
        $saldoAwal = $this->calculateBeginningBalance($selectedAccountCodes, $startDate);

        $journalEntries = JournalEntry::with(['details.chartOfAccount.klasifikasiAkun'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $rows = [];

        foreach ($journalEntries as $entry) {
            $details = $entry->details;

            $kasDetails = $details->whereIn('kode_akun', $selectedAccountCodes);
            if ($kasDetails->isEmpty()) {
                continue;
            }

            $lawanDetails = $details->whereNotIn('kode_akun', $selectedAccountCodes);

            // Skenario 1: Internal Cash Transfer (sesama akun Kas/Bank)
            if ($lawanDetails->isEmpty()) {
                // Khusus mode 'account', tampilkan mutasi internal agar buku kas/bank per akun akurat
                if ($displayMode === 'account') {
                    $debetKas = $kasDetails->where('debits', '>', 0);
                    $kreditKas = $kasDetails->where('credits', '>', 0);

                    if ($debetKas->isNotEmpty() && $kreditKas->isNotEmpty()) {
                        foreach ($debetKas as $dKas) {
                            $kreditNames = $kreditKas->map(function ($k) {
                                return $k->kode_akun . ' - ' . ($k->chartOfAccount->nama_akun ?? '');
                            })->implode(', ');

                            $rows[] = [
                                'tanggal'          => $entry->tanggal,
                                'source'           => $entry->source,
                                'akun_kas'         => $dKas->kode_akun . ' - ' . ($dKas->chartOfAccount->nama_akun ?? ''),
                                'kode_kas'         => $dKas->kode_akun,
                                'lawan_akun'       => 'Transfer Kas: ' . $kreditNames,
                                'kode_lawan'       => 'INTERNAL',
                                'nama_lawan'       => 'Transfer Antar Kas/Bank',
                                'klasifikasi_akun' => 'Cash Equivalents',
                                'aktivitas'        => 'Operasional',
                                'line_comment'     => $dKas->comment ?? 'Transfer Masuk Antar Kas/Bank',
                                'keterangan'       => $entry->comment ?? '',
                                'cash_in'          => (float) $dKas->debits,
                                'cash_out'         => 0.0,
                            ];
                        }

                        foreach ($kreditKas as $kKas) {
                            $debetNames = $debetKas->map(function ($d) {
                                return $d->kode_akun . ' - ' . ($d->chartOfAccount->nama_akun ?? '');
                            })->implode(', ');

                            $rows[] = [
                                'tanggal'          => $entry->tanggal,
                                'source'           => $entry->source,
                                'akun_kas'         => $kKas->kode_akun . ' - ' . ($kKas->chartOfAccount->nama_akun ?? ''),
                                'kode_kas'         => $kKas->kode_akun,
                                'lawan_akun'       => 'Transfer Kas: ' . $debetNames,
                                'kode_lawan'       => 'INTERNAL',
                                'nama_lawan'       => 'Transfer Antar Kas/Bank',
                                'klasifikasi_akun' => 'Cash Equivalents',
                                'aktivitas'        => 'Operasional',
                                'line_comment'     => $kKas->comment ?? 'Transfer Keluar Antar Kas/Bank',
                                'keterangan'       => $entry->comment ?? '',
                                'cash_in'          => 0.0,
                                'cash_out'         => (float) $kKas->credits,
                            ];
                        }
                    }
                }
                // Mode direct, universal, dan source tidak menampilkan internal transfer karena Net = 0
                continue;
            }

            // Skenario 2: Transaksi Normal (Ada Akun Kas vs Akun Lawan Non-Kas)
            $totalDebetLawan = $lawanDetails->sum('debits');
            $totalKreditLawan = $lawanDetails->sum('credits');

            foreach ($kasDetails as $kas) {
                $isCashIn = $kas->debits > 0;
                $totalKas = (float) ($isCashIn ? $kas->debits : $kas->credits);

                foreach ($lawanDetails as $lawan) {
                    $nilaiLawan = (float) ($isCashIn ? $lawan->credits : $lawan->debits);
                    $totalLawan = (float) ($isCashIn ? $totalKreditLawan : $totalDebetLawan);

                    if ($totalLawan == 0.0) {
                        $totalLawan = 1.0;
                    }

                    $proporsi = $nilaiLawan / $totalLawan;
                    $nilaiKasProporsional = $totalKas * $proporsi;

                    if ($nilaiKasProporsional <= 0.0) {
                        continue;
                    }

                    $klasifikasiAkun = $lawan->chartOfAccount->klasifikasiAkun->nama_klasifikasi ?? 'Lainnya';
                    $aktivitas = self::AKTIVITAS_MAPPING[$klasifikasiAkun] ?? 'Operasional';

                    if (in_array($klasifikasiAkun, self::NON_CASH_KLASIFIKASI, true)) {
                        continue;
                    }

                    $rows[] = [
                        'tanggal'          => $entry->tanggal,
                        'source'           => $entry->source,
                        'akun_kas'         => $kas->kode_akun . ' - ' . ($kas->chartOfAccount->nama_akun ?? ''),
                        'kode_kas'         => $kas->kode_akun,
                        'lawan_akun'       => $lawan->kode_akun . ' - ' . ($lawan->chartOfAccount->nama_akun ?? ''),
                        'kode_lawan'       => $lawan->kode_akun,
                        'nama_lawan'       => $lawan->chartOfAccount->nama_akun ?? '',
                        'klasifikasi_akun' => $klasifikasiAkun,
                        'aktivitas'        => $aktivitas,
                        'line_comment'     => $lawan->comment ?? $kas->comment ?? '',
                        'keterangan'       => $entry->comment ?? '',
                        'cash_in'          => $isCashIn ? $nilaiKasProporsional : 0.0,
                        'cash_out'         => !$isCashIn ? $nilaiKasProporsional : 0.0,
                    ];
                }
            }
        }

        $grandCashIn = (float) collect($rows)->sum('cash_in');
        $grandCashOut = (float) collect($rows)->sum('cash_out');
        $netCashFlow = $grandCashIn - $grandCashOut;
        $saldoAkhir = $saldoAwal + $netCashFlow;

        $aktivitasData = [];
        if ($displayMode === 'direct') {
            $aktivitasData = $this->groupByAktivitas($rows);
        }

        return [
            'rows'                 => $rows,
            'aktivitasData'        => $aktivitasData,
            'saldoAwal'            => $saldoAwal,
            'saldoAkhir'           => $saldoAkhir,
            'grandCashIn'          => $grandCashIn,
            'grandCashOut'         => $grandCashOut,
            'netCashFlow'          => $netCashFlow,
            'selectedAccountCodes' => $selectedAccountCodes,
            'tanggalAwal'          => $startDate,
            'tanggalAkhir'         => $endDate,
            'displayMode'          => $displayMode,
        ];
    }

    /**
     * Group rows by aktivitas for Direct Method display
     *
     * @param array $rows
     * @return array
     */
    public function groupByAktivitas(array $rows): array
    {
        $klasifikasiUrutan = KlasifikasiAkun::pluck('id', 'nama_klasifikasi')->toArray();

        $aktivitas = [
            'Operasional' => [
                'label'         => 'Arus Kas dari Aktivitas Operasional',
                'items'         => [],
                'cash_in'       => 0.0,
                'cash_out'      => 0.0,
                'net_cash_flow' => 0.0,
            ],
            'Investasi' => [
                'label'         => 'Arus Kas dari Aktivitas Investasi',
                'items'         => [],
                'cash_in'       => 0.0,
                'cash_out'      => 0.0,
                'net_cash_flow' => 0.0,
            ],
            'Pendanaan' => [
                'label'         => 'Arus Kas dari Aktivitas Pendanaan',
                'items'         => [],
                'cash_in'       => 0.0,
                'cash_out'      => 0.0,
                'net_cash_flow' => 0.0,
            ],
        ];

        foreach ($rows as $row) {
            $akt = $row['aktivitas'] ?? 'Operasional';
            $klasifikasi = $row['klasifikasi_akun'] ?? 'Lainnya';

            if (!isset($aktivitas[$akt])) {
                $akt = 'Operasional';
            }

            if (!isset($aktivitas[$akt]['items'][$klasifikasi])) {
                $aktivitas[$akt]['items'][$klasifikasi] = [
                    'label'         => $klasifikasi,
                    'urutan'        => $klasifikasiUrutan[$klasifikasi] ?? 999,
                    'cash_in'       => 0.0,
                    'cash_out'      => 0.0,
                    'net_cash_flow' => 0.0,
                    'count'         => 0,
                ];
            }

            $in = (float) $row['cash_in'];
            $out = (float) $row['cash_out'];

            $aktivitas[$akt]['items'][$klasifikasi]['cash_in'] += $in;
            $aktivitas[$akt]['items'][$klasifikasi]['cash_out'] += $out;
            $aktivitas[$akt]['items'][$klasifikasi]['net_cash_flow'] += ($in - $out);
            $aktivitas[$akt]['items'][$klasifikasi]['count']++;

            $aktivitas[$akt]['cash_in'] += $in;
            $aktivitas[$akt]['cash_out'] += $out;
            $aktivitas[$akt]['net_cash_flow'] += ($in - $out);
        }

        foreach ($aktivitas as $key => &$aktGroup) {
            uasort($aktGroup['items'], function ($a, $b) {
                return ($a['urutan'] ?? 999) <=> ($b['urutan'] ?? 999);
            });
        }

        return $aktivitas;
    }
}

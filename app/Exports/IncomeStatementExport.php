<?php

namespace App\Exports;

use App\ChartOfAccount;
use App\JournalEntryDetail;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class IncomeStatementExport implements FromView
{
    protected $tanggalAwal;
    protected $tanggalAkhir;

    public function __construct($tanggalAwal, $tanggalAkhir)
    {
        $this->tanggalAwal  = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function view(): View
    {
        // 🔹 Ambil mutasi per akun (EXCLUDE transaksi Start New Year)
        $entries = DB::table('journal_entry_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
            ->select(
                'jed.kode_akun',
                DB::raw('SUM(jed.debits) as total_debit'),
                DB::raw('SUM(jed.credits) as total_kredit')
            )
            ->whereBetween('je.tanggal', [$this->tanggalAwal, $this->tanggalAkhir])
            ->where('je.source', '!=', 'START NEW YEAR')
            ->groupBy('jed.kode_akun')
            ->get()
            ->keyBy('kode_akun');

        // 🔹 Ambil master akun (supaya rapi & konsisten)
        $accounts = ChartOfAccount::whereIn('tipe_akun', ['Pendapatan', 'Beban'])
            ->orderBy('kode_akun')
            ->get();

        $groups = [];
        $currentGroup = null;
        $currentAccount = null;

        foreach ($accounts as $account) {
            // Mulai grup baru kalau level GROUP ACCOUNT
            if ($account->level_akun === 'GROUP ACCOUNT') {
                // Tutup account sebelumnya jika ada
                if ($currentAccount) {
                    if (!empty($currentAccount['sub_accounts'])) {
                        $currentAccount['saldo_account'] = array_sum(array_column($currentAccount['sub_accounts'], 'saldo'));
                    }
                    if ($currentAccount['saldo_account'] != 0 && $currentGroup) {
                        $currentGroup['accounts'][] = $currentAccount;
                    }
                }
                $currentAccount = null;
                
                // Tutup group sebelumnya jika ada
                if ($currentGroup && !empty($currentGroup['accounts'])) {
                    $currentGroup['saldo_group'] = array_sum(array_column($currentGroup['accounts'], 'saldo_account'));
                    $groups[] = $currentGroup;
                }

                $currentGroup = [
                    'group'       => $account->nama_akun,
                    'tipe'        => strtolower($account->tipe_akun),
                    'accounts'    => [],
                    'saldo_group' => 0,
                ];
                continue;
            }

            // Skip HEADER
            if ($account->level_akun === 'HEADER') {
                continue;
            }

            // ACCOUNT level - buat parent baru
            if ($account->level_akun === 'ACCOUNT') {
                // Tutup account sebelumnya jika ada
                if ($currentAccount) {
                    if (!empty($currentAccount['sub_accounts'])) {
                        $currentAccount['saldo_account'] = array_sum(array_column($currentAccount['sub_accounts'], 'saldo'));
                    }
                    if ($currentAccount['saldo_account'] != 0 && $currentGroup) {
                        $currentGroup['accounts'][] = $currentAccount;
                    }
                }

                // Ambil saldo ACCOUNT dari mutasi - FIXED: menggunakan perhitungan NET
                $entry = $entries->get($account->kode_akun);
                $totalDebit  = $entry->total_debit ?? 0;
                $totalKredit = $entry->total_kredit ?? 0;
                $saldo = strtolower($account->tipe_akun) === 'pendapatan'
                    ? ($totalKredit - $totalDebit)
                    : ($totalDebit - $totalKredit);

                $currentAccount = [
                    'kode_akun'     => $account->kode_akun,
                    'nama_akun'     => $account->nama_akun,
                    'tipe_akun'     => $account->tipe_akun,
                    'level_akun'    => $account->level_akun,
                    'saldo_account' => $saldo,
                    'sub_accounts'  => [],
                ];
                continue;
            }

            // SUB ACCOUNT level - masukkan ke account parent
            if ($account->level_akun === 'SUB ACCOUNT') {
                if (!$currentGroup) {
                    $currentGroup = [
                        'group'       => '',
                        'tipe'        => strtolower($account->tipe_akun),
                        'accounts'    => [],
                        'saldo_group' => 0,
                    ];
                }

                // Ambil saldo SUB ACCOUNT dari mutasi - FIXED: menggunakan perhitungan NET
                $entry = $entries->get($account->kode_akun);
                $totalDebit  = $entry->total_debit ?? 0;
                $totalKredit = $entry->total_kredit ?? 0;
                $saldo = strtolower($account->tipe_akun) === 'pendapatan'
                    ? ($totalKredit - $totalDebit)
                    : ($totalDebit - $totalKredit);

                if ($saldo != 0) {
                    if ($currentAccount) {
                        $currentAccount['sub_accounts'][] = [
                            'kode_akun'  => $account->kode_akun,
                            'nama_akun'  => $account->nama_akun,
                            'tipe_akun'  => $account->tipe_akun,
                            'level_akun' => $account->level_akun,
                            'saldo'      => $saldo,
                        ];
                    } else {
                        $currentGroup['accounts'][] = [
                            'kode_akun'     => $account->kode_akun,
                            'nama_akun'     => $account->nama_akun,
                            'tipe_akun'     => $account->tipe_akun,
                            'level_akun'    => $account->level_akun,
                            'saldo_account' => $saldo,
                            'sub_accounts'  => [],
                        ];
                    }
                }
            }
        }

        // Tutup account terakhir jika ada
        if ($currentAccount) {
            if (!empty($currentAccount['sub_accounts'])) {
                $currentAccount['saldo_account'] = array_sum(array_column($currentAccount['sub_accounts'], 'saldo'));
            }
            if ($currentAccount['saldo_account'] != 0 && $currentGroup) {
                $currentGroup['accounts'][] = $currentAccount;
            }
        }

        // Tutup grup terakhir
        if ($currentGroup && !empty($currentGroup['accounts'])) {
            $currentGroup['saldo_group'] = array_sum(array_column($currentGroup['accounts'], 'saldo_account'));
            $groups[] = $currentGroup;
        }

        // 🔹 Pisahkan pendapatan vs beban
        $groupsPendapatan = array_values(array_filter($groups, fn($g) => $g['tipe'] === 'pendapatan'));
        $groupsBeban      = array_values(array_filter($groups, fn($g) => $g['tipe'] === 'beban'));

        $totalPendapatan = array_sum(array_column($groupsPendapatan, 'saldo_group'));
        $totalBeban      = array_sum(array_column($groupsBeban, 'saldo_group'));

        $labaSebelumPajak = $totalPendapatan - $totalBeban;

        // 🔹 Pajak penghasilan
        $akunPajak  = ChartOfAccount::where('is_income_tax', 1)->first();
        $bebanPajak = 0;

        if ($akunPajak) {
            $entryPajak = $entries->get($akunPajak->kode_akun);
            if ($entryPajak) {
                $bebanPajak = ($entryPajak->total_debit ?? 0) - ($entryPajak->total_kredit ?? 0);
            }
        }

        $labaSetelahPajak = $labaSebelumPajak - $bebanPajak;

        return view('income_statement.excel', [
            'groupsPendapatan' => $groupsPendapatan,
            'groupsBeban'      => $groupsBeban,
            'totalPendapatan'  => $totalPendapatan,
            'totalBeban'       => $totalBeban,
            'labaSebelumPajak' => $labaSebelumPajak,
            'bebanPajak'       => $bebanPajak,
            'labaSetelahPajak' => $labaSetelahPajak,
            'start_date'       => $this->tanggalAwal,
            'end_date'         => $this->tanggalAkhir,
        ]);
    }
}

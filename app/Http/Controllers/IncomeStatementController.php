<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\Departemen;
use App\Departement;
use App\Exports\IncomeStatementDepartementExport;
use App\Exports\IncomeStatementExport;
use App\JournalEntryDetail;
use App\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class IncomeStatementController extends Controller
{
    //
    public function incomeStatementFilter()
    {
        return view('income_statement.filter_income_statement');
    }

    public function incomeStatementReport(Request $request)
    {
        $tanggalAwal  = $request->start_date;
        $tanggalAkhir = $request->end_date;
        $siteTitle    = Setting::where('key', 'site_title')->value('value');

        // 🔹 Ambil mutasi per akun (EXCLUDE transaksi Start New Year)
        $entries = DB::table('journal_entry_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
            ->select(
                'jed.kode_akun',
                DB::raw('SUM(jed.debits) as total_debit'),
                DB::raw('SUM(jed.credits) as total_kredit')
            )
            ->whereBetween('je.tanggal', [$tanggalAwal, $tanggalAkhir])
            // Filter: jangan termasuk jurnal penutup Start New Year
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
                // Tutup account sebelumnya jika ada (baik dengan/tanpa sub_accounts)
                if ($currentAccount) {
                    // Jika ada sub_accounts, hitung ulang saldo dari sub_accounts
                    if (!empty($currentAccount['sub_accounts'])) {
                        $currentAccount['saldo_account'] = array_sum(array_column($currentAccount['sub_accounts'], 'saldo'));
                    }
                    // Tambahkan ke group jika saldo tidak 0
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
                // Tutup account sebelumnya jika ada (baik dengan/tanpa sub_accounts)
                if ($currentAccount) {
                    // Jika ada sub_accounts, hitung ulang saldo dari sub_accounts
                    if (!empty($currentAccount['sub_accounts'])) {
                        $currentAccount['saldo_account'] = array_sum(array_column($currentAccount['sub_accounts'], 'saldo'));
                    }
                    // Tambahkan ke group jika saldo tidak 0
                    if ($currentAccount['saldo_account'] != 0 && $currentGroup) {
                        $currentGroup['accounts'][] = $currentAccount;
                    }
                }

                // Ambil saldo ACCOUNT dari mutasi
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
                    'saldo_account' => $saldo, // Saldo langsung jika tidak ada sub
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

                // Ambil saldo SUB ACCOUNT dari mutasi
                $entry = $entries->get($account->kode_akun);
                $totalDebit  = $entry->total_debit ?? 0;
                $totalKredit = $entry->total_kredit ?? 0;
                $saldo = strtolower($account->tipe_akun) === 'pendapatan'
                    ? ($totalKredit - $totalDebit)
                    : ($totalDebit - $totalKredit);

                if ($saldo != 0) {
                    if ($currentAccount) {
                        // Ada parent ACCOUNT
                        $currentAccount['sub_accounts'][] = [
                            'kode_akun'  => $account->kode_akun,
                            'nama_akun'  => $account->nama_akun,
                            'tipe_akun'  => $account->tipe_akun,
                            'level_akun' => $account->level_akun,
                            'saldo'      => $saldo,
                        ];
                    } else {
                        // Tidak ada parent ACCOUNT, langsung ke group
                        // Buat dummy account
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

        // Tutup account terakhir jika ada (baik dengan/tanpa sub_accounts)
        if ($currentAccount) {
            // Jika ada sub_accounts, hitung ulang saldo dari sub_accounts
            if (!empty($currentAccount['sub_accounts'])) {
                $currentAccount['saldo_account'] = array_sum(array_column($currentAccount['sub_accounts'], 'saldo'));
            }
            // Tambahkan ke group jika saldo tidak 0
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

        // Pajak penghasilan
        $akunPajak  = ChartOfAccount::where('is_income_tax', 1)->first();
        $bebanPajak = 0;
        if ($akunPajak) {
            $entryPajak = $entries->get($akunPajak->kode_akun);
            if ($entryPajak) {
                $bebanPajak = ($entryPajak->total_debit ?? 0) - ($entryPajak->total_kredit ?? 0);
            }
        }

        $labaSetelahPajak = $labaSebelumPajak - $bebanPajak;

        return view('income_statement.income_statement_report', [
            'groupsPendapatan' => $groupsPendapatan,
            'groupsBeban'      => $groupsBeban,
            'totalPendapatan'  => $totalPendapatan,
            'totalBeban'       => $totalBeban,
            'labaSebelumPajak' => $labaSebelumPajak,
            'bebanPajak'       => $bebanPajak,
            'labaSetelahPajak' => $labaSetelahPajak,
            'tanggalAwal'      => $tanggalAwal,
            'tanggalAkhir'     => $tanggalAkhir,
            'siteTitle'        => $siteTitle,
        ]);
    }


    public function incomeStatementFilterDepartement()
    {
        $departemens = Departement::all();
        $account = chartOfAccount::all();

        return view('income_statement.filter_income_statement_departement', compact('departemens', 'account'));
    }
    public function incomeStatementDepartement(Request $request)
    {
        // Semua departemen untuk header
        $allDepartemens = Departement::select('id', 'kode', 'deskripsi')->get();

        $tanggalAwal  = $request->start_date;
        $tanggalAkhir = $request->end_date;
        $selectedAccounts = $request->selected_accounts;

        // Ambil departemen terpilih dari request (kalau ada)
        $selectedDepartemens = $request->selected_departemens
            ? explode(',', $request->selected_departemens)
            : [];

        // Untuk body laporan → kalau ada yang dipilih pakai filter, kalau kosong pakai semua
        $filteredDepartemens = !empty($selectedDepartemens)
            ? Departement::whereIn('id', $selectedDepartemens)->get()
            : $allDepartemens;

        // Proses akun terpilih
        $kodeAkunTerpilih = [];
        if (!empty($selectedAccounts)) {
            $kodeAkunTerpilih = explode(',', $selectedAccounts);
            $kodeAkunTerpilih = array_map(function ($akun) {
                return trim(explode(' - ', $akun)[0]);
            }, $kodeAkunTerpilih);
        }

        $accountsQuery = ChartOfAccount::whereIn('tipe_akun', ['Pendapatan', 'Beban'])
            ->orderBy('kode_akun');

        if (!empty($kodeAkunTerpilih)) {
            $accountsQuery->whereIn('kode_akun', $kodeAkunTerpilih);
        }

        $accounts = $accountsQuery->get();

        $incomeStatement = [];
        $totalPendapatan = 0;
        $totalBeban = 0;

        foreach ($accounts as $account) {
            $entries = JournalEntryDetail::where('kode_akun', $account->kode_akun)
                ->whereHas('journalEntry', function ($q) use ($tanggalAwal, $tanggalAkhir) {
                    $q->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
                      ->where('source', '!=', 'START NEW YEAR'); // Exclude jurnal penutup
                })
                ->selectRaw('SUM(debits) as total_debit, SUM(credits) as total_kredit')
                ->first();

            $totalDebit  = $entries->total_debit ?? 0;
            $totalKredit = $entries->total_kredit ?? 0;

            // Perhitungan NET: Pendapatan = Kredit - Debit, Beban = Debit - Kredit
            if (strtolower($account->tipe_akun) === 'pendapatan') {
                $saldoUtama = $totalKredit - $totalDebit;
                $totalPendapatan += $saldoUtama;
            } else {
                $saldoUtama = $totalDebit - $totalKredit;
                $totalBeban += $saldoUtama;
            }

            $perDepartemen = [];
            foreach ($filteredDepartemens as $departemen) {
                $departemenEntries = JournalEntryDetail::where('kode_akun', $account->kode_akun)
                    ->whereHas('departemenAkun', function ($q) use ($departemen) {
                        $q->where('departemen_id', $departemen->id);
                    })
                    ->whereHas('journalEntry', function ($q) use ($tanggalAwal, $tanggalAkhir) {
                        $q->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
                          ->where('source', '!=', 'START NEW YEAR'); // Exclude jurnal penutup
                    })
                    ->selectRaw('SUM(debits) as total_debit, SUM(credits) as total_kredit')
                    ->first();

                // Perhitungan NET per departemen
                if (strtolower($account->tipe_akun) === 'pendapatan') {
                    $nilai = ($departemenEntries->total_kredit ?? 0) - ($departemenEntries->total_debit ?? 0);
                } else {
                    $nilai = ($departemenEntries->total_debit ?? 0) - ($departemenEntries->total_kredit ?? 0);
                }

                $perDepartemen[$departemen->id] = $nilai;
            }

            if ($saldoUtama != 0) {
                $incomeStatement[] = [
                    'kode_akun'      => $account->kode_akun,
                    'nama_akun'      => $account->nama_akun,
                    'tipe_akun'      => $account->tipe_akun,
                    'saldo'          => $saldoUtama,
                    'per_departemen' => $perDepartemen,
                ];
            }
        }

        $labaBersih = $totalPendapatan - $totalBeban;

        return view('income_statement.income_statement_departement', [
            'incomeStatement'    => $incomeStatement,
            'totalPendapatan'    => $totalPendapatan,
            'totalBeban'         => $totalBeban,
            'labaBersih'         => $labaBersih,
            'start_date'         => $tanggalAwal,
            'end_date'           => $tanggalAkhir,
            'allDepartemens'     => $allDepartemens,     // untuk header (selalu semua)
            'filteredDepartemens' => $filteredDepartemens // untuk body (tergantung pilihan)
        ]);
    }



    public function export(Request $request)
    {
        $format       = $request->get('format', 'excel');
        $tanggalAwal  = $request->get('start_date') ?: now()->startOfMonth()->toDateString();
        $tanggalAkhir = $request->get('end_date')   ?: now()->toDateString();

        $periodeFormatted = \Carbon\Carbon::parse($tanggalAwal)->translatedFormat('d M Y')
            . '_sampai_' . \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d M Y');

        if ($format === 'excel') {
            $fileName = "income_statement_{$periodeFormatted}.xlsx";
            return Excel::download(new IncomeStatementExport($tanggalAwal, $tanggalAkhir), $fileName);
        }

        if ($format === 'pdf') {
            $incomeData = $this->buildIncomeStatementHierarchy($tanggalAwal, $tanggalAkhir);

            $fileName = "income_statement_{$periodeFormatted}.pdf";
            $pdf = Pdf::loadView('income_statement.pdf', [
                'groupsPendapatan' => $incomeData['groupsPendapatan'],
                'groupsBeban'      => $incomeData['groupsBeban'],
                'totalPendapatan'  => $incomeData['totalPendapatan'],
                'totalBeban'       => $incomeData['totalBeban'],
                'labaSebelumPajak' => $incomeData['labaSebelumPajak'],
                'bebanPajak'       => $incomeData['bebanPajak'],
                'labaSetelahPajak' => $incomeData['labaSetelahPajak'],
                'start_date'       => $tanggalAwal,
                'end_date'         => $tanggalAkhir,
            ])->setPaper('A4', 'portrait');

            return $pdf->download($fileName);
        }

        return back()->with('error', 'Format export tidak dikenali.');
    }

    private function buildIncomeStatementHierarchy(string $tanggalAwal, string $tanggalAkhir): array
    {
        // 🔹 Ambil mutasi per akun (EXCLUDE transaksi Start New Year)
        $entries = DB::table('journal_entry_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
            ->select(
                'jed.kode_akun',
                DB::raw('SUM(jed.debits) as total_debit'),
                DB::raw('SUM(jed.credits) as total_kredit')
            )
            ->whereBetween('je.tanggal', [$tanggalAwal, $tanggalAkhir])
            ->where('je.source', '!=', 'START NEW YEAR')
            ->groupBy('jed.kode_akun')
            ->get()
            ->keyBy('kode_akun');

        // 🔹 Ambil master akun
        $accounts = ChartOfAccount::whereIn('tipe_akun', ['Pendapatan', 'Beban'])
            ->orderBy('kode_akun')
            ->get();

        $groups = [];
        $currentGroup = null;
        $currentAccount = null;

        foreach ($accounts as $account) {
            if ($account->level_akun === 'GROUP ACCOUNT') {
                if ($currentAccount) {
                    if (!empty($currentAccount['sub_accounts'])) {
                        $currentAccount['saldo_account'] = array_sum(array_column($currentAccount['sub_accounts'], 'saldo'));
                    }
                    if ($currentAccount['saldo_account'] != 0 && $currentGroup) {
                        $currentGroup['accounts'][] = $currentAccount;
                    }
                }
                $currentAccount = null;
                
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

            if ($account->level_akun === 'HEADER') {
                continue;
            }

            if ($account->level_akun === 'ACCOUNT') {
                if ($currentAccount) {
                    if (!empty($currentAccount['sub_accounts'])) {
                        $currentAccount['saldo_account'] = array_sum(array_column($currentAccount['sub_accounts'], 'saldo'));
                    }
                    if ($currentAccount['saldo_account'] != 0 && $currentGroup) {
                        $currentGroup['accounts'][] = $currentAccount;
                    }
                }

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

            if ($account->level_akun === 'SUB ACCOUNT') {
                if (!$currentGroup) {
                    $currentGroup = [
                        'group'       => '',
                        'tipe'        => strtolower($account->tipe_akun),
                        'accounts'    => [],
                        'saldo_group' => 0,
                    ];
                }

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

        if ($currentAccount) {
            if (!empty($currentAccount['sub_accounts'])) {
                $currentAccount['saldo_account'] = array_sum(array_column($currentAccount['sub_accounts'], 'saldo'));
            }
            if ($currentAccount['saldo_account'] != 0 && $currentGroup) {
                $currentGroup['accounts'][] = $currentAccount;
            }
        }

        if ($currentGroup && !empty($currentGroup['accounts'])) {
            $currentGroup['saldo_group'] = array_sum(array_column($currentGroup['accounts'], 'saldo_account'));
            $groups[] = $currentGroup;
        }

        $groupsPendapatan = array_values(array_filter($groups, fn($g) => $g['tipe'] === 'pendapatan'));
        $groupsBeban      = array_values(array_filter($groups, fn($g) => $g['tipe'] === 'beban'));

        $totalPendapatan = array_sum(array_column($groupsPendapatan, 'saldo_group'));
        $totalBeban      = array_sum(array_column($groupsBeban, 'saldo_group'));

        $labaSebelumPajak = $totalPendapatan - $totalBeban;

        $akunPajak  = ChartOfAccount::where('is_income_tax', 1)->first();
        $bebanPajak = 0;

        if ($akunPajak) {
            $entryPajak = $entries->get($akunPajak->kode_akun);
            if ($entryPajak) {
                $bebanPajak = ($entryPajak->total_debit ?? 0) - ($entryPajak->total_kredit ?? 0);
            }
        }

        $labaSetelahPajak = $labaSebelumPajak - $bebanPajak;

        return [
            'groupsPendapatan' => $groupsPendapatan,
            'groupsBeban'      => $groupsBeban,
            'totalPendapatan'  => $totalPendapatan,
            'totalBeban'       => $totalBeban,
            'labaSebelumPajak' => $labaSebelumPajak,
            'bebanPajak'       => $bebanPajak,
            'labaSetelahPajak' => $labaSetelahPajak,
        ];
    }


    public function exportDepartemen(Request $request)
    {
        $format      = $request->get('format', 'excel');
        $start_date  = $request->get('start_date') ?: now()->startOfMonth()->toDateString();
        $end_date    = $request->get('end_date')   ?: now()->toDateString();

        $periodeFormatted = \Carbon\Carbon::parse($start_date)->translatedFormat('d M Y')
            . '_sampai_' . \Carbon\Carbon::parse($end_date)->translatedFormat('d M Y');

        if ($format === 'excel') {
            $fileName = "income_statement_departement_{$periodeFormatted}.xlsx";
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\IncomeStatementDepartementExport($start_date, $end_date),
                $fileName
            );
        }

        if ($format === 'pdf') {
            $export = new \App\Exports\IncomeStatementDepartementExport($start_date, $end_date);
            $view   = $export->view()->render(); // 🔹 gunakan view dari export

            $fileName = "income_statement_departement_{$periodeFormatted}.pdf";
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($view)->setPaper('A4', 'landscape');

            return $pdf->download($fileName);
        }

        return back()->with('error', 'Format export tidak dikenali.');
    }

    public function exportDepartementPdf(Request $request)
    {
        $start_date  = $request->start_date ?: now()->startOfMonth()->toDateString();
        $end_date    = $request->end_date   ?: now()->toDateString();

        // gunakan export class yang tadi dibuat
        $export = new \App\Exports\IncomeStatementDepartementExport($start_date, $end_date);
        $view   = $export->view()->render();

        $fileName = "income_statement_departement_{$start_date}_to_{$end_date}.pdf";

        $pdf = Pdf::loadHTML($view)->setPaper('A4', 'landscape');
        return $pdf->download($fileName);
    }
}

<?php

namespace App\Exports;

use App\ChartOfAccount;
use App\Departement;
use App\JournalEntryDetail;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class IncomeStatementDepartementExport implements FromView
{
    protected $start_date;
    protected $end_date;

    public function __construct($start_date, $end_date)
    {
        $this->start_date  = $start_date;
        $this->end_date    = $end_date;
    }

    public function view(): View
    {
        // 🔹 Ambil semua departemen
        $departemens = Departement::select('id', 'deskripsi')->orderBy('id')->get();

        // 🔹 Ambil saldo per akun × per departemen
        $entriesDept = JournalEntryDetail::select(
            'journal_entry_details.kode_akun',
            'departemen_akuns.departemen_id',
            DB::raw('SUM(journal_entry_details.debits) as total_debit'),
            DB::raw('SUM(journal_entry_details.credits) as total_kredit')
        )
            ->join('journal_entries', 'journal_entry_details.journal_entry_id', '=', 'journal_entries.id')
            ->join('departemen_akuns', 'journal_entry_details.departemen_akun_id', '=', 'departemen_akuns.id')
            ->whereBetween('journal_entries.tanggal', [$this->start_date, $this->end_date])
            ->groupBy('journal_entry_details.kode_akun', 'departemen_akuns.departemen_id')
            ->get()
            ->groupBy('kode_akun');

        // 🔹 Ambil total saldo per akun (tanpa departemen)
        $entriesTotal = JournalEntryDetail::select(
            'journal_entry_details.kode_akun',
            DB::raw('SUM(journal_entry_details.debits) as total_debit'),
            DB::raw('SUM(journal_entry_details.credits) as total_kredit')
        )
            ->join('journal_entries', 'journal_entry_details.journal_entry_id', '=', 'journal_entries.id')
            ->whereBetween('journal_entries.tanggal', [$this->start_date, $this->end_date])
            ->groupBy('journal_entry_details.kode_akun')
            ->get()
            ->keyBy('kode_akun');

        // 🔹 Ambil akun pendapatan & beban
        $accounts = ChartOfAccount::whereIn('tipe_akun', ['Pendapatan', 'Beban'])
            ->orderBy('kode_akun')
            ->get();

        $incomeStatement = [];
        $totalPendapatan = 0;
        $totalBeban      = 0;

        foreach ($accounts as $account) {
            $tipe = strtolower($account->tipe_akun);

            // Total saldo akun (pakai normal balance, bukan bruto)
            $entryTotal   = $entriesTotal->get($account->kode_akun);
            $totalDebit   = $entryTotal->total_debit ?? 0;
            $totalKredit  = $entryTotal->total_kredit ?? 0;

            $saldoUtama   = ($tipe === 'pendapatan')
                ? ($totalKredit - $totalDebit)   // normal kredit
                : ($totalDebit - $totalKredit);  // normal debit

            if ($saldoUtama == 0) {
                continue;
            }

            if ($tipe === 'pendapatan') {
                $totalPendapatan += $saldoUtama;
            } else {
                $totalBeban += $saldoUtama;
            }

            // Breakdown per departemen (pakai normal balance juga)
            $perDepartemen = [];
            $entriesPerDept = $entriesDept->get($account->kode_akun) ?? collect();

            foreach ($departemens as $dept) {
                $row   = $entriesPerDept->firstWhere('departemen_id', $dept->id);
                $d     = $row->total_debit ?? 0;
                $c     = $row->total_kredit ?? 0;
                $nilai = ($tipe === 'pendapatan') ? ($c - $d) : ($d - $c);
                $perDepartemen[$dept->deskripsi] = $nilai;
            }

            $incomeStatement[] = [
                'kode_akun'      => $account->kode_akun,
                'nama_akun'      => $account->nama_akun,
                'tipe_akun'      => $account->tipe_akun,
                'saldo'          => $saldoUtama,
                'per_departemen' => $perDepartemen,
            ];
        }

        $labaBersih = $totalPendapatan - $totalBeban;

        return view('income_statement.excel_departement', [
            'incomeStatement' => $incomeStatement,
            'departemens'     => $departemens,
            'totalPendapatan' => $totalPendapatan,
            'totalBeban'      => $totalBeban,
            'labaBersih'      => $labaBersih,
            'start_date'      => $this->start_date,
            'end_date'        => $this->end_date,
        ]);
    }
}

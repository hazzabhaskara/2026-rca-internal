<?php

namespace App\Exports;

use App\ChartOfAccount;
use App\JournalEntryDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class NeracaExport implements FromView
{
    protected $tanggalAkhir;

    public function __construct($tanggalAkhir)
    {
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function view(): View
    {
        // ambil data sama seperti di neracaReport()
        $accounts = ChartOfAccount::whereIn('tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas'])
            ->where('level_akun', '!=', 'X')
            ->get();

        $neraca = [];

        foreach ($accounts as $akun) {
            $saldo = JournalEntryDetail::where('kode_akun', $akun->kode_akun)
                ->whereHas('journalEntry', function ($q) {
                    if ($this->tanggalAkhir) {
                        $q->where('tanggal', '<=', $this->tanggalAkhir);
                    }
                })
                ->selectRaw('SUM(debits) as total_debit, SUM(credits) as total_credit')
                ->first();

            $totalDebit = $saldo->total_debit ?? 0;
            $totalCredit = $saldo->total_credit ?? 0;

            if ($akun->tipe_akun === 'Aset') {
                $endingBalance = $totalDebit - $totalCredit;
            } else {
                $endingBalance = $totalCredit - $totalDebit;
            }

            $neraca[$akun->tipe_akun][] = [
                'kode_akun'  => $akun->kode_akun,
                'nama_akun'  => $akun->nama_akun,
                'level_akun' => $akun->level_akun,
                'saldo'      => $endingBalance,
            ];
        }

        $grandTotalAset = collect($neraca['Aset'] ?? [])->where('level_akun', 'ACCOUNT')->sum('saldo');
        $grandTotalKewajiban = collect($neraca['Kewajiban'] ?? [])->where('level_akun', 'ACCOUNT')->sum('saldo');
        $grandTotalEkuitas = collect($neraca['Ekuitas'] ?? [])->where('level_akun', 'ACCOUNT')->sum('saldo');

        // hitung laba tahun berjalan (sama dengan controller: tahun berjalan & exclude START NEW YEAR)
        $tanggalAwal = Carbon::parse($this->tanggalAkhir)->startOfYear()->toDateString();

        $kodePendapatan = ChartOfAccount::where('tipe_akun', 'Pendapatan')->pluck('kode_akun');
        $kodeBeban      = ChartOfAccount::where('tipe_akun', 'Beban')->where('is_income_tax', '!=', 1)->pluck('kode_akun');
        $kodePajak      = ChartOfAccount::where('is_income_tax', 1)->pluck('kode_akun');

        $rowPendapatan = DB::table('journal_entry_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
            ->whereIn('jed.kode_akun', $kodePendapatan)
            ->whereBetween('je.tanggal', [$tanggalAwal, $this->tanggalAkhir])
            ->where('je.source', '!=', 'START NEW YEAR')
            ->selectRaw('SUM(jed.credits) - SUM(jed.debits) as saldo')
            ->first();
        $totalPendapatan = (float) ($rowPendapatan->saldo ?? 0);

        $rowBeban = DB::table('journal_entry_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
            ->whereIn('jed.kode_akun', $kodeBeban)
            ->whereBetween('je.tanggal', [$tanggalAwal, $this->tanggalAkhir])
            ->where('je.source', '!=', 'START NEW YEAR')
            ->selectRaw('SUM(jed.debits) - SUM(jed.credits) as saldo')
            ->first();
        $totalBeban = (float) ($rowBeban->saldo ?? 0);

        $totalPajak = 0;
        if ($kodePajak->isNotEmpty()) {
            $rowPajak = DB::table('journal_entry_details as jed')
                ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
                ->whereIn('jed.kode_akun', $kodePajak)
                ->whereBetween('je.tanggal', [$tanggalAwal, $this->tanggalAkhir])
                ->where('je.source', '!=', 'START NEW YEAR')
                ->selectRaw('SUM(jed.debits) - SUM(jed.credits) as saldo')
                ->first();
            $totalPajak = (float) ($rowPajak->saldo ?? 0);
        }

        $labaSebelumPajak = $totalPendapatan - $totalBeban;
        $labaTahunBerjalan = $labaSebelumPajak - $totalPajak;

        $akunLaba = ChartOfAccount::where('level_akun', 'X')->first();
        if ($akunLaba) {
            $neraca['Ekuitas'][] = [
                'kode_akun'  => $akunLaba->kode_akun,
                'nama_akun'  => $akunLaba->nama_akun,
                'level_akun' => $akunLaba->level_akun,
                'saldo'      => $labaTahunBerjalan,
            ];
            $grandTotalEkuitas += $labaTahunBerjalan;
        }

        // Hitung total untuk GROUP ACCOUNT dan assign Grand Total ke HEADER
        foreach ($neraca as $tipe => &$items) {
            $grandForTipe = $tipe === 'Aset' ? $grandTotalAset : ($tipe === 'Kewajiban' ? $grandTotalKewajiban : $grandTotalEkuitas);

            $groupAggByPrefix = [];
            foreach ($items as $it) {
                if ($it['level_akun'] === 'ACCOUNT') {
                    $p2 = substr((string)$it['kode_akun'], 0, 2);
                    $groupAggByPrefix[$p2] = ($groupAggByPrefix[$p2] ?? 0) + ($it['saldo'] ?? 0);
                }
            }

            foreach ($items as &$it) {
                if ($it['level_akun'] === 'HEADER') {
                    $it['saldo'] = $grandForTipe;
                } elseif ($it['level_akun'] === 'GROUP ACCOUNT') {
                    $p2 = substr((string)$it['kode_akun'], 0, 2);
                    $it['saldo'] = ($it['saldo'] ?? 0) + ($groupAggByPrefix[$p2] ?? 0);
                }
            }
        }
        unset($items, $it);

        return view('neraca.neraca_excel', [
            'neraca' => $neraca,
            'grandTotalAset' => $grandTotalAset,
            'grandTotalKewajiban' => $grandTotalKewajiban,
            'grandTotalEkuitas' => $grandTotalEkuitas,
            'labaSebelumPajak' => $labaSebelumPajak,
            'totalPajak' => $totalPajak,
            'labaTahunBerjalan' => $labaTahunBerjalan,
            'tanggalAkhir' => $this->tanggalAkhir,
        ]);
    }
}

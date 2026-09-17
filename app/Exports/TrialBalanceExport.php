<?php

namespace App\Exports;

use App\ChartOfAccount;
use App\JournalEntryDetail;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class TrialBalanceExport implements FromView
{
    protected $tanggalAkhir;

    public function __construct($tanggalAkhir)
    {
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function view(): View
    {
        // Ambil total debit & kredit per akun sampai tanggal akhir
        $entries = JournalEntryDetail::select(
            'kode_akun',
            DB::raw('SUM(debits) as total_debit'),
            DB::raw('SUM(credits) as total_kredit')
        )
            ->whereHas('journalEntry', function ($q) {
                $q->where('tanggal', '<=', $this->tanggalAkhir);
            })
            ->groupBy('kode_akun')
            ->get()
            ->keyBy('kode_akun');

        // Ambil daftar akun
        $accounts = ChartOfAccount::orderBy('kode_akun')->get();

        $trialBalances = [];

        foreach ($accounts as $account) {
            $entry = $entries->get($account->kode_akun);

            $totalDebit = $entry->total_debit ?? 0;
            $totalKredit = $entry->total_kredit ?? 0;

            $tipe = strtolower($account->tipe_akun);

            $saldo_debit = 0;
            $saldo_kredit = 0;

            if (in_array($tipe, ['kewajiban', 'ekuitas', 'pendapatan'])) {
                // saldo normal kredit
                $saldo = $totalKredit - $totalDebit;
                if ($saldo > 0) {
                    $saldo_kredit = $saldo;
                } else {
                    $saldo_debit = abs($saldo);
                }
            } else {
                // saldo normal debit
                $saldo = $totalDebit - $totalKredit;
                if ($saldo > 0) {
                    $saldo_debit = $saldo;
                } else {
                    $saldo_kredit = abs($saldo);
                }
            }

            if ($saldo_debit != 0 || $saldo_kredit != 0) {
                $trialBalances[] = [
                    'kode_akun'    => $account->kode_akun,
                    'nama_akun'    => $account->nama_akun,
                    'tipe_akun'    => $account->tipe_akun,
                    'saldo_debit'  => $saldo_debit,
                    'saldo_kredit' => $saldo_kredit,
                ];
            }
        }

        return view('trial_balance.excel', [
            'trialBalances' => $trialBalances,
            'end_date'      => $this->tanggalAkhir,
        ]);
    }
}

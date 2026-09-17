<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\Exports\TrialBalanceExport;
use App\JournalEntryDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TrialBalanceController extends Controller
{
    //
    public function trialBalanceFilter()
    {
        return view('trial_balance.filter_trial_balance');
    }
    public function trialBalanceReport(Request $request)
    {
        $tanggalAkhir = $request->end_date;

        // Ambil semua akun dari chart of accounts
        $accounts = chartOfAccount::all();

        $trialBalances = [];

        foreach ($accounts as $account) {
            // Ambil semua jurnal untuk akun ini sampai tanggal akhir
            $entries = JournalEntryDetail::with('journalEntry')
                ->where('kode_akun', $account->kode_akun)
                ->whereHas('journalEntry', function ($q) use ($tanggalAkhir) {
                    $q->where('tanggal', '<=', $tanggalAkhir);
                })
                ->orderBy('kode_akun')
                ->get();

            $totalDebit = $entries->sum('debits');
            $totalKredit = $entries->sum('credits');

            $tipe = strtolower($account->tipe_akun);

            $saldo_debit = 0;
            $saldo_kredit = 0;

            if (in_array($tipe, ['kewajiban', 'Ekuitas', 'Pendapatan'])) {
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

            // Masukkan ke hasil jika saldo ada (debit/kredit)
            if ($saldo_debit != 0 || $saldo_kredit != 0) {
                $trialBalances[] = [
                    'kode_akun' => $account->kode_akun,
                    'nama_akun' => $account->nama_akun,
                    'tipe_akun' => $account->tipe_akun,
                    'saldo_debit' => $saldo_debit,
                    'saldo_kredit' => $saldo_kredit,
                ];
            }
        }

        return view('trial_balance.trial_balance_report', [
            'trialBalances' => $trialBalances,
            'tanggalAkhir' => $tanggalAkhir,
        ]);
    }
    public function export(Request $request)
    {
        $format       = $request->get('format', 'excel');
        $tanggalAkhir = $request->get('end_date') ?: now()->toDateString();

        // Nama file yang enak dibaca
        $tanggalFormatted = Carbon::parse($tanggalAkhir)->translatedFormat('d M Y');

        if ($format === 'excel') {
            $fileName = "trial_balance_{$tanggalFormatted}.xlsx";
            return Excel::download(new TrialBalanceExport($tanggalAkhir), $fileName);
        }

        if ($format === 'pdf') {
            // Bangun data trial balance (supaya view PDF punya semua variabel yang dibutuhkan)
            $trialBalances = $this->buildTrialBalances($tanggalAkhir);

            $fileName = "trial_balance_{$tanggalFormatted}.pdf";
            $pdf = Pdf::loadView('trial_balance.pdf', [
                'trialBalances' => $trialBalances,
                'end_date'      => $tanggalAkhir, // <<< KIRIMKAN VARIABEL INI
            ])->setPaper('A4', 'landscape');

            return $pdf->download($fileName);
        }

        return back()->with('error', 'Format export tidak dikenali.');
    }

    /**
     * Kumpulkan saldo per akun sampai tanggal akhir (efisien: 1 query + loop).
     */
    private function buildTrialBalances(string $endDate): array
    {
        // Sum debit & kredit per akun sampai endDate
        $entries = JournalEntryDetail::select(
            'kode_akun',
            DB::raw('SUM(debits) as total_debit'),
            DB::raw('SUM(credits) as total_kredit')
        )
            ->whereHas('journalEntry', function ($q) use ($endDate) {
                $q->where('tanggal', '<=', $endDate);
            })
            ->groupBy('kode_akun')
            ->get()
            ->keyBy('kode_akun');

        $accounts = ChartOfAccount::orderBy('kode_akun')->get();

        $trialBalances = [];
        foreach ($accounts as $account) {
            $totalDebit  = (float) ($entries[$account->kode_akun]->total_debit  ?? 0);
            $totalKredit = (float) ($entries[$account->kode_akun]->total_kredit ?? 0);

            $tipe = strtolower($account->tipe_akun);

            $saldo_debit = 0.0;
            $saldo_kredit = 0.0;

            // Asumsi normal balance:
            // - kredit: kewajiban, ekuitas, pendapatan
            // - debit: selain itu (aset, beban, dll.)
            if (in_array($tipe, ['kewajiban', 'ekuitas', 'pendapatan'])) {
                $saldo = $totalKredit - $totalDebit; // normal kredit
                if ($saldo > 0) $saldo_kredit = $saldo;
                else $saldo_debit = abs($saldo);
            } else {
                $saldo = $totalDebit - $totalKredit;  // normal debit
                if ($saldo > 0) $saldo_debit = $saldo;
                else $saldo_kredit = abs($saldo);
            }

            if ($saldo_debit != 0.0 || $saldo_kredit != 0.0) {
                $trialBalances[] = [
                    'kode_akun'    => $account->kode_akun,
                    'nama_akun'    => $account->nama_akun,
                    'tipe_akun'    => $account->tipe_akun,
                    'saldo_debit'  => $saldo_debit,
                    'saldo_kredit' => $saldo_kredit,
                ];
            }
        }

        return $trialBalances;
    }
}

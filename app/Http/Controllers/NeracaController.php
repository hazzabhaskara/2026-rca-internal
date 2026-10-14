<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\Exports\NeracaExport;
use App\JournalEntryDetail;
use App\Setting;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NeracaController extends Controller
{
    //
    public function neracaFilter()
    {
        return view('neraca.filter_neraca');
    }
    public function neracaReport(Request $request)
    {
        $tanggalAkhir = $request->end_date;
        $siteTitle = Setting::where('key', 'site_title')->value('value');
        $siteLogo  = Setting::where('key', 'logo')->value('value');

        $showAccountNumber   = $request->has('show_account_number');
        $hideAccountWithZero = $request->has('hide_account_with_zero');

        // Ambil semua akun yang relevan untuk Balance Sheet
        $accounts = ChartOfAccount::whereIn('tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas'])
            ->orderBy('kode_akun')
            ->get();

        // 1) Hitung saldo_self untuk semua akun sekali saja (hindari N+1 di child loop)
        $saldoSelfByKode = [];
        foreach ($accounts as $akun) {
            $saldoSelfByKode[$akun->kode_akun] = $this->getSaldo($akun, $tanggalAkhir);
        }

        // 2) Bangun struktur neraca per tipe
        $neraca = ['Aset' => [], 'Kewajiban' => [], 'Ekuitas' => []];

        foreach ($accounts as $akun) {
            // Lewatkan level 'X' dulu (ditangani terpisah di bawah)
            if ($akun->level_akun === 'X') {
                continue;
            }

            $saldoSelf = $saldoSelfByKode[$akun->kode_akun] ?? 0;
            $record = [
                'kode_akun'   => $akun->kode_akun,
                'nama_akun'   => $akun->nama_akun,
                'level_akun'  => $akun->level_akun,
                'saldo_self'  => $saldoSelf,  // simpan saldo murni
                'saldo'       => $saldoSelf,  // default tampil = self; untuk ACCOUNT akan ditimpa saldo_agg
            ];

            // Untuk level ACCOUNT, hitung agregasi anak-anak SUB ACCOUNT
            if ($akun->level_akun === 'ACCOUNT') {
                $prefix = substr((string) $akun->kode_akun, 0, 4);

                // Ambil semua SUB ACCOUNT yang prefiks kodenya match
                $childSaldo = 0;
                foreach ($accounts as $a) {
                    if (
                        $a->level_akun === 'SUB ACCOUNT' &&
                        \Illuminate\Support\Str::startsWith((string) $a->kode_akun, $prefix)
                    ) {
                        $childSaldo += $saldoSelfByKode[$a->kode_akun] ?? 0;
                    }
                }

                $saldoAgg = $saldoSelf + $childSaldo;
                $record['saldo']      = $saldoAgg; // angka yang akan ditampilkan untuk parent
                $record['saldo_agg']  = $saldoAgg; // simpan juga untuk total
            }

            // Hide-zero:
            // - ACCOUNT: evaluasi saldo agregat (saldo)
            // - SUB ACCOUNT: evaluasi saldo_self
            $isZeroForHide =
                ($akun->level_akun === 'ACCOUNT' && ($record['saldo'] ?? 0) == 0) ||
                ($akun->level_akun === 'SUB ACCOUNT' && ($record['saldo_self'] ?? 0) == 0);

            if ($hideAccountWithZero && $isZeroForHide) {
                // Untuk struktur HEADER/GROUP kita tetap tampilkan agar hierarchy tidak hilang.
                // Untuk ACCOUNT & SUB ACCOUNT nol: skip.
                if (in_array($akun->level_akun, ['ACCOUNT', 'SUB ACCOUNT'])) {
                    continue;
                }
            }

            $neraca[$akun->tipe_akun][] = $record;
        }

        // 3) Grand Total per tipe dari parent (ACCOUNT) saja
        $grandTotalAset      = collect($neraca['Aset'])
            ->where('level_akun', 'ACCOUNT')
            ->sum(fn($r) => $r['saldo'] ?? 0);

        $grandTotalKewajiban = collect($neraca['Kewajiban'])
            ->where('level_akun', 'ACCOUNT')
            ->sum(fn($r) => $r['saldo'] ?? 0);

        $grandTotalEkuitas   = collect($neraca['Ekuitas'])
            ->where('level_akun', 'ACCOUNT')
            ->sum(fn($r) => $r['saldo'] ?? 0);

        // 4) Laba Tahun Berjalan (pos 'X') -> tambahkan ke neraca Ekuitas & grand total ekuitas
        $tanggalAwalTahun = Carbon::parse($tanggalAkhir)->startOfYear()->toDateString();
        $totalPendapatan = $this->getTotalByTipe('Pendapatan', $tanggalAkhir, 'credit', true, $tanggalAwalTahun);
        $totalBeban      = $this->getTotalByTipe('Beban',      $tanggalAkhir, 'debit',  true, $tanggalAwalTahun);
        $totalPajak      = $this->getTotalByPajak($tanggalAkhir, $tanggalAwalTahun);

        $labaSebelumPajak  = $totalPendapatan - $totalBeban;
        $labaTahunBerjalan = $labaSebelumPajak - $totalPajak;

        $akunLaba = ChartOfAccount::where('level_akun', 'X')->first();
        if ($akunLaba) {
            // hide-zero untuk laba berjalan kalau diminta
            if (!($hideAccountWithZero && $labaTahunBerjalan == 0)) {
                $neraca['Ekuitas'][] = [
                    'kode_akun'   => $akunLaba->kode_akun,
                    'nama_akun'   => $akunLaba->nama_akun,
                    'level_akun'  => 'X',
                    'saldo_self'  => $labaTahunBerjalan,
                    'saldo'       => $labaTahunBerjalan,
                ];
                $grandTotalEkuitas += $labaTahunBerjalan;
            }
        }

        // 5) Hitung total untuk GROUP ACCOUNT dan assign Grand Total ke HEADER
        foreach ($neraca as $tipe => $items) {
            $grandForTipe = $tipe === 'Aset' ? $grandTotalAset : ($tipe === 'Kewajiban' ? $grandTotalKewajiban : $grandTotalEkuitas);

            // Hitung sum ACCOUNT per prefix 2-digit untuk GROUP ACCOUNT
            $groupAggByPrefix = [];
            foreach ($items as $it) {
                if ($it['level_akun'] === 'ACCOUNT') {
                    $p2 = substr((string)$it['kode_akun'], 0, 2);
                    $groupAggByPrefix[$p2] = ($groupAggByPrefix[$p2] ?? 0) + ($it['saldo'] ?? 0);
                }
            }

            foreach ($neraca[$tipe] as $idx => $it) {
                if ($it['level_akun'] === 'HEADER') {
                    $neraca[$tipe][$idx]['saldo'] = $grandForTipe;
                    $neraca[$tipe][$idx]['saldo_agg'] = $grandForTipe;
                } elseif ($it['level_akun'] === 'GROUP ACCOUNT') {
                    $p2 = substr((string)$it['kode_akun'], 0, 2);
                    $grpVal = ($it['saldo_self'] ?? 0) + ($groupAggByPrefix[$p2] ?? 0);
                    $neraca[$tipe][$idx]['saldo'] = $grpVal;
                    $neraca[$tipe][$idx]['saldo_agg'] = $grpVal;
                }
            }
        }

        return view('neraca.neraca_report', compact(
            'neraca',
            'tanggalAkhir',
            'siteTitle',
            'siteLogo',
            'grandTotalAset',
            'grandTotalKewajiban',
            'grandTotalEkuitas',
            'labaSebelumPajak',
            'totalPajak',
            'labaTahunBerjalan',
            'showAccountNumber',
            'hideAccountWithZero'
        ));
    }


    /**
     * Hitung saldo per akun
     */
    private function getSaldo($akun, $tanggalAkhir)
    {
        $saldo = JournalEntryDetail::where('kode_akun', $akun->kode_akun)
            ->whereHas('journalEntry', function ($q) use ($tanggalAkhir) {
                if ($tanggalAkhir) {
                    $q->where('tanggal', '<=', $tanggalAkhir);
                }
            })
            ->selectRaw('SUM(debits) as total_debit, SUM(credits) as total_credit')
            ->first();

        $totalDebit  = $saldo->total_debit ?? 0;
        $totalCredit = $saldo->total_credit ?? 0;

        return $akun->tipe_akun === 'Aset'
            ? $totalDebit - $totalCredit   // normal debit
            : $totalCredit - $totalDebit;  // normal kredit
    }

    /**
     * Hitung total saldo per tipe akun untuk tahun berjalan (exclude START NEW YEAR)
     */
    private function getTotalByTipe($tipe, $tanggalAkhir, $normal = 'credit', $excludePajak = true, $tanggalAwal = null)
    {
        $query = ChartOfAccount::where('tipe_akun', $tipe);
        if ($excludePajak) {
            $query->where('is_income_tax', '!=', 1);
        }
        $kodeAkuns = $query->pluck('kode_akun');

        if ($kodeAkuns->isEmpty()) {
            return 0;
        }

        $queryJed = DB::table('journal_entry_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
            ->whereIn('jed.kode_akun', $kodeAkuns)
            ->where('je.source', '!=', 'START NEW YEAR');

        if ($tanggalAwal && $tanggalAkhir) {
            $queryJed->whereBetween('je.tanggal', [$tanggalAwal, $tanggalAkhir]);
        } elseif ($tanggalAkhir) {
            $queryJed->where('je.tanggal', '<=', $tanggalAkhir);
        }

        $row = $queryJed->selectRaw('SUM(jed.debits) as total_debit, SUM(jed.credits) as total_credit')->first();
        $totalDebit  = (float) ($row->total_debit ?? 0);
        $totalCredit = (float) ($row->total_credit ?? 0);

        return $normal === 'credit'
            ? ($totalCredit - $totalDebit)
            : ($totalDebit - $totalCredit);
    }

    /**
     * Hitung total pajak (is_income_tax = 1) untuk tahun berjalan (exclude START NEW YEAR)
     */
    private function getTotalByPajak($tanggalAkhir, $tanggalAwal = null)
    {
        $kodeAkuns = ChartOfAccount::where('is_income_tax', 1)->pluck('kode_akun');
        if ($kodeAkuns->isEmpty()) {
            return 0;
        }

        $queryJed = DB::table('journal_entry_details as jed')
            ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
            ->whereIn('jed.kode_akun', $kodeAkuns)
            ->where('je.source', '!=', 'START NEW YEAR');

        if ($tanggalAwal && $tanggalAkhir) {
            $queryJed->whereBetween('je.tanggal', [$tanggalAwal, $tanggalAkhir]);
        } elseif ($tanggalAkhir) {
            $queryJed->where('je.tanggal', '<=', $tanggalAkhir);
        }

        $row = $queryJed->selectRaw('SUM(jed.debits) as total_debit, SUM(jed.credits) as total_credit')->first();
        return (float) (($row->total_debit ?? 0) - ($row->total_credit ?? 0));
    }


    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');
        $tanggalAkhir = $request->get('end_date');
        $tanggalFormatted = Carbon::parse($tanggalAkhir)->translatedFormat('d M Y');

        if ($format === 'excel') {
            $fileName = "neraca_{$tanggalFormatted}.xlsx";
            return Excel::download(new NeracaExport($tanggalAkhir), $fileName);
        } elseif ($format === 'pdf') {
            // --- copy paste logika NeracaExport ---
            $accounts = ChartOfAccount::whereIn('tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas'])
                ->where('level_akun', '!=', 'X')
                ->get();

            $neraca = [];

            foreach ($accounts as $akun) {
                $saldo = JournalEntryDetail::where('kode_akun', $akun->kode_akun)
                    ->whereHas('journalEntry', function ($q) use ($tanggalAkhir) {
                        if ($tanggalAkhir) {
                            $q->where('tanggal', '<=', $tanggalAkhir);
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

            // hitung laba tahun berjalan
            $tanggalAwalTahun = Carbon::parse($tanggalAkhir)->startOfYear()->toDateString();
            $totalPendapatan = $this->getTotalByTipe('Pendapatan', $tanggalAkhir, 'credit', true, $tanggalAwalTahun);
            $totalBeban      = $this->getTotalByTipe('Beban',      $tanggalAkhir, 'debit',  true, $tanggalAwalTahun);
            $totalPajak      = $this->getTotalByPajak($tanggalAkhir, $tanggalAwalTahun);

            $labaSebelumPajak  = $totalPendapatan - $totalBeban;
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
            foreach ($neraca as $tipe => $items) {
                $grandForTipe = $tipe === 'Aset' ? $grandTotalAset : ($tipe === 'Kewajiban' ? $grandTotalKewajiban : $grandTotalEkuitas);

                $groupAggByPrefix = [];
                foreach ($items as $it) {
                    if ($it['level_akun'] === 'ACCOUNT') {
                        $p2 = substr((string)$it['kode_akun'], 0, 2);
                        $groupAggByPrefix[$p2] = ($groupAggByPrefix[$p2] ?? 0) + ($it['saldo'] ?? 0);
                    }
                }

                foreach ($neraca[$tipe] as $idx => $it) {
                    if ($it['level_akun'] === 'HEADER') {
                        $neraca[$tipe][$idx]['saldo'] = $grandForTipe;
                    } elseif ($it['level_akun'] === 'GROUP ACCOUNT') {
                        $p2 = substr((string)$it['kode_akun'], 0, 2);
                        $neraca[$tipe][$idx]['saldo'] = ($it['saldo'] ?? 0) + ($groupAggByPrefix[$p2] ?? 0);
                    }
                }
            }

            // --- end copy paste logika ---

            $pdf = PDF::loadView('neraca.export_pdf', compact(
                'neraca',
                'grandTotalAset',
                'grandTotalKewajiban',
                'grandTotalEkuitas',
                'labaSebelumPajak',
                'totalPajak',
                'labaTahunBerjalan',
                'tanggalAkhir'
            ))->setPaper('A4', 'portrait');

            return $pdf->download("neraca_{$tanggalFormatted}.pdf");
        }

        return back()->with('error', 'Format export tidak dikenali.');
    }



    /**
     * Kumpulkan data neraca biar bisa dipakai di report & export
     */
    private function buildNeracaData($tanggalAkhir, Request $request)
    {
        $siteTitle = Setting::where('key', 'site_title')->value('value');
        $siteLogo  = Setting::where('key', 'logo')->value('value');
        $showAccountNumber   = $request->has('show_account_number');
        $hideAccountWithZero = $request->has('hide_account_with_zero');

        // 👉 copy logika dari neracaReport (perhitungan saldo, neraca, grand total, laba, dsb.)
        // hasil akhirnya harus ada variabel $neraca, $grandTotalAset, $grandTotalKewajiban, $grandTotalEkuitas, dll.

        return compact(
            'neraca',
            'tanggalAkhir',
            'siteTitle',
            'siteLogo',
            'grandTotalAset',
            'grandTotalKewajiban',
            'grandTotalEkuitas',
            'labaSebelumPajak',
            'totalPajak',
            'labaTahunBerjalan',
            'showAccountNumber',
            'hideAccountWithZero'
        );
    }
}

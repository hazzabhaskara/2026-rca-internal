<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\JournalEntryDetail;
use App\ChartOfAccount;

class FiscalController extends Controller
{
    public function fiscal()
    {
        $details = JournalEntryDetail::with('chartOfAccount')
            ->whereHas('chartOfAccount', function ($q) {
                $q->whereIn('tipe_akun', ['Pendapatan', 'Beban']);
            })
            ->select(
                'id',
                'kode_akun',
                'debits',
                'credits',
                'penyesuaian_fiskal',
                'kode_fiscal',
                'comment'
            )
            ->orderBy('kode_akun')
            ->get();
        // Group by kode_akun
        $grouped = $details->groupBy('kode_akun');

        $report = $grouped->map(function ($items, $kodeAkun) {
            $namaAkun = $items->first()->chartOfAccount->nama_akun ?? '-';
            $kodeFiscal = $items->first()->kode_fiscal ?? null;

            // Pastikan selalu numeric, bukan null
            $nilaiKomersial = $items->map(function ($d) {
                return floatval($d->debits ?? 0) - floatval($d->credits ?? 0);
            })->sum();

            $nonTax = $items->filter(fn($d) => $d->penyesuaian_fiskal === 'non_tax')
                ->map(fn($d) => floatval($d->debits ?? 0) - floatval($d->credits ?? 0))
                ->sum();

            $pphFinal = $items->filter(fn($d) => $d->penyesuaian_fiskal === 'pph_final')
                ->map(fn($d) => floatval($d->debits ?? 0) - floatval($d->credits ?? 0))
                ->sum();

            $objekTidakFinal = $nilaiKomersial - $nonTax - $pphFinal;

            $koreksiPlus = $items->filter(fn($d) => $d->penyesuaian_fiskal === 'koreksi_plus')
                ->map(fn($d) => abs(floatval($d->debits ?? 0) - floatval($d->credits ?? 0)))
                ->sum();

            $koreksiMinus = $items->filter(fn($d) => $d->penyesuaian_fiskal === 'koreksi_minus')
                ->map(fn($d) => abs(floatval($d->debits ?? 0) - floatval($d->credits ?? 0)))
                ->sum();

            $nilaiFiscal = $objekTidakFinal + $koreksiPlus - $koreksiMinus;

            return [
                'kode_akun'        => $kodeAkun,
                'nama_akun'        => $namaAkun,
                'nilai_komersial'  => $nilaiKomersial,
                'non_tax'          => $nonTax,
                'pph_final'        => $pphFinal,
                'objek_tidak_final' => $objekTidakFinal,
                'koreksi_plus'     => $koreksiPlus,
                'koreksi_minus'    => $koreksiMinus,
                'kode_fiscal'      => $kodeFiscal,
                'nilai_fiscal'     => $nilaiFiscal,
            ];
        })->values();

        // Hitung total keseluruhan
        $total = [
            'nilai_komersial'   => $report->sum('nilai_komersial'),
            'non_tax'           => $report->sum('non_tax'),
            'pph_final'         => $report->sum('pph_final'),
            'objek_tidak_final' => $report->sum('objek_tidak_final'),
            'koreksi_plus'      => $report->sum('koreksi_plus'),
            'koreksi_minus'     => $report->sum('koreksi_minus'),
            'nilai_fiscal'      => $report->sum('nilai_fiscal'),
        ];


        return view('fiscal.fiscal_report', compact('report', 'total'));
    }
}

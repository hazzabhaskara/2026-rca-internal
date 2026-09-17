<?php

namespace App\Http\Controllers;

use App\JournalEntryDetail;
use App\KreditPajak;
use App\KreditPajakPph25;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PerhitunganPajakPenghasilanController extends Controller
{
    //
    public function index()
    {
        $details = JournalEntryDetail::with('chartOfAccount')->get();

        // Total nilai komersial (semua akun)
        $pendapatan = $details
            ->filter(fn($d) => $d->chartOfAccount->tipe_akun === 'Pendapatan')
            ->sum(fn($d) => floatval($d->credits ?? 0) - floatval($d->debits ?? 0));

        $beban = $details
            ->filter(fn($d) => $d->chartOfAccount->tipe_akun === 'Beban')
            ->sum(fn($d) => floatval($d->debits ?? 0) - floatval($d->credits ?? 0));

        $labaKomersial = $pendapatan - $beban;

        $pphFinal = $details->filter(fn($d) => $d->penyesuaian_fiskal === 'pph_final')
            ->map(fn($d) => ($d->credits ?? 0) - ($d->debits ?? 0))->sum();

        $tmop = $details->filter(fn($d) => $d->penyesuaian_fiskal === 'non_tax')
            ->map(fn($d) => ($d->credits ?? 0) - ($d->debits ?? 0))->sum();

        $koreksiPlus = $details->filter(fn($d) => $d->penyesuaian_fiskal === 'koreksi_plus')
            ->map(fn($d) => abs(($d->debits ?? 0) - ($d->credits ?? 0)))->sum();

        $koreksiMinus = $details->filter(fn($d) => $d->penyesuaian_fiskal === 'koreksi_minus')
            ->map(fn($d) => abs(($d->debits ?? 0) - ($d->credits ?? 0)))->sum();

        // PKP = Laba Komersial - PPh Final - TMOP + Koreksi Plus - Koreksi Minus
        $pkp = $labaKomersial - $pphFinal - $tmop + $koreksiPlus - $koreksiMinus;

        $summary = [
            'laba_komersial' => $labaKomersial,
            'pph_final'      => $pphFinal,
            'tmop'           => $tmop,
            'koreksi_plus'   => $koreksiPlus,
            'koreksi_minus'  => $koreksiMinus,
            'pkp'            => $pkp,
        ];

        $kredit = KreditPajak::with('pph25')->latest()->first();
        $pph22 = $kredit->pph_22 ?? 0;
        $pph23 = $kredit->pph_23 ?? 0;
        $pph24 = $kredit->pph_24 ?? 0;
        $pph25Total = optional(optional($kredit)->pph25)->sum('nilai') ?? 0;


        $pphTerutang = $summary['pkp'] * 0.22;

        $pph29 = $pphTerutang - ($pph22 + $pph23 + $pph24 + $pph25Total);

        return view('perhitungan_pajak_penghasilan.index', compact(
            'summary',
            'kredit',
            'pphTerutang',
            'pph25Total',
            'pph29'
        ));
    }

    public function store(Request $request)
    {
        $kredit = KreditPajak::create([
            'tahun'   => now()->year,
            'pph_22'  => $request->pph_22 ?? 0,
            'pph_23'  => $request->pph_23 ?? 0,
            'pph_24'  => $request->pph_24 ?? 0,
        ]);

        $bulan = [
            'januari',
            'februari',
            'maret',
            'april',
            'mei',
            'juni',
            'juli',
            'agustus',
            'september',
            'oktober',
            'november',
            'desember'
        ];

        foreach ($bulan as $b) {
            KreditPajakPph25::create([
                'kredit_pajak_id' => $kredit->id,
                'bulan' => ucfirst($b),
                'nilai' => $request->input('pph_25_' . $b) ?? 0,
            ]);
        }

        return redirect()->back()->with('success', 'Data berhasil disimpan');
    }

    public function update(Request $request, $id)
    {
        $kredit = KreditPajak::findOrFail($id);
        $kredit->update([
            'pph_22' => $request->pph_22 ?? 0,
            'pph_23' => $request->pph_23 ?? 0,
            'pph_24' => $request->pph_24 ?? 0,
        ]);

        $bulan = [
            'januari',
            'februari',
            'maret',
            'april',
            'mei',
            'juni',
            'juli',
            'agustus',
            'september',
            'oktober',
            'november',
            'desember'
        ];

        foreach ($bulan as $b) {
            $detail = KreditPajakPph25::where('kredit_pajak_id', $kredit->id)
                ->where('bulan', ucfirst($b))->first();

            if ($detail) {
                $detail->update(['nilai' => $request->input('pph_25_' . $b) ?? 0]);
            } else {
                KreditPajakPph25::create([
                    'kredit_pajak_id' => $kredit->id,
                    'bulan' => ucfirst($b),
                    'nilai' => $request->input('pph_25_' . $b) ?? 0,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Data berhasil diperbarui');
    }
}

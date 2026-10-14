<?php

namespace App\Services;

use App\ChartOfAccount;
use App\JournalEntry;
use Illuminate\Support\Facades\DB;

class StartNewYearService
{
    /**
     * Proses start new year untuk update laba tahun berjalan secara berpasangan (Double-Entry Balanced)
     */
    public function updateLabaTahunBerjalan(int $tahunLama): void
    {
        DB::transaction(function () use ($tahunLama) {
            $tahunBaru = $tahunLama + 1;

            // 1. Hitung laba bersih tahun berjalan (Kredit Pendapatan - Debit Beban)
            $labaTahunBerjalan = $this->hitungLabaTahunBerjalan($tahunLama);

            // 2. Cari jurnal Start New Year
            $jurnal = JournalEntry::where('tanggal', "{$tahunBaru}-01-01")
                ->where('source', 'START NEW YEAR')
                ->first();

            if (!$jurnal) {
                $jurnal = JournalEntry::create([
                    'source'  => 'START NEW YEAR',
                    'tanggal' => "{$tahunBaru}-01-01",
                    'comment' => 'Start New Year - Laba Tahun Berjalan',
                ]);
            }

            // 3. Hapus detail lama
            $jurnal->details()->delete();

            if (abs($labaTahunBerjalan) < 0.01) {
                return;
            }

            // 4. Ambil akun Laba Tahun Berjalan (level_akun = 'X') & Laba Ditahan (Retained Earnings)
            $akunLabaBerjalan = ChartOfAccount::where('level_akun', 'X')->first();
            $akunLabaDitahan = ChartOfAccount::where('tipe_akun', 'Ekuitas')
                ->where('nama_akun', 'like', '%Laba Ditahan%')
                ->first() ?? $akunLabaBerjalan;

            if (!$akunLabaBerjalan) {
                throw new \Exception('Akun dengan level "X" (Laba Tahun Berjalan) tidak ditemukan.');
            }

            $nominal = abs($labaTahunBerjalan);

            if ($labaTahunBerjalan > 0) {
                // Laba Bersih: Debit Ikhtisar/Laba Tahun Berjalan, Credit Laba Ditahan
                $jurnal->details()->create([
                    'departemen_akun_id' => null,
                    'kode_akun'          => $akunLabaBerjalan->kode_akun,
                    'debits'             => $nominal,
                    'credits'            => 0,
                    'comment'            => 'Penutupan ikhtisar laba tahun berjalan ' . $tahunLama,
                ]);
                $jurnal->details()->create([
                    'departemen_akun_id' => null,
                    'kode_akun'          => $akunLabaDitahan->kode_akun,
                    'debits'             => 0,
                    'credits'            => $nominal,
                    'comment'            => 'Pemindahan laba bersih ke laba ditahan ' . $tahunLama,
                ]);
            } else {
                // Rugi Bersih: Debit Laba Ditahan, Credit Ikhtisar/Laba Tahun Berjalan
                $jurnal->details()->create([
                    'departemen_akun_id' => null,
                    'kode_akun'          => $akunLabaDitahan->kode_akun,
                    'debits'             => $nominal,
                    'credits'            => 0,
                    'comment'            => 'Pemindahan rugi bersih ke laba ditahan ' . $tahunLama,
                ]);
                $jurnal->details()->create([
                    'departemen_akun_id' => null,
                    'kode_akun'          => $akunLabaBerjalan->kode_akun,
                    'debits'             => 0,
                    'credits'            => $nominal,
                    'comment'            => 'Penutupan ikhtisar rugi tahun berjalan ' . $tahunLama,
                ]);
            }
        });
    }

    /**
     * Hitung laba bersih tahun berjalan dari akun nominal (Pendapatan & Beban: 4000000 s/d 6000000)
     */
    private function hitungLabaTahunBerjalan(int $tahun): float
    {
        return (float) DB::table('journal_entry_details as d')
            ->join('journal_entries as j', 'd.journal_entry_id', '=', 'j.id')
            ->join('chart_of_accounts as coa', 'd.kode_akun', '=', 'coa.kode_akun')
            ->whereYear('j.tanggal', $tahun)
            ->where('j.source', '!=', 'START NEW YEAR')
            ->whereIn('coa.tipe_akun', ['Pendapatan', 'Beban'])
            ->selectRaw('SUM(d.credits - d.debits) as laba')
            ->value('laba') ?? 0.0;
    }
}

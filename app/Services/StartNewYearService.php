<?php

namespace App\Services;

use App\ChartOfAccount;
use App\JournalEntry;
use Illuminate\Support\Facades\DB;

class StartNewYearService
{
    /**
     * Proses start new year untuk update laba tahun berjalan
     */
    public function updateLabaTahunBerjalan(int $tahunLama): void
    {
        DB::transaction(function () use ($tahunLama) {
            $tahunBaru = $tahunLama + 1;

            // 1. Hitung laba tahun berjalan
            $labaTahunBerjalan = $this->hitungLabaTahunBerjalan($tahunLama);

            // 2. Cari jurnal Start New Year
            $jurnal = JournalEntry::where('tanggal', "{$tahunBaru}-01-01")
                ->where('source', 'START NEW YEAR')
                ->first();

            // 3. Kalau belum ada, buat
            if (!$jurnal) {
                $jurnal = JournalEntry::create([
                    'source'    => 'START NEW YEAR',
                    'tanggal'   => "{$tahunBaru}-01-01",
                    'comment'   => 'Start New Year - Laba Tahun Berjalan',
                ]);
            }

            // 4. Hapus detail lama
            $jurnal->details()->delete();

            // 5. Ambil kode akun berdasarkan level akun 'X'
            $akunLaba = ChartOfAccount::where('level_akun', 'X')->first();

            if (!$akunLaba) {
                throw new \Exception('Akun dengan level "X" (Laba Ditahan) tidak ditemukan.');
            }

            // 6. Tambahkan detail baru
            $jurnal->details()->create([
                'departemen_akun_id' => null,
                'kode_akun'          => $akunLaba->kode_akun,
                'debits'             => 0,
                'credits'            => $labaTahunBerjalan,
                'comment'            => 'Penutupan laba tahun berjalan'
            ]);
        });
    }

    /**
     * Hitung laba tahun berjalan
     */
    private function hitungLabaTahunBerjalan(int $tahun): float
    {
        return (float) DB::table('journal_entry_details as d')
            ->join('journal_entries as j', 'd.journal_entry_id', '=', 'j.id')
            ->whereYear('j.tanggal', $tahun)
            ->selectRaw('SUM(d.debits - d.credits) as laba')
            ->value('laba') ?? 0;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    //
    public function showStartNewYear()
    {
        // Ambil periode aktif untuk ditampilkan info
        $periodeAktif = DB::table('start_new_years')->where('status', 'Opening')->first();

        return view('accounting.start_new_year', compact('periodeAktif'));
    }

    public function startNewYearProcess(Request $request)
    {
        $periodeAktif = DB::table('start_new_years')->where('status', 'Opening')->first();
        if (!$periodeAktif) {
            return response()->json(['success' => false, 'message' => 'Periode aktif tidak ditemukan']);
        }

        $tahun = $periodeAktif->tahun;

        // Closing dibuat H+1 (awal tahun baru)
        $tanggalClosing = date('Y-m-d', strtotime($periodeAktif->akhir_periode . ' +1 day'));

        // Ambil data jurnal detail tahun berjalan
        $entries = DB::table('journal_entry_details as d')
            ->join('journal_entries as j', 'd.journal_entry_id', '=', 'j.id')
            ->join('chart_of_accounts as coa', 'd.kode_akun', '=', 'coa.kode_akun')
            ->whereYear('j.tanggal', $tahun)
            ->whereIn('coa.tipe_akun', ['Pendapatan', 'Beban']) // hanya akun nominal
            ->select(
                'd.kode_akun',
                'coa.nama_akun',
                'coa.tipe_akun',
                DB::raw('SUM(d.debits) as total_debit'),
                DB::raw('SUM(d.credits) as total_credit')
            )
            ->groupBy('d.kode_akun', 'coa.nama_akun', 'coa.tipe_akun')
            ->get();

        // Balik posisi debit & kredit untuk penutupan
        $entries = $entries->map(function ($item) {
            $tmp = $item->total_debit;
            $item->total_debit = $item->total_credit;
            $item->total_credit = $tmp;
            return $item;
        });

        // Hitung laba/rugi
        $totalDebit  = $entries->sum('total_debit');
        $totalCredit = $entries->sum('total_credit');

        if ($totalDebit > $totalCredit) {
            $debitLaba = 0;
            $creditLaba = $totalDebit - $totalCredit; // Laba
        } elseif ($totalCredit > $totalDebit) {
            $debitLaba = $totalCredit - $totalDebit; // Rugi
            $creditLaba = 0;
        } else {
            $debitLaba = 0;
            $creditLaba = 0;
        }

        // Ambil akun
        $akunLabaTahunBerjalan = DB::table('chart_of_accounts')->where('level_akun', 'X')->first();
        $akunLabaDitahan = DB::table('chart_of_accounts')
            ->where('tipe_akun', 'Ekuitas')
            ->where('nama_akun', 'like', '%Laba Ditahan%')
            ->first();

        if (!$akunLabaTahunBerjalan || !$akunLabaDitahan) {
            return response()->json(['success' => false, 'message' => 'Akun laba tahun berjalan atau laba ditahan tidak ditemukan']);
        }

        DB::beginTransaction();
        try {
            // 1. Jurnal penutup
            $closingJournalId = DB::table('journal_entries')->insertGetId([
                'source'     => 'START NEW YEAR',
                'tanggal'    => $tanggalClosing,
                'comment'    => 'Start New Year - Penutupan Buku',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($entries as $item) {
                DB::table('journal_entry_details')->insert([
                    'journal_entry_id'    => $closingJournalId,
                    'departemen_akun_id'  => null,
                    'kode_akun'           => $item->kode_akun,
                    'debits'              => $item->total_debit,
                    'credits'             => $item->total_credit,
                    'comment'             => 'Penutupan akun ' . $item->nama_akun,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            if ($debitLaba > 0) {
                DB::table('journal_entry_details')->insert([
                    'journal_entry_id' => $closingJournalId,
                    'kode_akun'        => $akunLabaTahunBerjalan->kode_akun,
                    'debits'           => $debitLaba,
                    'credits'          => 0,
                    'comment'          => 'Laba tahun berjalan (debit)',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
            if ($creditLaba > 0) {
                DB::table('journal_entry_details')->insert([
                    'journal_entry_id' => $closingJournalId,
                    'kode_akun'        => $akunLabaTahunBerjalan->kode_akun,
                    'debits'           => 0,
                    'credits'          => $creditLaba,
                    'comment'          => 'Laba tahun berjalan (kredit)',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            // 2. Jurnal pemindahan laba ke laba ditahan
            $transferJournalId = DB::table('journal_entries')->insertGetId([
                'source'     => 'START NEW YEAR',
                'tanggal'    => $tanggalClosing,
                'comment'    => 'Pemindahan Laba Tahun Berjalan ke Laba Ditahan',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($creditLaba > 0) {
                // Laba
                DB::table('journal_entry_details')->insert([
                    [
                        'journal_entry_id' => $transferJournalId,
                        'kode_akun'        => $akunLabaTahunBerjalan->kode_akun,
                        'debits'           => $creditLaba,
                        'credits'          => 0,
                        'comment'          => 'Pemindahan laba tahun berjalan',
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ],
                    [
                        'journal_entry_id' => $transferJournalId,
                        'kode_akun'        => $akunLabaDitahan->kode_akun,
                        'debits'           => 0,
                        'credits'          => $creditLaba,
                        'comment'          => 'Pemindahan ke laba ditahan',
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]
                ]);
            } elseif ($debitLaba > 0) {
                // Rugi
                DB::table('journal_entry_details')->insert([
                    [
                        'journal_entry_id' => $transferJournalId,
                        'kode_akun'        => $akunLabaDitahan->kode_akun,
                        'debits'           => $debitLaba,
                        'credits'          => 0,
                        'comment'          => 'Pemindahan rugi tahun berjalan',
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ],
                    [
                        'journal_entry_id' => $transferJournalId,
                        'kode_akun'        => $akunLabaTahunBerjalan->kode_akun,
                        'debits'           => 0,
                        'credits'          => $debitLaba,
                        'comment'          => 'Pemindahan rugi tahun berjalan',
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]
                ]);
            }

            // 3. Tutup periode lama
            DB::table('start_new_years')
                ->where('id', $periodeAktif->id)
                ->update(['status' => 'Closing']);

            // 4. Buat periode baru (awal = tanggalClosing)
            $awalPeriodeBaru  = $tanggalClosing;
            $akhirPeriodeBaru = date('Y-m-d', strtotime($awalPeriodeBaru . ' +1 year -1 day'));

            DB::table('start_new_years')->insert([
                'tahun'         => $tahun + 1,
                'awal_periode'  => $awalPeriodeBaru,
                'akhir_periode' => $akhirPeriodeBaru,
                'status'        => 'Opening',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Start new year berhasil diproses']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal proses start new year: ' . $e->getMessage()]);
        }
    }
}

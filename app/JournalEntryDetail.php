<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class JournalEntryDetail extends Model
{
    //
    protected $fillable = [
        'journal_entry_id',
        'departemen_akun_id',
        'debits',
        'credits',
        'comment',
        'status',
        'kode_akun',
        'project_id',
        'pajak',
        'penyesuaian_fiskal',
        'kode_fiscal',
    ];

    // protected static function booted()
    // {
    //     // Trigger ketika data diubah atau dibuat
    //     static::saved(function ($detail) {
    //         self::handleStartNewYear($detail);
    //     });

    //     // Trigger ketika data dihapus
    //     static::deleted(function ($detail) {
    //         self::handleStartNewYear($detail);
    //     });
    // }

    // protected static function handleStartNewYear($detail)
    // {
    //     $tanggalTransaksi = \Carbon\Carbon::parse($detail->journalEntry->tanggal);
    //     $tahunTransaksi   = $tanggalTransaksi->format('Y');
    //     $tahunSekarang    = now()->format('Y');

    //     // Cari periode berjalan
    //     $periodeAktif = \App\StartNewYear::where('tahun', $tahunTransaksi)->first();

    //     Log::info('[StartNewYear] Debug', [
    //         'tanggal_transaksi' => $tanggalTransaksi->toDateString(),
    //         'tahun_transaksi'   => $tahunTransaksi,
    //         'tahun_sekarang'    => $tahunSekarang,
    //         'periode_ditemukan' => $periodeAktif ? 'YA' : 'TIDAK',
    //         'akhir_periode'     => $periodeAktif->akhir_periode ?? null,
    //     ]);


    //     if (!$periodeAktif) {
    //         dump(['step' => 'STOP - Tidak ada periode aktif']);
    //         return;
    //     }

    //     $tanggalClosing = \Carbon\Carbon::parse($periodeAktif->akhir_periode)->addDay();

    //     dump([
    //         'step'            => 'Hitung tanggal closing',
    //         'akhir_periode'   => $periodeAktif->akhir_periode,
    //         'tanggal_closing' => $tanggalClosing->toDateString(),
    //     ]);

    //     // Hanya jalan kalau transaksi tepat di tanggal closing
    //     if (!$tanggalTransaksi->isSameDay($tanggalClosing)) {
    //         dump([
    //             'step'              => 'STOP - Bukan tanggal closing',
    //             'tanggal_transaksi' => $tanggalTransaksi->toDateString(),
    //             'tanggal_closing'   => $tanggalClosing->toDateString(),
    //         ]);
    //         return;
    //     }

    //     // Guard tambahan
    //     if ($tahunTransaksi != $tanggalClosing->format('Y')) {
    //         dump([
    //             'step'              => 'STOP - Tahun transaksi tidak cocok',
    //             'tahun_transaksi'   => $tahunTransaksi,
    //             'tahun_closing'     => $tanggalClosing->format('Y'),
    //         ]);
    //         return;
    //     }

    //     // Eksekusi service
    //     dump([
    //         'step'          => 'EKSEKUSI updateLabaTahunBerjalan',
    //         'tahun_ditutup' => $periodeAktif->tahun,
    //     ]);

    //     app(\App\Services\StartNewYearService::class)
    //         ->updateLabaTahunBerjalan($periodeAktif->tahun);
    // }


    // Relasi: setiap detail milik satu journal entry
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
    public function akun()
    {
        return $this->belongsTo(DepartemenAkun::class, 'departemen_akun_id');
    }


    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'kode_akun', 'kode_akun');
    }
    public function departemenAkun()
    {
        return $this->belongsTo(DepartemenAkun::class, 'departemen_akun_id');
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}

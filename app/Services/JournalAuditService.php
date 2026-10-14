<?php

namespace App\Services;

use App\ChartOfAccount;
use App\DepartemenAkun;
use App\Departement;
use App\JournalAuditAnomaly;
use App\JournalAuditRemediationLog;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\KamusIndikatorAkun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JournalAuditService
{
    /**
     * Memindai transaksi jurnal berdasarkan aturan Kamus Indikator
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int|null $limit
     * @param string|null $sourcePrefix
     * @return array
     */
    public function scanTransactions($startDate = null, $endDate = null, $limit = null, $sourcePrefix = null): array
    {
        // 1. Ambil aturan aktif dari kamus indikator
        $rules = KamusIndikatorAkun::active()->get();

        if ($rules->isEmpty()) {
            return [
                'status' => 'empty_rules',
                'message' => 'Belum ada aturan aktif di Kamus Indikator Akun.',
                'total_scanned' => 0,
                'salah_akun' => 0,
                'ambigu' => 0,
                'tepat' => 0,
                'pola_baru' => 0,
            ];
        }

        // 2. Pre-load master data untuk lookup cepat
        $coas = ChartOfAccount::select('id', 'kode_akun', 'nama_akun', 'tipe_akun')
            ->get()
            ->keyBy('kode_akun');

        $deptAkunMap = DepartemenAkun::pluck('departemen_id', 'id')->toArray();

        // 3. Bangun query transaksi jurnal
        $query = JournalEntryDetail::with(['journalEntry'])
            ->select('journal_entry_details.*')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_details.journal_entry_id');

        if (!empty($startDate)) {
            $query->whereDate('journal_entries.tanggal', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('journal_entries.tanggal', '<=', $endDate);
        }
        if (!empty($sourcePrefix) && $sourcePrefix !== 'all') {
            $query->where('journal_entries.source', 'like', $sourcePrefix . '%');
        }

        if ($limit) {
            $query->limit($limit);
        }

        $details = $query->get();

        $stats = [
            'total_scanned' => $details->count(),
            'tepat'         => 0,
            'salah_akun'    => 0,
            'ambigu'        => 0,
            'pola_baru'     => 0,
            'upserted'      => 0,
        ];

        // Ambil existing anomalies untuk ID ini agar tidak duplikasi
        $detailIds = $details->pluck('id')->toArray();
        $existingAnomalies = JournalAuditAnomaly::whereIn('journal_entry_detail_id', $detailIds)
            ->get()
            ->keyBy('journal_entry_detail_id');

        $batchAnomalies = [];
        $now = now()->toDateTimeString();

        foreach ($details as $detail) {
            $je = $detail->journalEntry;
            if (!$je) {
                continue;
            }
            $voucher = $je->source ?? '';
            $tanggal = $je->tanggal ?? null;
            $comment = trim($detail->comment ?: ($je->comment ?? ''));

            $debit = (float) $detail->debits;
            $kredit = (float) $detail->credits;
            $amount = max($debit, $kredit);
            $currentKodeAkun = trim($detail->kode_akun);
            $currentAkun = $coas->get($currentKodeAkun);
            $currentNamaAkun = $currentAkun ? $currentAkun->nama_akun : null;

            // Cari departemen_id langsung dari memory map
            $departemenId = $detail->departemen_akun_id ? ($deptAkunMap[$detail->departemen_akun_id] ?? null) : null;

            // Jalankan pencocokan terhadap rules
            $matchedRule = null;
            foreach ($rules as $rule) {
                // Filter sumber dana prefix jika diset
                if (!empty($rule->sumber_dana_prefix)) {
                    if (stripos($voucher, $rule->sumber_dana_prefix) !== 0) {
                        continue;
                    }
                }

                // Filter arah mutasi
                if ($rule->arah_mutasi === 'debit' && $debit <= 0) {
                    continue;
                }
                if ($rule->arah_mutasi === 'kredit' && $kredit <= 0) {
                    continue;
                }

                // Filter nominal amount
                if (!$rule->matchesAmount($amount)) {
                    continue;
                }

                // Filter text match
                if ($rule->matchesText($comment)) {
                    $matchedRule = $rule;
                    break; // Ambil aturan prioritas pertama yang cocok
                }
            }

            // Tentukan status dan diagnosa
            $status = 'POLA_BARU';
            $recommendedKodeAkun = null;
            $recommendedNamaAkun = null;
            $alternateOptions = null;
            $alasanDiagnosa = null;
            $ruleId = null;

            // Pengaman Khusus: Jurnal Penutupan Laba Rugi / Saldo Awal Tahunan (LR- / SA-)
            if (Str::startsWith($voucher, ['LR-', 'SA-']) || Str::contains($comment, ['Laba Rugi 2025', 'Saldo Akhir 31 Des', 'Saldo Awal'])) {
                $status = 'TEPAT';
                $alasanDiagnosa = "Sesuai: Jurnal Saldo Awal Neraca / Penutupan Buku Tahunan ({$voucher}).";
                $recommendedKodeAkun = $currentKodeAkun;
                $recommendedNamaAkun = $currentNamaAkun;
                $stats['tepat']++;
            } elseif ($matchedRule) {
                $ruleId = $matchedRule->id;
                $targetKode = $matchedRule->target_kode_akun;
                $targetAkun = $coas->get($targetKode);
                $recommendedKodeAkun = $targetKode;
                $recommendedNamaAkun = $targetAkun ? $targetAkun->nama_akun : $matchedRule->target_nama_akun;
                $alternateOptions = $matchedRule->alternate_kode_akuns;

                if ($matchedRule->kategori === 'PENDAPATAN_FOTO') {
                    $isPendapatanFoto = Str::startsWith($currentKodeAkun, '41');
                    $isKasPiutangClosing = in_array($currentKodeAkun, ['1101002', '1103001']);
                    if ($isPendapatanFoto || $isKasPiutangClosing) {
                        $status = 'TEPAT';
                        $alasanDiagnosa = "Sesuai: Closing penjualan foto wahana tercatat di akun yang sah ({$currentKodeAkun} - {$currentNamaAkun}).";
                        $recommendedKodeAkun = $currentKodeAkun;
                        $recommendedNamaAkun = $currentNamaAkun;
                        $stats['tepat']++;
                    } else {
                        $status = 'SALAH_AKUN';
                        $alasanDiagnosa = "Terindikasi salah akun! Transaksi closing foto '{$comment}' dicatat di akun tidak wajar {$currentKodeAkun} ({$currentNamaAkun}). Seharusnya dicatat ke akun Pendapatan Foto (41xxxxx) atau Kas Omset (1101002) / Piutang QRIS (1103001).";
                        $recommendedKodeAkun = '1101002';
                        $recommendedNamaAkun = 'KAS OMSET';
                        $stats['salah_akun']++;
                    }
                } elseif ($matchedRule->kategori === 'CABANG_KINGS') {
                    $isBcaEvi = ($currentKodeAkun === '1102012');
                    $isPiutangKings = Str::startsWith($currentKodeAkun, '1108');
                    if ($isBcaEvi || $isPiutangKings) {
                        $status = 'TEPAT';
                        $alasanDiagnosa = "Sesuai: Penyetoran omset/pelunasan cabang Fotolov Kings Bandung tercatat di akun yang sah ({$currentKodeAkun} - {$currentNamaAkun}).";
                        $recommendedKodeAkun = $currentKodeAkun;
                        $recommendedNamaAkun = $currentNamaAkun;
                        $stats['tepat']++;
                    } else {
                        $status = 'SALAH_AKUN';
                        $alasanDiagnosa = "Terindikasi salah akun! Transaksi cabang Kings '{$comment}' dicatat di akun {$currentKodeAkun} ({$currentNamaAkun}). Seharusnya di BCA Evi (1102012) atau Piutang Fotolov Kings (1108003).";
                        $recommendedKodeAkun = '1108003';
                        $recommendedNamaAkun = 'PIUTANG FOTOLOV - KINGS';
                        $stats['salah_akun']++;
                    }
                } elseif ($matchedRule->kategori === 'GOWET_GOPAY') {
                    $isPendapatanGowet = Str::startsWith($currentKodeAkun, '416');
                    $isKasBankGowet = in_array($currentKodeAkun, ['1102013', '1101012', '1107001', '1101006']);
                    if ($isPendapatanGowet || $isKasBankGowet) {
                        $status = 'TEPAT';
                        $alasanDiagnosa = "Sesuai: Penerimaan omset / settlement GoPay wahana Go! Wet Waterpark tercatat di akun yang sah ({$currentKodeAkun} - {$currentNamaAkun}).";
                        $recommendedKodeAkun = $currentKodeAkun;
                        $recommendedNamaAkun = $currentNamaAkun;
                        $stats['tepat']++;
                    } else {
                        $status = 'SALAH_AKUN';
                        $alasanDiagnosa = "Terindikasi salah akun! Transaksi GoWet/GoPay '{$comment}' dicatat di akun {$currentKodeAkun} ({$currentNamaAkun}). Seharusnya di Pendapatan GoWet (4160100) atau Rekening Operasional GoWet.";
                        $recommendedKodeAkun = '4160100';
                        $recommendedNamaAkun = 'PENDAPATAN GOWET';
                        $stats['salah_akun']++;
                    }
                } elseif ($matchedRule->kategori === 'SETTLEMENT_QRIS') {
                    $isKasBankTampungan = Str::startsWith($currentKodeAkun, ['1101', '1102']);
                    $isPiutangQris = ($currentKodeAkun === '1103001');
                    if ($isKasBankTampungan || $isPiutangQris) {
                        $status = 'TEPAT';
                        $alasanDiagnosa = "Sesuai: Penerimaan settlement QRIS tercatat di rekening penampungan/piutang yang sah ({$currentKodeAkun} - {$currentNamaAkun}).";
                        $recommendedKodeAkun = $currentKodeAkun;
                        $recommendedNamaAkun = $currentNamaAkun;
                        $stats['tepat']++;
                    } else {
                        $status = 'SALAH_AKUN';
                        $alasanDiagnosa = "Terindikasi salah akun! Transaksi settlement QRIS '{$comment}' dicatat di akun {$currentKodeAkun} ({$currentNamaAkun}). Seharusnya di Bank Penampungan (1102xxx) atau Piutang QRIS (1103001).";
                        $recommendedKodeAkun = '1103001';
                        $recommendedNamaAkun = 'PIUTANG QRIS';
                        $stats['salah_akun']++;
                    }
                } elseif ($matchedRule->kategori === 'SETOR_BAGI_HASIL') {
                    $isKasSetorRetail = ($currentKodeAkun === '1101011');
                    $isKasOmset = ($currentKodeAkun === '1101002');
                    if ($isKasSetorRetail || $isKasOmset) {
                        $status = 'TEPAT';
                        $alasanDiagnosa = "Sesuai: Penyetoran bagi hasil omset wahana Ancol tercatat di akun kas yang sah ({$currentKodeAkun} - {$currentNamaAkun}).";
                        $recommendedKodeAkun = $currentKodeAkun;
                        $recommendedNamaAkun = $currentNamaAkun;
                        $stats['tepat']++;
                    } else {
                        $status = 'SALAH_AKUN';
                        $alasanDiagnosa = "Terindikasi salah akun! Penyetoran bagi hasil omset Ancol '{$comment}' dicatat di akun {$currentKodeAkun} ({$currentNamaAkun}). Seharusnya di Kas Setor Retail (1101011) atau Kas Omset (1101002).";
                        $recommendedKodeAkun = '1101011';
                        $recommendedNamaAkun = 'KAS SETOR RETAIL';
                        $stats['salah_akun']++;
                    }
                } elseif ($matchedRule->kategori === 'MUTASI_INTERNAL') {
                    $isKasBank = Str::startsWith($currentKodeAkun, ['1101', '1102']);
                    if ($isKasBank) {
                        $status = 'TEPAT';
                        $alasanDiagnosa = "Sesuai: Mutasi kas/bank internal tercatat di akun Kas/Bank ({$currentKodeAkun} - {$currentNamaAkun}).";
                        $recommendedKodeAkun = $currentKodeAkun;
                        $recommendedNamaAkun = $currentNamaAkun;
                        $stats['tepat']++;
                    } else {
                        $status = 'SALAH_AKUN';
                        $alasanDiagnosa = "Terindikasi salah akun! Keterangan '{$comment}' mengindikasikan transfer/mutasi internal kas/bank, tetapi dicatat di akun non-kas/bank {$currentKodeAkun} ({$currentNamaAkun}). Wajib dipindahkan ke akun Kas/Bank yang sebenarnya, dilarang dibuang ke 1104001 Piutang Lain-Lain.";
                        $recommendedKodeAkun = '1102001';
                        $recommendedNamaAkun = 'KAS / BANK OPERASIONAL';
                        $stats['salah_akun']++;
                    }
                } elseif ($matchedRule->kategori === 'CABANG_LEMBANG_ZOO') {
                    $isKasPenampungan = in_array($currentKodeAkun, ['1101003', '1102001']);
                    $isPiutangLZ = Str::startsWith($currentKodeAkun, '1108001');
                    if ($isKasPenampungan || $isPiutangLZ) {
                        $status = 'TEPAT';
                        $alasanDiagnosa = "Sesuai: Penyetoran omset/pelunasan cabang Fotolov Lembang Zoo tercatat di akun yang sah ({$currentKodeAkun} - {$currentNamaAkun}).";
                        $recommendedKodeAkun = $currentKodeAkun;
                        $recommendedNamaAkun = $currentNamaAkun;
                        $stats['tepat']++;
                    } else {
                        $status = 'SALAH_AKUN';
                        $alasanDiagnosa = "Terindikasi salah akun! Transaksi cabang Lembang Zoo '{$comment}' dicatat di akun {$currentKodeAkun} ({$currentNamaAkun}). Seharusnya di Kas Firman (1101003) atau Piutang Lembang Zoo (1108001).";
                        $recommendedKodeAkun = '1108001';
                        $recommendedNamaAkun = 'PIUTANG FOTOLOV - LEMBANG ZOO';
                        $stats['salah_akun']++;
                    }
                } elseif (Str::startsWith($currentKodeAkun, ['1101', '1102']) && in_array($matchedRule->kategori, ['ADMIN_BANK', 'BUNGA_BANK'])) {
                    // Sisi rekening kas/bank untuk biaya admin atau bunga/pajak giro
                    $status = 'TEPAT';
                    $alasanDiagnosa = "Sesuai: Mutasi kas/bank sah ({$currentKodeAkun} - {$currentNamaAkun}) sebagai sumber dana/penerimaan transaksi perbankan.";
                    $recommendedKodeAkun = $currentKodeAkun;
                    $recommendedNamaAkun = $currentNamaAkun;
                    $stats['tepat']++;
                } elseif (Str::startsWith($currentKodeAkun, ['1101', '1102']) && $kredit > 0 && in_array($matchedRule->kategori, [
                    'GAJI_KARYAWAN', 'REFUND', 'PEMELIHARAAN', 'PEMELIHARAAN_KENDARAAN', 'PEMELIHARAAN_FASILITAS',
                    'UTILITAS', 'KONSUMSI', 'ATK_CETAKAN', 'BPJS', 'TRANSPORTASI', 'PERALATAN_KECIL', 'ASET_INVENTARIS', 'ASET_KENDARAAN'
                ])) {
                    // Pengeluaran kas/bank untuk pembayaran operasional yang sah via transfer bank atau kasir
                    $status = 'TEPAT';
                    $alasanDiagnosa = "Sesuai: Pengeluaran rekening Kas/Bank ({$currentKodeAkun} - {$currentNamaAkun}) sebagai sumber dana pembayaran sah ({$matchedRule->kategori}).";
                    $recommendedKodeAkun = $currentKodeAkun;
                    $recommendedNamaAkun = $currentNamaAkun;
                    $stats['tepat']++;
                } elseif (Str::startsWith($currentKodeAkun, ['1101', '1102']) && Str::startsWith($targetKode, ['5', '12'])) {
                    // Kasus mutasi kas/bank internal yang memuat kata kunci beban (misal transfer antar kasir)
                    $status = 'AMBIGU_BIAS';
                    $alasanDiagnosa = "Tercatat di akun Kas/Bank ({$currentKodeAkun}). Memo '{$comment}' merujuk pada keperluan operasional, namun transaksi ini merupakan mutasi kas/bank fisik. Perlu konfirmasi apakah ini mutasi antar kasir atau pengeluaran langsung.";
                    $stats['ambigu']++;
                } elseif ($currentKodeAkun === $targetKode) {
                    if ($matchedRule->tingkat_kepastian === 'AMBIGU_REVIEW') {
                        $status = 'AMBIGU_BIAS';
                        $alasanDiagnosa = "Akun sesuai target aturan, namun berada dalam zona evaluasi kebijakan: {$matchedRule->penjelasan_aturan}";
                        $stats['ambigu']++;
                    } else {
                        $status = 'TEPAT';
                        $alasanDiagnosa = "Sesuai aturan kamus: {$matchedRule->nama_indikator}.";
                        $stats['tepat']++;
                    }
                } else {
                    // Cek apakah akun sekarang masuk ke alternate_kode_akuns
                    $isAlternate = is_array($alternateOptions) && in_array($currentKodeAkun, $alternateOptions);

                    if ($isAlternate) {
                        $status = 'AMBIGU_BIAS';
                        $alasanDiagnosa = "Tercatat di akun alternatif ({$currentKodeAkun}). Memerlukan konfirmasi kesesuaian: {$matchedRule->penjelasan_aturan}";
                        $stats['ambigu']++;
                    } else {
                        $status = 'SALAH_AKUN';
                        $alasanDiagnosa = "Terindikasi salah akun! Komentar '{$comment}' mengarah ke akun {$targetKode} ({$recommendedNamaAkun}), namun dicatat di akun {$currentKodeAkun} ({$currentNamaAkun}). {$matchedRule->penjelasan_aturan}";
                        $stats['salah_akun']++;
                    }
                }
            } else {
                // Tidak ada rule yang cocok
                if (empty($comment)) {
                    $status = 'POLA_BARU';
                    $alasanDiagnosa = 'Transaksi tidak memiliki keterangan (komentar kosong), perlu pengecekan bukti fisik/voucher.';
                } else {
                    $status = 'POLA_BARU';
                    $alasanDiagnosa = "Pola komentar '{$comment}' belum terdaftar di Kamus Indikator Akun.";
                }
                $stats['pola_baru']++;
            }

            // Simpan atau update record anomali
            $existing = $existingAnomalies->get($detail->id);

            // Jika anomali sudah pernah di-resolve secara manual oleh user, jangan timpa action-nya
            $isResolved = $existing ? $existing->is_resolved : false;
            $resolvedAt = $existing ? $existing->resolved_at : null;
            $resolvedBy = $existing ? $existing->resolved_by : null;
            $resolvedAction = $existing ? $existing->resolved_action : null;

            if ($status === 'TEPAT' && !$isResolved) {
                // Jika sudah tepat dan belum ditandai, kita bisa tandai resolved
                $isResolved = true;
                $resolvedAction = 'confirmed_valid';
            }

            $payload = [
                'journal_entry_id'       => $je->id,
                'voucher'                => $voucher,
                'tanggal'                => $tanggal,
                'departemen_id'          => $departemenId,
                'current_kode_akun'      => $currentKodeAkun,
                'current_nama_akun'      => $currentNamaAkun,
                'recommended_kode_akun'  => $recommendedKodeAkun,
                'recommended_nama_akun'  => $recommendedNamaAkun,
                'alternate_options'      => $alternateOptions,
                'debits'                 => $debit,
                'credits'                => $kredit,
                'comment'                => $comment,
                'status'                 => $status,
                'alasan_diagnosa'        => $alasanDiagnosa,
                'rule_id'                => $ruleId,
                'is_resolved'            => $isResolved,
                'resolved_at'            => $resolvedAt,
                'resolved_by'            => $resolvedBy,
                'resolved_action'        => $resolvedAction,
            ];

            try {
                if ($existing) {
                    $existing->update($payload);
                } else {
                    $payload['journal_entry_detail_id'] = $detail->id;
                    JournalAuditAnomaly::create($payload);
                }
                $stats['upserted']++;
            } catch (\Exception $e) {
                // Lewati dengan aman jika record detail sudah terhapus di DB
            }
        }

        return $stats;
    }

    /**
     * Memperbaiki satu anomali dengan re-assign akun
     */
    public function remediateSingle(int $anomalyId, string $newKodeAkun, ?int $userId, ?string $alasan = null): array
    {
        $anomaly = JournalAuditAnomaly::with('journalEntryDetail')->findOrFail($anomalyId);
        $detail = $anomaly->journalEntryDetail;

        if (!$detail) {
            throw new \Exception('Journal Entry Detail tidak ditemukan.');
        }

        $newAkun = ChartOfAccount::where('kode_akun', $newKodeAkun)->first();
        if (!$newAkun) {
            throw new \Exception("Chart of Account dengan kode {$newKodeAkun} tidak ditemukan.");
        }

        return DB::transaction(function () use ($anomaly, $detail, $newAkun, $newKodeAkun, $userId, $alasan) {
            $oldKodeAkun = $detail->kode_akun;
            $oldDeptAkunId = $detail->departemen_akun_id;

            // Cari departemen_akun_id yang sesuai jika detail memiliki departemen sebelumnya
            $newDeptAkunId = null;
            if ($oldDeptAkunId) {
                $oldDa = DepartemenAkun::find($oldDeptAkunId);
                if ($oldDa) {
                    $newDa = DepartemenAkun::where('departemen_id', $oldDa->departemen_id)
                        ->where('akun_id', $newAkun->id)
                        ->first();
                    $newDeptAkunId = $newDa ? $newDa->id : null;
                }
            }

            // Update baris journal_entry_detail
            $detail->update([
                'kode_akun' => $newKodeAkun,
                'departemen_akun_id' => $newDeptAkunId,
            ]);

            // Catat log remediasi
            JournalAuditRemediationLog::create([
                'journal_entry_detail_id' => $detail->id,
                'journal_audit_anomaly_id' => $anomaly->id,
                'voucher'                 => $anomaly->voucher,
                'old_kode_akun'           => $oldKodeAkun,
                'new_kode_akun'           => $newKodeAkun,
                'old_departemen_akun_id'  => $oldDeptAkunId,
                'new_departemen_akun_id'  => $newDeptAkunId,
                'amount'                  => max((float)$detail->debits, (float)$detail->credits),
                'comment'                 => $detail->comment,
                'alasan'                  => $alasan ?: 'Remediasi melalui Audit & Kamus Jurnal',
                'user_id'                 => $userId,
            ]);

            // Update status anomali
            $anomaly->update([
                'current_kode_akun' => $newKodeAkun,
                'current_nama_akun' => $newAkun->nama_akun,
                'status'            => 'TEPAT',
                'is_resolved'       => true,
                'resolved_at'       => now(),
                'resolved_by'       => $userId,
                'resolved_action'   => 'reassigned',
                'alasan_diagnosa'   => "Telah diremediasi dari {$oldKodeAkun} ke {$newKodeAkun}: " . ($alasan ?: 'Sesuai rekomendasi kamus'),
            ]);

            return [
                'success' => true,
                'message' => "Akun berhasil diubah dari {$oldKodeAkun} ke {$newKodeAkun} ({$newAkun->nama_akun}).",
            ];
        });
    }

    /**
     * Memperbaiki anomali secara masal (Bulk Re-assign)
     */
    public function remediateBulk(array $anomalyIds, ?int $userId, ?string $alasan = null): array
    {
        $anomalies = JournalAuditAnomaly::with('journalEntryDetail')
            ->whereIn('id', $anomalyIds)
            ->where('is_resolved', false)
            ->whereNotNull('recommended_kode_akun')
            ->get();

        $successCount = 0;
        $failedCount = 0;
        $batchId = 'BULK-' . date('YmdHis');

        foreach ($anomalies as $anomaly) {
            try {
                $detail = $anomaly->journalEntryDetail;
                if (!$detail) {
                    $failedCount++;
                    continue;
                }

                $newKodeAkun = $anomaly->recommended_kode_akun;
                $newAkun = ChartOfAccount::where('kode_akun', $newKodeAkun)->first();
                if (!$newAkun) {
                    $failedCount++;
                    continue;
                }

                $oldKodeAkun = $detail->kode_akun;
                $oldDeptAkunId = $detail->departemen_akun_id;

                $newDeptAkunId = null;
                if ($oldDeptAkunId) {
                    $oldDa = DepartemenAkun::find($oldDeptAkunId);
                    if ($oldDa) {
                        $newDa = DepartemenAkun::where('departemen_id', $oldDa->departemen_id)
                            ->where('akun_id', $newAkun->id)
                            ->first();
                        $newDeptAkunId = $newDa ? $newDa->id : null;
                    }
                }

                $detail->update([
                    'kode_akun' => $newKodeAkun,
                    'departemen_akun_id' => $newDeptAkunId,
                ]);

                JournalAuditRemediationLog::create([
                    'journal_entry_detail_id' => $detail->id,
                    'journal_audit_anomaly_id' => $anomaly->id,
                    'voucher'                 => $anomaly->voucher,
                    'old_kode_akun'           => $oldKodeAkun,
                    'new_kode_akun'           => $newKodeAkun,
                    'old_departemen_akun_id'  => $oldDeptAkunId,
                    'new_departemen_akun_id'  => $newDeptAkunId,
                    'amount'                  => max((float)$detail->debits, (float)$detail->credits),
                    'comment'                 => $detail->comment,
                    'alasan'                  => $alasan ?: 'Bulk Remediasi Otomatis Sesuai Kamus',
                    'user_id'                 => $userId,
                    'batch_id'                => $batchId,
                ]);

                $anomaly->update([
                    'current_kode_akun' => $newKodeAkun,
                    'current_nama_akun' => $newAkun->nama_akun,
                    'status'            => 'TEPAT',
                    'is_resolved'       => true,
                    'resolved_at'       => now(),
                    'resolved_by'       => $userId,
                    'resolved_action'   => 'reassigned',
                    'alasan_diagnosa'   => "Telah diremediasi massal ke {$newKodeAkun} ({$newAkun->nama_akun})",
                ]);

                $successCount++;
            } catch (\Exception $e) {
                Log::error("Gagal remediate anomaly ID {$anomaly->id}: " . $e->getMessage());
                $failedCount++;
            }
        }

        return [
            'success' => true,
            'batch_id' => $batchId,
            'remediated' => $successCount,
            'failed' => $failedCount,
        ];
    }
}

<?php

namespace App\Services;

use App\StagingJournalEntry;
use App\ChartOfAccount;
use App\DepartemenAkun;
use App\Departement;
use App\JournalEntry;
use App\JournalEntryDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JournalIngestionService
{
    /**
     * All-or-Nothing: Validasi SEMUA baris pending terlebih dahulu.
     * Jika ada 1 error di mana saja → SEMUA baris ditolak, tidak ada yang masuk ke journal_entries.
     * Jika semua valid → insert dalam satu transaksi besar.
     *
     * @param  string|null $batchId  Filter batch tertentu (opsional)
     * @return array
     */
    public function processPendingEntries($batchId = null): array
    {
        $query = StagingJournalEntry::where('status', 'pending');
        if ($batchId) {
            $query->where('batch_id', $batchId);
        }
        $pendingEntries = $query->get();

        if ($pendingEntries->isEmpty()) {
            return [
                'status'         => 'empty',
                'message'        => 'Tidak ada data pending untuk diproses.',
                'processed_rows' => 0,
                'failed_rows'    => 0,
                'errors'         => [],
                'summary'        => null,
            ];
        }

        // ─── Preload Master Data (hindari N+1 query) ───────────────────────────
        $coaByCode = ChartOfAccount::get(['id', 'kode_akun', 'nama_akun'])
            ->keyBy('kode_akun');

        $allDepartemen         = Departement::all(['id', 'kode', 'deskripsi']);
        $departemenByKode      = $allDepartemen->keyBy(fn($d) => strtoupper(trim($d->kode ?? '')));
        $departemenByDeskripsi = $allDepartemen->keyBy(fn($d) => strtoupper(trim($d->deskripsi ?? '')));

        $deptAkunIndex = DepartemenAkun::all(['id', 'departemen_id', 'akun_id'])
            ->keyBy(fn($da) => $da->departemen_id . ':' . $da->akun_id);

        $kamusRules = \App\KamusIndikatorAkun::active()->get();

        // ─── Kelompokkan per Voucher (source + tanggal) ────────────────────────
        $grouped = $pendingEntries->groupBy(
            fn($item) => ($item->source ?? '') . '|' . ($item->tanggal ?? '')
        );

        // ═══════════════════════════════════════════════════════════════════════
        // FASE 1 — VALIDASI TOTAL (kumpulkan SEMUA error, jangan insert dulu)
        // ═══════════════════════════════════════════════════════════════════════
        $allErrors   = [];   // [ ['source'=>.., 'tanggal'=>.., 'message'=>..], ... ]
        $rowErrorMap = [];   // row->id => string error_message

        foreach ($grouped as $key => $groupRows) {
            [$source, $tanggal] = array_pad(explode('|', $key, 2), 2, '');

            $voucherErrors = [];
            $totalDebit    = 0.0;
            $totalKredit   = 0.0;

            foreach ($groupRows as $row) {
                $debitVal  = $this->parseAmount($row->debit);
                $kreditVal = $this->parseAmount($row->kredit);
                $totalDebit  += $debitVal;
                $totalKredit += $kreditVal;

                $rowErrors = [];

                // ── Validasi kode_akun ────────────────────────────────────────
                if (empty(trim($row->kode_akun ?? ''))) {
                    $rowErrors[] = "Kode akun kosong.";
                } elseif (!$coaByCode->has($row->kode_akun)) {
                    $rowErrors[] = "Kode akun '{$row->kode_akun}' tidak ditemukan di Chart of Accounts.";
                }

                // ── Validasi departemen (OPSIONAL) ────────────────────────────
                $deptValue = trim($row->departemen ?? '');
                if (!empty($deptValue)) {
                    $deptKey = strtoupper($deptValue);
                    $dept    = $departemenByKode->get($deptKey)
                            ?? $departemenByDeskripsi->get($deptKey);

                    if (!$dept) {
                        $rowErrors[] = "Departemen '{$deptValue}' tidak ditemukan di master data.";
                    } elseif ($coaByCode->has($row->kode_akun)) {
                        $akun     = $coaByCode->get($row->kode_akun);
                        $indexKey = $dept->id . ':' . $akun->id;
                        if (!$deptAkunIndex->has($indexKey)) {
                            $rowErrors[] = "Kombinasi departemen '{$deptValue}' + akun '{$row->kode_akun}' belum terdaftar di Assign Account (departemen_akuns).";
                        }
                    }
                }

                // ── Validasi Kamus Indikator (Cek Salah Akun) ─────────────────
                if (!empty($row->comment_line) && $kamusRules->isNotEmpty()) {
                    foreach ($kamusRules as $rule) {
                        if ($rule->tingkat_kepastian === 'PASTI' && $rule->matchesText($row->comment_line)) {
                            $isTarget = ($row->kode_akun === $rule->target_kode_akun);
                            $isAlt    = is_array($rule->alternate_kode_akuns) && in_array($row->kode_akun, $rule->alternate_kode_akuns);
                            if (!$isTarget && !$isAlt) {
                                $rowErrors[] = "[Peringatan Kamus COA] Narasi '{$row->comment_line}' terindikasi ke akun {$rule->target_kode_akun} ({$rule->target_nama_akun}), bukan '{$row->kode_akun}'.";
                                break;
                            }
                        }
                    }
                }

                if (!empty($rowErrors)) {
                    $rowErrorMap[$row->id] = implode('; ', $rowErrors);
                }
            }

            // ── Validasi Balance per Voucher ──────────────────────────────────
            if (round($totalDebit, 2) !== round($totalKredit, 2)) {
                $balanceMsg = sprintf(
                    "Balance tidak seimbang: Total Debit=%s ≠ Total Kredit=%s.",
                    number_format($totalDebit, 2, '.', ','),
                    number_format($totalKredit, 2, '.', ',')
                );
                $voucherErrors[] = $balanceMsg;

                foreach ($groupRows as $row) {
                    $rowErrorMap[$row->id] = isset($rowErrorMap[$row->id])
                        ? $rowErrorMap[$row->id] . '; ' . $balanceMsg
                        : $balanceMsg;
                }
            }

            // ── Tambahkan error baris ke voucherErrors ────────────────────────
            foreach ($groupRows as $row) {
                if (isset($rowErrorMap[$row->id])) {
                    $voucherErrors[] = "Baris [{$row->kode_akun}]: " . $rowErrorMap[$row->id];
                }
            }

            if (!empty($voucherErrors)) {
                $allErrors[] = [
                    'source'  => $source,
                    'tanggal' => $tanggal,
                    'rows'    => $groupRows->count(),
                    'message' => implode(' | ', array_unique($voucherErrors)),
                ];
            }
        }

        // ═══════════════════════════════════════════════════════════════════════
        // FASE 1 RESULT — Ada Error → Tolak Semua (All-or-Nothing)
        // ═══════════════════════════════════════════════════════════════════════
        if (!empty($allErrors)) {
            $totalRows     = $pendingEntries->count();
            $totalVouchers = $grouped->count();
            $errorVouchers = count($allErrors);

            // ⚠️ PENTING: Update status TANPA DB::transaction()
            //    Agar tidak di-rollback secara tidak sengaja
            $defaultMsg = "Ditolak: batch ini ditolak karena voucher lain mengandung error (All-or-Nothing).";
            foreach ($pendingEntries as $row) {
                StagingJournalEntry::where('id', $row->id)->update([
                    'status'        => 'failed',
                    'error_message' => $rowErrorMap[$row->id] ?? $defaultMsg,
                    'updated_at'    => now(),
                ]);
            }

            return [
                'status'         => 'validation_error',
                'message'        => "Proses ditolak (All-or-Nothing): {$errorVouchers} dari {$totalVouchers} voucher bermasalah. Semua {$totalRows} baris ditandai failed.",
                'processed_rows' => 0,
                'failed_rows'    => $totalRows,
                'errors'         => $allErrors,
                'summary'        => [
                    'total_rows'     => $totalRows,
                    'total_vouchers' => $totalVouchers,
                    'error_vouchers' => $errorVouchers,
                    'ok_vouchers'    => $totalVouchers - $errorVouchers,
                ],
            ];
        }

        // ═══════════════════════════════════════════════════════════════════════
        // FASE 2 — SEMUA VALID → Insert dalam satu transaksi besar
        // ═══════════════════════════════════════════════════════════════════════
        try {
            DB::transaction(function () use (
                $grouped, $coaByCode,
                $departemenByKode, $departemenByDeskripsi, $deptAkunIndex
            ) {
                foreach ($grouped as $groupRows) {
                    $firstRow = $groupRows->first();

                    $journalEntry = JournalEntry::create([
                        'source'  => $firstRow->source,
                        'tanggal' => $this->transformDate($firstRow->tanggal),
                        'comment' => $firstRow->comment_line ?? '',
                    ]);

                    foreach ($groupRows as $row) {
                        $akun             = $coaByCode->get($row->kode_akun);
                        $departemenAkunId = null;

                        $deptValue = trim($row->departemen ?? '');
                        if (!empty($deptValue) && $akun) {
                            $deptKey = strtoupper($deptValue);
                            $dept    = $departemenByKode->get($deptKey)
                                    ?? $departemenByDeskripsi->get($deptKey);
                            if ($dept) {
                                $indexKey        = $dept->id . ':' . $akun->id;
                                $deptAkunRecord  = $deptAkunIndex->get($indexKey);
                                $departemenAkunId = $deptAkunRecord ? $deptAkunRecord->id : null;
                            }
                        }

                        JournalEntryDetail::create([
                            'journal_entry_id'   => $journalEntry->id,
                            'kode_akun'          => $row->kode_akun,
                            'departemen_akun_id' => $departemenAkunId,
                            'debits'             => $this->parseAmount($row->debit),
                            'credits'            => $this->parseAmount($row->kredit),
                            'comment'            => $row->comment_line ?? '',
                        ]);
                    }

                    // Tandai semua baris voucher ini sebagai success
                    StagingJournalEntry::whereIn('id', $groupRows->pluck('id')->toArray())
                        ->update([
                            'status'        => 'success',
                            'error_message' => null,
                            'updated_at'    => now(),
                        ]);
                }
            });

            $totalRows = $pendingEntries->count();
            return [
                'status'         => 'success',
                'message'        => "Proses Ingestion Selesai. Semua data berhasil dimasukkan.",
                'processed_rows' => $totalRows,
                'failed_rows'    => 0,
                'errors'         => [],
                'summary'        => null,
            ];

        } catch (\Exception $e) {
            Log::error('[JournalIngestionService] Fatal error: ' . $e->getMessage());

            // Semua baris ditandai failed — DILUAR transaction yang sudah rollback
            StagingJournalEntry::whereIn('id', $pendingEntries->pluck('id')->toArray())
                ->update([
                    'status'        => 'failed',
                    'error_message' => 'Internal Error: ' . $e->getMessage(),
                    'updated_at'    => now(),
                ]);

            return [
                'status'         => 'error',
                'message'        => 'Terjadi error internal: ' . $e->getMessage(),
                'processed_rows' => 0,
                'failed_rows'    => $pendingEntries->count(),
                'errors'         => [],
                'summary'        => null,
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    protected function parseAmount($value): float
    {
        if (empty($value)) return 0.0;
        return (float) str_replace([',', ' ', '-'], '', $value);
    }

    protected function transformDate($dateString): ?string
    {
        if (empty($dateString)) return null;

        if (is_numeric($dateString)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$dateString)
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                // fallthrough
            }
        }

        $parsed = strtotime($dateString);
        return $parsed ? date('Y-m-d', $parsed) : null;
    }
}

<?php

namespace App\Services;

use App\StagingJournalEntry;
use App\ChartOfAccount;
use App\DepartemenAkun;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JournalIngestionService
{
    /**
     * Memproses semua data staging dengan status 'pending'.
     *
     * @param string|null $batchId Opsional, memproses batch tertentu saja.
     * @return array Hasil ringkasan proses
     */
    public function processPendingEntries($batchId = null)
    {
        $query = StagingJournalEntry::where('status', 'pending');
        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        $pendingEntries = $query->get();

        if ($pendingEntries->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada data pending untuk diproses.', 'processed' => 0];
        }

        // 1) Kelompokkan berdasarkan Jurnal (source + tanggal)
        $grouped = $pendingEntries->groupBy(function ($item) {
            return ($item->source ?? '') . '|' . ($item->tanggal ?? '');
        });

        $processedCount = 0;
        $failedCount    = 0;

        // 2) Preload Master Data untuk performa (hindari N+1 query)
        $allCoa    = ChartOfAccount::get(['kode_akun', 'nama_akun', 'id']);
        $coaByCode = $allCoa->keyBy('kode_akun');

        // Preload Departement — lookup by kode ATAU deskripsi (case-insensitive)
        $allDepartemen         = \App\Departement::all(['id', 'kode', 'deskripsi']);
        $departemenByKode      = $allDepartemen->keyBy(fn($d) => strtoupper(trim($d->kode ?? '')));
        $departemenByDeskripsi = $allDepartemen->keyBy(fn($d) => strtoupper(trim($d->deskripsi ?? '')));

        // Preload DepartemenAkun — index: "departemen_id:akun_id" → record
        $allDeptAkun   = DepartemenAkun::all(['id', 'departemen_id', 'akun_id']);
        $deptAkunIndex = $allDeptAkun->keyBy(fn($da) => $da->departemen_id . ':' . $da->akun_id);

        foreach ($grouped as $key => $groupRows) {
            DB::beginTransaction();
            try {
                $totalDebit    = 0;
                $totalKredit   = 0;
                $hasError      = false;
                $errorMessages = [];

                // 3) Validasi Per Baris
                foreach ($groupRows as $row) {
                    $debitVal  = $this->parseAmount($row->debit);
                    $kreditVal = $this->parseAmount($row->kredit);
                    $totalDebit  += $debitVal;
                    $totalKredit += $kreditVal;

                    // Cek ketersediaan akun di COA
                    $akun = $coaByCode->get($row->kode_akun);
                    if (!$akun) {
                        $hasError        = true;
                        $errorMessages[] = "Kode akun '{$row->kode_akun}' tidak ditemukan di Chart of Accounts.";
                    }

                    // Validasi departemen — hanya jika kolom departemen DIISI di Excel
                    if (!empty(trim($row->departemen ?? ''))) {
                        $deptKey = strtoupper(trim($row->departemen));

                        // Cari by kode dulu, jika tidak ada coba by deskripsi
                        $dept = $departemenByKode->get($deptKey)
                             ?? $departemenByDeskripsi->get($deptKey);

                        if (!$dept) {
                            $hasError        = true;
                            $errorMessages[] = "Departemen '{$row->departemen}' tidak ditemukan di master data.";
                        } elseif ($akun) {
                            // Cek apakah pasangan departemen + akun terdaftar di departemen_akuns
                            $indexKey = $dept->id . ':' . $akun->id;
                            $deptAkun = $deptAkunIndex->get($indexKey);

                            if (!$deptAkun) {
                                $hasError        = true;
                                $errorMessages[] = "Kombinasi departemen '{$row->departemen}' dan akun '{$row->kode_akun}' belum terdaftar di Assign Account (departemen_akuns).";
                            }
                        }
                    }
                }

                // 4) Validasi Total Balance
                if (round($totalDebit, 2) !== round($totalKredit, 2)) {
                    $hasError        = true;
                    $errorMessages[] = "Total debit ($totalDebit) dan kredit ($totalKredit) tidak balance.";
                }

                if ($hasError) {
                    // Tandai SELURUH BATCH jurnal ini sebagai failed — tidak ada baris yang lolos
                    $this->markAsFailed($groupRows, implode('; ', array_unique($errorMessages)));
                    $failedCount += $groupRows->count();
                    DB::rollBack();
                    continue;
                }

                // 5) Insert ke Tabel Utama (journal_entries & journal_entry_details)
                $firstRow     = $groupRows->first();
                $journalEntry = JournalEntry::create([
                    'source'  => $firstRow->source,
                    'tanggal' => $this->transformDate($firstRow->tanggal),
                    'comment' => $firstRow->comment_line ?? '',
                ]);

                foreach ($groupRows as $row) {
                    $akun = $coaByCode->get($row->kode_akun);

                    // Resolve departemen_akun_id dari string departemen
                    $departemenAkunId = null;
                    if (!empty(trim($row->departemen ?? '')) && $akun) {
                        $deptKey = strtoupper(trim($row->departemen));
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

                // 6) Tandai sukses di staging
                $this->markAsSuccess($groupRows);
                $processedCount += $groupRows->count();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Gagal proses Ingestion: ' . $e->getMessage());
                $this->markAsFailed($groupRows, 'Internal Error: ' . $e->getMessage());
                $failedCount += $groupRows->count();
            }
        }

        return [
            'status'         => 'success',
            'message'        => "Proses Ingestion Selesai.",
            'processed_rows' => $processedCount,
            'failed_rows'    => $failedCount
        ];
    }

    protected function parseAmount($value)
    {
        if (empty($value)) return 0;
        return (float) str_replace([',', ' ', '-'], '', $value);
    }

    protected function transformDate($dateString)
    {
        if (empty($dateString)) return null;

        // Tangani konversi date berupa angka serial dari excel
        if (is_numeric($dateString)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateString)->format('Y-m-d');
            } catch (\Exception $e) {
                // Fallback jika gagal
            }
        }

        return date('Y-m-d', strtotime($dateString));
    }

    protected function markAsFailed($groupRows, $message)
    {
        $ids = $groupRows->pluck('id');
        StagingJournalEntry::whereIn('id', $ids)->update([
            'status'        => 'failed',
            'error_message' => $message,
            'updated_at'    => now()
        ]);
    }

    protected function markAsSuccess($groupRows)
    {
        $ids = $groupRows->pluck('id');
        StagingJournalEntry::whereIn('id', $ids)->update([
            'status'        => 'success',
            'error_message' => null,
            'updated_at'    => now()
        ]);
    }
}

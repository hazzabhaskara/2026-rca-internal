<?php

namespace App\Imports;

use App\StagingJournalEntry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Str;

class JournalEntryImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    protected $batchId;

    public function __construct($batchId = null)
    {
        // Jika tidak diberikan dari controller, buat UID baru
        $this->batchId = $batchId ?? (string) Str::uuid();
    }

    public function collection(Collection $rows)
    {
        $stagingData = [];

        foreach ($rows as $row) {
            // Abaikan jika source dan tanggal kosong
            if (empty($row['source']) && empty($row['tanggal'])) {
                continue;
            }

            $stagingData[] = [
                'batch_id'     => $this->batchId,
                'source'       => $row['source'] ?? null,
                'tanggal'      => $row['tanggal'] ?? null,
                'departemen'   => $row['departemen'] ?? null,
                'kode_akun'    => $row['kode_akun'] ?? null,
                'nama_akun'    => $row['nama_akun'] ?? null,
                'debit'        => $row['debit'] ?? null,
                'kredit'       => $row['kredit'] ?? null,
                'comment_line' => $row['comment_line'] ?? $row['comment_transaksi'] ?? null,
                'specpose'     => $row['specpose'] ?? null,
                'status'       => 'pending',
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        if (count($stagingData) > 0) {
            StagingJournalEntry::insert($stagingData);
        }
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getBatchId()
    {
        return $this->batchId;
    }
}

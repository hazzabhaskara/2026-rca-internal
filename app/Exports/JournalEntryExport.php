<?php

namespace App\Exports;

use App\JournalEntry;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JournalEntryExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = JournalEntry::with([
            'details',
            'details.chartOfAccount',
            'details.departemenAkun.departemen',
            'details.project'
        ])
            ->orderBy('tanggal', 'asc') // bisa 'desc' kalau mau terbaru duluan
            ->get();


        // Flatten agar setiap detail jadi 1 row
        return $data->flatMap(function ($entry) {
            return $entry->details->map(function ($detail) use ($entry) {
                return [
                    $entry->tanggal,
                    $entry->source,
                    $entry->comment ?? '-',
                    $detail->kode_akun,
                    $detail->chartOfAccount->nama_akun ?? '-',
                    $detail->departemenAkun->departemen->deskripsi ?? '-',
                    $detail->debits,
                    $detail->credits,
                    $detail->comment,
                    $entry->project->nama_project ?? '-',
                ];
            });
        });
    }


    public function headings(): array
    {
        return [
            'Tanggal',
            'Source',
            'Comment Transaksi',
            'Kode Account',
            'Nama Account',
            'Departemen',
            'Debits',
            'Credits',
            'Comment Line',
            'Specpose'

        ];
    }
}

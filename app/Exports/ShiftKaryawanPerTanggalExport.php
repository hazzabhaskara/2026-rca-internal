<?php

namespace App\Exports;

use App\ShiftKaryawanWahana;
use App\EmployeeOffDay;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class ShiftKaryawanPerTanggalExport implements FromArray, WithTitle, WithStyles
{
    protected $tanggal;

    public function __construct($tanggal)
    {
        $this->tanggal = $tanggal;
    }

    public function array(): array
    {
        // Ambil data shift per tanggal
        $shifts = ShiftKaryawanWahana::with(['wahana', 'karyawan', 'unitKerja'])
            ->whereDate('tanggal', $this->tanggal)
            ->get()
            ->groupBy(fn($s) => optional($s->wahana)->nama_wahana ?? 'Tanpa Wahana');

        // Ambil data OFF dari tabel employee_off_days
        $offEmployees = EmployeeOffDay::with('employee')
            ->whereDate('tanggal', $this->tanggal)
            ->get()
            ->pluck('employee.nama_karyawan')
            ->toArray();

        $rows = [];

        // Header besar
        $rows[] = ["SHIFT KARYAWAN WAHANA - " . strtoupper(Carbon::parse($this->tanggal)->translatedFormat('l, d F Y'))];
        $rows[] = [];
        $rows[] = ['Wahana', 'Crew 1', 'Crew 2', 'Crew 3', 'Crew 4', 'Crew 5'];

        // Isi data
        foreach ($shifts as $wahana => $items) {
            $row = [$wahana];
            // Urutkan crew berdasarkan crew_id agar sejajar
            $crewList = $items->sortBy('crew_id')->pluck('karyawan.nama_karyawan')->toArray();
            foreach ($crewList as $nama) {
                $row[] = $nama;
            }
            $rows[] = $row;
        }

        // Tambah baris OFF (kalau ada)
        $rows[] = [];
        if (count($offEmployees) > 0) {
            $rows[] = ['OFF: ' . implode(', ', $offEmployees)];
        } else {
            $rows[] = ['OFF: -'];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Header utama
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFF00']
            ]
        ]);

        // Header kolom
        $sheet->getStyle('A3:F3')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'F4B084']
            ]
        ]);

        // Auto-size kolom
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Baris "OFF" diberi warna kuning muda
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A{$highestRow}:F{$highestRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF2CC']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'font' => ['bold' => true]
        ]);

        return [];
    }

    public function title(): string
    {
        return Carbon::parse($this->tanggal)->format('d-M');
    }
}

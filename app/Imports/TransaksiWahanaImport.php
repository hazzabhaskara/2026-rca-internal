<?php

namespace App\Imports;

use App\JenisHari;
use App\TransaksiWahana;
use App\UnitKerja;
use App\Wahana;
use App\TargetWahana;
use App\ShiftKaryawanWahana;
use App\Targetunit;
use App\BonusKaryawan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Str;

class TransaksiWahanaImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Cari ID unit kerja, wahana, jenis hari
            $jenisHari = JenisHari::where('nama', $row['jenis_hari'])->first();
            $wahana    = Wahana::where('nama_wahana', $row['wahana'])->first();
            $unitKerja = UnitKerja::where('nama_unit', $row['unit_kerja'])->first();

            if (!$unitKerja) {
                throw ValidationException::withMessages(['unit_kerja' => "Unit kerja '{$row['unit_kerja']}' tidak ditemukan."]);
            }
            if (!$wahana) {
                throw ValidationException::withMessages(['wahana' => "Wahana '{$row['wahana']}' tidak ditemukan."]);
            }
            if (!$jenisHari) {
                throw ValidationException::withMessages(['jenis_hari' => "Jenis Hari '{$row['jenis_hari']}' tidak ditemukan."]);
            }

            $tanggal   = $this->transformDate($row['tanggal']);
            $realisasi = (int) str_replace('.', '', $row['realisasi']);
            $pengunjung = $row['jumlah_pengunjung'] ?? null;

            // Cek unik (wahana+unit+tanggal)
            $exists = TransaksiWahana::where('wahana_id', $wahana->id)
                ->where('unit_kerja_id', $unitKerja->id)
                ->where('tanggal', $tanggal)
                ->exists();

            if ($exists) {
                Log::warning("Data duplikat dilewati untuk {$row['wahana']} - {$row['unit_kerja']} - {$tanggal}");
                continue;
            }

            // Simpan transaksi
            $transaksi = TransaksiWahana::create([
                'wahana_id'        => $wahana->id,
                'unit_kerja_id'    => $unitKerja->id,
                'jenis_hari_id'    => $jenisHari->id,
                'tanggal'          => $tanggal,
                'realisasi'        => $realisasi,
                'jumlah_pengunjung' => $pengunjung,
            ]);

            Log::info('Transaksi baru diimport', $transaksi->toArray());

            // Cari target harian
            $target = TargetWahana::where('wahana_id', $wahana->id)
                ->where('jenis_hari_id', $jenisHari->id)
                ->where('bulan', date('m', strtotime($tanggal)))
                ->where('tahun', date('Y', strtotime($tanggal)))
                ->first();

            // Cari shift karyawan
            $shifts = ShiftKaryawanWahana::with('karyawan')
                ->where('wahana_id', $wahana->id)
                ->where('unit_kerja_id', $unitKerja->id)
                ->where('tanggal', $tanggal)
                ->get();

            foreach ($shifts as $shift) {
                $bonus = 0;
                $transport = 0;

                if ($target && $realisasi >= $target->target_harian) {
                    // Ambil komponen "Bonus"
                    $targetUnitBonus = Targetunit::where('unit_kerja_id', $shift->unit_kerja_id)
                        ->where('level_karyawan_id', $shift->karyawan->level_kepegawaian_id ?? null)
                        ->whereHas('komponen', fn($q) => $q->where('nama_komponen', 'like', '%Bonus%'))
                        ->first();

                    if ($targetUnitBonus) {
                        $bonus = ($targetUnitBonus->besaran_nominal ?? 0) * ($shift->persentase_jam ?? 1);
                    }

                    // Ambil komponen "Transport"
                    $targetUnitTransport = Targetunit::where('unit_kerja_id', $shift->unit_kerja_id)
                        ->where('level_karyawan_id', $shift->karyawan->level_karyawan_id ?? null)
                        ->whereHas('komponen', fn($q) => $q->where('nama_komponen', 'like', '%Transport%'))
                        ->first();

                    if ($targetUnitTransport) {
                        $transport = $targetUnitTransport->besaran_nominal ?? 0;
                    }
                }

                // Simpan BonusKaryawan
                BonusKaryawan::updateOrCreate(
                    [
                        'employee_id' => $shift->employee_id,
                        'shift_id'    => $shift->id,
                    ],
                    [
                        'transaksi_wahana_id' => $transaksi->id,
                        'tanggal'             => $tanggal,
                        'jenis_hari_id'       => $jenisHari->id,
                        'bonus'               => $bonus,
                        'transportasi'        => $transport,
                        'total'               => $bonus + $transport,
                        'status'              => 'Calculated',
                    ]
                );
            }
        }
    }

    private function transformDate($value)
    {
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}

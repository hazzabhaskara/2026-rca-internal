<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\UnitKerja;
use App\Wahana;
use App\WahanaItem;
use App\linkedAccounts;
use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;

class ClosingHarianMasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $deptMap = Departement::pluck('id', 'kode')->toArray();
        $coaMap = ChartOfAccount::pluck('id', 'kode_akun')->toArray();

        $kasOmsetId = $coaMap['1101002'] ?? null;
        $piutangQrisId = $coaMap['1103001'] ?? null;
        $titipanMerchId = $coaMap['2106001'] ?? null;
        $bebanBagiHasilId = $coaMap['5303000'] ?? ($coaMap['5116000'] ?? null);
        $pendapatanNonOpId = $coaMap['4204000'] ?? ($coaMap['4200000'] ?? null);

        $ensureDeptAkun = function($akunId, $deptId) {
            if (!$akunId || !$deptId) return null;
            $exist = DepartemenAkun::where('akun_id', $akunId)->where('departemen_id', $deptId)->first();
            if ($exist) return $exist->id;

            $new = DepartemenAkun::create([
                'akun_id' => $akunId,
                'departemen_id' => $deptId
            ]);
            return $new->id;
        };

        // Pastikan departemen PBS ada
        if (!isset($deptMap['0010'])) {
            $pbsDept = Departement::firstOrCreate(['kode' => '0010'], ['deskripsi' => 'PBS']);
            $deptMap['0010'] = $pbsDept->id;
        }

        $units = [
            [
                'kode_unit' => 'DFN',
                'nama_unit' => 'DUNIA FANTASI',
                'deskripsi' => 'Unit Kerja Dunia Fantasi Ancol',
                'urutan' => 1,
                'format_closing' => 1,
                'dept_kode' => '0002',
                'wahanas' => [
                    ['NIAGARA', 'NIAGARA', '4110101', '4110102'],
                    ['ARUNG JERAM', 'ARUNG JERAM', '4110201', '4110202'],
                    ['ISTANA BONEKA', 'ISTANA BONEKA', '4110301', '4110302'],
                    ['KORA KORA', 'KORA KORA', '4110401', '4110402'],
                    ['BIANGLALA', 'BIANGLALA', '4110501', '4110502'],
                    ['HALILINTAR', 'HALILINTAR', '4110601', '4110602'],
                    ['TURANGGA', 'TURANGGA', '4110701', '4110702'],
                    ['BALING-BALING', 'BALING-BALING', '4110801', '4110802'],
                    ['TORNADO', 'TORNADO', '4110901', '4110902'],
                    ['ALAP-ALAP', 'ALAP-ALAP', '4111001', '4111002'],
                    ['ICE AGE', 'ICE AGE', '4111101', '4111102'],
                    ['HYSTERIA', 'HYSTERIA', '4111201', '4111202'],
                    ['SELAMAT DATANG', 'SELAMAT DATANG', '4111301', '4111302'],
                ],
                'items_generator' => function($wKode, $acc4rId, $acc6rId) {
                    return [
                        ['kode' => '4R', 'nama' => '4R', 'harga' => 70000, 'titipan' => 0, 'acc' => $acc4rId],
                        ['kode' => '4R', 'nama' => '4R SD', 'harga' => 70000, 'titipan' => 0, 'acc' => $acc4rId],
                        ['kode' => '6R', 'nama' => '6R', 'harga' => 100000, 'titipan' => 0, 'acc' => $acc6rId],
                        ['kode' => '6R', 'nama' => '6R SD', 'harga' => 100000, 'titipan' => 0, 'acc' => $acc6rId],
                    ];
                }
            ],
            [
                'kode_unit' => 'ODS',
                'nama_unit' => 'OCEAN DREAM SAMUDRA',
                'deskripsi' => 'Unit Kerja Ocean Dream Samudra Ancol',
                'urutan' => 2,
                'format_closing' => 2,
                'dept_kode' => '0003',
                'wahanas' => [
                    ['MOLA-MOLA', 'MOLA-MOLA', '4120101', '4120102', 0],
                    ['CAROUSEL', 'CAROUSEL', '4120201', '4120202', 0],
                    ['SINGA LAUT', 'SINGA LAUT', '4120301', '4120302', 30000],
                    ['LUMBA-LUMBA', 'LUMBA-LUMBA', '4120401', '4120402', 50000],
                    ['DOLPHIN BAY', 'DOLPHIN BAY', '4120501', '4120502', 100000],
                    ['PENGUIN', 'PENGUIN', '4120601', '4120602', 150000],
                    ['SELAMAT DATANG ODS', 'SELAMAT DATANG ODS', '4120101', '4120102', 0],
                ],
                'items_generator' => function($wKode, $acc4rId, $acc6rId, $titipanNominal = 0) {
                    return [
                        ['kode' => '4R', 'nama' => '4R', 'harga' => 70000, 'titipan' => 0, 'acc' => $acc4rId],
                        ['kode' => '4R', 'nama' => '4R SD', 'harga' => 70000, 'titipan' => 0, 'acc' => $acc4rId],
                        ['kode' => '6R', 'nama' => '6R', 'harga' => 100000, 'titipan' => $titipanNominal, 'acc' => $acc6rId],
                        ['kode' => '6R', 'nama' => '6R KEDUA', 'harga' => 100000, 'titipan' => 0, 'acc' => $acc6rId],
                    ];
                }
            ],
            [
                'kode_unit' => 'AWA',
                'nama_unit' => 'ATLANTIS',
                'deskripsi' => 'Unit Kerja Atlantis Water Adventures',
                'urutan' => 3,
                'format_closing' => 3,
                'dept_kode' => '0004',
                'wahanas' => [
                    ['ATLANTIS', 'ATLANTIS', '4130101', '4130102'],
                ],
                'items_generator' => function($wKode, $acc4rId, $acc6rId) {
                    return [
                        ['kode' => '4R', 'nama' => '4R', 'harga' => 50000, 'titipan' => 0, 'acc' => $acc4rId],
                        ['kode' => '4R', 'nama' => '4R SD', 'harga' => 70000, 'titipan' => 0, 'acc' => $acc4rId],
                        ['kode' => '6R', 'nama' => '6R', 'harga' => 100000, 'titipan' => 0, 'acc' => $acc6rId],
                        ['kode' => '6R', 'nama' => '6R SD', 'harga' => 100000, 'titipan' => 0, 'acc' => $acc6rId],
                    ];
                }
            ],
            [
                'kode_unit' => 'JBL',
                'nama_unit' => 'JAKARTA BIRD LAND',
                'deskripsi' => 'Unit Kerja Jakarta Bird Land Ancol',
                'urutan' => 4,
                'format_closing' => 4,
                'dept_kode' => '0005',
                'wahanas' => [
                    ['JAKARTA BIRD LAND', 'JAKARTA BIRD LAND', '4140101', '4140102'],
                ],
                'items_generator' => function($wKode, $acc4rId, $acc6rId) {
                    return [
                        ['kode' => '4R', 'nama' => '4R', 'harga' => 70000, 'titipan' => 20000, 'acc' => $acc4rId],
                        ['kode' => '4R', 'nama' => '4R DISKON', 'harga' => 50000, 'titipan' => 0, 'acc' => $acc4rId],
                        ['kode' => '6R', 'nama' => '6R', 'harga' => 100000, 'titipan' => 0, 'acc' => $acc6rId],
                        ['kode' => '6R', 'nama' => '6R DISKON', 'harga' => 80000, 'titipan' => 0, 'acc' => $acc6rId],
                    ];
                }
            ],
            [
                'kode_unit' => 'PBN',
                'nama_unit' => 'PHOTOBOX NEWSPAPER DUFAN',
                'deskripsi' => 'Unit Kerja Photobox Newspaper Dufan',
                'urutan' => 5,
                'format_closing' => 3,
                'dept_kode' => '0006',
                'wahanas' => [
                    ['PHOTOBOX NEWSPAPER DUFAN', 'PHOTOBOX NEWSPAPER DUFAN', '4150101', '4150101'],
                ],
                'items_generator' => function($wKode, $acc4rId, $acc6rId) {
                    return [
                        ['kode' => 'A3', 'nama' => 'A3', 'harga' => 50000, 'titipan' => 0, 'acc' => $acc4rId],
                        ['kode' => 'A3_2', 'nama' => 'A3 CETAKAN KEDUA', 'harga' => 20000, 'titipan' => 0, 'acc' => $acc4rId],
                    ];
                }
            ],
            [
                'kode_unit' => 'PBS',
                'nama_unit' => 'PHOTOBOX NEWSPAPER ODS',
                'deskripsi' => 'Unit Kerja Photobox Newspaper ODS',
                'urutan' => 6,
                'format_closing' => 3,
                'dept_kode' => '0006',
                'wahanas' => [
                    ['PHOTOBOX NEWSPAPER ODS', 'PHOTOBOX NEWSPAPER ODS', '4150101', '4150101'],
                ],
                'items_generator' => function($wKode, $acc4rId, $acc6rId) {
                    return [
                        ['kode' => 'A3', 'nama' => 'A3', 'harga' => 50000, 'titipan' => 0, 'acc' => $acc4rId],
                        ['kode' => 'A3_2', 'nama' => 'A3 CETAKAN KEDUA', 'harga' => 20000, 'titipan' => 0, 'acc' => $acc4rId],
                    ];
                }
            ],
            [
                'kode_unit' => 'SQP',
                'nama_unit' => 'Scientia Square Park',
                'deskripsi' => 'Scientia Square Park Serpong',
                'urutan' => 7,
                'format_closing' => 2,
                'dept_kode' => '0007',
                'wahanas' => [
                    ['SQP', 'Wahana Scientia Square Park', '4160101', '4160100'],
                ],
                'items_generator' => function($wKode, $acc4rId, $acc6rId) {
                    return [
                        ['kode' => '4R', 'nama' => '4R', 'harga' => 70000, 'titipan' => 0, 'acc' => $acc4rId],
                        ['kode' => '6R', 'nama' => '6R', 'harga' => 100000, 'titipan' => 0, 'acc' => $acc6rId],
                    ];
                }
            ],
            [
                'kode_unit' => 'GOWET',
                'nama_unit' => 'Go! Wet Waterpark',
                'deskripsi' => 'Go! Wet Waterpark Grand Wisata',
                'urutan' => 8,
                'format_closing' => 2,
                'dept_kode' => '0008',
                'wahanas' => [
                    ['GOWET', 'Wahana Go! Wet Waterpark', '4170101', '4170100'],
                ],
                'items_generator' => function($wKode, $acc4rId, $acc6rId) {
                    return [
                        ['kode' => '4R', 'nama' => '4R', 'harga' => 70000, 'titipan' => 0, 'acc' => $acc4rId],
                        ['kode' => '6R', 'nama' => '6R', 'harga' => 100000, 'titipan' => 0, 'acc' => $acc6rId],
                    ];
                }
            ],
            [
                'kode_unit' => 'EMO',
                'nama_unit' => 'Emo Project',
                'deskripsi' => 'Emo Project Wahana',
                'urutan' => 9,
                'format_closing' => 2,
                'dept_kode' => '0009',
                'wahanas' => [
                    ['EMO', 'Wahana Emo Project', '4180101', '4180100'],
                ],
                'items_generator' => function($wKode, $acc4rId, $acc6rId) {
                    return [
                        ['kode' => '4R', 'nama' => '4R', 'harga' => 70000, 'titipan' => 0, 'acc' => $acc4rId],
                        ['kode' => '6R', 'nama' => '6R', 'harga' => 100000, 'titipan' => 0, 'acc' => $acc6rId],
                    ];
                }
            ],
        ];

        DB::beginTransaction();
        try {
            foreach ($units as $u) {
                $deptId = $deptMap[$u['dept_kode']] ?? null;
                if (!$deptId) continue;

                $unit = UnitKerja::where('kode_unit', $u['kode_unit'])
                    ->orWhere('nama_unit', $u['nama_unit'])
                    ->first();

                if (!$unit && $u['kode_unit'] === 'DFN') {
                    $unit = UnitKerja::where('kode_unit', 'DUFAN')->first();
                }

                if ($unit) {
                    $unit->update([
                        'kode_unit' => $u['kode_unit'],
                        'nama_unit' => $u['nama_unit'],
                        'deskripsi' => $u['deskripsi'],
                        'urutan' => $u['urutan'],
                        'format_closing' => $u['format_closing']
                    ]);
                } else {
                    $unit = UnitKerja::create([
                        'kode_unit' => $u['kode_unit'],
                        'nama_unit' => $u['nama_unit'],
                        'deskripsi' => $u['deskripsi'],
                        'urutan' => $u['urutan'],
                        'format_closing' => $u['format_closing']
                    ]);
                }

                $linkedConfigs = [
                    ['Piutang Qris', $piutangQrisId],
                    ['Kas Omset', $kasOmsetId],
                    ['Titipan Omset Merchandise', $titipanMerchId],
                    ['Beban Bagi Hasil(Sewa)', $bebanBagiHasilId],
                    ['Pendapatan Non Operasional', $pendapatanNonOpId],
                ];

                foreach ($linkedConfigs as [$kodeLinked, $accId]) {
                    if (!$accId) continue;
                    $ensureDeptAkun($accId, $deptId);

                    linkedAccounts::updateOrCreate(
                        [
                            'modul' => 'closing',
                            'kode' => $kodeLinked,
                            'unit_kerja_id' => $unit->id,
                        ],
                        [
                            'akun_id' => $accId,
                            'departemen_id' => $deptId
                        ]
                    );
                }

                $oldWahanaIds = Wahana::where('unit_kerja_id', $unit->id)->pluck('id');
                WahanaItem::whereIn('wahana_id', $oldWahanaIds)->delete();
                Wahana::where('unit_kerja_id', $unit->id)->delete();

                foreach ($u['wahanas'] as $wIdx => $wRow) {
                    $wKode = $wRow[0];
                    $wNama = $wRow[1];
                    $item4rCode = $wRow[2];
                    $item6rCode = $wRow[3];
                    $titipanNominal = $wRow[4] ?? 0;

                    $wahana = Wahana::create([
                        'unit_kerja_id' => $unit->id,
                        'kode_wahana' => $wKode,
                        'nama_wahana' => $wNama,
                        'status' => 'aktif',
                        'kapasitas' => 1,
                        'kategori' => 'Foto',
                        'urutan' => $wIdx + 1
                    ]);

                    $acc4rId = $coaMap[$item4rCode] ?? ($coaMap['4100000'] ?? $kasOmsetId);
                    $acc6rId = $coaMap[$item6rCode] ?? $acc4rId;

                    $ensureDeptAkun($acc4rId, $deptId);
                    $ensureDeptAkun($acc6rId, $deptId);

                    $items = $u['items_generator']($wKode, $acc4rId, $acc6rId, $titipanNominal);

                    foreach ($items as $item) {
                        WahanaItem::create([
                            'wahana_id' => $wahana->id,
                            'kode_item' => $item['kode'],
                            'nama_item' => $item['nama'],
                            'harga' => $item['harga'],
                            'status' => 1,
                            'account_id' => $item['acc'],
                            'departemen_id' => $deptId,
                            'dasar_perhitungan_titipan' => ($item['titipan'] > 0 ? 1 : 0),
                            'harga_perhitungan_titipan' => $item['titipan']
                        ]);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

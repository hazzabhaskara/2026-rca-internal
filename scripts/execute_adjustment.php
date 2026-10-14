<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$totalanJeIds = [79786, 79790, 79794, 79801, 80996];

$coaList = DB::table('chart_of_accounts')->get()->keyBy('kode_akun');
$coaById = DB::table('chart_of_accounts')->get()->keyBy('id');

// Preload departemen_akuns
$deptAkunRows = DB::table('departemen_akuns')->get();
$deptAkunIndex = []; // "dept_id:akun_id" => id
$deptAkunToDept = []; // dept_akun_id => dept_id
foreach ($deptAkunRows as $da) {
    $deptAkunIndex[$da->departemen_id . ':' . $da->akun_id] = $da->id;
    $deptAkunToDept[$da->id] = $da->departemen_id;
}

$rows = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->whereIn('jed.kode_akun', ['5305000', '5305001'])
    ->whereYear('je.tanggal', 2026)
    ->select([
        'jed.id',
        'jed.journal_entry_id',
        'je.source',
        'je.tanggal',
        'jed.kode_akun',
        'jed.comment as jed_comment',
        'je.comment as je_comment',
        'jed.debits',
        'jed.credits',
        'jed.departemen_akun_id'
    ])
    ->orderBy('je.tanggal')
    ->orderBy('jed.id')
    ->get();

DB::beginTransaction();

try {
    // 1. Delete 5 totalan journal entries
    echo "1. Menghapus 5 voucher Pengeluaran Bank totalan...\n";
    $delDetails = DB::table('journal_entry_details')->whereIn('journal_entry_id', $totalanJeIds)->delete();
    $delEntries = DB::table('journal_entries')->whereIn('id', $totalanJeIds)->delete();
    echo "   -> Terhapus: {$delEntries} voucher journal_entries dan {$delDetails} baris journal_entry_details.\n\n";

    // 2. Reclassify remaining records
    echo "2. Memproses penyesuaian akun COA pada sisa transaksi...\n";
    $updatedCount = 0;
    $createdDeptAkunCount = 0;
    $unchangedCount = 0;

    foreach ($rows as $r) {
        if (in_array($r->journal_entry_id, $totalanJeIds)) {
            continue; // already deleted
        }

        $desc = strtolower(trim(($r->jed_comment ?? '') . ' ' . ($r->je_comment ?? '')));
        $newKode = '5305001'; // Default: Peralatan & Perlengkapan Operasional

        // 1. Biaya Bank (Administrasi Bank)
        if (preg_match('/b\.\s*transfer\s+antar\s+bank|biaya\s+transfer|cetak\s+rek|cetak\s+koran|b\.\s*ganti\s+kartu|biaya\s+ganti\s+kartu|charge\s+kartu|insuf\s+fund|b\.\s*kartu|b\.\s*gagal\s+transaksi\s+kartu|biaya\s+transfer\s+online/i', $desc)) {
            $newKode = '5316002';
        }
        // 2. Kartu Ancol Karyawan
        elseif (preg_match('/kartu\s+ancol|kartu\s+bulanan|pengembalian\s+kartu\s+ancol/i', $desc)) {
            $newKode = '1106001';
        }
        // 3. Setor Omset
        elseif (preg_match('/setor\s+omset|setoran\s+omset/i', $desc)) {
            $newKode = '1101011';
        }
        // 4. Listrik & Utilitas
        elseif (preg_match('/listrik\s+kost/i', $desc)) {
            $newKode = '5307004';
        } elseif (preg_match('/listrik|token\s+pln|token\s+listrik|pln/i', $desc)) {
            $newKode = '5307001';
        } elseif (preg_match('/air|pdam/i', $desc)) {
            $newKode = '5307002';
        }
        // 5. Mutasi Bank & Pindah Dana
        elseif (preg_match('/keluar\s+ke\s+bni|transfer.*bni\s+pt\s+ops|transfer.*bni\s+rca|transfer.*rek\s+bni\s+rca|bni\s+2010790365/i', $desc)) {
            $newKode = '3105000';
        } elseif (preg_match('/keluar\s+ke\s+mandiri|mandiri\s+rca|mandiri\s+pusat|mandiri\s+bdg/i', $desc)) {
            $newKode = '1102001';
        } elseif (preg_match('/keluar\s+ke\s+bri/i', $desc)) {
            $newKode = '1102015';
        } elseif (preg_match('/keluar\s+ke\s+cash\s+ops|cash\s+ops\s*\(untuk\s+gajian\)/i', $desc)) {
            $newKode = '1101010';
        } elseif (preg_match('/transfer\s+bca.*firman/i', $desc)) {
            $newKode = '1101003';
        } elseif (preg_match('/transfer\s+bca\s+evi|transfer\s+rek\s+bca\s+evi|transfer\s+evi\/kas/i', $desc)) {
            $newKode = '1102012';
        } elseif (preg_match('/transfer.*yerri/i', $desc)) {
            $newKode = '1102010';
        } elseif (preg_match('/transfer.*amelia|transfer.*amelya|transfer.*rek\s+bca\s+amel|transfer.*rek\s+bni\s+amel/i', $desc)) {
            $newKode = '1101008';
        }
        // 6. Setor ke Bandung / Pusat
        elseif (preg_match('/setor\s+cash\s+ke\s+bandung|setor\s+ke\s+bandung|keluar\s+ke\s+bandung|di\s+bawa\s+amel\s+untuk\s+ke\s+bdg|pusat/i', $desc)) {
            $newKode = '3105000';
        }
        // 7. Bahan Baku / Supplies Cetak Foto (Ribbon & Frame)
        elseif (preg_match('/ribbon|dnp|perdana\s+bangun\s+pusaka|syukran\s+misilu|dp\s+frame|pelunasan\s+frame|bayar\s+frame|pembelian\s+frame|kertas\s+newspaper/i', $desc)) {
            $newKode = '1110001';
        }
        // 8. Pemeliharaan & Perbaikan
        elseif (preg_match('/service\s+motor|servis\s+motor|pemeliharaan\s+kendaraan/i', $desc)) {
            $newKode = '5306002';
        } elseif (preg_match('/service\s+kamera|service\s+printer|service\s+dnp|pemeliharaan\s+peralatan|ganti\s+layar\s+laptop/i', $desc)) {
            $newKode = '5306003';
        } elseif (preg_match('/perbaikan\s+booth|pemeliharaan\s+booth/i', $desc)) {
            $newKode = '5306001';
        }
        // 9. Iuran & Retribusi (Sampah & Kebersihan)
        elseif (preg_match('/sampah|kebersihan|iuran/i', $desc)) {
            $newKode = '5309006';
        }
        // 10. IT, Software, Domain, Hosting
        elseif (preg_match('/rumah\s+web|langganan\s+server|server\s+np|server\s+fr|labtech|aplikasi|software|dslrbooth/i', $desc)) {
            $newKode = '5309010';
        }
        // 11. Fee & Imbalan Lain
        elseif (preg_match('/fee\s+komisaris|fee\s+admin|fee\s+rca|fee\s+pak\s+jaya/i', $desc)) {
            $newKode = '5301007';
        }
        // 12. ATK & Fotokopi
        elseif (preg_match('/atk|fotokopi|buku\s+laporan|pembelian\s+map|pulpen/i', $desc)) {
            $newKode = '5309001';
        }
        // 13. Sewa
        elseif (preg_match('/sewa\s+vo|biaya\s+sewa|sewa\s+booth/i', $desc)) {
            $newKode = '5311001';
        }

        if ($newKode === $r->kode_akun) {
            $unchangedCount++;
            continue;
        }

        // Determine new departemen_akun_id
        $newDeptAkunId = null;
        if (!empty($r->departemen_akun_id) && isset($deptAkunToDept[$r->departemen_akun_id])) {
            $deptId = $deptAkunToDept[$r->departemen_akun_id];
            $newAkun = $coaList[$newKode] ?? null;

            if ($newAkun) {
                $comboKey = $deptId . ':' . $newAkun->id;
                if (isset($deptAkunIndex[$comboKey])) {
                    $newDeptAkunId = $deptAkunIndex[$comboKey];
                } else {
                    // Create new assign in departemen_akuns
                    $newDaId = DB::table('departemen_akuns')->insertGetId([
                        'departemen_id' => $deptId,
                        'akun_id' => $newAkun->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $deptAkunIndex[$comboKey] = $newDaId;
                    $deptAkunToDept[$newDaId] = $deptId;
                    $newDeptAkunId = $newDaId;
                    $createdDeptAkunCount++;
                }
            }
        }

        DB::table('journal_entry_details')
            ->where('id', $r->id)
            ->update([
                'kode_akun' => $newKode,
                'departemen_akun_id' => $newDeptAkunId ?? $r->departemen_akun_id,
                'updated_at' => now()
            ]);

        $updatedCount++;
    }

    DB::commit();

    echo "=== EKSEKUSI BERHASIL SELESAI ===\n";
    echo "1. Transaksi diperbarui ke akun baru : {$updatedCount} baris\n";
    echo "2. Transaksi tetap di 5305001       : {$unchangedCount} baris\n";
    echo "3. Kombinasi Departemen-Akun baru    : {$createdDeptAkunCount} relasi ditambahkan ke departemen_akuns\n";
    echo "4. Total voucher totalan terhapus    : " . count($totalanJeIds) . " voucher\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

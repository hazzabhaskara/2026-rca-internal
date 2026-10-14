<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$totalanJeIds = [79786, 79790, 79794, 79801, 80996];

$coaList = DB::table('chart_of_accounts')->get()->keyBy('kode_akun');
$coaById = DB::table('chart_of_accounts')->get()->keyBy('id');

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

$toDelete = [];
$reclassified = [];
$stats = [];

foreach ($rows as $r) {
    if (in_array($r->journal_entry_id, $totalanJeIds)) {
        $toDelete[] = $r;
        continue;
    }

    $desc = strtolower(trim(($r->jed_comment ?? '') . ' ' . ($r->je_comment ?? '')));
    $newKode = '5305001'; // Default: Peralatan & Perlengkapan Operasional
    $catName = 'Tetap di Peralatan & Perlengkapan Operasional';

    // 1. Biaya Bank (Administrasi Bank)
    if (preg_match('/b\.\s*transfer\s+antar\s+bank|biaya\s+transfer|cetak\s+rek|cetak\s+koran|b\.\s*ganti\s+kartu|biaya\s+ganti\s+kartu|charge\s+kartu|insuf\s+fund|b\.\s*kartu|b\.\s*gagal\s+transaksi\s+kartu|biaya\s+transfer\s+online/i', $desc)) {
        $newKode = '5316002';
        $catName = 'Beban Administrasi Bank';
    }
    // 2. Kartu Ancol Karyawan
    elseif (preg_match('/kartu\s+ancol|kartu\s+bulanan|pengembalian\s+kartu\s+ancol/i', $desc)) {
        $newKode = '1106001';
        $catName = 'Piutang Karyawan - Kartu Ancol';
    }
    // 3. Setor Omset
    elseif (preg_match('/setor\s+omset|setoran\s+omset/i', $desc)) {
        $newKode = '1101011';
        $catName = 'Kas Setor Retail (Setor Omset)';
    }
    // 4. Listrik & Utilitas
    elseif (preg_match('/listrik\s+kost/i', $desc)) {
        $newKode = '5307004';
        $catName = 'Listrik Kost Karyawan';
    } elseif (preg_match('/listrik|token\s+pln|token\s+listrik|pln/i', $desc)) {
        $newKode = '5307001';
        $catName = 'Beban Listrik Operasional';
    } elseif (preg_match('/air|pdam/i', $desc)) {
        $newKode = '5307002';
        $catName = 'Beban Air';
    }
    // 5. Mutasi Bank & Pindah Dana
    elseif (preg_match('/keluar\s+ke\s+bni|transfer.*bni\s+pt\s+ops|transfer.*bni\s+rca|transfer.*rek\s+bni\s+rca|bni\s+2010790365/i', $desc)) {
        $newKode = '3105000';
        $catName = 'Rekonsiliasi (Keluar ke BNI)';
    } elseif (preg_match('/keluar\s+ke\s+mandiri|mandiri\s+rca|mandiri\s+pusat|mandiri\s+bdg/i', $desc)) {
        $newKode = '1102001';
        $catName = 'Mandiri Operasional (Keluar ke Mandiri)';
    } elseif (preg_match('/keluar\s+ke\s+bri/i', $desc)) {
        $newKode = '1102015';
        $catName = 'BRI Amel (Keluar ke BRI)';
    } elseif (preg_match('/keluar\s+ke\s+cash\s+ops|cash\s+ops\s*\(untuk\s+gajian\)/i', $desc)) {
        $newKode = '1101010';
        $catName = 'Kas Operasional Ancol (Keluar ke Cash Ops)';
    } elseif (preg_match('/transfer\s+bca.*firman/i', $desc)) {
        $newKode = '1101003';
        $catName = 'Kas Firman';
    } elseif (preg_match('/transfer\s+bca\s+evi|transfer\s+rek\s+bca\s+evi|transfer\s+evi\/kas/i', $desc)) {
        $newKode = '1102012';
        $catName = 'BCA Evi';
    } elseif (preg_match('/transfer.*yerri/i', $desc)) {
        $newKode = '1102010';
        $catName = 'BCA Yerri';
    } elseif (preg_match('/transfer.*amelia|transfer.*amelya|transfer.*rek\s+bca\s+amel|transfer.*rek\s+bni\s+amel/i', $desc)) {
        $newKode = '1101008';
        $catName = 'Kas Amel';
    }
    // 6. Setor ke Bandung / Pusat
    elseif (preg_match('/setor\s+cash\s+ke\s+bandung|setor\s+ke\s+bandung|keluar\s+ke\s+bandung|di\s+bawa\s+amel\s+untuk\s+ke\s+bdg|pusat/i', $desc)) {
        $newKode = '3105000';
        $catName = 'Rekonsiliasi (Setor ke Bandung / Pusat)';
    }
    // 7. Bahan Baku / Supplies Cetak Foto (Ribbon & Frame)
    elseif (preg_match('/ribbon|dnp|perdana\s+bangun\s+pusaka|syukran\s+misilu|dp\s+frame|pelunasan\s+frame|bayar\s+frame|pembelian\s+frame|kertas\s+newspaper/i', $desc)) {
        $newKode = '1110001';
        $catName = 'Persediaan Bahan Baku (Ribbon & Frame)';
    }
    // 8. Pemeliharaan & Perbaikan
    elseif (preg_match('/service\s+motor|servis\s+motor|pemeliharaan\s+kendaraan/i', $desc)) {
        $newKode = '5306002';
        $catName = 'Pemeliharaan Kendaraan';
    } elseif (preg_match('/service\s+kamera|service\s+printer|service\s+dnp|pemeliharaan\s+peralatan|ganti\s+layar\s+laptop/i', $desc)) {
        $newKode = '5306003';
        $catName = 'Pemeliharaan Peralatan';
    } elseif (preg_match('/perbaikan\s+booth|pemeliharaan\s+booth/i', $desc)) {
        $newKode = '5306001';
        $catName = 'Pemeliharaan Bangunan & Booth';
    }
    // 9. Iuran & Retribusi (Sampah & Kebersihan)
    elseif (preg_match('/sampah|kebersihan|iuran/i', $desc)) {
        $newKode = '5309006';
        $catName = 'Iuran & Retribusi';
    }
    // 10. IT, Software, Domain, Hosting
    elseif (preg_match('/rumah\s+web|langganan\s+server|server\s+np|server\s+fr|labtech|aplikasi|software|dslrbooth/i', $desc)) {
        $newKode = '5309010';
        $catName = 'Biaya Langganan Aplikasi & Software';
    }
    // 11. Fee & Imbalan Lain
    elseif (preg_match('/fee\s+komisaris|fee\s+admin|fee\s+rca|fee\s+pak\s+jaya/i', $desc)) {
        $newKode = '5301007';
        $catName = 'Fee & Imbalan Lain';
    }
    // 12. ATK & Fotokopi
    elseif (preg_match('/atk|fotokopi|buku\s+laporan|pembelian\s+map|pulpen/i', $desc)) {
        $newKode = '5309001';
        $catName = 'ATK, Fotokopi & Barang Cetakan';
    }
    // 13. Sewa
    elseif (preg_match('/sewa\s+vo|biaya\s+sewa|sewa\s+booth/i', $desc)) {
        $newKode = '5311001';
        $catName = 'Sewa Tanah dan/atau Bangunan';
    }

    if (!isset($stats[$catName])) {
        $stats[$catName] = [
            'kode_akun' => $newKode,
            'nama_akun' => $coaList[$newKode]->nama_akun ?? 'UNKNOWN',
            'count' => 0,
            'total_debit' => 0.0,
            'samples' => []
        ];
    }
    $stats[$catName]['count']++;
    $stats[$catName]['total_debit'] += (float)$r->debits;
    if (count($stats[$catName]['samples']) < 2) {
        $stats[$catName]['samples'][] = "[{$r->tanggal}] Rp " . number_format($r->debits, 0) . " - " . ($r->jed_comment ?: $r->je_comment);
    }

    $reclassified[] = [
        'id' => $r->id,
        'journal_entry_id' => $r->journal_entry_id,
        'old_kode' => $r->kode_akun,
        'new_kode' => $newKode,
        'debit' => $r->debits,
        'desc' => $r->jed_comment ?: $r->je_comment
    ];
}

echo "=== DRY-RUN SIMULATION RESULTS ===\n";
echo "Total records evaluated: " . $rows->count() . "\n";
echo "Total vouchers marked for deletion (totalan): " . count($toDelete) . " (from 5 JE)\n";
$sumDelete = array_sum(array_map(fn($x) => (float)$x->debits, $toDelete));
echo "Total Nominal deleted: Rp " . number_format($sumDelete, 2) . "\n\n";

echo "=== BREAKDOWN BY NEW COA CLASSIFICATION ===\n";
uasort($stats, fn($a, $b) => $b['total_debit'] <=> $a['total_debit']);
$no = 0;
foreach ($stats as $cat => $st) {
    $no++;
    echo sprintf("#%02d | %-45s | %s (%s)\n     Trx: %4d | Total Debit: Rp %15s\n     Sample: %s\n\n",
        $no,
        $cat,
        $st['kode_akun'],
        $st['nama_akun'],
        $st['count'],
        number_format($st['total_debit'], 2),
        implode(' ; ', $st['samples'])
    );
}

$sumReclassified = array_sum(array_map(fn($x) => $x['total_debit'], $stats));
echo "Total Reclassified Debit: Rp " . number_format($sumReclassified, 2) . "\n";
echo "Total Original Debit    : Rp " . number_format($sumDelete + $sumReclassified, 2) . "\n";

<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->whereIn('jed.kode_akun', ['5305000', '5305001'])
    ->whereYear('je.tanggal', 2026)
    ->select([
        'jed.id',
        'je.id as je_id',
        'je.source',
        'je.tanggal',
        'jed.kode_akun',
        'jed.comment as jed_comment',
        'je.comment as je_comment',
        'jed.debits',
        'jed.credits',
        'jed.departemen_akun_id'
    ])
    ->get();

$categories = [
    'antar_bank' => [
        'label' => 'Mutasi / Transfer Antar Bank / Kas',
        'count' => 0,
        'debit' => 0,
        'credit' => 0,
        'samples' => []
    ],
    'setor_omset' => [
        'label' => 'Setor Omset / Omzet',
        'count' => 0,
        'debit' => 0,
        'credit' => 0,
        'samples' => []
    ],
    'listrik_air_internet' => [
        'label' => 'Listrik / Token / Air / Wifi',
        'count' => 0,
        'debit' => 0,
        'credit' => 0,
        'samples' => []
    ],
    'biaya_bank_kartu' => [
        'label' => 'Biaya Bank / Cetak Koran / Ganti Kartu / Adm / Trf',
        'count' => 0,
        'debit' => 0,
        'credit' => 0,
        'samples' => []
    ],
    'gaji_makan_karyawan' => [
        'label' => 'Gaji / Uang Makan / HR / Karyawan',
        'count' => 0,
        'debit' => 0,
        'credit' => 0,
        'samples' => []
    ],
    'peralatan_perlengkapan' => [
        'label' => 'Peralatan / Perlengkapan Fisik / Sparepart / ATK',
        'count' => 0,
        'debit' => 0,
        'credit' => 0,
        'samples' => []
    ],
    'sewa_perizinan_maintenance' => [
        'label' => 'Pemeliharaan / Sewa / Iuran / Sampah',
        'count' => 0,
        'debit' => 0,
        'credit' => 0,
        'samples' => []
    ],
    'lainnya' => [
        'label' => 'Lain-lain / Perlu Verifikasi',
        'count' => 0,
        'debit' => 0,
        'credit' => 0,
        'samples' => []
    ]
];

foreach ($rows as $r) {
    $text = strtolower(($r->jed_comment ?? '') . ' ' . ($r->je_comment ?? ''));
    $cat = 'lainnya';

    if (preg_match('/setor\s+omset|setoran\s+omset|omset|omzet/i', $text)) {
        $cat = 'setor_omset';
    } elseif (preg_match('/keluar\s+ke\s+(bni|bri|bca|mandiri|cash)|transfer\s+(ke|antar|bca|bni|bri|mandiri)|mutasi|pindah\s+dana/i', $text)) {
        $cat = 'antar_bank';
    } elseif (preg_match('/listrik|pln|token|air|pdam|indihome|wifi|internet/i', $text)) {
        $cat = 'listrik_air_internet';
    } elseif (preg_match('/biaya\s+transfer|b\.\s*transfer|rekening\s+koran|cetak\s+koran|ganti\s+kartu|charge\s+kartu|adm\s+bank|insuf\s+fund/i', $text)) {
        $cat = 'biaya_bank_kartu';
    } elseif (preg_match('/gaji|gajian|u\.\s*mkn|uang\s+makan|lembur|thr|kas\s+ops\s*\(untuk\s+gajian\)/i', $text)) {
        $cat = 'gaji_makan_karyawan';
    } elseif (preg_match('/service|servis|pemeliharaan|perbaikan|sampah|iuran|kebersihan|sewa/i', $text)) {
        $cat = 'sewa_perizinan_maintenance';
    } elseif (preg_match('/beli|pembelian|kabel|baut|lem|cat|kuas|pipa|tplink|tp-link|kertas|atk|lakban|plastik|banner|spanduk/i', $text)) {
        $cat = 'peralatan_perlengkapan';
    }

    $categories[$cat]['count']++;
    $categories[$cat]['debit'] += (float)$r->debits;
    $categories[$cat]['credit'] += (float)$r->credits;
    if (count($categories[$cat]['samples']) < 5) {
        $categories[$cat]['samples'][] = [
            'id' => $r->id,
            'tanggal' => $r->tanggal,
            'debit' => $r->debits,
            'desc' => $r->jed_comment ?: $r->je_comment
        ];
    }
}

echo "=== BREAKDOWN OF 2354 TRANSACTIONS IN 5305000/5305001 (2026) ===\n\n";
foreach ($categories as $k => $c) {
    echo "Category: {$c['label']} ($k)\n";
    echo "Count: {$c['count']} | Debit: " . number_format($c['debit'], 2) . " | Credit: " . number_format($c['credit'], 2) . "\n";
    echo "Samples:\n";
    foreach ($c['samples'] as $s) {
        echo "  - [{$s['tanggal']}] ID: {$s['id']} | Rp " . number_format($s['debit'], 0) . " | {$s['desc']}\n";
    }
    echo "---------------------------------------------------------\n";
}

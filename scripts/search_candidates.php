<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$keywords = [
    'kas', 'bank', 'bca', 'mandiri', 'bni', 'bri',
    'omset', 'pendapatan',
    'listrik', 'air', 'telepon', 'utilitas', 'internet',
    'administrasi', 'transfer',
    'peralatan', 'perlengkapan', 'atk',
    'pemeliharaan', 'perbaikan',
    'kebersihan', 'keamanan', 'sampah',
    'gaji', 'upah', 'makan', 'komisaris',
    'kartu', 'tiket', 'ancol', 'retribusi',
    'sewa', 'hosting', 'domain', 'langganan'
];

$coas = DB::table('chart_of_accounts')
    ->orderBy('kode_akun')
    ->get(['id', 'kode_akun', 'nama_akun', 'tipe_akun']);

echo "=== SEARCH CANDIDATE ACCOUNTS IN COA ===\n";
foreach ($coas as $c) {
    foreach ($keywords as $kw) {
        if (stripos($c->nama_akun, $kw) !== false) {
            echo "{$c->kode_akun}\t{$c->tipe_akun}\t{$c->nama_akun}\n";
            break;
        }
    }
}

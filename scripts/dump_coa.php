<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$coa = DB::table('chart_of_accounts')
    ->orderBy('kode_akun')
    ->get(['id', 'kode_akun', 'nama_akun', 'tipe_akun']);

$file = fopen(__DIR__ . '/coa_list.txt', 'w');
foreach ($coa as $c) {
    fwrite($file, "{$c->id}\t{$c->kode_akun}\t{$c->nama_akun}\t{$c->tipe_akun}\n");
}
fclose($file);

echo "Total COA: " . $coa->count() . " written to coa_list.txt\n";

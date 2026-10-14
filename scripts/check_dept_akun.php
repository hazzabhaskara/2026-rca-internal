<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$c = DB::table('journal_entry_details')->whereNotNull('departemen_akun_id')->count();
$cNull = DB::table('journal_entry_details')->whereNull('departemen_akun_id')->count();
echo "JED with departemen_akun_id: {$c} | with NULL: {$cNull}\n";

$sample = DB::table('journal_entry_details as jed')
    ->join('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')
    ->join('departements as d', 'd.id', '=', 'da.departemen_id')
    ->whereIn('jed.kode_akun', ['5305000', '5305001'])
    ->take(5)
    ->get(['jed.id', 'jed.kode_akun', 'da.departemen_id', 'd.deskripsi']);

echo "Sample dept for 5305001:\n";
foreach ($sample as $s) {
    echo "JED {$s->id} | Akun: {$s->kode_akun} | Dept ID: {$s->departemen_id} ({$s->deskripsi})\n";
}

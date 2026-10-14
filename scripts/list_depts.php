<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$depts = DB::table('departements')->get();
foreach ($depts as $d) {
    echo "ID: {$d->id} | Kode: '{$d->kode}' | Deskripsi: '{$d->deskripsi}'\n";
}

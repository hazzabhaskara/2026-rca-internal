<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$deps = \App\Departement::all();
echo "DEPARTEMENTS:\n";
foreach($deps as $d) {
    echo $d->kode . " => " . $d->deskripsi . "\n";
}

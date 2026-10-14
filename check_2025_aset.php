<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Query rembulancitraabadi_2025_db
$cutOff = '2025-12-31';
$res = DB::connection('mysql')->select("
    SELECT SUM(jed.debits - jed.credits) as total_aset
    FROM rembulancitraabadi_2025_db.journal_entry_details jed
    JOIN rembulancitraabadi_2025_db.journal_entries je ON je.id = jed.journal_entry_id
    JOIN rembulancitraabadi_2025_db.chart_of_accounts coa ON coa.kode_akun = jed.kode_akun
    WHERE coa.tipe_akun = 'Aset'
      AND je.tanggal <= '{$cutOff}'
");

echo "2025 DB Total Aset on 2025-12-31: " . sprintf('%.2f', $res[0]->total_aset ?? 0) . "\n";

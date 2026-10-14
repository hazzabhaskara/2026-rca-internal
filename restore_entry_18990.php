<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$backupFile = __DIR__ . '/database_backups/backup_rembulancitraabadi_2026_db_PRE_UPGRADE_20260918_023400.sql';
$f = fopen($backupFile, 'r');
$insideDetails = false;
$detailTuples = [];

while (($line = fgets($f)) !== false) {
    if (strpos($line, 'INSERT INTO `journal_entry_details`') !== false) {
        $insideDetails = true;
    }
    if ($insideDetails) {
        // match: (id, 18990, departemen_akun_id, 'kode_akun', debits, credits, 'comment', status, 'created_at', 'updated_at', project_id, pajak, penyesuaian_fiskal, kode_fiscal)
        preg_match_all('/\((\d+),18990,([^)]+)\)/', $line, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $detailTuples[] = '(' . $m[1] . ', 18990, ' . $m[2] . ')';
        }
        if (strpos($line, ';') !== false && !empty($matches)) {
            // may continue if multiple INSERT lines
        }
    }
}
fclose($f);

echo "Found " . count($detailTuples) . " details for entry 18990 in backup.\n";

if (count($detailTuples) > 0) {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    // Restore entry header
    DB::table('journal_entries')->where('id', 18990)->update([
        'source' => 'SA2026-0001',
        'tanggal' => '2025-12-31',
        'comment' => 'SALDO AWAL PER 31 DESEMBER 2025 - PUSAT BANDUNG',
        'updated_at' => '2026-09-15 07:31:17'
    ]);

    // Delete corrupted details
    DB::table('journal_entry_details')->where('journal_entry_id', 18990)->delete();

    // Insert back the original details
    $chunks = array_chunk($detailTuples, 50);
    foreach ($chunks as $chunk) {
        $sql = "INSERT INTO `journal_entry_details` VALUES " . implode(',', $chunk);
        DB::statement($sql);
    }
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    echo "Successfully restored entry 18990 and its " . count($detailTuples) . " details!\n";
}

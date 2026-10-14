<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

ini_set('memory_limit', '2048M');
set_time_limit(0);

$fileUser = 'D:/Hazza/REMBULAN CITRA ABADI/2026/2026-RCA-INTERNAL/Import_Transaksi_2026_Pasca_Adjustment.xlsx';
$fileOri  = __DIR__ . '/../data file excel/Import_Transaksi_2026_Pasca_Adjustment.xlsx';

echo "Comparing User File vs Original File...\n";

$sheetUser = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileUser)->getActiveSheet();
$sheetOri  = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileOri)->getActiveSheet();

$maxUser = $sheetUser->getHighestRow();
$maxOri  = $sheetOri->getHighestRow();

echo "Rows: User={$maxUser}, Ori={$maxOri}\n";

// Let's check headers
$headUser = [];
$headOri  = [];
foreach (range('A', 'I') as $col) {
    $headUser[$col] = $sheetUser->getCell("{$col}1")->getValue();
    $headOri[$col]  = $sheetOri->getCell("{$col}1")->getValue();
}

echo "User Headers: " . json_encode($headUser) . "\n";
echo "Ori Headers : " . json_encode($headOri) . "\n";

// Count differences
$diffs = [];
for ($r = 2; $r <= min($maxUser, $maxOri); $r++) {
    $uSource = trim((string)$sheetUser->getCell("A{$r}")->getValue());
    $uKode   = trim((string)$sheetUser->getCell("E{$r}")->getValue());
    $uNama   = trim((string)$sheetUser->getCell("F{$r}")->getValue());

    $oSource = trim((string)$sheetOri->getCell("A{$r}")->getValue());
    $oKode   = trim((string)$sheetOri->getCell("D{$r}")->getValue());
    $oNama   = trim((string)$sheetOri->getCell("E{$r}")->getValue());

    if ($uKode !== $oKode) {
        $diffs[] = [
            'row' => $r,
            'source' => $uSource,
            'user_kode' => $uKode,
            'user_nama' => $uNama,
            'ori_kode' => $oKode,
            'ori_nama' => $oNama,
        ];
    }
}

echo "Total differences in kode_akun between user and original: " . count($diffs) . "\n";
foreach (array_slice($diffs, 0, 20) as $d) {
    echo "Baris {$d['row']} [{$d['source']}]: User='{$d['user_kode']}' ({$d['user_nama']}) vs Ori='{$d['ori_kode']}' ({$d['ori_nama']})\n";
}

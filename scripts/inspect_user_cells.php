<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$fileUser = 'D:/Hazza/REMBULAN CITRA ABADI/2026/2026-RCA-INTERNAL/Import_Transaksi_2026_Pasca_Adjustment.xlsx';
$spreadsheetUser = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileUser);
$sheet = $spreadsheetUser->getActiveSheet();

echo "=== FIRST 10 ROWS OF USER MODIFIED EXCEL ===\n";
for ($r = 1; $r <= 10; $r++) {
    $rowVals = [];
    foreach (range('A', 'I') as $col) {
        $cell = $sheet->getCell("{$col}{$r}");
        $val = $cell->getValue();
        $formatted = $cell->getFormattedValue();
        $dataType = $cell->getDataType();
        $rowVals[] = "{$col}: [val='{$val}', fmt='{$formatted}', type='{$dataType}']";
    }
    echo "Row {$r}:\n  " . implode("\n  ", $rowVals) . "\n\n";
}

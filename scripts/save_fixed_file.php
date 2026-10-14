<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

ini_set('memory_limit', '2048M');
set_time_limit(0);

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$fileUser = 'D:/Hazza/REMBULAN CITRA ABADI/2026/2026-RCA-INTERNAL/Import_Transaksi_2026_Pasca_Adjustment.xlsx';
$targetXlsx = 'D:/Hazza/REMBULAN CITRA ABADI/2026/2026-RCA-INTERNAL/Import_Transaksi_2026_Pasca_Adjustment_FIXED.xlsx';
$targetCsv  = 'D:/Hazza/REMBULAN CITRA ABADI/2026/2026-RCA-INTERNAL/Import_Transaksi_2026_Pasca_Adjustment_FIXED.csv';
$copyXlsx   = __DIR__ . '/../data file excel/Import_Transaksi_2026_Pasca_Adjustment_FIXED.xlsx';

echo "1. Membaca file pengguna (read-only stream)...\n";
$spreadsheet = IOFactory::load($fileUser);
$sheet = $spreadsheet->getActiveSheet();

echo "2. Memperbaiki E3937 (kode_akun = 3105000)...\n";
$sheet->setCellValueExplicit('E3937', '3105000', DataType::TYPE_STRING);
$sheet->setCellValueExplicit('F3937', 'REKONSILIASI', DataType::TYPE_STRING);

echo "3. Memastikan Header D1 adalah 'departemen'...\n";
$sheet->setCellValue('D1', 'departemen');

echo "4. Menyimpan ke file FIXED: {$targetXlsx}...\n";
$writerXlsx = new Xlsx($spreadsheet);
$writerXlsx->save($targetXlsx);
copy($targetXlsx, $copyXlsx);
echo "   ✅ File XLSX FIXED berhasil disimpan.\n";

echo "5. Menyimpan ke CSV FIXED: {$targetCsv}...\n";
$writerCsv = new Csv($spreadsheet);
$writerCsv->setDelimiter(',');
$writerCsv->setEnclosure('"');
$writerCsv->setLineEnding("\r\n");
$writerCsv->setSheetIndex(0);
$writerCsv->save($targetCsv);
echo "   ✅ File CSV FIXED berhasil disimpan.\n";

echo "\nSELESAI LENGKAP!\n";

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

$fileXlsx = 'D:/Hazza/REMBULAN CITRA ABADI/2026/2026-RCA-INTERNAL/Import_Transaksi_2026_Pasca_Adjustment.xlsx';
$copyXlsx = __DIR__ . '/../data file excel/Import_Transaksi_2026_Pasca_Adjustment.xlsx';
$fileCsv  = 'D:/Hazza/REMBULAN CITRA ABADI/2026/2026-RCA-INTERNAL/Import_Transaksi_2026_Pasca_Adjustment.csv';

echo "1. Membuka file XLSX pengguna...\n";
$spreadsheet = IOFactory::load($fileXlsx);
$sheet = $spreadsheet->getActiveSheet();

// 1. Perbaiki Baris 3937 kolom E (kode_akun)
echo "2. Memperbaiki E3937 (kode_akun untuk voucher BNI7884-260128-006)...\n";
$sheet->setCellValueExplicit('E3937', '3105000', DataType::TYPE_STRING);
$sheet->setCellValueExplicit('F3937', 'REKONSILIASI', DataType::TYPE_STRING);

// 2. Perbaiki Header D1
echo "3. Memastikan Header D1 adalah 'departemen'...\n";
$sheet->setCellValue('D1', 'departemen');

// 3. Simpan XLSX
echo "4. Menyimpan pembaruan ke {$fileXlsx}...\n";
$writerXlsx = new Xlsx($spreadsheet);
$writerXlsx->save($fileXlsx);
copy($fileXlsx, $copyXlsx);
echo "   ✅ File XLSX berhasil diperbarui di kedua lokasi.\n";

// 4. Simpan CSV
echo "5. Menyimpan pembaruan ke CSV {$fileCsv}...\n";
$writerCsv = new Csv($spreadsheet);
$writerCsv->setDelimiter(',');
$writerCsv->setEnclosure('"');
$writerCsv->setLineEnding("\r\n");
$writerCsv->setSheetIndex(0);
$writerCsv->save($fileCsv);
echo "   ✅ File CSV pendamping berhasil diperbarui.\n";

echo "\nSELESAI LENGKAP & SIAP DIUPLOAD ULANG!\n";

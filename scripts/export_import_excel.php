<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

ini_set('memory_limit', '2048M');
set_time_limit(0);

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

echo "1. Mengambil seluruh data transaksi 2026 pasca adjustment...\n";

// Preload COA and Departemen
$coaList = DB::table('chart_of_accounts')->get()->keyBy('kode_akun');
$deptList = DB::table('departements')->get()->keyBy('id');
$deptAkunList = DB::table('departemen_akuns')->get()->keyBy('id');

$rows = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->whereYear('je.tanggal', 2026)
    ->select([
        'je.id as je_id',
        'je.source',
        'je.tanggal',
        'je.comment as je_comment',
        'jed.id as jed_id',
        'jed.departemen_akun_id',
        'jed.kode_akun',
        'jed.debits',
        'jed.credits',
        'jed.comment as jed_comment'
    ])
    ->orderBy('je.tanggal', 'asc')
    ->orderBy('je.id', 'asc')
    ->orderBy('jed.id', 'asc')
    ->get();

$totalRows = $rows->count();
echo "   Total baris transaksi: {$totalRows}\n\n";

echo "2. Membuat file Excel (Spreadsheet)...\n";
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Transaksi 2026');

// Header row sesuai format JournalEntryImport
$headers = [
    'source',
    'tanggal',
    'departemen',
    'kode_akun',
    'nama_akun',
    'debit',
    'kredit',
    'comment_line',
    'specpose'
];

$colIndex = 1;
foreach ($headers as $h) {
    $sheet->setCellValueByColumnAndRow($colIndex++, 1, $h);
}

// Style header row
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => '2E4053']
    ]
];
$sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

$rowIndex = 2;
foreach ($rows as $r) {
    $deptName = '';
    if (!empty($r->departemen_akun_id) && isset($deptAkunList[$r->departemen_akun_id])) {
        $da = $deptAkunList[$r->departemen_akun_id];
        if (isset($deptList[$da->departemen_id])) {
            $deptName = $deptList[$da->departemen_id]->deskripsi;
        }
    }

    $namaAkun = $coaList[$r->kode_akun]->nama_akun ?? '';
    $commentLine = $r->jed_comment ?: $r->je_comment;

    $sheet->setCellValueExplicitByColumnAndRow(1, $rowIndex, $r->source, DataType::TYPE_STRING);
    $sheet->setCellValueExplicitByColumnAndRow(2, $rowIndex, $r->tanggal, DataType::TYPE_STRING);
    $sheet->setCellValueExplicitByColumnAndRow(3, $rowIndex, $deptName, DataType::TYPE_STRING);
    $sheet->setCellValueExplicitByColumnAndRow(4, $rowIndex, $r->kode_akun, DataType::TYPE_STRING);
    $sheet->setCellValueExplicitByColumnAndRow(5, $rowIndex, $namaAkun, DataType::TYPE_STRING);
    $sheet->setCellValueByColumnAndRow(6, $rowIndex, (float)$r->debits);
    $sheet->setCellValueByColumnAndRow(7, $rowIndex, (float)$r->credits);
    $sheet->setCellValueExplicitByColumnAndRow(8, $rowIndex, $commentLine, DataType::TYPE_STRING);
    $sheet->setCellValueExplicitByColumnAndRow(9, $rowIndex, $r->je_comment, DataType::TYPE_STRING);

    $rowIndex++;

    if ($rowIndex % 5000 === 0) {
        echo "   -> Diproses {$rowIndex} / {$totalRows} baris...\n";
    }
}

// Auto size columns
foreach (range(1, 9) as $c) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
}

// Number format for debit & credit columns (F & G)
$sheet->getStyle('F2:G' . ($rowIndex - 1))->getNumberFormat()->setFormatCode('#,##0.00');

echo "3. Menyimpan file XLSX...\n";
$targetPath1 = 'D:/Hazza/REMBULAN CITRA ABADI/2026/2026-RCA-INTERNAL/Import_Transaksi_2026_Pasca_Adjustment.xlsx';
$targetPath2 = __DIR__ . '/../data file excel/Import_Transaksi_2026_Pasca_Adjustment.xlsx';

$writer = new Xlsx($spreadsheet);
$writer->save($targetPath1);
echo "   ✅ File berhasil disimpan di: {$targetPath1}\n";

copy($targetPath1, $targetPath2);
echo "   ✅ Salinan file berhasil disimpan di: {$targetPath2}\n";

// Also export CSV companion for ultra fast upload option
echo "4. Menyimpan file CSV pendamping...\n";
$csvPath1 = 'D:/Hazza/REMBULAN CITRA ABADI/2026/2026-RCA-INTERNAL/Import_Transaksi_2026_Pasca_Adjustment.csv';
$csvWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
$csvWriter->setDelimiter(',');
$csvWriter->setEnclosure('"');
$csvWriter->setLineEnding("\r\n");
$csvWriter->setSheetIndex(0);
$csvWriter->save($csvPath1);
echo "   ✅ File CSV berhasil disimpan di: {$csvPath1}\n";

echo "\nSELESAI LENGKAP!\n";

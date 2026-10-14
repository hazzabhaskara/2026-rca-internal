<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

ini_set('memory_limit', '2048M');
set_time_limit(0);

$fileUser = 'D:/Hazza/REMBULAN CITRA ABADI/2026/2026-RCA-INTERNAL/Import_Transaksi_2026_Pasca_Adjustment.xlsx';
$spreadsheetUser = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileUser);
$sheetUser = $spreadsheetUser->getActiveSheet();
$maxRow = $sheetUser->getHighestRow();

echo "Mencari voucher 'BNI7884-260128-006' dan semua baris dengan kode_akun kosong...\n";

$blankAccounts = [];
for ($r = 1; $r <= $maxRow; $r++) {
    $src = trim((string)$sheetUser->getCell("A{$r}")->getValue());
    $tgl = trim((string)$sheetUser->getCell("B{$r}")->getValue());
    // In user file, column E is kode_akun
    $kode = trim((string)$sheetUser->getCell("E{$r}")->getValue());
    $nama = trim((string)$sheetUser->getCell("F{$r}")->getValue());
    $deb = trim((string)$sheetUser->getCell("G{$r}")->getValue());
    $kred = trim((string)$sheetUser->getCell("H{$r}")->getValue());
    $comm = trim((string)$sheetUser->getCell("C{$r}")->getValue());

    if ($src === 'BNI7884-260128-006') {
        echo "Baris {$r} [BNI7884-260128-006]: Kode='{$kode}', Nama='{$nama}', Deb='{$deb}', Kred='{$kred}', Desc='{$comm}'\n";
    }

    if ($r > 1 && empty($kode) && (!empty($src) || !empty($tgl))) {
        $blankAccounts[] = [
            'row' => $r,
            'source' => $src,
            'tanggal' => $tgl,
            'desc' => $comm,
            'debit' => $deb,
            'kredit' => $kred
        ];
    }
}

echo "\nTotal baris dengan kode akun kosong di file: " . count($blankAccounts) . "\n";
foreach ($blankAccounts as $b) {
    echo "Baris {$b['row']} | Source: {$b['source']} | Tgl: {$b['tanggal']} | Deb: {$b['debit']} | Kred: {$b['kredit']} | Desc: {$b['desc']}\n";
}

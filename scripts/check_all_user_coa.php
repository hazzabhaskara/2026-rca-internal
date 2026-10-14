<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

ini_set('memory_limit', '2048M');
set_time_limit(0);

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;

$fileUser = 'D:/Hazza/REMBULAN CITRA ABADI/2026/2026-RCA-INTERNAL/Import_Transaksi_2026_Pasca_Adjustment.xlsx';
$spreadsheetUser = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileUser);
$sheetUser = $spreadsheetUser->getActiveSheet();
$maxRow = $sheetUser->getHighestRow();

$coaByCode = ChartOfAccount::get(['id', 'kode_akun', 'nama_akun'])->keyBy('kode_akun');

echo "Memeriksa seluruh kode akun di file pengguna (Total {$maxRow} baris)...\n";
$missingCoa = [];
$blankCoa = [];
$uniqueCodes = [];

for ($r = 2; $r <= $maxRow; $r++) {
    $src = trim((string)$sheetUser->getCell("A{$r}")->getValue());
    $kode = trim((string)$sheetUser->getCell("E{$r}")->getValue()); // Column E is kode_akun
    $nama = trim((string)$sheetUser->getCell("F{$r}")->getValue());

    if (empty($src)) continue;

    if (empty($kode)) {
        $blankCoa[] = "Baris {$r} [Source: {$src}]: Nama='{$nama}'";
    } else {
        $uniqueCodes[$kode] = true;
        if (!$coaByCode->has($kode)) {
            $missingCoa[$kode][] = "Baris {$r} [{$src}]: Nama='{$nama}'";
        }
    }
}

echo "Total kode unik digunakan: " . count($uniqueCodes) . "\n";
echo "Total baris dengan kode kosong: " . count($blankCoa) . "\n";
foreach ($blankCoa as $b) echo "  - {$b}\n";

echo "Total kode akun tidak terdaftar di COA: " . count($missingCoa) . "\n";
foreach ($missingCoa as $k => $rows) {
    echo "  - Kode '{$k}' (" . count($rows) . " baris). Contoh: " . $rows[0] . "\n";
}

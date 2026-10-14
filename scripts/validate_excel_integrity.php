<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;

echo "=== VALIDASI KELAYAKAN IMPORT EXCEL ===\n";

$coaByCode = ChartOfAccount::get(['id', 'kode_akun', 'nama_akun'])->keyBy('kode_akun');
$allDepartemen = Departement::all(['id', 'kode', 'deskripsi']);
$departemenByKode = $allDepartemen->keyBy(fn($d) => strtoupper(trim($d->kode ?? '')));
$departemenByDeskripsi = $allDepartemen->keyBy(fn($d) => strtoupper(trim($d->deskripsi ?? '')));
$deptAkunIndex = DepartemenAkun::all(['id', 'departemen_id', 'akun_id'])
    ->keyBy(fn($da) => $da->departemen_id . ':' . $da->akun_id);

$filePath = 'D:/Hazza/REMBULAN CITRA ABADI/2026/2026-RCA-INTERNAL/Import_Transaksi_2026_Pasca_Adjustment.xlsx';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$highestRow = $sheet->getHighestRow();

echo "Total baris di Excel: {$highestRow} (termasuk 1 baris header)\n";

$errors = [];
$voucherTotals = []; // [voucher_key => ['deb' => 0, 'cred' => 0]]

for ($row = 2; $row <= $highestRow; $row++) {
    $source     = trim($sheet->getCell("A{$row}")->getValue() ?? '');
    $tanggal    = trim($sheet->getCell("B{$row}")->getValue() ?? '');
    $departemen = trim($sheet->getCell("C{$row}")->getValue() ?? '');
    $kode_akun  = trim($sheet->getCell("D{$row}")->getValue() ?? '');
    $debit      = (float)$sheet->getCell("F{$row}")->getValue();
    $kredit     = (float)$sheet->getCell("G{$row}")->getValue();

    $voucherKey = "{$source}|{$tanggal}";
    if (!isset($voucherTotals[$voucherKey])) {
        $voucherTotals[$voucherKey] = ['deb' => 0.0, 'cred' => 0.0];
    }
    $voucherTotals[$voucherKey]['deb'] += $debit;
    $voucherTotals[$voucherKey]['cred'] += $kredit;

    // Validate kode_akun
    if (!$coaByCode->has($kode_akun)) {
        $errors[] = "Row {$row}: Kode akun '{$kode_akun}' tidak ada di COA.";
    }

    // Validate departemen
    if (!empty($departemen)) {
        $deptKey = strtoupper($departemen);
        $dept = $departemenByKode->get($deptKey) ?? $departemenByDeskripsi->get($deptKey);
        if (!$dept) {
            $errors[] = "Row {$row}: Departemen '{$departemen}' tidak ditemukan.";
        } elseif ($coaByCode->has($kode_akun)) {
            $akun = $coaByCode->get($kode_akun);
            $idx = $dept->id . ':' . $akun->id;
            if (!$deptAkunIndex->has($idx)) {
                $errors[] = "Row {$row}: Kombinasi Dept '{$departemen}' + Akun '{$kode_akun}' belum ada di departemen_akuns.";
            }
        }
    }

    if (count($errors) > 10) {
        echo "Ditemukan lebih dari 10 error validasi, menghentikan pengecekan...\n";
        break;
    }
}

// Check voucher balances
$unbalancedVouchers = 0;
foreach ($voucherTotals as $k => $v) {
    if (abs($v['deb'] - $v['cred']) > 0.01) {
        $unbalancedVouchers++;
        if ($unbalancedVouchers <= 5) {
            echo "Voucher {$k} tidak seimbang! Deb: {$v['deb']} | Cred: {$v['cred']}\n";
        }
    }
}

echo "\nHASIL UJI INTEGRITAS FILE IMPORT EXCEL:\n";
echo "1. Error baris (COA / Departemen): " . count($errors) . "\n";
if (count($errors) > 0) {
    foreach ($errors as $e) echo "   - {$e}\n";
} else {
    echo "   ✅ Semua kode akun & kombinasi departemen 100% VALID dan terdaftar di master data!\n";
}

echo "2. Voucher tidak balance: {$unbalancedVouchers}\n";
if ($unbalancedVouchers === 0) {
    echo "   ✅ Semua voucher ({$sheet->getHighestRow()} baris / " . count($voucherTotals) . " voucher) 100% BALANCED!\n";
}

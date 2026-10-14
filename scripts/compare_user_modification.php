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

echo "=== MEMERIKSA FILE MODIFIKASI PENGGUNA ===\n";

$fileUser = 'D:/Hazza/REMBULAN CITRA ABADI/2026/2026-RCA-INTERNAL/Import_Transaksi_2026_Pasca_Adjustment.xlsx';
$fileOri  = __DIR__ . '/../data file excel/Import_Transaksi_2026_Pasca_Adjustment.xlsx';

$coaByCode = ChartOfAccount::get(['id', 'kode_akun', 'nama_akun'])->keyBy('kode_akun');
$allDepartemen = Departement::all(['id', 'kode', 'deskripsi']);
$departemenByKode = $allDepartemen->keyBy(fn($d) => strtoupper(trim($d->kode ?? '')));
$departemenByDeskripsi = $allDepartemen->keyBy(fn($d) => strtoupper(trim($d->deskripsi ?? '')));
$deptAkunIndex = DepartemenAkun::all(['id', 'departemen_id', 'akun_id'])
    ->keyBy(fn($da) => $da->departemen_id . ':' . $da->akun_id);

echo "Loading file pengguna: {$fileUser}...\n";
$spreadsheetUser = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileUser);
$sheetUser = $spreadsheetUser->getActiveSheet();
$maxRowUser = $sheetUser->getHighestRow();
echo "Total baris di file pengguna: {$maxRowUser}\n";

$missingCoa = [];
$missingDept = [];
$missingDeptAkun = [];
$invalidRows = [];
$voucherTotals = [];

for ($row = 2; $row <= $maxRowUser; $row++) {
    $source     = trim((string)$sheetUser->getCell("A{$row}")->getValue());
    $tanggal    = trim((string)$sheetUser->getCell("B{$row}")->getValue());
    $departemen = trim((string)$sheetUser->getCell("C{$row}")->getValue());
    $kode_akun  = trim((string)$sheetUser->getCell("D{$row}")->getValue());
    $nama_akun  = trim((string)$sheetUser->getCell("E{$row}")->getValue());
    $debitVal   = $sheetUser->getCell("F{$row}")->getValue();
    $kreditVal  = $sheetUser->getCell("G{$row}")->getValue();
    $debit      = (float)str_replace([',', ' '], '', (string)$debitVal);
    $kredit     = (float)str_replace([',', ' '], '', (string)$kreditVal);

    if (empty($source) && empty($tanggal)) {
        continue;
    }

    $voucherKey = "{$source}|{$tanggal}";
    if (!isset($voucherTotals[$voucherKey])) {
        $voucherTotals[$voucherKey] = ['deb' => 0.0, 'cred' => 0.0, 'first_row' => $row];
    }
    $voucherTotals[$voucherKey]['deb'] += $debit;
    $voucherTotals[$voucherKey]['cred'] += $kredit;

    // Check kode_akun
    if (empty($kode_akun)) {
        $invalidRows[] = "Row {$row}: Kode akun KOSONG. (Source: {$source})";
    } elseif (!$coaByCode->has($kode_akun)) {
        if (!isset($missingCoa[$kode_akun])) {
            $missingCoa[$kode_akun] = ['count' => 0, 'rows' => [], 'nama' => $nama_akun];
        }
        $missingCoa[$kode_akun]['count']++;
        if (count($missingCoa[$kode_akun]['rows']) < 5) {
            $missingCoa[$kode_akun]['rows'][] = $row;
        }
    }

    // Check departemen
    if (!empty($departemen)) {
        $deptKey = strtoupper($departemen);
        $dept = $departemenByKode->get($deptKey) ?? $departemenByDeskripsi->get($deptKey);
        if (!$dept) {
            if (!isset($missingDept[$departemen])) $missingDept[$departemen] = 0;
            $missingDept[$departemen]++;
        } elseif ($coaByCode->has($kode_akun)) {
            $akun = $coaByCode->get($kode_akun);
            $idx = $dept->id . ':' . $akun->id;
            if (!$deptAkunIndex->has($idx)) {
                $combo = "{$departemen} + {$kode_akun} ({$akun->nama_akun})";
                if (!isset($missingDeptAkun[$combo])) {
                    $missingDeptAkun[$combo] = ['count' => 0, 'rows' => []];
                }
                $missingDeptAkun[$combo]['count']++;
                if (count($missingDeptAkun[$combo]['rows']) < 3) {
                    $missingDeptAkun[$combo]['rows'][] = $row;
                }
            }
        }
    }
}

echo "\n--- 1. KODE AKUN YANG TIDAK ADA DI COA ---\n";
if (count($missingCoa) === 0) {
    echo "✅ Semua kode akun ada di master COA lokal.\n";
} else {
    echo "❌ Ditemukan " . count($missingCoa) . " kode akun yang TIDAK ADA di COA:\n";
    foreach ($missingCoa as $k => $v) {
        echo "   - Kode '{$k}' (Nama di file: '{$v['nama']}') | Muncul di {$v['count']} baris. Contoh baris: " . implode(', ', $v['rows']) . "\n";
    }
}

echo "\n--- 2. DEPARTEMEN YANG TIDAK TERDAFTAR ---\n";
if (count($missingDept) === 0) {
    echo "✅ Semua departemen valid.\n";
} else {
    echo "❌ Ditemukan departemen yang tidak ada di master:\n";
    foreach ($missingDept as $k => $cnt) {
        echo "   - '{$k}': {$cnt} baris\n";
    }
}

echo "\n--- 3. KOMBINASI DEPARTEMEN + AKUN BELUM DI-ASSIGN (departemen_akuns) ---\n";
if (count($missingDeptAkun) === 0) {
    echo "✅ Semua kombinasi Departemen + Akun sudah terdaftar di Assign Account (departemen_akuns).\n";
} else {
    echo "❌ Ditemukan " . count($missingDeptAkun) . " kombinasi yang BELUM TERDAFTAR di departemen_akuns:\n";
    foreach ($missingDeptAkun as $combo => $v) {
        echo "   - {$combo} | Muncul di {$v['count']} baris (Contoh baris: " . implode(', ', $v['rows']) . ")\n";
    }
}

echo "\n--- 4. VOUCHER TIDAK SEIMBANG (DEBIT != KREDIT) ---\n";
$unbalanced = 0;
foreach ($voucherTotals as $k => $v) {
    if (abs($v['deb'] - $v['cred']) > 0.01) {
        $unbalanced++;
        if ($unbalanced <= 10) {
            echo "   - Voucher [{$k}] (Mulai baris {$v['first_row']}): Debit = " . number_format($v['deb'], 2) . " | Kredit = " . number_format($v['cred'], 2) . " | Selisih = " . number_format(abs($v['deb'] - $v['cred']), 2) . "\n";
        }
    }
}
if ($unbalanced === 0) {
    echo "✅ Semua voucher seimbang 100%.\n";
} else {
    echo "❌ Total voucher tidak seimbang: {$unbalanced} voucher!\n";
}

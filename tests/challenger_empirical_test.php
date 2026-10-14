<?php

/**
 * Challenger 2 Empirical Test Suite
 * PT Rembulan Citra Abadi (RCA) - Internal Accounting System Verification
 *
 * Tests:
 * 1. Mathematical Invariants:
 *    - Buku Besar Opening Balance: Consolidated = Sum(Branches 1..9) + Unassigned
 *    - Rekonsiliasi Closing: Selisih = Total Kas Masuk - Total Omset
 *    - Unassigned Watchdog: Total = Real (426) + Nominal (235) = 661
 * 2. RBAC Route Protection:
 *    - Guest (unauthenticated) -> 302 Redirect to /login
 *    - Admin & Staff -> 200 OK
 * 3. Dual-System Parity:
 *    - 2025 (web-internal) vs 2026 (C:\laragon\www\2026-rca-internal) file parity & checksums
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\User;
use App\Http\Controllers\BukuBesarController;
use App\Http\Controllers\RekonsiliasiClosingController;
use App\Http\Controllers\UnassignedWatchdogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

echo "====================================================================\n";
echo " CHALLENGER 2: EMPIRICAL MATHEMATICAL & SECURITY VERIFICATION SUITE \n";
echo "====================================================================\n\n";

$failures = [];
$totalTests = 0;
$passedTests = 0;

function recordResult($name, $passed, $details = '') {
    global $failures, $totalTests, $passedTests;
    $totalTests++;
    if ($passed) {
        $passedTests++;
        echo "[PASS] $name\n";
        if ($details) {
            echo "       $details\n";
        }
    } else {
        $failures[] = "$name: $details";
        echo "[FAIL] $name\n";
        echo "       $details\n";
    }
}

// ============================================================================
// TASK 1.1: BUKU BESAR OPENING BALANCE ISOLATION INVARIANT
// Consolidated Opening Balance = Sum(Branches 1..9) + Unassigned
// ============================================================================
echo "--- TEST GROUP 1.1: Buku Besar Opening Balance Invariants ---\n";

$bukuBesar = new BukuBesarController();

// We will test multiple accounts across multiple dates
$testDates = ['2025-01-01', '2025-06-01', '2025-12-01'];
// Select prominent Real and Nominal accounts
$testAccountCodes = [
    '1101001', // KAS KECIL PUSAT
    '1101002', // KAS BESAR
    '1103001', // QRIS
    '1104001', // PIUTANG USAHA
    '2102019', // HUTANG LAIN-LAIN
    '3101001', // MODAL
    '5313003', // BEBAN OPERASIONAL (Nominal)
    '4101001', // PENDAPATAN (Nominal if exists)
];

$activeDepts = Departement::whereIn('id', range(1, 9))->get();

foreach ($testDates as $date) {
    foreach ($testAccountCodes as $code) {
        $coa = ChartOfAccount::where('kode_akun', $code)->first();
        if (!$coa) continue;

        // 1. Consolidated Opening Balance (departemen_id = 'all' or '')
        $reqConsolidated = new Request([
            'start_date' => $date,
            'end_date' => $date,
            'selected_accounts' => $code,
            'departemen_id' => 'all',
        ]);
        $resConsolidated = $bukuBesar->bukuBesarReport($reqConsolidated);
        $dataConsolidated = $resConsolidated->getData();
        $obConsolidated = $dataConsolidated['saldoAwalPerAkun'][$code] ?? 0.0;

        // 2. Unassigned Opening Balance (departemen_id = 'unassigned')
        $reqUnassigned = new Request([
            'start_date' => $date,
            'end_date' => $date,
            'selected_accounts' => $code,
            'departemen_id' => 'unassigned',
        ]);
        $resUnassigned = $bukuBesar->bukuBesarReport($reqUnassigned);
        $dataUnassigned = $resUnassigned->getData();
        $obUnassigned = $dataUnassigned['saldoAwalPerAkun'][$code] ?? 0.0;

        // 3. Sum of all 9 Branches
        $sumBranches = 0.0;
        $branchDetails = [];
        foreach ($activeDepts as $dept) {
            $reqBranch = new Request([
                'start_date' => $date,
                'end_date' => $date,
                'selected_accounts' => $code,
                'departemen_id' => $dept->id,
            ]);
            $resBranch = $bukuBesar->bukuBesarReport($reqBranch);
            $dataBranch = $resBranch->getData();
            $obBranch = $dataBranch['saldoAwalPerAkun'][$code] ?? 0.0;
            $sumBranches += (float)$obBranch;
            if (abs($obBranch) > 0.001) {
                $branchDetails[] = "Dept {$dept->id} ({$dept->kode}): " . number_format($obBranch, 2);
            }
        }

        $sumExpected = $sumBranches + (float)$obUnassigned;
        $diff = abs((float)$obConsolidated - $sumExpected);
        $passed = ($diff < 0.01);

        $detailMsg = sprintf(
            "Consolidated: %s | Sum(Depts 1..9): %s | Unassigned: %s | Diff: %s (Branches with balance: %s)",
            number_format($obConsolidated, 2),
            number_format($sumBranches, 2),
            number_format($obUnassigned, 2),
            number_format($diff, 4),
            count($branchDetails) > 0 ? implode(', ', $branchDetails) : 'None'
        );

        recordResult("GL Opening Balance Invariant: Akun $code ($coa->nama_akun) @ $date", $passed, $detailMsg);
    }
}

// ============================================================================
// TASK 1.2: REKONSILIASI CLOSING INVARIANTS
// Selisih = Total Kas Masuk - Total Omset
// ============================================================================
echo "\n--- TEST GROUP 1.2: Rekonsiliasi Closing Mathematical Invariants ---\n";

$rekonController = new RekonsiliasiClosingController();

// Test multiple department / period combinations
$rekonTestConfigs = [
    ['start_date' => '2025-12-01', 'end_date' => '2025-12-31', 'departemen_id' => '2', 'label' => 'DUFAN Dec 2025'],
    ['start_date' => '2025-12-01', 'end_date' => '2025-12-31', 'departemen_id' => '1', 'label' => 'PUSAT Dec 2025'],
    ['start_date' => '2025-11-01', 'end_date' => '2025-11-30', 'departemen_id' => '2', 'label' => 'DUFAN Nov 2025'],
    ['start_date' => '2025-01-01', 'end_date' => '2025-12-31', 'departemen_id' => '', 'label' => 'ALL DEPTS Full Year 2025'],
];

foreach ($rekonTestConfigs as $cfg) {
    $req = new Request($cfg);
    $response = $rekonController->index($req);
    $data = $response->getData();
    $records = $data['records'] ?? [];
    $summary = $data['summary'] ?? [];

    $rekonRowsChecked = 0;
    $formulaViolations = 0;
    $statusViolations = 0;

    $runningOmset = 0.0;
    $runningKas = 0.0;
    $runningSelisih = 0.0;

    foreach ($records as $row) {
        $rekonRowsChecked++;
        $omset = (float)$row['total_omset'];
        $kas = (float)$row['total_kas_masuk'];
        $selisih = (float)$row['selisih'];
        $status = $row['status'];

        // Formula Check: selisih = total_kas_masuk - total_omset
        $expectedSelisih = $kas - $omset;
        if (abs($selisih - $expectedSelisih) > 0.01) {
            $formulaViolations++;
        }

        // Status Classification Check:
        // MATCH: abs(selisih) <= 1000
        // PENDING: ($omset == 0 xor $kas == 0) and not match
        // SELISIH: otherwise
        $expectedStatus = 'SELISIH';
        if (abs($selisih) <= 1000) {
            $expectedStatus = 'MATCH';
        } elseif ($omset == 0 || $kas == 0) {
            $expectedStatus = 'PENDING';
        }

        if ($status !== $expectedStatus) {
            $statusViolations++;
        }

        $runningOmset += $omset;
        $runningKas += $kas;
        $runningSelisih += $selisih;
    }

    $summarySelisih = (float)($summary['total_selisih'] ?? 0);
    $summaryExpected = (float)($summary['total_kas_masuk'] ?? 0) - (float)($summary['total_omset'] ?? 0);
    $summaryPassed = abs($summarySelisih - $summaryExpected) < 0.01;

    $passed = ($formulaViolations === 0 && $statusViolations === 0 && $summaryPassed && $rekonRowsChecked > 0);
    $detail = sprintf(
        "Rows checked: %d | Formula violations: %d | Status violations: %d | Summary Selisih: %s == (%s - %s)",
        $rekonRowsChecked,
        $formulaViolations,
        $statusViolations,
        number_format($summarySelisih, 2),
        number_format($summary['total_kas_masuk'] ?? 0, 2),
        number_format($summary['total_omset'] ?? 0, 2)
    );

    recordResult("Rekonsiliasi Invariant ({$cfg['label']})", $passed, $detail);
}

// ============================================================================
// TASK 1.3: UNASSIGNED WATCHDOG TOTAL INVARIANTS
// Total Unassigned = Real (426) + Nominal (235) = 661
// ============================================================================
echo "\n--- TEST GROUP 1.3: Unassigned Watchdog Mathematical Invariants ---\n";

$watchdog = new UnassignedWatchdogController();
$kpi = $watchdog->getKpiCounts();

$totalUnassigned = $kpi['total'] ?? 0;
$realCount = $kpi['real'] ?? 0;
$nominalCount = $kpi['nominal'] ?? 0;

$watchdogSumPassed = ($totalUnassigned === ($realCount + $nominalCount));
$watchdogExpectedPassed = ($totalUnassigned === 661 && $realCount === 426 && $nominalCount === 235);

recordResult(
    "Watchdog Total = Real + Nominal",
    $watchdogSumPassed,
    "Total: $totalUnassigned | Real: $realCount | Nominal: $nominalCount | Sum: " . ($realCount + $nominalCount)
);

recordResult(
    "Watchdog Exact Database Counts: 661 = 426 + 235",
    $watchdogExpectedPassed,
    "Expected (661, 426, 235) vs Actual ($totalUnassigned, $realCount, $nominalCount)"
);

// Verify directly against database rows
$dbUnassignedCount = JournalEntryDetail::whereNull('departemen_akun_id')->count();
$dbRealCount = JournalEntryDetail::whereNull('departemen_akun_id')
    ->whereHas('chartOfAccount', function ($q) {
        $q->whereIn('tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas']);
    })->count();
$dbNominalCount = JournalEntryDetail::whereNull('departemen_akun_id')
    ->whereHas('chartOfAccount', function ($q) {
        $q->whereIn('tipe_akun', ['Pendapatan', 'Beban']);
    })->count();

$dbCountsMatch = ($dbUnassignedCount === 661 && $dbRealCount === 426 && $dbNominalCount === 235);
recordResult(
    "Direct DB Query: journal_entry_details unassigned counts",
    $dbCountsMatch,
    "DB Total: $dbUnassignedCount | DB Real: $dbRealCount | DB Nominal: $dbNominalCount"
);

// Verify breakdown of Real accounts
$countAset = JournalEntryDetail::whereNull('departemen_akun_id')->whereHas('chartOfAccount', function ($q) { $q->where('tipe_akun', 'Aset'); })->count();
$countKewajiban = JournalEntryDetail::whereNull('departemen_akun_id')->whereHas('chartOfAccount', function ($q) { $q->where('tipe_akun', 'Kewajiban'); })->count();
$countEkuitas = JournalEntryDetail::whereNull('departemen_akun_id')->whereHas('chartOfAccount', function ($q) { $q->where('tipe_akun', 'Ekuitas'); })->count();

$realBreakdownPassed = ($countAset === 280 && $countKewajiban === 132 && $countEkuitas === 14 && ($countAset + $countKewajiban + $countEkuitas === 426));
recordResult(
    "Watchdog Real Accounts Breakdown (Aset: 280, Kewajiban: 132, Ekuitas: 14)",
    $realBreakdownPassed,
    "Aset: $countAset | Kewajiban: $countKewajiban | Ekuitas: $countEkuitas | Sum: " . ($countAset + $countKewajiban + $countEkuitas)
);

// Verify breakdown of Nominal accounts
$countBeban = JournalEntryDetail::whereNull('departemen_akun_id')->whereHas('chartOfAccount', function ($q) { $q->where('tipe_akun', 'Beban'); })->count();
$countPendapatan = JournalEntryDetail::whereNull('departemen_akun_id')->whereHas('chartOfAccount', function ($q) { $q->where('tipe_akun', 'Pendapatan'); })->count();

$nominalBreakdownPassed = ($countBeban === 192 && $countPendapatan === 43 && ($countBeban + $countPendapatan === 235));
recordResult(
    "Watchdog Nominal Accounts Breakdown (Beban: 192, Pendapatan: 43)",
    $nominalBreakdownPassed,
    "Beban: $countBeban | Pendapatan: $countPendapatan | Sum: " . ($countBeban + $countPendapatan)
);

// ============================================================================
// TASK 2: RBAC ROUTE PROTECTION VERIFICATION
// Guest -> 302 Redirect to /login
// Admin & Staff -> 200 OK
// ============================================================================
echo "\n--- TEST GROUP 2: RBAC Route Protection Verification ---\n";

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$routesToTest = [
    ['method' => 'GET', 'uri' => '/rekonsiliasi_closing', 'name' => 'rekonsiliasi_closing.index'],
    ['method' => 'GET', 'uri' => '/rekonsiliasi_closing/detail', 'name' => 'rekonsiliasi_closing.detail'],
    ['method' => 'GET', 'uri' => '/rekonsiliasi_closing/export?format=excel', 'name' => 'rekonsiliasi_closing.export'],
    ['method' => 'GET', 'uri' => '/unassigned_watchdog', 'name' => 'unassigned_watchdog.index'],
    ['method' => 'GET', 'uri' => '/unassigned_watchdog/export?format=excel', 'name' => 'unassigned_watchdog.export'],
    ['method' => 'GET', 'uri' => '/filter_buku_besar', 'name' => 'buku_besar.filter_buku_besar'],
    ['method' => 'GET', 'uri' => '/laporan/buku-besar?start_date=2025-12-01&end_date=2025-12-31', 'name' => 'buku_besar.buku_besar_report'],
    ['method' => 'GET', 'uri' => '/journal_entry/1/edit', 'name' => 'journal_entry.edit'],
    ['method' => 'GET', 'uri' => '/filter_neraca_departemen', 'name' => 'neraca_departemen.filter_neraca_departemen'],
];

// 2.1 Guest Tests (Unauthenticated)
foreach ($routesToTest as $r) {
    // Clear session & auth
    auth()->logout();
    session()->flush();

    $request = Request::create($r['uri'], $r['method']);
    $response = $httpKernel->handle($request);
    $status = $response->getStatusCode();
    $targetUrl = $response->headers->get('Location') ?? '';

    // Must be 302 redirecting to login or 401/403
    $passed = ($status === 302 && strpos($targetUrl, 'login') !== false);
    recordResult(
        "RBAC Guest Route Protection: {$r['method']} {$r['uri']}",
        $passed,
        "Status: $status | Redirect Location: $targetUrl"
    );
    $httpKernel->terminate($request, $response);
}

// 2.2 Admin User Tests (Authenticated)
$adminUser = User::where('name', 'Admin')->first() ?? User::find(1);
if ($adminUser) {
    echo "\nTesting Admin User (ID: {$adminUser->id}, Name: {$adminUser->name}):\n";
    foreach ($routesToTest as $r) {
        if ($r['uri'] === '/rekonsiliasi_closing/detail') continue; // requires specific parameters

        auth()->login($adminUser);
        $request = Request::create($r['uri'], $r['method']);
        $request->setUserResolver(function () use ($adminUser) { return $adminUser; });

        $response = $httpKernel->handle($request);
        $status = $response->getStatusCode();

        $passed = ($status === 200);
        recordResult(
            "RBAC Admin Access: {$r['method']} {$r['uri']}",
            $passed,
            "Status: $status"
        );
        $httpKernel->terminate($request, $response);
    }
}

// 2.3 Staff User Tests (Authenticated)
$staffUser = User::where('name', 'Staff')->first() ?? User::whereHas('roles', function($q){ $q->where('name', 'Staff'); })->first() ?? User::find(2);
if ($staffUser) {
    echo "\nTesting Staff User (ID: {$staffUser->id}, Name: {$staffUser->name}):\n";
    foreach ($routesToTest as $r) {
        if ($r['uri'] === '/rekonsiliasi_closing/detail') continue;

        auth()->login($staffUser);
        $request = Request::create($r['uri'], $r['method']);
        $request->setUserResolver(function () use ($staffUser) { return $staffUser; });

        $response = $httpKernel->handle($request);
        $status = $response->getStatusCode();

        $passed = ($status === 200);
        recordResult(
            "RBAC Staff Access: {$r['method']} {$r['uri']}",
            $passed,
            "Status: $status"
        );
        $httpKernel->terminate($request, $response);
    }
}

// ============================================================================
// TASK 3: DUAL-SYSTEM PARITY (2025 vs 2026)
// ============================================================================
echo "\n--- TEST GROUP 3: Dual-System Parity (2025 vs 2026) ---\n";

$dir2025 = 'd:\\Hazza\\REMBULAN CITRA ABADI\\2025\\2025-RCA-INTERNAL\\web-internal';
$dir2026 = 'C:\\laragon\\www\\2026-rca-internal';

$syncFiles = [
    'app/Http/Controllers/BukuBesarController.php',
    'app/Http/Controllers/JournalEntryController.php',
    'app/Http/Controllers/DepartemenAkunController.php',
    'app/Http/Controllers/RekonsiliasiClosingController.php',
    'app/Http/Controllers/UnassignedWatchdogController.php',
    'app/Http/Controllers/NeracaDepartemenController.php',
    'app/Exports/BukuBesarExport.php',
    'app/Exports/RekonsiliasiClosingExport.php',
    'app/Exports/UnassignedWatchdogExport.php',
    'app/Exports/NeracaDepartemenExport.php',
    'resources/views/buku_besar/filter_buku_besar.blade.php',
    'resources/views/buku_besar/buku_besar_report.blade.php',
    'resources/views/buku_besar/export_excel.blade.php',
    'resources/views/buku_besar/export_pdf.blade.php',
    'resources/views/journal_entry/edit.blade.php',
    'resources/views/journal_entry/index.blade.php',
    'resources/views/income_statement/income_statement_departement.blade.php',
    'resources/views/rekonsiliasi_closing/index.blade.php',
    'resources/views/rekonsiliasi_closing/export_excel.blade.php',
    'resources/views/rekonsiliasi_closing/export_pdf.blade.php',
    'resources/views/unassigned_watchdog/index.blade.php',
    'resources/views/unassigned_watchdog/export_excel.blade.php',
    'resources/views/unassigned_watchdog/export_pdf.blade.php',
    'resources/views/neraca_departemen/filter_neraca_departemen.blade.php',
    'resources/views/neraca_departemen/neraca_departemen_report.blade.php',
    'resources/views/departemen/assign.blade.php',
    'resources/views/components/navbar.blade.php',
    'routes/web.php',
];

$parityFailures = 0;
foreach ($syncFiles as $relPath) {
    $f2025 = $dir2025 . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
    $f2026 = $dir2026 . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);

    if (!file_exists($f2025)) {
        recordResult("Parity Existence 2025: $relPath", false, "Missing in 2025");
        $parityFailures++;
        continue;
    }
    if (!file_exists($f2026)) {
        recordResult("Parity Existence 2026: $relPath", false, "Missing in 2026");
        $parityFailures++;
        continue;
    }

    $md5_2025 = md5_file($f2025);
    $md5_2026 = md5_file($f2026);
    $matches = ($md5_2025 === $md5_2026);

    recordResult(
        "Dual-System Checksum Parity: $relPath",
        $matches,
        "2025 MD5: $md5_2025 | 2026 MD5: $md5_2026"
    );
    if (!$matches) {
        $parityFailures++;
    }
}

// Also check permission parity in 2026 database
try {
    $db2026 = DB::connection('mysql')->select("SELECT count(*) as cnt FROM rembulancitraabadi_2026_db.permissions WHERE name IN ('rekonsiliasi_closing.access', 'unassigned_watchdog.access', 'unassigned_watchdog.update')");
    $permCount2026 = (int)($db2026[0]->cnt ?? 0);
    recordResult(
        "Database 2026 Permission Seeding Parity",
        $permCount2026 === 3,
        "Found $permCount2026 / 3 required permissions in rembulancitraabadi_2026_db"
    );
} catch (\Exception $e) {
    recordResult("Database 2026 Permission Seeding Parity", false, $e->getMessage());
}

// ============================================================================
// SUMMARY & VERDICT
// ============================================================================
echo "\n====================================================================\n";
echo " CHALLENGER 2 EMPIRICAL TEST RESULTS SUMMARY\n";
echo "====================================================================\n";
echo "Total Invariants & Security Checks : $totalTests\n";
echo "Passed Checks                      : $passedTests\n";
echo "Failed Checks                      : " . count($failures) . "\n";

if (count($failures) === 0) {
    echo "\nFINAL EMPIRICAL VERDICT: >>> APPROVE <<<\n";
    echo "All mathematical invariants, RBAC route guards, and dual-system checksums hold 100% true.\n";
} else {
    echo "\nFINAL EMPIRICAL VERDICT: >>> REJECT <<<\n";
    echo "Failures encountered:\n";
    foreach ($failures as $f) {
        echo " - $f\n";
    }
}
echo "====================================================================\n";

exit(count($failures) === 0 ? 0 : 1);

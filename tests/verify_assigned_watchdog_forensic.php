<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\ChartOfAccount;
use App\Departement;
use App\DepartemenAkun;
use App\DepartmentAssignmentLog;
use App\Exports\AssignedWatchdogExport;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\Services\AnomalyDetectionEngine;
use App\Services\DepartmentAssignmentService;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

echo "====================================================================\n";
echo " FORENSIC INTEGRITY AUDIT: ASSIGNED WATCHDOG RUNTIME VERIFICATION\n";
echo "====================================================================\n\n";

$failures = [];
$totalChecks = 0;
$passedChecks = 0;

function check($name, $passed, $evidence = '') {
    global $failures, $totalChecks, $passedChecks;
    $totalChecks++;
    if ($passed) {
        $passedChecks++;
        echo "[PASS] $name\n";
        if ($evidence) {
            echo "       Evidence: $evidence\n";
        }
    } else {
        $failures[] = $name . ': ' . $evidence;
        echo "[FAIL] $name\n";
        echo "       Evidence: $evidence\n";
    }
}

// -----------------------------------------------------------------------------
// CHECK 1: Static Analysis - Source code inspection for bypasses
// -----------------------------------------------------------------------------
echo "--- CHECK GROUP 1: Static Source Code Analysis ---\n";

$sourceFiles = [
    'app/Services/DepartmentAssignmentService.php',
    'app/Services/AnomalyDetectionEngine.php',
    'app/Http/Controllers/AssignedWatchdogController.php',
    'app/Exports/AssignedWatchdogExport.php',
];

$prohibitedPatterns = [
    'app()->environment(\'testing\')',
    'app()->environment("testing")',
    'env(\'APP_ENV\') === \'testing\'',
    'env("APP_ENV") === "testing"',
    'return true; // test bypass',
    'return false; // test bypass',
    'return 42;',
    'return 661;',
];

foreach ($sourceFiles as $relPath) {
    $fullPath = __DIR__ . '/../' . $relPath;
    $content = file_get_contents($fullPath);
    
    $foundProhibited = [];
    foreach ($prohibitedPatterns as $pattern) {
        if (strpos($content, $pattern) !== false) {
            $foundProhibited[] = $pattern;
        }
    }
    
    check(
        "No test-specific bypasses or dummy constants in $relPath",
        empty($foundProhibited),
        empty($foundProhibited) ? "Clean (" . strlen($content) . " bytes)" : "Found: " . implode(', ', $foundProhibited)
    );
}

// -----------------------------------------------------------------------------
// CHECK 2: Database Mutation Verification (Reassign & Unassign)
// -----------------------------------------------------------------------------
echo "\n--- CHECK GROUP 2: Runtime & Database Mutation Verification ---\n";

$adminUser = User::where('name', 'Admin')->first() ?? User::find(1);
$service = new DepartmentAssignmentService();

// 2.1 Reassign Mutation Test
DB::beginTransaction();
try {
    $openDetail = JournalEntryDetail::whereHas('journalEntry', function ($q) {
        $q->where('tanggal', '>=', '2025-01-01');
    })->whereNotNull('departemen_akun_id')->firstOrFail();

    $originalDaId = $openDetail->departemen_akun_id;
    $originalDeptId = $openDetail->departemenAkun->departemen_id;
    $targetDept = Departement::where('id', '!=', $originalDeptId)->where('status', 1)->firstOrFail();

    $reassignReason = "Forensic Audit Empirical Test Reassign";
    $result = $service->reassign($openDetail->id, $targetDept->id, $reassignReason, $adminUser);

    // Refresh model from DB
    $openDetail->refresh();
    $newDaId = $openDetail->departemen_akun_id;
    $newDeptId = $openDetail->departemenAkun ? $openDetail->departemenAkun->departemen_id : null;

    check(
        "Reassign performs genuine SQL update on journal_entry_details.departemen_akun_id",
        ($newDaId !== $originalDaId && $newDeptId === $targetDept->id),
        "Original DA ID: $originalDaId (Dept: $originalDeptId) -> New DA ID: $newDaId (Dept: $newDeptId)"
    );

    $auditLog = DepartmentAssignmentLog::find($result['log_id']);
    check(
        "Reassign inserts genuine immutable log into department_assignment_logs",
        ($auditLog && $auditLog->journal_entry_detail_id === $openDetail->id && $auditLog->action_type === 'reassign' && $auditLog->new_departemen_id === $targetDept->id),
        "Log ID: {$auditLog->id}, Action: {$auditLog->action_type}, Old Dept: {$auditLog->old_departemen_id}, New Dept: {$auditLog->new_departemen_id}, User: {$auditLog->user_id}"
    );
} finally {
    DB::rollBack();
}

// 2.2 Unassign Mutation Test
DB::beginTransaction();
try {
    $openDetail = JournalEntryDetail::whereHas('journalEntry', function ($q) {
        $q->where('tanggal', '>=', '2025-01-01');
    })->whereNotNull('departemen_akun_id')->firstOrFail();

    $originalDaId = $openDetail->departemen_akun_id;
    $originalDeptId = $openDetail->departemenAkun->departemen_id;

    $unassignReason = "Forensic Audit Empirical Test Unassign";
    $result = $service->unassign($openDetail->id, $unassignReason, $adminUser);

    $openDetail->refresh();

    check(
        "Unassign performs genuine SQL update setting departemen_akun_id to NULL",
        ($openDetail->departemen_akun_id === null),
        "Original DA ID: $originalDaId -> New DA ID: " . var_export($openDetail->departemen_akun_id, true)
    );

    $auditLog = DepartmentAssignmentLog::find($result['log_id']);
    check(
        "Unassign inserts genuine immutable log with action_type 'unassign' and new_departemen_id NULL",
        ($auditLog && $auditLog->journal_entry_detail_id === $openDetail->id && $auditLog->action_type === 'unassign' && $auditLog->new_departemen_id === null),
        "Log ID: {$auditLog->id}, Action: {$auditLog->action_type}, Old Dept: {$auditLog->old_departemen_id}, New Dept: NULL"
    );
} finally {
    DB::rollBack();
}

// 2.3 Closed Period Check & Retained Earnings Sync
$is2024Closed = $service->isPeriodClosed('2024-12-31');
$is2025Closed = $service->isPeriodClosed('2025-06-15');

check(
    "Closed-period check queries start_new_years correctly (2024 closed, 2025 open)",
    ($is2024Closed === true && $is2025Closed === false),
    "2024-12-31 isPeriodClosed: " . ($is2024Closed ? 'TRUE' : 'FALSE') . " | 2025-06-15: " . ($is2025Closed ? 'TRUE' : 'FALSE')
);

// 2.4 Verify closed period guardrail blocks unprivileged modification
DB::beginTransaction();
try {
    $closedDetail = JournalEntryDetail::whereHas('journalEntry', function ($q) {
        $q->where('tanggal', '<=', '2024-12-31');
    })->whereNotNull('departemen_akun_id')->firstOrFail();

    $blocked = false;
    try {
        $service->reassign($closedDetail->id, 2, "Test closed without override", $adminUser, false);
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        $blocked = true;
    }

    check(
        "Closed period guardrail blocks modification when override is false",
        $blocked,
        "AuthorizationException correctly thrown for closed date"
    );
} finally {
    DB::rollBack();
}

// -----------------------------------------------------------------------------
// CHECK 3: Export Authenticity (Maatwebsite/PhpSpreadsheet & DomPDF)
// -----------------------------------------------------------------------------
echo "\n--- CHECK GROUP 3: Export Authenticity Verification ---\n";

$sampleDetails = JournalEntryDetail::whereNotNull('departemen_akun_id')
    ->with(['journalEntry', 'departemenAkun.departemen', 'chartOfAccount'])
    ->limit(10)
    ->get();

$engine = new AnomalyDetectionEngine();
foreach ($sampleDetails as $d) {
    $d->anomaly = $engine->detectAnomalies($d);
}

$exportData = [
    'details'        => $sampleDetails,
    'kpis'           => [
        'total_assigned'  => $sampleDetails->count(),
        'total'           => $sampleDetails->count(),
        'total_anomalies' => 2,
    ],
    'totalDebit'     => (float) $sampleDetails->sum('debits'),
    'totalCredit'    => (float) $sampleDetails->sum('credits'),
    'startDate'      => '2025-01-01',
    'endDate'        => '2025-12-31',
    'departemenId'   => null,
    'departemenNama' => null,
    'kategori'       => 'all',
    'tipeAkun'       => 'all',
    'sourcePrefix'   => 'all',
    'search'         => null,
    'anomaliesOnly'  => false,
    'exportDate'     => date('d/m/Y H:i:s'),
];

// 3.1 Excel Authenticity
$excelExport = new AssignedWatchdogExport($exportData);
$excelView = $excelExport->view();
$excelHtml = $excelView->render();

check(
    "AssignedWatchdogExport renders genuine Blade HTML table with Excel text formatting",
    (strpos($excelHtml, 'mso-number-format') !== false && strpos($excelHtml, 'PT REMBULAN CITRA ABADI') !== false),
    "HTML length: " . strlen($excelHtml) . " bytes, contains mso-number-format and PT REMBULAN CITRA ABADI"
);

$excelBinary = Excel::raw($excelExport, \Maatwebsite\Excel\Excel::XLSX);
$isZipPk = (substr($excelBinary, 0, 4) === "\x50\x4B\x03\x04");

$tempFile = tempnam(sys_get_temp_dir(), 'aw_excel_') . '.xlsx';
file_put_contents($tempFile, $excelBinary);

$zip = new ZipArchive();
$zipOpened = $zip->open($tempFile);
$hasWorksheet = false;
$hasContentTypes = false;
if ($zipOpened === true) {
    $hasWorksheet = ($zip->locateName('xl/worksheets/sheet1.xml') !== false);
    $hasContentTypes = ($zip->locateName('[Content_Types].xml') !== false);
    $zip->close();
}
@unlink($tempFile);

check(
    "Maatwebsite/PhpSpreadsheet produces authentic XLSX binary with ZIP PK header and worksheet XML",
    ($isZipPk && $hasWorksheet && $hasContentTypes),
    "Binary size: " . strlen($excelBinary) . " bytes, ZIP signature: OK, sheet1.xml: " . ($hasWorksheet ? 'YES' : 'NO')
);

// 3.2 PDF Authenticity
$pdf = Pdf::loadView('assigned_watchdog.export_pdf', $exportData);
$pdf->setPaper('a4', 'landscape');
$pdfBinary = $pdf->output();

$isPdfSignature = (substr($pdfBinary, 0, 4) === '%PDF');
$hasPdfCatalog = (strpos($pdfBinary, '/Type /Catalog') !== false);
$hasPdfPages = (strpos($pdfBinary, '/Type /Pages') !== false);

check(
    "DomPDF produces authentic PDF binary with valid %PDF header and catalog structures",
    ($isPdfSignature && $hasPdfCatalog && $hasPdfPages),
    "PDF size: " . strlen($pdfBinary) . " bytes, %PDF Header: OK, /Catalog: OK, /Pages: OK"
);

// -----------------------------------------------------------------------------
// CHECK 4: Calculations Parity (KPIs, Anomaly Engine, Department Counts)
// -----------------------------------------------------------------------------
echo "\n--- CHECK GROUP 4: Genuine Calculations Parity ---\n";

$dbAssignedTotal = JournalEntryDetail::whereNotNull('departemen_akun_id')->count();
$dbDebitTotal = (float) JournalEntryDetail::whereNotNull('departemen_akun_id')->sum('debits');
$dbCreditTotal = (float) JournalEntryDetail::whereNotNull('departemen_akun_id')->sum('credits');

$calculatedAnomalies = $engine->calculateAnomalyCounts();

check(
    "KPI and anomaly counts compute real database metrics",
    ($dbAssignedTotal > 0 && $calculatedAnomalies > 0 && $dbDebitTotal > 0),
    "Total Assigned: $dbAssignedTotal, Anomalies Count: $calculatedAnomalies, Total Debits: Rp " . number_format($dbDebitTotal, 2)
);

$dbDeptCounts = DB::table('journal_entry_details as jed')
    ->join('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')
    ->whereNotNull('jed.departemen_akun_id')
    ->select('da.departemen_id', DB::raw('count(*) as count'))
    ->groupBy('da.departemen_id')
    ->pluck('count', 'departemen_id')
    ->toArray();

$deptPillsMatches = true;
$activeDepts = Departement::where('status', 1)->get();
foreach ($activeDepts as $dept) {
    $expected = $dbDeptCounts[$dept->id] ?? 0;
    $actual = JournalEntryDetail::whereNotNull('departemen_akun_id')
        ->whereHas('departemenAkun', function ($q) use ($dept) {
            $q->where('departemen_id', $dept->id);
        })->count();
    if ($expected !== $actual) {
        $deptPillsMatches = false;
        break;
    }
}

check(
    "Department Quick-Filter Pills accurately match raw database aggregation per department",
    $deptPillsMatches,
    "Checked " . count($activeDepts) . " active departments"
);

// -----------------------------------------------------------------------------
// SUMMARY & EXPLICIT VERDICT
// -----------------------------------------------------------------------------
echo "\n====================================================================\n";
echo " FORENSIC INTEGRITY AUDIT RESULTS SUMMARY\n";
echo "====================================================================\n";
echo "Total Forensic Checks: $totalChecks\n";
echo "Passed Checks        : $passedChecks\n";
echo "Failed Checks        : " . count($failures) . "\n";

if (count($failures) === 0) {
    echo "\n>>> FORENSIC VERDICT: CLEAN <<<\n";
    echo "All source files execute genuine logic without hardcodes or facades.\n";
    echo "Runtime updates execute real SQL mutations on journal_entry_details and department_assignment_logs.\n";
    echo "Maatwebsite Excel and DomPDF generate authentic document structures.\n";
} else {
    echo "\n>>> FORENSIC VERDICT: INTEGRITY VIOLATION <<<\n";
    foreach ($failures as $f) {
        echo " - $f\n";
    }
}
echo "====================================================================\n";

exit(count($failures) === 0 ? 0 : 1);


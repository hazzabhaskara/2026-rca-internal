<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Sources of the 27 modified rows
$modifiedSources = [
    'BNI7884-260128-005', 'BNI7884-260128-006', 'BNI7884-260128-007',
    'BNI7884-260131-012', 'BNI0365-260702-002', 'MDR6646-260705-001',
    'BNI0365-260708-003', 'BNI0365-260720-004', 'BNI0365-260720-005',
    'MDR6646-260723-001', 'BNI0365-260724-003', 'BNI0365-260724-004',
    'BNI0365-260724-005', 'BNI0365-260724-006', 'BNI0365-260725-003',
    'BNI0365-260729-003', 'BNI0365-260729-004', 'BNI0365-260729-005',
    'BNI0365-260729-006', 'BNI0365-260729-008', 'BNI0365-260729-009',
    'BNI0365-260729-010', 'BNI0365-260729-011', 'BNI0365-260729-012',
    'BNI0365-260731-002', 'BNI0365-260731-003', 'BNI0365-260731-004'
];

$coa3105 = DB::table('chart_of_accounts')->where('kode_akun', '3105000')->first();

$updated = 0;
foreach ($modifiedSources as $src) {
    $rows = DB::table('journal_entries as je')
        ->join('journal_entry_details as jed', 'je.id', '=', 'jed.journal_entry_id')
        ->where('je.source', $src)
        ->where('jed.kode_akun', '5305001')
        ->select(['jed.id', 'jed.departemen_akun_id'])
        ->get();

    foreach ($rows as $r) {
        $newDeptAkunId = $r->departemen_akun_id;
        if (!empty($r->departemen_akun_id)) {
            $da = DB::table('departemen_akuns')->where('id', $r->departemen_akun_id)->first();
            if ($da) {
                $targetDa = DB::table('departemen_akuns')
                    ->where('departemen_id', $da->departemen_id)
                    ->where('akun_id', $coa3105->id)
                    ->first();
                if ($targetDa) {
                    $newDeptAkunId = $targetDa->id;
                } else {
                    $newDeptAkunId = DB::table('departemen_akuns')->insertGetId([
                        'departemen_id' => $da->departemen_id,
                        'akun_id' => $coa3105->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        DB::table('journal_entry_details')->where('id', $r->id)->update([
            'kode_akun' => '3105000',
            'departemen_akun_id' => $newDeptAkunId,
            'updated_at' => now()
        ]);
        $updated++;
    }
}

echo "Sinkronisasi database lokal: {$updated} baris diperbarui ke akun 3105000 (REKONSILIASI).\n";

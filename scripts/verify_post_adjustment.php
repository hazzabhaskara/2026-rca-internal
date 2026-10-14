<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Check balance per voucher in 2026
$unbalanced = DB::table('journal_entries as je')
    ->join('journal_entry_details as jed', 'je.id', '=', 'jed.journal_entry_id')
    ->whereYear('je.tanggal', 2026)
    ->groupBy('je.id', 'je.source', 'je.tanggal')
    ->select([
        'je.id',
        'je.source',
        'je.tanggal',
        DB::raw('SUM(jed.debits) as tot_deb'),
        DB::raw('SUM(jed.credits) as tot_cred'),
        DB::raw('ABS(SUM(jed.debits) - SUM(jed.credits)) as diff')
    ])
    ->havingRaw('diff > 0.01')
    ->get();

echo "=== AUDIT KESEIMBANGAN JURNAL 2026 ===\n";
echo "Voucher tidak seimbang (diff > 0.01): " . $unbalanced->count() . "\n";
if ($unbalanced->count() > 0) {
    foreach ($unbalanced->take(5) as $u) {
        echo "JE {$u->id} | {$u->source} | {$u->tanggal} | Deb: {$u->tot_deb} | Cred: {$u->tot_cred} | Diff: {$u->diff}\n";
    }
} else {
    echo "✅ SEMUA VOUCHER 100% SEIMBANG (DEBIT == KREDIT, SELISIH = 0,00)\n";
}

// 2. Total 2026 trial balance
$tot = DB::table('journal_entries as je')
    ->join('journal_entry_details as jed', 'je.id', '=', 'jed.journal_entry_id')
    ->whereYear('je.tanggal', 2026)
    ->selectRaw('SUM(jed.debits) as tot_deb, SUM(jed.credits) as tot_cred')
    ->first();

echo "\nTotal Debit 2026 : Rp " . number_format($tot->tot_deb, 2) . "\n";
echo "Total Kredit 2026: Rp " . number_format($tot->tot_cred, 2) . "\n";
echo "Selisih          : Rp " . number_format(abs($tot->tot_deb - $tot->tot_cred), 2) . "\n";

// 3. Status akun 5305001 sekarang
$p5305001 = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->where('jed.kode_akun', '5305001')
    ->whereYear('je.tanggal', 2026)
    ->selectRaw('COUNT(*) as cnt, SUM(jed.debits) as tot_deb, SUM(jed.credits) as tot_cred')
    ->first();

echo "\n=== KONDISI AKUN 5305001 PASCA ADJUSTMENT ===\n";
echo "Jumlah Transaksi : {$p5305001->cnt} baris\n";
echo "Total Debit      : Rp " . number_format($p5305001->tot_deb, 2) . "\n";
echo "Total Kredit     : Rp " . number_format($p5305001->tot_cred, 2) . "\n";

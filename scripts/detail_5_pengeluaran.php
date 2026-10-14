<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ids = [177310, 177318, 177326, 177340, 179730];
$rows = DB::table('journal_entries as je')
    ->join('journal_entry_details as jed', 'je.id', '=', 'jed.journal_entry_id')
    ->whereIn('jed.id', $ids)
    ->get(['je.id as je_id', 'je.source', 'je.tanggal', 'je.comment as je_comment', 'jed.id', 'jed.kode_akun', 'jed.debits', 'jed.credits', 'jed.comment as jed_comment']);

foreach ($rows as $r) {
    echo "========================================================\n";
    echo "JE ID: {$r->je_id} | Source Voucher: {$r->source} | Tanggal: {$r->tanggal}\n";
    echo "Keterangan Header: '{$r->je_comment}'\n";
    $legs = DB::table('journal_entry_details')->where('journal_entry_id', $r->je_id)->get();
    foreach ($legs as $leg) {
        $coa = DB::table('chart_of_accounts')->where('kode_akun', $leg->kode_akun)->first();
        $pos = $leg->debits > 0 ? "DEBIT : Rp " . number_format($leg->debits, 2) : "KREDIT: Rp " . number_format($leg->credits, 2);
        echo "  - {$pos} | Akun: {$leg->kode_akun} - {$coa->nama_akun} | Line Comment: '{$leg->comment}'\n";
    }
}

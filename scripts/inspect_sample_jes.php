<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Ambil beberapa je_id dari berbagai kategori
$samples = [150478, 150572, 150548, 150638, 150640, 150936];

$entries = DB::table('journal_entries as je')
    ->whereIn('je.id', function($q) use ($samples) {
        $q->select('journal_entry_id')->from('journal_entry_details')->whereIn('id', $samples);
    })
    ->get();

foreach ($entries as $je) {
    echo "=== JE ID: {$je->id} | Source: {$je->source} | Tanggal: {$je->tanggal} | Comment: {$je->comment} ===\n";
    $details = DB::table('journal_entry_details')
        ->where('journal_entry_id', $je->id)
        ->get();
    foreach ($details as $d) {
        $coa = DB::table('chart_of_accounts')->where('kode_akun', $d->kode_akun)->first();
        $nama = $coa ? $coa->nama_akun : 'UNKNOWN';
        echo "   JED {$d->id} | Akun: {$d->kode_akun} ({$nama}) | Deb: " . number_format($d->debits, 2) . " | Cred: " . number_format($d->credits, 2) . " | Desc: {$d->comment}\n";
    }
}

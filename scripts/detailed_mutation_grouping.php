<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('journal_entry_details as jed')
    ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
    ->whereIn('jed.kode_akun', ['5305000', '5305001'])
    ->whereYear('je.tanggal', 2026)
    ->where(function($q) {
        $q->where('jed.comment', 'like', '%keluar ke%')
          ->orWhere('jed.comment', 'like', '%transfer%')
          ->orWhere('jed.comment', 'like', '%mutasi%')
          ->orWhere('je.comment', 'like', '%keluar ke%')
          ->orWhere('je.comment', 'like', '%transfer%')
          ->orWhere('je.comment', 'like', '%mutasi%');
    })
    ->get(['jed.id', 'jed.journal_entry_id', 'je.source', 'je.tanggal', 'jed.comment', 'jed.debits']);

$groups = [];
foreach ($rows as $r) {
    $creditLeg = DB::table('journal_entry_details')
        ->where('journal_entry_id', $r->journal_entry_id)
        ->where('credits', '>', 0)
        ->first();
    $creditAcc = $creditLeg ? $creditLeg->kode_akun : 'UNKNOWN';

    $text = strtolower(trim($r->comment));
    $key = '';
    if (preg_match('/b\.\s*transfer|biaya\s*transfer/i', $text)) {
        $key = 'Biaya Transfer Bank (B. Transfer)';
    } elseif (preg_match('/keluar ke bni pt ops|keluar ke bni/i', $text)) {
        $key = 'Keluar ke BNI';
    } elseif (preg_match('/keluar ke bri/i', $text)) {
        $key = 'Keluar ke BRI';
    } elseif (preg_match('/keluar ke mandiri/i', $text)) {
        $key = 'Keluar ke Mandiri';
    } elseif (preg_match('/keluar ke cash ops/i', $text)) {
        $key = 'Keluar ke Cash Ops (Kas)';
    } elseif (preg_match('/keluar ke btn redi/i', $text)) {
        $key = 'Keluar ke BTN Redi';
    } elseif (preg_match('/transfer bca evi/i', $text)) {
        $key = 'Transfer BCA Evi';
    } elseif (preg_match('/transfer .* firman/i', $text)) {
        $key = 'Transfer BCA/Kas Firman';
    } elseif (preg_match('/transfer ke bca yerri/i', $text)) {
        $key = 'Transfer BCA Yerri';
    } elseif (preg_match('/keluar ke ancol/i', $text)) {
        $key = 'Keluar ke Ancol (Non-Listrik/Listrik)';
    } else {
        $key = 'Transfer Pihak Ketiga: ' . substr($text, 0, 30);
    }

    $hash = $key . ' [Kredit: ' . $creditAcc . ']';
    if (!isset($groups[$hash])) {
        $groups[$hash] = ['count' => 0, 'sum' => 0, 'samples' => []];
    }
    $groups[$hash]['count']++;
    $groups[$hash]['sum'] += (float)$r->debits;
    if (count($groups[$hash]['samples']) < 2) {
        $groups[$hash]['samples'][] = $r->comment;
    }
}

uasort($groups, fn($a, $b) => $b['sum'] <=> $a['sum']);
echo "=== TOP 20 MUTASI BANK BY NOMINAL ===\n";
$i = 0;
foreach ($groups as $k => $v) {
    if (++$i > 20) break;
    echo sprintf("#%02d | %-55s | %3d trx | Rp %14s\n     Sample: %s\n", 
        $i, $k, $v['count'], number_format($v['sum'], 0), implode(' ; ', $v['samples']));
}

<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Departement;
use Illuminate\Support\Facades\DB;

$cutOff = '2025-12-31';
$departments = Departement::orderBy('id')->get()->map(fn($d) => ['id' => $d->id, 'name' => $d->deskripsi])->toArray();
$departments[] = ['id' => null, 'name' => 'NON-DEPARTEMEN / UNASSIGNED'];

$totAset = 0;
foreach ($departments as $d) {
    $id = $d['id'];
    $qAset = DB::table('journal_entry_details as jed')
        ->join('journal_entries as je', 'je.id', '=', 'jed.journal_entry_id')
        ->join('chart_of_accounts as coa', 'coa.kode_akun', '=', 'jed.kode_akun')
        ->where('coa.tipe_akun', 'Aset')
        ->where('je.tanggal', '<=', $cutOff);
    if ($id === null) $qAset->whereNull('jed.departemen_akun_id');
    else $qAset->join('departemen_akuns as da', 'da.id', '=', 'jed.departemen_akun_id')->where('da.departemen_id', $id);
    $aset = (float)($qAset->selectRaw('SUM(jed.debits - jed.credits) as s')->value('s') ?? 0);
    $totAset += $aset;
}
echo 'totAset=' . sprintf('%.2f', $totAset) . PHP_EOL;

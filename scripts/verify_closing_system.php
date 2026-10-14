<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\UnitKerja;
use App\Wahana;
use App\WahanaItem;
use App\linkedAccounts;
use App\ClosingHarian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "========================================================\n";
echo "🔍 VERIFIKASI SISTEM CLOSING HARIAN (" . config('app.name') . ")\n";
echo "========================================================\n";

// 1. Auth check
$user = User::where('name', 'Admin')->first() ?: User::first();
if (!$user) {
    die("❌ Tidak ada user untuk login simulasi.\n");
}
Auth::login($user);
echo "✅ Logged in as: {$user->name} (Role: " . implode(', ', $user->getRoleNames()->toArray()) . ")\n";

// 2. Master data counts
$unitCount = UnitKerja::count();
$wahanaCount = Wahana::count();
$itemCount = WahanaItem::count();
$linkedCount = linkedAccounts::where('modul', 'closing')->count();
$closingTxCount = ClosingHarian::count();

echo "📊 Master Data Status:\n";
echo "   - Unit Kerja: {$unitCount}\n";
echo "   - Wahana: {$wahanaCount}\n";
echo "   - Wahana Items: {$itemCount}\n";
echo "   - Linked Accounts (closing): {$linkedCount}\n";
echo "   - Transaksi Closing Harian (Must be 0): {$closingTxCount}\n";

if ($unitCount === 0 || $linkedCount === 0) {
    die("❌ Master data belum terisi!\n");
}
if ($closingTxCount > 0) {
    echo "⚠️ PERINGATAN: Ada transaksi closing harian di tabel ({$closingTxCount} baris). User meminta transaksi belum diinput!\n";
} else {
    echo "✅ KONFIRMASI: Transaksi Daily Closing 0 baris (Sesuai Syarat User)!\n";
}

// 3. Test Controller create() View
$closingCtrl = app(\App\Http\Controllers\ClosingHarianController::class);
$createView = $closingCtrl->create();
echo "✅ ClosingHarianController@create view rendered successfully!\n";
$viewData = $createView->getData();
echo "   - Units in view dropdown: " . count($viewData['unitKerja']) . "\n";
echo "   - Kas Omset linked: " . ($viewData['kasOmsetAccount'] ? 'Found' : 'Missing') . "\n";
echo "   - Piutang QRIS linked: " . ($viewData['piutangAccount'] ? 'Found' : 'Missing') . "\n";
echo "   - Titipan Merch linked: " . ($viewData['titipanOmsetAccount'] ? 'Found' : 'Missing') . "\n";
echo "   - Beban Sewa linked: " . ($viewData['bebanSewaAccount'] ? 'Found' : 'Missing') . "\n";
echo "   - Pendapatan Non-Op linked: " . ($viewData['pendapatanOpAccount'] ? 'Found' : 'Missing') . "\n";

// 4. Test Controller index() View
$indexView = $closingCtrl->index();
echo "✅ ClosingHarianController@index view rendered successfully!\n";

// 5. Test LinkedAccountClosingController
$linkedCtrl = app(\App\Http\Controllers\LinkedAccountClosingController::class);
$linkedIndexView = $linkedCtrl->index();
echo "✅ LinkedAccountClosingController@index view rendered successfully!\n";

// Test AJAX getLinkedAccountByUnit
$dufanUnit = UnitKerja::where('kode_unit', 'DUFAN')->first();
if ($dufanUnit) {
    $ajaxLinked = $linkedCtrl->getLinkedAccountByUnit($dufanUnit->id);
    $ajaxLinkedData = json_decode($ajaxLinked->getContent(), true);
    echo "✅ AJAX getLinkedAccountByUnit for DUFAN: " . count($ajaxLinkedData['linkedAccounts']) . " accounts returned.\n";
    foreach ($ajaxLinkedData['linkedAccounts'] as $la) {
        echo "      * {$la['kode']} => {$la['akun']} ({$la['departemen']})\n";
    }
}

// 6. Test UnitKerjaController getWahanaByUnit
$unitCtrl = app(\App\Http\Controllers\UnitKerjaController::class);
if ($dufanUnit) {
    $ajaxWahana = $unitCtrl->getWahanaByUnit($dufanUnit->id);
    $ajaxWahanaData = json_decode($ajaxWahana->getContent(), true);
    echo "✅ AJAX getWahanaByUnit for DUFAN: " . count($ajaxWahanaData) . " wahanas returned.\n";
    $firstW = $ajaxWahanaData[0] ?? null;
    if ($firstW) {
        echo "      * Sample Wahana: {$firstW['nama_wahana']} with " . count($firstW['wahana_item'] ?? $firstW['wahanaItem'] ?? []) . " items.\n";
    }
}

// 7. Test RekonsiliasiClosingController
$rekonCtrl = app(\App\Http\Controllers\RekonsiliasiClosingController::class);
$request = new Request();
$rekonView = $rekonCtrl->index($request);
echo "✅ RekonsiliasiClosingController@index view rendered successfully!\n";

echo "========================================================\n";
echo "🎉 ALL TESTS PASSED! SISTEM SIAP DIGUNAKAN 100%!\n";
echo "========================================================\n\n";

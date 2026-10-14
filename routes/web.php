<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AlamatController;
use App\Http\Controllers\BonusKaryawanController;
use App\Http\Controllers\BukuBesarController;
use App\Http\Controllers\ClosingHarianController;
use App\Http\Controllers\CoaSearchController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartemenAkunController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeOffDayController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FiscalAccountPersamaanController;
use App\Http\Controllers\FiscalController;
use App\Http\Controllers\FiscalSettingController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\IncomeStatementController;
use App\Http\Controllers\LabaRugiDepartemenController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\KomposisiGajiController;
use App\Http\Controllers\LinkedAccountClosingController;
use App\Http\Controllers\NeracaController;
use App\Http\Controllers\NeracaDepartemenController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SettingDepartementController;
use App\Http\Controllers\TrialBalanceController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PembayaranGajiController;
use App\Http\Controllers\PengajuanApprovalController;
use App\Http\Controllers\PerhitunganPajakPenghasilan;
use App\Http\Controllers\PerhitunganPajakPenghasilanController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\PurchaseInvoiceDocumentController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseOrderDocumentController;
use App\Http\Controllers\ReceiptsController;
use App\Http\Controllers\RekeningController;
use App\Http\Controllers\RekonsiliasiClosingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\SalesInvoiceDocumentController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SalesOrderDocumentController;
use App\Http\Controllers\ShiftKaryawanWahanaController;
use App\Http\Controllers\SlipGajiController;
use App\Http\Controllers\AssignedWatchdogController;
use App\Http\Controllers\TargetUnitController;
use App\Http\Controllers\TargetWahanaController;
use App\Http\Controllers\UnassignedWatchdogController;
use App\Http\Controllers\UnitKerjaController;
use App\Http\Controllers\VendorsController;
use App\Http\Controllers\WahanaController;
use App\SalesInvoiceDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    Route::get('/home', 'HomeController@index')->name('home');
    // setup 
    Route::get('/setting', [SettingController::class, 'index'])->name('setting.index')->middleware('permission:setting_setup.access');
    Route::post('/setting', [SettingController::class, 'update'])->name('setting.update');

    // user dan role
    Route::resource('users', 'UserController');
    Route::resource('roles', 'RoleController')->except('index', 'create', 'edit');
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    Route::get('/roles-permissions', [RolePermissionController::class, 'index'])->name('roles.permissions.index');
    Route::post('/roles-permissions', [RolePermissionController::class, 'update'])->name('roles.permissions.update');



    // setting company
    Route::resource('/taxpayers_company', 'TaxpayersCompanyController')->middleware('permission:taxpayers_company.access');

    Route::resource('/company_profile', 'CompanyProfileController')->middleware('permission:company_profile.access');
    Route::get('/company/searchProvinsi', [AlamatController::class, 'searchProvinsi'])->name('company.searchProvinsi');
    Route::get('/company/searchKota', [AlamatController::class, 'searchKota'])->name('company.searchKota');
    Route::get('/company/searchKecamatan', [AlamatController::class, 'searchKecamatan'])->name('company.searchKecamatan');
    Route::get('/company/searchKelurahan', [AlamatController::class, 'searchKelurahan'])->name('company.searchKelurahan');


    // general menu
    Route::resource('/numbering_account', 'NumberingAccountController')->middleware('permission:numbering.access');

    Route::resource('/klasifikasiAkun', 'KlasifikasiAkunController')->middleware('permission:klasifikasi_akun.access');
    // export dan import klasifikasi akun
    Route::get('/export/klasifikasiAkun', [ExportController::class, 'exportklasifikasiAkun'])->name('export.klasifikasiAkun');
    Route::post('/import/klasifikasiAkun', [ImportController::class, 'importklasifikasiAkun'])->name('import.klasifikasiAkun');

    Route::delete('/chartOfAccount_truncate', 'ChartOfAccountController@truncate')->name('chartOfAccount.truncate')->middleware('permission:chart_of_account.delete');
    Route::resource('/chartOfAccount', 'ChartOfAccountController')->middleware('permission:chart_of_account.access');
    Route::get('/coa/search', [CoaSearchController::class, 'search'])->name('coa.search');
    // export dan import chart of account
    Route::get('/export/charofaccount', [ExportController::class, 'exportchartOfAccount'])->name('export.chartOfAccount');
    Route::post('/import/chartofaccount', [ImportController::class, 'importchartOfAccount'])->name('import.chartOfAccount');

    // crud linked Accounts
    Route::resource('linkedAccount', 'linkedAccountController')->middleware('permission:linked_account_setup.access');


    Route::resource('linkedAccount_closing', 'LinkedAccountClosingController')->middleware('permission:linked_account_closing.access');
    Route::get('/get-linked-account-by-unit/{unitId}', [LinkedAccountClosingController::class, 'getLinkedAccountByUnit']);


    Route::get('/admin/setting_departement', [SettingDepartementController::class, 'edit'])->name('setting_departement.edit');
    Route::put('/admin/setting_departement', [SettingDepartementController::class, 'update'])->name('setting_departement.update');

    Route::get('/departemen/assign-account', [DepartementController::class, 'assign'])->name('departemen.assign');
    Route::post('/departemen/assign-account', [DepartementController::class, 'storeAssign'])->name('departemen.assign.store');
    Route::delete('/departemen/assign/{id}', [DepartementController::class, 'destroyAssign'])->name('departemen.assign.destroy');
    Route::delete('/departemen/assign-bulk/remove', [DepartementController::class, 'destroyAssignBulk'])->name('departemen.assign.destroyBulk');

    Route::resource('/departemen', 'DepartementController')->middleware('permission:departement.access');

    // sales taxes
    Route::resource('/sales_taxes', 'SalesTaxesController')->middleware('permission:sales_taxes.access');
    Route::get('/report-sales-taxes', [ReportController::class, 'salesTaxes'])->name('report.sales_taxes');


    // export dan import Departemen
    Route::get('/export/Departemen', [ExportController::class, 'exportDepartemen'])->name('export.Departemen');
    Route::post('/import/Departemen', [ImportController::class, 'importDepartemen'])->name('import.Departemen');
    
    // export template dan import assign account (departemen akun)
    Route::get('/export/template-departemen-akun', [ExportController::class, 'exportDepartemenAkunTemplate'])->name('export.template-departemen-akun');
    Route::get('/export/departemen-akun', [ExportController::class, 'exportDepartemenAkun'])->name('export.departemen-akun');
    Route::post('/import/departemen-akun', [ImportController::class, 'importDepartemenAkun'])->name('import.departemen-akun');

    // journal entry menu
    Route::get('/journal-entry/auto-data', [JournalEntryController::class, 'getAutoData']);

    Route::get('view_journal_entry', [JournalEntryController::class, 'journalEntryShow'])->name('journal_entry.view_journal_entry')->middleware('permission:journal_entry.access');
    Route::get('view_journal_entry/result', [JournalEntryController::class, 'journalEntryView'])->name('journal_entry.view_journal_entry_result');
    // Tampilkan form kosong
    Route::get('filter_journal_entry', [JournalEntryController::class, 'showFilterForm'])->name('journal_entry.filter_journal_entry')->middleware('permission:journal_entry.access');
    // Tampilkan hasil filter
    Route::get('filter_journal_entry/result', [JournalEntryController::class, 'journalEntryFilter'])->name('journal_entry.filter_journal_entry_result');

    Route::delete('journal_entry_truncate', [JournalEntryController::class, 'truncate'])->name('journal_entry.truncate')->middleware('permission:journal_entry.delete');
    Route::delete('journal_entry_delete_range', [JournalEntryController::class, 'deleteByRange'])->name('journal_entry.delete_range')->middleware('permission:journal_entry.delete');
    Route::delete('journal_entry_bulk_delete', [JournalEntryController::class, 'bulkDestroy'])->name('journal_entry.bulk_delete')->middleware('permission:journal_entry.delete');

    Route::get('journal_entry/{id}/quick-view', [JournalEntryController::class, 'quickView'])->name('journal_entry.quick_view')->middleware('permission:journal_entry.access');
    Route::resource('journal_entry', 'JournalEntryController')->middleware('permission:journal_entry.access');

    Route::post('/import/journal_entry', [ImportController::class, 'importjournal_entry'])->name('import.journal_entry');
    Route::get('/export/journal_entry', [ExportController::class, 'exportJournalEntry'])->name('export.journal_entry');


    Route::get('/search-account', [DepartemenAkunController::class, 'search']);

    // general report
    Route::get('report_account', [ReportController::class, 'reportAccount'])->name('report.account');
    Route::get('report_klasifikasi', [ReportController::class, 'reportKlasifikasi'])->name('report.klasifikasi');
    Route::get('/report/departemen-akun', [ReportController::class, 'reportDepartemenAkun'])->name('report.departemen-akun');


    // report

    // fiscal
    Route::get('fiscal_account_persamaan', [FiscalAccountPersamaanController::class, 'index'])->name('fiscal_account_persamaan.index');
    Route::get('fiscal_account_persamaan/{id}', [FiscalAccountPersamaanController::class, 'show'])->name('fiscal_account_persamaan.show');
    Route::get('fiscal', [FiscalController::class, 'fiscal'])->name('fiscal.fiscal_report')->middleware('permission:fiscal.access');
    Route::resource('fiscal_account', 'FiscalAccountController')->middleware('permission:fiscal_account.access');
    Route::get('fiscal_setting', [FiscalSettingController::class, 'index'])->name('fiscal_setting.index')->middleware('permission:fiscal.access');
    Route::post('fiscal_setting', [FiscalSettingController::class, 'update'])->name('fiscal_setting.update')->middleware('permission:fiscal.access');
    Route::get('/export/fiscal', [ExportController::class, 'exportfiscal'])->name('export.fiscal');

    Route::post('/import/fiscal', [ImportController::class, 'importfiscal'])->name('import.fiscal');


    Route::get('perhitungan_pajak_penghasilan', [PerhitunganPajakPenghasilanController::class, 'index'])->name('perhitungan_pajak_penghasilan.index')->middleware('permission:perhitungan_pajak_penghasilan.access');
    Route::post('/perhitungan-pajak/kredit-pajak/store', [PerhitunganPajakPenghasilanController::class, 'store'])->name('perhitungan_pajak.saveKreditPajak');
    Route::post('/perhitungan-pajak/kredit-pajak/store', [PerhitunganPajakPenghasilanController::class, 'store'])->name('perhitungan_pajak.saveKreditPajak');
    Route::put('/perhitungan-pajak/kredit-pajak/{id}', [PerhitunganPajakPenghasilanController::class, 'update'])->name('perhitungan_pajak.updateKreditPajak');



    // neraca
    Route::get('filter_neraca', [NeracaController::class, 'neracaFilter'])->name('neraca.filter_neraca')->middleware('permission:neraca.access');
    Route::get('neraca_report', [NeracaController::class, 'neracaReport'])->name('neraca.neraca_report');
    Route::get('/neraca/export', [NeracaController::class, 'export'])->name('neraca.export');

    // neraca departemen
    Route::get('filter_neraca_departemen', [NeracaDepartemenController::class, 'filterNeracaDepartemen'])->name('neraca_departemen.filter_neraca_departemen')->middleware('permission:neraca_departemen.access');
    Route::get('neraca_departemen_report', [NeracaDepartemenController::class, 'neracaDepartemenReport'])->name('neraca_departemen.report')->middleware('permission:neraca_departemen.access');
    Route::get('neraca_departemen_report_view', [NeracaDepartemenController::class, 'neracaDepartemenReport'])->name('neraca_departemen.neraca_departemen_report')->middleware('permission:neraca_departemen.access');
    Route::get('/neraca_departemen/export', [NeracaDepartemenController::class, 'export'])->name('neraca_departemen.export')->middleware('permission:neraca_departemen.access');
    Route::get('/neraca_departemen/rk_audit', [NeracaDepartemenController::class, 'rkAuditDetails'])->name('neraca_departemen.rk_audit')->middleware('permission:neraca_departemen.access');


    // arus kas 
    Route::get('filter_arus_kas', [ReportController::class, 'aruskasFilter'])->name('arus_kas.filter_arus_kas')->middleware('permission:arus_kas.access');
    Route::get('arus_kas_report', [ReportController::class, 'reportArusKas'])->name('arus_kas.arus_kas_report');
    Route::get('arus_kas_export', [ReportController::class, 'exportArusKas'])->name('arus_kas.export');



    // buku besar
    Route::get('filter_buku_besar', [BukuBesarController::class, 'bukuBesarFilter'])->name('buku_besar.filter_buku_besar')->middleware('permission:buku_besar.access');
    Route::get('/laporan/buku-besar', [BukuBesarController::class, 'bukuBesarReport'])->name('buku_besar.buku_besar_report');
    Route::get('/buku_besar/export', [BukuBesarController::class, 'export'])->name('buku_besar.export');
    Route::patch('/buku_besar/detail/{id}/contra', [BukuBesarController::class, 'updateContra'])->name('buku_besar.update_contra');
    Route::patch('/buku_besar/detail/{id}/departemen', [BukuBesarController::class, 'updateDepartemen'])->name('buku_besar.update_departemen');
    Route::patch('/buku_besar/bulk/contra', [BukuBesarController::class, 'bulkUpdateContra'])->name('buku_besar.bulk_update_contra');
    Route::patch('/buku_besar/bulk/departemen', [BukuBesarController::class, 'bulkUpdateDepartemen'])->name('buku_besar.bulk_update_departemen');


    // Trial Balance
    Route::get('filter_trial_balance', [TrialBalanceController::class, 'trialBalanceFilter'])->name('trial_balance.filter_trial_balance')->middleware('permission:trial_balance.access');
    Route::get('trial_balance_report', [TrialBalanceController::class, 'trialBalanceReport'])->name('trial_balance.trial_balance_report');
    Route::get('/trial_balance/export', [TrialBalanceController::class, 'export'])->name('trial_balance.export');

    // income statement
    Route::get('filter_income_statement', [IncomeStatementController::class, 'incomeStatementFilter'])->name('income_statement.filter_income_statement')->middleware('permission:income_statement.access');
    Route::get('income_statement', [IncomeStatementController::class, 'incomeStatementReport'])->name('income_statement.income_statement_report');
    Route::get('/income_statement/export', [IncomeStatementController::class, 'export'])->name('income_statement.export');

    // laba rugi departemen (modern PSAK/IFRS multi-step matrix & single report)
    Route::get('filter_laba_rugi_departemen', [LabaRugiDepartemenController::class, 'filter'])->name('laba_rugi_departemen.filter');
    Route::get('laba_rugi_departemen_report', [LabaRugiDepartemenController::class, 'report'])->name('laba_rugi_departemen.report');
    Route::get('laba_rugi_departemen_report_view', [LabaRugiDepartemenController::class, 'report'])->name('laba_rugi_departemen.laba_rugi_departemen_report');
    Route::get('/laba_rugi_departemen/export', [LabaRugiDepartemenController::class, 'export'])->name('laba_rugi_departemen.export');
    Route::get('/laba_rugi_departemen/reconcile_audit', [LabaRugiDepartemenController::class, 'reconcileAudit'])->name('laba_rugi_departemen.reconcile_audit');

    // Backward-compatible alias for existing menu items
    Route::get('filter_income_statement_departement', [LabaRugiDepartemenController::class, 'filter'])->name('income_statement.filter_income_statement_departement');
    Route::get('income_satetement_departement', [LabaRugiDepartemenController::class, 'report'])->name('income_statement.income_statement_departement');
    Route::get('/income_statement_departement/export', [LabaRugiDepartemenController::class, 'export'])->name('income_statement_departement.export');




    // sales menu 
    Route::resource('linkedAccountSales', 'linkedAccountSalesController')->middleware('permission:linked_account_sales.access');
    // linked asset controller
    Route::resource('linkedAccountAsset', 'LinkedAccountAssetController')->middleware('permission:linked_account_asset.access');
    Route::resource('sales_option', 'salesOptionsController')->middleware('permission:option_sales.access');
    Route::resource('sales_discount', 'SalesDiscountController')->middleware('permission:sales_discount.access');

    Route::resource('sales_person', 'SalesPersonController')->middleware('permission:sales_person.access');


    Route::resource('item_category', 'ItemCategoryController')->middleware('permission:item_category.access');

    Route::resource('items', 'ItemController')->middleware('permission:items.access');
    // export dan import items
    Route::get('/export/items', [ExportController::class, 'exportItems'])->name('export.items');
    Route::post('/import/items', [ImportController::class, 'importItems'])->name('import.items');
    // payment method 
    Route::resource('PaymentMethod', 'PaymentMethodController')->middleware('permission:payment_method.access');
    // routes/web.php
    Route::get('/payment-methods/{id}/accounts', [\App\Http\Controllers\PaymentMethodController::class, 'accounts'])
        ->name('payment-methods.accounts');


    Route::get('/customers/search', [CustomersController::class, 'search'])->name('customers.search');
    Route::resource('customers', 'CustomersController')->middleware('permission:customers.access');
    // export dan import customers
    Route::get('/export/customers', [ExportController::class, 'exportcustomers'])->name('export.customers');
    Route::post('/import/customers', [ImportController::class, 'importcustomers'])->name('import.customers');

    Route::resource('sales_order', 'SalesOrderController')->middleware('permission:sales_orders.access');
    Route::get('/sales_order/{id}/print', [SalesOrderController::class, 'print'])->name('sales_order.print');
    Route::get('/sales_order/{id}/pdf', [SalesOrderController::class, 'downloadPdf'])->name('sales_order.pdf');
    Route::get('sales-orders/documents', [SalesOrderDocumentController::class, 'index'])
        ->name('sales_orders.documents.index');

    Route::prefix('sales-orders/{salesOrder}')->group(function () {
        Route::post('/documents', [App\Http\Controllers\SalesOrderDocumentController::class, 'store'])->name('sales_orders.documents.store');
        Route::get('/documents/{document}/download', [App\Http\Controllers\SalesOrderDocumentController::class, 'download'])->name('sales_orders.documents.download');
        Route::delete('/documents/{document}', [App\Http\Controllers\SalesOrderDocumentController::class, 'destroy'])->name('sales_orders.documents.destroy');
    });

    Route::get('/sales-orders/{id}/pdf', [App\Http\Controllers\SalesOrderController::class, 'exportPdf'])
        ->name('sales_order.pdf');


    Route::resource('sales_invoice', 'SalesInvoiceController')->middleware('permission:sales_invoice.access');
    Route::get('/sales_invoice/{id}/print', [SalesInvoiceController::class, 'print'])->name('sales_invoice.print');
    Route::get('/sales_invoice/{id}/pdf', [SalesInvoiceController::class, 'downloadPdf'])->name('sales_invoice.pdf');
    Route::get('sales-invoice/documents', [SalesInvoiceDocumentController::class, 'index'])
        ->name('sales_invoice.documents.index');

    Route::get('/sales-invoice/{id}/pdf', [App\Http\Controllers\SalesInvoiceController::class, 'exportPdf'])
        ->name('sales_invoice.pdf');
    Route::prefix('sales-invoice/{salesInvoice}')->group(function () {
        Route::post('/documents', [App\Http\Controllers\SalesInvoiceDocumentController::class, 'store'])->name('sales_invoice.documents.store');
        Route::get('/documents/{document}/download', [App\Http\Controllers\SalesInvoiceDocumentController::class, 'download'])->name('sales_invoice.documents.download');
        Route::delete('/documents/{document}', [App\Http\Controllers\SalesInvoiceDocumentController::class, 'destroy'])->name('sales_invoice.documents.destroy');
    });

    Route::get('/sales_invoice/get-items/{salesOrderId}', [SalesInvoiceController::class, 'getItemsFromSalesOrder']);
    Route::resource('sales_deposits', 'SalesDepositController')->middleware('permission:deposits.access');
    Route::resource('receipts', 'ReceiptsController')->middleware('permission:receipts.access');
    // web.php
    Route::get('/get-invoices/{customer}', [ReceiptsController::class, 'getInvoices']);




    // end sales menu

    // purchases menu
    Route::resource('linkedAccountPurchases', 'LinkedAccountPurchasesControlller')->middleware('permission:linked_account_purchase.access');
    Route::resource('purchases_options', 'PurchasesOptionsController')->middleware('permission:option_purchase.access');


    Route::get('/vendors/search', [VendorsController::class, 'search'])->name('vendors.search');
    Route::resource('vendors', 'VendorsController')->middleware('permission:vendor.access');
    Route::get('/vendor/{vendor}/invoices-prepayments', [VendorsController::class, 'getInvoicesAndPrepayments']);
    Route::get('/vendor/{id}/invoices', [VendorsController::class, 'getInvoices']);



    // export dan import items
    Route::get('/export/vendors', [ExportController::class, 'exportVendors'])->name('export.vendors');
    Route::post('/import/vendors', [ImportController::class, 'ImportVendors'])->name('import.vendors');

    // purchase order
    Route::resource('purchase_order', 'PurchaseOrderController')->middleware('permission:purchase_order.access');
    Route::get('/purchase_order/{id}/print', [PurchaseOrderController::class, 'print'])->name('purchase_order.print');
    Route::get('/purchase_order/{id}/pdf', [PurchaseOrderController::class, 'downloadPdf'])->name('purchase_order.pdf');

    Route::get('purchase-order/documents', [PurchaseOrderDocumentController::class, 'index'])
        ->name('purchase_order.documents.index');
    Route::prefix('purchase-order/{purchasOrder}')->group(function () {
        Route::post('/documents', [App\Http\Controllers\PurchaseOrderDocumentController::class, 'store'])->name('purchase_order.documents.store');
        Route::get('/documents/{document}/download', [App\Http\Controllers\PurchaseOrderDocumentController::class, 'download'])->name('purchase_order.documents.download');
        Route::delete('/documents/{document}', [App\Http\Controllers\PurchaseOrderDocumentController::class, 'destroy'])->name('purchase_order.documents.destroy');
    });

    Route::get('/search-item', [ItemController::class, 'search']);

    // purchase invoice
    Route::resource('purchase_invoice', 'PurchaseInvoiceController')->middleware('permission:purchase_invoice.access');
    Route::get('/purchase_invoice/{id}/print', [PurchaseInvoiceController::class, 'print'])->name('purchase_invoice.print');
    Route::get('/purchase_invoice/{id}/pdf', [PurchaseInvoiceController::class, 'downloadPdf'])->name('purchase_invoice.pdf');
    Route::get('purchase-invoice/documents', [PurchaseInvoiceDocumentController::class, 'index'])
        ->name('purchase_invoice.documents.index');
    Route::prefix('purchase-invoice/{purchaseInvoice}')->group(function () {
        Route::post('/documents', [App\Http\Controllers\PurchaseInvoiceDocumentController::class, 'store'])->name('purchase_invoice.documents.store');
        Route::get('/documents/{document}/download', [App\Http\Controllers\PurchaseInvoiceDocumentController::class, 'download'])->name('purchase_invoice.documents.download');
        Route::delete('/documents/{document}', [App\Http\Controllers\PurchaseInvoiceDocumentController::class, 'destroy'])->name('purchase_invoice.documents.destroy');
    });
    Route::get('/purchase_invoice/get-items/{purchaseOrderId}', [PurchaseInvoiceController::class, 'getItemsFromPurchaseOrder']);


    // payment
    Route::resource('payment', 'PaymentController')->middleware('permission:payment_purchase.access');

    // payment expense
    Route::get('payment_expense/ajax/generate-voucher', 'PaymentExpenseController@ajaxGetVoucherNumber')->name('payment_expense.generate_voucher')->middleware('permission:payment_expense.access');
    Route::get('payment_expense/{id}/print-voucher', 'PaymentExpenseController@printVoucher')->name('payment_expense.print_voucher')->middleware('permission:payment_expense.access');
    Route::resource('payment_expense', 'PaymentExpenseController')->middleware('permission:payment_expense.access');
    Route::resource('prepayment', 'PrepaymentController')->middleware('permission:prepayment_purchase.access');



    // end purchases menu

    // inventory menu 
    Route::resource('inventory', 'InventoryController')->middleware('permission:inventory.access');
    Route::resource('lokasi_inventory', 'LokasiInventoryController')->middleware('permission:lokasi_inventory.access');
    Route::resource('price_list_inventory', 'PriceListInventoryController')->middleware('permission:price_list_inventory.access');
    Route::resource('options_inventory', 'OptionsInventoryController')->middleware('permission:options_inventory.access');

    Route::resource('build_of_bom', 'BuildOfBomController')->middleware('permission:Build from Bom.access');
    Route::get('/items/{id}/info', [ItemController::class, 'info'])->name('items.info');
    Route::get('/items/by-location/{location}', [ItemController::class, 'byLocation']);
    Route::get('/items/{id}/bom', [ItemController::class, 'bom'])->name('items.bom');
    Route::get('/items/{id}/accounts', [App\Http\Controllers\ItemController::class, 'getAccounts']);
    Route::get('/items/{id}/bom', [App\Http\Controllers\ItemController::class, 'getBom']);

    Route::resource('item_assembly', 'ItemAssemblyController')->middleware('permission:Build from item assembly.access');


    Route::resource('transfer_inventory', 'TransferInventoryController')->middleware('permission:Transfer inventory.access');

    // end inventory  menu

    // budgeting menu 
    Route::resource('approval_step', 'ApprovalStepController')->middleware('permission:approval_step.access');
    Route::get('/rekening/search', [RekeningController::class, 'search'])->name('rekening.search');

    Route::resource('rekening', 'RekeningController')->middleware('permission:rekening.access');

    Route::resource('pengajuan', 'PengajuanController')->middleware('permission:create_budget.access');
    Route::get('approval', [PengajuanApprovalController::class, 'index'])->name('pengajuan.approval.index');
    // payroll menu

    // level karyawan
    Route::resource('LevelKaryawan', 'LevelKaryawanController')->middleware('permission:level_karyawan.access');
    // unit kerja
    Route::resource('unit_kerja', 'UnitKerjaController')->middleware('permission:unit.access');

    // jabatan
    Route::get('/jabatan/search', [JabatanController::class, 'search'])->name('jabatan.search');
    Route::resource('jabatan', 'JabatanController')->middleware('permission:jabatan.access');
    // ptkp
    Route::resource('ptkp', 'PtkpController')->middleware('permission:ptkp.access');

    // absensi menu 
    Route::get('/scan-rfid', [AbsensiController::class, 'form'])->name('absensi.form')->middleware('permission:absensi.access');
    Route::post('/scan-rfid', [AbsensiController::class, 'scan'])->name('absensi.scan');

    // export dan import ptkp 
    Route::get('/export/Ptkp', [ExportController::class, 'exportPtkp'])->name('export.Ptkp');
    Route::post('/import/Ptkp', [ImportController::class, 'importPtkp'])->name('import.Ptkp');

    // komponen penghasilan
    Route::resource('komponen_penghasilan', 'KomponenPenghasilanController')->middleware('permission:komponen_penghasilan.access');
    Route::get('/export/KomponenPenghasilan', [ExportController::class, 'exportKomponenPenghasilan'])->name('export.KomponenPenghasilan');
    Route::post('/import/KomponenPenghasilan', [ImportController::class, 'importKomponenPenghasilan'])->name('import.KomponenPenghasilan');
    // Tax Rates
    Route::resource('tax_rates', 'TaxRatesController')->middleware('permission:tax_rates.access');
    // export dan import tax rates 
    Route::get('/export/TaxRates', [ExportController::class, 'exportTaxRates'])->name('export.TaxRates');
    Route::post('/import/TaxRates', [ImportController::class, 'importTaxRates'])->name('import.TaxRates');
    // employee

    Route::get('/employee/search', [EmployeeController::class, 'search'])->name('employee.search');
    Route::resource('employee', 'EmployeeController')->middleware('permission:employee.access');

    Route::get('/export/Employee', [ExportController::class, 'exportEmployee'])->name('export.Employee');
    Route::post('/import/Employee', [ImportController::class, 'importEmployee'])->name('import.Employee');
    // wahana
    Route::resource('wahana', 'WahanaController')->middleware('permission:wahana.access');
    Route::get('/export/Wahana', [ExportController::class, 'exportWahana'])->name('export.Wahana');
    Route::post('/import/Wahana', [ImportController::class, 'importWahana'])->name('import.Wahana');
    // target wahana
    Route::resource('target_wahana', 'TargetWahanaController')->middleware('permission:target_wahana.access');
    Route::get('/get-wahana-by-unit/{id}', [UnitKerjaController::class, 'getWahanaByUnit']);
    Route::get('/wahana-by-unit/{id}', [TargetWahanaController::class, 'getWahanaByUnit']);
    Route::post('/import/TargetWahana', [ImportController::class, 'importTargetWahana'])->name('import.TargetWahana');

    // closing harian 
    Route::resource('closing_harian', 'ClosingHarianController')->middleware('permission:closing_harian.access');
    Route::get('/closing_harian/{id}/print', [ClosingHarianController::class, 'print'])->name('closing_harian.print');
    Route::get('/closing_harian/{id}/pdf', [ClosingHarianController::class, 'downloadPdf'])->name('closing_harian.pdf');

    // Rekonsiliasi Closing Harian vs Kas & Bank
    Route::get('/rekonsiliasi_closing', [RekonsiliasiClosingController::class, 'index'])
        ->name('rekonsiliasi_closing.index')
        ->middleware('permission:rekonsiliasi_closing.access');
    Route::get('/rekonsiliasi_closing/detail', [RekonsiliasiClosingController::class, 'detail'])
        ->name('rekonsiliasi_closing.detail')
        ->middleware('permission:rekonsiliasi_closing.access');
    Route::get('/rekonsiliasi_closing/export', [RekonsiliasiClosingController::class, 'export'])
        ->name('rekonsiliasi_closing.export')
        ->middleware('permission:rekonsiliasi_closing.access');

    // Unassigned Watchdog (Audit Transaksi Tanpa Departemen)
    Route::get('/unassigned_watchdog', [UnassignedWatchdogController::class, 'index'])
        ->name('unassigned_watchdog.index')
        ->middleware('permission:unassigned_watchdog.access');
    Route::post('/unassigned_watchdog/assign', [UnassignedWatchdogController::class, 'assign'])
        ->name('unassigned_watchdog.assign')
        ->middleware('permission:unassigned_watchdog.update');
    Route::post('/unassigned_watchdog/assign_bulk', [UnassignedWatchdogController::class, 'assignBulk'])
        ->name('unassigned_watchdog.assign_bulk')
        ->middleware('permission:unassigned_watchdog.update');
    Route::get('/unassigned_watchdog/export', [UnassignedWatchdogController::class, 'export'])
        ->name('unassigned_watchdog.export')
        ->middleware('permission:unassigned_watchdog.access');

    // Assigned Watchdog (Audit & Adjustment Departemen)
    Route::get('/assigned_watchdog', [AssignedWatchdogController::class, 'index'])
        ->name('assigned_watchdog.index')
        ->middleware('permission:assigned_watchdog.access');
    Route::post('/assigned_watchdog/reassign', [AssignedWatchdogController::class, 'reassign'])
        ->name('assigned_watchdog.reassign')
        ->middleware('permission:assigned_watchdog.update');
    Route::post('/assigned_watchdog/unassign', [AssignedWatchdogController::class, 'unassign'])
        ->name('assigned_watchdog.unassign')
        ->middleware('permission:assigned_watchdog.update');
    Route::post('/assigned_watchdog/reassign_bulk', [AssignedWatchdogController::class, 'reassignBulk'])
        ->name('assigned_watchdog.reassign_bulk')
        ->middleware('permission:assigned_watchdog.update');
    Route::post('/assigned_watchdog/unassign_bulk', [AssignedWatchdogController::class, 'unassignBulk'])
        ->name('assigned_watchdog.unassign_bulk')
        ->middleware('permission:assigned_watchdog.update');
    Route::get('/assigned_watchdog/audit/{id}', [AssignedWatchdogController::class, 'auditHistory'])
        ->name('assigned_watchdog.audit')
        ->middleware('permission:assigned_watchdog.access');
    Route::get('/assigned_watchdog/audit_history/{detailId}', [AssignedWatchdogController::class, 'auditHistory'])
        ->name('assigned_watchdog.audit_history')
        ->middleware('permission:assigned_watchdog.access');
    Route::get('/assigned_watchdog/audit_logs', [AssignedWatchdogController::class, 'auditLogs'])
        ->name('assigned_watchdog.audit_logs')
        ->middleware('permission:assigned_watchdog.access');
    Route::get('/assigned_watchdog/export', [AssignedWatchdogController::class, 'export'])
        ->name('assigned_watchdog.export')
        ->middleware('permission:assigned_watchdog.access');

    // Journal Audit & Kamus Indikator (Deteksi Salah Akun & Ambigu)
    Route::get('/journal-audit', [\App\Http\Controllers\JournalAuditController::class, 'index'])
        ->name('journal_audit.index')
        ->middleware('permission:journal_entry.access');
    Route::get('/journal-audit/export', [\App\Http\Controllers\JournalAuditController::class, 'export'])
        ->name('journal_audit.export')
        ->middleware('permission:journal_entry.access');
    Route::get('/audit-jurnal', [\App\Http\Controllers\JournalAuditController::class, 'index'])
        ->name('journal_audit.alias_dash')
        ->middleware('permission:journal_entry.access');
    Route::get('/audit_jurnal', [\App\Http\Controllers\JournalAuditController::class, 'index'])
        ->name('journal_audit.alias_underscore')
        ->middleware('permission:journal_entry.access');
    Route::post('/journal-audit/scan', [\App\Http\Controllers\JournalAuditController::class, 'scan'])
        ->name('journal_audit.scan')
        ->middleware('permission:journal_entry.access');
    Route::post('/journal-audit/remediate', [\App\Http\Controllers\JournalAuditController::class, 'remediateSingle'])
        ->name('journal_audit.remediate')
        ->middleware('permission:journal_entry.access');
    Route::post('/journal-audit/remediate-bulk', [\App\Http\Controllers\JournalAuditController::class, 'remediateBulk'])
        ->name('journal_audit.remediate_bulk')
        ->middleware('permission:journal_entry.access');
    Route::post('/journal-audit/ignore/{id}', [\App\Http\Controllers\JournalAuditController::class, 'ignoreAnomaly'])
        ->name('journal_audit.ignore')
        ->middleware('permission:journal_entry.access');
    Route::get('/journal-audit/rules', [\App\Http\Controllers\JournalAuditController::class, 'rulesIndex'])
        ->name('journal_audit.rules')
        ->middleware('permission:journal_entry.access');
    Route::post('/journal-audit/rules', [\App\Http\Controllers\JournalAuditController::class, 'storeRule'])
        ->name('journal_audit.rules.store')
        ->middleware('permission:journal_entry.access');
    Route::put('/journal-audit/rules/{id}', [\App\Http\Controllers\JournalAuditController::class, 'updateRule'])
        ->name('journal_audit.rules.update')
        ->middleware('permission:journal_entry.access');
    Route::delete('/journal-audit/rules/{id}', [\App\Http\Controllers\JournalAuditController::class, 'destroyRule'])
        ->name('journal_audit.rules.destroy')
        ->middleware('permission:journal_entry.access');


    // target unit
    Route::resource('target_unit', 'TargetUnitController')->middleware('permission:target_unit.access');
    Route::get('/komponen-by-level/{id}', [TargetUnitController::class, 'getKomponenByLevel']);
    Route::get('/export/TargetUnit', [ExportController::class, 'exportTargetUnit'])->name('export.TargetUnit');
    Route::post('/import/TargetUnit', [ImportController::class, 'importTargetUnit'])->name('import.TargetUnit');

    // transaksi Wahana
    Route::resource('transaksi_wahana', 'TransaksiWahanaController')->middleware('permission:transaksi_wahana.access');
    Route::get('/export/TransaksiWahana', [ExportController::class, 'exportTransaksiWahana'])->name('export.TransaksiWahana');
    Route::post('/import/TransaksiWahana', [ImportController::class, 'importTransaksiWahana'])->name('import.TransaksiWahana');

    // shift karyawan wahana
    Route::get('/get-karyawan-available', [ShiftKaryawanWahanaController::class, 'getKaryawanAvailable'])
        ->name('karyawan.available');

    Route::resource('shift_karyawan', 'ShiftKaryawanWahanaController')->middleware('permission:shift_karyawan.access');
    Route::get('/get-jenis-hari/{unit_kerja_id}', [ShiftKaryawanWahanaController::class, 'getJenisHari']);
    Route::get('/get-employee-shift-by-unit/{unitId}/{tanggal}', [ShiftKaryawanWahanaController::class, 'getEmployeeShiftByUnit']);

    Route::get('/export/ShiftKaryawan', [ExportController::class, 'exportShiftKaryawan'])->name('export.ShiftKaryawan');
    Route::get('/export/ShiftKaryawanTabel', [ExportController::class, 'exportShiftKaryawanTabel'])->name('export.ShiftKaryawanTabel');
    Route::get('/export-shift-karyawan-pdf', [ShiftKaryawanWahanaController::class, 'exportShiftKaryawanPDF'])
        ->name('shift_karyawan.ShiftKaryawanPDF');

    Route::post('/import/ShiftKaryawan', [ImportController::class, 'importShiftKaryawan'])->name('import.ShiftKaryawan');

    Route::get('/wahana/by-unit/{unit}', [WahanaController::class, 'byUnit'])
        ->name('wahana.byUnit');


    Route::get('/shift-wahana/assignments', [ShiftKaryawanWahanaController::class, 'listByUnitDate'])
        ->name('shift_wahana.assignments'); // ?unit_id=..&tanggal=YYYY-MM-DD
    Route::get('/off-days', [EmployeeOffDayController::class, 'index'])->name('off_days.index');     // ?tanggal=YYYY-MM-DD&unit_id=optional
    Route::post('/off-days', [EmployeeOffDayController::class, 'store'])->name('off_days.store');    // {employee_id[], tanggal, catatan?}
    Route::delete('/off-days/{off}', [EmployeeOffDayController::class, 'destroy'])->name('off_days.destroy');

    // jenis hari
    Route::resource('jenis_hari', 'JenisHariController')->middleware('permission:jenis_hari.access');

    Route::resource('jam_kerja', 'JamKerjaController')->middleware('permission:jam_kerja.access');
    // komposisi Gaji
    Route::resource('komposisi_gaji', 'KomposisiGajiController')->middleware('permission:komposisi_gaji.access');
    Route::get('/get-komponen-by-karyawan/{id}', [KomposisiGajiController::class, 'getKomponenByKaryawan']);
    Route::delete('komposisi_gaji/detail/{id}', [KomposisiGajiController::class, 'destroyDetail'])->name('komposisi_gaji.detail.destroy');

    Route::resource('pembayaran_gaji', 'PembayaranGajiController')->middleware('permission:pembayaran_gaji.access');
    Route::resource('pembayaran_gaji_nonstaff', 'PembayaranGajiNonStaffController')->middleware('permission:pembayaran_gaji_nonstaff.access');
    Route::get('/get-pembayaran-gaji-by-karyawan/{id}', [PembayaranGajiController::class, 'getKomposisiGajiByKaryawan']);

    // slip gaji staff
    Route::get('/slip-gaji', [SlipGajiController::class, 'index'])->name('slip.index')->middleware('permission:slip_gaji.access');
    Route::get('/slip-gaji/{id}', [SlipGajiController::class, 'show'])->name('slip.show')->middleware('permission:slip_gaji_nonstaff.access');
    Route::get('/slip-gaji/{id}/download', [SlipGajiController::class, 'download'])->name('slip.download');

    // crew shift karyawan
    Route::resource('crew_shift_karyawan', 'CrewShiftKaryawanControlller')->middleware('permission:crew_shift_karyawan.access');

    // group unit
    Route::resource('group_unit', 'GroupUnitController')->middleware('permission:group_unit.access');

    // rekap absensi
    Route::get('/rekap-absensi', [ReportController::class, 'filter'])->name('report.absensi.filter')->middleware('permission:rekap_absensi.access');
    Route::get('/rekap-absensi/hasil', [ReportController::class, 'hasil'])->name('report.absensi.hasil');
    // report target wahana 
    Route::prefix('reports')->group(function () {
        Route::get('/target-wahana/filter', [ReportController::class, 'filterWahana'])->name('report.target_wahana.filter')->middleware('permission:rekap_target_wahana.access');
        Route::get('/target-wahana/result', [ReportController::class, 'resultWahana'])->name('report.target_wahana.result');
    });
    Route::get('/get-wahana-by-unit/{unitId}', [ReportController::class, 'getWahanaByUnit'])->name('report.getWahanaByUnit');
    Route::get('/reports/target-wahana/pdf', [ReportController::class, 'exportPdfWahana'])->name('report.target_wahana.pdf');
    Route::get('/reports/target-wahana/excel', [ReportController::class, 'exportExcelWahana'])->name('report.target_wahana.excel');
    // routes/web.php
    Route::get('/report/absensi/pdf', [ReportController::class, 'exportPdf'])->name('report.absensi.pdf');
    Route::get('/report/absensi/excel', [ReportController::class, 'exportExcel'])->name('report.absensi.excel');

    // slip gaji non staff
    Route::get('/slip-gaji-nonstaff', [SlipGajiController::class, 'indexNonStaff'])->name('slip.nonStaff.index')->middleware('permission:slip_gaji_nonstaff.access');
    Route::get('/slip-gaji-nonstaff/{id}', [SlipGajiController::class, 'showNonSataff'])->name('slip.nonStaffshow');
    Route::get('/slip-gaji-nonstaff/{id}/download', [SlipGajiController::class, 'downloadNonStaff'])->name('slip.nonStaffdownload');

    Route::get('/bonus_karyawan', [BonusKaryawanController::class, 'index'])->name('bonus_karyawan.index')->middleware('permission:bonus_karyawan.access');

    // end payroll menu

    // asset menu 
    Route::resource('kategori_asset', 'KategoriAssetController')->middleware('permission:kategori_asset.access');
    Route::resource('lokasi', 'LokasiController')->middleware('permission:lokasi.access');
    Route::resource('masa_manfaat', 'MasaManfaatController')->middleware('permission:masa_manfaat.access');
    Route::resource('metode_penyusutan', 'MetodePenyusutanController')->middleware('permission:metode_penyusutan.access');

    Route::resource('tangible_asset', 'TangibleAssetController')->middleware('permission:tangible_asset.access');
    Route::get('/export/TangibleAsset', [ExportController::class, 'exportTangibleAsset'])->name('export.TangibleAsset');
    Route::post('/import/TangibleAsset', [ImportController::class, 'importTangibleAsset'])->name('import.TangibleAsset');

    Route::resource('intangible_asset', 'IntangibleAssetController')->middleware('permission:intangible_asset.access');
    Route::get('/export/IntangibleAsset', [ExportController::class, 'exportIntangibleAsset'])->name('export.IntangibleAsset');
    Route::post('/import/IntangibleAsset', [ImportController::class, 'importIntangibleAsset'])->name('import.IntangibleAsset');

    // end asset menu


    // project 
    Route::get('view_project', [ProjectController::class, 'projectView'])->name('project.view_project');
    Route::get('edit_project', [ProjectController::class, 'projectEdit'])->name('project.edit_project');
    Route::resource('project', 'ProjectController');
    Route::get('/export/Project', [ExportController::class, 'exportProject'])->name('export.Project');
    Route::post('/import/Project', [ImportController::class, 'importProject'])->name('import.Project');
    // end project menu

    // maintenance menu
    Route::resource('start_new_year', 'StartNewYearController')->middleware('permission:year_book.access');

    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/start-new-year', [AccountingController::class, 'showStartNewYear'])->name('start_new_year')->middleware('permission:start_new_year.access');
        Route::post('/start-new-year', [AccountingController::class, 'startNewYearProcess'])->name('start_new_year_proses');
    });

    Route::get('activity_log', [ActivityLogController::class, 'index'])->name('activity_log.index')->middleware('permission:log_activity.access');
    Route::get('activity_log/export-pdf', [ActivityLogController::class, 'exportPdf'])->name('activity_log.export_pdf')->middleware('permission:log_activity.access');


    // end maintenance menu

    // taxes menu
    Route::resource('taxes', 'TaxesController')->middleware('permission:taxes.access');

    // Staging Journal Ingestion (Inbox Error)
    Route::get('staging_journal', [\App\Http\Controllers\StagingJournalController::class, 'index'])->name('staging_journal.index');
    Route::post('staging_journal/reprocess', [\App\Http\Controllers\StagingJournalController::class, 'reprocess'])->name('staging_journal.reprocess');
    Route::post('staging_journal/{id}/update', [\App\Http\Controllers\StagingJournalController::class, 'update'])->name('staging_journal.update');

    // end taxes menu
});

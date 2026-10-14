@php
    $themeColor = \App\Setting::get('theme_color', '#D552A3');

    // =========================================================================
    // 1. DEFINISI HIERARKI 14 MENU UTAMA SESUAI VISUAL NAVBAR RCA APLIKASI
    // (Menu Utama -> Sub-Menu -> Modul / Sub-sub Menu)
    // =========================================================================
    $navHierarchy = [
        'setup' => [
            'id' => 'setup',
            'num' => 1,
            'title' => 'Setup',
            'desc' => 'Pengaturan Global Perusahaan, Akuntansi, & Laporan',
            'icon' => 'fa-gear',
            'gate_perms' => ['setup_seluruh.access'],
            'submenus' => [
                'company' => [
                    'id' => 'setup_company',
                    'title' => 'Company',
                    'icon' => 'fa-building',
                    'gate_perms' => ['company.access'],
                    'modules' => [
                        'setting_setup' => ['label' => 'Settings', 'icon' => 'fa-sliders'],
                        'company_profile' => ['label' => 'Company Profile', 'icon' => 'fa-id-card'],
                        'taxpayers_company' => ['label' => 'Taxpayer Profile', 'icon' => 'fa-landmark'],
                    ]
                ],
                'general' => [
                    'id' => 'setup_general',
                    'title' => 'General',
                    'icon' => 'fa-sliders',
                    'gate_perms' => ['general.access'],
                    'modules' => [
                        'year_book' => ['label' => 'Year Book', 'icon' => 'fa-book-bookmark'],
                        'numbering' => ['label' => 'Numbering', 'icon' => 'fa-list-ol'],
                        'klasifikasi_akun' => ['label' => 'Account Classification', 'icon' => 'fa-tags'],
                        'chart_of_account' => ['label' => 'Accounts (COA)', 'icon' => 'fa-diagram-project'],
                        'departement' => ['label' => 'Departments', 'icon' => 'fa-sitemap'],
                        'linked_account_setup' => ['label' => 'Linked Account', 'icon' => 'fa-link'],
                        'sales_taxes' => ['label' => 'Taxes', 'icon' => 'fa-percent'],
                    ]
                ],
                'reports' => [
                    'id' => 'setup_reports',
                    'title' => 'Reports',
                    'icon' => 'fa-file-invoice',
                    'gate_perms' => ['reports_setup.access'],
                    'modules' => [
                        'report_account' => ['label' => 'Account List', 'icon' => 'fa-file-lines'],
                        'report_klasifikasi_akun' => ['label' => 'Account Classification List', 'icon' => 'fa-file-lines'],
                        'report_departemen_account' => ['label' => 'Department Accounts List', 'icon' => 'fa-file-lines'],
                    ]
                ],
            ]
        ],
        'sales' => [
            'id' => 'sales',
            'num' => 2,
            'title' => 'Sales',
            'desc' => 'Penjualan, Pelanggan, Faktur, & Penerimaan Piutang',
            'icon' => 'fa-cash-register',
            'gate_perms' => ['sales.access'],
            'submenus' => [
                'setup' => [
                    'id' => 'sales_setup',
                    'title' => 'Setup',
                    'icon' => 'fa-gear',
                    'gate_perms' => ['setup_sales.access'],
                    'modules' => [
                        'linked_account_sales' => ['label' => 'Linked Accounts', 'icon' => 'fa-link'],
                        'option_sales' => ['label' => 'Options', 'icon' => 'fa-sliders'],
                        'sales_discount' => ['label' => 'Sales Discount', 'icon' => 'fa-percent'],
                    ]
                ],
                'data' => [
                    'id' => 'sales_data',
                    'title' => 'Data',
                    'icon' => 'fa-database',
                    'gate_perms' => ['data.access'],
                    'modules' => [
                        'payment_method' => ['label' => 'Payment Method', 'icon' => 'fa-credit-card'],
                        'customers' => ['label' => 'Customers', 'icon' => 'fa-users'],
                        'sales_person' => ['label' => 'Sales Person', 'icon' => 'fa-user-tie'],
                    ]
                ],
                'sales' => [
                    'id' => 'sales_ops',
                    'title' => 'Sales Transactions',
                    'icon' => 'fa-receipt',
                    'gate_perms' => [],
                    'modules' => [
                        'sales_orders' => ['label' => 'Sales Orders (SO)', 'icon' => 'fa-cart-shopping'],
                        'sales_invoice' => ['label' => 'Sales Invoices', 'icon' => 'fa-file-invoice-dollar'],
                        'deposits' => ['label' => 'Deposits', 'icon' => 'fa-money-bill-transfer'],
                        'receipts' => ['label' => 'Receipts', 'icon' => 'fa-hand-holding-dollar'],
                    ]
                ],
            ]
        ],
        'purchases' => [
            'id' => 'purchases',
            'num' => 3,
            'title' => 'Purchases',
            'desc' => 'Pembelian, Pemasok / Vendor, & Pembayaran Hutang',
            'icon' => 'fa-basket-shopping',
            'gate_perms' => ['purchase.access'],
            'submenus' => [
                'setup' => [
                    'id' => 'purchases_setup',
                    'title' => 'Setup',
                    'icon' => 'fa-gear',
                    'gate_perms' => ['setup_purchase.access'],
                    'modules' => [
                        'linked_account_purchase' => ['label' => 'Linked Account', 'icon' => 'fa-link'],
                        'option_purchase' => ['label' => 'Options', 'icon' => 'fa-sliders'],
                    ]
                ],
                'data' => [
                    'id' => 'purchases_data',
                    'title' => 'Data',
                    'icon' => 'fa-database',
                    'gate_perms' => [],
                    'modules' => [
                        'vendor' => ['label' => 'Vendors', 'icon' => 'fa-truck-field'],
                    ]
                ],
                'purchases' => [
                    'id' => 'purchases_ops',
                    'title' => 'Purchases Transactions',
                    'icon' => 'fa-file-lines',
                    'gate_perms' => [],
                    'modules' => [
                        'purchase_order' => ['label' => 'Purchases Orders (PO)', 'icon' => 'fa-file-lines'],
                        'purchase_invoice' => ['label' => 'Purchase Invoice', 'icon' => 'fa-file-invoice-dollar'],
                        'prepayment_purchase' => ['label' => 'Prepayments', 'icon' => 'fa-money-bill-wave'],
                        'payment_purchase' => ['label' => 'Payments', 'icon' => 'fa-money-check-dollar'],
                        'payment_expense' => ['label' => 'Payment Expense (Kas Keluar)', 'icon' => 'fa-wallet'],
                    ]
                ],
            ]
        ],
        'inventory' => [
            'id' => 'inventory',
            'num' => 4,
            'title' => 'Inventory',
            'desc' => 'Inventaris, Stok Barang, Perakitan BOM, & Transfer',
            'icon' => 'fa-warehouse',
            'gate_perms' => ['inventory.access'],
            'submenus' => [
                'setup' => [
                    'id' => 'inventory_setup',
                    'title' => 'Setup',
                    'icon' => 'fa-gear',
                    'gate_perms' => ['setting_inventory.access'],
                    'modules' => [
                        'options_inventory' => ['label' => 'Options', 'icon' => 'fa-sliders'],
                        'price_list_inventory' => ['label' => 'Price List', 'icon' => 'fa-tags'],
                        'lokasi_inventory' => ['label' => 'Locations', 'icon' => 'fa-map-pin'],
                        'kategori_inventory' => ['label' => 'Categories', 'icon' => 'fa-layer-group'],
                    ]
                ],
                'operational' => [
                    'id' => 'inventory_ops',
                    'title' => 'Inventory Operations',
                    'icon' => 'fa-boxes-stacked',
                    'gate_perms' => [],
                    'modules' => [
                        'inventory' => ['label' => 'Inventory & Services', 'icon' => 'fa-warehouse'],
                        'Build from Bom' => ['label' => 'Build From BOM', 'icon' => 'fa-gears'],
                        'Build from item assembly' => ['label' => 'Build From Item Assembly', 'icon' => 'fa-box-open'],
                        'Transfer inventory' => ['label' => 'Transfer Inventory', 'icon' => 'fa-truck-ramp-box'],
                    ]
                ],
            ]
        ],
        'asset' => [
            'id' => 'asset',
            'num' => 5,
            'title' => 'Asset',
            'desc' => 'Manajemen Aset Tetap, Berwujud, Tidak Berwujud, & Depresiasi',
            'icon' => 'fa-boxes-packing',
            'gate_perms' => ['asset.access'],
            'submenus' => [
                'setup' => [
                    'id' => 'asset_setup',
                    'title' => 'Setup',
                    'icon' => 'fa-gear',
                    'gate_perms' => ['setup_asset.access'],
                    'modules' => [
                        'setup_asset' => ['label' => 'Linked Account Asset', 'icon' => 'fa-link'],
                    ]
                ],
                'data' => [
                    'id' => 'asset_data',
                    'title' => 'Data',
                    'icon' => 'fa-database',
                    'gate_perms' => [],
                    'modules' => [
                        'kategori_asset' => ['label' => 'Category Asset', 'icon' => 'fa-shapes'],
                        'lokasi_asset' => ['label' => 'Location Asset', 'icon' => 'fa-location-dot'],
                        'masa_manfaat' => ['label' => 'Masa Manfaat', 'icon' => 'fa-hourglass-half'],
                        'metode_penyusutan' => ['label' => 'Metode Penyusutan', 'icon' => 'fa-chart-line'],
                    ]
                ],
                'operational' => [
                    'id' => 'asset_ops',
                    'title' => 'Asset Operations',
                    'icon' => 'fa-cube',
                    'gate_perms' => [],
                    'modules' => [
                        'tangible_asset' => ['label' => 'Tangible Asset', 'icon' => 'fa-cube'],
                        'intangible_asset' => ['label' => 'Intangible Asset', 'icon' => 'fa-cloud'],
                        'monthly_process' => ['label' => 'Monthly Process', 'icon' => 'fa-clock-rotate-left'],
                    ]
                ],
            ]
        ],
        'closing_harian' => [
            'id' => 'closing_harian',
            'num' => 6,
            'title' => 'Closing Harian',
            'desc' => 'Penutupan Kasir Harian, Wahana, & Rekonsiliasi Kas/Bank',
            'icon' => 'fa-calendar-check',
            'gate_perms' => [],
            'submenus' => [
                'setup' => [
                    'id' => 'closing_setup',
                    'title' => 'Setup',
                    'icon' => 'fa-gear',
                    'gate_perms' => [],
                    'modules' => [
                        'linked_account_closing' => ['label' => 'Linked Account', 'icon' => 'fa-link'],
                        'wahana' => ['label' => 'Wahana (Rides)', 'icon' => 'fa-ticket'],
                    ]
                ],
                'process' => [
                    'id' => 'closing_process',
                    'title' => 'Process',
                    'icon' => 'fa-arrows-rotate',
                    'gate_perms' => [],
                    'modules' => [
                        'closing_harian' => ['label' => 'Closing Harian', 'icon' => 'fa-calendar-check'],
                        'rekonsiliasi_closing' => ['label' => 'Rekonsiliasi Closing vs Kas/Bank', 'icon' => 'fa-code-compare'],
                    ]
                ],
            ]
        ],
        'general_journal' => [
            'id' => 'general_journal',
            'num' => 7,
            'title' => 'General Journal',
            'desc' => 'Entri Jurnal Umum, Audit Non-Departemen, & Watchdog Departemen',
            'icon' => 'fa-book',
            'gate_perms' => ['general_journal.access'],
            'submenus' => [
                'entries' => [
                    'id' => 'journal_entries',
                    'title' => 'Journal Entries & Audit',
                    'icon' => 'fa-pen-to-square',
                    'gate_perms' => [],
                    'modules' => [
                        'journal_entry' => ['label' => 'General Journal Entry', 'icon' => 'fa-book'],
                        'unassigned_watchdog' => ['label' => 'Unassigned Watchdog (Audit Non-Dept)', 'icon' => 'fa-shield-halved'],
                        'assigned_watchdog' => ['label' => 'Assigned Watchdog (Audit Departemen)', 'icon' => 'fa-shield-halved'],
                    ]
                ],
            ]
        ],
        'report' => [
            'id' => 'report',
            'num' => 8,
            'title' => 'Report',
            'desc' => 'Laporan Buku Besar, Neraca, Laba Rugi, & Arus Kas',
            'icon' => 'fa-chart-pie',
            'gate_perms' => ['report_menu.access'],
            'submenus' => [
                'financial' => [
                    'id' => 'report_financial',
                    'title' => 'Financial Reports',
                    'icon' => 'fa-file-invoice-dollar',
                    'gate_perms' => [],
                    'modules' => [
                        'buku_besar' => ['label' => 'Buku Besar', 'icon' => 'fa-book-bookmark'],
                        'trial_balance' => ['label' => 'Trial Balance', 'icon' => 'fa-scale-balanced'],
                        'neraca' => ['label' => 'Neraca', 'icon' => 'fa-balance-scale'],
                        'neraca_departemen' => ['label' => 'Balance Sheet Departemen', 'icon' => 'fa-balance-scale'],
                        'income_statement' => ['label' => 'Income Statement', 'icon' => 'fa-chart-pie'],
                        'income_statement_departement' => ['label' => 'Income Statement Department', 'icon' => 'fa-chart-pie'],
                        'arus_kas' => ['label' => 'Cash Flow', 'icon' => 'fa-money-bill-trend-up'],
                    ]
                ],
            ]
        ],
        'payroll' => [
            'id' => 'payroll',
            'num' => 9,
            'title' => 'Payroll',
            'desc' => 'SDM, Penggajian Staff & Non-Staff, Absensi, & Shift Kerja',
            'icon' => 'fa-users-gear',
            'gate_perms' => ['payroll.access'],
            'submenus' => [
                'setup' => [
                    'id' => 'payroll_setup',
                    'title' => 'Setup',
                    'icon' => 'fa-gear',
                    'gate_perms' => ['setup_payroll.access'],
                    'modules' => [
                        'level_karyawan' => ['label' => 'Employee Level', 'icon' => 'fa-stairs'],
                        'jabatan' => ['label' => 'Position', 'icon' => 'fa-id-badge'],
                        'komponen_penghasilan' => ['label' => 'Income Components By Level', 'icon' => 'fa-file-invoice'],
                        'group_unit' => ['label' => 'Group Unit', 'icon' => 'fa-object-group'],
                        'unit' => ['label' => 'Units/Departemens', 'icon' => 'fa-network-wired'],
                        'ptkp' => ['label' => 'PTKP', 'icon' => 'fa-percent'],
                        'employee' => ['label' => 'Employee Profiles', 'icon' => 'fa-users-gear'],
                        'komposisi_gaji' => ['label' => 'Income Components By Employee', 'icon' => 'fa-coins'],
                        'jenis_hari' => ['label' => 'Type Of Days', 'icon' => 'fa-calendar-day'],
                        'jam_kerja' => ['label' => 'Working Hours', 'icon' => 'fa-business-time'],
                        'target_unit' => ['label' => 'Sales Target By Unit', 'icon' => 'fa-crosshairs'],
                        'target_wahana' => ['label' => 'Sales Target By Wahana', 'icon' => 'fa-bullseye'],
                        'crew_shift_karyawan' => ['label' => 'Crew Scheduling', 'icon' => 'fa-user-group'],
                        'shift_karyawan' => ['label' => 'Personnel Scheduling', 'icon' => 'fa-calendar-days'],
                        'transaksi_wahana' => ['label' => 'Input Sales Achievement', 'icon' => 'fa-ticket-simple'],
                        'bonus_karyawan' => ['label' => 'Employee Bonus Update', 'icon' => 'fa-gift'],
                        'tax_rates' => ['label' => 'TER (Tax Rates)', 'icon' => 'fa-percent'],
                    ]
                ],
                'process' => [
                    'id' => 'payroll_process',
                    'title' => 'Process',
                    'icon' => 'fa-calculator',
                    'gate_perms' => ['process_payroll.access'],
                    'modules' => [
                        'pembayaran_gaji' => ['label' => 'Create Salary Calculations Staff', 'icon' => 'fa-money-check'],
                        'pembayaran_gaji_nonstaff' => ['label' => 'Create Salary Calculations Non Staff', 'icon' => 'fa-money-check'],
                        'slip_gaji' => ['label' => 'Salary Slip Staff', 'icon' => 'fa-ticket'],
                        'slip_gaji_nonstaff' => ['label' => 'Salary Slip Non Staff', 'icon' => 'fa-ticket'],
                        'absensi' => ['label' => 'Absensi Pegawai', 'icon' => 'fa-clipboard-user'],
                    ]
                ],
                'report' => [
                    'id' => 'payroll_report',
                    'title' => 'Report',
                    'icon' => 'fa-file-excel',
                    'gate_perms' => ['report_payroll.access'],
                    'modules' => [
                        'rekap_absensi' => ['label' => 'Rekap Absensi Pegawai', 'icon' => 'fa-list-check'],
                        'rekap_target_wahana' => ['label' => 'Rekap Target Wahana', 'icon' => 'fa-chart-column'],
                    ]
                ],
            ]
        ],
        'budgeting' => [
            'id' => 'budgeting',
            'num' => 10,
            'title' => 'Budgeting',
            'desc' => 'Penyusunan Anggaran, Approval, Pengajuan, & Realisasi',
            'icon' => 'fa-coins',
            'gate_perms' => ['budgeting.access'],
            'submenus' => [
                'data' => [
                    'id' => 'budgeting_data',
                    'title' => 'Data',
                    'icon' => 'fa-database',
                    'gate_perms' => [],
                    'modules' => [
                        'approval_step' => ['label' => 'Approval Step', 'icon' => 'fa-user-check'],
                        'rekening' => ['label' => 'Rekening Kas & Bank', 'icon' => 'fa-credit-card'],
                    ]
                ],
                'operations' => [
                    'id' => 'budgeting_ops',
                    'title' => 'Budget Operations',
                    'icon' => 'fa-paper-plane',
                    'gate_perms' => [],
                    'modules' => [
                        'create_budget' => ['label' => 'Create & Data Budget', 'icon' => 'fa-folder-plus'],
                        'budget_submission' => ['label' => 'Budget Submission', 'icon' => 'fa-paper-plane'],
                        'budget_disbursement' => ['label' => 'Budget Disbursement', 'icon' => 'fa-hand-holding-dollar'],
                        'budget_realization' => ['label' => 'Budget Realization', 'icon' => 'fa-chart-simple'],
                    ]
                ],
            ]
        ],
        'specpose' => [
            'id' => 'specpose',
            'num' => 11,
            'title' => 'Specpose',
            'desc' => 'Pos Khusus Transaksi / Proyek & Pelaporan Specpose',
            'icon' => 'fa-folder-tree',
            'gate_perms' => [],
            'submenus' => [
                'setup' => [
                    'id' => 'specpose_setup',
                    'title' => 'Setup & Data',
                    'icon' => 'fa-folder-open',
                    'gate_perms' => [],
                    'modules' => [
                        'specpose' => ['label' => 'Data & Form Specpose', 'icon' => 'fa-folder-tree'],
                    ]
                ],
                'reports' => [
                    'id' => 'specpose_reports',
                    'title' => 'Reports',
                    'icon' => 'fa-file-waveform',
                    'gate_perms' => [],
                    'modules' => [
                        'report_specpose' => ['label' => 'Reports Specpose', 'icon' => 'fa-file-waveform'],
                    ]
                ],
            ]
        ],
        'fisrec' => [
            'id' => 'fisrec',
            'num' => 12,
            'title' => 'Fisrec',
            'desc' => 'Rekonsiliasi Fiskal Pajak & Perhitungan PPh',
            'icon' => 'fa-file-contract',
            'gate_perms' => [],
            'submenus' => [
                'setup' => [
                    'id' => 'fisrec_setup',
                    'title' => 'Setup',
                    'icon' => 'fa-gear',
                    'gate_perms' => [],
                    'modules' => [
                        'fiscal_account' => ['label' => 'Fiscal Account', 'icon' => 'fa-scale-balanced'],
                        'fiscal_account_persamaan' => ['label' => 'Fiscal Account Persamaan', 'icon' => 'fa-scale-balanced'],
                    ]
                ],
                'operations' => [
                    'id' => 'fisrec_ops',
                    'title' => 'Fiscal Operations',
                    'icon' => 'fa-calculator',
                    'gate_perms' => [],
                    'modules' => [
                        'fiscal' => ['label' => 'Fiscal Settings & Reconciliation', 'icon' => 'fa-file-contract'],
                        'perhitungan_pajak_penghasilan' => ['label' => 'Perhitungan Pajak Penghasilan', 'icon' => 'fa-calculator'],
                    ]
                ],
            ]
        ],
        'documents' => [
            'id' => 'documents',
            'num' => 13,
            'title' => 'Documents',
            'desc' => 'Arsip Dokumen Penjualan, Pembelian, & Pajak',
            'icon' => 'fa-folder-closed',
            'gate_perms' => ['documents.access'],
            'submenus' => [
                'sales' => [
                    'id' => 'documents_sales',
                    'title' => 'Sales',
                    'icon' => 'fa-file',
                    'gate_perms' => ['sales_document.access'],
                    'modules' => [
                        'sales_document' => ['label' => 'Sales Order & Invoice Document', 'icon' => 'fa-file'],
                    ]
                ],
                'purchases' => [
                    'id' => 'documents_purchases',
                    'title' => 'Purchases',
                    'icon' => 'fa-file',
                    'gate_perms' => ['purchases_document.access'],
                    'modules' => [
                        'purchases_document' => ['label' => 'Purchase Order & Invoice Document', 'icon' => 'fa-file'],
                    ]
                ],
                'taxes' => [
                    'id' => 'documents_taxes',
                    'title' => 'Taxes',
                    'icon' => 'fa-receipt',
                    'gate_perms' => [],
                    'modules' => [
                        'taxes' => ['label' => 'Dokumen Pajak (Taxes)', 'icon' => 'fa-receipt'],
                    ]
                ],
            ]
        ],
        'maintenance' => [
            'id' => 'maintenance',
            'num' => 14,
            'title' => 'Maintenance',
            'desc' => 'Pemeliharaan Sistem, Tutup/Buka Tahun Baru, & Log Aktivitas',
            'icon' => 'fa-wrench',
            'gate_perms' => ['maintenance.access'],
            'submenus' => [
                'system' => [
                    'id' => 'maintenance_system',
                    'title' => 'System Maintenance',
                    'icon' => 'fa-server',
                    'gate_perms' => [],
                    'modules' => [
                        'start_new_year' => ['label' => 'Start New Year', 'icon' => 'fa-calendar-plus'],
                        'log_activity' => ['label' => 'Log Activity', 'icon' => 'fa-clock-rotate-left'],
                    ]
                ],
            ]
        ],
    ];

    // Indeks semua permissions yang ada di database
    $allPermNames = $permissions->pluck('name')->toArray();
    $totalPermissionsCount = count($allPermNames);

    // Pemetaan prefix permission ke daftar actions
    $permsByPrefix = [];
    foreach ($permissions as $p) {
        $parts = explode('.', $p->name);
        $pref = $parts[0];
        $act = $parts[1] ?? 'access';
        $permsByPrefix[$pref][$act] = $p->name;
    }

    // Bangun tree kompilasi dan peta sinkronisasi parent gate
    $parentGatesByPerm = [];
    $compiledTree = [];
    $allMenuKeys = array_keys($navHierarchy);

    foreach ($navHierarchy as $menuKey => $menu) {
        $menuPerms = [];
        $menuGatePerms = array_values(array_filter($menu['gate_perms'], function($gp) use ($allPermNames) {
            return in_array($gp, $allPermNames);
        }));
        foreach ($menuGatePerms as $gp) {
            $menuPerms[] = $gp;
        }

        $submenus = [];
        foreach ($menu['submenus'] as $subKey => $sub) {
            $subPerms = [];
            $subGatePerms = array_values(array_filter($sub['gate_perms'], function($gp) use ($allPermNames) {
                return in_array($gp, $allPermNames);
            }));
            foreach ($subGatePerms as $gp) {
                $subPerms[] = $gp;
                $menuPerms[] = $gp;
                foreach ($menuGatePerms as $mgp) {
                    $parentGatesByPerm[$gp][] = $mgp;
                }
            }

            $modules = [];
            foreach ($sub['modules'] as $modKey => $modCfg) {
                $modActions = [];
                $modExtras = [];
                $modAllPerms = [];

                if (isset($permsByPrefix[$modKey])) {
                    foreach ($permsByPrefix[$modKey] as $act => $permFullName) {
                        $modAllPerms[] = $permFullName;
                        $subPerms[] = $permFullName;
                        $menuPerms[] = $permFullName;

                        if (in_array($act, ['access', 'view', 'create', 'update', 'delete'])) {
                            $modActions[$act] = $permFullName;
                        } else {
                            $modExtras[$act] = $permFullName;
                        }

                        // Hubungkan permission modul ke parent gates (menu gates + submenu gates)
                        foreach (array_merge($menuGatePerms, $subGatePerms) as $pg) {
                            if (!isset($parentGatesByPerm[$permFullName])) {
                                $parentGatesByPerm[$permFullName] = [];
                            }
                            if (!in_array($pg, $parentGatesByPerm[$permFullName])) {
                                $parentGatesByPerm[$permFullName][] = $pg;
                            }
                        }
                    }
                }

                $modules[$modKey] = [
                    'key' => $modKey,
                    'label' => $modCfg['label'],
                    'icon' => $modCfg['icon'],
                    'breadcrumb' => $menu['title'] . ' › ' . $sub['title'] . ' › ' . $modCfg['label'],
                    'actions' => $modActions,
                    'extras' => $modExtras,
                    'all_perms' => array_values(array_unique($modAllPerms)),
                ];
            }

            $submenus[$subKey] = [
                'id' => $sub['id'],
                'title' => $sub['title'],
                'icon' => $sub['icon'],
                'gate_perms' => $subGatePerms,
                'modules' => $modules,
                'all_perms' => array_values(array_unique($subPerms)),
            ];
        }

        $compiledTree[$menuKey] = [
            'id' => $menu['id'],
            'num' => $menu['num'],
            'title' => $menu['title'],
            'desc' => $menu['desc'],
            'icon' => $menu['icon'],
            'gate_perms' => $menuGatePerms,
            'submenus' => $submenus,
            'all_perms' => array_values(array_unique($menuPerms)),
        ];
    }

    // Siapkan data roles untuk Alpine.js
    $rolesPayload = $roles->map(function($r) {
        return [
            'id' => $r->id,
            'name' => $r->name,
            'guard_name' => $r->guard_name,
            'permissions' => $r->permissions->pluck('name')->toArray(),
            'users_count' => $r->users->count(),
        ];
    });

    // Buat lookup search keywords per menu & submenu untuk performa instan
    $menuPermsMap = [];
    foreach ($compiledTree as $mKey => $mVal) {
        $menuPermsMap[$mKey] = $mVal['all_perms'];
    }
@endphp

<div x-data="roleManager({{ $rolesPayload->toJson() }}, {{ json_encode($allPermNames) }}, {{ json_encode($parentGatesByPerm) }}, {{ json_encode($menuPermsMap) }})" class="space-y-6">

    <!-- Card Container Daftar Role -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Top Accent Bar -->
        <div class="h-1.5 w-full" style="background: linear-gradient(90deg, {{ $themeColor }} 0%, #8b5cf6 100%);"></div>

        <!-- Role Header Card -->
        <div class="p-5 md:p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white shadow-xs font-bold text-xs" style="background-color: {{ $themeColor }}">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">Daftar Role & Hak Akses</h2>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Kelola peran akun dan izin modul terstruktur 14 Menu Navbar RCA. Total <strong>{{ $totalPermissionsCount }}</strong> izin tersedia.
                </p>
            </div>

            <button type="button" @click="openCreate()"
                style="background-color: {{ $themeColor }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm hover:opacity-95 active:scale-95 transition-all">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Role Baru</span>
            </button>
        </div>

        <!-- Tabel Role Pengguna -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50/80 text-xs uppercase font-semibold text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4">Peran (Role)</th>
                        <th class="px-6 py-4">Jenis Guard</th>
                        <th class="px-6 py-4">Cakupan Izin Akses</th>
                        <th class="px-6 py-4">Pengguna Terkait</th>
                        <th class="px-6 py-4 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="role in rolesList" :key="role.id">
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <!-- Role Name -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs"
                                        :class="role.name.toLowerCase() === 'admin' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700'">
                                        <i class="fa-solid" :class="role.name.toLowerCase() === 'admin' ? 'fa-crown' : 'fa-user-tag'"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-sm" x-text="role.name"></div>
                                        <span class="text-[11px] text-gray-400 font-mono" x-text="'ID #' + role.id"></span>
                                    </div>
                                </div>
                            </td>

                            <!-- Guard Name -->
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200" x-text="role.guard_name || 'web'"></span>
                            </td>

                            <!-- Permission Coverage Badge -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                        :class="role.permissions.length === {{ $totalPermissionsCount }} ? 'bg-emerald-100 text-emerald-800' : (role.permissions.length > 0 ? 'bg-pink-100 text-pink-800' : 'bg-gray-100 text-gray-500')"
                                        x-text="role.permissions.length + ' / {{ $totalPermissionsCount }} Izin'">
                                    </span>
                                    <span class="text-xs text-gray-400" x-text="role.permissions.length === {{ $totalPermissionsCount }} ? '(Akses Penuh)' : (role.permissions.length === 0 ? '(Belum Ada Izin)' : '')"></span>
                                </div>
                            </td>

                            <!-- Users count -->
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 text-xs text-gray-700 bg-gray-100 px-2.5 py-1 rounded-full font-medium">
                                    <i class="fa-solid fa-users text-gray-400"></i>
                                    <span x-text="role.users_count + ' User'"></span>
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right space-x-1">
                                <button type="button" @click="openEdit(role)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-100 text-xs font-semibold transition-all">
                                    <i class="fa-solid fa-pen-to-square text-gray-500"></i>
                                    <span>Edit Izin</span>
                                </button>
                                <button type="button" @click="confirmDeleteRole(role)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition-all">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Hapus</span>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- SLIDE-OVER DRAWER (OFFCANVAS KANAN DENGAN ACCORDION 14 MENU NAVBAR)  -->
    <!-- ===================================================================== -->
    <div x-show="drawerOpen" x-cloak class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <!-- Backdrop Blur Overlay -->
        <div x-show="drawerOpen"
            x-transition:enter="ease-in-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in-out duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="closeDrawer()"
            class="absolute inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-4 sm:pl-10">
            <div x-show="drawerOpen"
                x-transition:enter="transform transition ease-in-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-300"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="w-screen max-w-5xl bg-white shadow-2xl flex flex-col border-l border-gray-200">

                <!-- Form Role Wrapper -->
                <form :action="formAction" method="POST" @submit="prepareSubmit($event)" class="flex flex-col h-full m-0 p-0">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Dynamic Hidden Inputs Container for All Selected Permissions -->
                    <div id="submitted-permissions-container" class="hidden"></div>

                    <!-- Drawer Header -->
                    <div class="p-5 md:p-6 border-b border-gray-200 bg-gray-50/90 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-xs" style="background-color: {{ $themeColor }}">
                                <i class="fa-solid" :class="isEdit ? 'fa-pen-to-square' : 'fa-plus'"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 tracking-tight" x-text="isEdit ? 'Edit Role & Hirarki Hak Akses' : 'Tambah Role Baru'"></h3>
                                <p class="text-xs text-gray-500">Konfigurasi hak akses diurutkan sesuai dengan struktur visual Menu Utama & Sub-Menu RCA.</p>
                            </div>
                        </div>

                        <button type="button" @click="closeDrawer()"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-200/60 font-bold transition-colors">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <!-- Drawer Body (Scrollable) -->
                    <div class="flex-1 overflow-y-auto p-5 md:p-6 space-y-5 bg-slate-50/50">

                        <!-- Role Attributes Form Inputs -->
                        <div class="bg-white p-4.5 rounded-2xl border border-gray-200 shadow-xs grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                                    Nama Role <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="name" x-model="roleName" required
                                    placeholder="Contoh: Staff Keuangan, Kasir Wahana"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 bg-white"
                                    style="focus:border-color: {{ $themeColor }}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                                    Guard Name
                                </label>
                                <input type="text" name="guard_name" x-model="roleGuard" readonly
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 bg-gray-100 text-gray-500 text-sm cursor-not-allowed">
                            </div>
                        </div>

                        <!-- Sticky Toolbar Kontrol Permissions -->
                        <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs space-y-3 sticky top-0 z-20">
                            
                            <!-- Presets, Accordion Toggle & Live Counter -->
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <!-- Presets -->
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mr-1">Preset:</span>
                                    <button type="button" @click="applyPreset('all')"
                                        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs active:scale-95 transition-all">
                                        <i class="fa-solid fa-check-double mr-1"></i> Full Akses
                                    </button>
                                    <button type="button" @click="applyPreset('view-only')"
                                        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-xs active:scale-95 transition-all">
                                        <i class="fa-solid fa-eye mr-1"></i> View Only
                                    </button>
                                    <button type="button" @click="applyPreset('none')"
                                        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 active:scale-95 transition-all">
                                        <i class="fa-solid fa-rotate-left mr-1"></i> Kosongkan
                                    </button>
                                </div>

                                <!-- Accordion Controls & Counter -->
                                <div class="flex items-center gap-2 flex-wrap">
                                    <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-0.5">
                                        <button type="button" @click="expandAll()"
                                            class="px-2.5 py-1 text-xs font-semibold rounded-md text-gray-600 hover:bg-white hover:shadow-xs transition-all">
                                            <i class="fa-solid fa-angles-down text-[10px] mr-1"></i> Buka Semua
                                        </button>
                                        <button type="button" @click="collapseAll()"
                                            class="px-2.5 py-1 text-xs font-semibold rounded-md text-gray-600 hover:bg-white hover:shadow-xs transition-all">
                                            <i class="fa-solid fa-angles-up text-[10px] mr-1"></i> Tutup Semua
                                        </button>
                                    </div>

                                    <!-- Live Selected Counter Badge -->
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border"
                                        style="background-color: #fdf2f8; color: {{ $themeColor }}; border-color: #fbcfe8;">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span x-text="countSelected() + ' / {{ $totalPermissionsCount }} Izin Terpilih'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Search Input & Menu Filter Pills -->
                            <div class="space-y-2 pt-2 border-t border-gray-100">
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-gray-400 text-xs"></i>
                                    <input type="text" x-model="searchQuery"
                                        placeholder="Cari modul (contoh: asset, sales, piutang, gaji, voucher, coa)..."
                                        class="w-full pl-9 pr-4 py-2 rounded-xl border border-gray-300 text-xs focus:outline-none focus:ring-2 bg-white"
                                        style="focus:border-color: {{ $themeColor }}">
                                    <button type="button" x-show="searchQuery" @click="searchQuery = ''"
                                        class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 text-xs">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>

                                <!-- Filter Pills 14 Menu Utama -->
                                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar">
                                    <button type="button" @click="activeMenuFilter = 'all'"
                                        :class="activeMenuFilter === 'all' ? 'bg-gray-900 text-white font-bold shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 font-medium'"
                                        class="px-3 py-1 rounded-lg whitespace-nowrap transition-all">
                                        Semua Menu (14)
                                    </button>
                                    @foreach ($compiledTree as $mKey => $m)
                                        <button type="button" @click="setMenuFilter('{{ $mKey }}')"
                                            :class="activeMenuFilter === '{{ $mKey }}' ? 'bg-gray-900 text-white font-bold shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 font-medium'"
                                            class="px-2.5 py-1 rounded-lg whitespace-nowrap transition-all flex items-center gap-1.5">
                                            <span class="w-4 h-4 rounded text-[10px] flex items-center justify-center font-bold"
                                                :class="activeMenuFilter === '{{ $mKey }}' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-600'">
                                                {{ $m['num'] }}
                                            </span>
                                            <span>{{ $m['title'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                        </div>

                        <!-- ============================================================= -->
                        <!-- ACCORDION LIST: 14 MENU UTAMA DENGAN SUB-MENU & MATRIKS CRUD  -->
                        <!-- ============================================================= -->
                        <div class="space-y-3.5">
                            @foreach ($compiledTree as $menuKey => $menu)
                                <div x-show="isMenuVisible('{{ $menuKey }}')"
                                    class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden transition-all duration-200">

                                    <!-- --------------------------------------------------------- -->
                                    <!-- LEVEL 1: ACCORDION HEADER MENU UTAMA                      -->
                                    <!-- --------------------------------------------------------- -->
                                    <div @click="toggleMenu('{{ $menuKey }}')"
                                        class="p-4 md:p-4.5 bg-white hover:bg-gray-50/80 cursor-pointer flex items-center justify-between gap-3 select-none transition-colors">
                                        
                                        <!-- Left Info -->
                                        <div class="flex items-center gap-3 min-w-0">
                                            <!-- Nomor Urut Badge -->
                                            <span class="w-6 h-6 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center font-bold text-xs shrink-0 font-mono">
                                                {{ sprintf('%02d', $menu['num']) }}
                                            </span>

                                            <!-- Menu Icon -->
                                            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs shrink-0 shadow-2xs"
                                                style="background-color: {{ $themeColor }}">
                                                <i class="fa-solid {{ $menu['icon'] }}"></i>
                                            </div>

                                            <!-- Menu Title & Description -->
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="font-bold text-gray-900 text-sm tracking-tight truncate">{{ $menu['title'] }}</h4>
                                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium hidden sm:inline">
                                                        {{ count($menu['submenus']) }} Sub-Menu
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-gray-400 truncate mt-0.5">{{ $menu['desc'] }}</p>
                                            </div>
                                        </div>

                                        <!-- Right Controls -->
                                        <div class="flex items-center gap-2.5 shrink-0" @click.stop>
                                            <!-- Counter Badge -->
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold transition-colors"
                                                :class="countSelectedInList({{ json_encode($menu['all_perms']) }}) > 0 ? 'bg-pink-100 text-pink-700 font-bold' : 'bg-gray-100 text-gray-500'"
                                                x-text="countSelectedInList({{ json_encode($menu['all_perms']) }}) + ' / {{ count($menu['all_perms']) }} Izin'">
                                            </span>

                                            <!-- Tombol Cepat: Pilih Seluruh Menu Ini -->
                                            <button type="button" @click="togglePermList({{ json_encode($menu['all_perms']) }})"
                                                title="Pilih atau kosongkan seluruh izin di Menu ini"
                                                class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 active:scale-95 transition-all">
                                                <i class="fa-solid fa-list-check mr-1 text-gray-500"></i>
                                                <span class="hidden sm:inline">Pilih Menu</span>
                                            </button>

                                            <!-- Chevron Rotate Icon -->
                                            <button type="button" @click="toggleMenu('{{ $menuKey }}')"
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-transform duration-200"
                                                :class="{ 'rotate-180': openMenus['{{ $menuKey }}'] }">
                                                <i class="fa-solid fa-chevron-down text-xs"></i>
                                            </button>
                                        </div>

                                    </div>

                                    <!-- --------------------------------------------------------- -->
                                    <!-- LEVEL 2 & 3: ACCORDION BODY (SUB-MENUS & MATRIKS MODUL)   -->
                                    <!-- --------------------------------------------------------- -->
                                    <div x-show="openMenus['{{ $menuKey }}']" x-collapse class="border-t border-gray-100 bg-white">
                                        
                                        @foreach ($menu['submenus'] as $subKey => $sub)
                                            <div x-show="isSubmenuVisible('{{ $menuKey }}', '{{ $subKey }}')"
                                                class="border-b last:border-b-0 border-gray-100">

                                                <!-- Sub-Menu Header Banner (Level 2) -->
                                                <div class="bg-slate-50/90 px-4 py-2.5 flex items-center justify-between gap-3 border-y border-gray-200/80 select-none">
                                                    
                                                    <!-- Left Title & Tree Connector -->
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-pink-400 font-mono font-bold text-xs">↳</span>
                                                        <div class="w-5 h-5 rounded-md bg-white border border-gray-200 text-gray-600 flex items-center justify-center text-[10px]">
                                                            <i class="fa-solid {{ $sub['icon'] }}"></i>
                                                        </div>
                                                        <span class="font-bold text-xs uppercase tracking-wider text-gray-800">
                                                            {{ $sub['title'] }}
                                                        </span>
                                                        <span class="text-[10px] text-gray-400">
                                                            ({{ count($sub['modules']) }} Modul)
                                                        </span>
                                                    </div>

                                                    <!-- Right Action: Pilih Seluruh Sub-Menu Ini -->
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[11px] text-gray-500 font-mono hidden sm:inline"
                                                            x-text="countSelectedInList({{ json_encode($sub['all_perms']) }}) + ' / {{ count($sub['all_perms']) }} dipilih'">
                                                        </span>
                                                        
                                                        <button type="button" @click="togglePermList({{ json_encode($sub['all_perms']) }})"
                                                            class="px-2.5 py-0.8 text-[11px] font-semibold rounded-md bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 shadow-2xs active:scale-95 transition-all">
                                                            <i class="fa-solid fa-check mr-1 text-gray-400"></i>
                                                            <span>Pilih Sub-Menu</span>
                                                        </button>
                                                    </div>

                                                </div>

                                                <!-- Tabel Matriks Modul CRUD (Level 3) -->
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-xs text-left">
                                                        <thead class="bg-gray-50/50 text-gray-500 font-semibold uppercase tracking-wider text-[10px] border-b border-gray-100 select-none">
                                                            <tr>
                                                                <th class="py-2.5 px-4 w-5/12">Modul / Sub-sub Menu</th>
                                                                <th class="py-2.5 px-2 text-center w-20">Access</th>
                                                                <th class="py-2.5 px-2 text-center w-20">View</th>
                                                                <th class="py-2.5 px-2 text-center w-20">Create</th>
                                                                <th class="py-2.5 px-2 text-center w-20">Update</th>
                                                                <th class="py-2.5 px-2 text-center w-20">Delete</th>
                                                                <th class="py-2.5 px-3 text-center w-24">Master (Yes/No)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100/80">
                                                            @foreach ($sub['modules'] as $modKey => $mod)
                                                                @php
                                                                    $isSingle = count($mod['all_perms']) === 1;
                                                                    $singlePerm = $isSingle ? $mod['all_perms'][0] : null;
                                                                @endphp
                                                                <tr x-show="isModuleVisible('{{ $modKey }}', '{{ strtolower($mod['label']) }}')"
                                                                    class="hover:bg-pink-50/20 transition-colors">
                                                                    
                                                                    <!-- Modul Label & Breadcrumb -->
                                                                    <td class="py-3 px-4">
                                                                        <div class="flex items-start gap-2.5">
                                                                            <!-- Tree Branch Indicator -->
                                                                            <span class="text-gray-300 font-mono text-xs mt-1">├──</span>
                                                                            
                                                                            <div class="w-6 h-6 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center text-[10px] shrink-0 mt-0.5">
                                                                                <i class="fa-solid {{ $mod['icon'] }}"></i>
                                                                            </div>

                                                                            <div class="min-w-0">
                                                                                <div class="font-bold text-gray-800 text-xs">{{ $mod['label'] }}</div>
                                                                                
                                                                                <!-- Breadcrumb Pill -->
                                                                                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                                                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-gray-100 text-gray-500 font-medium">
                                                                                        {{ $mod['breadcrumb'] }}
                                                                                    </span>
                                                                                    <span class="text-[9px] font-mono text-gray-400">
                                                                                        {{ $modKey }}.*
                                                                                    </span>
                                                                                </div>

                                                                                <!-- Extra Actions jika ada (data, override_closed, dll.) -->
                                                                                @if (!empty($mod['extras']))
                                                                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                                                                        @foreach ($mod['extras'] as $extraAct => $permFull)
                                                                                            <label class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 text-[10px] cursor-pointer transition-colors">
                                                                                                <input type="checkbox"
                                                                                                    :checked="isPermSelected('{{ $permFull }}')"
                                                                                                    @change="togglePerm('{{ $permFull }}')"
                                                                                                    class="rounded border-gray-300 text-purple-600 focus:ring-purple-400 h-3 w-3">
                                                                                                <span>{{ $extraAct }}</span>
                                                                                            </label>
                                                                                        @endforeach
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </td>

                                                                    @if ($isSingle)
                                                                        <!-- Kolom Opsi YES / NO Tunggal (Bebas dari strip —) -->
                                                                        <td colspan="6" class="py-3 px-4 text-left">
                                                                            <div class="flex items-center gap-3">
                                                                                <!-- Modern iOS Toggle Switch -->
                                                                                <button type="button" 
                                                                                    @click="togglePerm('{{ $singlePerm }}')"
                                                                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                                                                    :class="isPermSelected('{{ $singlePerm }}') ? 'bg-emerald-500 shadow-xs' : 'bg-gray-200'">
                                                                                    <span class="sr-only">Toggle Akses</span>
                                                                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                                                                        :class="isPermSelected('{{ $singlePerm }}') ? 'translate-x-5' : 'translate-x-0'"></span>
                                                                                </button>
                                                                                
                                                                                <!-- Label Status YES / NO -->
                                                                                <div class="flex items-center gap-1.5 font-bold text-xs select-none">
                                                                                    <span x-show="isPermSelected('{{ $singlePerm }}')" class="text-emerald-600 flex items-center gap-1">
                                                                                        <i class="fa-solid fa-circle-check"></i> YES <span class="font-normal text-[11px] text-gray-500">(Aktif)</span>
                                                                                    </span>
                                                                                    <span x-show="!isPermSelected('{{ $singlePerm }}')" class="text-gray-400 flex items-center gap-1">
                                                                                        <i class="fa-solid fa-circle-xmark"></i> NO <span class="font-normal text-[11px] text-gray-400">(Nonaktif)</span>
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                    @else
                                                                        <!-- Checkbox: Access -->
                                                                        <td class="py-3 px-2 text-center">
                                                                            @if (isset($mod['actions']['access']))
                                                                                @php $pName = $mod['actions']['access']; @endphp
                                                                                <input type="checkbox"
                                                                                    :checked="isPermSelected('{{ $pName }}')"
                                                                                    @change="togglePerm('{{ $pName }}')"
                                                                                    class="w-4 h-4 rounded border-gray-300 text-pink-600 focus:ring-pink-400 cursor-pointer">
                                                                            @else
                                                                                <span class="text-gray-300 font-bold">—</span>
                                                                            @endif
                                                                        </td>

                                                                        <!-- Checkbox: View -->
                                                                        <td class="py-3 px-2 text-center">
                                                                            @if (isset($mod['actions']['view']))
                                                                                @php $pName = $mod['actions']['view']; @endphp
                                                                                <input type="checkbox"
                                                                                    :checked="isPermSelected('{{ $pName }}')"
                                                                                    @change="togglePerm('{{ $pName }}')"
                                                                                    class="w-4 h-4 rounded border-gray-300 text-pink-600 focus:ring-pink-400 cursor-pointer">
                                                                            @else
                                                                                <span class="text-gray-300 font-bold">—</span>
                                                                            @endif
                                                                        </td>

                                                                        <!-- Checkbox: Create -->
                                                                        <td class="py-3 px-2 text-center">
                                                                            @if (isset($mod['actions']['create']))
                                                                                @php $pName = $mod['actions']['create']; @endphp
                                                                                <input type="checkbox"
                                                                                    :checked="isPermSelected('{{ $pName }}')"
                                                                                    @change="togglePerm('{{ $pName }}')"
                                                                                    class="w-4 h-4 rounded border-gray-300 text-pink-600 focus:ring-pink-400 cursor-pointer">
                                                                            @else
                                                                                <span class="text-gray-300 font-bold">—</span>
                                                                            @endif
                                                                        </td>

                                                                        <!-- Checkbox: Update -->
                                                                        <td class="py-3 px-2 text-center">
                                                                            @if (isset($mod['actions']['update']))
                                                                                @php $pName = $mod['actions']['update']; @endphp
                                                                                <input type="checkbox"
                                                                                    :checked="isPermSelected('{{ $pName }}')"
                                                                                    @change="togglePerm('{{ $pName }}')"
                                                                                    class="w-4 h-4 rounded border-gray-300 text-pink-600 focus:ring-pink-400 cursor-pointer">
                                                                            @else
                                                                                <span class="text-gray-300 font-bold">—</span>
                                                                            @endif
                                                                        </td>

                                                                        <!-- Checkbox: Delete -->
                                                                        <td class="py-3 px-2 text-center">
                                                                            @if (isset($mod['actions']['delete']))
                                                                                @php $pName = $mod['actions']['delete']; @endphp
                                                                                <input type="checkbox"
                                                                                    :checked="isPermSelected('{{ $pName }}')"
                                                                                    @change="togglePerm('{{ $pName }}')"
                                                                                    class="w-4 h-4 rounded border-gray-300 text-pink-600 focus:ring-pink-400 cursor-pointer">
                                                                            @else
                                                                                <span class="text-gray-300 font-bold">—</span>
                                                                            @endif
                                                                        </td>

                                                                        <!-- Master Switch YES / NO untuk Modul CRUD -->
                                                                        <td class="py-3 px-3 text-center">
                                                                            <div class="flex flex-col items-center justify-center gap-1 select-none">
                                                                                <button type="button" @click="togglePermList({{ json_encode($mod['all_perms']) }})"
                                                                                    title="Aktifkan atau nonaktifkan seluruh izin modul ini"
                                                                                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                                                                    :class="countSelectedInList({{ json_encode($mod['all_perms']) }}) === {{ count($mod['all_perms']) }} ? 'bg-emerald-500 shadow-xs' : (countSelectedInList({{ json_encode($mod['all_perms']) }}) > 0 ? 'bg-pink-500 shadow-xs' : 'bg-gray-200')">
                                                                                    <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out"
                                                                                        :class="countSelectedInList({{ json_encode($mod['all_perms']) }}) > 0 ? 'translate-x-4' : 'translate-x-0'"></span>
                                                                                </button>
                                                                                <span class="text-[9px] font-bold"
                                                                                    :class="countSelectedInList({{ json_encode($mod['all_perms']) }}) === {{ count($mod['all_perms']) }} ? 'text-emerald-600' : (countSelectedInList({{ json_encode($mod['all_perms']) }}) > 0 ? 'text-pink-600' : 'text-gray-400')"
                                                                                    x-text="countSelectedInList({{ json_encode($mod['all_perms']) }}) === {{ count($mod['all_perms']) }} ? 'YES' : (countSelectedInList({{ json_encode($mod['all_perms']) }}) > 0 ? 'KUSTOM' : 'NO')">
                                                                                </span>
                                                                            </div>
                                                                        </td>
                                                                    @endif

                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                            </div>
                                        @endforeach

                                    </div>

                                </div>
                            @endforeach
                        </div>

                    </div>

                    <!-- Drawer Sticky Footer -->
                    <div class="p-5 border-t border-gray-200 bg-gray-50/90 flex items-center justify-between shrink-0">
                        <button type="button" @click="closeDrawer()"
                            class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-100 transition-all">
                            Batal
                        </button>

                        <button type="submit"
                            style="background-color: {{ $themeColor }}"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm hover:opacity-95 active:scale-95 transition-all">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span x-text="isEdit ? 'Perbarui Role & Hak Akses' : 'Simpan Role Baru'"></span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- Hidden form for deleting roles -->
    <form id="delete-role-form" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

</div>

<script>
function roleManager(initialRoles, allPermissions, parentGatesMap, menuPermsMap) {
    return {
        rolesList: initialRoles || [],
        allPerms: allPermissions || [],
        parentGates: parentGatesMap || {},
        menuPerms: menuPermsMap || {},
        drawerOpen: false,
        isEdit: false,
        roleId: null,
        roleName: '',
        roleGuard: 'web',
        formAction: '{{ route('roles.store') }}',
        activeMenuFilter: 'all',
        searchQuery: '',
        selectedPerms: {},
        openMenus: {},

        init() {
            // Inisialisasi status open accordion untuk 14 menu
            Object.keys(this.menuPerms).forEach(mKey => {
                this.openMenus[mKey] = false;
            });

            // Reaktifitas pencarian otomatis buka menu yang memiliki kecocokan
            this.$watch('searchQuery', (val) => {
                if (val && val.trim().length > 0) {
                    this.expandMatchingMenus(val.toLowerCase().trim());
                }
            });
        },

        openCreate() {
            this.isEdit = false;
            this.roleId = null;
            this.roleName = '';
            this.roleGuard = 'web';
            this.formAction = '{{ route('roles.store') }}';
            this.selectedPerms = {};
            this.searchQuery = '';
            this.activeMenuFilter = 'all';

            // Smart Expand untuk Role Baru: Buka Menu Setup (nomor 1)
            Object.keys(this.menuPerms).forEach(mKey => {
                this.openMenus[mKey] = (mKey === 'setup');
            });

            this.drawerOpen = true;
        },

        openEdit(role) {
            this.isEdit = true;
            this.roleId = role.id;
            this.roleName = role.name;
            this.roleGuard = role.guard_name || 'web';
            this.formAction = '/roles/' + role.id;
            
            // Masukkan permissions role terpilih ke map
            const map = {};
            if (Array.isArray(role.permissions)) {
                role.permissions.forEach(p => {
                    map[p] = true;
                });
            }
            this.selectedPerms = map;
            this.searchQuery = '';
            this.activeMenuFilter = 'all';

            // Smart Expand: Buka hanya menu yang memiliki izin terpilih
            let hasAnyOpen = false;
            Object.keys(this.menuPerms).forEach(mKey => {
                const perms = this.menuPerms[mKey] || [];
                const hasSelected = perms.some(p => !!this.selectedPerms[p]);
                this.openMenus[mKey] = hasSelected;
                if (hasSelected) hasAnyOpen = true;
            });

            // Jika role belum memiliki izin apa pun, buka menu pertama
            if (!hasAnyOpen) {
                this.openMenus['setup'] = true;
            }

            this.drawerOpen = true;
        },

        closeDrawer() {
            this.drawerOpen = false;
        },

        toggleMenu(menuKey) {
            this.openMenus[menuKey] = !this.openMenus[menuKey];
        },

        expandAll() {
            Object.keys(this.menuPerms).forEach(mKey => {
                this.openMenus[mKey] = true;
            });
        },

        collapseAll() {
            Object.keys(this.menuPerms).forEach(mKey => {
                this.openMenus[mKey] = false;
            });
        },

        setMenuFilter(menuKey) {
            this.activeMenuFilter = menuKey;
            if (menuKey !== 'all') {
                this.openMenus[menuKey] = true;
            }
        },

        isPermSelected(name) {
            return !!this.selectedPerms[name];
        },

        togglePerm(name) {
            if (this.selectedPerms[name]) {
                delete this.selectedPerms[name];
            } else {
                this.selectedPerms[name] = true;
                // Auto Parent Sync: Aktifkan parent gate izin agar menu navbar otomatis tampil
                this.syncParentGates(name);
            }
        },

        syncParentGates(name) {
            const gates = this.parentGates[name];
            if (Array.isArray(gates)) {
                gates.forEach(g => {
                    this.selectedPerms[g] = true;
                });
            }
        },

        togglePermList(perms) {
            if (!Array.isArray(perms) || perms.length === 0) return;
            const allSelected = perms.every(p => !!this.selectedPerms[p]);
            const newMap = Object.assign({}, this.selectedPerms);

            if (allSelected) {
                // Uncheck semua izin dalam list
                perms.forEach(p => {
                    delete newMap[p];
                });
            } else {
                // Check semua izin dalam list
                perms.forEach(p => {
                    newMap[p] = true;
                    // Auto-sync parent gates untuk setiap item
                    const gates = this.parentGates[p];
                    if (Array.isArray(gates)) {
                        gates.forEach(g => {
                            newMap[g] = true;
                        });
                    }
                });
            }

            this.selectedPerms = newMap;
        },

        countSelected() {
            return Object.keys(this.selectedPerms).filter(k => this.selectedPerms[k]).length;
        },

        countSelectedInList(perms) {
            if (!Array.isArray(perms) || perms.length === 0) return 0;
            return perms.filter(p => !!this.selectedPerms[p]).length;
        },

        applyPreset(mode) {
            if (mode === 'all') {
                const map = {};
                this.allPerms.forEach(p => { map[p] = true; });
                this.selectedPerms = map;
                this.expandAll();
            } else if (mode === 'view-only') {
                const map = {};
                this.allPerms.forEach(p => {
                    if (p.endsWith('.access') || p.endsWith('.view')) {
                        map[p] = true;
                    }
                });
                this.selectedPerms = map;
            } else if (mode === 'none') {
                this.selectedPerms = {};
            }
        },

        expandMatchingMenus(query) {
            // Ketika user mencari, buka semua menu yang cocok
            Object.keys(this.menuPerms).forEach(mKey => {
                if (mKey.toLowerCase().includes(query)) {
                    this.openMenus[mKey] = true;
                    return;
                }
                const perms = this.menuPerms[mKey] || [];
                const hasMatch = perms.some(p => p.toLowerCase().includes(query));
                if (hasMatch) {
                    this.openMenus[mKey] = true;
                }
            });
        },

        isMenuVisible(menuKey) {
            // Filter pill
            if (this.activeMenuFilter !== 'all' && this.activeMenuFilter !== menuKey) {
                return false;
            }
            // Filter pencarian
            if (!this.searchQuery) return true;
            const q = this.searchQuery.toLowerCase().trim();
            if (menuKey.toLowerCase().includes(q)) return true;
            const perms = this.menuPerms[menuKey] || [];
            return perms.some(p => p.toLowerCase().includes(q));
        },

        isSubmenuVisible(menuKey, subKey) {
            if (!this.searchQuery) return true;
            const q = this.searchQuery.toLowerCase().trim();
            if (subKey.toLowerCase().includes(q) || menuKey.toLowerCase().includes(q)) return true;
            const perms = this.menuPerms[menuKey] || [];
            return perms.some(p => p.toLowerCase().includes(q));
        },

        isModuleVisible(modKey, modLabel) {
            if (!this.searchQuery) return true;
            const q = this.searchQuery.toLowerCase().trim();
            return modKey.toLowerCase().includes(q) || modLabel.toLowerCase().includes(q);
        },

        prepareSubmit(event) {
            const container = document.getElementById('submitted-permissions-container');
            if (!container) return;
            container.innerHTML = '';

            // Pastikan semua parent gates aktif untuk setiap izin yang dipilih
            Object.keys(this.selectedPerms).forEach(pName => {
                if (this.selectedPerms[pName]) {
                    const gates = this.parentGates[pName];
                    if (Array.isArray(gates)) {
                        gates.forEach(g => {
                            this.selectedPerms[g] = true;
                        });
                    }
                }
            });

            // Buat elemen input hidden untuk setiap izin yang aktif
            const fragment = document.createDocumentFragment();
            Object.keys(this.selectedPerms).forEach(pName => {
                if (this.selectedPerms[pName]) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'permissions[]';
                    input.value = pName;
                    fragment.appendChild(input);
                }
            });
            container.appendChild(fragment);
        },

        confirmDeleteRole(role) {
            Swal.fire({
                title: 'Hapus Role "' + role.name + '"?',
                text: 'Role yang dihapus tidak dapat dipulihkan. Pengguna dengan role ini akan kehilangan hak aksesnya.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus Role',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('delete-role-form');
                    form.action = '/roles/' + role.id;
                    form.submit();
                }
            });
        }
    };
}
</script>

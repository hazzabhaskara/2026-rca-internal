@php
    $appSystemKey = 'operasional';
    $basePath = base_path();
    $dbName = config('database.connections.mysql.database', '');
    
    if (strpos($basePath, '2025') !== false || strpos($dbName, '2025') !== false || config('app.name') === 'RembulanCitraAbadi_Internal_2025') {
        $appSystemKey = 'internal_2025';
    } elseif (strpos($basePath, '2026') !== false || strpos($dbName, '2026') !== false || config('app.name') === 'RembulanCitraAbadi_Internal_2026') {
        $appSystemKey = 'internal_2026';
    }

    $systemTheme = [
        'operasional' => [
            'badge' => 'OPERASIONAL',
            'badge_bg' => 'bg-indigo-500/15',
            'badge_text' => 'text-indigo-400',
            'badge_border' => 'border-indigo-500/30',
            'active_bg' => 'bg-indigo-600',
            'active_text' => 'text-white',
            'active_ring' => 'ring-1 ring-indigo-400/50',
            'accent_hover' => 'hover:text-indigo-300',
            'glow' => 'shadow-indigo-500/20',
        ],
        'internal_2025' => [
            'badge' => 'INTERNAL 2025',
            'badge_bg' => 'bg-amber-500/15',
            'badge_text' => 'text-amber-400',
            'badge_border' => 'border-amber-500/30',
            'active_bg' => 'bg-amber-600',
            'active_text' => 'text-white',
            'active_ring' => 'ring-1 ring-amber-400/50',
            'accent_hover' => 'hover:text-amber-300',
            'glow' => 'shadow-amber-500/20',
        ],
        'internal_2026' => [
            'badge' => 'INTERNAL 2026',
            'badge_bg' => 'bg-emerald-500/15',
            'badge_text' => 'text-emerald-400',
            'badge_border' => 'border-emerald-500/30',
            'active_bg' => 'bg-emerald-600',
            'active_text' => 'text-white',
            'active_ring' => 'ring-1 ring-emerald-400/50',
            'accent_hover' => 'hover:text-emerald-300',
            'glow' => 'shadow-emerald-500/20',
        ],
    ][$appSystemKey];
@endphp

<!-- Mobile Backdrop -->
<div x-show="mobileSidebarOpen" 
     x-cloak
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="mobileSidebarOpen = false"
     class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-40 lg:hidden">
</div>

<!-- Sidebar Container -->
<aside :class="{
        'w-64': !sidebarCollapsed,
        'w-20': sidebarCollapsed,
        '-translate-x-full lg:translate-x-0': !mobileSidebarOpen,
        'translate-x-0': mobileSidebarOpen
       }"
       class="fixed top-0 bottom-0 left-0 z-50 flex flex-col bg-slate-900 border-r border-slate-800 text-slate-300 transition-all duration-300 ease-in-out shadow-2xl overflow-hidden select-none">
    
    <!-- Header / Brand -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800/80 bg-slate-950/40 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden group">
            <div class="h-10 w-10 shrink-0 rounded-xl bg-white/5 border border-white/10 p-1 flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform duration-200">
                <img src="{{ asset('storage/' . \App\Setting::get('logo', 'logo.jpg')) }}" 
                     alt="Logo" 
                     class="h-full w-full object-contain rounded-lg"
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=RCA&background=4F46E5&color=fff&size=64'">
            </div>
            <div x-show="!sidebarCollapsed" x-cloak class="flex flex-col min-w-0 transition-opacity duration-200">
                <span class="font-bold text-sm tracking-wider text-white truncate">PT. RCA ERP</span>
                <div class="flex items-center space-x-1.5 mt-0.5">
                    <span class="inline-flex items-center px-2 py-0.5 text-[9px] font-semibold tracking-wider rounded-full border {{ $systemTheme['badge_bg'] }} {{ $systemTheme['badge_text'] }} {{ $systemTheme['badge_border'] }}">
                        {{ $systemTheme['badge'] }}
                    </span>
                </div>
            </div>
        </a>

        <!-- Desktop Mini Toggle Button inside Header -->
        <button @click="toggleSidebarCollapse()" 
                type="button"
                x-show="!sidebarCollapsed" 
                x-cloak
                title="Ciutkan Sidebar"
                class="hidden lg:flex items-center justify-center h-8 w-8 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 active:scale-[0.96] transition-all duration-150">
            <i class="fas fa-angles-left text-xs"></i>
        </button>

        <!-- Mobile Close Button -->
        <button @click="mobileSidebarOpen = false" 
                type="button"
                class="lg:hidden flex items-center justify-center h-8 w-8 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
            <i class="fas fa-xmark text-sm"></i>
        </button>
    </div>

    <!-- Quick Search Input inside Sidebar (Expanded only) -->
    <div x-show="!sidebarCollapsed" x-cloak class="p-3 border-b border-slate-800/60 bg-slate-900/60 shrink-0">
        <div class="relative">
            <i class="fas fa-magnifying-glass absolute left-3 top-2.5 text-xs text-slate-500"></i>
            <input type="text" 
                   x-model="menuSearch" 
                   placeholder="Filter menu..." 
                   class="w-full bg-slate-950/80 border border-slate-800 rounded-lg pl-8 pr-3 py-1.5 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
            <button x-show="menuSearch" 
                    @click="menuSearch = ''" 
                    class="absolute right-2.5 top-2 text-xs text-slate-500 hover:text-slate-300">
                <i class="fas fa-times-circle"></i>
            </button>
        </div>
    </div>

    <!-- Navigation Menu List -->
    <nav class="flex-1 overflow-y-auto overflow-x-hidden p-2.5 space-y-1 custom-sidebar-scrollbar">
        
        <!-- DASHBOARD -->
        <a href="{{ route('dashboard') }}" 
           class="flex items-center px-3 py-2 rounded-xl text-xs font-medium transition-all duration-150 group active:scale-[0.96] {{ request()->routeIs('dashboard') ? $systemTheme['active_bg'] . ' ' . $systemTheme['active_text'] . ' shadow-md ' . $systemTheme['glow'] : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
           :title="sidebarCollapsed ? 'Dashboard' : ''">
            <i class="fas fa-gauge-high w-5 text-center text-sm mr-3 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
            <span x-show="!sidebarCollapsed" x-cloak class="truncate">Dashboard</span>
        </a>

        <!-- 1. SETUP -->
        @php
            $isSetupActive = request()->routeIs([
                'setting.*', 'company_profile.*', 'taxpayers_company.*',
                'start_new_year.*', 'numbering_account.*', 'klasifikasiAkun.*', 'chartOfAccount.*',
                'departemen.*', 'setting_departement.*', 'linkedAccount.*', 'sales_taxes.*',
                'report.account', 'report.klasifikasi', 'report.departemen-akun', 'report.sales_taxes',
                'users.*', 'roles.*', 'permissions.*', 'approval_step.*'
            ]);
        @endphp
        <div x-data="{ open: @json($isSetupActive) || isSectionOpen('setup') }" class="space-y-1">
            <button @click="open = !open; toggleSection('setup', open)" 
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 group active:scale-[0.96]"
                    :class="{ 'bg-slate-800/50 text-white': open || {{ $isSetupActive ? 'true' : 'false' }} }"
                    :title="sidebarCollapsed ? 'Setup' : ''">
                <div class="flex items-center min-w-0">
                    <i class="fas fa-sliders w-5 text-center text-sm mr-3 shrink-0 text-slate-400 group-hover:text-white"></i>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Setup</span>
                </div>
                <i x-show="!sidebarCollapsed" x-cloak class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>

            <div x-show="open && !sidebarCollapsed" x-collapse x-cloak class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-4">
                <!-- Sub: Company -->
                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-1">Company</div>
                @can('setting_setup.access')
                    @if (Route::has('setting.index'))
                        <a href="{{ route('setting.index') }}" class="sidebar-sublink {{ request()->routeIs('setting.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Settings</span>
                        </a>
                    @endif
                @endcan
                @can('company_profile.access')
                    @if (Route::has('company_profile.show') || Route::has('company_profile.create'))
                        <a href="{{ $company && Route::has('company_profile.show') ? route('company_profile.show', $company->id) : (Route::has('company_profile.create') ? route('company_profile.create') : '#') }}" class="sidebar-sublink {{ request()->routeIs('company_profile.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Company Profile</span>
                        </a>
                    @endif
                @endcan
                @can('taxpayers_company.access')
                    @if (Route::has('taxpayers_company.show') || Route::has('taxpayers_company.create'))
                        <a href="{{ $taxpayers && Route::has('taxpayers_company.show') ? route('taxpayers_company.show', $taxpayers->id) : (Route::has('taxpayers_company.create') ? route('taxpayers_company.create') : '#') }}" class="sidebar-sublink {{ request()->routeIs('taxpayers_company.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Taxpayer Profile</span>
                        </a>
                    @endif
                @endcan

                <!-- Sub: General -->
                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">General</div>
                @can('year_book.access')
                    @if (Route::has('start_new_year.index'))
                        <a href="{{ route('start_new_year.index') }}" class="sidebar-sublink {{ request()->routeIs('start_new_year.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Year Book</span>
                        </a>
                    @endif
                @endcan
                @can('numbering.access')
                    @if (Route::has('numbering_account.index'))
                        <a href="{{ route('numbering_account.index') }}" class="sidebar-sublink {{ request()->routeIs('numbering_account.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Numbering</span>
                        </a>
                    @endif
                @endcan
                @can('klasifikasi_akun.access')
                    @if (Route::has('klasifikasiAkun.index'))
                        <a href="{{ route('klasifikasiAkun.index') }}" class="sidebar-sublink {{ request()->routeIs('klasifikasiAkun.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Account Classification</span>
                        </a>
                    @endif
                @endcan
                @can('chart_of_account.access')
                    @if (Route::has('chartOfAccount.index'))
                        <a href="{{ route('chartOfAccount.index') }}" class="sidebar-sublink {{ request()->routeIs('chartOfAccount.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Accounts (COA)</span>
                        </a>
                    @endif
                @endcan
                @can('departement.access')
                    @if (Route::has('departemen.index'))
                        <a href="{{ route('departemen.index') }}" class="sidebar-sublink {{ request()->routeIs('departemen.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Departments</span>
                        </a>
                    @endif
                @endcan
                @can('linked_account_setup.access')
                    @if (Route::has('linkedAccount.index'))
                        <a href="{{ route('linkedAccount.index') }}" class="sidebar-sublink {{ request()->routeIs('linkedAccount.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Linked Account</span>
                        </a>
                    @endif
                @endcan
                @can('sales_taxes.access')
                    @if (Route::has('sales_taxes.index'))
                        <a href="{{ route('sales_taxes.index') }}" class="sidebar-sublink {{ request()->routeIs('sales_taxes.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Taxes</span>
                        </a>
                    @endif
                @endcan

                <!-- Sub: Reports Setup -->
                @can('reports_setup.access')
                    <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Reports</div>
                    @can('report_account.access')
                        @if (Route::has('report.account'))
                            <a href="{{ route('report.account') }}" class="sidebar-sublink {{ request()->routeIs('report.account') ? 'active-sublink' : '' }}">
                                <i class="fas fa-circle-dot sublink-dot"></i><span>Account List</span>
                            </a>
                        @endif
                    @endcan
                    @can('report_klasifikasi_akun.access')
                        @if (Route::has('report.klasifikasi'))
                            <a href="{{ route('report.klasifikasi') }}" class="sidebar-sublink {{ request()->routeIs('report.klasifikasi') ? 'active-sublink' : '' }}">
                                <i class="fas fa-circle-dot sublink-dot"></i><span>Classification List</span>
                            </a>
                        @endif
                    @endcan
                    @can('report_departemen_account.access')
                        @if (Route::has('report.departemen-akun'))
                            <a href="{{ route('report.departemen-akun') }}" class="sidebar-sublink {{ request()->routeIs('report.departemen-akun') ? 'active-sublink' : '' }}">
                                <i class="fas fa-circle-dot sublink-dot"></i><span>Dept Accounts List</span>
                            </a>
                        @endif
                    @endcan
                    @if (Route::has('report.sales_taxes'))
                        <a href="{{ route('report.sales_taxes') }}" class="sidebar-sublink {{ request()->routeIs('report.sales_taxes') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Taxes List</span>
                        </a>
                    @endif
                @endcan

                <!-- Sub: Security -->
                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Security</div>
                @if (Route::has('users.index'))
                    <a href="{{ route('users.index') }}" class="sidebar-sublink {{ request()->routeIs('users.*') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Users & Roles</span>
                    </a>
                @endif
                @can('approval_step.access')
                    @if (Route::has('approval_step.index'))
                        <a href="{{ route('approval_step.index') }}" class="sidebar-sublink {{ request()->routeIs('approval_step.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Approval Step</span>
                        </a>
                    @endif
                @endcan
            </div>
        </div>

        <!-- 2. SALES -->
        @php
            $isSalesActive = request()->routeIs([
                'linkedAccountSales.*', 'sales_option.*', 'sales_discount.*', 'item_category.*',
                'items.*', 'PaymentMethod.*', 'customers.*', 'sales_person.*', 'sales_order.*',
                'sales_invoice.*', 'sales_deposits.*', 'receipts.*'
            ]);
        @endphp
        <div x-data="{ open: @json($isSalesActive) || isSectionOpen('sales') }" class="space-y-1">
            <button @click="open = !open; toggleSection('sales', open)" 
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 group active:scale-[0.96]"
                    :class="{ 'bg-slate-800/50 text-white': open || {{ $isSalesActive ? 'true' : 'false' }} }"
                    :title="sidebarCollapsed ? 'Sales' : ''">
                <div class="flex items-center min-w-0">
                    <i class="fas fa-cash-register w-5 text-center text-sm mr-3 shrink-0 text-slate-400 group-hover:text-white"></i>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Sales</span>
                </div>
                <i x-show="!sidebarCollapsed" x-cloak class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse x-cloak class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-4">
                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-1">Setup</div>
                @can('linked_account_sales.access')
                    @if (Route::has('linkedAccountSales.index'))
                        <a href="{{ route('linkedAccountSales.index') }}" class="sidebar-sublink {{ request()->routeIs('linkedAccountSales.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Linked Accounts</span>
                        </a>
                    @endif
                @endcan
                @can('option_sales.access')
                    @if (Route::has('sales_option.create'))
                        <a href="{{ route('sales_option.create') }}" class="sidebar-sublink {{ request()->routeIs('sales_option.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Options</span>
                        </a>
                    @endif
                @endcan
                @can('sales_discount.access')
                    @if (Route::has('sales_discount.index'))
                        <a href="{{ route('sales_discount.index') }}" class="sidebar-sublink {{ request()->routeIs('sales_discount.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Sales Discount</span>
                        </a>
                    @endif
                @endcan

                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Master Data</div>
                @can('customers.access')
                    @if (Route::has('customers.index'))
                        <a href="{{ route('customers.index') }}" class="sidebar-sublink {{ request()->routeIs('customers.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Customers</span>
                        </a>
                    @endif
                @endcan
                @can('sales_person.access')
                    @if (Route::has('sales_person.index'))
                        <a href="{{ route('sales_person.index') }}" class="sidebar-sublink {{ request()->routeIs('sales_person.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Sales Person</span>
                        </a>
                    @endif
                @endcan

                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Transactions</div>
                @can('sales_orders.access')
                    @if (Route::has('sales_order.index'))
                        <a href="{{ route('sales_order.index') }}" class="sidebar-sublink {{ request()->routeIs('sales_order.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Sales Orders</span>
                        </a>
                    @endif
                @endcan
                @can('sales_invoice.access')
                    @if (Route::has('sales_invoice.index'))
                        <a href="{{ route('sales_invoice.index') }}" class="sidebar-sublink {{ request()->routeIs('sales_invoice.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Sales Invoices</span>
                        </a>
                    @endif
                @endcan
                @can('deposits.access')
                    @if (Route::has('sales_deposits.index'))
                        <a href="{{ route('sales_deposits.index') }}" class="sidebar-sublink {{ request()->routeIs('sales_deposits.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Deposits</span>
                        </a>
                    @endif
                @endcan
                @can('receipts.access')
                    @if (Route::has('receipts.index'))
                        <a href="{{ route('receipts.index') }}" class="sidebar-sublink {{ request()->routeIs('receipts.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Receipts</span>
                        </a>
                    @endif
                @endcan
            </div>
        </div>

        <!-- 3. PURCHASES -->
        @php
            $isPurchasesActive = request()->routeIs([
                'linkedAccountPurchases.*', 'purchases_options.*', 'vendors.*',
                'purchase_order.*', 'purchase_invoice.*', 'prepayment.*', 'payment.*', 'payment_expense.*'
            ]);
        @endphp
        <div x-data="{ open: @json($isPurchasesActive) || isSectionOpen('purchases') }" class="space-y-1">
            <button @click="open = !open; toggleSection('purchases', open)" 
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 group active:scale-[0.96]"
                    :class="{ 'bg-slate-800/50 text-white': open || {{ $isPurchasesActive ? 'true' : 'false' }} }"
                    :title="sidebarCollapsed ? 'Purchases' : ''">
                <div class="flex items-center min-w-0">
                    <i class="fas fa-cart-shopping w-5 text-center text-sm mr-3 shrink-0 text-slate-400 group-hover:text-white"></i>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Purchases</span>
                </div>
                <i x-show="!sidebarCollapsed" x-cloak class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse x-cloak class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-4">
                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-1">Setup</div>
                @can('linked_account_purchase.access')
                    @if (Route::has('linkedAccountPurchases.index'))
                        <a href="{{ route('linkedAccountPurchases.index') }}" class="sidebar-sublink {{ request()->routeIs('linkedAccountPurchases.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Linked Account</span>
                        </a>
                    @endif
                @endcan
                @can('option_purchase.access')
                    @if (Route::has('purchases_options.create'))
                        <a href="{{ route('purchases_options.create') }}" class="sidebar-sublink {{ request()->routeIs('purchases_options.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Options</span>
                        </a>
                    @endif
                @endcan

                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Master Data</div>
                @can('vendor.access')
                    @if (Route::has('vendors.index'))
                        <a href="{{ route('vendors.index') }}" class="sidebar-sublink {{ request()->routeIs('vendors.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Vendors</span>
                        </a>
                    @endif
                @endcan

                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Transactions</div>
                @can('purchase_order.access')
                    @if (Route::has('purchase_order.index'))
                        <a href="{{ route('purchase_order.index') }}" class="sidebar-sublink {{ request()->routeIs('purchase_order.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Purchases Orders</span>
                        </a>
                    @endif
                @endcan
                @can('purchase_invoice.access')
                    @if (Route::has('purchase_invoice.index'))
                        <a href="{{ route('purchase_invoice.index') }}" class="sidebar-sublink {{ request()->routeIs('purchase_invoice.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Purchase Invoice</span>
                        </a>
                    @endif
                @endcan
                @can('prepayment_purchase.access')
                    @if (Route::has('prepayment.index'))
                        <a href="{{ route('prepayment.index') }}" class="sidebar-sublink {{ request()->routeIs('prepayment.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Prepayments</span>
                        </a>
                    @endif
                @endcan
                @can('payment_purchase.access')
                    @if (Route::has('payment.index'))
                        <a href="{{ route('payment.index') }}" class="sidebar-sublink {{ request()->routeIs('payment.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Payments</span>
                        </a>
                    @endif
                @endcan
                @can('payment_expense.access')
                    @if (Route::has('payment_expense.index'))
                        <a href="{{ route('payment_expense.index') }}" class="sidebar-sublink {{ request()->routeIs('payment_expense.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Payment Expense (Kas Keluar)</span>
                        </a>
                    @endif
                @endcan
            </div>
        </div>

        <!-- 4. INVENTORY -->
        @php
            $isInventoryActive = request()->routeIs([
                'options_inventory.*', 'price_list_inventory.*', 'lokasi_inventory.*', 'item_category.*',
                'items.*', 'inventory.*', 'build_of_bom.*', 'item_assembly.*', 'transfer_inventory.*'
            ]);
        @endphp
        <div x-data="{ open: @json($isInventoryActive) || isSectionOpen('inventory') }" class="space-y-1">
            <button @click="open = !open; toggleSection('inventory', open)" 
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 group active:scale-[0.96]"
                    :class="{ 'bg-slate-800/50 text-white': open || {{ $isInventoryActive ? 'true' : 'false' }} }"
                    :title="sidebarCollapsed ? 'Inventory' : ''">
                <div class="flex items-center min-w-0">
                    <i class="fas fa-boxes-stacked w-5 text-center text-sm mr-3 shrink-0 text-slate-400 group-hover:text-white"></i>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Inventory</span>
                </div>
                <i x-show="!sidebarCollapsed" x-cloak class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse x-cloak class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-4">
                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-1">Setup</div>
                @can('kategori_inventory.access')
                    @if (Route::has('item_category.index'))
                        <a href="{{ route('item_category.index') }}" class="sidebar-sublink {{ request()->routeIs('item_category.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Item Category</span>
                        </a>
                    @endif
                @endcan
                @if (Route::has('options_inventory.edit') && $options_inventory)
                    <a href="{{ route('options_inventory.edit', $options_inventory->id) }}" class="sidebar-sublink {{ request()->routeIs('options_inventory.*') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Options</span>
                    </a>
                @endif
                @can('price_list_inventory.access')
                    @if (Route::has('price_list_inventory.index'))
                        <a href="{{ route('price_list_inventory.index') }}" class="sidebar-sublink {{ request()->routeIs('price_list_inventory.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Price List</span>
                        </a>
                    @endif
                @endcan
                @can('lokasi_inventory.access')
                    @if (Route::has('lokasi_inventory.index'))
                        <a href="{{ route('lokasi_inventory.index') }}" class="sidebar-sublink {{ request()->routeIs('lokasi_inventory.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Locations</span>
                        </a>
                    @endif
                @endcan

                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Operations</div>
                @if (Route::has('items.index'))
                    <a href="{{ route('items.index') }}" class="sidebar-sublink {{ request()->routeIs('items.*') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Items</span>
                    </a>
                @endif
                @if (Route::has('inventory.index'))
                    <a href="{{ route('inventory.index') }}" class="sidebar-sublink {{ request()->routeIs('inventory.*') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Inventory & Services</span>
                    </a>
                @endif
                @if (Route::has('transfer_inventory.index'))
                    <a href="{{ route('transfer_inventory.index') }}" class="sidebar-sublink {{ request()->routeIs('transfer_inventory.*') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Transfer Inventory</span>
                    </a>
                @endif

                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Manufacturing</div>
                @if (Route::has('build_of_bom.index'))
                    <a href="{{ route('build_of_bom.index') }}" class="sidebar-sublink {{ request()->routeIs('build_of_bom.*') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Build From BOM</span>
                    </a>
                @endif
                @if (Route::has('item_assembly.index'))
                    <a href="{{ route('item_assembly.index') }}" class="sidebar-sublink {{ request()->routeIs('item_assembly.*') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Build From Assembly</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- 5. BUDGETING -->
        @php
            $isBudgetingActive = request()->routeIs(['approval_step.*', 'rekening.*', 'pengajuan.*']);
        @endphp
        <div x-data="{ open: @json($isBudgetingActive) || isSectionOpen('budgeting') }" class="space-y-1">
            <button @click="open = !open; toggleSection('budgeting', open)" 
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 group active:scale-[0.96]"
                    :class="{ 'bg-slate-800/50 text-white': open || {{ $isBudgetingActive ? 'true' : 'false' }} }"
                    :title="sidebarCollapsed ? 'Budgeting' : ''">
                <div class="flex items-center min-w-0">
                    <i class="fas fa-chart-pie w-5 text-center text-sm mr-3 shrink-0 text-slate-400 group-hover:text-white"></i>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Budgeting</span>
                </div>
                <i x-show="!sidebarCollapsed" x-cloak class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse x-cloak class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-4">
                @can('approval_step.access')
                    @if (Route::has('approval_step.index'))
                        <a href="{{ route('approval_step.index') }}" class="sidebar-sublink {{ request()->routeIs('approval_step.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Approval Step</span>
                        </a>
                    @endif
                @endcan
                @can('rekening.access')
                    @if (Route::has('rekening.index'))
                        <a href="{{ route('rekening.index') }}" class="sidebar-sublink {{ request()->routeIs('rekening.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Rekening</span>
                        </a>
                    @endif
                @endcan
                @can('create_budget.access')
                    @if (Route::has('pengajuan.create'))
                        <a href="{{ route('pengajuan.create') }}" class="sidebar-sublink {{ request()->routeIs('pengajuan.create') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Create Budget</span>
                        </a>
                    @endif
                @endcan
                @if (Route::has('pengajuan.index'))
                    <a href="{{ route('pengajuan.index') }}" class="sidebar-sublink {{ request()->routeIs('pengajuan.index') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Data Budget</span>
                    </a>
                @endif
                @can('budget_submission.access')
                    @if (Route::has('pengajuan.approval.index'))
                        <a href="{{ route('pengajuan.approval.index') }}" class="sidebar-sublink {{ request()->routeIs('pengajuan.approval.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Budget Submission</span>
                        </a>
                    @endif
                @endcan
            </div>
        </div>

        <!-- 6. CLOSING HARIAN -->
        @php
            $isClosingActive = request()->routeIs(['linkedAccount_closing.*', 'closing_harian.*', 'rekonsiliasi_closing.*']);
        @endphp
        <div x-data="{ open: @json($isClosingActive) || isSectionOpen('closing') }" class="space-y-1">
            <button @click="open = !open; toggleSection('closing', open)" 
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 group active:scale-[0.96]"
                    :class="{ 'bg-slate-800/50 text-white': open || {{ $isClosingActive ? 'true' : 'false' }} }"
                    :title="sidebarCollapsed ? 'Closing Harian' : ''">
                <div class="flex items-center min-w-0">
                    <i class="fas fa-calendar-check w-5 text-center text-sm mr-3 shrink-0 text-slate-400 group-hover:text-white"></i>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Closing Harian</span>
                </div>
                <i x-show="!sidebarCollapsed" x-cloak class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse x-cloak class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-4">
                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-1">Setup</div>
                @can('linked_account_closing.access')
                    @if (Route::has('linkedAccount_closing.index'))
                        <a href="{{ route('linkedAccount_closing.index') }}" class="sidebar-sublink {{ request()->routeIs('linkedAccount_closing.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Linked Account</span>
                        </a>
                    @endif
                @endcan
                @can('wahana.access')
                    @if (Route::has('wahana.index'))
                        <a href="{{ route('wahana.index') }}" class="sidebar-sublink {{ request()->routeIs('wahana.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Wahana (Rides)</span>
                        </a>
                    @endif
                @endcan

                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Transactions</div>
                @can('closing_harian.create')
                    @if (Route::has('closing_harian.create'))
                        <a href="{{ route('closing_harian.create') }}" class="sidebar-sublink {{ request()->routeIs('closing_harian.create') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Create Closing Harian</span>
                        </a>
                    @endif
                @endcan
                @can('closing_harian.access')
                    @if (Route::has('closing_harian.index'))
                        <a href="{{ route('closing_harian.index') }}" class="sidebar-sublink {{ request()->routeIs('closing_harian.index') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Data Closing Harian</span>
                        </a>
                    @endif
                @endcan
                @can('rekonsiliasi_closing.access')
                    @if (Route::has('rekonsiliasi_closing.index'))
                        <a href="{{ route('rekonsiliasi_closing.index') }}" class="sidebar-sublink {{ request()->routeIs('rekonsiliasi_closing.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-scale-balanced sublink-dot text-indigo-400"></i><span>Rekonsiliasi Kas/Bank</span>
                        </a>
                    @endif
                @endcan
            </div>
        </div>

        <!-- 7. PAYROLL -->
        @php
            $isPayrollActive = request()->routeIs([
                'LevelKaryawan.*', 'jabatan.*', 'komponen_penghasilan.*', 'group_unit.*',
                'unit_kerja.*', 'wahana.*', 'ptkp.*', 'employee.*', 'komposisi_gaji.*',
                'jenis_hari.*', 'jam_kerja.*', 'target_unit.*', 'target_wahana.*',
                'crew_shift_karyawan.*', 'shift_karyawan.*', 'transaksi_wahana.*',
                'bonus_karyawan.*', 'tax_rates.*', 'pembayaran_gaji.*', 'pembayaran_gaji_nonstaff.*',
                'slip.*', 'absensi.*', 'report.absensi.*', 'report.target_wahana.*'
            ]);
        @endphp
        <div x-data="{ open: @json($isPayrollActive) || isSectionOpen('payroll') }" class="space-y-1">
            <button @click="open = !open; toggleSection('payroll', open)" 
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 group active:scale-[0.96]"
                    :class="{ 'bg-slate-800/50 text-white': open || {{ $isPayrollActive ? 'true' : 'false' }} }"
                    :title="sidebarCollapsed ? 'Payroll' : ''">
                <div class="flex items-center min-w-0">
                    <i class="fas fa-users-gear w-5 text-center text-sm mr-3 shrink-0 text-slate-400 group-hover:text-white"></i>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Payroll</span>
                </div>
                <i x-show="!sidebarCollapsed" x-cloak class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse x-cloak class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-4">
                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-1">Setup & Master</div>
                @can('level_karyawan.access')
                    @if (Route::has('LevelKaryawan.index'))
                        <a href="{{ route('LevelKaryawan.index') }}" class="sidebar-sublink {{ request()->routeIs('LevelKaryawan.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Employee Level</span>
                        </a>
                    @endif
                @endcan
                @can('jabatan.access')
                    @if (Route::has('jabatan.index'))
                        <a href="{{ route('jabatan.index') }}" class="sidebar-sublink {{ request()->routeIs('jabatan.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Position</span>
                        </a>
                    @endif
                @endcan
                @can('komponen_penghasilan.access')
                    @if (Route::has('komponen_penghasilan.index'))
                        <a href="{{ route('komponen_penghasilan.index') }}" class="sidebar-sublink {{ request()->routeIs('komponen_penghasilan.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Komponen by Level</span>
                        </a>
                    @endif
                @endcan
                @can('group_unit.access')
                    @if (Route::has('group_unit.index'))
                        <a href="{{ route('group_unit.index') }}" class="sidebar-sublink {{ request()->routeIs('group_unit.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Group Unit</span>
                        </a>
                    @endif
                @endcan
                @can('unit.access')
                    @if (Route::has('unit_kerja.index'))
                        <a href="{{ route('unit_kerja.index') }}" class="sidebar-sublink {{ request()->routeIs('unit_kerja.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Units/Departemens</span>
                        </a>
                    @endif
                @endcan
                @can('ptkp.access')
                    @if (Route::has('ptkp.index'))
                        <a href="{{ route('ptkp.index') }}" class="sidebar-sublink {{ request()->routeIs('ptkp.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>PTKP</span>
                        </a>
                    @endif
                @endcan
                @can('employee.access')
                    @if (Route::has('employee.index'))
                        <a href="{{ route('employee.index') }}" class="sidebar-sublink {{ request()->routeIs('employee.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Employee Profiles</span>
                        </a>
                    @endif
                @endcan
                @can('komposisi_gaji.access')
                    @if (Route::has('komposisi_gaji.index'))
                        <a href="{{ route('komposisi_gaji.index') }}" class="sidebar-sublink {{ request()->routeIs('komposisi_gaji.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Komponen by Employee</span>
                        </a>
                    @endif
                @endcan
                @can('jenis_hari.access')
                    @if (Route::has('jenis_hari.index'))
                        <a href="{{ route('jenis_hari.index') }}" class="sidebar-sublink {{ request()->routeIs('jenis_hari.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Type Of Days</span>
                        </a>
                    @endif
                @endcan
                @can('jam_kerja.access')
                    @if (Route::has('jam_kerja.index'))
                        <a href="{{ route('jam_kerja.index') }}" class="sidebar-sublink {{ request()->routeIs('jam_kerja.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Working Hours</span>
                        </a>
                    @endif
                @endcan
                @can('crew_shift_karyawan.access')
                    @if (Route::has('crew_shift_karyawan.index'))
                        <a href="{{ route('crew_shift_karyawan.index') }}" class="sidebar-sublink {{ request()->routeIs('crew_shift_karyawan.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Crew Scheduling</span>
                        </a>
                    @endif
                @endcan
                @can('shift_karyawan.access')
                    @if (Route::has('shift_karyawan.index'))
                        <a href="{{ route('shift_karyawan.index') }}" class="sidebar-sublink {{ request()->routeIs('shift_karyawan.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Personnel Scheduling</span>
                        </a>
                    @endif
                @endcan
                @can('bonus_karyawan.access')
                    @if (Route::has('bonus_karyawan.index'))
                        <a href="{{ route('bonus_karyawan.index') }}" class="sidebar-sublink {{ request()->routeIs('bonus_karyawan.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Employee Bonus Update</span>
                        </a>
                    @endif
                @endcan
                @can('tax_rates.access')
                    @if (Route::has('tax_rates.index'))
                        <a href="{{ route('tax_rates.index') }}" class="sidebar-sublink {{ request()->routeIs('tax_rates.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>TER (Tax Rates)</span>
                        </a>
                    @endif
                @endcan

                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Penggajian & Slip</div>
                @can('pembayaran_gaji.access')
                    @if (Route::has('pembayaran_gaji.index'))
                        <a href="{{ route('pembayaran_gaji.index') }}" class="sidebar-sublink {{ request()->routeIs('pembayaran_gaji.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Salary Calc Staff</span>
                        </a>
                    @endif
                @endcan
                @can('pembayaran_gaji_nonstaff.access')
                    @if (Route::has('pembayaran_gaji_nonstaff.index'))
                        <a href="{{ route('pembayaran_gaji_nonstaff.index') }}" class="sidebar-sublink {{ request()->routeIs('pembayaran_gaji_nonstaff.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Salary Calc Non-Staff</span>
                        </a>
                    @endif
                @endcan
                @if (Route::has('slip.index'))
                    <a href="{{ route('slip.index') }}" class="sidebar-sublink {{ request()->routeIs('slip.index') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Salary Slip Staff</span>
                    </a>
                @endif
                @if (Route::has('slip.nonStaff.index'))
                    <a href="{{ route('slip.nonStaff.index') }}" class="sidebar-sublink {{ request()->routeIs('slip.nonStaff.*') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Salary Slip Non-Staff</span>
                    </a>
                @endif

                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Absensi & Laporan</div>
                @can('absensi.access')
                    @if (Route::has('absensi.form'))
                        <a href="{{ route('absensi.form') }}" class="sidebar-sublink {{ request()->routeIs('absensi.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Absensi Pegawai</span>
                        </a>
                    @endif
                @endcan
                @can('rekap_absensi.access')
                    @if (Route::has('report.absensi.filter'))
                        <a href="{{ route('report.absensi.filter') }}" class="sidebar-sublink {{ request()->routeIs('report.absensi.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Rekap Absensi</span>
                        </a>
                    @endif
                @endcan
                @can('rekap_target_wahana.access')
                    @if (Route::has('report.target_wahana.filter'))
                        <a href="{{ route('report.target_wahana.filter') }}" class="sidebar-sublink {{ request()->routeIs('report.target_wahana.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Rekap Target Wahana</span>
                        </a>
                    @endif
                @endcan
            </div>
        </div>

        <!-- 8. ASSET -->
        @php
            $isAssetActive = request()->routeIs(['linkedAccountAsset.*', 'kategori_asset.*', 'lokasi.*', 'masa_manfaat.*', 'metode_penyusutan.*', 'tangible_asset.*', 'intangible_asset.*']);
        @endphp
        <div x-data="{ open: @json($isAssetActive) || isSectionOpen('asset') }" class="space-y-1">
            <button @click="open = !open; toggleSection('asset', open)" 
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 group active:scale-[0.96]"
                    :class="{ 'bg-slate-800/50 text-white': open || {{ $isAssetActive ? 'true' : 'false' }} }"
                    :title="sidebarCollapsed ? 'Asset' : ''">
                <div class="flex items-center min-w-0">
                    <i class="fas fa-landmark w-5 text-center text-sm mr-3 shrink-0 text-slate-400 group-hover:text-white"></i>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Asset</span>
                </div>
                <i x-show="!sidebarCollapsed" x-cloak class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse x-cloak class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-4">
                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-1">Setup</div>
                @can('setup_asset.access')
                    @if (Route::has('linkedAccountAsset.index'))
                        <a href="{{ route('linkedAccountAsset.index') }}" class="sidebar-sublink {{ request()->routeIs('linkedAccountAsset.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Linked Account</span>
                        </a>
                    @endif
                    @if (Route::has('kategori_asset.index'))
                        <a href="{{ route('kategori_asset.index') }}" class="sidebar-sublink {{ request()->routeIs('kategori_asset.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Category Asset</span>
                        </a>
                    @endif
                    @if (Route::has('lokasi.index'))
                        <a href="{{ route('lokasi.index') }}" class="sidebar-sublink {{ request()->routeIs('lokasi.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Location Asset</span>
                        </a>
                    @endif
                    @if (Route::has('masa_manfaat.index'))
                        <a href="{{ route('masa_manfaat.index') }}" class="sidebar-sublink {{ request()->routeIs('masa_manfaat.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Masa Manfaat</span>
                        </a>
                    @endif
                    @if (Route::has('metode_penyusutan.index'))
                        <a href="{{ route('metode_penyusutan.index') }}" class="sidebar-sublink {{ request()->routeIs('metode_penyusutan.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Metode Penyusutan</span>
                        </a>
                    @endif
                @endcan

                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Data Asset</div>
                @can('tangible_asset.access')
                    @if (Route::has('tangible_asset.index'))
                        <a href="{{ route('tangible_asset.index') }}" class="sidebar-sublink {{ request()->routeIs('tangible_asset.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Tangible Asset</span>
                        </a>
                    @endif
                @endcan
                @can('intangible_asset.access')
                    @if (Route::has('intangible_asset.index'))
                        <a href="{{ route('intangible_asset.index') }}" class="sidebar-sublink {{ request()->routeIs('intangible_asset.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Intangible Asset</span>
                        </a>
                    @endif
                @endcan
            </div>
        </div>

        <!-- 9. SPECPOSE -->
        @php
            $isSpecposeActive = request()->routeIs(['project.*']);
        @endphp
        <div x-data="{ open: @json($isSpecposeActive) || isSectionOpen('specpose') }" class="space-y-1">
            <button @click="open = !open; toggleSection('specpose', open)" 
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 group active:scale-[0.96]"
                    :class="{ 'bg-slate-800/50 text-white': open || {{ $isSpecposeActive ? 'true' : 'false' }} }"
                    :title="sidebarCollapsed ? 'Specpose' : ''">
                <div class="flex items-center min-w-0">
                    <i class="fas fa-briefcase w-5 text-center text-sm mr-3 shrink-0 text-slate-400 group-hover:text-white"></i>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Specpose</span>
                </div>
                <i x-show="!sidebarCollapsed" x-cloak class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse x-cloak class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-4">
                @if (Route::has('project.index'))
                    <a href="{{ route('project.index') }}" class="sidebar-sublink {{ request()->routeIs('project.index') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Data Specpose</span>
                    </a>
                @endif
                @if (Route::has('project.create'))
                    <a href="{{ route('project.create') }}" class="sidebar-sublink {{ request()->routeIs('project.create') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Create Specpose</span>
                    </a>
                @endif
                @if (Route::has('project.edit_project'))
                    <a href="{{ route('project.edit_project') }}" class="sidebar-sublink {{ request()->routeIs('project.edit_project') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Edit Specpose</span>
                    </a>
                @endif
                @if (Route::has('project.view_project'))
                    <a href="{{ route('project.view_project') }}" class="sidebar-sublink {{ request()->routeIs('project.view_project') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>View Specpose</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- 10. GENERAL JOURNAL -->
        @php 
            $isJournalActive = request()->routeIs([
                'journal_entry.*', 'rekonsiliasi_closing.*', 'unassigned_watchdog.*', 'assigned_watchdog.*', 'journal_audit.*'
            ]); 
        @endphp
        <div x-data="{ open: @json($isJournalActive) || isSectionOpen('journal') }" class="space-y-1">
            <button @click="open = !open; toggleSection('journal', open)" 
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 group active:scale-[0.96]"
                    :class="{ 'bg-slate-800/50 text-white': open || {{ $isJournalActive ? 'true' : 'false' }} }"
                    :title="sidebarCollapsed ? 'General Journal' : ''">
                <div class="flex items-center min-w-0">
                    <i class="fas fa-book-journal-whills w-5 text-center text-sm mr-3 shrink-0 text-slate-400 group-hover:text-white"></i>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">General Journal</span>
                </div>
                <i x-show="!sidebarCollapsed" x-cloak class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse x-cloak class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-4">
                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-1">Jurnal Umum</div>
                @can('journal_entry.create')
                    @if (Route::has('journal_entry.create'))
                        <a href="{{ route('journal_entry.create') }}" class="sidebar-sublink {{ request()->routeIs('journal_entry.create') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Create General Journal</span>
                        </a>
                    @endif
                @endcan
                @can('journal_entry.access')
                    @if (Route::has('journal_entry.index'))
                        <a href="{{ route('journal_entry.index') }}" class="sidebar-sublink {{ request()->routeIs('journal_entry.index') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Data General Journal</span>
                        </a>
                    @endif
                    @if (Route::has('journal_entry.view_journal_entry'))
                        <a href="{{ route('journal_entry.view_journal_entry') }}" class="sidebar-sublink {{ request()->routeIs('journal_entry.view_journal_entry') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>View General Journal</span>
                        </a>
                    @endif
                    @if (Route::has('journal_entry.filter_journal_entry'))
                        <a href="{{ route('journal_entry.filter_journal_entry') }}" class="sidebar-sublink {{ request()->routeIs('journal_entry.filter_journal_entry') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Edit General Journal</span>
                        </a>
                    @endif
                @endcan

                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Audit & Watchdog</div>
                @can('rekonsiliasi_closing.access')
                    @if (Route::has('rekonsiliasi_closing.index'))
                        <a href="{{ route('rekonsiliasi_closing.index') }}" class="sidebar-sublink {{ request()->routeIs('rekonsiliasi_closing.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-scale-balanced sublink-dot text-indigo-400"></i><span>Rekonsiliasi Kas/Bank</span>
                        </a>
                    @endif
                @endcan
                @can('unassigned_watchdog.access')
                    @if (Route::has('unassigned_watchdog.index'))
                        <a href="{{ route('unassigned_watchdog.index') }}" class="sidebar-sublink {{ request()->routeIs('unassigned_watchdog.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-shield-halved sublink-dot text-amber-400"></i><span>Unassigned Watchdog</span>
                        </a>
                    @endif
                @endcan
                @can('assigned_watchdog.access')
                    @if (Route::has('assigned_watchdog.index'))
                        <a href="{{ route('assigned_watchdog.index') }}" class="sidebar-sublink {{ request()->routeIs('assigned_watchdog.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-user-shield sublink-dot text-amber-400"></i><span>Assigned Watchdog</span>
                        </a>
                    @endif
                @endcan
                @can('journal_entry.access')
                    @if (Route::has('journal_audit.index'))
                        <a href="{{ route('journal_audit.index') }}" class="sidebar-sublink {{ request()->routeIs('journal_audit.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-scale-balanced sublink-dot text-indigo-400"></i><span>Audit & Kamus Akun</span>
                        </a>
                    @endif
                @endcan
            </div>
        </div>

        <!-- 11. REPORT -->
        @php 
            $isReportActive = request()->routeIs([
                'buku_besar.*', 'trial_balance.*', 'neraca.*', 'neraca_departemen.*',
                'income_statement.*', 'arus_kas.*', 'rekonsiliasi_closing.*', 'unassigned_watchdog.*',
                'assigned_watchdog.*', 'journal_audit.*'
            ]); 
        @endphp
        @canany(['report_menu.access', 'buku_besar.access', 'trial_balance.access', 'neraca.access', 'neraca_departemen.access', 'income_statement.access', 'income_statement_departement.access', 'arus_kas.access', 'rekonsiliasi_closing.access', 'unassigned_watchdog.access', 'assigned_watchdog.access', 'journal_entry.access', 'journal_audit.access'])
            <div x-data="{ open: @json($isReportActive) || isSectionOpen('report') }" class="space-y-1">
                <button @click="open = !open; toggleSection('report', open)" 
                        type="button"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 group active:scale-[0.96]"
                        :class="{ 'bg-slate-800/50 text-white': open || {{ $isReportActive ? 'true' : 'false' }} }"
                        :title="sidebarCollapsed ? 'Report' : ''">
                    <div class="flex items-center min-w-0">
                        <i class="fas fa-chart-column w-5 text-center text-sm mr-3 shrink-0 text-slate-400 group-hover:text-white"></i>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate font-semibold text-slate-200">Report</span>
                    </div>
                    <i x-show="!sidebarCollapsed" x-cloak class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
                </button>
                <div x-show="open && !sidebarCollapsed" x-collapse x-cloak class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-4">
                    <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-1">Laporan Keuangan</div>
                    @can('buku_besar.access')
                        @if (Route::has('buku_besar.filter_buku_besar'))
                            <a href="{{ route('buku_besar.filter_buku_besar') }}" class="sidebar-sublink {{ request()->routeIs('buku_besar.*') ? 'active-sublink' : '' }}">
                                <i class="fas fa-circle-dot sublink-dot"></i><span>Buku Besar</span>
                            </a>
                        @endif
                    @endcan
                    @can('trial_balance.access')
                        @if (Route::has('trial_balance.filter_trial_balance'))
                            <a href="{{ route('trial_balance.filter_trial_balance') }}" class="sidebar-sublink {{ request()->routeIs('trial_balance.*') ? 'active-sublink' : '' }}">
                                <i class="fas fa-circle-dot sublink-dot"></i><span>Trial Balance</span>
                            </a>
                        @endif
                    @endcan
                    @can('neraca.access')
                        @if (Route::has('neraca.filter_neraca'))
                            <a href="{{ route('neraca.filter_neraca') }}" class="sidebar-sublink {{ request()->routeIs('neraca.*') && !request()->routeIs('neraca_departemen.*') ? 'active-sublink' : '' }}">
                                <i class="fas fa-circle-dot sublink-dot"></i><span>Neraca</span>
                            </a>
                        @endif
                    @endcan
                    @can('neraca_departemen.access')
                        @if (Route::has('neraca_departemen.filter_neraca_departemen'))
                            <a href="{{ route('neraca_departemen.filter_neraca_departemen') }}" class="sidebar-sublink {{ request()->routeIs('neraca_departemen.*') ? 'active-sublink' : '' }}">
                                <i class="fas fa-sitemap sublink-dot text-indigo-400"></i><span>Balance Sheet Departemen</span>
                            </a>
                        @endif
                    @endcan
                    @can('income_statement.access')
                        @if (Route::has('income_statement.filter_income_statement'))
                            <a href="{{ route('income_statement.filter_income_statement') }}" class="sidebar-sublink {{ request()->routeIs('income_statement.filter_income_statement') ? 'active-sublink' : '' }}">
                                <i class="fas fa-circle-dot sublink-dot"></i><span>Income Statement</span>
                            </a>
                        @endif
                    @endcan
                    @can('income_statement_departement.access')
                        @if (Route::has('income_statement.filter_income_statement_departement'))
                            <a href="{{ route('income_statement.filter_income_statement_departement') }}" class="sidebar-sublink {{ request()->routeIs('income_statement.filter_income_statement_departement') ? 'active-sublink' : '' }}">
                                <i class="fas fa-circle-dot sublink-dot"></i><span>Income Statement Dept</span>
                            </a>
                        @endif
                    @endcan
                    @can('arus_kas.access')
                        @if (Route::has('arus_kas.filter_arus_kas'))
                            <a href="{{ route('arus_kas.filter_arus_kas') }}" class="sidebar-sublink {{ request()->routeIs('arus_kas.*') ? 'active-sublink' : '' }}">
                                <i class="fas fa-circle-dot sublink-dot"></i><span>Cash Flow</span>
                            </a>
                        @endif
                    @endcan

                    <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Audit & Rekonsiliasi</div>
                    @can('rekonsiliasi_closing.access')
                        @if (Route::has('rekonsiliasi_closing.index'))
                            <a href="{{ route('rekonsiliasi_closing.index') }}" class="sidebar-sublink {{ request()->routeIs('rekonsiliasi_closing.*') ? 'active-sublink' : '' }}">
                                <i class="fas fa-scale-balanced sublink-dot text-indigo-400"></i><span>Rekonsiliasi Closing vs Kas/Bank</span>
                            </a>
                        @endif
                    @endcan
                    @can('unassigned_watchdog.access')
                        @if (Route::has('unassigned_watchdog.index'))
                            <a href="{{ route('unassigned_watchdog.index') }}" class="sidebar-sublink {{ request()->routeIs('unassigned_watchdog.*') ? 'active-sublink' : '' }}">
                                <i class="fas fa-shield-halved sublink-dot text-amber-400"></i><span>Unassigned Watchdog</span>
                            </a>
                        @endif
                    @endcan
                    @can('assigned_watchdog.access')
                        @if (Route::has('assigned_watchdog.index'))
                            <a href="{{ route('assigned_watchdog.index') }}" class="sidebar-sublink {{ request()->routeIs('assigned_watchdog.*') ? 'active-sublink' : '' }}">
                                <i class="fas fa-user-shield sublink-dot text-amber-400"></i><span>Assigned Watchdog</span>
                            </a>
                        @endif
                    @endcan
                    @can('journal_entry.access')
                        @if (Route::has('journal_audit.index'))
                            <a href="{{ route('journal_audit.index') }}" class="sidebar-sublink {{ request()->routeIs('journal_audit.*') ? 'active-sublink' : '' }}">
                                <i class="fas fa-scale-balanced sublink-dot text-indigo-400"></i><span>Audit & Kamus Akun</span>
                            </a>
                        @endif
                    @endcan
                </div>
            </div>
        @endcanany

        <!-- 12. FISREC (FISCAL RECONCILIATION) -->
        @php 
            $isFisrecActive = request()->routeIs(['fiscal_account.*', 'fiscal_account_persamaan.*', 'fiscal_setting.*', 'fiscal.*', 'perhitungan_pajak_penghasilan.*']); 
        @endphp
        @canany(['fiscal.access', 'fiscal_account.create', 'fiscal_account_persamaan.create', 'perhitungan_pajak_penghasilan.access'])
            <div x-data="{ open: @json($isFisrecActive) || isSectionOpen('fisrec') }" class="space-y-1">
                <button @click="open = !open; toggleSection('fisrec', open)" 
                        type="button"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 group active:scale-[0.96]"
                        :class="{ 'bg-slate-800/50 text-white': open || {{ $isFisrecActive ? 'true' : 'false' }} }"
                        :title="sidebarCollapsed ? 'Fisrec' : ''">
                    <div class="flex items-center min-w-0">
                        <i class="fas fa-file-invoice-dollar w-5 text-center text-sm mr-3 shrink-0 text-slate-400 group-hover:text-white"></i>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate">Fisrec</span>
                    </div>
                    <i x-show="!sidebarCollapsed" x-cloak class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
                </button>
                <div x-show="open && !sidebarCollapsed" x-collapse x-cloak class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-4">
                    <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-1">Setup Fiskal</div>
                    @can('fiscal_account.create')
                        @if (Route::has('fiscal_account.index'))
                            <a href="{{ route('fiscal_account.index') }}" class="sidebar-sublink {{ request()->routeIs('fiscal_account.*') ? 'active-sublink' : '' }}">
                                <i class="fas fa-circle-dot sublink-dot"></i><span>Fiscal Account</span>
                            </a>
                        @endif
                    @endcan
                    @can('fiscal_account_persamaan.create')
                        @if (Route::has('fiscal_account_persamaan.index'))
                            <a href="{{ route('fiscal_account_persamaan.index') }}" class="sidebar-sublink {{ request()->routeIs('fiscal_account_persamaan.*') ? 'active-sublink' : '' }}">
                                <i class="fas fa-circle-dot sublink-dot"></i><span>Fiscal Account Persamaan</span>
                            </a>
                        @endif
                    @endcan
                    @can('fiscal.access')
                        @if (Route::has('fiscal_setting.index'))
                            <a href="{{ route('fiscal_setting.index') }}" class="sidebar-sublink {{ request()->routeIs('fiscal_setting.*') ? 'active-sublink' : '' }}">
                                <i class="fas fa-circle-dot sublink-dot"></i><span>Fiscal Settings (Opsi Fisrec)</span>
                            </a>
                        @endif
                    @endcan

                    <div class="text-[10px] font-bold tracking-wider uppercase text-slate-500 px-2 pt-2">Rekonsiliasi & Pajak</div>
                    @can('fiscal.access')
                        @if (Route::has('fiscal.fiscal_report'))
                            <a href="{{ route('fiscal.fiscal_report') }}" class="sidebar-sublink {{ request()->routeIs('fiscal.fiscal_report') ? 'active-sublink' : '' }}">
                                <i class="fas fa-circle-dot sublink-dot"></i><span>Fiscal Reconciliation</span>
                            </a>
                        @endif
                    @endcan
                    @can('perhitungan_pajak_penghasilan.access')
                        @if (Route::has('perhitungan_pajak_penghasilan.index'))
                            <a href="{{ route('perhitungan_pajak_penghasilan.index') }}" class="sidebar-sublink {{ request()->routeIs('perhitungan_pajak_penghasilan.*') ? 'active-sublink' : '' }}">
                                <i class="fas fa-circle-dot sublink-dot"></i><span>Perhitungan Pajak Penghasilan</span>
                            </a>
                        @endif
                    @endcan
                </div>
            </div>
        @endcanany

        <!-- 13. DOCUMENTS -->
        @php
            $isDocActive = request()->routeIs(['sales_orders.documents.*', 'sales_invoice.documents.*', 'purchase_order.documents.*', 'purchase_invoice.documents.*', 'taxes.*', 'rekening.*', 'transaksi_wahana.*']);
        @endphp
        <div x-data="{ open: @json($isDocActive) || isSectionOpen('documents') }" class="space-y-1">
            <button @click="open = !open; toggleSection('documents', open)" 
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 group active:scale-[0.96]"
                    :class="{ 'bg-slate-800/50 text-white': open || {{ $isDocActive ? 'true' : 'false' }} }"
                    :title="sidebarCollapsed ? 'Documents' : ''">
                <div class="flex items-center min-w-0">
                    <i class="fas fa-folder-closed w-5 text-center text-sm mr-3 shrink-0 text-slate-400 group-hover:text-white"></i>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Documents</span>
                </div>
                <i x-show="!sidebarCollapsed" x-cloak class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse x-cloak class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-4">
                @if (Route::has('sales_orders.documents.index'))
                    <a href="{{ route('sales_orders.documents.index') }}" class="sidebar-sublink {{ request()->routeIs('sales_orders.documents.*') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Sales Order Documents</span>
                    </a>
                @endif
                @if (Route::has('sales_invoice.documents.index'))
                    <a href="{{ route('sales_invoice.documents.index') }}" class="sidebar-sublink {{ request()->routeIs('sales_invoice.documents.*') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Sales Invoice Documents</span>
                    </a>
                @endif
                @if (Route::has('purchase_order.documents.index'))
                    <a href="{{ route('purchase_order.documents.index') }}" class="sidebar-sublink {{ request()->routeIs('purchase_order.documents.*') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Purchase Order Documents</span>
                    </a>
                @endif
                @if (Route::has('purchase_invoice.documents.index'))
                    <a href="{{ route('purchase_invoice.documents.index') }}" class="sidebar-sublink {{ request()->routeIs('purchase_invoice.documents.*') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Purchase Invoice Documents</span>
                    </a>
                @endif
                @can('taxes.access')
                    @if (Route::has('taxes.index'))
                        <a href="{{ route('taxes.index') }}" class="sidebar-sublink {{ request()->routeIs('taxes.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Taxes</span>
                        </a>
                    @endif
                @endcan
                @can('rekening.access')
                    @if (Route::has('rekening.index'))
                        <a href="{{ route('rekening.index') }}" class="sidebar-sublink {{ request()->routeIs('rekening.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Rekening Bank</span>
                        </a>
                    @endif
                @endcan
                @can('transaksi_wahana.access')
                    @if (Route::has('transaksi_wahana.index'))
                        <a href="{{ route('transaksi_wahana.index') }}" class="sidebar-sublink {{ request()->routeIs('transaksi_wahana.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Transaksi Wahana</span>
                        </a>
                    @endif
                @endcan
            </div>
        </div>

        <!-- 14. MAINTENANCE -->
        @php
            $isMaintActive = request()->routeIs(['accounting.start_new_year', 'activity_log.*']);
        @endphp
        <div x-data="{ open: @json($isMaintActive) || isSectionOpen('maintenance') }" class="space-y-1">
            <button @click="open = !open; toggleSection('maintenance', open)" 
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all duration-150 group active:scale-[0.96]"
                    :class="{ 'bg-slate-800/50 text-white': open || {{ $isMaintActive ? 'true' : 'false' }} }"
                    :title="sidebarCollapsed ? 'Maintenance' : ''">
                <div class="flex items-center min-w-0">
                    <i class="fas fa-wrench w-5 text-center text-sm mr-3 shrink-0 text-slate-400 group-hover:text-white"></i>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Maintenance</span>
                </div>
                <i x-show="!sidebarCollapsed" x-cloak class="fas fa-chevron-right text-[10px] text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>
            <div x-show="open && !sidebarCollapsed" x-collapse x-cloak class="pl-4 pr-1 py-1 space-y-1 border-l-2 border-slate-800 ml-4">
                @if (Route::has('accounting.start_new_year'))
                    <a href="{{ route('accounting.start_new_year') }}" class="sidebar-sublink {{ request()->routeIs('accounting.start_new_year') ? 'active-sublink' : '' }}">
                        <i class="fas fa-circle-dot sublink-dot"></i><span>Start New Year</span>
                    </a>
                @endif
                @can('log_activity.access')
                    @if (Route::has('activity_log.index'))
                        <a href="{{ route('activity_log.index') }}" class="sidebar-sublink {{ request()->routeIs('activity_log.*') ? 'active-sublink' : '' }}">
                            <i class="fas fa-circle-dot sublink-dot"></i><span>Log Activity</span>
                        </a>
                    @endif
                @endcan
            </div>
        </div>

    </nav>

    <!-- Bottom User Section in Sidebar -->
    <div class="p-3 border-t border-slate-800/80 bg-slate-950/40 shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3 overflow-hidden">
                <div class="h-9 w-9 rounded-xl bg-slate-800 border border-slate-700/60 flex items-center justify-center text-slate-300 font-bold text-xs shrink-0 shadow-inner">
                    {{ strtoupper(substr(Auth::user() ? Auth::user()->name : 'G', 0, 2)) }}
                </div>
                <div x-show="!sidebarCollapsed" x-cloak class="flex flex-col min-w-0 transition-opacity duration-200">
                    <span class="text-xs font-semibold text-white truncate">{{ Auth::user() ? Auth::user()->name : 'Guest' }}</span>
                    <span class="text-[10px] text-slate-400 truncate">Dept: {{ $currentDept ?? '-' }}</span>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" x-show="!sidebarCollapsed" x-cloak class="shrink-0">
                @csrf
                <button type="submit" 
                        title="Logout" 
                        class="flex items-center justify-center h-8 w-8 rounded-lg text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 active:scale-[0.96] transition-all">
                    <i class="fas fa-power-off text-xs"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<style>
.custom-sidebar-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-sidebar-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-sidebar-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
    border-radius: 4px;
}
.custom-sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #475569;
}
.sidebar-sublink {
    display: flex;
    align-items: center;
    padding: 0.35rem 0.65rem;
    font-size: 0.75rem;
    line-height: 1rem;
    border-radius: 0.5rem;
    color: #94a3b8;
    transition: all 150ms cubic-bezier(0.2, 0, 0, 1);
}
.sidebar-sublink:hover {
    background-color: rgba(30, 41, 59, 0.7);
    color: #f8fafc;
    transform: translateX(2px);
}
.active-sublink {
    background-color: rgba(51, 65, 85, 0.9);
    color: #ffffff !important;
    font-weight: 600;
}
.sublink-dot {
    font-size: 6px;
    margin-right: 0.5rem;
    opacity: 0.7;
    flex-shrink: 0;
}
.active-sublink .sublink-dot {
    opacity: 1;
    color: #818cf8;
}
</style>

    <nav x-data="{ menuOpen: false }" x-cloak class="bg-white shadow-lg border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-full mx-auto px-3 sm:px-6 lg:px-8">
            <!-- Header: Logo + Toggle -->
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('storage/' . \App\Setting::get('logo', 'logo.jpg')) }}" alt="Logo"
                                class="h-10 w-10">
                            <span class="font-bold text-lg text-gray-700"></span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex md:items-center md:space-x-8">
                    <!-- Setup Dropdown -->
                    <div class="relative ml-1" x-data="{ open: false }">
                        <button @click="open = !open" @keydown.escape="open = false"
                            class="flex items-center px-2 py-2 text-gray-700 font-medium text-sm hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
                            :class="{ 'text-blue-600 bg-blue-50': open }">
                            Setup
                            <svg class="w-4 h-4 ml-1 transform transition-transform duration-200"
                                :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>


                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            @click.outside="open = false"
                            class="absolute left-2 mt-2 w-60 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">

                            <!-- Company Submenu -->
                            @can('company.access')
                                <div class="relative" x-data="{ subOpen: false }">
                                    <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                        class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                        <span class="text-gray-700 group-hover:text-blue-600">Company</span>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div x-show="subOpen" x-cloak x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-x-1"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-1"
                                        class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                        @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                        @can('setting_setup.access')
                                            <a href="{{ route('setting.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Settings</a>
                                        @endcan
                                        @can('company_profile.access')
                                            <a href="{{ $company ? route('company_profile.show', $company->id) : route('company_profile.create') }}"
                                                @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Company
                                                Profile</a>
                                        @endcan
                                        @can('taxpayers_company.access')
                                            <a href="{{ $taxpayers ? route('taxpayers_company.show', $taxpayers->id) : route('taxpayers_company.create') }}"
                                                @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Taxpayer
                                                Profile</a>
                                        @endcan
                                    </div>
                                </div>
                            @endcan


                            <!-- General Submenu -->
                            @can('general.access')
                                <div class="relative" x-data="{ subOpen: false }">
                                    <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                        class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                        <span class="text-gray-700 group-hover:text-blue-600">General</span>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-x-1"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-1"
                                        class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                        @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                        @can('year_book.access')
                                            <a href="{{ route('start_new_year.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Year
                                                Book</a>
                                        @endcan
                                        @can('numbering.access')
                                            <a href="{{ route('numbering_account.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Numbering</a>
                                        @endcan
                                        @can('klasifikasi_akun.access')
                                            <a href="{{ route('klasifikasiAkun.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Account
                                                Classification</a>
                                        @endcan
                                        @can('chart_of_account.access')
                                            <a href="{{ route('chartOfAccount.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Accounts</a>
                                        @endcan
                                        @can('departement.access')
                                            @if ($currentDept === 'Accounting')
                                                <a href="{{ route('departemen.index') }}" @click="open = false"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Departments
                                                </a>
                                            @elseif ($currentDept === '-' || is_null($currentDept))
                                                <a href="{{ route('setting_departement.edit') }}" @click="open = false"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Departments
                                                </a>
                                            @else
                                                <a href="#"
                                                    onclick="alert('Menu ini hanya aktif saat mode departemen Accounting')"
                                                    @click="open = false"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Departments
                                                </a>
                                            @endif
                                        @endcan
                                        @can('linked_account_setup.access')
                                            <a href="{{ route('linkedAccount.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                                Account</a>
                                        @endcan
                                        @can('sales_taxes.access')
                                            <a href="{{ route('sales_taxes.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Taxes
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            @endcan

                            @can('reports_setup.access')
                                <!-- Reports Submenu -->
                                <div class="relative" x-data="{ subOpen: false }">
                                    <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                        class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                        <span class="text-gray-700 group-hover:text-blue-600">Reports</span>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-x-1"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-1"
                                        class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                        @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                        @can('report_account.access')
                                            <a href="{{ route('report.account') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Account
                                                List</a>
                                        @endcan
                                        @can('report_klasifikasi_akun.access')
                                            <a href="{{ route('report.klasifikasi') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Account
                                                Classification List</a>
                                        @endcan
                                        @can('report_departemen_account.access')
                                            <a href="{{ route('report.departemen-akun') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Department
                                                Accounts List</a>
                                        @endcan
                                        <a href="{{ route('report.sales_taxes') }}" @click="open = false"
                                            class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                            Taxes List</a>
                                    </div>
                                </div>
                            @endcan

                            <a href="{{ route('users.index') }}" @click="open = false"
                                class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                Users & Roles
                            </a>
                        </div>
                    </div>


                    <!-- Sales Dropdown -->
                    @can('sales.access')
                        <div class="relative ml-1" x-data="{ open: false }">
                            <button @click="open = !open" @keydown.escape="open = false"
                                class="flex items-center px-2 py-2 text-gray-700 font-medium text-sm hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
                                :class="{ 'text-blue-600 bg-blue-50': open }">
                                Sales
                                <svg class="w-4 h-4 ml-1 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" @click.outside="open = false"
                                class="absolute left-2 mt-2 w-64 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">

                                <!-- Company Submenu -->
                                @can('setup_sales.access')
                                    <div class="relative" x-data="{ subOpen: false }">
                                        <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                            class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                            <span class="text-gray-700 group-hover:text-blue-600">Setup</span>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-x-1"
                                            x-transition:enter-end="opacity-100 translate-x-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-x-0"
                                            x-transition:leave-end="opacity-0 translate-x-1"
                                            class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                            @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                            @can('linked_account_sales.access')
                                                <a href="{{ route('linkedAccountSales.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                                    Accounts</a>
                                            @endcan

                                            @can('option_sales.access')
                                                <a href="{{ route('sales_option.create') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Options
                                                </a>
                                            @endcan

                                            @can('sales_discount.access')
                                                <a href="{{ route('sales_discount.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Sales Discount
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan

                                <!-- General Submenu -->
                                @can('data.access')
                                    <div class="relative" x-data="{ subOpen: false }">
                                        <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                            class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                            <span class="text-gray-700 group-hover:text-blue-600">Data</span>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-x-1"
                                            x-transition:enter-end="opacity-100 translate-x-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-x-0"
                                            x-transition:leave-end="opacity-0 translate-x-1"
                                            class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                            @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                            {{-- <a href="{{ route('item_category.index') }}"
                                        class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Item
                                        Category</a>
                                        <a href="{{ route('items.index') }}"
                                            class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Items
                                        </a> --}}
                                            @can('payment_method.access')
                                                <a href="{{ route('PaymentMethod.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Payment
                                                    Method</a>
                                            @endcan
                                            @can('customers.access')
                                                <a href="{{ route('customers.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Customers</a>
                                            @endcan
                                            @can('sales_person.access')
                                                <a href="{{ route('sales_person.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Person
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan

                                @can('sales.access')
                                    <div class="relative" x-data="{ subOpen: false }">
                                        <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                            class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                            <span class="text-gray-700 group-hover:text-blue-600">Sales</span>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-x-1"
                                            x-transition:enter-end="opacity-100 translate-x-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-x-0"
                                            x-transition:leave-end="opacity-0 translate-x-1"
                                            class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                            @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                            @can('sales_orders.access')
                                                <a href="{{ route('sales_order.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Orders</a>
                                            @endcan
                                            @can('sales_invoice.access')
                                                <a href="{{ route('sales_invoice.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Invoices
                                                </a>
                                            @endcan


                                            @can('deposits.access')
                                                <a href="{{ route('sales_deposits.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Deposits</a>
                                            @endcan
                                            @can('receipts.access')
                                                <a href="{{ route('receipts.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Receipts</a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan

                                <div class="relative" x-data="{ subOpen: false }">
                                    <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                        class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                        <span class="text-gray-700 group-hover:text-blue-600">Report</span>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-x-1"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-1"
                                        class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                        @mouseenter="subOpen = true" @mouseleave="subOpen = false">

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan

                    {{-- Purchases dropdown --}}

                    @can('purchase.access')
                        <div class="relative ml-1" x-data="{ open: false }">
                            <button @click="open = !open" @keydown.escape="open = false"
                                class="flex items-center px-2 py-2 text-gray-700 font-medium text-sm text-sm hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
                                :class="{ 'text-blue-600 bg-blue-50': open }">
                                Purchases
                                <svg class="w-4 h-4 ml-1 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" @click.outside="open = false"
                                class="absolute left-0 mt-2 w-64 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">

                                <!-- Company Submenu -->
                                @can('setup_purchase.access')
                                    <div class="relative" x-data="{ subOpen: false }">
                                        <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                            class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                            <span class="text-gray-700 group-hover:text-blue-600">Setup</span>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-x-1"
                                            x-transition:enter-end="opacity-100 translate-x-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-x-0"
                                            x-transition:leave-end="opacity-0 translate-x-1"
                                            class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                            @mouseenter="subOpen = true" @mouseleave="subOpen = false">

                                            @can('linked_account_purchase.access')
                                                <a href="{{ route('linkedAccountPurchases.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                                    Account
                                                </a>
                                            @endcan
                                            @can('option_purchase.access')
                                                <a href="{{ route('purchases_options.create') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Options
                                                </a>
                                            @endcan

                                        </div>
                                    </div>
                                @endcan

                                @can('data.access')
                                    <div class="relative" x-data="{ subOpen: false }">
                                        <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                            class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                            <span class="text-gray-700 group-hover:text-blue-600">Data</span>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-x-1"
                                            x-transition:enter-end="opacity-100 translate-x-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-x-0"
                                            x-transition:leave-end="opacity-0 translate-x-1"
                                            class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                            @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                            {{-- <a href="{{ route('item_category.index') }}"
                                        class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Item
                                        Category</a>
                                        <a href="{{ route('items.index') }}"
                                            class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Items
                                        </a> --}}
                                            @can('payment_method.access')
                                                <a href="{{ route('PaymentMethod.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Payment
                                                    Method</a>
                                            @endcan
                                            @can('vendor.access')
                                                <a href="{{ route('vendors.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Vendors</a>
                                            @endcan

                                        </div>
                                    </div>
                                @endcan

                                @can('purchase.access')
                                    <div class="relative" x-data="{ subOpen: false }">
                                        <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                            class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                            <span class="text-gray-700 group-hover:text-blue-600">Purchases</span>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-x-1"
                                            x-transition:enter-end="opacity-100 translate-x-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-x-0"
                                            x-transition:leave-end="opacity-0 translate-x-1"
                                            class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                            @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                            @can('purchase_order.access')
                                                <a href="{{ route('purchase_order.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchases
                                                    Orders
                                                </a>
                                            @endcan

                                            @can('purchase_invoice.access')
                                                <a href="{{ route('purchase_invoice.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchase
                                                    Invoice
                                                </a>
                                            @endcan

                                            @can('prepayment_purchase.access')
                                                <a href="{{ route('prepayment.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Prepayments
                                                </a>
                                            @endcan

                                            @can('payment_purchase.access')
                                                <a href="{{ route('payment.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Payments
                                                </a>
                                            @endcan

                                            @can('payment_expense.access')
                                                <a href="{{ route('payment_expense.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Payment
                                                    Expense / Kas Keluar
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan
                                <div class="relative" x-data="{ subOpen: false }">
                                    <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                        class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                        <span class="text-gray-700 group-hover:text-blue-600">Report</span>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-x-1"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-1"
                                        class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                        @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan

                    {{-- inventory dropdown --}}
                    @can('inventory.access')
                        <div class="relative ml-1" x-data="{ open: false }">
                            <button @click="open = !open" @keydown.escape="open = false"
                                class="flex items-center px-2 py-2 text-gray-700 font-medium text-sm text-sm hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
                                :class="{ 'text-blue-600 bg-blue-50': open }">
                                Inventory
                                <svg class="w-4 h-4 ml-1 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>


                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" @click.outside="open = false"
                                class="absolute left-0 mt-2 w-64 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">

                                <div class="relative" x-data="{ subOpen: false }">
                                    <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                        class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                        <span class="text-gray-700 group-hover:text-blue-600">Setup</span>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-x-1"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-1"
                                        class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                        @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                        {{-- <a href="{{ route('item_category.index') }}"
                                        class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Item
                                        Category</a>
                                    <a href="{{ route('items.index') }}"
                                        class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Items
                                    </a> --}}
                                        @can('options_inventory.access')
                                            <a href="{{ $options_inventory ? route('options_inventory.edit', $options_inventory->id) : route('options_inventory.create') }}"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Options</a>
                                        @endcan

                                        @can('price_list_inventory.access')
                                            <a href="{{ route('price_list_inventory.index') }}"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Price List</a>
                                        @endcan

                                        @can('lokasi_inventory.access')
                                            <a href="{{ route('lokasi_inventory.index') }}"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Locations</a>
                                        @endcan

                                        @can('kategori_inventory.access')
                                            <a href="{{ route('item_category.index') }}"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Categories</a>
                                        @endcan

                                    </div>
                                </div>

                                @can('inventory.access')
                                    <a href="{{ route('inventory.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Inventory
                                        & Services
                                    </a>
                                @endcan

                                @can('Build from Bom.access')
                                    <a href="{{ route('build_of_bom.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Build
                                        From BOM
                                    </a>
                                @endcan

                                @can('Build from item assembly.access')
                                    <a href="{{ route('item_assembly.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Build
                                        From Item Assembly
                                    </a>
                                @endcan

                                @can('Transfer inventory.access')
                                    <a href="{{ route('transfer_inventory.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Transfer
                                        Inventory
                                    </a>
                                @endcan
                                <div class="relative" x-data="{ subOpen: false }">
                                    <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                        class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                        <span class="text-gray-700 group-hover:text-blue-600">Report</span>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-x-1"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-1"
                                        class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                        @mouseenter="subOpen = true" @mouseleave="subOpen = false">


                                    </div>
                                </div>
                            </div>


                        </div>
                    @endcan



                    {{-- budgeting dropdown --}}
                    @can('budgeting.access')
                        <div class="relative ml-1" x-data="{ open: false }">
                            <button @click="open = !open" @keydown.escape="open = false"
                                class="flex items-center px-2 py-2 text-gray-700 font-medium text-sm text-sm hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
                                :class="{ 'text-blue-600 bg-blue-50': open }">
                                Budgeting
                                <svg class="w-4 h-4 ml-1 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" @click.outside="open = false"
                                class="absolute left-0 mt-2 w-64 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">
                                <div class="relative" x-data="{ subOpen: false }">
                                    <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                        class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                        <span class="text-gray-700 group-hover:text-blue-600">Data</span>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-x-1"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-1"
                                        class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                        @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                        @can('approval_step.access')
                                            <a href="{{ route('approval_step.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Approval
                                                Step</a>
                                        @endcan
                                        @can('rekening.access')
                                            <a href="{{ route('rekening.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Rekening
                                            </a>
                                        @endcan

                                    </div>
                                </div>

                                @can('create_budget.access')
                                    <a href="{{ route('pengajuan.create') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                        Budget
                                    </a>
                                @endcan

                                @can('create_budget.access')
                                    <a href="{{ route('pengajuan.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                        Budget
                                    </a>
                                @endcan

                                @can('budget_submission.access')
                                    <a href="{{ route('pengajuan.approval.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Budget
                                        Submission

                                    </a>
                                @endcan
                                @can('budget_disbursement.access')
                                    <a href="{{ route('intangible_asset.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Budget
                                        Disbursement

                                    </a>
                                @endcan
                                @can('budget_realization.access')
                                    <a href="{{ route('intangible_asset.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Budget
                                        Realization

                                    </a>
                                @endcan

                            </div>
                        </div>
                    @endcan

                    <!-- Closing Harian Dropdown -->
                    @can('closing_harian.access')
                        <div class="relative ml-1" x-data="{ open: false }">
                            <button @click="open = !open" @keydown.escape="open = false"
                                class="flex items-center px-2 py-2 text-gray-700 font-medium text-sm text-sm hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
                                :class="{ 'text-blue-600 bg-blue-50': open }">
                                Closing Harian
                                <svg class="w-4 h-4 ml-1 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>


                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" @click.outside="open = false"
                                class="absolute left-0 mt-2 w-64 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">

                                <div class="relative" x-data="{ subOpen: false }">
                                    <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                        class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                        <span class="text-gray-700 group-hover:text-blue-600">Setup</span>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-x-1"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-1"
                                        class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                        @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                        {{-- <a href="{{ route('item_category.index') }}"
                                        class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Item
                                        Category</a>
                                    <a href="{{ route('items.index') }}"
                                        class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Items
                                    </a> --}}
                                        @can('linked_account_closing.access')
                                            <a href="{{ route('linkedAccount_closing.index') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Linked Account </a>
                                        @endcan

                                        @can('wahana.access')
                                            <a href="{{ route('wahana.index') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Wahana(Rides)
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                                <div class="relative" x-data="{ subOpen: false }">
                                    <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                        class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                        <span class="text-gray-700 group-hover:text-blue-600">Process</span>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-x-1"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-1"
                                        class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                        @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                        {{-- <a href="{{ route('item_category.index') }}"
                                        class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Item
                                        Category</a>
                                    <a href="{{ route('items.index') }}"
                                        class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Items
                                    </a> --}}
                                        @can('closing_harian.create')
                                            <a href="{{ route('closing_harian.create') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                Closing Harian </a>
                                        @endcan
                                        @can('closing_harian.access')
                                            <a href="{{ route('closing_harian.index') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                                Closing Harian</a>
                                        @endcan
                                        @can('rekonsiliasi_closing.access')
                                            <a href="{{ route('rekonsiliasi_closing.index') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Rekonsiliasi Closing vs Kas/Bank</a>
                                        @endcan
                                    </div>
                                </div>


                                <!-- Divider -->
                                <div class="my-1 border-t border-gray-100"></div>
                            </div>
                        </div>
                    @endcan

                    {{-- payroll dropdown --}}
                    @can('payroll.access')
                        <div class="relative ml-1" x-data="{ open: false }">
                            <button @click="open = !open" @keydown.escape="open = false"
                                class="flex items-center px-2 py-2 text-gray-700 font-medium text-sm text-sm hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
                                :class="{ 'text-blue-600 bg-blue-50': open }">
                                Payroll
                                <svg class="w-4 h-4 ml-1 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" @click.outside="open = false"
                                class="absolute left-2 mt-2 w-64 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">

                                <!-- Company Submenu -->
                                @can('setup_payroll.access')
                                    <div class="relative" x-data="{ subOpen: false }">
                                        <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                            class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                            <span class="text-gray-700 group-hover:text-blue-600">Setup</span>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-x-1"
                                            x-transition:enter-end="opacity-100 translate-x-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-x-0"
                                            x-transition:leave-end="opacity-0 translate-x-1"
                                            class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                            @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                            @can('level_karyawan.access')
                                                <a href="{{ route('LevelKaryawan.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Employee
                                                    Level</a>
                                            @endcan
                                            @can('jabatan.access')
                                                <a href="{{ route('jabatan.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Position
                                                </a>
                                            @endcan
                                            @can('komponen_penghasilan.access')
                                                <a href="{{ route('komponen_penghasilan.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Income
                                                    Components By Level
                                                </a>
                                            @endcan
                                            @can('group_unit.access')
                                                <a href="{{ route('group_unit.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Group
                                                    Unit
                                                </a>
                                            @endcan
                                            @can('unit.access')
                                                <a href="{{ route('unit_kerja.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Units/Departemens
                                                </a>
                                            @endcan
                                            @can('wahana.access')
                                                <a href="{{ route('wahana.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Wahana(Rides)
                                                </a>
                                            @endcan
                                            @can('ptkp.access')
                                                <a href="{{ route('ptkp.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">PTKP

                                                </a>
                                            @endcan
                                            @can('employee.access')
                                                <a href="{{ route('employee.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Employee Profiles
                                                </a>
                                            @endcan
                                            @can('komposisi_gaji.access')
                                                <a href="{{ route('komposisi_gaji.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Income Components By Employee
                                                </a>
                                            @endcan
                                            @can('jenis_hari.access')
                                                <a href="{{ route('jenis_hari.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Type
                                                    Of Days

                                                </a>
                                            @endcan
                                            @can('jam_kerja.access')
                                                <a href="{{ route('jam_kerja.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Working
                                                    Hours
                                                </a>
                                            @endcan
                                            @can('target_unit.access')
                                                <a href="{{ route('target_unit.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Target
                                                    By Unit
                                                </a>
                                            @endcan

                                            @can('target_wahana.access')
                                                <a href="{{ route('target_wahana.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Target
                                                    By Wahana
                                                </a>
                                            @endcan


                                            @can('crew_shift_karyawan.access')
                                                <a href="{{ route('crew_shift_karyawan.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Crew
                                                    Scheduling

                                                </a>
                                            @endcan
                                            @can('shift_karyawan.access')
                                                <a href="{{ route('shift_karyawan.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Personnel
                                                    Scheduling

                                                </a>
                                            @endcan

                                            @can('transaksi_wahana.access')
                                                <a href="{{ route('transaksi_wahana.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Input
                                                    Sales Achievement

                                                </a>
                                            @endcan
                                            @can('bonus_karyawan.access')
                                                <a href="{{ route('bonus_karyawan.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Employee
                                                    Bonus Update
                                                </a>
                                            @endcan
                                            @can('tax_rates.access')
                                                <a href="{{ route('tax_rates.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">TER
                                                    (Tax Rates)
                                                </a>
                                            @endcan


                                        </div>
                                    </div>
                                @endcan


                                <!-- Reports Submenu -->
                                @can('process_payroll.access')
                                    <div class="relative" x-data="{ subOpen: false }">
                                        <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                            class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                            <span class="text-gray-700 group-hover:text-blue-600">Process</span>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-x-1"
                                            x-transition:enter-end="opacity-100 translate-x-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-x-0"
                                            x-transition:leave-end="opacity-0 translate-x-1"
                                            class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                            @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                            @can('pembayaran_gaji.access')
                                                <a href="{{ route('pembayaran_gaji.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                    Salary Calculations Staff
                                                </a>
                                            @endcan

                                            @can('pembayaran_gaji_nonstaff.access')
                                                <a href="{{ route('pembayaran_gaji_nonstaff.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                    Salary Calculations Non Staff
                                                </a>
                                            @endcan

                                            @can('slip.access')
                                                <a href="{{ route('slip.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Salary Slip Staff</a>
                                            @endcan

                                            @can('slip_gaji_nonstaff.access')
                                                <a href="{{ route('slip.nonStaff.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Salary Slip Non Staff</a>
                                            @endcan

                                            @can('absensi.access')
                                                <a href="{{ route('absensi.form') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Absensi
                                                    Pegawai
                                                </a>
                                            @endcan

                                        </div>
                                    </div>
                                @endcan

                                @can('report_payroll.access')
                                    <div class="relative" x-data="{ subOpen: false }">
                                        <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                            class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                            <span class="text-gray-700 group-hover:text-blue-600">Report</span>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-x-1"
                                            x-transition:enter-end="opacity-100 translate-x-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-x-0"
                                            x-transition:leave-end="opacity-0 translate-x-1"
                                            class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                            @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                            @can('rekap_absensi.access')
                                                <a href="{{ route('report.absensi.filter') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Rekap Absensi Pegawai
                                                </a>
                                            @endcan
                                            @can('rekap_target_wahana.access')
                                                <a href="{{ route('report.target_wahana.filter') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Rekap Target Wahana
                                                </a>
                                            @endcan

                                        </div>
                                    </div>
                                @endcan

                            </div>
                        </div>
                    @endcan


                    {{-- asset --}}
                    @can('asset.access')
                        <div class="relative ml-1" x-data="{ open: false }">
                            <button @click="open = !open" @keydown.escape="open = false"
                                class="flex items-center px-2 py-2 text-gray-700 font-medium text-sm text-sm hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
                                :class="{ 'text-blue-600 bg-blue-50': open }">
                                Asset
                                <svg class="w-4 h-4 ml-1 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" @click.outside="open = false"
                                class="absolute left-2 mt-2 w-64 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">

                                <!-- setup asset Submenu -->
                                @can('setup_asset.access')
                                    <div class="relative" x-data="{ subOpen: false }">
                                        <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                            class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                            <span class="text-gray-700 group-hover:text-blue-600">Setup</span>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-x-1"
                                            x-transition:enter-end="opacity-100 translate-x-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-x-0"
                                            x-transition:leave-end="opacity-0 translate-x-1"
                                            class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                            @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                            <a href="{{ route('linkedAccountAsset.index') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                                Account
                                                Asset
                                            </a>
                                        </div>
                                    </div>
                                @endcan
                                @can('setup_asset.access')
                                    <div class="relative" x-data="{ subOpen: false }">
                                        <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                            class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                            <span class="text-gray-700 group-hover:text-blue-600">Data</span>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-x-1"
                                            x-transition:enter-end="opacity-100 translate-x-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-x-0"
                                            x-transition:leave-end="opacity-0 translate-x-1"
                                            class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                            @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                            @can('kategori_asset.access')
                                                <a href="{{ route('kategori_asset.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Category
                                                    Asset
                                                </a>
                                            @endcan
                                            @can('lokasi_asset.access')
                                                <a href="{{ route('lokasi.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Location
                                                    Asset
                                                </a>
                                            @endcan

                                            @can('masa_manfaat.access')
                                                <a href="{{ route('masa_manfaat.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Masa
                                                    Manfaat

                                                </a>
                                            @endcan
                                            @can('metode_penyusutan.access')
                                                <a href="{{ route('metode_penyusutan.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Metode
                                                    Penyusutan
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan


                                <!-- Tangible Asset -->
                                @can('tangible_asset.access')
                                    <a href="{{ route('tangible_asset.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Tangible
                                        Asset
                                    </a>
                                @endcan

                                @can('intangible_asset.access')
                                    <a href="{{ route('intangible_asset.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Intangible
                                        Asset
                                    </a>
                                @endcan

                                @can('monthly_process.access')
                                    <a href=""
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Monthly
                                        Process
                                    </a>
                                @endcan
                                <div class="relative" x-data="{ subOpen: false }">
                                    <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                        class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                        <span class="text-gray-700 group-hover:text-blue-600">Report</span>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-x-1"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-1"
                                        class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                        @mouseenter="subOpen = true" @mouseleave="subOpen = false">


                                    </div>
                                </div>


                                {{-- <!-- Reports Submenu -->
                            <div class="relative" x-data="{ subOpen: false }">
                                <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                    class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                    <span class="text-gray-700 group-hover:text-blue-600">Reports</span>
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                                <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-x-1"
                                    x-transition:enter-end="opacity-100 translate-x-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-x-0"
                                    x-transition:leave-end="opacity-0 translate-x-1"
                                    class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                    @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                    <a href=""
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Assets
                                        List</a>
                                    <a href=""
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                        Depreciation
                                        List</a>

                                </div>
                            </div> --}}
                            </div>
                        </div>
                    @endcan


                    {{-- specpose dropdown --}}

                    @can('specpose.access')
                        <div class="relative ml-1" x-data="{ open: false }">
                            <button @click="open = !open" @keydown.escape="open = false"
                                class="flex items-center px-2 py-2 text-gray-700 font-medium text-sm text-sm hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
                                :class="{ 'text-blue-600 bg-blue-50': open }">
                                Specpose
                                <svg class="w-4 h-4 ml-1 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" @click.outside="open = false"
                                class="absolute left-0 mt-2 w-64 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">

                                <!-- Project  Submenu -->
                                <div class="relative" x-data="{ subOpen: false }">
                                    <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                        class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                        <span class="text-gray-700 group-hover:text-blue-600">Setup</span>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-x-1"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-1"
                                        class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                        @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                        @can('specpose.create')
                                            <a href="{{ route('project.index') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                                Specpose</a>
                                        @endcan

                                        @can('specpose.create')
                                            <a href="{{ route('project.create') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                Specpose</a>
                                        @endcan

                                        @can('specpose.update')
                                            <a href="{{ route('project.edit_project') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Edit
                                                Specpose</a>
                                        @endcan

                                        @can('specpose.view')
                                            <a href="{{ route('project.view_project') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">View
                                                Specpose</a>
                                        @endcan
                                    </div>

                                    @can('report_specpose.access')
                                        <a href="#"
                                            class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                            Reports</a>
                                    @endcan
                                </div>

                                <!-- Divider -->
                                <div class="my-1 border-t border-gray-100"></div>
                            </div>
                        </div>
                    @endcan



                    <!-- Journal Entry Dropdown -->
                    @can('journal_entry.access')
                        <div class="relative ml-1" x-data="{ open: false }">
                            <button @click="open = !open" @keydown.escape="open = false"
                                class="flex items-center px-2 py-2 text-gray-700 font-medium text-sm text-sm hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
                                :class="{ 'text-blue-600 bg-blue-50': open }">
                                General Journal
                                <svg class="w-4 h-4 ml-1 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" @click.outside="open = false"
                                class="absolute left-0 mt-2 w-64 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">

                                @can('journal_entry.create')
                                    <a href="{{ route('journal_entry.create') }}"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                        General Journal </a>
                                @endcan
                                @can('journal_entry.data')
                                    <a href="{{ route('journal_entry.index') }}"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                        General Journal Entry</a>
                                @endcan
                                @can('journal_entry.view')
                                    <a href="{{ route('journal_entry.view_journal_entry') }}"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">View
                                        General Journal Entry</a>
                                @endcan
                                @can('journal_entry.update')
                                    <a href="{{ route('journal_entry.filter_journal_entry') }}"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Edit
                                        General Journal Entry</a>
                                @endcan
                                @can('rekonsiliasi_closing.access')
                                    <a href="{{ route('rekonsiliasi_closing.index') }}"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Rekonsiliasi Closing vs Kas/Bank</a>
                                @endcan
                                @can('unassigned_watchdog.access')
                                    <a href="{{ route('unassigned_watchdog.index') }}"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Unassigned Watchdog (Audit Non-Dept)</a>
                                @endcan
                                @can('assigned_watchdog.access')
                                    <a href="{{ route('assigned_watchdog.index') }}"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Assigned Watchdog (Audit Departemen)</a>
                                @endcan
                                @can('journal_entry.access')
                                    <a href="{{ route('journal_audit.index') }}"
                                        class="block px-4 py-2 text-indigo-700 font-medium hover:bg-indigo-50 hover:text-indigo-900 transition-colors duration-150">
                                        <i class="fas fa-scale-balanced mr-1 text-indigo-500"></i> Audit &amp; Kamus Akun Jurnal
                                    </a>
                                @endcan



                                <!-- Divider -->
                                <div class="my-1 border-t border-gray-100"></div>
                            </div>
                        </div>
                    @endcan

                    @can('report_menu.access')
                        <div class="relative ml-1" x-data="{ open: false }">
                            <button @click="open = !open" @keydown.escape="open = false"
                                class="flex items-center px-3 py-2 text-gray-700 font-medium hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
                                :class="{ 'text-blue-600 bg-blue-50': open }">
                                Report
                                <svg class="w-4 h-4 ml-1 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" @click.outside="open = false"
                                class="absolute right-0 mt-2 w-64 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">
                                @can('buku_besar.access')
                                    <a href="{{ route('buku_besar.filter_buku_besar') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Buku
                                        Besar</a>
                                @endcan

                                @can('trial_balance.access')
                                    <a href="{{ route('trial_balance.filter_trial_balance') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Trial
                                        Balance</a>
                                @endcan
                                @can('neraca.access')
                                    <a href="{{ route('neraca.filter_neraca') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Neraca
                                    </a>
                                @endcan
                                @can('neraca_departemen.access')
                                    <a href="{{ route('neraca_departemen.filter_neraca_departemen') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Balance Sheet Departemen
                                    </a>
                                @endcan

                                @can('income_statement.access')
                                    <a href="{{ route('income_statement.filter_income_statement') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Income
                                        Statement</a>
                                @endcan

                                @can('income_statement_departement.access')
                                    <a href="{{ route('income_statement.filter_income_statement_departement') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Income
                                        Statement Department</a>
                                @endcan
                                @can('arus_kas.access')
                                    <a href="{{ route('arus_kas.filter_arus_kas') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Cash
                                        Flow
                                    </a>
                                @endcan
                                @can('rekonsiliasi_closing.access')
                                    <a href="{{ route('rekonsiliasi_closing.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Rekonsiliasi Closing vs Kas/Bank
                                    </a>
                                @endcan
                                @can('unassigned_watchdog.access')
                                    <a href="{{ route('unassigned_watchdog.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Unassigned Watchdog (Audit Non-Dept)
                                    </a>
                                @endcan
                                @can('assigned_watchdog.access')
                                    <a href="{{ route('assigned_watchdog.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Assigned Watchdog (Audit Departemen)
                                    </a>
                                @endcan
                                @can('journal_entry.access')
                                    <a href="{{ route('journal_audit.index') }}"
                                        class="block px-2 py-1 text-indigo-700 font-medium hover:bg-indigo-50 hover:text-indigo-900 transition-colors duration-150">
                                        <i class="fas fa-scale-balanced mr-1 text-indigo-500"></i> Audit &amp; Kamus Akun Jurnal
                                    </a>
                                @endcan

                            </div>
                        </div>
                    @endcan

                    <!-- Report Dropdown -->
                    @can('fiscal.access')
                        <div class="relative ml-1" x-data="{ open: false }">
                            <button @click="open = !open" @keydown.escape="open = false"
                                class="flex items-center px-3 py-2 text-gray-700 font-medium hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
                                :class="{ 'text-blue-600 bg-blue-50': open }">
                                Fisrec
                                <svg class="w-4 h-4 ml-1 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>



                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" @click.outside="open = false"
                                class="absolute right-0 mt-2 w-64 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">
                                <div class="relative" x-data="{ subOpen: false }">
                                    <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                        class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                        <span class="text-gray-700 group-hover:text-blue-600">Setup</span>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-x-1"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-1"
                                        class="absolute left-full top-0 ml-1 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                        @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                        @can('fiscal_account.create')
                                            <a href="{{ route('fiscal_account.index') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Fiscal
                                                Account
                                            </a>
                                        @endcan

                                        @can('fiscal_account_persamaan.create')
                                            <a href="{{ route('fiscal_account_persamaan.index') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Fiscal
                                                Account
                                                Persamaan</a>
                                        @endcan
                                        @can('fiscal.access')
                                            <a href="{{ route('fiscal_setting.index') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Fiscal Settings (Opsi Fisrec)</a>
                                        @endcan
                                    </div>

                                </div>
                                @can('fiscal.access')
                                    <a href="{{ route('fiscal.fiscal_report') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Fiscal
                                        Reconciliation
                                    </a>
                                @endcan

                                @can('perhitungan_pajak_penghasilan.access')
                                    <a href="{{ route('perhitungan_pajak_penghasilan.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                        Perhitungan Pajak Penghasilan
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endcan
                    {{-- document menu  --}}
                    @can('documents.access')
                        <div class="relative ml-1" x-data="{ open: false }">
                            <button @click="open = !open" @keydown.escape="open = false"
                                class="flex items-center px-3 py-2 text-gray-700 font-medium hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
                                :class="{ 'text-blue-600 bg-blue-50': open }">
                                Documents
                                <svg class="w-4 h-4 ml-1 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" @click.outside="open = false"
                                class="absolute left-2 mt-2 w-60 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">

                                <!-- Company Submenu -->
                                @can('sales_document.access')
                                    <div class="relative" x-data="{ subOpen: false }">
                                        <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                            class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                            <span class="text-gray-700 group-hover:text-blue-600">Sales</span>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div x-show="subOpen" x-cloak x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-x-1"
                                            x-transition:enter-end="opacity-100 translate-x-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-x-0"
                                            x-transition:leave-end="opacity-0 translate-x-1"
                                            class="absolute left-full top-0 ml-0 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                            @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                            <a href="{{ route('sales_orders.documents.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                Order</a>
                                            <a href="{{ route('sales_invoice.documents.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                Invoice</a>
                                        </div>
                                    </div>
                                @endcan

                                <!-- General Submenu -->
                                @can('purchases_document.access')
                                    <div class="relative" x-data="{ subOpen: false }">
                                        <button @mouseenter="subOpen = true" @mouseleave="subOpen = false"
                                            class="w-full text-left px-2 py-1 hover:bg-blue-50 flex justify-between items-center group transition-colors duration-150">
                                            <span class="text-gray-700 group-hover:text-blue-600">Purchases</span>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div x-show="subOpen" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-x-1"
                                            x-transition:enter-end="opacity-100 translate-x-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-x-0"
                                            x-transition:leave-end="opacity-0 translate-x-1"
                                            class="absolute left-full top-0 ml-0 w-56 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50"
                                            @mouseenter="subOpen = true" @mouseleave="subOpen = false">
                                            <a href="{{ route('purchase_order.documents.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchase
                                                Order</a>
                                            <a href="{{ route('purchase_invoice.documents.index') }}"
                                                @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchase
                                                Invoice
                                            </a>
                                        </div>
                                    </div>
                                @endcan

                                @can('taxes.access')
                                    <a href="{{ route('taxes.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                        Taxes
                                    </a>
                                @endcan
                                {{-- end document menu  --}}
                            </div>
                        </div>
                    @endcan

                    <!-- Maintenance Dropdown -->
                    @can('maintenance.access')
                        <div class="relative ml-1" x-data="{ open: false }">
                            <button @click="open = !open" @keydown.escape="open = false"
                                class="flex items-center px-3 py-2 text-gray-700 font-medium hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
                                :class="{ 'text-blue-600 bg-blue-50': open }">
                                Maintenance
                                <svg class="w-4 h-4 ml-1 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" @click.outside="open = false"
                                class="absolute right-0 mt-2 w-64 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">
                                @can('start_new_year.access')
                                    <a href="{{ route('accounting.start_new_year') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Start
                                        New Year
                                    </a>
                                @endcan

                                @can('log_activity.access')
                                    <a href="{{ route('activity_log.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Log
                                        Activity
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endcan




                </div>
                @php
                    $user = Auth::check() ? Auth::user()->name : 'User';
                @endphp
                <!-- Profile Section -->
                <div class="hidden md:flex md:items-center">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <span class="text-gray-700 font-medium">Hi, {{ $user }}</span>
                            <div class="h-8 w-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <span class="text-gray-600 text-sm font-medium">A</span>
                            </div>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95" @click.outside="open = false"
                            class="absolute right-0 mt-2 w-48 bg-white shadow-xl rounded-lg border border-gray-200 py-2 z-50">
                            <a href="#"
                                class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Profile</a>
                            <div class="border-t border-gray-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 hover:bg-blue-100 rounded">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button @click="menuOpen = !menuOpen"
                        class="p-2 rounded-md text-gray-700 hover:text-blue-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div x-show="menuOpen" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="md:hidden border-t border-gray-200 bg-white">
                <div class="px-2 pt-2 pb-3 space-y-1">

                    <!-- Setup with Submenu -->
                    <div x-data="{ open: false }">
                        <button @click="open = !open"
                            class="w-full flex justify-between items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md focus:outline-none">
                            Setup
                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Level 1: Setup Submenu -->
                        <div x-show="open" x-collapse class="pl-4 space-y-1">

                            <!-- Company with Sub-submenu -->
                            @can('company.access')
                                <div x-data="{ subOpen: false }">
                                    <button @click="subOpen = !subOpen"
                                        class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                        Company
                                        <svg class="w-4 h-4 transform transition-transform duration-200"
                                            :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <!-- Level 2: Company Sub-submenu -->
                                    <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                        @can('setting_setup.access')
                                            <a href="{{ route('setting.index') }}"
                                                class="block px-3 py-2 text-sm text-gray-600 hover:bg-blue-100 rounded-md">Settings</a>
                                        @endcan
                                        @can('company_profile.access')
                                            <a href="{{ $company ? route('company_profile.show', $company->id) : route('company_profile.create') }}"
                                                class="block px-3 py-2 text-sm text-gray-600 hover:bg-blue-100 rounded-md">Company
                                                Profile</a>
                                        @endcan
                                        @can('taxpayers_company.access')
                                            <a href="{{ $taxpayers ? route('taxpayers_company.show', $taxpayers->id) : route('taxpayers_company.create') }}"
                                                class="block px-3 py-2 text-sm text-gray-600 hover:bg-blue-100 rounded-md">Taxpayer
                                                Profile</a>
                                        @endcan
                                    </div>
                                </div>
                            @endcan
                            {{-- general menu --}}
                            @can('general.access')
                                <div x-data="{ subOpen: false }">
                                    <button @click="subOpen = !subOpen"
                                        class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                        General
                                        <svg class="w-4 h-4 transform transition-transform duration-200"
                                            :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <!-- Level 2: Company Sub-submenu -->
                                    <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                        @can('year_book.access')
                                            <a href="{{ route('start_new_year.index') }}"
                                                class="block px-3 py-2 text-sm text-gray-600 hover:bg-blue-100 rounded-md">Year
                                                Book</a>
                                        @endcan
                                        @can('numbering_account.access')
                                            <a href="{{ route('klasifikasiAkun.index') }}"
                                                class="block px-3 py-2 text-sm text-gray-600 hover:bg-blue-100 rounded-md">Numbering

                                            </a>
                                        @endcan
                                        @can('klasifikasi_akun.access')
                                            <a href="{{ route('klasifikasiAkun.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Account
                                                Classification</a>
                                        @endcan
                                        @can('chart_of_account.access')
                                            <a href="{{ route('chartOfAccount.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Accounts</a>
                                        @endcan
                                        @can('departement.access')
                                            @if ($currentDept === 'Accounting')
                                                <a href="{{ route('departemen.index') }}" @click="open = false"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Departments
                                                </a>
                                            @elseif ($currentDept === '-' || is_null($currentDept))
                                                <a href="{{ route('setting_departement.edit') }}" @click="open = false"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Departments
                                                </a>
                                            @else
                                                <a href="#"
                                                    onclick="alert('Menu ini hanya aktif saat mode departemen Accounting')"
                                                    @click="open = false"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Departments
                                                </a>
                                            @endif
                                        @endcan
                                        @can('linked_account_setup.access')
                                            <a href="{{ route('linkedAccount.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                                Account</a>
                                        @endcan
                                        @can('sales_taxes.access')
                                            <a href="{{ route('sales_taxes.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Taxes
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            @endcan
                            {{-- report setup --}}
                            @can('reports_setup.access')
                                <div x-data="{ subOpen: false }">
                                    <button @click="subOpen = !subOpen"
                                        class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                        Reports
                                        <svg class="w-4 h-4 transform transition-transform duration-200"
                                            :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <!-- Level 2: Company Sub-submenu -->
                                    <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                        @can('report_account.access')
                                            <a href="{{ route('report.account') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Account
                                                List</a>
                                        @endcan
                                        @can('report_klasifikasi_akun.access')
                                            <a href="{{ route('report.klasifikasi') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Account
                                                Classification List</a>
                                        @endcan
                                        @can('report_departemen_account.access')
                                            <a href="{{ route('report.departemen-akun') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Department
                                                Accounts List</a>
                                        @endcan
                                    </div>
                                </div>
                            @endcan
                            <a href="{{ route('users.index') }}" @click="open = false"
                                class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                Users & Roles
                            </a>
                        </div>
                    </div>

                    {{-- sales mobile --}}

                    @can('sales.access')

                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex justify-between items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md focus:outline-none">
                                Sales
                                <svg class="w-4 h-4 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Level 1: Setup Submenu -->
                            <div x-show="open" x-collapse class="pl-4 space-y-1">

                                <!-- Company with Sub-submenu -->
                                @can('setup_sales.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            Setup
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            @can('linked_account_sales.access')
                                                <a href="{{ route('linkedAccountSales.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                                    Accounts</a>
                                            @endcan

                                            @can('option_sales.access')
                                                <a href="{{ route('sales_option.create') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Options
                                                </a>
                                            @endcan

                                            @can('sales_discount.access')
                                                <a href="{{ route('sales_discount.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Sales Discount
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan
                                {{-- general menu --}}
                                @can('data.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            Data
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            @can('payment_method.access')
                                                <a href="{{ route('PaymentMethod.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Payment
                                                    Method</a>
                                            @endcan
                                            @can('customers.access')
                                                <a href="{{ route('customers.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Customers</a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan
                                {{-- report setup --}}
                                @can('sales.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            Sales
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            @can('sales_orders.access')
                                                <a href="{{ route('sales_order.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Orders</a>
                                            @endcan
                                            @can('sales_invoice.access')
                                                <a href="{{ route('sales_invoice.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Invoices
                                                </a>
                                            @endcan

                                            @can('sales_person.access')
                                                <a href="{{ route('employee.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Person
                                                </a>
                                            @endcan
                                            @can('deposits.access')
                                                <a href="{{ route('sales_deposits.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Deposits</a>
                                            @endcan
                                            @can('receipts.access')
                                                <a href="{{ route('receipts.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Receipts</a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    @endcan

                    {{-- purchase --}}
                    @can('purchase.access')

                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex justify-between items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md focus:outline-none">
                                Purchases
                                <svg class="w-4 h-4 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Level 1: Setup Submenu -->
                            <div x-show="open" x-collapse class="pl-4 space-y-1">

                                <!-- Company with Sub-submenu -->
                                @can('setup_purchase.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            Setup
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            @can('option_purchase.access')
                                                <a href="{{ route('purchases_options.create') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Options
                                                </a>
                                            @endcan

                                            @can('linked_account_purchase.access')
                                                <a href="{{ route('linkedAccountPurchases.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                                    Account
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan
                                {{-- general menu --}}
                                @can('purchase.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            Purchases
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            @can('purchase_order.access')
                                                <a href="{{ route('purchase_order.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchases
                                                    Orders
                                                </a>
                                            @endcan

                                            @can('purchase_invoice.access')
                                                <a href="{{ route('purchase_invoice.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchase
                                                    Invoice
                                                </a>
                                            @endcan

                                            @can('prepayment_purchase.access')
                                                <a href="{{ route('prepayment.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Prepayments
                                                </a>
                                            @endcan

                                            @can('payment_purchase.access')
                                                <a href="{{ route('payment.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Payments
                                                </a>
                                            @endcan

                                            @can('payment_expense.access')
                                                <a href="{{ route('payment_expense.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Payment
                                                    Expense / Kas Keluar
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan
                                @can('vendor.access')
                                    <a href="{{ route('vendors.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Vendors

                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endcan


                    {{-- inventory --}}
                    @can('inventory.access')

                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex justify-between items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md focus:outline-none">
                                Inventory
                                <svg class="w-4 h-4 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Level 1: Setup Submenu -->
                            <div x-show="open" x-collapse class="pl-4 space-y-1">

                                <!-- Company with Sub-submenu -->
                                @can('setting_inventory.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            Setup
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            @can('options_inventory.access')
                                                <a href="{{ $options_inventory ? route('options_inventory.edit', $options_inventory->id) : route('options_inventory.create') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Options</a>
                                            @endcan

                                            @can('price_list_inventory.access')
                                                <a href="{{ route('price_list_inventory.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Price List</a>
                                            @endcan

                                            @can('lokasi_inventory.access')
                                                <a href="{{ route('lokasi_inventory.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Locations</a>
                                            @endcan

                                            @can('kategori_inventory.access')
                                                <a href="{{ route('item_category.index') }}"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Categories</a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan

                                @can('inventory.access')
                                    <a href="{{ route('inventory.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Inventory
                                        & Service
                                    </a>
                                @endcan

                                @can('Build from Bom.access')
                                    <a href="{{ route('build_of_bom.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Build
                                        From BOM
                                    </a>
                                @endcan

                                @can('Build from item assembly.access')
                                    <a href="{{ route('item_assembly.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Build
                                        From Item Assembly
                                    </a>
                                @endcan

                                @can('Transfer inventory.access')
                                    <a href=""
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Transfer
                                        Inventory
                                    </a>
                                @endcan

                            </div>
                        </div>
                    @endcan

                    {{-- budgeting --}}
                    @can('budgeting.access')

                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex justify-between items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md focus:outline-none">
                                Budgeting
                                <svg class="w-4 h-4 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Level 1: Setup Submenu -->
                            <div x-show="open" x-collapse class="pl-4 space-y-1">

                                <!-- Company with Sub-submenu -->
                                @can('budgeting.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            Budgeting
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            @can('create_budget.access')
                                                <a href="{{ route('pengajuan.create') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Budget

                                                </a>
                                            @endcan
                                            @can('create_budget.access')
                                                <a href="{{ route('pengajuan.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                                    Budget
                                                </a>
                                            @endcan
                                            @can('budget_submission.access')
                                                <a href="{{ route('intangible_asset.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Budget
                                                    Submission
                                                </a>
                                            @endcan
                                            @can('budget_disbursement.access')
                                                <a href="{{ route('intangible_asset.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Budget
                                                    Disbursement

                                                </a>
                                            @endcan
                                            @can('budget_realization.access')
                                                <a href="{{ route('intangible_asset.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Budget
                                                    Realization

                                                </a>
                                            @endcan

                                        </div>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    @endcan

                    @can('closing_harian.access')
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex justify-between items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md focus:outline-none">
                                Closing Harian
                                <svg class="w-4 h-4 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-collapse class="pl-4 space-y-1">
                                @can('linked_account_closing.access')
                                    <a href="{{ route('linkedAccount_closing.index') }}"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                        Account</a>
                                @endcan
                                @can('closing_harian.create')
                                    <a href="{{ route('closing_harian.create') }}"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                        Closing Harian</a>
                                @endcan
                                @can('closing_harian.access')
                                    <a href="{{ route('closing_harian.index') }}"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                        Closing Harian</a>
                                @endcan
                                @can('rekonsiliasi_closing.access')
                                    <a href="{{ route('rekonsiliasi_closing.index') }}"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Rekonsiliasi Closing vs Kas/Bank</a>
                                @endcan
                            </div>
                        </div>
                    @endcan

                    {{-- payroll --}}
                    @can('payroll.access')

                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex justify-between items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md focus:outline-none">
                                Payroll
                                <svg class="w-4 h-4 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Level 1: Setup Submenu -->
                            <div x-show="open" x-collapse class="pl-4 space-y-1">

                                <!-- Company with Sub-submenu -->
                                @can('setup_payroll.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            Setup
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            @can('level_karyawan.access')
                                                <a href="{{ route('LevelKaryawan.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Employee
                                                    Level</a>
                                            @endcan
                                            @can('jabatan.access')
                                                <a href="{{ route('jabatan.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Position
                                                </a>
                                            @endcan
                                            @can('komponen_penghasilan.access')
                                                <a href="{{ route('komponen_penghasilan.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Income
                                                    Components By Level
                                                </a>
                                            @endcan
                                            @can('unit.access')
                                                <a href="{{ route('unit_kerja.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Units/Departemens
                                                </a>
                                            @endcan
                                            @can('wahana.access')
                                                <a href="{{ route('wahana.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Wahana(Rides)
                                                </a>
                                            @endcan
                                            @can('ptkp.access')
                                                <a href="{{ route('ptkp.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">PTKP

                                                </a>
                                            @endcan
                                            @can('employee.access')
                                                <a href="{{ route('employee.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Employee Profiles
                                                </a>
                                            @endcan
                                            @can('komposisi_gaji.access')
                                                <a href="{{ route('komposisi_gaji.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Income Components By Employee
                                                </a>
                                            @endcan
                                            @can('jenis_hari.access')
                                                <a href="{{ route('jenis_hari.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Type
                                                    Of Days

                                                </a>
                                            @endcan
                                            @can('jam_kerja.access')
                                                <a href="{{ route('jam_kerja.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Working
                                                    Hours
                                                </a>
                                            @endcan
                                            @can('target_unit.access')
                                                <a href="{{ route('target_unit.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Target
                                                    By Unit
                                                </a>
                                            @endcan

                                            @can('target_wahana.access')
                                                <a href="{{ route('target_wahana.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Target
                                                    By Wahana
                                                </a>
                                            @endcan


                                            @can('shift_karyawan.access')
                                                <a href="{{ route('shift_karyawan.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Personnel
                                                    Scheduling

                                                </a>
                                            @endcan

                                            @can('transaksi_wahana.access')
                                                <a href="{{ route('transaksi_wahana.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Input
                                                    Sales Achievement

                                                </a>
                                            @endcan
                                            @can('bonus_karyawan.access')
                                                <a href="{{ route('bonus_karyawan.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Employee
                                                    Bonus Update
                                                </a>
                                            @endcan
                                            @can('tax_rates.access')
                                                <a href="{{ route('tax_rates.index') }}"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">TER
                                                    (Tax Rates)
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan
                                @can('process_payroll.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            Process
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            @can('pembayaran_gaji.access')
                                                <a href="{{ route('pembayaran_gaji.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                    Salary Calculations Staff
                                                </a>
                                            @endcan

                                            @can('pembayaran_gaji_nonstaff.access')
                                                <a href="{{ route('pembayaran_gaji_nonstaff.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                    Salary Calculations Non Staff
                                                </a>
                                            @endcan

                                            @can('slip.access')
                                                <a href="{{ route('slip.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Salary Slip Staff</a>
                                            @endcan

                                            @can('slip_gaji_nonstaff.access')
                                                <a href="{{ route('slip.nonStaff.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Salary Slip Non Staff</a>
                                            @endcan

                                            @can('absensi.access')
                                                <a href="{{ route('absensi.form') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Absensi
                                                    Pegawai
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan

                                @can('report_payroll.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            Report
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            @can('rekap_absensi.access')
                                                <a href="{{ route('report.absensi.filter') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Rekap Absensi Pegawai
                                                </a>
                                            @endcan
                                            @can('rekap_target_wahana.access')
                                                <a href="{{ route('report.target_wahana.filter') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Rekap Target Wahana
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    @endcan


                    {{-- asset --}}
                    @can('asset.access')

                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex justify-between items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md focus:outline-none">
                                Asset
                                <svg class="w-4 h-4 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Level 1: Setup Submenu -->
                            <div x-show="open" x-collapse class="pl-4 space-y-1">

                                <!-- Company with Sub-submenu -->
                                @can('setup_asset.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            Setup
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            @can('kategori_asset.access')
                                                <a href="{{ route('kategori_asset.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Category
                                                    Asset
                                                </a>
                                            @endcan
                                            @can('lokasi_asset.access')
                                                <a href="{{ route('lokasi.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Location
                                                    Asset
                                                </a>
                                            @endcan

                                            @can('masa_manfaat.access')
                                                <a href="{{ route('masa_manfaat.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Masa
                                                    Manfaat

                                                </a>
                                            @endcan
                                            @can('metode_penyusutan.access')
                                                <a href="{{ route('metode_penyusutan.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Metode
                                                    Penyusutan
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan
                                @can('tangible_asset.access')
                                    <a href="{{ route('tangible_asset.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Tangible
                                        Asset
                                    </a>
                                @endcan

                                @can('intangible_asset.access')
                                    <a href="{{ route('intangible_asset.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Intangible
                                        Asset
                                    </a>
                                @endcan

                                @can('monthly_process.access')
                                    <a href=""
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Monthly
                                        Process
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endcan


                    {{-- specpose --}}
                    @can('specpose.access')

                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex justify-between items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md focus:outline-none">
                                Specpose
                                <svg class="w-4 h-4 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Level 1: Setup Submenu -->
                            <div x-show="open" x-collapse class="pl-4 space-y-1">

                                <!-- Company with Sub-submenu -->
                                @can('specpose.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            Specpose
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            @can('specpose.create')
                                                <a href="{{ route('project.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                                    Specpose</a>
                                            @endcan

                                            @can('specpose.create')
                                                <a href="{{ route('project.create') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                    Specpose</a>
                                            @endcan

                                            @can('specpose.update')
                                                <a href="{{ route('project.edit_project') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Edit
                                                    Specpose</a>
                                            @endcan

                                            @can('specpose.view')
                                                <a href="{{ route('project.view_project') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">View
                                                    Specpose</a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan
                                @can('report_specpose.access')
                                    <a href="#"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                        Reports</a>
                                @endcan
                            </div>
                        </div>
                    @endcan


                    {{-- journal entry --}}
                    @can('journal_entry.access')

                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex justify-between items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md focus:outline-none">
                                Journal Entry
                                <svg class="w-4 h-4 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Level 1: Setup Submenu -->
                            <div x-show="open" x-collapse class="pl-4 space-y-1">

                                <!-- Company with Sub-submenu -->
                                @can('specpose.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            General Journal
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            @can('journal_entry.data')
                                                <a href="{{ route('journal_entry.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                                    Journal Entry</a>
                                            @endcan
                                            @can('journal_entry.create')
                                                <a href="{{ route('journal_entry.create') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                    Journal Entry</a>
                                            @endcan

                                            @can('journal_entry.update')
                                                <a href="{{ route('journal_entry.filter_journal_entry') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Edit
                                                    Journal Entry</a>
                                            @endcan

                                            @can('journal_entry.view')
                                                <a href="{{ route('journal_entry.view_journal_entry') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">View
                                                    Journal Entry</a>
                                            @endcan
                                            @can('rekonsiliasi_closing.access')
                                                <a href="{{ route('rekonsiliasi_closing.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Rekonsiliasi Closing vs Kas/Bank</a>
                                            @endcan
                                            @can('unassigned_watchdog.access')
                                                <a href="{{ route('unassigned_watchdog.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Unassigned Watchdog (Audit Non-Dept)</a>
                                            @endcan
                                            @can('assigned_watchdog.access')
                                                <a href="{{ route('assigned_watchdog.index') }}"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Assigned Watchdog (Audit Departemen)</a>
                                            @endcan
                                            @can('journal_entry.access')
                                                <a href="{{ route('journal_audit.index') }}"
                                                    class="block px-4 py-2 text-indigo-700 font-medium hover:bg-indigo-50 hover:text-indigo-900 transition-colors duration-150">
                                                    <i class="fas fa-scale-balanced mr-1 text-indigo-500"></i> Audit &amp; Kamus Akun Jurnal
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endcan

                            </div>
                        </div>
                    @endcan
                    @can('report_menu.access')

                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex justify-between items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md focus:outline-none">
                                Report
                                <svg class="w-4 h-4 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Level 1: Setup Submenu -->
                            <div x-show="open" x-collapse class="pl-4 space-y-1">

                                @can('buku_besar.access')
                                    <a href="{{ route('buku_besar.filter_buku_besar') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Buku
                                        Besar</a>
                                @endcan

                                @can('trial_balance.access')
                                    <a href="{{ route('trial_balance.filter_trial_balance') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Trial
                                        Balance</a>
                                @endcan

                                @can('neraca.access')
                                    <a href="{{ route('neraca.filter_neraca') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Neraca
                                    </a>
                                @endcan
                                @can('neraca_departemen.access')
                                    <a href="{{ route('neraca_departemen.filter_neraca_departemen') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Balance Sheet Departemen
                                    </a>
                                @endcan

                                @can('income_statement.access')
                                    <a href="{{ route('income_statement.filter_income_statement') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Income
                                        Statement</a>
                                @endcan

                                @can('income_statement_departement.access')
                                    <a href="{{ route('income_statement.filter_income_statement_departement') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Income
                                        Statement Department</a>
                                @endcan
                                @can('rekonsiliasi_closing.access')
                                    <a href="{{ route('rekonsiliasi_closing.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Rekonsiliasi Closing vs Kas/Bank
                                    </a>
                                @endcan
                                @can('unassigned_watchdog.access')
                                    <a href="{{ route('unassigned_watchdog.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Unassigned Watchdog (Audit Non-Dept)
                                    </a>
                                @endcan
                                @can('assigned_watchdog.access')
                                    <a href="{{ route('assigned_watchdog.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Assigned Watchdog (Audit Departemen)
                                    </a>
                                @endcan
                                @can('journal_entry.access')
                                    <a href="{{ route('journal_audit.index') }}"
                                        class="block px-2 py-1 text-indigo-700 font-medium hover:bg-indigo-50 hover:text-indigo-900 transition-colors duration-150">
                                        <i class="fas fa-scale-balanced mr-1 text-indigo-500"></i> Audit &amp; Kamus Akun Jurnal
                                    </a>
                                @endcan

                            </div>
                        </div>
                    @endcan

                    @can('documents.access')

                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex justify-between items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md focus:outline-none">
                                Documents
                                <svg class="w-4 h-4 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Level 1: Setup Submenu -->
                            <div x-show="open" x-collapse class="pl-4 space-y-1">

                                <!-- Company with Sub-submenu -->
                                @can('sales_document.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            Sales
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            <a href="{{ route('sales_orders.documents.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                Order</a>
                                            <a href="{{ route('sales_invoice.documents.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                Invoice</a>
                                        </div>
                                    </div>
                                @endcan
                                @can('purchases_document.access')
                                    <div x-data="{ subOpen: false }">
                                        <button @click="subOpen = !subOpen"
                                            class="w-full flex justify-between items-center px-3 py-2 text-sm text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md">
                                            Purchase
                                            <svg class="w-4 h-4 transform transition-transform duration-200"
                                                :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Level 2: Company Sub-submenu -->
                                        <div x-show="subOpen" x-collapse class="pl-4 space-y-1">
                                            <a href="{{ route('purchase_order.documents.index') }}" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchase
                                                Order</a>
                                            <a href="{{ route('purchase_invoice.documents.index') }}"
                                                @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchase
                                                Invoice
                                            </a>
                                        </div>
                                    </div>
                                @endcan
                                @can('taxes.access')
                                    <a href="{{ route('taxes.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                        Taxes
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endcan

                    @can('maintenance.access')

                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex justify-between items-center px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md focus:outline-none">
                                Maintenance
                                <svg class="w-4 h-4 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Level 1: Setup Submenu -->
                            <div x-show="open" x-collapse class="pl-4 space-y-1">

                                @can('start_new_year.access')
                                    <a href="{{ route('accounting.start_new_year') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Start
                                        New Year
                                    </a>
                                @endcan

                                @can('log_activity.access')
                                    <a href="{{ route('activity_log.index') }}"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Log
                                        Activity
                                    </a>
                                @endcan

                            </div>
                        </div>
                    @endcan


                    <!-- Profile & Logout -->
                    <div class="border-t border-gray-200 pt-2">
                        <a href="#"
                            class="block px-2 py-1 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="lock px-2 py-1 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md">Logout</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </nav>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

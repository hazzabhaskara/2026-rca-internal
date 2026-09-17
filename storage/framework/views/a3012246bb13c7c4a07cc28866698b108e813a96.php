    <nav x-data="{ menuOpen: false }" x-cloak class="bg-white shadow-lg border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-full mx-auto px-3 sm:px-6 lg:px-8">
            <!-- Header: Logo + Toggle -->
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="<?php echo e(route('dashboard')); ?>">
                        <div class="flex items-center space-x-2">
                            <img src="<?php echo e(asset('storage/' . \App\Setting::get('logo', 'logo.jpg'))); ?>" alt="Logo"
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
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('company.access')): ?>
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
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setting_setup.access')): ?>
                                            <a href="<?php echo e(route('setting.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Settings</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('company_profile.access')): ?>
                                            <a href="<?php echo e($company ? route('company_profile.show', $company->id) : route('company_profile.create')); ?>"
                                                @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Company
                                                Profile</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('taxpayers_company.access')): ?>
                                            <a href="<?php echo e($taxpayers ? route('taxpayers_company.show', $taxpayers->id) : route('taxpayers_company.create')); ?>"
                                                @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Taxpayer
                                                Profile</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>


                            <!-- General Submenu -->
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('general.access')): ?>
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
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('year_book.access')): ?>
                                            <a href="<?php echo e(route('start_new_year.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Year
                                                Book</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('numbering.access')): ?>
                                            <a href="<?php echo e(route('numbering_account.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Numbering</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('klasifikasi_akun.access')): ?>
                                            <a href="<?php echo e(route('klasifikasiAkun.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Account
                                                Classification</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('chart_of_account.access')): ?>
                                            <a href="<?php echo e(route('chartOfAccount.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Accounts</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('departement.access')): ?>
                                            <?php if($currentDept === 'Accounting'): ?>
                                                <a href="<?php echo e(route('departemen.index')); ?>" @click="open = false"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Departments
                                                </a>
                                            <?php elseif($currentDept === '-' || is_null($currentDept)): ?>
                                                <a href="<?php echo e(route('setting_departement.edit')); ?>" @click="open = false"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Departments
                                                </a>
                                            <?php else: ?>
                                                <a href="#"
                                                    onclick="alert('Menu ini hanya aktif saat mode departemen Accounting')"
                                                    @click="open = false"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Departments
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('linked_account_setup.access')): ?>
                                            <a href="<?php echo e(route('linkedAccount.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                                Account</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_taxes.access')): ?>
                                            <a href="<?php echo e(route('sales_taxes.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Taxes
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reports_setup.access')): ?>
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
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report_account.access')): ?>
                                            <a href="<?php echo e(route('report.account')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Account
                                                List</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report_klasifikasi_akun.access')): ?>
                                            <a href="<?php echo e(route('report.klasifikasi')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Account
                                                Classification List</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report_departemen_account.access')): ?>
                                            <a href="<?php echo e(route('report.departemen-akun')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Department
                                                Accounts List</a>
                                        <?php endif; ?>
                                        <a href="<?php echo e(route('report.sales_taxes')); ?>" @click="open = false"
                                            class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                            Taxes List</a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <a href="<?php echo e(route('users.index')); ?>" @click="open = false"
                                class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                Users & Roles
                            </a>
                        </div>
                    </div>


                    <!-- Sales Dropdown -->
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales.access')): ?>
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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setup_sales.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('linked_account_sales.access')): ?>
                                                <a href="<?php echo e(route('linkedAccountSales.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                                    Accounts</a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('option_sales.access')): ?>
                                                <a href="<?php echo e(route('sales_option.create')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Options
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_discount.access')): ?>
                                                <a href="<?php echo e(route('sales_discount.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Sales Discount
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- General Submenu -->
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('data.access')): ?>
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
                                            
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payment_method.access')): ?>
                                                <a href="<?php echo e(route('PaymentMethod.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Payment
                                                    Method</a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('customers.access')): ?>
                                                <a href="<?php echo e(route('customers.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Customers</a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_person.access')): ?>
                                                <a href="<?php echo e(route('sales_person.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Person
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_orders.access')): ?>
                                                <a href="<?php echo e(route('sales_order.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Orders</a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_invoice.access')): ?>
                                                <a href="<?php echo e(route('sales_invoice.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Invoices
                                                </a>
                                            <?php endif; ?>


                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('deposits.access')): ?>
                                                <a href="<?php echo e(route('sales_deposits.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Deposits</a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('receipts.access')): ?>
                                                <a href="<?php echo e(route('receipts.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Receipts</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

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
                    <?php endif; ?>

                    

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase.access')): ?>
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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setup_purchase.access')): ?>
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

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('linked_account_purchase.access')): ?>
                                                <a href="<?php echo e(route('linkedAccountPurchases.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                                    Account
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('option_purchase.access')): ?>
                                                <a href="<?php echo e(route('purchases_options.create')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Options
                                                </a>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('data.access')): ?>
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
                                            
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payment_method.access')): ?>
                                                <a href="<?php echo e(route('PaymentMethod.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Payment
                                                    Method</a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('vendor.access')): ?>
                                                <a href="<?php echo e(route('vendors.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Vendors</a>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase_order.access')): ?>
                                                <a href="<?php echo e(route('purchase_order.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchases
                                                    Orders
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase_invoice.access')): ?>
                                                <a href="<?php echo e(route('purchase_invoice.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchase
                                                    Invoice
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('prepayment_purchase.access')): ?>
                                                <a href="<?php echo e(route('prepayment.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Prepayments
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payment_purchase.access')): ?>
                                                <a href="<?php echo e(route('payment.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Payments
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payment_expense.access')): ?>
                                                <a href="<?php echo e(route('payment_expense.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Payment
                                                    Expense
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
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
                    <?php endif; ?>

                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('inventory.access')): ?>
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
                                        
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('options_inventory.access')): ?>
                                            <a href="<?php echo e($options_inventory ? route('options_inventory.edit', $options_inventory->id) : route('options_inventory.create')); ?>"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Options</a>
                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('price_list_inventory.access')): ?>
                                            <a href="<?php echo e(route('price_list_inventory.index')); ?>"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Price List</a>
                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('lokasi_inventory.access')): ?>
                                            <a href="<?php echo e(route('lokasi_inventory.index')); ?>"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Locations</a>
                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('kategori_inventory.access')): ?>
                                            <a href="<?php echo e(route('item_category.index')); ?>"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Categories</a>
                                        <?php endif; ?>

                                    </div>
                                </div>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('inventory.access')): ?>
                                    <a href="<?php echo e(route('inventory.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Inventory
                                        & Services
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Build from Bom.access')): ?>
                                    <a href="<?php echo e(route('build_of_bom.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Build
                                        From BOM
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Build from item assembly.access')): ?>
                                    <a href="<?php echo e(route('item_assembly.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Build
                                        From Item Assembly
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Transfer inventory.access')): ?>
                                    <a href="<?php echo e(route('transfer_inventory.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Transfer
                                        Inventory
                                    </a>
                                <?php endif; ?>
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
                    <?php endif; ?>



                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('budgeting.access')): ?>
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
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approval_step.access')): ?>
                                            <a href="<?php echo e(route('approval_step.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Approval
                                                Step</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('rekening.access')): ?>
                                            <a href="<?php echo e(route('rekening.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Rekening
                                            </a>
                                        <?php endif; ?>

                                    </div>
                                </div>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_budget.access')): ?>
                                    <a href="<?php echo e(route('pengajuan.create')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                        Budget
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_budget.access')): ?>
                                    <a href="<?php echo e(route('pengajuan.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                        Budget
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('budget_submission.access')): ?>
                                    <a href="<?php echo e(route('pengajuan.approval.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Budget
                                        Submission

                                    </a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('budget_disbursement.access')): ?>
                                    <a href="<?php echo e(route('intangible_asset.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Budget
                                        Disbursement

                                    </a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('budget_realization.access')): ?>
                                    <a href="<?php echo e(route('intangible_asset.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Budget
                                        Realization

                                    </a>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Closing Harian Dropdown -->
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('closing_harian.access')): ?>
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
                                        
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('linked_account_closing.access')): ?>
                                            <a href="<?php echo e(route('linkedAccount_closing.index')); ?>"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Linked Account </a>
                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('wahana.access')): ?>
                                            <a href="<?php echo e(route('wahana.index')); ?>"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Wahana(Rides)
                                            </a>
                                        <?php endif; ?>
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
                                        
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('closing_harian.create')): ?>
                                            <a href="<?php echo e(route('closing_harian.create')); ?>"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                Closing Harian </a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('closing_harian.access')): ?>
                                            <a href="<?php echo e(route('closing_harian.index')); ?>"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                                Closing Harian</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('rekonsiliasi_closing.access')): ?>
                                            <a href="<?php echo e(route('rekonsiliasi_closing.index')); ?>"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Rekonsiliasi Closing vs Kas/Bank</a>
                                        <?php endif; ?>
                                    </div>
                                </div>


                                <!-- Divider -->
                                <div class="my-1 border-t border-gray-100"></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payroll.access')): ?>
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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setup_payroll.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('level_karyawan.access')): ?>
                                                <a href="<?php echo e(route('LevelKaryawan.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Employee
                                                    Level</a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('jabatan.access')): ?>
                                                <a href="<?php echo e(route('jabatan.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Position
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('komponen_penghasilan.access')): ?>
                                                <a href="<?php echo e(route('komponen_penghasilan.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Income
                                                    Components By Level
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('group_unit.access')): ?>
                                                <a href="<?php echo e(route('group_unit.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Group
                                                    Unit
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('unit.access')): ?>
                                                <a href="<?php echo e(route('unit_kerja.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Units/Departemens
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('wahana.access')): ?>
                                                <a href="<?php echo e(route('wahana.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Wahana(Rides)
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ptkp.access')): ?>
                                                <a href="<?php echo e(route('ptkp.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">PTKP

                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('employee.access')): ?>
                                                <a href="<?php echo e(route('employee.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Employee Profiles
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('komposisi_gaji.access')): ?>
                                                <a href="<?php echo e(route('komposisi_gaji.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Income Components By Employee
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('jenis_hari.access')): ?>
                                                <a href="<?php echo e(route('jenis_hari.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Type
                                                    Of Days

                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('jam_kerja.access')): ?>
                                                <a href="<?php echo e(route('jam_kerja.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Working
                                                    Hours
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('target_unit.access')): ?>
                                                <a href="<?php echo e(route('target_unit.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Target
                                                    By Unit
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('target_wahana.access')): ?>
                                                <a href="<?php echo e(route('target_wahana.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Target
                                                    By Wahana
                                                </a>
                                            <?php endif; ?>


                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('crew_shift_karyawan.access')): ?>
                                                <a href="<?php echo e(route('crew_shift_karyawan.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Crew
                                                    Scheduling

                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('shift_karyawan.access')): ?>
                                                <a href="<?php echo e(route('shift_karyawan.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Personnel
                                                    Scheduling

                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('transaksi_wahana.access')): ?>
                                                <a href="<?php echo e(route('transaksi_wahana.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Input
                                                    Sales Achievement

                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('bonus_karyawan.access')): ?>
                                                <a href="<?php echo e(route('bonus_karyawan.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Employee
                                                    Bonus Update
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('tax_rates.access')): ?>
                                                <a href="<?php echo e(route('tax_rates.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">TER
                                                    (Tax Rates)
                                                </a>
                                            <?php endif; ?>


                                        </div>
                                    </div>
                                <?php endif; ?>


                                <!-- Reports Submenu -->
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('process_payroll.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('pembayaran_gaji.access')): ?>
                                                <a href="<?php echo e(route('pembayaran_gaji.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                    Salary Calculations Staff
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('pembayaran_gaji_nonstaff.access')): ?>
                                                <a href="<?php echo e(route('pembayaran_gaji_nonstaff.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                    Salary Calculations Non Staff
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('slip.access')): ?>
                                                <a href="<?php echo e(route('slip.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Salary Slip Staff</a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('slip_gaji_nonstaff.access')): ?>
                                                <a href="<?php echo e(route('slip.nonStaff.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Salary Slip Non Staff</a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('absensi.access')): ?>
                                                <a href="<?php echo e(route('absensi.form')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Absensi
                                                    Pegawai
                                                </a>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report_payroll.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('rekap_absensi.access')): ?>
                                                <a href="<?php echo e(route('report.absensi.filter')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Rekap Absensi Pegawai
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('rekap_target_wahana.access')): ?>
                                                <a href="<?php echo e(route('report.target_wahana.filter')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Rekap Target Wahana
                                                </a>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endif; ?>


                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('asset.access')): ?>
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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setup_asset.access')): ?>
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
                                            <a href="<?php echo e(route('linkedAccountAsset.index')); ?>"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                                Account
                                                Asset
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setup_asset.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('kategori_asset.access')): ?>
                                                <a href="<?php echo e(route('kategori_asset.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Category
                                                    Asset
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('lokasi_asset.access')): ?>
                                                <a href="<?php echo e(route('lokasi.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Location
                                                    Asset
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('masa_manfaat.access')): ?>
                                                <a href="<?php echo e(route('masa_manfaat.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Masa
                                                    Manfaat

                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('metode_penyusutan.access')): ?>
                                                <a href="<?php echo e(route('metode_penyusutan.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Metode
                                                    Penyusutan
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>


                                <!-- Tangible Asset -->
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('tangible_asset.access')): ?>
                                    <a href="<?php echo e(route('tangible_asset.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Tangible
                                        Asset
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('intangible_asset.access')): ?>
                                    <a href="<?php echo e(route('intangible_asset.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Intangible
                                        Asset
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('monthly_process.access')): ?>
                                    <a href=""
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Monthly
                                        Process
                                    </a>
                                <?php endif; ?>
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
                    <?php endif; ?>


                    

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('specpose.access')): ?>
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
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('specpose.create')): ?>
                                            <a href="<?php echo e(route('project.index')); ?>"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                                Specpose</a>
                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('specpose.create')): ?>
                                            <a href="<?php echo e(route('project.create')); ?>"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                Specpose</a>
                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('specpose.update')): ?>
                                            <a href="<?php echo e(route('project.edit_project')); ?>"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Edit
                                                Specpose</a>
                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('specpose.view')): ?>
                                            <a href="<?php echo e(route('project.view_project')); ?>"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">View
                                                Specpose</a>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report_specpose.access')): ?>
                                        <a href="#"
                                            class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                            Reports</a>
                                    <?php endif; ?>
                                </div>

                                <!-- Divider -->
                                <div class="my-1 border-t border-gray-100"></div>
                            </div>
                        </div>
                    <?php endif; ?>



                    <!-- Journal Entry Dropdown -->
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('journal_entry.access')): ?>
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

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('journal_entry.create')): ?>
                                    <a href="<?php echo e(route('journal_entry.create')); ?>"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                        General Journal </a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('journal_entry.data')): ?>
                                    <a href="<?php echo e(route('journal_entry.index')); ?>"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                        General Journal Entry</a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('journal_entry.view')): ?>
                                    <a href="<?php echo e(route('journal_entry.view_journal_entry')); ?>"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">View
                                        General Journal Entry</a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('journal_entry.update')): ?>
                                    <a href="<?php echo e(route('journal_entry.filter_journal_entry')); ?>"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Edit
                                        General Journal Entry</a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('rekonsiliasi_closing.access')): ?>
                                    <a href="<?php echo e(route('rekonsiliasi_closing.index')); ?>"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Rekonsiliasi Closing vs Kas/Bank</a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('unassigned_watchdog.access')): ?>
                                    <a href="<?php echo e(route('unassigned_watchdog.index')); ?>"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Unassigned Watchdog (Audit Non-Dept)</a>
                                <?php endif; ?>



                                <!-- Divider -->
                                <div class="my-1 border-t border-gray-100"></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report_menu.access')): ?>
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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('buku_besar.access')): ?>
                                    <a href="<?php echo e(route('buku_besar.filter_buku_besar')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Buku
                                        Besar</a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('trial_balance.access')): ?>
                                    <a href="<?php echo e(route('trial_balance.filter_trial_balance')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Trial
                                        Balance</a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('neraca.access')): ?>
                                    <a href="<?php echo e(route('neraca.filter_neraca')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Neraca
                                    </a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('neraca_departemen.access')): ?>
                                    <a href="<?php echo e(route('neraca_departemen.filter_neraca_departemen')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Balance Sheet Departemen
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('income_statement.access')): ?>
                                    <a href="<?php echo e(route('income_statement.filter_income_statement')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Income
                                        Statement</a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('income_statement_departement.access')): ?>
                                    <a href="<?php echo e(route('income_statement.filter_income_statement_departement')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Income
                                        Statement Department</a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('arus_kas.access')): ?>
                                    <a href="<?php echo e(route('arus_kas.filter_arus_kas')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Cash
                                        Flow
                                    </a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('rekonsiliasi_closing.access')): ?>
                                    <a href="<?php echo e(route('rekonsiliasi_closing.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Rekonsiliasi Closing vs Kas/Bank
                                    </a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('unassigned_watchdog.access')): ?>
                                    <a href="<?php echo e(route('unassigned_watchdog.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Unassigned Watchdog (Audit Non-Dept)
                                    </a>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Report Dropdown -->
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('fiscal.access')): ?>
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
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('fiscal_account.create')): ?>
                                            <a href="<?php echo e(route('fiscal_account.index')); ?>"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Fiscal
                                                Account
                                            </a>
                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('fiscal_account_persamaan.create')): ?>
                                            <a href="<?php echo e(route('fiscal_account_persamaan.index')); ?>"
                                                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Fiscal
                                                Account
                                                Persamaan</a>
                                        <?php endif; ?>
                                    </div>

                                </div>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('fiscal.access')): ?>
                                    <a href="<?php echo e(route('fiscal.fiscal_report')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Fiscal
                                        Reconciliation
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('perhitungan_pajak_penghasilan.access')): ?>
                                    <a href="<?php echo e(route('perhitungan_pajak_penghasilan.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                        Perhitungan Pajak Penghasilan
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('documents.access')): ?>
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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_document.access')): ?>
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
                                            <a href="<?php echo e(route('sales_orders.documents.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                Order</a>
                                            <a href="<?php echo e(route('sales_invoice.documents.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                Invoice</a>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- General Submenu -->
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchases_document.access')): ?>
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
                                            <a href="<?php echo e(route('purchase_order.documents.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchase
                                                Order</a>
                                            <a href="<?php echo e(route('purchase_invoice.documents.index')); ?>"
                                                @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchase
                                                Invoice
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('taxes.access')): ?>
                                    <a href="<?php echo e(route('taxes.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                        Taxes
                                    </a>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Maintenance Dropdown -->
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('maintenance.access')): ?>
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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('start_new_year.access')): ?>
                                    <a href="<?php echo e(route('accounting.start_new_year')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Start
                                        New Year
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('log_activity.access')): ?>
                                    <a href="<?php echo e(route('activity_log.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Log
                                        Activity
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>




                </div>
                <?php
                    $user = Auth::user()->name;
                ?>
                <!-- Profile Section -->
                <div class="hidden md:flex md:items-center">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <span class="text-gray-700 font-medium">Hi, <?php echo e($user); ?></span>
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
                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
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
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('company.access')): ?>
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
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setting_setup.access')): ?>
                                            <a href="<?php echo e(route('setting.index')); ?>"
                                                class="block px-3 py-2 text-sm text-gray-600 hover:bg-blue-100 rounded-md">Settings</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('company_profile.access')): ?>
                                            <a href="<?php echo e($company ? route('company_profile.show', $company->id) : route('company_profile.create')); ?>"
                                                class="block px-3 py-2 text-sm text-gray-600 hover:bg-blue-100 rounded-md">Company
                                                Profile</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('taxpayers_company.access')): ?>
                                            <a href="<?php echo e($taxpayers ? route('taxpayers_company.show', $taxpayers->id) : route('taxpayers_company.create')); ?>"
                                                class="block px-3 py-2 text-sm text-gray-600 hover:bg-blue-100 rounded-md">Taxpayer
                                                Profile</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('general.access')): ?>
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
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('year_book.access')): ?>
                                            <a href="<?php echo e(route('start_new_year.index')); ?>"
                                                class="block px-3 py-2 text-sm text-gray-600 hover:bg-blue-100 rounded-md">Year
                                                Book</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('numbering_account.access')): ?>
                                            <a href="<?php echo e(route('klasifikasiAkun.index')); ?>"
                                                class="block px-3 py-2 text-sm text-gray-600 hover:bg-blue-100 rounded-md">Numbering

                                            </a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('klasifikasi_akun.access')): ?>
                                            <a href="<?php echo e(route('klasifikasiAkun.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Account
                                                Classification</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('chart_of_account.access')): ?>
                                            <a href="<?php echo e(route('chartOfAccount.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Accounts</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('departement.access')): ?>
                                            <?php if($currentDept === 'Accounting'): ?>
                                                <a href="<?php echo e(route('departemen.index')); ?>" @click="open = false"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Departments
                                                </a>
                                            <?php elseif($currentDept === '-' || is_null($currentDept)): ?>
                                                <a href="<?php echo e(route('setting_departement.edit')); ?>" @click="open = false"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Departments
                                                </a>
                                            <?php else: ?>
                                                <a href="#"
                                                    onclick="alert('Menu ini hanya aktif saat mode departemen Accounting')"
                                                    @click="open = false"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Departments
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('linked_account_setup.access')): ?>
                                            <a href="<?php echo e(route('linkedAccount.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                                Account</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_taxes.access')): ?>
                                            <a href="<?php echo e(route('sales_taxes.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                Taxes
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reports_setup.access')): ?>
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
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report_account.access')): ?>
                                            <a href="<?php echo e(route('report.account')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Account
                                                List</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report_klasifikasi_akun.access')): ?>
                                            <a href="<?php echo e(route('report.klasifikasi')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Account
                                                Classification List</a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report_departemen_account.access')): ?>
                                            <a href="<?php echo e(route('report.departemen-akun')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Department
                                                Accounts List</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <a href="<?php echo e(route('users.index')); ?>" @click="open = false"
                                class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                Users & Roles
                            </a>
                        </div>
                    </div>

                    

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales.access')): ?>

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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setup_sales.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('linked_account_sales.access')): ?>
                                                <a href="<?php echo e(route('linkedAccountSales.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                                    Accounts</a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('option_sales.access')): ?>
                                                <a href="<?php echo e(route('sales_option.create')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Options
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_discount.access')): ?>
                                                <a href="<?php echo e(route('sales_discount.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Sales Discount
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('data.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payment_method.access')): ?>
                                                <a href="<?php echo e(route('PaymentMethod.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Payment
                                                    Method</a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('customers.access')): ?>
                                                <a href="<?php echo e(route('customers.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Customers</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_orders.access')): ?>
                                                <a href="<?php echo e(route('sales_order.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Orders</a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_invoice.access')): ?>
                                                <a href="<?php echo e(route('sales_invoice.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Invoices
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_person.access')): ?>
                                                <a href="<?php echo e(route('employee.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Person
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('deposits.access')): ?>
                                                <a href="<?php echo e(route('sales_deposits.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Deposits</a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('receipts.access')): ?>
                                                <a href="<?php echo e(route('receipts.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Receipts</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase.access')): ?>

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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setup_purchase.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('option_purchase.access')): ?>
                                                <a href="<?php echo e(route('purchases_options.create')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Options
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('linked_account_purchase.access')): ?>
                                                <a href="<?php echo e(route('linkedAccountPurchases.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Linked
                                                    Account
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase_order.access')): ?>
                                                <a href="<?php echo e(route('purchase_order.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchases
                                                    Orders
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase_invoice.access')): ?>
                                                <a href="<?php echo e(route('purchase_invoice.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchase
                                                    Invoice
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('prepayment_purchase.access')): ?>
                                                <a href="<?php echo e(route('prepayment.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Prepayments
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payment_purchase.access')): ?>
                                                <a href="<?php echo e(route('payment.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Payments
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('vendor.access')): ?>
                                    <a href="<?php echo e(route('vendors.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Vendors

                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>


                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('inventory.access')): ?>

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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setting_inventory.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('options_inventory.access')): ?>
                                                <a href="<?php echo e($options_inventory ? route('options_inventory.edit', $options_inventory->id) : route('options_inventory.create')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Options</a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('price_list_inventory.access')): ?>
                                                <a href="<?php echo e(route('price_list_inventory.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Price List</a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('lokasi_inventory.access')): ?>
                                                <a href="<?php echo e(route('lokasi_inventory.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Locations</a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('kategori_inventory.access')): ?>
                                                <a href="<?php echo e(route('item_category.index')); ?>"
                                                    class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Categories</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('inventory.access')): ?>
                                    <a href="<?php echo e(route('inventory.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Inventory
                                        & Service
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Build from Bom.access')): ?>
                                    <a href="<?php echo e(route('build_of_bom.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Build
                                        From BOM
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Build from item assembly.access')): ?>
                                    <a href="<?php echo e(route('item_assembly.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Build
                                        From Item Assembly
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Transfer inventory.access')): ?>
                                    <a href=""
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Transfer
                                        Inventory
                                    </a>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endif; ?>

                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('budgeting.access')): ?>

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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('budgeting.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_budget.access')): ?>
                                                <a href="<?php echo e(route('pengajuan.create')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Budget

                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_budget.access')): ?>
                                                <a href="<?php echo e(route('pengajuan.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                                    Budget
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('budget_submission.access')): ?>
                                                <a href="<?php echo e(route('intangible_asset.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Budget
                                                    Submission
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('budget_disbursement.access')): ?>
                                                <a href="<?php echo e(route('intangible_asset.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Budget
                                                    Disbursement

                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('budget_realization.access')): ?>
                                                <a href="<?php echo e(route('intangible_asset.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Budget
                                                    Realization

                                                </a>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('closing_harian.access')): ?>
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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('closing_harian.create')): ?>
                                    <a href="<?php echo e(route('closing_harian.create')); ?>"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                        Closing Harian</a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('closing_harian.access')): ?>
                                    <a href="<?php echo e(route('closing_harian.index')); ?>"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                        Closing Harian</a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('rekonsiliasi_closing.access')): ?>
                                    <a href="<?php echo e(route('rekonsiliasi_closing.index')); ?>"
                                        class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Rekonsiliasi Closing vs Kas/Bank</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payroll.access')): ?>

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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setup_payroll.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('level_karyawan.access')): ?>
                                                <a href="<?php echo e(route('LevelKaryawan.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Employee
                                                    Level</a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('jabatan.access')): ?>
                                                <a href="<?php echo e(route('jabatan.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Position
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('komponen_penghasilan.access')): ?>
                                                <a href="<?php echo e(route('komponen_penghasilan.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Income
                                                    Components By Level
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('unit.access')): ?>
                                                <a href="<?php echo e(route('unit_kerja.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Units/Departemens
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('wahana.access')): ?>
                                                <a href="<?php echo e(route('wahana.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Wahana(Rides)
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ptkp.access')): ?>
                                                <a href="<?php echo e(route('ptkp.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">PTKP

                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('employee.access')): ?>
                                                <a href="<?php echo e(route('employee.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Employee Profiles
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('komposisi_gaji.access')): ?>
                                                <a href="<?php echo e(route('komposisi_gaji.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Income Components By Employee
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('jenis_hari.access')): ?>
                                                <a href="<?php echo e(route('jenis_hari.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Type
                                                    Of Days

                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('jam_kerja.access')): ?>
                                                <a href="<?php echo e(route('jam_kerja.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Working
                                                    Hours
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('target_unit.access')): ?>
                                                <a href="<?php echo e(route('target_unit.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Target
                                                    By Unit
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('target_wahana.access')): ?>
                                                <a href="<?php echo e(route('target_wahana.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                    Target
                                                    By Wahana
                                                </a>
                                            <?php endif; ?>


                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('shift_karyawan.access')): ?>
                                                <a href="<?php echo e(route('shift_karyawan.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Personnel
                                                    Scheduling

                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('transaksi_wahana.access')): ?>
                                                <a href="<?php echo e(route('transaksi_wahana.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Input
                                                    Sales Achievement

                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('bonus_karyawan.access')): ?>
                                                <a href="<?php echo e(route('bonus_karyawan.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Employee
                                                    Bonus Update
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('tax_rates.access')): ?>
                                                <a href="<?php echo e(route('tax_rates.index')); ?>"
                                                    class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">TER
                                                    (Tax Rates)
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('process_payroll.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('pembayaran_gaji.access')): ?>
                                                <a href="<?php echo e(route('pembayaran_gaji.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                    Salary Calculations Staff
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('pembayaran_gaji_nonstaff.access')): ?>
                                                <a href="<?php echo e(route('pembayaran_gaji_nonstaff.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                    Salary Calculations Non Staff
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('slip.access')): ?>
                                                <a href="<?php echo e(route('slip.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Salary Slip Staff</a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('slip_gaji_nonstaff.access')): ?>
                                                <a href="<?php echo e(route('slip.nonStaff.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Salary Slip Non Staff</a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('absensi.access')): ?>
                                                <a href="<?php echo e(route('absensi.form')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Absensi
                                                    Pegawai
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report_payroll.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('rekap_absensi.access')): ?>
                                                <a href="<?php echo e(route('report.absensi.filter')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Rekap Absensi Pegawai
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('rekap_target_wahana.access')): ?>
                                                <a href="<?php echo e(route('report.target_wahana.filter')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                                    Rekap Target Wahana
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>


                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('asset.access')): ?>

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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setup_asset.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('kategori_asset.access')): ?>
                                                <a href="<?php echo e(route('kategori_asset.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Category
                                                    Asset
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('lokasi_asset.access')): ?>
                                                <a href="<?php echo e(route('lokasi.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Location
                                                    Asset
                                                </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('masa_manfaat.access')): ?>
                                                <a href="<?php echo e(route('masa_manfaat.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Masa
                                                    Manfaat

                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('metode_penyusutan.access')): ?>
                                                <a href="<?php echo e(route('metode_penyusutan.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Metode
                                                    Penyusutan
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('tangible_asset.access')): ?>
                                    <a href="<?php echo e(route('tangible_asset.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Tangible
                                        Asset
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('intangible_asset.access')): ?>
                                    <a href="<?php echo e(route('intangible_asset.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Intangible
                                        Asset
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('monthly_process.access')): ?>
                                    <a href=""
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Monthly
                                        Process
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>


                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('specpose.access')): ?>

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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('specpose.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('specpose.create')): ?>
                                                <a href="<?php echo e(route('project.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                                    Specpose</a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('specpose.create')): ?>
                                                <a href="<?php echo e(route('project.create')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                    Specpose</a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('specpose.update')): ?>
                                                <a href="<?php echo e(route('project.edit_project')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Edit
                                                    Specpose</a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('specpose.view')): ?>
                                                <a href="<?php echo e(route('project.view_project')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">View
                                                    Specpose</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report_specpose.access')): ?>
                                    <a href="#"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                        Reports</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>


                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('journal_entry.access')): ?>

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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('specpose.access')): ?>
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
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('journal_entry.data')): ?>
                                                <a href="<?php echo e(route('journal_entry.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Data
                                                    Journal Entry</a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('journal_entry.create')): ?>
                                                <a href="<?php echo e(route('journal_entry.create')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Create
                                                    Journal Entry</a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('journal_entry.update')): ?>
                                                <a href="<?php echo e(route('journal_entry.filter_journal_entry')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Edit
                                                    Journal Entry</a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('journal_entry.view')): ?>
                                                <a href="<?php echo e(route('journal_entry.view_journal_entry')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">View
                                                    Journal Entry</a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('rekonsiliasi_closing.access')): ?>
                                                <a href="<?php echo e(route('rekonsiliasi_closing.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Rekonsiliasi Closing vs Kas/Bank</a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('unassigned_watchdog.access')): ?>
                                                <a href="<?php echo e(route('unassigned_watchdog.index')); ?>"
                                                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Unassigned Watchdog (Audit Non-Dept)</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('report_menu.access')): ?>

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

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('buku_besar.access')): ?>
                                    <a href="<?php echo e(route('buku_besar.filter_buku_besar')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Buku
                                        Besar</a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('trial_balance.access')): ?>
                                    <a href="<?php echo e(route('trial_balance.filter_trial_balance')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Trial
                                        Balance</a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('neraca.access')): ?>
                                    <a href="<?php echo e(route('neraca.filter_neraca')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Neraca
                                    </a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('neraca_departemen.access')): ?>
                                    <a href="<?php echo e(route('neraca_departemen.filter_neraca_departemen')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Balance Sheet Departemen
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('income_statement.access')): ?>
                                    <a href="<?php echo e(route('income_statement.filter_income_statement')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Income
                                        Statement</a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('income_statement_departement.access')): ?>
                                    <a href="<?php echo e(route('income_statement.filter_income_statement_departement')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Income
                                        Statement Department</a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('rekonsiliasi_closing.access')): ?>
                                    <a href="<?php echo e(route('rekonsiliasi_closing.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Rekonsiliasi Closing vs Kas/Bank
                                    </a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('unassigned_watchdog.access')): ?>
                                    <a href="<?php echo e(route('unassigned_watchdog.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Unassigned Watchdog (Audit Non-Dept)
                                    </a>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('documents.access')): ?>

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
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_document.access')): ?>
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
                                            <a href="<?php echo e(route('sales_orders.documents.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                Order</a>
                                            <a href="<?php echo e(route('sales_invoice.documents.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Sales
                                                Invoice</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchases_document.access')): ?>
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
                                            <a href="<?php echo e(route('purchase_order.documents.index')); ?>" @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchase
                                                Order</a>
                                            <a href="<?php echo e(route('purchase_invoice.documents.index')); ?>"
                                                @click="open = false"
                                                class="block px-3 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Purchase
                                                Invoice
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('taxes.access')): ?>
                                    <a href="<?php echo e(route('taxes.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">
                                        Taxes
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('maintenance.access')): ?>

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

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('start_new_year.access')): ?>
                                    <a href="<?php echo e(route('accounting.start_new_year')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Start
                                        New Year
                                    </a>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('log_activity.access')): ?>
                                    <a href="<?php echo e(route('activity_log.index')); ?>"
                                        class="block px-2 py-1 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150">Log
                                        Activity
                                    </a>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endif; ?>


                    <!-- Profile & Logout -->
                    <div class="border-t border-gray-200 pt-2">
                        <a href="#"
                            class="block px-2 py-1 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md">Profile</a>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
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
<?php /**PATH C:\laragon\www\2026-rca-internal\resources\views/components/navbar.blade.php ENDPATH**/ ?>
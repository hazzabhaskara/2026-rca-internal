<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        // Cache-busting: ambil timestamp file jika ada, fallback ke time()
        if (!function_exists('ver')) {
            function ver($path)
            {
                $fullPath = public_path($path);
                return file_exists($fullPath) ? filemtime($fullPath) : time();
            }
        }
    @endphp

    <!-- Favicon ICO -->
    <link rel="icon" type="image/x-icon"
        href="{{ asset('image/favicon/favicon.ico') }}?v={{ ver('image/favicon/favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon"
        href="{{ asset('image/favicon/favicon.ico') }}?v={{ ver('image/favicon/favicon.ico') }}">

    <!-- Favicon PNG -->
    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('image/favicon/favicon-32x32.png') }}?v={{ ver('image/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('image/favicon/favicon-16x16.png') }}?v={{ ver('image/favicon/favicon-16x16.png') }}">

    <!-- Optional: SVG modern browser -->
    <link rel="icon" type="image/svg+xml"
        href="{{ asset('image/favicon/favicon.svg') }}?v={{ ver('image/favicon/favicon.svg') }}">

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- AlpineJS + Collapse Plugin --}}
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Fonts --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- sweetalert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Custom Styles & /better-ui polish --}}
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .smooth-transition {
            transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05),
                0 4px 6px -2px rgba(0, 0, 0, 0.025);
        }

        [x-cloak] {
            display: none !important;
        }

        /* Better UI polish: scrollbars */
        main::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        main::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 9999px;
        }
        main::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8;
        }

        .btn-primary {
            background-color: #4f46e5;
            color: white;
        }

        .bg-primary {
            background-color: white;
        }
    </style>

    @stack('styles')
</head>

<body x-data="erpApp()" 
      x-cloak 
      class="m-0 p-0 font-sans antialiased bg-slate-50 text-slate-800 min-h-screen selection:bg-indigo-500 selection:text-white">

    {{-- Unified Modern Sidebar --}}
    @include('components.sidebar')

    {{-- Main View Wrapper (Adapts margin smoothly to sidebar collapse) --}}
    <div :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'" 
         class="flex flex-col min-h-screen transition-all duration-300 ease-in-out">
        
        {{-- Unified Modern Topbar --}}
        @include('components.topbar')

        {{-- Main Content --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8 pb-20">
            @yield('content')
        </main>

        {{-- Unified Footer --}}
        @php
            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            $textFooter = \App\Setting::get('text_footer', 'ANTS LITE+ ©2025_AN NAML CORP.');
        @endphp
        <footer class="bg-white border-t border-slate-200/80 text-slate-500 text-xs py-3.5 px-6 shrink-0 flex flex-col sm:flex-row items-center justify-between gap-2 shadow-xs">
            <span class="font-medium text-slate-600">{{ $textFooter }}</span>
            <div class="flex items-center space-x-3 text-[11px] text-slate-400">
                <span>Enterprise Resource Planning</span>
                <span>•</span>
                <span class="font-semibold text-slate-500">PT. Rembulan Citra Abadi</span>
            </div>
        </footer>
    </div>

    {{-- Core jQuery & Select2 --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Alpine App Controller & Utilities --}}
    <script>
        function erpApp() {
            return {
                mobileSidebarOpen: false,
                sidebarCollapsed: localStorage.getItem('rca_sidebar_collapsed') === 'true',
                quickSearchOpen: false,
                quickSearchQuery: '',
                menuSearch: '',
                openSections: JSON.parse(localStorage.getItem('rca_open_sections') || '[]'),
                
                toggleSidebarCollapse() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    localStorage.setItem('rca_sidebar_collapsed', this.sidebarCollapsed);
                },

                isSectionOpen(name) {
                    return this.openSections.includes(name);
                },

                toggleSection(name, isOpen) {
                    if (isOpen) {
                        if (!this.openSections.includes(name)) this.openSections.push(name);
                    } else {
                        this.openSections = this.openSections.filter(s => s !== name);
                    }
                    localStorage.setItem('rca_open_sections', JSON.stringify(this.openSections));
                },

                get quickLinks() {
                    return [
                        { title: 'Dashboard', category: 'General', icon: 'fas fa-gauge-high', url: '{{ route("dashboard") }}' },
                        { title: 'Chart of Account', category: 'Accounting', icon: 'fas fa-book-bookmark', url: '{{ route("chartOfAccount.index") }}' },
                        { title: 'Create Journal Entry', category: 'Journal', icon: 'fas fa-plus-circle', url: '{{ route("journal_entry.create") }}' },
                        { title: 'View Journal Entry', category: 'Journal', icon: 'fas fa-book-open', url: '{{ route("journal_entry.index") }}' },
                        { title: 'Buku Besar', category: 'Report', icon: 'fas fa-book', url: '{{ route("buku_besar.filter_buku_besar") }}' },
                        { title: 'Trial Balance', category: 'Report', icon: 'fas fa-scale-unbalanced-flip', url: '{{ route("trial_balance.filter_trial_balance") }}' },
                        { title: 'Neraca', category: 'Report', icon: 'fas fa-balance-scale', url: '{{ route("neraca.filter_neraca") }}' },
                        { title: 'Income Statement', category: 'Report', icon: 'fas fa-file-invoice-dollar', url: '{{ route("income_statement.filter_income_statement") }}' },
                        { title: 'Closing Harian', category: 'Closing', icon: 'fas fa-calendar-check', url: '{{ route("closing_harian.index") }}' },
                        { title: 'Data Karyawan', category: 'Payroll', icon: 'fas fa-users', url: '{{ route("employee.index") }}' },
                        { title: 'Pembayaran Gaji', category: 'Payroll', icon: 'fas fa-money-bill-transfer', url: '{{ route("pembayaran_gaji.index") }}' },
                        { title: 'Slip Gaji', category: 'Payroll', icon: 'fas fa-file-lines', url: '{{ route("slip.index") }}' },
                        { title: 'Items & Stok', category: 'Inventory', icon: 'fas fa-boxes-stacked', url: '{{ route("items.index") }}' },
                        { title: 'Sales Invoice', category: 'Sales', icon: 'fas fa-file-invoice-dollar', url: '{{ route("sales_invoice.index") }}' },
                        { title: 'Purchase Invoice', category: 'Purchases', icon: 'fas fa-cart-shopping', url: '{{ route("purchase_invoice.index") }}' },
                        { title: 'System Settings', category: 'Setup', icon: 'fas fa-sliders', url: '{{ route("setting.index") }}' }
                    ];
                },

                get filteredQuickLinks() {
                    if (!this.quickSearchQuery) return this.quickLinks.slice(0, 8);
                    const q = this.quickSearchQuery.toLowerCase();
                    return this.quickLinks.filter(item => 
                        item.title.toLowerCase().includes(q) || item.category.toLowerCase().includes(q)
                    );
                },

                init() {
                    window.addEventListener('keydown', (e) => {
                        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                            e.preventDefault();
                            this.quickSearchOpen = !this.quickSearchOpen;
                            if (this.quickSearchOpen) {
                                this.$nextTick(() => {
                                    if (this.$refs.quickSearchInput) this.$refs.quickSearchInput.focus();
                                });
                            }
                        }
                    });
                }
            };
        }

        // Backward compatibility functions
        function toggleMobileMenu() {
            // Trigger alpine if present
            const alpineEl = document.querySelector('[x-data]');
            if (alpineEl && alpineEl.__x) {
                alpineEl.__x.$data.mobileSidebarOpen = !alpineEl.__x.$data.mobileSidebarOpen;
            }
        }

        function toggleSidebar() {
            const alpineEl = document.querySelector('[x-data]');
            if (alpineEl && alpineEl.__x) {
                alpineEl.__x.$data.toggleSidebarCollapse();
            }
        }
    </script>

    {{-- SweetAlert Session Alerts --}}
    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'OK'
            });
        @elseif (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: `{!! session('error') !!}`,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
        @endif

        function confirmDelete(id) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data ini tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>

    {{-- Stack scripts --}}
    @stack('scripts')
</body>

</html>

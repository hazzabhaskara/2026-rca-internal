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
            'name' => 'Web RCA Operasional',
            'badge' => 'OPERASIONAL',
            'badge_bg' => 'bg-indigo-50',
            'badge_text' => 'text-indigo-700',
            'badge_border' => 'border-indigo-200',
            'accent' => 'indigo',
            'accent_hex' => '#4F46E5',
        ],
        'internal_2025' => [
            'name' => 'Web Internal 2025',
            'badge' => 'INTERNAL 2025',
            'badge_bg' => 'bg-amber-50',
            'badge_text' => 'text-amber-800',
            'badge_border' => 'border-amber-300',
            'accent' => 'amber',
            'accent_hex' => '#D97706',
        ],
        'internal_2026' => [
            'name' => 'Web Internal 2026',
            'badge' => 'INTERNAL 2026',
            'badge_bg' => 'bg-emerald-50',
            'badge_text' => 'text-emerald-800',
            'badge_border' => 'border-emerald-300',
            'accent' => 'emerald',
            'accent_hex' => '#059669',
        ],
    ][$appSystemKey];

    $user = Auth::user() ? Auth::user()->name : 'Guest';
@endphp

<header class="sticky top-0 z-30 flex h-16 w-full items-center justify-between border-b border-slate-200/80 bg-white/95 px-4 backdrop-blur-md sm:px-6 shadow-sm transition-all duration-300">
    <!-- Left Section: Toggle & Title/Breadcrumbs -->
    <div class="flex items-center space-x-3 sm:space-x-4">
        <!-- Mobile Toggle Button -->
        <button @click="mobileSidebarOpen = !mobileSidebarOpen" 
                type="button" 
                class="lg:hidden flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 active:scale-[0.96] transition-all">
            <i class="fas fa-bars text-sm"></i>
        </button>

        <!-- Desktop Toggle Button (Only when collapsed) -->
        <button @click="toggleSidebarCollapse()" 
                type="button" 
                x-show="sidebarCollapsed" 
                x-cloak
                title="Buka Sidebar"
                class="hidden lg:flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 active:scale-[0.96] transition-all">
            <i class="fas fa-bars text-sm"></i>
        </button>

        <!-- System Identity & Page Indicator -->
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-lg border {{ $systemTheme['badge_bg'] }} {{ $systemTheme['badge_text'] }} {{ $systemTheme['badge_border'] }} shadow-xs">
                <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $systemTheme['accent_hex'] }}"></span>
                {{ $systemTheme['badge'] }}
            </span>

            <div class="hidden sm:flex items-center text-xs text-slate-400 space-x-2">
                <span>/</span>
                <span class="font-medium text-slate-700 capitalize">
                    @php
                        $routeParts = explode('.', Route::currentRouteName() ?? 'Dashboard');
                        echo str_replace('_', ' ', $routeParts[0]);
                    @endphp
                </span>
            </div>
        </div>
    </div>

    <!-- Center / Right Section -->
    <div class="flex items-center space-x-2 sm:space-x-3">
        <!-- Quick Search Modal Trigger -->
        <button @click="quickSearchOpen = true" 
                type="button" 
                class="hidden md:flex items-center space-x-2 rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-1.5 text-xs text-slate-500 hover:border-slate-300 hover:bg-slate-100 transition-all active:scale-[0.96]">
            <i class="fas fa-magnifying-glass text-slate-400 text-xs"></i>
            <span>Cari modul & menu...</span>
            <kbd class="ml-2 rounded bg-white px-1.5 py-0.5 text-[10px] font-semibold text-slate-400 shadow-xs border border-slate-200">Ctrl K</kbd>
        </button>

        <!-- Department Badge -->
        <div class="hidden lg:flex items-center space-x-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-600">
            <i class="fas fa-building text-slate-400 text-xs"></i>
            <span class="font-semibold">{{ $currentDept ?? '-' }}</span>
        </div>

        <!-- User Profile Dropdown -->
        <div class="relative" x-data="{ userMenuOpen: false }">
            <button @click="userMenuOpen = !userMenuOpen" 
                    @click.outside="userMenuOpen = false"
                    type="button" 
                    class="flex items-center space-x-2.5 rounded-xl border border-slate-200 bg-white p-1.5 sm:px-3 sm:py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 active:scale-[0.96] transition-all">
                <div class="h-7 w-7 rounded-lg flex items-center justify-center font-bold text-white text-[11px] shadow-xs" style="background-color: {{ $systemTheme['accent_hex'] }}">
                    {{ strtoupper(substr($user, 0, 2)) }}
                </div>
                <div class="hidden sm:flex flex-col text-left">
                    <span class="font-semibold text-slate-800 leading-tight truncate max-w-[120px]">{{ $user }}</span>
                    <span class="text-[10px] text-slate-400 leading-tight">Pengguna Aktif</span>
                </div>
                <i class="fas fa-chevron-down text-[10px] text-slate-400 ml-1 transition-transform duration-200" :class="{ 'rotate-180': userMenuOpen }"></i>
            </button>

            <!-- User Menu Dropdown Panel -->
            <div x-show="userMenuOpen" 
                 x-cloak 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                 class="absolute right-0 mt-2 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl z-50">
                <div class="px-3 py-2 border-b border-slate-100 mb-1">
                    <p class="text-xs font-bold text-slate-800">{{ $user }}</p>
                    <p class="text-[10px] text-slate-500">Dept: {{ $currentDept ?? '-' }}</p>
                </div>
                
                @can('setting_setup.access')
                    <a href="{{ route('setting.index') }}" class="flex items-center space-x-2.5 rounded-xl px-3 py-2 text-xs text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fas fa-sliders w-4 text-slate-400"></i>
                        <span>System Settings</span>
                    </a>
                @endcan

                @can('company_profile.access')
                    <a href="{{ $company ? route('company_profile.show', $company->id) : route('company_profile.create') }}" class="flex items-center space-x-2.5 rounded-xl px-3 py-2 text-xs text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i class="fas fa-building w-4 text-slate-400"></i>
                        <span>Company Profile</span>
                    </a>
                @endcan

                <div class="my-1 border-t border-slate-100"></div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-2.5 rounded-xl px-3 py-2 text-xs text-rose-600 hover:bg-rose-50 transition-colors text-left font-medium">
                        <i class="fas fa-arrow-right-from-bracket w-4 text-rose-500"></i>
                        <span>Keluar / Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<!-- Quick Navigation Modal (Ctrl + K) -->
<div x-show="quickSearchOpen" 
     x-cloak
     x-transition:enter="transition-opacity ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-start justify-center pt-20 px-4 bg-slate-950/60 backdrop-blur-xs"
     @keydown.escape.window="quickSearchOpen = false">
    
    <div @click.outside="quickSearchOpen = false" 
         class="w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        
        <div class="flex items-center border-b border-slate-200 px-4 py-3 bg-slate-50/50">
            <i class="fas fa-magnifying-glass text-slate-400 mr-3 text-sm"></i>
            <input type="text" 
                   x-ref="quickSearchInput"
                   x-model="quickSearchQuery"
                   placeholder="Ketik nama modul, jurnal, laporan, akun..." 
                   class="w-full bg-transparent text-sm text-slate-800 placeholder-slate-400 focus:outline-none">
            <kbd class="text-[10px] font-semibold text-slate-400 bg-white border border-slate-200 rounded px-1.5 py-0.5">ESC</kbd>
        </div>

        <div class="max-h-80 overflow-y-auto p-2 space-y-1">
            <template x-for="item in filteredQuickLinks" :key="item.url">
                <a :href="item.url" 
                   class="flex items-center justify-between px-3 py-2 rounded-xl text-xs text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors group">
                    <div class="flex items-center space-x-2.5">
                        <i :class="item.icon" class="w-4 text-center text-slate-400 group-hover:text-indigo-600"></i>
                        <span x-text="item.title" class="font-medium"></span>
                    </div>
                    <span x-text="item.category" class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider"></span>
                </a>
            </template>
            <div x-show="filteredQuickLinks.length === 0" class="p-6 text-center text-xs text-slate-400">
                Tidak ada menu yang cocok dengan kata kunci.
            </div>
        </div>
    </div>
</div>

{{-- resources/views/journal_audit/index.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    .audit-header {
        background: linear-gradient(135deg, #0f172a 0%, #312e81 50%, #1e1b4b 100%);
        position: relative;
        overflow: hidden;
    }
    .audit-header::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(129, 140, 248, 0.2) 0%, transparent 70%);
        pointer-events: none;
    }
    .kpi-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }
    .table-audit th {
        font-size: 0.72rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        background-color: #1e293b;
        color: #f8fafc;
        border-bottom: 2px solid #334155;
    }
    .bulk-toolbar {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(120px);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 40;
    }
    .bulk-toolbar.active {
        transform: translateX(-50%) translateY(0);
    }
    .select2-container {
        width: 100% !important;
    }
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 0.5rem !important;
        background-color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 10px !important;
        font-size: 0.75rem !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2) !important;
        outline: none !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 0 !important;
        color: #1e293b !important;
        font-size: 0.75rem !important;
        font-weight: 500 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #94a3b8 !important;
        font-size: 0.75rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }
    .select2-dropdown {
        font-size: 0.75rem !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        z-index: 50 !important;
        overflow: hidden !important;
    }
    .select2-container--default .select2-search--dropdown {
        padding: 6px !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #cbd5e1 !important;
        border-radius: 0.375rem !important;
        padding: 5px 8px !important;
        font-size: 0.75rem !important;
        outline: none !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2) !important;
    }
    .select2-results__option {
        padding: 6px 10px !important;
        font-size: 0.75rem !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #4f46e5 !important;
        color: #ffffff !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-800 pb-24">

    {{-- Header Banner --}}
    <div class="audit-header text-white px-6 py-8 mb-6 shadow-lg">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">
                        <i class="fas fa-robot mr-1"></i> SMART JOURNAL AUDITOR
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/30 text-amber-200 border border-amber-400/30">
                        <i class="fas fa-book-bookmark mr-1"></i> KAMUS INDIKATOR COA
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white flex items-center gap-2">
                    <span>Audit &amp; Kamus Akun Jurnal</span>
                </h1>
                <p class="text-slate-300 text-sm mt-1">
                    Deteksi Cerdas Potensi Salah Akun, Transaksi Ambigu/Bias, dan Hubungan Narasi <code>comment_line</code> terhadap COA Perusahaan
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="openScanModal()"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold shadow-md transition-colors duration-150">
                    <i class="fas fa-magnifying-glass-chart mr-2"></i> Scan Jurnal Sekarang
                </button>
                <a href="{{ route('journal_audit.rules') }}"
                    class="inline-flex items-center px-3.5 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-semibold shadow-md transition-colors duration-150">
                    <i class="fas fa-sliders mr-2"></i> Kelola Kamus ({{ $rules->count() }} Aturan)
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center shadow-sm">
                <i class="fas fa-check-circle text-emerald-500 mr-2 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center shadow-sm">
                <i class="fas fa-exclamation-circle text-rose-500 mr-2 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- KPI Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            {{-- Salah Akun --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'SALAH_AKUN', 'is_resolved' => '0']) }}"
               class="kpi-card bg-white p-4 rounded-xl border border-rose-200 shadow-sm block hover:border-rose-400">
                <div class="flex items-center justify-between text-rose-600 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider">Salah Akun</span>
                    <i class="fas fa-triangle-exclamation text-base"></i>
                </div>
                <div class="text-2xl font-black text-rose-700">{{ number_format($stats['salah_akun']) }}</div>
                <div class="text-[11px] text-rose-500 mt-1">Perlu Perbaikan Segera</div>
            </a>

            {{-- Ambigu / Bias --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'AMBIGU_BIAS', 'is_resolved' => '0']) }}"
               class="kpi-card bg-white p-4 rounded-xl border border-amber-200 shadow-sm block hover:border-amber-400">
                <div class="flex items-center justify-between text-amber-600 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider">Ambigu / Bias</span>
                    <i class="fas fa-scale-balanced text-base"></i>
                </div>
                <div class="text-2xl font-black text-amber-700">{{ number_format($stats['ambigu']) }}</div>
                <div class="text-[11px] text-amber-600 mt-1">Evaluasi 1jt - 5jt / Opsi Ganda</div>
            </a>

            {{-- Pola Baru --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'POLA_BARU', 'is_resolved' => '0']) }}"
               class="kpi-card bg-white p-4 rounded-xl border border-slate-200 shadow-sm block hover:border-slate-400">
                <div class="flex items-center justify-between text-slate-500 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider">Pola Baru</span>
                    <i class="fas fa-question-circle text-base"></i>
                </div>
                <div class="text-2xl font-black text-slate-700">{{ number_format($stats['pola_baru']) }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Belum Terdaftar di Kamus</div>
            </a>

            {{-- Tepat --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'TEPAT', 'is_resolved' => 'all']) }}"
               class="kpi-card bg-white p-4 rounded-xl border border-emerald-200 shadow-sm block hover:border-emerald-400">
                <div class="flex items-center justify-between text-emerald-600 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider">Tepat (Valid)</span>
                    <i class="fas fa-circle-check text-base"></i>
                </div>
                <div class="text-2xl font-black text-emerald-700">{{ number_format($stats['tepat']) }}</div>
                <div class="text-[11px] text-emerald-600 mt-1">Sesuai Kamus 100%</div>
            </a>

            {{-- Selesai Diremediasi --}}
            <a href="{{ request()->fullUrlWithQuery(['is_resolved' => '1']) }}"
               class="kpi-card bg-white p-4 rounded-xl border border-indigo-200 shadow-sm block hover:border-indigo-400">
                <div class="flex items-center justify-between text-indigo-600 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider">Telah Diselesaikan</span>
                    <i class="fas fa-circle-check text-base"></i>
                </div>
                <div class="text-2xl font-black text-indigo-700">{{ number_format($stats['resolved']) }}</div>
                <div class="text-[11px] text-indigo-500 mt-1">Re-assigned / Dikonfirmasi</div>
            </a>
        </div>

        {{-- Filter Box (Unified Grouped Control Panel - Better UI) --}}
        <div x-data="{ panelOpen: true }" class="bg-white rounded-xl shadow-sm border border-slate-200/90 mb-6 overflow-hidden">
            {{-- Panel Header with Collapse Toggle --}}
            <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-200/80 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-200/60 flex items-center justify-center text-indigo-600 shadow-2xs">
                        <i class="fas fa-sliders text-xs"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 tracking-wide uppercase">Parameter &amp; Filter Audit Jurnal</h3>
                        <p class="text-[11px] text-slate-500">Konfigurasi menyeluruh kriteria deteksi &amp; anomali transaksi</p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="text-[11px] font-medium text-slate-500 hidden sm:block">
                        Total Cocok: <span class="font-bold text-slate-800 font-mono">{{ number_format($anomalies->total()) }}</span> Transaksi
                    </div>
                    <button type="button" @click="panelOpen = !panelOpen"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 transition flex items-center gap-1.5 shadow-2xs active:scale-[0.98]"
                        title="Sembunyikan / Tampilkan Panel Filter">
                        <span x-text="panelOpen ? 'Sembunyikan' : 'Buka Filter'"></span>
                        <i class="fas text-[10px] text-slate-400 transition-transform duration-200" :class="panelOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('journal_audit.index') }}" method="GET" id="formAuditFilter">
                {{-- Form Body (Semantically Grouped) --}}
                <div x-show="panelOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="p-5 space-y-5">
                    
                    {{-- 1. Periode & Status Audit --}}
                    <div>
                        <div class="flex items-center gap-2 mb-2.5">
                            <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-bold flex items-center justify-center">1</span>
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Periode &amp; Status Audit</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai</label>
                                <input type="date" name="start_date" value="{{ $startDate }}"
                                    class="h-[38px] w-full text-xs rounded-lg border border-slate-300 px-3 bg-white text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Akhir</label>
                                <input type="date" name="end_date" value="{{ $endDate }}"
                                    class="h-[38px] w-full text-xs rounded-lg border border-slate-300 px-3 bg-white text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Diagnosa Audit</label>
                                <select name="status"
                                    class="h-[38px] w-full text-xs rounded-lg border border-slate-300 px-3 bg-white text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-colors">
                                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status Diagnosa</option>
                                    <option value="SALAH_AKUN" {{ $status === 'SALAH_AKUN' ? 'selected' : '' }}>Salah Akun (Merah)</option>
                                    <option value="AMBIGU_BIAS" {{ $status === 'AMBIGU_BIAS' ? 'selected' : '' }}>Ambigu / Bias (Kuning)</option>
                                    <option value="POLA_BARU" {{ $status === 'POLA_BARU' ? 'selected' : '' }}>Pola Baru (Abu-abu)</option>
                                    <option value="TEPAT" {{ $status === 'TEPAT' ? 'selected' : '' }}>Tepat / Sesuai (Hijau)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Remediasi</label>
                                <select name="is_resolved"
                                    class="h-[38px] w-full text-xs rounded-lg border border-slate-300 px-3 bg-white text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-colors">
                                    <option value="0" {{ $resolved === '0' ? 'selected' : '' }}>Belum Selesai (Pending)</option>
                                    <option value="1" {{ $resolved === '1' ? 'selected' : '' }}>Sudah Diremediasi</option>
                                    <option value="all" {{ $resolved === 'all' ? 'selected' : '' }}>Semua (Pending &amp; Selesai)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Pemisah Halus --}}
                    <div class="border-t border-slate-100"></div>

                    {{-- 2. Entitas Akun & Unit Kerja --}}
                    <div>
                        <div class="flex items-center gap-2 mb-2.5">
                            <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-bold flex items-center justify-center">2</span>
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Entitas Akun &amp; Unit Kerja</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Akun Saat Ini (Tercatat)</label>
                                <select name="current_kode_akun" class="select2-filter w-full text-xs">
                                    <option value="">Semua Akun Tercatat</option>
                                    @foreach($coas as $c)
                                        <option value="{{ $c->kode_akun }}" {{ $currentKodeAkun === $c->kode_akun ? 'selected' : '' }}>
                                            {{ $c->kode_akun }} - {{ $c->nama_akun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Rekomendasi Akun (Kamus)</label>
                                <select name="recommended_kode_akun" class="select2-filter w-full text-xs">
                                    <option value="">Semua Rekomendasi</option>
                                    <option value="__HAS_REC__" {{ $recommendedKodeAkun === '__HAS_REC__' ? 'selected' : '' }}>★ Memiliki Rekomendasi Kamus</option>
                                    <option value="__NO_REC__" {{ $recommendedKodeAkun === '__NO_REC__' ? 'selected' : '' }}>Tanpa Rekomendasi (Belum Ada Pola)</option>
                                    <optgroup label="Pilih Akun Spesifik">
                                        @foreach($coas as $c)
                                            <option value="{{ $c->kode_akun }}" {{ $recommendedKodeAkun === $c->kode_akun ? 'selected' : '' }}>
                                                {{ $c->kode_akun }} - {{ $c->nama_akun }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen / Unit</label>
                                <select name="departemen_id"
                                    class="h-[38px] w-full text-xs rounded-lg border border-slate-300 px-3 bg-white text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-colors">
                                    <option value="all">Semua Departemen</option>
                                    @foreach($departemens as $dept)
                                        <option value="{{ $dept->id }}" {{ (string)$departemenId === (string)$dept->id ? 'selected' : '' }}>
                                            {{ $dept->kode }} - {{ $dept->deskripsi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Prefix / Sumber Voucher</label>
                                <select name="source_prefix"
                                    class="h-[38px] w-full text-xs rounded-lg border border-slate-300 px-3 bg-white text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-colors">
                                    <option value="all" {{ $sourcePrefix === 'all' || empty($sourcePrefix) ? 'selected' : '' }}>Semua Prefix</option>
                                    @foreach($popularPrefixes as $pfx)
                                        <option value="{{ $pfx }}" {{ $sourcePrefix === $pfx ? 'selected' : '' }}>{{ $pfx }}</option>
                                    @endforeach
                                    @if(!empty($sourcePrefix) && !in_array($sourcePrefix, array_merge(['all'], $popularPrefixes)))
                                        <option value="{{ $sourcePrefix }}" selected>{{ $sourcePrefix }} (Kustom)</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Pemisah Halus --}}
                    <div class="border-t border-slate-100"></div>

                    {{-- 3. Nilai Moneter & Parameter Transaksi --}}
                    <div>
                        <div class="flex items-center gap-2 mb-2.5">
                            <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-bold flex items-center justify-center">3</span>
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Nilai Moneter &amp; Parameter Transaksi</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Posisi Mutasi</label>
                                <select name="mutation_type"
                                    class="h-[38px] w-full text-xs rounded-lg border border-slate-300 px-3 bg-white text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-colors">
                                    <option value="all" {{ $mutationType === 'all' ? 'selected' : '' }}>Semua Mutasi (Dr &amp; Cr)</option>
                                    <option value="debit" {{ $mutationType === 'debit' ? 'selected' : '' }}>Debit Saja (Dr)</option>
                                    <option value="credit" {{ $mutationType === 'credit' ? 'selected' : '' }}>Kredit Saja (Cr)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Rentang Nominal (Min - Max)</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="relative rounded-lg shadow-2xs">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                                            <span class="text-[11px] font-semibold text-slate-400">Rp</span>
                                        </div>
                                        <input type="text" name="min_amount" value="{{ $minAmount }}" placeholder="Minimum"
                                            class="h-[38px] w-full rounded-lg border border-slate-300 pl-8 pr-2 text-xs font-mono text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-colors">
                                    </div>
                                    <div class="relative rounded-lg shadow-2xs">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                                            <span class="text-[11px] font-semibold text-slate-400">Rp</span>
                                        </div>
                                        <input type="text" name="max_amount" value="{{ $maxAmount }}" placeholder="Maksimum"
                                            class="h-[38px] w-full rounded-lg border border-slate-300 pl-8 pr-2 text-xs font-mono text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-colors">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Aturan Kamus (Rule)</label>
                                <select name="rule_id"
                                    class="h-[38px] w-full text-xs rounded-lg border border-slate-300 px-3 bg-white text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-colors">
                                    <option value="all">Semua Aturan Kamus</option>
                                    @foreach($rules as $r)
                                        <option value="{{ $r->id }}" {{ (string)$ruleId === (string)$r->id ? 'selected' : '' }}>
                                            [{{ $r->kategori }}] {{ $r->nama_indikator }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Pencarian Teks Bebas</label>
                                <div class="relative rounded-lg shadow-2xs">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-search text-slate-400 text-xs"></i>
                                    </div>
                                    <input type="text" name="search" value="{{ $search }}" placeholder="Komentar, voucher, akun..."
                                        class="h-[38px] w-full rounded-lg border border-slate-300 pl-9 pr-3 text-xs text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Single Unified Bottom Action Toolbar --}}
                <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200/80 rounded-b-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    {{-- Left: Status Ringkasan & Reset --}}
                    <div class="flex items-center gap-3 flex-wrap text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="font-semibold text-slate-700">Filter Aktif:</span>
                            @if($activeAdvancedCount > 0)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                    {{ $activeAdvancedCount }} Parameter Spesifik
                                </span>
                            @else
                                <span class="text-slate-400 text-[11px] italic">Default (Semua Transaksi)</span>
                            @endif
                        </div>
                        <span class="text-slate-300 hidden sm:inline">•</span>
                        <a href="{{ route('journal_audit.index') }}"
                            class="inline-flex items-center gap-1 text-slate-500 hover:text-rose-600 text-xs font-medium transition hover:underline">
                            <i class="fas fa-rotate-left text-[10px]"></i>
                            <span>Reset Semua Filter</span>
                        </a>
                    </div>

                    {{-- Right: Pagination Per-Page, Export Excel, and Single Primary Apply Button --}}
                    <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                        <div class="flex items-center gap-1.5 text-xs text-slate-600">
                            <span class="text-[11px] text-slate-400 hidden md:inline">Baris:</span>
                            <select name="per_page"
                                class="h-[36px] text-xs rounded-lg border border-slate-300 px-2.5 bg-white text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition">
                                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 / hal</option>
                                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / hal</option>
                                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 / hal</option>
                                <option value="200" {{ $perPage == 200 ? 'selected' : '' }}>200 / hal</option>
                            </select>
                        </div>

                        <a href="{{ route('journal_audit.export', request()->query()) }}"
                            class="h-[36px] inline-flex items-center gap-1.5 px-3 rounded-lg border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-semibold shadow-2xs transition active:scale-[0.98]"
                            title="Download seluruh hasil audit ke format CSV / Excel">
                            <i class="fas fa-file-excel text-emerald-600 text-xs"></i>
                            <span>Export Excel</span>
                        </a>

                        <button type="submit"
                            class="h-[36px] inline-flex items-center gap-1.5 px-4 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition-all duration-150 active:scale-[0.98]">
                            <i class="fas fa-check text-xs"></i>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Table Container --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 bg-slate-50">
                <div class="text-xs font-semibold text-slate-600">
                    Menampilkan <span class="text-slate-900 font-bold">{{ $anomalies->firstItem() ?? 0 }}</span> - 
                    <span class="text-slate-900 font-bold">{{ $anomalies->lastItem() ?? 0 }}</span> dari 
                    <span class="text-slate-900 font-bold">{{ number_format($anomalies->total()) }}</span> transaksi
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('journal_audit.export', request()->query()) }}"
                        class="text-xs bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-lg font-semibold shadow-sm transition inline-flex items-center gap-1.5"
                        title="Unduh seluruh hasil audit terfilter ke file CSV/Excel">
                        <i class="fas fa-file-excel"></i> Export CSV/Excel ({{ number_format($anomalies->total()) }})
                    </a>
                    <button type="button" id="btnBulkRemediate" onclick="openBulkRemediateModal()"
                        class="hidden text-xs bg-rose-600 hover:bg-rose-500 text-white px-3 py-1.5 rounded-lg font-semibold shadow transition">
                        <i class="fas fa-wrench mr-1"></i> Bulk Re-assign Terpilih (<span id="selectedCount">0</span>)
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-audit">
                    <thead>
                        <tr>
                            <th class="py-3 px-3 w-8 text-center">
                                <input type="checkbox" id="checkAll" onclick="toggleSelectAll(this)"
                                    class="rounded border-slate-400 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="py-3 px-3">Tanggal / Voucher</th>
                            <th class="py-3 px-4">Keterangan (Comment Line)</th>
                            <th class="py-3 px-3 text-right">Debit / Kredit</th>
                            <th class="py-3 px-3">Akun Saat Ini</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-4">Rekomendasi Kamus &amp; Diagnosa</th>
                            <th class="py-3 px-3 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($anomalies as $row)
                            @php
                                $statusClass = [
                                    'SALAH_AKUN'  => 'bg-rose-100 text-rose-800 border-rose-300',
                                    'AMBIGU_BIAS' => 'bg-amber-100 text-amber-800 border-amber-300',
                                    'POLA_BARU'   => 'bg-slate-100 text-slate-800 border-slate-300',
                                    'TEPAT'       => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                ][$row->status] ?? 'bg-gray-100 text-gray-800 border-gray-300';

                                $amount = max((float)$row->debits, (float)$row->credits);
                                $isDebit = (float)$row->debits > 0;
                            @endphp
                            <tr class="hover:bg-slate-50 transition {{ $row->is_resolved ? 'bg-emerald-50/30' : '' }}" id="row-{{ $row->id }}">
                                <td class="py-3 px-3 text-center">
                                    @if(!$row->is_resolved && $row->recommended_kode_akun)
                                        <input type="checkbox" name="selected_ids[]" value="{{ $row->id }}"
                                            onchange="updateSelection()"
                                            class="anomaly-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-mono text-[11px] whitespace-nowrap">
                                    <div class="font-bold text-slate-800">{{ $row->tanggal ? $row->tanggal->format('d/m/Y') : '-' }}</div>
                                    <button type="button" 
                                        onclick="showVoucherQuickView({{ $row->journal_entry_id }}, {{ $row->journal_entry_detail_id }}, '{{ $row->voucher }}', {{ $row->id }})"
                                        class="inline-flex items-center gap-1 font-bold text-indigo-600 hover:text-indigo-800 hover:underline bg-indigo-50 hover:bg-indigo-100 px-1.5 py-0.5 rounded transition text-[10px] mt-0.5"
                                        title="Klik untuk melihat seluruh jurnal dan lawan akun voucher ini">
                                        <i class="fas fa-eye text-[9px]"></i>
                                        <span>{{ $row->voucher }}</span>
                                    </button>
                                </td>
                                <td class="py-3 px-4 max-w-xs">
                                    <div class="font-medium text-slate-800 break-words">{{ $row->comment ?: '(Komentar Kosong)' }}</div>
                                </td>
                                <td class="py-3 px-3 text-right font-mono whitespace-nowrap">
                                    @if($isDebit)
                                        <span class="text-emerald-700 font-semibold">Dr Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-rose-700 font-semibold">Cr Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <div class="font-mono font-bold text-slate-700">{{ $row->current_kode_akun }}</div>
                                    <div class="text-[11px] text-slate-500 truncate max-w-[180px]">{{ $row->current_nama_akun }}</div>
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClass }}">
                                        {{ str_replace('_', ' ', $row->status) }}
                                    </span>
                                    @if($row->is_resolved)
                                        <div class="text-[9px] text-emerald-600 font-medium mt-0.5"><i class="fas fa-check"></i> Selesai</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 max-w-sm">
                                    @if($row->recommended_kode_akun)
                                        <div class="flex items-center gap-1.5 mb-0.5">
                                            <i class="fas fa-arrow-right text-indigo-500 text-[10px]"></i>
                                            <span class="font-mono font-bold text-indigo-700">{{ $row->recommended_kode_akun }}</span>
                                            <span class="text-slate-700 font-medium text-[11px]">({{ $row->recommended_nama_akun }})</span>
                                        </div>
                                    @endif
                                    <div class="text-[11px] text-slate-600 leading-relaxed">{{ $row->alasan_diagnosa }}</div>
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    @if(!$row->is_resolved)
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button"
                                                onclick="openRemediateModal({{ $row->id }}, '{{ $row->voucher }}', '{{ addslashes($row->comment) }}', '{{ $row->current_kode_akun }}', '{{ addslashes($row->current_nama_akun) }}', '{{ $row->recommended_kode_akun }}', '{{ addslashes($row->recommended_nama_akun) }}', {{ json_encode($row->alternate_options) }})"
                                                class="px-2 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded font-medium text-[11px] transition shadow-sm">
                                                <i class="fas fa-wrench mr-0.5"></i> Perbaiki
                                            </button>
                                            <button type="button" onclick="ignoreRow({{ $row->id }})"
                                                title="Konfirmasi Benar / Abaikan"
                                                class="px-1.5 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded text-[11px] transition">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[11px] italic">Telah Diremediasi</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <div class="text-3xl mb-2"><i class="fas fa-circle-check text-emerald-400"></i></div>
                                    <div class="font-semibold text-slate-600">Tidak ada data anomali untuk filter ini</div>
                                    <div class="text-xs text-slate-400 mt-1">Gunakan tombol "Scan Jurnal Sekarang" untuk memulai pemindaian data transaksi.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($anomalies->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50">
                    {{ $anomalies->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL SCAN JURNAL --}}
<div id="modalScan" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-slate-200 overflow-hidden transform transition-all">
        <div class="bg-indigo-900 px-6 py-4 text-white flex justify-between items-center">
            <h3 class="font-bold text-base flex items-center gap-2">
                <i class="fas fa-magnifying-glass-chart text-indigo-400"></i> Pemindaian Jurnal (Scan)
            </h3>
            <button onclick="closeScanModal()" class="text-slate-300 hover:text-white">&times;</button>
        </div>
        <form id="formScan" onsubmit="executeScan(event)" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Rentang Tanggal</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" id="scan_start_date" class="text-xs rounded-lg border-slate-300 py-1.5 px-2.5">
                    <input type="date" name="end_date" id="scan_end_date" class="text-xs rounded-lg border-slate-300 py-1.5 px-2.5">
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Kosongkan untuk memindai seluruh tanggal di database.</div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Prefix Sumber / Rekening</label>
                <select name="source_prefix" id="scan_source_prefix" class="w-full text-xs rounded-lg border-slate-300 py-1.5 px-2.5">
                    <option value="">Semua Sumber Transaksi</option>
                    <option value="BNI7884">BNI 7884 (Kas Ops Ancol)</option>
                    <option value="BNI0365">BNI 0365 (Operasional)</option>
                    <option value="BNI6625">BNI 6625 (QRIS Dufan)</option>
                    <option value="BNI9741">BNI 9741 (QRIS ODS/JBL/AWA)</option>
                    <option value="MDR6215">Mandiri 6215 (Operasional)</option>
                    <option value="KASAML">Kas Amel (Kasir Pusat)</option>
                    <option value="KASDFN">Kas Dufan</option>
                    <option value="JAGO">Bank Jago Rahman</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Batasan Jumlah Data (Limit)</label>
                <select name="limit" class="w-full text-xs rounded-lg border-slate-300 py-1.5 px-2.5">
                    <option value="">Semua Transaksi (Batch)</option>
                    <option value="500">500 Baris Pertama</option>
                    <option value="1000">1.000 Baris Pertama</option>
                    <option value="5000">5.000 Baris Pertama</option>
                </select>
            </div>
            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeScanModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">Batal</button>
                <button type="submit" id="btnSubmitScan" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg shadow transition flex items-center gap-1.5">
                    <i class="fas fa-play"></i> Mulai Scan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL REMEDIATE / RE-ASSIGN --}}
<div id="modalRemediate" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full border border-slate-200 overflow-hidden transform transition-all">
        <div class="bg-indigo-900 px-6 py-4 text-white flex justify-between items-center">
            <h3 class="font-bold text-base flex items-center gap-2">
                <i class="fas fa-wrench text-amber-400"></i> Perbaikan Akun (Re-assign COA)
            </h3>
            <button onclick="closeRemediateModal()" class="text-slate-300 hover:text-white">&times;</button>
        </div>
        <form id="formRemediate" onsubmit="submitRemediate(event)" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="remediate_anomaly_id" name="anomaly_id">

            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs space-y-1">
                <div><span class="text-slate-400">Voucher:</span> <strong id="remediate_voucher" class="font-mono text-slate-800"></strong></div>
                <div><span class="text-slate-400">Keterangan:</span> <span id="remediate_comment" class="font-medium text-slate-800"></span></div>
                <div><span class="text-slate-400">Akun Sekarang:</span> <span id="remediate_current_account" class="font-mono text-rose-700 font-bold"></span></div>
            </div>

            <div id="remediate_recommendation_box" class="bg-indigo-50 p-3 rounded-lg border border-indigo-200 text-xs">
                <div class="text-indigo-900 font-semibold mb-1 flex items-center gap-1">
                    <i class="fas fa-lightbulb text-amber-500"></i> Rekomendasi Kamus:
                </div>
                <div id="remediate_rec_text" class="font-mono font-bold text-indigo-700 text-sm"></div>
                <div id="remediate_alternate_buttons" class="mt-2 flex flex-wrap gap-1.5"></div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Akun Pengganti Baru (COA)</label>
                <select name="new_kode_akun" id="remediate_new_kode_akun" required class="w-full text-xs rounded-lg border-slate-300 py-2 px-2.5">
                    @foreach($coas as $c)
                        <option value="{{ $c->kode_akun }}">{{ $c->kode_akun }} - {{ $c->nama_akun }} ({{ $c->tipe_akun }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Alasan Perbaikan</label>
                <input type="text" name="alasan" id="remediate_alasan" placeholder="Sesuai rekomendasi kamus / audit..."
                    class="w-full text-xs rounded-lg border-slate-300 py-1.5 px-2.5">
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeRemediateModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">Batal</button>
                <button type="submit" id="btnSubmitRemediate" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg shadow transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL QUICK-VIEW FULL VOUCHER --}}
<div id="modalVoucherQuickView" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[92vh] flex flex-col border border-slate-200 overflow-hidden transform transition-all">
        {{-- Modal Header --}}
        <div class="bg-slate-900 px-6 py-4 text-white flex justify-between items-center border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-500/20 text-indigo-300 rounded-lg border border-indigo-400/30">
                    <i class="fas fa-file-invoice text-lg"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-base text-white font-mono" id="qv_voucher">VOUCHER-NUMBER</h3>
                        <span id="qv_balance_badge" class="px-2 py-0.5 rounded text-[10px] font-bold">BALANCE</span>
                    </div>
                    <p class="text-slate-400 text-xs mt-0.5">
                        Tanggal: <span id="qv_tanggal" class="text-slate-200 font-semibold font-mono"></span> | 
                        Header: <span id="qv_header_comment" class="text-slate-300 italic"></span>
                    </p>
                </div>
            </div>
            <button onclick="closeVoucherQuickView()" class="text-slate-400 hover:text-white text-2xl leading-none">&times;</button>
        </div>

        {{-- Modal Body: Scrollable Table --}}
        <div class="p-4 sm:p-6 overflow-y-auto flex-1 bg-slate-50">
            {{-- Info alert --}}
            <div class="mb-3 p-3 bg-indigo-50 border border-indigo-200 rounded-lg text-xs text-indigo-900 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div class="flex items-center gap-2">
                    <i class="fas fa-circle-info text-indigo-500 text-sm"></i>
                    <span>Berikut seluruh baris akun Debit &amp; Kredit voucher ini. Baris dengan sorotan <strong class="text-rose-700">merah</strong> adalah transaksi anomali yang terdeteksi.</span>
                </div>
                <div class="flex items-center gap-2">
                    <a id="qv_btn_open_tab" href="#" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-white hover:bg-indigo-100 text-indigo-700 border border-indigo-300 rounded font-semibold text-xs shadow-sm transition whitespace-nowrap">
                        <i class="fas fa-arrow-up-right-from-square text-[11px]"></i> Buka di Tab Baru
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-800 text-white text-[11px] uppercase tracking-wider">
                            <th class="py-2.5 px-3 w-8 text-center">#</th>
                            <th class="py-2.5 px-3">Kode Akun</th>
                            <th class="py-2.5 px-3">Nama Akun (COA)</th>
                            <th class="py-2.5 px-3">Departemen</th>
                            <th class="py-2.5 px-3">Keterangan Baris</th>
                            <th class="py-2.5 px-3 text-right">Debit (Rp)</th>
                            <th class="py-2.5 px-3 text-right">Kredit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody id="qv_table_body" class="divide-y divide-slate-100">
                        {{-- Injected via JS --}}
                    </tbody>
                    <tfoot class="bg-slate-100 font-bold text-slate-800 border-t border-slate-300">
                        <tr>
                            <td colspan="5" class="py-2.5 px-3 text-right">TOTAL:</td>
                            <td class="py-2.5 px-3 text-right font-mono text-emerald-700" id="qv_total_debit">0</td>
                            <td class="py-2.5 px-3 text-right font-mono text-rose-700" id="qv_total_kredit">0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-3 bg-white border-t border-slate-200 flex justify-between items-center">
            <div class="text-xs text-slate-500">
                <span id="qv_line_count" class="font-bold text-slate-700">0</span> Baris Jurnal
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="qv_btn_remediate"
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-semibold shadow transition flex items-center gap-1.5">
                    <i class="fas fa-wrench"></i> Perbaiki Akun Ini
                </button>
                <button type="button" onclick="closeVoucherQuickView()"
                    class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-semibold transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const scanUrl = "{{ route('journal_audit.scan') }}";
    const remediateUrl = "{{ route('journal_audit.remediate') }}";
    const remediateBulkUrl = "{{ route('journal_audit.remediate_bulk') }}";

    function openScanModal() {
        document.getElementById('modalScan').classList.remove('hidden');
    }
    function closeScanModal() {
        document.getElementById('modalScan').classList.add('hidden');
    }

    function executeScan(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitScan');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memindai...';

        const formData = new FormData(document.getElementById('formScan'));

        fetch(scanUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => res.json())
        .then(data => {
            closeScanModal();
            Swal.fire({
                icon: 'success',
                title: 'Scan Selesai!',
                text: data.message,
                confirmButtonColor: '#4f46e5'
            }).then(() => {
                window.location.reload();
            });
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-play"></i> Mulai Scan';
            Swal.fire('Error', 'Gagal memindai: ' + err.message, 'error');
        });
    }

    function openRemediateModal(id, voucher, comment, curCode, curName, recCode, recName, alternates) {
        document.getElementById('remediate_anomaly_id').value = id;
        document.getElementById('remediate_voucher').textContent = voucher;
        document.getElementById('remediate_comment').textContent = comment;
        document.getElementById('remediate_current_account').textContent = curCode + ' - ' + curName;

        const recBox = document.getElementById('remediate_recommendation_box');
        const altContainer = document.getElementById('remediate_alternate_buttons');
        altContainer.innerHTML = '';

        if (recCode) {
            recBox.classList.remove('hidden');
            document.getElementById('remediate_rec_text').textContent = recCode + ' - ' + recName;
            document.getElementById('remediate_new_kode_akun').value = recCode;
        } else {
            recBox.classList.add('hidden');
            document.getElementById('remediate_new_kode_akun').value = curCode;
        }

        if (alternates && alternates.length > 0) {
            alternates.forEach(alt => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'px-2 py-0.5 rounded bg-indigo-100 hover:bg-indigo-200 text-indigo-800 text-[10px] font-mono font-bold transition';
                btn.textContent = 'Gunakan ' + alt;
                btn.onclick = () => {
                    document.getElementById('remediate_new_kode_akun').value = alt;
                };
                altContainer.appendChild(btn);
            });
        }

        document.getElementById('modalRemediate').classList.remove('hidden');
    }

    function closeRemediateModal() {
        document.getElementById('modalRemediate').classList.add('hidden');
    }

    function submitRemediate(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitRemediate');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const formData = new FormData(document.getElementById('formRemediate'));

        fetch(remediateUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = 'Simpan Perubahan';
            if (data.success) {
                closeRemediateModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = 'Simpan Perubahan';
            Swal.fire('Error', 'Terjadi kesalahan: ' + err.message, 'error');
        });
    }

    function ignoreRow(id) {
        Swal.fire({
            title: 'Konfirmasi Transaksi',
            text: 'Tandai transaksi ini sudah sesuai/diabaikan dari daftar anomali?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Ya, Konfirmasi Valid'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('/journal-audit/ignore') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(res => res.json())
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terkonfirmasi',
                        text: data.message,
                        timer: 1200,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                });
            }
        });
    }

    function toggleSelectAll(master) {
        document.querySelectorAll('.anomaly-checkbox').forEach(cb => {
            cb.checked = master.checked;
        });
        updateSelection();
    }

    function updateSelection() {
        const checked = document.querySelectorAll('.anomaly-checkbox:checked');
        const btn = document.getElementById('btnBulkRemediate');
        const countSpan = document.getElementById('selectedCount');
        countSpan.textContent = checked.length;

        if (checked.length > 0) {
            btn.classList.remove('hidden');
        } else {
            btn.classList.add('hidden');
        }
    }

    function openBulkRemediateModal() {
        const checked = Array.from(document.querySelectorAll('.anomaly-checkbox:checked')).map(cb => cb.value);
        if (checked.length === 0) return;

        Swal.fire({
            title: 'Bulk Re-assign Otomatis?',
            text: `Perbaiki ${checked.length} transaksi yang dipilih ke akun rekomendasi masing-masing secara serentak?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: `Ya, Re-assign ${checked.length} Data!`,
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch(remediateBulkUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ anomaly_ids: checked })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Berhasil!', data.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error', err.message, 'error');
                });
            }
        });
    }

    // ── QUICK-VIEW FULL VOUCHER MODAL ───────────────────────────────────────
    function showVoucherQuickView(journalEntryId, highlightedDetailId, voucher, anomalyId) {
        Swal.fire({
            title: 'Memuat Transaksi...',
            text: 'Mengambil seluruh baris jurnal untuk voucher ' + voucher,
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch(`{{ url('/journal_entry') }}/${journalEntryId}/quick-view`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Gagal memuat detail voucher.');
            return res.json();
        })
        .then(data => {
            Swal.close();
            document.getElementById('qv_voucher').textContent = data.source;
            document.getElementById('qv_tanggal').textContent = data.tanggal;
            document.getElementById('qv_header_comment').textContent = data.comment || '-';
            document.getElementById('qv_total_debit').textContent = data.total_debits_formatted;
            document.getElementById('qv_total_kredit').textContent = data.total_credits_formatted;
            document.getElementById('qv_line_count').textContent = data.details.length;
            document.getElementById('qv_btn_open_tab').href = data.show_url;

            const badge = document.getElementById('qv_balance_badge');
            if (data.is_balanced) {
                badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30';
                badge.textContent = 'SEIMBANG (BALANCE)';
            } else {
                badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-400/30';
                badge.textContent = 'TIDAK BALANCE!';
            }

            const tbody = document.getElementById('qv_table_body');
            tbody.innerHTML = '';

            data.details.forEach((d, idx) => {
                const tr = document.createElement('tr');
                const isTarget = (d.id == highlightedDetailId);

                if (isTarget) {
                    tr.className = 'bg-rose-50/90 border-l-4 border-rose-500 font-semibold transition';
                } else {
                    tr.className = 'hover:bg-slate-50 transition';
                }

                tr.innerHTML = `
                    <td class="py-2.5 px-3 text-center font-mono ${isTarget ? 'text-rose-700 font-bold' : 'text-slate-400'}">${idx + 1}</td>
                    <td class="py-2.5 px-3 font-mono font-bold ${isTarget ? 'text-rose-700' : 'text-slate-800'}">
                        ${d.kode_akun}
                        ${isTarget ? '<span class="ml-1 px-1.5 py-0.5 rounded bg-rose-200 text-rose-900 text-[9px] font-bold uppercase tracking-tight">Anomali Terdeteksi</span>' : ''}
                    </td>
                    <td class="py-2.5 px-3 font-medium ${isTarget ? 'text-rose-900 font-bold' : 'text-slate-700'}">${d.nama_akun}</td>
                    <td class="py-2.5 px-3 text-slate-500 text-[11px]">${d.departemen}</td>
                    <td class="py-2.5 px-3 text-slate-600 text-[11px] max-w-xs break-words">${d.comment}</td>
                    <td class="py-2.5 px-3 text-right font-mono ${d.debits > 0 ? 'text-emerald-700 font-semibold' : 'text-slate-400'}">${d.debits > 0 ? d.debits_formatted : '-'}</td>
                    <td class="py-2.5 px-3 text-right font-mono ${d.credits > 0 ? 'text-rose-700 font-semibold' : 'text-slate-400'}">${d.credits > 0 ? d.credits_formatted : '-'}</td>
                `;
                tbody.appendChild(tr);
            });

            // Set action button for this anomaly
            const remediateBtn = document.getElementById('qv_btn_remediate');
            if (anomalyId) {
                remediateBtn.classList.remove('hidden');
                remediateBtn.onclick = () => {
                    closeVoucherQuickView();
                    const rowBtn = document.querySelector(`#row-${anomalyId} button[onclick^="openRemediateModal"]`);
                    if (rowBtn) {
                        rowBtn.click();
                    }
                };
            } else {
                remediateBtn.classList.add('hidden');
            }

            document.getElementById('modalVoucherQuickView').classList.remove('hidden');
        })
        .catch(err => {
            Swal.fire('Error', err.message, 'error');
        });
    }

    function closeVoucherQuickView() {
        document.getElementById('modalVoucherQuickView').classList.add('hidden');
    }

    // Inisialisasi Select2 untuk Filter Akun
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('.select2-filter').select2({
                placeholder: 'Pilih Akun...',
                allowClear: true,
                width: '100%'
            });
        }
    });
</script>
@endpush
@endsection

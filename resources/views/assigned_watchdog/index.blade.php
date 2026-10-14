{{-- resources/views/assigned_watchdog/index.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    .watchdog-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
        position: relative;
        overflow: hidden;
    }
    .watchdog-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(99,102,241,0.18) 0%, transparent 70%);
        pointer-events: none;
    }
    .kpi-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }
    .bulk-toolbar {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(140px);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 50;
    }
    .bulk-toolbar.active {
        transform: translateX(-50%) translateY(0);
    }
    .table-watchdog th {
        font-size: 0.73rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        background-color: #1e293b;
        color: #f8fafc;
        border-bottom: 2px solid #334155;
    }
    .dept-pill {
        transition: all 0.15s ease-in-out;
    }
    .dept-pill:hover {
        transform: translateY(-1px);
    }
    .dept-pill.active {
        background-color: #4f46e5 !important;
        color: #ffffff !important;
        border-color: #4338ca !important;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.25);
    }
    .dept-pill.active .pill-count {
        background-color: #ffffff !important;
        color: #4338ca !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-800 pb-32">

    {{-- Header Banner --}}
    <div class="watchdog-header text-white px-6 py-8 mb-6 shadow-lg">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">
                        <i class="fas fa-shield-alt mr-1"></i> AUDIT &amp; GOVERNANCE
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/30 text-emerald-200 border border-emerald-400/30">
                        <i class="fas fa-check-double mr-1"></i> ASSIGNED WATCHDOG
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white flex items-center gap-2">
                    <span>Assigned Watchdog</span>
                </h1>
                <p class="text-slate-300 text-sm mt-1">
                    Pusat Kendali, Inspeksi Anomali &amp; Penyesuaian Alokasi Departemen Transaksi Jurnal (<code class="text-indigo-300 font-mono">departemen_akun_id IS NOT NULL</code>)
                </p>
            </div>

            @php
                $queryParams = request()->query();
                $exportExcelUrl = route('assigned_watchdog.export', array_merge($queryParams, ['format' => 'excel']));
                $exportPdfUrl = route('assigned_watchdog.export', array_merge($queryParams, ['format' => 'pdf']));
                $resetUrl = route('assigned_watchdog.index');
            @endphp

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('assigned_watchdog.audit_logs') }}"
                   class="inline-flex items-center px-3.5 py-2 bg-indigo-600/80 hover:bg-indigo-600 text-white rounded-lg text-xs font-semibold shadow-md transition-colors duration-150">
                    <i class="fas fa-history mr-2 text-indigo-200"></i> Audit Logs
                </a>
                <a href="{{ $exportExcelUrl }}"
                   class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-semibold shadow-md transition-colors duration-150">
                    <i class="fas fa-file-excel mr-2 text-white"></i> Export Excel
                </a>
                <a href="{{ $exportPdfUrl }}" target="_blank"
                   class="inline-flex items-center px-3.5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-semibold shadow-md transition-colors duration-150">
                    <i class="fas fa-file-pdf mr-2 text-white"></i> Export PDF
                </a>
                <a href="{{ $resetUrl }}"
                   class="inline-flex items-center px-3.5 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-xs font-semibold shadow-md transition-colors duration-150">
                    <i class="fas fa-redo-alt mr-2 text-slate-300"></i> Reset
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- KPI Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Card 1: Total Assigned --}}
            <div class="kpi-card bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Transaksi Assigned</span>
                        <div class="text-2xl font-bold text-slate-900 mt-1">
                            {{ number_format($kpis['total_assigned'] ?? $kpis['total'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-inner">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
                <div class="mt-3 text-xs text-slate-500 flex items-center gap-1.5">
                    <span class="inline-flex items-center text-emerald-600 font-semibold">
                        <i class="fas fa-check-circle mr-1"></i> Terdistribusi
                    </span>
                    <span>ke unit departemen aktif</span>
                </div>
            </div>

            {{-- Card 2: Anomalies Warning --}}
            <div class="kpi-card bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Terdeteksi Anomali</span>
                        <div class="text-2xl font-bold {{ ($kpis['total_anomalies'] ?? 0) > 0 ? 'text-amber-600' : 'text-emerald-600' }} mt-1">
                            {{ number_format($kpis['total_anomalies'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-xl {{ ($kpis['total_anomalies'] ?? 0) > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center text-xl shadow-inner">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="mt-3 text-xs flex items-center justify-between">
                    <span class="text-slate-500">3 Aturan Deteksi (Prefix, Outlier, COA)</span>
                    @if(($kpis['total_anomalies'] ?? 0) > 0)
                        <a href="{{ route('assigned_watchdog.index', array_merge(request()->except(['anomalies_only', 'page']), ['anomalies_only' => 1])) }}"
                           class="text-amber-600 font-bold hover:underline">
                            Lihat &rarr;
                        </a>
                    @endif
                </div>
            </div>

            {{-- Card 3: Real vs Nominal --}}
            <div class="kpi-card bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Akun Real vs Nominal</span>
                        <div class="text-lg font-bold text-slate-900 mt-1 flex items-center gap-2">
                            <span class="text-blue-700 font-mono">{{ number_format($kpis['real_accounts'] ?? $kpis['total_real'] ?? 0, 0, ',', '.') }}</span>
                            <span class="text-slate-300 font-normal">/</span>
                            <span class="text-purple-700 font-mono">{{ number_format($kpis['nominal_accounts'] ?? $kpis['total_nominal'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shadow-inner">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                </div>
                <div class="mt-3 text-xs text-slate-500 flex items-center justify-between">
                    <span><strong class="text-blue-700">Neraca</strong> (Aset/Kew/Ekuitas)</span>
                    <span><strong class="text-purple-700">L/R</strong> (Pend/Beban)</span>
                </div>
            </div>

            {{-- Card 4: Total Value Debit & Credit --}}
            <div class="kpi-card bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Nilai Mutasi</span>
                        <div class="text-xs font-bold text-slate-800 mt-1 font-mono">
                            <span class="text-slate-500 font-normal">D:</span> Rp {{ number_format($kpis['total_debit'] ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="text-xs font-bold text-slate-800 mt-0.5 font-mono">
                            <span class="text-slate-500 font-normal">K:</span> Rp {{ number_format($kpis['total_credit'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl shadow-inner">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
                <div class="mt-3 text-xs text-slate-500">
                    <span>Cakupan filter aktif saat ini</span>
                </div>
            </div>
        </div>

        {{-- Quick-Filter Department Pills Bar --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 mb-6">
            <div class="flex items-center justify-between mb-2 px-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                    <i class="fas fa-building text-indigo-600"></i> Filter Cepat Departemen (Pills)
                </span>
                <span class="text-[11px] text-slate-400">Klik unit untuk filter instan</span>
            </div>

            <div class="flex flex-wrap items-center gap-2 overflow-x-auto pb-1">
                {{-- All Departments Pill --}}
                @php
                    $allQuery = array_merge(request()->except(['departemen_id', 'page']), ['per_page' => $perPage ?? 50]);
                    $isAllActive = empty($selectedDeptId);
                @endphp
                <a href="{{ route('assigned_watchdog.index', $allQuery) }}"
                   class="dept-pill inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold border {{ $isAllActive ? 'active' : 'bg-slate-50 text-slate-700 border-slate-300 hover:bg-slate-100' }}">
                    <span>Semua Unit</span>
                    <span class="pill-count ml-2 px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $isAllActive ? 'bg-white text-indigo-700' : 'bg-slate-200 text-slate-800' }}">
                        {{ number_format($kpis['total_assigned'] ?? $kpis['total'] ?? 0, 0, ',', '.') }}
                    </span>
                </a>

                {{-- Per Department Pills --}}
                @foreach($deptPills as $pill)
                    @php
                        $pillId = is_array($pill) ? $pill['id'] : $pill->id;
                        $pillKode = is_array($pill) ? $pill['kode'] : $pill->kode;
                        $pillDeskripsi = is_array($pill) ? ($pill['deskripsi'] ?? $pill['nama']) : ($pill->deskripsi ?? $pill->nama);
                        $pillCount = is_array($pill) ? $pill['count'] : $pill->count;
                        $isActive = ($selectedDeptId == $pillId);
                        $pillQuery = array_merge(request()->except(['page']), [
                            'departemen_id' => $pillId,
                            'per_page'      => $perPage ?? 50
                        ]);
                    @endphp
                    <a href="{{ route('assigned_watchdog.index', $pillQuery) }}"
                       class="dept-pill inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold border {{ $isActive ? 'active' : 'bg-slate-50 text-slate-700 border-slate-300 hover:bg-slate-100' }}">
                        <span>{{ $pillDeskripsi }}</span>
                        <span class="pill-count ml-2 px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $isActive ? 'bg-white text-indigo-700' : 'bg-slate-200 text-slate-800' }}">
                            {{ number_format($pillCount, 0, ',', '.') }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Inspector Multi-Facet Filter Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-6" x-data="{ advancedOpen: true }">
            <form method="GET" action="{{ route('assigned_watchdog.index') }}" id="filterForm">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                    {{-- Departemen --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Departemen</label>
                        <select name="departemen_id" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Departemen</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $selectedDeptId == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->kode }} - {{ $dept->deskripsi }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal Mulai --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                               class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Tanggal Selesai --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                               class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Kategori (Real vs Nominal) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Kategori Akun</label>
                        <select name="kategori" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all" {{ $kategori === 'all' || empty($kategori) ? 'selected' : '' }}>Semua Kategori</option>
                            <option value="real" {{ $kategori === 'real' ? 'selected' : '' }}>Real (Neraca)</option>
                            <option value="nominal" {{ $kategori === 'nominal' ? 'selected' : '' }}>Nominal (Laba Rugi)</option>
                        </select>
                    </div>

                    {{-- Tipe Akun --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Tipe Akun COA</label>
                        <select name="tipe_akun" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all">Semua Tipe</option>
                            @foreach($tipeAkunList as $t)
                                <option value="{{ $t }}" {{ $tipeAkun === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Voucher Prefix --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Prefix Voucher</label>
                        <select name="source_prefix" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all">Semua Prefix</option>
                            @foreach($sourcePrefixes as $sp)
                                @if(!empty($sp->prefix))
                                    <option value="{{ $sp->prefix }}" {{ $sourcePrefix === $sp->prefix ? 'selected' : '' }}>
                                        {{ $sp->prefix }} ({{ $sp->count }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Per Page (Tampilkan) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Tampilkan</label>
                        <select name="per_page" id="filterPerPage" onchange="onPerPageChange(this.value)"
                                class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 baris</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 baris</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 baris</option>
                            <option value="250" {{ $perPage == 250 ? 'selected' : '' }}>250 baris</option>
                            <option value="500" {{ $perPage == 500 ? 'selected' : '' }}>500 baris</option>
                        </select>
                    </div>
                </div>

                {{-- Second row: Search, Anomalies toggle, Buttons --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mt-3 pt-3 border-t border-slate-100 items-center">
                    <div class="md:col-span-6">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-3 text-slate-400 text-xs"></i>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor voucher, kode/nama akun, atau keterangan..."
                                   class="w-full text-xs border border-slate-300 rounded-lg pl-8 pr-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="md:col-span-3 flex items-center">
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="anomalies_only" value="1" {{ $anomaliesOnly ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-xs font-bold {{ $anomaliesOnly ? 'text-amber-700' : 'text-slate-700' }}">
                                <i class="fas fa-filter text-amber-500 mr-1"></i> Hanya Tampilkan Anomali
                            </span>
                        </label>
                    </div>

                    <div class="md:col-span-3 flex items-center justify-end gap-2">
                        <a href="{{ route('assigned_watchdog.index') }}"
                           class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                            Reset
                        </a>
                        <button type="submit"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors">
                            <i class="fas fa-filter mr-1.5"></i> Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Main Data Table Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-8">
            <div class="px-5 py-4 border-b border-slate-200 flex flex-wrap justify-between items-center bg-slate-50/50 gap-2">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-600">
                        Daftar Transaksi Terdata
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                        {{ number_format($filteredCount, 0, ',', '.') }} Transaksi
                    </span>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-600 font-medium">Tampilkan:</span>
                        <select onchange="onPerPageChange(this.value)"
                                class="text-xs border border-slate-300 rounded-lg px-2 py-1 bg-white font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                            <option value="250" {{ $perPage == 250 ? 'selected' : '' }}>250</option>
                            <option value="500" {{ $perPage == 500 ? 'selected' : '' }}>500</option>
                        </select>
                    </div>
                    <span class="text-slate-300">|</span>
                    <span>
                        Menampilkan {{ $details->firstItem() ?? 0 }} - {{ $details->lastItem() ?? 0 }} dari {{ number_format($filteredCount, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-watchdog text-xs">
                    <thead>
                        <tr>
                            <th class="py-3 px-3 text-center w-10">
                                <input type="checkbox" id="selectAllCheckbox" class="rounded border-slate-400 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="py-3 px-3 text-center w-12">NO</th>
                            <th class="py-3 px-3">Voucher</th>
                            <th class="py-3 px-3">Tanggal</th>
                            <th class="py-3 px-3">Akun COA</th>
                            <th class="py-3 px-3 text-right">Debit</th>
                            <th class="py-3 px-3 text-right">Kredit</th>
                            <th class="py-3 px-3">Departemen Saat Ini</th>
                            <th class="py-3 px-3">Deteksi Anomali</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="assignedTableBody">
                        @forelse($details as $index => $detail)
                            @php
                                $isAnomaly = !empty($detail->anomaly['is_anomaly']);
                                $rules = $detail->anomaly['rules'] ?? [];
                                $reasons = $detail->anomaly['reasons'] ?? [];
                                $rowBg = $isAnomaly ? 'bg-amber-50/30 hover:bg-amber-50/60' : 'hover:bg-slate-50/80';
                                $currentDept = $detail->departemenAkun && $detail->departemenAkun->departemen ? $detail->departemenAkun->departemen : null;
                            @endphp
                            <tr class="{{ $rowBg }} transition-colors" id="row-detail-{{ $detail->id }}">
                                {{-- Checkbox --}}
                                <td class="py-3 px-3 text-center">
                                    <input type="checkbox" class="row-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                           value="{{ $detail->id }}"
                                           data-id="{{ $detail->id }}"
                                           data-closed="{{ $detail->is_closed_period ? '1' : '0' }}"
                                           data-voucher="{{ $detail->journalEntry ? $detail->journalEntry->source : '' }}">
                                </td>

                                {{-- Number (NO) --}}
                                <td class="py-3 px-3 text-center text-slate-400 font-mono text-xs">
                                    {{ $details->firstItem() + $index }}
                                </td>

                                {{-- Voucher --}}
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="font-bold text-slate-800">{{ $detail->journalEntry ? $detail->journalEntry->source : '-' }}</span>
                                    @if($detail->comment || ($detail->journalEntry && $detail->journalEntry->comment))
                                        <span class="text-slate-400 text-[10px] block truncate max-w-[140px]" title="{{ $detail->comment ?: $detail->journalEntry->comment }}">
                                            {{ $detail->comment ?: $detail->journalEntry->comment }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Tanggal --}}
                                <td class="py-3 px-3 whitespace-nowrap text-slate-600 font-mono text-[11px]">
                                    {{ $detail->journalEntry ? \Carbon\Carbon::parse($detail->journalEntry->tanggal)->format('d/m/Y') : '-' }}
                                </td>

                                {{-- Akun COA --}}
                                <td class="py-3 px-3">
                                    <span class="font-mono font-bold text-slate-800">{{ $detail->kode_akun }}</span>
                                    <span class="text-slate-500 text-[11px] block truncate max-w-[170px]" title="{{ $detail->chartOfAccount ? $detail->chartOfAccount->nama_akun : '' }}">
                                        {{ $detail->chartOfAccount ? $detail->chartOfAccount->nama_akun : '' }}
                                    </span>
                                </td>

                                {{-- Debit --}}
                                <td class="py-3 px-3 text-right font-mono font-medium text-slate-900 whitespace-nowrap">
                                    {{ $detail->debits > 0 ? number_format($detail->debits, 0, ',', '.') : '-' }}
                                </td>

                                {{-- Kredit --}}
                                <td class="py-3 px-3 text-right font-mono font-medium text-slate-900 whitespace-nowrap">
                                    {{ $detail->credits > 0 ? number_format($detail->credits, 0, ',', '.') : '-' }}
                                </td>

                                {{-- Current Department --}}
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @if($currentDept)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                            <i class="fas fa-building mr-1 text-blue-500"></i> {{ $currentDept->kode }} - {{ $currentDept->deskripsi }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
                                            Tanpa Dept
                                        </span>
                                    @endif
                                </td>

                                {{-- Deteksi Anomali --}}
                                <td class="py-3 px-3">
                                    @if($isAnomaly)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($rules as $idx => $rule)
                                                @php
                                                    $ruleLabel = 'Anomali';
                                                    $ruleClass = 'bg-red-100 text-red-800 border-red-300';
                                                    $ruleIcon = 'fa-exclamation-triangle';
                                                    if ($rule === 'rule_1_prefix_mismatch') {
                                                        $ruleLabel = 'Prefix Mismatch';
                                                        $ruleClass = 'bg-red-100 text-red-800 border-red-300';
                                                        $ruleIcon = 'fa-tag';
                                                    } elseif ($rule === 'rule_2_voucher_inconsistency') {
                                                        $ruleLabel = 'Inkonsistensi';
                                                        $ruleClass = 'bg-amber-100 text-amber-800 border-amber-300';
                                                        $ruleIcon = 'fa-code-branch';
                                                    } elseif ($rule === 'rule_3_unit_account_mismatch') {
                                                        $ruleLabel = 'Akun Unit Mismatch';
                                                        $ruleClass = 'bg-purple-100 text-purple-800 border-purple-300';
                                                        $ruleIcon = 'fa-landmark';
                                                    }
                                                    $reasonDesc = $reasons[$idx] ?? 'Anomali alokasi departemen';
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border {{ $ruleClass }}"
                                                      title="{{ $reasonDesc }}" data-toggle="tooltip">
                                                    <i class="fas {{ $ruleIcon }} mr-1"></i> {{ $ruleLabel }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <i class="fas fa-check-circle mr-1"></i> Valid
                                        </span>
                                    @endif
                                </td>

                                {{-- Status Periode (Lock) --}}
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    @if($detail->is_closed_period)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300"
                                              title="Terkunci: Transaksi berada di periode tutup buku">
                                            <i class="fas fa-lock mr-1 text-rose-600"></i> Closed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            <i class="fas fa-lock-open mr-1 text-slate-400"></i> Open
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi Buttons --}}
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center space-x-1">
                                        {{-- Reassign button --}}
                                        <button type="button"
                                                class="btn-reassign p-1.5 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded transition-colors"
                                                title="Alihkan Departemen (Reassign)"
                                                data-id="{{ $detail->id }}"
                                                data-current-dept="{{ $currentDept ? $currentDept->id : '' }}"
                                                data-closed="{{ $detail->is_closed_period ? '1' : '0' }}">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>

                                        {{-- Unassign button --}}
                                        <button type="button"
                                                class="btn-unassign p-1.5 text-rose-600 hover:text-rose-900 hover:bg-rose-50 rounded transition-colors"
                                                title="Lepaskan Departemen (Unassign ke NULL)"
                                                data-id="{{ $detail->id }}"
                                                data-closed="{{ $detail->is_closed_period ? '1' : '0' }}">
                                            <i class="fas fa-unlink"></i>
                                        </button>

                                        {{-- Audit history button --}}
                                        <button type="button"
                                                class="btn-history p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded transition-colors"
                                                title="Lihat Riwayat Perubahan (Audit History)"
                                                data-id="{{ $detail->id }}">
                                            <i class="fas fa-history"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-16 text-slate-500">
                                    <i class="fas fa-check-double text-4xl text-slate-300 mb-3"></i>
                                    <p class="text-sm font-semibold text-slate-700">Tidak ada transaksi yang cocok dengan filter aktif.</p>
                                    <p class="text-xs text-slate-400 mt-1">Coba ubah kriteria filter atau reset filter pencarian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($details->hasPages())
                <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $details->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Floating Bulk Action Toolbar --}}
    <div id="bulkToolbar" class="bulk-toolbar bg-slate-900/95 text-white px-6 py-3.5 rounded-2xl shadow-2xl border border-slate-700 backdrop-blur-md flex items-center gap-4">
        <div class="flex items-center gap-2 pr-4 border-r border-slate-700">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span id="selectedCount" class="font-bold text-sm text-emerald-300">0</span>
            <span class="text-xs text-slate-300">item dipilih</span>
        </div>

        {{-- Department Select for Bulk Reassign --}}
        <div class="flex items-center gap-2">
            <select id="bulkDeptSelect" class="text-xs bg-slate-800 text-white border border-slate-600 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">-- Pilih Departemen Tujuan --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->kode }} - {{ $dept->deskripsi }}</option>
                @endforeach
            </select>

            <button type="button" id="btnBulkReassign"
                    class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-bold shadow-md transition-colors">
                <i class="fas fa-exchange-alt mr-1"></i> Reassign Masal
            </button>
        </div>

        <div class="flex items-center gap-2 pl-4 border-l border-slate-700">
            <button type="button" id="btnBulkUnassign"
                    class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-bold shadow-md transition-colors">
                <i class="fas fa-unlink mr-1"></i> Unassign Masal
            </button>

            <button type="button" id="btnBulkCancel"
                    class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-xs font-semibold transition-colors">
                Batal
            </button>
        </div>
    </div>

    {{-- Modal Container for Audit History --}}
    <div id="historyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="px-5 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-history text-indigo-600"></i> Audit History Detail Transaksi
                </h3>
                <button type="button" id="closeHistoryModal" class="text-slate-400 hover:text-slate-600 text-lg">
                    &times;
                </button>
            </div>
            <div id="historyModalContent" class="overflow-y-auto flex-1 p-2">
                <div class="text-center py-12 text-slate-400">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <p class="text-xs">Memuat riwayat perubahan...</p>
                </div>
            </div>
            <div class="px-5 py-3 border-t border-slate-200 bg-slate-50 flex justify-end">
                <button type="button" id="closeHistoryModalBtn" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-semibold">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
window.onPerPageChange = function (val) {
    const form = document.getElementById('filterForm');
    if (form) {
        let perPageInput = form.querySelector('select[name="per_page"]');
        if (perPageInput) perPageInput.value = val;

        let pageInput = form.querySelector('input[name="page"]');
        if (pageInput) {
            pageInput.value = 1;
        }
        form.submit();
    } else {
        let url = new URL(window.location.href);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }
};

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        }
    });

    const canOverrideClosed = {{ auth()->check() && auth()->user()->can('assigned_watchdog.override_closed') ? 'true' : 'false' }};
    const departmentsList = @json($departments);

    function updateBulkToolbar() {
        const checked = $('.row-checkbox:checked');
        const count = checked.length;
        $('#selectedCount').text(count);
        if (count > 0) {
            $('#bulkToolbar').addClass('active');
        } else {
            $('#bulkToolbar').removeClass('active');
        }
    }

    // Select all checkbox
    $('#selectAllCheckbox').on('change', function () {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked);
        updateBulkToolbar();
    });

    // Individual checkbox
    $(document).on('change', '.row-checkbox', function () {
        updateBulkToolbar();
        const allChecked = $('.row-checkbox').length === $('.row-checkbox:checked').length;
        $('#selectAllCheckbox').prop('checked', allChecked);
    });

    // Cancel selection
    $('#btnBulkCancel').on('click', function () {
        $('.row-checkbox').prop('checked', false);
        $('#selectAllCheckbox').prop('checked', false);
        updateBulkToolbar();
    });

    // Helper: Build department options HTML
    function buildDeptOptions(selectedDeptId) {
        let options = '';
        departmentsList.forEach(function(d) {
            const isSel = (selectedDeptId && selectedDeptId == d.id) ? 'selected' : '';
            options += `<option value="${d.id}" ${isSel}>${d.kode} - ${d.deskripsi}</option>`;
        });
        return options;
    }

    // -------------------------------------------------------------
    // Single Row Reassign
    // -------------------------------------------------------------
    $(document).on('click', '.btn-reassign', function () {
        const detailId = $(this).data('id');
        const currentDeptId = $(this).data('current-dept');
        const isClosed = $(this).data('closed') == '1';

        let closedWarningHtml = '';
        if (isClosed) {
            closedWarningHtml = `
                <div class="mb-3 p-2.5 bg-rose-50 border border-rose-300 rounded text-left text-xs text-rose-800">
                    <i class="fas fa-lock text-rose-600 mr-1"></i>
                    <strong>Peringatan Periode Ditutup:</strong> Transaksi ini berada pada periode buku yang telah ditutup (Closing).
                    ${canOverrideClosed
                        ? '<div class="mt-1 font-semibold text-rose-900"><i class="fas fa-check-circle"></i> Anda memiliki hak akses override_closed.</div>'
                        : '<div class="mt-1 text-rose-700"><i class="fas fa-times-circle"></i> Anda tidak memiliki izin override_closed. Permintaan akan ditolak.</div>'
                    }
                </div>
            `;
        }

        Swal.fire({
            title: 'Pengalihan Departemen (Reassign)',
            html: `
                ${closedWarningHtml}
                <div class="text-left text-xs mb-3">
                    <label class="font-bold text-slate-700 block mb-1">Pilih Departemen Baru:</label>
                    <select id="swal_new_dept" class="w-full text-xs border border-slate-300 rounded p-2">
                        <option value="">-- Pilih Departemen --</option>
                        ${buildDeptOptions(currentDeptId)}
                    </select>
                </div>
                <div class="text-left text-xs">
                    <label class="font-bold text-slate-700 block mb-1">Alasan Penyesuaian (Wajib diisi):</label>
                    <textarea id="swal_reason" class="w-full text-xs border border-slate-300 rounded p-2" rows="3" placeholder="Jelaskan alasan pengalihan alokasi departemen..."></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Simpan Penyesuaian',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#4f46e5',
            focusConfirm: false,
            preConfirm: () => {
                const newDept = $('#swal_new_dept').val();
                const reason = $('#swal_reason').val().trim();
                if (!newDept) {
                    Swal.showValidationMessage('Silakan pilih departemen baru.');
                    return false;
                }
                if (!reason) {
                    Swal.showValidationMessage('Alasan penyesuaian wajib diisi.');
                    return false;
                }
                return { newDept: newDept, reason: reason };
            }
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Menyimpan perubahan alokasi departemen...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '{{ route("assigned_watchdog.reassign") }}',
                    type: 'POST',
                    data: {
                        detail_id: detailId,
                        departemen_id: res.value.newDept,
                        reason: res.value.reason,
                        override_closed: isClosed && canOverrideClosed ? 1 : 0
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Departemen berhasil dialihkan.',
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
                    }
                });
            }
        });
    });

    // -------------------------------------------------------------
    // Single Row Unassign
    // -------------------------------------------------------------
    $(document).on('click', '.btn-unassign', function () {
        const detailId = $(this).data('id');
        const isClosed = $(this).data('closed') == '1';

        let closedWarningHtml = '';
        if (isClosed) {
            closedWarningHtml = `
                <div class="mb-3 p-2.5 bg-rose-50 border border-rose-300 rounded text-left text-xs text-rose-800">
                    <i class="fas fa-lock text-rose-600 mr-1"></i>
                    <strong>Peringatan Periode Ditutup:</strong> Transaksi berada pada periode buku tertutup.
                </div>
            `;
        }

        Swal.fire({
            title: 'Pelepasan Departemen (Unassign)?',
            text: 'Baris transaksi ini akan dilepaskan (departemen_akun_id = NULL) dan kembali muncul di Unassigned Watchdog.',
            html: `
                ${closedWarningHtml}
                <div class="text-left text-xs mt-2">
                    <label class="font-bold text-slate-700 block mb-1">Alasan Pelepasan (Wajib diisi):</label>
                    <textarea id="swal_unassign_reason" class="w-full text-xs border border-slate-300 rounded p-2" rows="3" placeholder="Jelaskan alasan pelepasan departemen transaksi ini..."></textarea>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lepaskan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e11d48',
            preConfirm: () => {
                const reason = $('#swal_unassign_reason').val().trim();
                if (!reason) {
                    Swal.showValidationMessage('Alasan pelepasan wajib diisi.');
                    return false;
                }
                return reason;
            }
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Melepaskan alokasi departemen...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '{{ route("assigned_watchdog.unassign") }}',
                    type: 'POST',
                    data: {
                        detail_id: detailId,
                        reason: res.value,
                        override_closed: isClosed && canOverrideClosed ? 1 : 0
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Departemen berhasil dilepas.',
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal melepaskan departemen.';
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
                    }
                });
            }
        });
    });

    // -------------------------------------------------------------
    // Bulk Reassign
    // -------------------------------------------------------------
    $('#btnBulkReassign').on('click', function () {
        const checked = $('.row-checkbox:checked');
        const detailIds = [];
        let hasClosed = false;

        checked.each(function () {
            detailIds.push($(this).val());
            if ($(this).data('closed') == '1') {
                hasClosed = true;
            }
        });

        if (detailIds.length === 0) {
            Swal.fire({ icon: 'info', title: 'Perhatian', text: 'Pilih minimal satu transaksi.' });
            return;
        }

        const targetDeptId = $('#bulkDeptSelect').val();
        if (!targetDeptId) {
            Swal.fire({ icon: 'warning', title: 'Pilih Departemen', text: 'Silakan pilih departemen tujuan di toolbar.' });
            return;
        }

        let closedWarningHtml = '';
        if (hasClosed) {
            closedWarningHtml = `
                <div class="mb-3 p-2.5 bg-rose-50 border border-rose-300 rounded text-left text-xs text-rose-800">
                    <i class="fas fa-lock text-rose-600 mr-1"></i>
                    <strong>Peringatan:</strong> Beberapa transaksi yang dipilih berada pada periode buku tertutup.
                </div>
            `;
        }

        Swal.fire({
            title: `Reassign Masal (${detailIds.length} item)?`,
            html: `
                ${closedWarningHtml}
                <div class="text-left text-xs mt-2">
                    <label class="font-bold text-slate-700 block mb-1">Alasan Reassign Masal (Wajib diisi):</label>
                    <textarea id="swal_bulk_reason" class="w-full text-xs border border-slate-300 rounded p-2" rows="3" placeholder="Jelaskan alasan pengalihan alokasi masal..."></textarea>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Eksekusi Reassign Masal',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#4f46e5',
            preConfirm: () => {
                const reason = $('#swal_bulk_reason').val().trim();
                if (!reason) {
                    Swal.showValidationMessage('Alasan penyesuaian wajib diisi.');
                    return false;
                }
                return reason;
            }
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: `Mengalihkan ${detailIds.length} transaksi secara atomik...`,
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '{{ route("assigned_watchdog.reassign_bulk") }}',
                    type: 'POST',
                    data: {
                        detail_ids: detailIds,
                        departemen_id: targetDeptId,
                        reason: res.value,
                        override_closed: hasClosed && canOverrideClosed ? 1 : 0
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Selesai!',
                            text: response.message || `${response.updated_count} transaksi berhasil dialihkan.`,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memproses reassign masal.';
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
                    }
                });
            }
        });
    });

    // -------------------------------------------------------------
    // Bulk Unassign
    // -------------------------------------------------------------
    $('#btnBulkUnassign').on('click', function () {
        const checked = $('.row-checkbox:checked');
        const detailIds = [];
        let hasClosed = false;

        checked.each(function () {
            detailIds.push($(this).val());
            if ($(this).data('closed') == '1') {
                hasClosed = true;
            }
        });

        if (detailIds.length === 0) {
            Swal.fire({ icon: 'info', title: 'Perhatian', text: 'Pilih minimal satu transaksi.' });
            return;
        }

        let closedWarningHtml = '';
        if (hasClosed) {
            closedWarningHtml = `
                <div class="mb-3 p-2.5 bg-rose-50 border border-rose-300 rounded text-left text-xs text-rose-800">
                    <i class="fas fa-lock text-rose-600 mr-1"></i>
                    <strong>Peringatan:</strong> Beberapa transaksi yang dipilih berada pada periode tertutup.
                </div>
            `;
        }

        Swal.fire({
            title: `Unassign Masal (${detailIds.length} item)?`,
            text: 'Semua transaksi terpilih akan dilepaskan dari departemennya dan kembali ke antrean Unassigned Watchdog.',
            html: `
                ${closedWarningHtml}
                <div class="text-left text-xs mt-2">
                    <label class="font-bold text-slate-700 block mb-1">Alasan Pelepasan Masal (Wajib diisi):</label>
                    <textarea id="swal_bulk_unassign_reason" class="w-full text-xs border border-slate-300 rounded p-2" rows="3" placeholder="Jelaskan alasan pelepasan alokasi masal..."></textarea>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Eksekusi Unassign Masal',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e11d48',
            preConfirm: () => {
                const reason = $('#swal_bulk_unassign_reason').val().trim();
                if (!reason) {
                    Swal.showValidationMessage('Alasan pelepasan wajib diisi.');
                    return false;
                }
                return reason;
            }
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: `Melepaskan ${detailIds.length} transaksi...`,
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '{{ route("assigned_watchdog.unassign_bulk") }}',
                    type: 'POST',
                    data: {
                        detail_ids: detailIds,
                        reason: res.value,
                        override_closed: hasClosed && canOverrideClosed ? 1 : 0
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Selesai!',
                            text: response.message || `${response.updated_count} transaksi berhasil dilepaskan.`,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memproses unassign masal.';
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
                    }
                });
            }
        });
    });

    // -------------------------------------------------------------
    // Audit History Modal Popup
    // -------------------------------------------------------------
    $(document).on('click', '.btn-history', function () {
        const detailId = $(this).data('id');
        $('#historyModal').removeClass('hidden').show();
        $('#historyModalContent').html(`
            <div class="text-center py-12 text-slate-400">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <p class="text-xs">Memuat riwayat perubahan...</p>
            </div>
        `);

        $.ajax({
            url: `/assigned_watchdog/audit_history/${detailId}`,
            type: 'GET',
            success: function (response) {
                if (response.html) {
                    $('#historyModalContent').html(response.html);
                } else {
                    $('#historyModalContent').html('<p class="text-xs text-slate-500 p-4">Tidak ada data riwayat.</p>');
                }
            },
            error: function () {
                $('#historyModalContent').html(`
                    <div class="p-6 text-center text-rose-600 text-xs">
                        <i class="fas fa-exclamation-circle text-xl mb-1"></i>
                        <p>Gagal memuat riwayat audit detail #${detailId}.</p>
                    </div>
                `);
            }
        });
    });

    $('#closeHistoryModal, #closeHistoryModalBtn').on('click', function () {
        $('#historyModal').addClass('hidden').hide();
    });
});
</script>
@endpush

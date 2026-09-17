@extends('layouts.app')

@push('styles')
<style>
    .kpi-stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }
    .sticky-table-header th {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    /* Modal drawer styling */
    .drawer-overlay {
        background-color: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
    }
</style>
@endpush

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-full">
    @php
        $themeColor = \App\Setting::get('theme_color', '#4F46E5');
    @endphp

    {{-- Main Header Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4"
             style="background: linear-gradient(135deg, {{ $themeColor }} 0%, #1e1b4b 100%);">
            <div>
                <div class="flex items-center space-x-3">
                    <span class="p-2.5 bg-white bg-opacity-20 rounded-xl text-white">
                        <i class="fas fa-balance-scale text-2xl"></i>
                    </span>
                    <div>
                        <h2 class="text-2xl font-bold text-white tracking-tight">
                            Rekonsiliasi Closing vs Kas & Bank
                        </h2>
                        <p class="text-sm text-indigo-100 mt-0.5">
                            Audit komparasi setoran omset kasir wahana (Cash & QRIS) terhadap mutasi penerimaan kas/bank
                        </p>
                    </div>
                </div>
            </div>

            {{-- Action & Export Buttons --}}
            <div class="flex flex-wrap items-center gap-2">
                @php
                    $excelExportUrl = \Illuminate\Support\Facades\Route::has('rekonsiliasi_closing.export')
                        ? route('rekonsiliasi_closing.export', array_merge(request()->query(), ['format' => 'excel']))
                        : url('/rekonsiliasi_closing/export?' . http_build_query(array_merge(request()->query(), ['format' => 'excel'])));
                    $pdfExportUrl = \Illuminate\Support\Facades\Route::has('rekonsiliasi_closing.export')
                        ? route('rekonsiliasi_closing.export', array_merge(request()->query(), ['format' => 'pdf']))
                        : url('/rekonsiliasi_closing/export?' . http_build_query(array_merge(request()->query(), ['format' => 'pdf'])));
                    $indexUrl = \Illuminate\Support\Facades\Route::has('rekonsiliasi_closing.index')
                        ? route('rekonsiliasi_closing.index')
                        : url('/rekonsiliasi_closing');
                    $detailBaseUrl = \Illuminate\Support\Facades\Route::has('rekonsiliasi_closing.detail')
                        ? route('rekonsiliasi_closing.detail')
                        : url('/rekonsiliasi_closing/detail');
                @endphp
                <a href="{{ $excelExportUrl }}"
                   class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all duration-150">
                    <i class="fas fa-file-excel mr-2 text-white"></i> Export Excel
                </a>
                <a href="{{ $pdfExportUrl }}"
                   target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all duration-150">
                    <i class="fas fa-file-pdf mr-2 text-white"></i> Export PDF
                </a>
            </div>
        </div>

        {{-- Filter Toolbar --}}
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <form id="filterForm" method="GET" action="{{ $indexUrl }}" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    {{-- Start Date --}}
                    <div>
                        <label for="start_date" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            <i class="fas fa-calendar-alt text-indigo-500 mr-1"></i> Tanggal Awal
                        </label>
                        <input type="date" name="start_date" id="start_date"
                               value="{{ $startDate }}"
                               class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 bg-white">
                    </div>

                    {{-- End Date --}}
                    <div>
                        <label for="end_date" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            <i class="fas fa-calendar-alt text-indigo-500 mr-1"></i> Tanggal Akhir
                        </label>
                        <input type="date" name="end_date" id="end_date"
                               value="{{ $endDate }}"
                               class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 bg-white">
                    </div>

                    {{-- Departemen Filter --}}
                    <div>
                        <label for="departemen_id" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            <i class="fas fa-building text-indigo-500 mr-1"></i> Departemen / Wahana
                        </label>
                        <select name="departemen_id" id="departemen_id"
                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 bg-white">
                            <option value="all" {{ ($departemenId === 'all' || empty($departemenId)) ? 'selected' : '' }}>
                                Semua Departemen (Konsolidasi)
                            </option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ (string)$departemenId === (string)$dept->id ? 'selected' : '' }}>
                                    {{ $dept->kode }} - {{ $dept->deskripsi }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <label for="status" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            <i class="fas fa-tag text-indigo-500 mr-1"></i> Status Rekonsiliasi
                        </label>
                        <select name="status" id="status"
                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 bg-white">
                            <option value="all" {{ ($statusFilter === 'ALL' || empty($statusFilter)) ? 'selected' : '' }}>
                                Semua Status
                            </option>
                            <option value="MATCH" {{ $statusFilter === 'MATCH' ? 'selected' : '' }}>
                                MATCH (Selisih &le; 1.000)
                            </option>
                            <option value="PENDING" {{ $statusFilter === 'PENDING' ? 'selected' : '' }}>
                                PENDING (Salah Satu Sisi Nol)
                            </option>
                            <option value="SELISIH" {{ $statusFilter === 'SELISIH' ? 'selected' : '' }}>
                                SELISIH (Discrepancy &gt; 1.000)
                            </option>
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-end gap-2">
                        <button type="submit"
                                class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all duration-150">
                            <i class="fas fa-filter mr-1.5"></i> Terapkan
                        </button>
                        <a href="{{ $indexUrl }}"
                           class="inline-flex items-center justify-center px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg transition-colors duration-150"
                           title="Reset Filter">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>

                {{-- Date Presets --}}
                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-200 text-xs">
                    <span class="text-gray-500 font-medium mr-1"><i class="fas fa-bolt text-amber-500"></i> Preset Cepat:</span>
                    <button type="button" onclick="setPreset('today')"
                            class="px-2.5 py-1 rounded bg-white hover:bg-indigo-50 hover:text-indigo-600 text-gray-700 border border-gray-200 transition">
                        Hari Ini
                    </button>
                    <button type="button" onclick="setPreset('last7days')"
                            class="px-2.5 py-1 rounded bg-white hover:bg-indigo-50 hover:text-indigo-600 text-gray-700 border border-gray-200 transition">
                        7 Hari Terakhir
                    </button>
                    <button type="button" onclick="setPreset('thismonth')"
                            class="px-2.5 py-1 rounded bg-white hover:bg-indigo-50 hover:text-indigo-600 text-gray-700 border border-gray-200 transition">
                        Bulan Ini
                    </button>
                    <button type="button" onclick="setPreset('december2025')"
                            class="px-2.5 py-1 rounded bg-white hover:bg-indigo-50 hover:text-indigo-600 text-gray-700 border border-gray-200 transition">
                        Desember 2025
                    </button>
                    <button type="button" onclick="setPreset('year2025')"
                            class="px-2.5 py-1 rounded bg-white hover:bg-indigo-50 hover:text-indigo-600 text-gray-700 border border-gray-200 transition">
                        Sepanjang Tahun 2025
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- KPI Metric Summary Cards (6 Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        {{-- Card 1: Total Omset --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 kpi-stat-card border-l-4 border-l-indigo-600">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Total Omset Kasir</span>
                <span class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                    <i class="fas fa-cash-register text-base"></i>
                </span>
            </div>
            <div class="text-lg font-bold text-gray-900 mt-2">
                Rp {{ number_format($kpi['total_omset'], 0, ',', '.') }}
            </div>
            <div class="flex justify-between text-[11px] text-gray-500 mt-2 pt-2 border-t border-gray-100">
                <span>Cash: {{ number_format($kpi['omset_cash'], 0, ',', '.') }}</span>
                <span>QRIS: {{ number_format($kpi['omset_qris'], 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Card 2: Total Kas Masuk --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 kpi-stat-card border-l-4 border-l-blue-600">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Kas & Bank Masuk</span>
                <span class="p-2 bg-blue-50 rounded-lg text-blue-600">
                    <i class="fas fa-university text-base"></i>
                </span>
            </div>
            <div class="text-lg font-bold text-gray-900 mt-2">
                Rp {{ number_format($kpi['total_kas_masuk'], 0, ',', '.') }}
            </div>
            <div class="flex justify-between text-[11px] text-gray-500 mt-2 pt-2 border-t border-gray-100">
                <span>Cash: {{ number_format($kpi['kas_cash'], 0, ',', '.') }}</span>
                <span>QRIS: {{ number_format($kpi['kas_qris'], 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Card 3: Net Selisih --}}
        @php
            $isBalanced = abs($kpi['net_selisih']) <= 1000;
            $selisihBorder = $isBalanced ? 'border-l-emerald-600' : ($kpi['net_selisih'] < 0 ? 'border-l-rose-600' : 'border-l-amber-500');
            $selisihBg = $isBalanced ? 'bg-emerald-50 text-emerald-700' : ($kpi['net_selisih'] < 0 ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700');
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 kpi-stat-card border-l-4 {{ $selisihBorder }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Net Selisih (Variance)</span>
                <span class="p-2 rounded-lg {{ $selisihBg }}">
                    <i class="fas {{ $isBalanced ? 'fa-check' : 'fa-exchange-alt' }} text-base"></i>
                </span>
            </div>
            <div class="text-lg font-bold mt-2 {{ $isBalanced ? 'text-emerald-600' : ($kpi['net_selisih'] < 0 ? 'text-rose-600' : 'text-amber-600') }}">
                Rp {{ number_format($kpi['net_selisih'], 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-gray-500 mt-2 pt-2 border-t border-gray-100 truncate">
                {{ $isBalanced ? '100% Balanced' : ($kpi['net_selisih'] < 0 ? 'Omset belum masuk bank' : 'Penerimaan melebihi closing') }}
            </div>
        </div>

        {{-- Card 4: Count MATCH --}}
        <div onclick="quickFilterStatus('MATCH')"
             class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 kpi-stat-card border-l-4 border-l-emerald-500 cursor-pointer hover:bg-emerald-50/30 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Status MATCH</span>
                <span class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                    <i class="fas fa-check-circle text-base"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-600 mt-2">
                {{ $kpi['count_match'] }}
            </div>
            <div class="text-[11px] text-gray-500 mt-2 pt-2 border-t border-gray-100 flex items-center justify-between">
                <span>Klik untuk filter</span>
                <i class="fas fa-arrow-right text-[10px] text-emerald-500"></i>
            </div>
        </div>

        {{-- Card 5: Count PENDING --}}
        <div onclick="quickFilterStatus('PENDING')"
             class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 kpi-stat-card border-l-4 border-l-amber-500 cursor-pointer hover:bg-amber-50/30 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Status PENDING</span>
                <span class="p-2 bg-amber-50 rounded-lg text-amber-600">
                    <i class="fas fa-clock text-base"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-amber-600 mt-2">
                {{ $kpi['count_pending'] }}
            </div>
            <div class="text-[11px] text-gray-500 mt-2 pt-2 border-t border-gray-100 flex items-center justify-between">
                <span>Satu sisi bernilai 0</span>
                <i class="fas fa-arrow-right text-[10px] text-amber-500"></i>
            </div>
        </div>

        {{-- Card 6: Count SELISIH --}}
        <div onclick="quickFilterStatus('SELISIH')"
             class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 kpi-stat-card border-l-4 border-l-rose-500 cursor-pointer hover:bg-rose-50/30 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Status SELISIH</span>
                <span class="p-2 bg-rose-50 rounded-lg text-rose-600">
                    <i class="fas fa-exclamation-triangle text-base"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-rose-600 mt-2">
                {{ $kpi['count_selisih'] }}
            </div>
            <div class="text-[11px] text-gray-500 mt-2 pt-2 border-t border-gray-100 flex items-center justify-between">
                <span>Discrepancy &gt; 1.000</span>
                <i class="fas fa-arrow-right text-[10px] text-rose-500"></i>
            </div>
        </div>
    </div>

    {{-- Main Reconciliation Data Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Table Toolbar --}}
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/50">
            <div class="flex items-center space-x-2">
                <span class="text-sm font-semibold text-gray-800">
                    Daftar Rekonsiliasi
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                    Menampilkan {{ $paginatedItems->firstItem() ?? 0 }} - {{ $paginatedItems->lastItem() ?? 0 }} dari {{ $paginatedItems->total() }} baris
                </span>
                @if ($statusFilter !== 'ALL')
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-200 text-gray-700 flex items-center gap-1">
                        Status: {{ $statusFilter }}
                        <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" class="text-gray-500 hover:text-gray-700 ml-1">
                            <i class="fas fa-times-circle"></i>
                        </a>
                    </span>
                @endif
            </div>

            {{-- Per Page Selector --}}
            <div class="flex items-center space-x-2 text-xs text-gray-600">
                <label for="per_page_select">Baris per halaman:</label>
                <select id="per_page_select" onchange="changePerPage(this.value)"
                        class="text-xs border-gray-300 rounded shadow-sm py-1 px-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                    <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                </select>
            </div>
        </div>

        {{-- Table Element --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-800 text-white sticky-table-header">
                    <tr>
                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider border-r border-gray-700 w-12">
                            No
                        </th>
                        <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider border-r border-gray-700 w-28">
                            Tanggal
                        </th>
                        <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider border-r border-gray-700">
                            Unit / Departemen
                        </th>
                        <th colspan="3" class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider border-r border-gray-700 bg-gray-900/60 text-indigo-200">
                            <i class="fas fa-cash-register mr-1"></i> Omset Closing Kasir
                        </th>
                        <th colspan="3" class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider border-r border-gray-700 bg-gray-900/60 text-blue-200">
                            <i class="fas fa-university mr-1"></i> Penerimaan Kas & Bank
                        </th>
                        <th rowspan="2" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider border-r border-gray-700 w-32">
                            Selisih (Rp)
                        </th>
                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider border-r border-gray-700 w-28">
                            Status
                        </th>
                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider w-24">
                            Aksi Audit
                        </th>
                    </tr>
                    <tr class="bg-gray-700 text-gray-200 text-[11px]">
                        <th class="px-3 py-1.5 text-right font-medium border-r border-gray-600 w-28">Cash</th>
                        <th class="px-3 py-1.5 text-right font-medium border-r border-gray-600 w-28">QRIS</th>
                        <th class="px-3 py-1.5 text-right font-semibold border-r border-gray-600 w-32 text-indigo-300">Total Omset</th>
                        <th class="px-3 py-1.5 text-right font-medium border-r border-gray-600 w-28">Setoran Cash</th>
                        <th class="px-3 py-1.5 text-right font-medium border-r border-gray-600 w-28">Settlement QRIS</th>
                        <th class="px-3 py-1.5 text-right font-semibold border-r border-gray-600 w-32 text-blue-300">Total Masuk</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($paginatedItems as $idx => $row)
                        @php
                            $rowNumber = ($paginatedItems->currentPage() - 1) * $paginatedItems->perPage() + $idx + 1;
                        @endphp
                        <tr class="hover:bg-indigo-50/40 transition-colors duration-150">
                            {{-- No --}}
                            <td class="px-3 py-3 text-center text-xs text-gray-500 font-medium">
                                {{ $rowNumber }}
                            </td>

                            {{-- Tanggal --}}
                            <td class="px-4 py-3 text-xs text-gray-900 font-semibold whitespace-nowrap">
                                <div>{{ \Carbon\Carbon::parse($row['tanggal'])->translatedFormat('d M Y') }}</div>
                                <span class="text-[11px] text-gray-500 font-normal">
                                    {{ \Carbon\Carbon::parse($row['tanggal'])->translatedFormat('l') }}
                                </span>
                            </td>

                            {{-- Unit / Departemen --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-0.5 text-[11px] font-mono font-bold rounded bg-gray-100 text-gray-700 border border-gray-200">
                                        {{ $row['dept_kode'] }}
                                    </span>
                                    <span class="text-xs font-semibold text-gray-800">
                                        {{ $row['dept_nama'] }}
                                    </span>
                                </div>
                            </td>

                            {{-- Omset Cash --}}
                            <td class="px-3 py-3 text-right text-xs text-gray-700 whitespace-nowrap">
                                {{ number_format($row['omset_cash'], 2, ',', '.') }}
                            </td>

                            {{-- Omset QRIS --}}
                            <td class="px-3 py-3 text-right text-xs text-gray-700 whitespace-nowrap">
                                {{ number_format($row['omset_qris'], 2, ',', '.') }}
                            </td>

                            {{-- Total Omset --}}
                            <td class="px-3 py-3 text-right text-xs font-bold text-indigo-900 bg-indigo-50/30 whitespace-nowrap">
                                {{ number_format($row['total_omset'], 2, ',', '.') }}
                            </td>

                            {{-- Kas Masuk Cash --}}
                            <td class="px-3 py-3 text-right text-xs text-gray-700 whitespace-nowrap">
                                {{ number_format($row['kas_masuk_cash'], 2, ',', '.') }}
                            </td>

                            {{-- Kas Masuk QRIS --}}
                            <td class="px-3 py-3 text-right text-xs text-gray-700 whitespace-nowrap">
                                {{ number_format($row['kas_masuk_qris'], 2, ',', '.') }}
                            </td>

                            {{-- Total Kas Masuk --}}
                            <td class="px-3 py-3 text-right text-xs font-bold text-blue-900 bg-blue-50/30 whitespace-nowrap">
                                {{ number_format($row['total_kas_masuk'], 2, ',', '.') }}
                            </td>

                            {{-- Selisih --}}
                            <td class="px-4 py-3 text-right text-xs font-bold whitespace-nowrap {{ $row['selisih'] == 0 ? 'text-emerald-600' : ($row['selisih'] < 0 ? 'text-rose-600' : 'text-blue-600') }}">
                                {{ number_format($row['selisih'], 2, ',', '.') }}
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-3 py-3 text-center whitespace-nowrap">
                                @if ($row['status'] === 'MATCH')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fas fa-check-circle mr-1 text-emerald-600"></i> MATCH
                                    </span>
                                @elseif ($row['status'] === 'PENDING')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200"
                                          title="{{ $row['total_omset'] == 0 ? 'Kas masuk tanpa closing omset' : 'Closing omset tanpa mutasi kas masuk' }}">
                                        <i class="fas fa-clock mr-1 text-amber-600"></i> PENDING
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                        <i class="fas fa-exclamation-circle mr-1 text-rose-600"></i> SELISIH
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi: Detail Drawer --}}
                            <td class="px-3 py-3 text-center whitespace-nowrap">
                                <button type="button"
                                        onclick="openDetailModal('{{ $row['tanggal'] }}', '{{ $row['departemen_id'] }}', '{{ addslashes($row['dept_nama']) }}')"
                                        class="inline-flex items-center px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-medium rounded-lg border border-indigo-200 transition-colors duration-150">
                                    <i class="fas fa-search-plus mr-1"></i> Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                    <p class="text-base font-semibold text-gray-700">Tidak ada data rekonsiliasi</p>
                                    <p class="text-xs text-gray-500 mt-1">Coba sesuaikan tanggal atau pilihan filter departemen/status di atas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                {{-- Table Summary Footer --}}
                <tfoot class="bg-gray-100 font-semibold text-gray-900 border-t-2 border-gray-300">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-700">
                            TOTAL REKONSILIASI PERIODE
                        </td>
                        <td class="px-3 py-3 text-right text-xs">
                            {{ number_format($kpi['omset_cash'], 2, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-right text-xs">
                            {{ number_format($kpi['omset_qris'], 2, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-right text-xs font-bold text-indigo-900 bg-indigo-100/50">
                            {{ number_format($kpi['total_omset'], 2, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-right text-xs">
                            {{ number_format($kpi['kas_cash'], 2, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-right text-xs">
                            {{ number_format($kpi['kas_qris'], 2, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-right text-xs font-bold text-blue-900 bg-blue-100/50">
                            {{ number_format($kpi['total_kas_masuk'], 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-xs font-bold {{ $kpi['net_selisih'] == 0 ? 'text-emerald-600' : ($kpi['net_selisih'] < 0 ? 'text-rose-600' : 'text-blue-600') }}">
                            {{ number_format($kpi['net_selisih'], 2, ',', '.') }}
                        </td>
                        <td colspan="2" class="px-3 py-3 text-center text-[11px] text-gray-600 font-normal">
                            M: {{ $kpi['count_match'] }} | P: {{ $kpi['count_pending'] }} | S: {{ $kpi['count_selisih'] }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Pagination Bar --}}
        @if ($paginatedItems->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $paginatedItems->links() }}
            </div>
        @endif
    </div>
</div>

{{-- AJAX Modal Drawer for Voucher Line Items --}}
<div id="detailModal" class="fixed inset-0 z-50 hidden overflow-y-auto drawer-overlay" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" onclick="closeDetailModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full border border-gray-200">
            {{-- Modal Header --}}
            <div class="px-6 py-5 bg-gradient-to-r from-gray-900 via-indigo-950 to-gray-900 text-white flex justify-between items-center border-b border-gray-800">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-indigo-500/30 text-indigo-300 border border-indigo-500/40">
                            Audit Drilldown
                        </span>
                        <h3 class="text-lg font-bold text-white" id="modalTitle">
                            Detail Voucher Rekonsiliasi
                        </h3>
                    </div>
                    <p class="text-xs text-indigo-200 mt-1" id="modalSubtitle">
                        Memuat data transaksi...
                    </p>
                </div>
                <button type="button" onclick="closeDetailModal()" class="text-gray-400 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 max-h-[75vh] overflow-y-auto">
                {{-- Loading Spinner --}}
                <div id="modalLoading" class="py-12 text-center">
                    <i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-3"></i>
                    <p class="text-sm text-gray-600 font-medium">Mengambil rincian voucher dari database...</p>
                </div>

                {{-- Modal Content --}}
                <div id="modalContent" class="hidden space-y-6">
                    {{-- Summary Comparison Box --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <div class="p-3 bg-white rounded-lg border border-gray-200">
                            <span class="text-[11px] font-semibold uppercase text-gray-500">Omset Kasir (CH-)</span>
                            <div class="text-base font-bold text-indigo-900 mt-1" id="modalTotalOmset">Rp 0</div>
                            <div class="text-[11px] text-gray-500 mt-1" id="modalOmsetBreakdown">Cash: 0 | QRIS: 0</div>
                        </div>
                        <div class="p-3 bg-white rounded-lg border border-gray-200">
                            <span class="text-[11px] font-semibold uppercase text-gray-500">Kas & Bank Masuk</span>
                            <div class="text-base font-bold text-blue-900 mt-1" id="modalTotalKas">Rp 0</div>
                            <div class="text-[11px] text-gray-500 mt-1" id="modalKasBreakdown">Cash: 0 | QRIS: 0</div>
                        </div>
                        <div class="p-3 bg-white rounded-lg border border-gray-200">
                            <span class="text-[11px] font-semibold uppercase text-gray-500">Net Selisih & Status</span>
                            <div class="text-base font-bold mt-1" id="modalSelisih">Rp 0</div>
                            <div class="mt-1" id="modalStatusBadge"></div>
                        </div>
                    </div>

                    {{-- Section 1: Closing Vouchers (CH-) --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-gray-900 flex items-center">
                                <i class="fas fa-cash-register text-indigo-600 mr-2"></i>
                                Voucher Closing Kasir (CH-)
                            </h4>
                            <span class="text-xs text-gray-500" id="closingVouchersCount">0 Voucher</span>
                        </div>
                        <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                            <div class="overflow-x-auto" id="closingVouchersContainer">
                                {{-- Populated via AJAX --}}
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Kas & Bank Inflow Vouchers --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-gray-900 flex items-center">
                                <i class="fas fa-university text-blue-600 mr-2"></i>
                                Voucher Penerimaan Kas & Bank (Mutasi Masuk)
                            </h4>
                            <span class="text-xs text-gray-500" id="kasVouchersCount">0 Voucher</span>
                        </div>
                        <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                            <div class="overflow-x-auto" id="kasVouchersContainer">
                                {{-- Populated via AJAX --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closeDetailModal()"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold text-xs rounded-lg transition-colors duration-150">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Format number to Indonesian Rupiah representation
    function formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    // Set Date Presets
    function setPreset(type) {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const today = new Date();

        const formatDate = (d) => {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        if (type === 'today') {
            startDateInput.value = formatDate(today);
            endDateInput.value = formatDate(today);
        } else if (type === 'last7days') {
            const past7 = new Date();
            past7.setDate(today.getDate() - 6);
            startDateInput.value = formatDate(past7);
            endDateInput.value = formatDate(today);
        } else if (type === 'thismonth') {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            startDateInput.value = formatDate(firstDay);
            endDateInput.value = formatDate(lastDay);
        } else if (type === 'december2025') {
            startDateInput.value = '2025-12-01';
            endDateInput.value = '2025-12-31';
        } else if (type === 'year2025') {
            startDateInput.value = '2025-01-01';
            endDateInput.value = '2025-12-31';
        }

        document.getElementById('filterForm').submit();
    }

    // Quick Filter by Status from KPI cards
    function quickFilterStatus(status) {
        document.getElementById('status').value = status;
        document.getElementById('filterForm').submit();
    }

    // Per page change
    function changePerPage(perPage) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    // Open Detail Modal via AJAX
    function openDetailModal(tanggal, deptId, deptName) {
        const modal = document.getElementById('detailModal');
        const loading = document.getElementById('modalLoading');
        const content = document.getElementById('modalContent');

        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        content.classList.add('hidden');

        document.getElementById('modalTitle').textContent = `Audit Rekonsiliasi: ${deptName}`;
        document.getElementById('modalSubtitle').textContent = `Tanggal Transaksi: ${tanggal}`;

        const url = `{{ $detailBaseUrl }}?tanggal=${tanggal}&departemen_id=${deptId}`;

        fetch(url)
            .then(res => res.json())
            .then(response => {
                loading.classList.add('hidden');
                content.classList.remove('hidden');

                if (!response.success) {
                    content.innerHTML = `<div class="p-4 bg-rose-50 text-rose-700 rounded-lg text-sm">${response.message || 'Gagal mengambil data.'}</div>`;
                    return;
                }

                const summary = response.summary;
                document.getElementById('modalTotalOmset').textContent = `Rp ${formatRupiah(summary.total_omset)}`;
                document.getElementById('modalOmsetBreakdown').textContent = `Cash: Rp ${formatRupiah(summary.omset_cash)} | QRIS: Rp ${formatRupiah(summary.omset_qris)}`;

                document.getElementById('modalTotalKas').textContent = `Rp ${formatRupiah(summary.total_kas_masuk)}`;
                document.getElementById('modalKasBreakdown').textContent = `Cash: Rp ${formatRupiah(summary.kas_cash)} | QRIS: Rp ${formatRupiah(summary.kas_qris)}`;

                const selisihEl = document.getElementById('modalSelisih');
                selisihEl.textContent = `Rp ${formatRupiah(summary.selisih)}`;
                selisihEl.className = `text-base font-bold mt-1 ${summary.selisih === 0 ? 'text-emerald-600' : (summary.selisih < 0 ? 'text-rose-600' : 'text-blue-600')}`;

                let badgeHtml = '';
                if (summary.status === 'MATCH') {
                    badgeHtml = `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800"><i class="fas fa-check-circle mr-1"></i> MATCH</span>`;
                } else if (summary.status === 'PENDING') {
                    badgeHtml = `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800"><i class="fas fa-clock mr-1"></i> PENDING</span>`;
                } else {
                    badgeHtml = `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800"><i class="fas fa-exclamation-circle mr-1"></i> SELISIH</span>`;
                }
                document.getElementById('modalStatusBadge').innerHTML = badgeHtml;

                // Render Closing Vouchers
                const closingContainer = document.getElementById('closingVouchersContainer');
                document.getElementById('closingVouchersCount').textContent = `${response.closing_vouchers.length} Voucher`;

                if (response.closing_vouchers.length === 0) {
                    closingContainer.innerHTML = `<div class="p-4 text-center text-xs text-gray-500 italic">Tidak ada voucher closing kasir (CH-) pada tanggal ini.</div>`;
                } else {
                    let html = '';
                    response.closing_vouchers.forEach(v => {
                        html += `
                            <div class="border-b border-gray-200 last:border-b-0 p-3 bg-gray-50/50">
                                <div class="flex flex-wrap items-center justify-between text-xs mb-2">
                                    <div class="font-bold text-indigo-900"><i class="fas fa-receipt mr-1 text-indigo-600"></i> ${v.source}</div>
                                    <div class="text-gray-500 italic">${v.comment || '-'}</div>
                                    <div class="font-bold text-gray-900">Total: Rp ${formatRupiah(v.total_omset)} (Cash: ${formatRupiah(v.omset_cash)}, QRIS: ${formatRupiah(v.omset_qris)})</div>
                                </div>
                                <table class="w-full text-xs border border-gray-200 bg-white">
                                    <thead class="bg-gray-100 text-gray-700">
                                        <tr>
                                            <th class="px-2 py-1 text-left">Kode Akun</th>
                                            <th class="px-2 py-1 text-left">Nama Akun</th>
                                            <th class="px-2 py-1 text-left">Keterangan</th>
                                            <th class="px-2 py-1 text-right">Debit (Rp)</th>
                                            <th class="px-2 py-1 text-right">Kredit (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                        `;
                        v.items.forEach(item => {
                            const isCash = item.kode_akun === '1101002';
                            const isQris = item.kode_akun === '1103001';
                            const highlightClass = (isCash || isQris) ? 'bg-indigo-50/40 font-semibold' : '';
                            html += `
                                <tr class="${highlightClass}">
                                    <td class="px-2 py-1 font-mono">${item.kode_akun}</td>
                                    <td class="px-2 py-1">${item.nama_akun}</td>
                                    <td class="px-2 py-1 text-gray-500">${item.comment}</td>
                                    <td class="px-2 py-1 text-right">${formatRupiah(item.debits)}</td>
                                    <td class="px-2 py-1 text-right">${formatRupiah(item.credits)}</td>
                                </tr>
                            `;
                        });
                        html += `</tbody></table></div>`;
                    });
                    closingContainer.innerHTML = html;
                }

                // Render Kas & Bank Vouchers
                const kasContainer = document.getElementById('kasVouchersContainer');
                document.getElementById('kasVouchersCount').textContent = `${response.kas_bank_vouchers.length} Voucher`;

                if (response.kas_bank_vouchers.length === 0) {
                    kasContainer.innerHTML = `<div class="p-4 text-center text-xs text-gray-500 italic">Tidak ada voucher penerimaan kas & bank pada tanggal ini.</div>`;
                } else {
                    let html = '';
                    response.kas_bank_vouchers.forEach(v => {
                        html += `
                            <div class="border-b border-gray-200 last:border-b-0 p-3 bg-gray-50/50">
                                <div class="flex flex-wrap items-center justify-between text-xs mb-2">
                                    <div class="font-bold text-blue-900"><i class="fas fa-file-invoice-dollar mr-1 text-blue-600"></i> ${v.source}</div>
                                    <div class="text-gray-500 italic">${v.comment || '-'}</div>
                                    <div class="font-bold text-gray-900">Total Masuk: Rp ${formatRupiah(v.total_kas)}</div>
                                </div>
                                <table class="w-full text-xs border border-gray-200 bg-white">
                                    <thead class="bg-gray-100 text-gray-700">
                                        <tr>
                                            <th class="px-2 py-1 text-left">Kode Akun</th>
                                            <th class="px-2 py-1 text-left">Nama Akun</th>
                                            <th class="px-2 py-1 text-left">Keterangan</th>
                                            <th class="px-2 py-1 text-right">Debit (Rp)</th>
                                            <th class="px-2 py-1 text-right">Kredit (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                        `;
                        v.items.forEach(item => {
                            const isCreditReceipt = (item.kode_akun === '1101002' || item.kode_akun === '1103001') && item.credits > 0;
                            const isDebitBank = item.debits > 0;
                            const highlightClass = isCreditReceipt ? 'bg-emerald-50/50 font-semibold' : (isDebitBank ? 'bg-blue-50/30' : '');
                            html += `
                                <tr class="${highlightClass}">
                                    <td class="px-2 py-1 font-mono">${item.kode_akun}</td>
                                    <td class="px-2 py-1">${item.nama_akun}</td>
                                    <td class="px-2 py-1 text-gray-500">${item.comment}</td>
                                    <td class="px-2 py-1 text-right">${formatRupiah(item.debits)}</td>
                                    <td class="px-2 py-1 text-right">${formatRupiah(item.credits)}</td>
                                </tr>
                            `;
                        });
                        html += `</tbody></table></div>`;
                    });
                    kasContainer.innerHTML = html;
                }
            })
            .catch(err => {
                loading.classList.add('hidden');
                content.classList.remove('hidden');
                content.innerHTML = `<div class="p-4 bg-rose-50 text-rose-700 rounded-lg text-sm">Terjadi kesalahan koneksi saat memuat data: ${err.message}</div>`;
            });
    }

    // Close Detail Modal
    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeDetailModal();
        }
    });
</script>
@endpush

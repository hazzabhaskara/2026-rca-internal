{{-- resources/views/laba_rugi_departemen/laba_rugi_departemen_report.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    .pnl-wrap { font-family: 'Inter', sans-serif; }

    /* Header */
    .pnl-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
        position: relative;
        overflow: hidden;
    }
    .pnl-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
        pointer-events: none;
    }

    /* Summary KPI Cards */
    .summary-card {
        backdrop-filter: blur(12px);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .summary-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }

    /* Table Viewport Container */
    .table-container {
        max-height: calc(100vh - 220px);
        overflow: auto;
        position: relative;
    }

    /* Table */
    .pnl-matrix-table { border-collapse: separate; border-spacing: 0; }
    .pnl-matrix-table thead th {
        position: sticky;
        top: 0;
        z-index: 30;
        background: #1e293b;
        color: white;
        font-size: 0.72rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 10px 12px;
        border-bottom: 2px solid #334155;
        white-space: nowrap;
        transition: background-color 0.15s ease;
    }
    .pnl-matrix-table td {
        border-bottom: 1px solid #f1f5f9;
    }

    /* 2D Freeze Panes: Layering & Sticky Coordinates */
    /* Col 1: Nama Akun (Left: 0, Width: 300px) */
    .col-sticky-1 {
        position: sticky !important;
        left: 0 !important;
        z-index: 20 !important;
        min-width: 300px;
        width: 300px;
        max-width: 300px;
        border-right: 1px solid #e2e8f0;
    }
    /* Col 2: Total Konsolidasi (Left: 300px, Width: 160px) */
    .col-sticky-2 {
        position: sticky !important;
        left: 300px !important;
        z-index: 20 !important;
        min-width: 160px;
        width: 160px;
        max-width: 160px;
        border-right: 2px solid #cbd5e1;
        box-shadow: 4px 0 8px -2px rgba(0, 0, 0, 0.12);
    }
    /* Corner Sticky: Top-left headers (intersection of sticky top and sticky cols) */
    .corner-sticky-1 {
        position: sticky !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 50 !important;
        background: #0f172a !important;
        min-width: 300px;
        width: 300px;
        max-width: 300px;
        border-right: 1px solid #334155;
    }
    .corner-sticky-2 {
        position: sticky !important;
        top: 0 !important;
        left: 300px !important;
        z-index: 50 !important;
        background: #1e1b4b !important;
        min-width: 160px;
        width: 160px;
        max-width: 160px;
        border-right: 2px solid #334155;
        box-shadow: 4px 0 8px -2px rgba(0, 0, 0, 0.25);
    }

    /* Cell Content container avoiding display: flex on td directly */
    .cell-content {
        display: flex;
        align-items: center;
        width: 100%;
    }

    /* Accordion row types */
    .row-section { cursor: pointer; user-select: none; transition: filter 0.15s; }
    .row-section:hover { filter: brightness(1.08); }

    .row-group { cursor: pointer; user-select: none; transition: background 0.15s; }
    .row-group:hover { filter: brightness(0.97); }

    .row-account { cursor: pointer; transition: background 0.15s; }
    .row-account:hover { background: #f8fafc !important; }

    .row-subaccount { transition: background 0.15s; }
    .row-subaccount:hover { background: #f1f5f9 !important; }

    .collapsible-row { transition: opacity 0.2s ease; }
    .collapsible-row.hidden { display: none !important; }

    /* Crosshair Highlights (Preserve row-fixed, row-section, row-subtotal, row-total) */
    .crosshair-row:not(.row-fixed):not(.row-section):not(.row-subtotal):not(.row-total) td {
        background-color: #f1f5f9 !important;
    }
    .crosshair-row:not(.row-fixed):not(.row-section):not(.row-subtotal):not(.row-total) td.col-sticky-1,
    .crosshair-row:not(.row-fixed):not(.row-section):not(.row-subtotal):not(.row-total) td.col-sticky-2 {
        background-color: #e2e8f0 !important;
    }
    th.crosshair-col {
        background-color: #334155 !important;
        color: #facc15 !important;
    }

    /* Fullscreen Focus Mode */
    .pnl-fullscreen {
        position: fixed !important;
        inset: 0 !important;
        z-index: 9999 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: #f1f5f9 !important;
        padding: 1rem !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
    }
    .pnl-fullscreen .pnl-header,
    .pnl-fullscreen .header-cards {
        display: none !important;
    }
    .pnl-fullscreen #matrixCard {
        flex: 1 1 0% !important;
        min-height: 0 !important;
    }
    .pnl-fullscreen .table-container {
        flex: 1 1 0% !important;
        max-height: none !important;
        height: 100% !important;
    }

    /* Chevron */
    .toggle-chevron {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 4px;
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }
    .toggle-chevron.open { transform: rotate(90deg); }
    .toggle-chevron svg { width: 11px; height: 11px; }

    /* Numbers - Standar Inter Tabular Nums */
    .num-negative { color: #dc2626; font-weight: 500; }
    .num-positive { color: #1e293b; }
    .num-cell { font-variant-numeric: tabular-nums; font-size: 0.76rem; }

    /* Drill-down links */
    .drilldown-link {
        color: inherit;
        text-decoration: none;
        transition: color 0.15s;
    }
    .drilldown-link:hover { color: #4f46e5; text-decoration: underline; }

    /* Control buttons */
    .ctrl-btn {
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
        border: 1px solid #e2e8f0;
        background: white;
        color: #374151;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .ctrl-btn:hover { background: #f1f5f9; border-color: #94a3b8; }
    .ctrl-btn.active { background: #1e293b; color: white; border-color: #1e293b; }

    /* Floating horizontal scrollbar */
    .floating-scrollbar-wrap {
        position: sticky;
        bottom: 0;
        left: 0;
        right: 0;
        height: 12px;
        background: rgba(226, 232, 240, 0.95);
        overflow-x: auto;
        overflow-y: hidden;
        z-index: 40;
        border-top: 1px solid #cbd5e1;
    }
    .floating-scrollbar-wrap.is-floating {
        position: fixed !important;
        bottom: 0 !important;
        z-index: 45 !important;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.15);
    }
    .floating-scrollbar-inner {
        height: 1px;
    }

    /* Sleek WebKit scrollbars for table container and floating scrollbar */
    .table-container::-webkit-scrollbar,
    .floating-scrollbar-wrap::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }
    .table-container::-webkit-scrollbar-track,
    .floating-scrollbar-wrap::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .table-container::-webkit-scrollbar-thumb,
    .floating-scrollbar-wrap::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 4px;
    }
    .table-container::-webkit-scrollbar-thumb:hover,
    .floating-scrollbar-wrap::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }

    /* Print Styling */
    @media print {
        body { background: white !important; font-size: 8pt !important; }
        .no-print { display: none !important; }
        .table-container { max-height: none !important; overflow: visible !important; }
        .col-sticky-1, .col-sticky-2, .corner-sticky-1, .corner-sticky-2 { position: static !important; box-shadow: none !important; }
    }
</style>
@endpush

@section('content')
<div id="pnlReportContainer" class="pnl-wrap min-h-screen bg-slate-100 py-6 px-4 sm:px-6 lg:px-8">

    {{-- HEADER BANNER --}}
    <div class="pnl-header rounded-2xl shadow-xl text-white p-6 sm:p-8 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 text-xs font-semibold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 rounded-full">
                        Financial Statement
                    </span>
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $mode === 'matrix' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-blue-500/20 text-blue-300 border border-blue-400/30' }}">
                        {{ $mode === 'matrix' ? 'Mode Matriks Komparatif' : 'Mode Single: ' . ($selectedDept->deskripsi ?? 'Departemen') }}
                    </span>
                    <button type="button" onclick="openReconciliationModal()" class="px-2.5 py-0.5 text-xs font-semibold rounded-full border transition flex items-center gap-1.5 {{ $reconciliation['statusBadgeClass'] ?? 'bg-emerald-500/20 text-emerald-300 border-emerald-400/30' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ ($reconciliation['isFullyReconciled'] ?? true) ? 'bg-emerald-400' : 'bg-rose-400 animate-pulse' }}"></span>
                        {{ $reconciliation['statusText'] ?? '100% Klop Neraca' }}
                    </button>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white flex items-center gap-2">
                    Laporan Laba Rugi Departemen
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm mt-1">
                    Periode: <span class="font-semibold text-white">{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }}</span> s/d <span class="font-semibold text-white">{{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</span> &bull; {{ $siteTitle }}
                    @if ($mode === 'single' && !empty($selectedDept))
                        &bull; <span class="text-emerald-300 font-medium">{{ $selectedDept->kode }} - {{ $selectedDept->deskripsi }}</span>
                    @endif
                </p>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex flex-wrap items-center gap-2.5 no-print">
                <a href="{{ route('laba_rugi_departemen.filter', request()->query()) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg text-xs font-semibold backdrop-blur border border-white/15 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Ubah Filter
                </a>
                <a href="{{ route('laba_rugi_departemen.export', array_merge(request()->query(), ['format' => 'excel'])) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-semibold shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Excel
                </a>
                <a href="{{ route('laba_rugi_departemen.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-semibold shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Export PDF
                </a>
            </div>
        </div>

        {{-- 4 TOP KPI CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6 header-cards">
            {{-- KPI 1: Pendapatan Operasional --}}
            <div class="summary-card bg-white/10 border border-white/15 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-300">TOTAL PENDAPATAN</span>
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                </div>
                <div class="text-lg sm:text-xl font-bold text-white mt-1">
                    {{ $kpi['formattedRevenue'] ?? ('Rp ' . number_format($kpi['totalRevenue'] ?? 0, 2, ',', '.')) }}
                </div>
                <div class="text-[11px] text-blue-200 mt-1">
                    {{ $mode === 'single' ? ($selectedDept->deskripsi ?? 'Cabang') : 'Total Konsolidasi Perusahaan' }}
                </div>
            </div>

            {{-- KPI 2: Beban Pokok (HPP) & Gross Margin --}}
            <div class="summary-card bg-white/10 border border-white/15 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-300">BEBAN POKOK (HPP)</span>
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                </div>
                <div class="text-lg sm:text-xl font-bold text-white mt-1">
                    {{ $kpi['formattedCogs'] ?? ('Rp ' . number_format($kpi['totalCogs'] ?? 0, 2, ',', '.')) }}
                </div>
                <div class="text-[11px] text-amber-200 mt-1">
                    Laba Kotor: {{ $kpi['formattedGrossProfit'] ?? ('Rp ' . number_format($kpi['grossProfit'] ?? 0, 2, ',', '.')) }} ({{ $kpi['grossMarginPercent'] ?? 0 }}% GM)
                </div>
            </div>

            {{-- KPI 3: Beban Operasional & Opex Ratio --}}
            <div class="summary-card bg-white/10 border border-white/15 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-300">BEBAN OPERASIONAL (OPEX)</span>
                    <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                </div>
                <div class="text-lg sm:text-xl font-bold text-white mt-1">
                    {{ $kpi['formattedOpex'] ?? ('Rp ' . number_format($kpi['totalOpex'] ?? 0, 2, ',', '.')) }}
                </div>
                <div class="text-[11px] text-purple-200 mt-1">
                    Laba Usaha (EBIT): {{ $kpi['formattedEbit'] ?? ('Rp ' . number_format($kpi['ebit'] ?? 0, 2, ',', '.')) }} ({{ $kpi['opexRatioPercent'] ?? 0 }}% Rasio)
                </div>
            </div>

            {{-- KPI 4: Laba Bersih Konsolidasi --}}
            <div class="summary-card bg-emerald-500/20 border border-emerald-400/30 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-emerald-200">LABA BERSIH TAHUN BERJALAN</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                </div>
                <div class="text-lg sm:text-xl font-bold text-emerald-100 mt-1">
                    {{ $kpi['formattedNetProfit'] ?? ('Rp ' . number_format($kpi['netProfit'] ?? 0, 2, ',', '.')) }}
                </div>
                <div class="text-[11px] text-emerald-300 mt-1 flex items-center justify-between font-semibold">
                    <span>Net Margin: {{ $kpi['netMarginPercent'] ?? 0 }}%</span>
                    <button type="button" onclick="openReconciliationModal()" class="text-emerald-200 hover:text-white underline text-[11px] cursor-pointer">
                        Audit Neraca
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- FULLSCREEN FOCUS HEADER (Shown only in Fullscreen Mode) --}}
    <div id="fullscreenTopBar" class="hidden pb-3 mb-3 border-b border-slate-200 flex items-center justify-between bg-white rounded-xl p-3 shadow-sm no-print">
        <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-indigo-100 text-indigo-800">
                Mode Fokus Layar Penuh
            </span>
            <span class="text-xs text-slate-600 font-semibold">
                {{ $siteTitle }} &bull; Periode {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
            </span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[11px] text-slate-400 font-medium mr-2">Tekan <kbd class="px-1.5 py-0.5 bg-slate-200 text-slate-700 rounded text-[10px]">Esc</kbd> untuk keluar</span>
            <button type="button" onclick="toggleFullscreen()" class="ctrl-btn bg-slate-800 text-white hover:bg-slate-900 border-slate-800">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Keluar Focus
            </button>
        </div>
    </div>

    {{-- TOOLBAR: ACCORDION, BRANCH TOGGLE, FOCUS MODE & QUICK SEARCH --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3.5 mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 no-print">
        {{-- Left controls --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold text-slate-500 mr-1">Hierarki:</span>
            <button type="button" onclick="setLevel('all')" class="ctrl-btn active" id="btn-lvl-all">Expand All</button>
            <button type="button" onclick="setLevel(2)" class="ctrl-btn" id="btn-lvl-2">Level 2</button>
            <button type="button" onclick="setLevel(1)" class="ctrl-btn" id="btn-lvl-1">Level 1</button>
            <button type="button" onclick="setLevel(0)" class="ctrl-btn" id="btn-lvl-0">Collapse All</button>

            @if ($mode === 'matrix')
                {{-- Branch Column Visibility Toggle --}}
                <div class="relative inline-block text-left" id="colVisibilityDropdownWrap">
                    <button type="button" onclick="toggleColVisibilityMenu()" class="ctrl-btn" id="btnColVisibility">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        <span>Cabang</span>
                        <span class="text-[10px] bg-slate-200 text-slate-700 px-1.5 py-0.2 rounded font-semibold" id="colCountBadge">{{ count($departments) }}</span>
                    </button>
                    <div id="colVisibilityMenu" class="hidden absolute left-0 mt-1 w-64 bg-white rounded-xl shadow-2xl border border-slate-200 p-3 z-50 text-xs">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                            <span class="font-bold text-slate-700">Tampilkan Cabang:</span>
                            <div class="flex gap-2">
                                <button type="button" onclick="setAllDeptVisibility(true)" class="text-[11px] text-indigo-600 hover:underline">Semua</button>
                                <span class="text-slate-300">|</span>
                                <button type="button" onclick="setAllDeptVisibility(false)" class="text-[11px] text-slate-500 hover:underline">Kosongkan</button>
                            </div>
                        </div>
                        <div class="max-h-60 overflow-y-auto space-y-1.5 pr-1">
                            @foreach ($departments as $dept)
                                <label class="flex items-center gap-2 p-1.5 rounded hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" checked class="dept-col-toggle rounded border-slate-300 text-indigo-600 focus:ring-indigo-300"
                                           data-dept-id="{{ $dept->id }}" onchange="toggleDeptColumn('{{ $dept->id }}', this.checked)">
                                    <span class="text-slate-700 font-medium">{{ $dept->kode }} - {{ $dept->deskripsi }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Fullscreen Toggle Button --}}
                <button type="button" onclick="toggleFullscreen()" class="ctrl-btn" id="btnFullscreen" title="Masuk Mode Fokus Layar Penuh">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    <span>Focus Mode</span>
                </button>
            @endif
        </div>

        {{-- Quick Search --}}
        <div class="relative w-full md:w-72">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" id="quickSearchInput" onkeyup="filterTableRows(this.value)"
                placeholder="Cari akun atau kode..."
                class="w-full pl-9 pr-4 py-1.5 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400">
        </div>
    </div>

    {{-- TABEL MATRIKS / SINGLE LABA RUGI --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col" id="matrixCard">
        <div class="table-container flex-1" id="matrixTableContainer">
            @if ($mode === 'matrix')
                {{-- ============================================================
                     MODE 1: COMPARATIVE MATRIX VIEW (ALL DEPARTMENTS)
                     Freeze Panes Order:
                     Col 1: Keterangan / Nama Akun (Sticky Left: 0)
                     Col 2: Total Konsolidasi (Sticky Left: 300px)
                     Col 3..N: Cabang-cabang Operasional (Scrollable)
                     Col N+1: Eliminasi R/K
                     Col N+2: Non-Departemen (Paling Pojok Kanan)
                ============================================================ --}}
                <table class="pnl-matrix-table w-full text-left" id="matrixReportTable">
                    <thead>
                        <tr>
                            {{-- Freeze Col 1: Nama Akun --}}
                            <th class="corner-sticky-1 text-left" style="min-width: 300px; width: 300px;">
                                KETERANGAN / NAMA AKUN
                            </th>
                            {{-- Freeze Col 2: Total Konsolidasi --}}
                            <th class="corner-sticky-2 text-right" style="min-width: 160px; width: 160px;">
                                TOTAL KONSOLIDASI<br>
                                <span class="text-[10px] text-indigo-300 font-normal">RESMI PERUSAHAAN</span>
                            </th>
                            {{-- Branches --}}
                            @foreach ($departments as $d)
                                <th class="text-right min-w-[130px] col-dept-{{ $d->id }}">
                                    {{ $d->kode }}<br>
                                    <span class="text-[10px] text-slate-300 font-normal">{{ $d->deskripsi }}</span>
                                </th>
                            @endforeach
                            {{-- Eliminasi --}}
                            <th class="text-right min-w-[120px] bg-slate-950 text-amber-200 border-l border-slate-700">
                                ELIMINASI<br><span class="text-[10px] text-amber-300 font-normal">R/K ANTAR-UNIT</span>
                            </th>
                            {{-- Non-Departemen --}}
                            <th class="text-right min-w-[120px] bg-slate-900 text-slate-300">
                                NON-DEPT<br><span class="text-[10px] text-slate-400 font-normal">UNASSIGNED</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sections as $secKey => $sec)
                            @if (!empty($sec['row_type']))
                                {{-- ========================================================
                                     BARIS SUBTOTAL SEKSI / LABA KOTOR / EBIT / EBT / NET PROFIT
                                     Selalu tampil (row-fixed) saat filter hierarki level
                                ======================================================== --}}
                                @php
                                    $isNetProfit   = ($sec['row_type'] === 'subtotal_net_profit');
                                    $isGrossProfit = ($sec['row_type'] === 'subtotal_gross_profit');
                                    $isOpex        = ($sec['row_type'] === 'subtotal_opex');
                                    $isEbit        = ($sec['row_type'] === 'subtotal_ebit');
                                    $isEbitda      = ($sec['row_type'] === 'indicator_ebitda' || $sec['row_type'] === 'subtotal_ebitda');
                                    $isEbt         = ($sec['row_type'] === 'subtotal_ebt');

                                    if ($isNetProfit) {
                                        $rowClass     = 'row-fixed row-total bg-gradient-to-r from-emerald-900 via-teal-950 to-emerald-900 text-white font-extrabold text-sm border-t-4 border-emerald-400';
                                        $sticky1Class = 'bg-emerald-900 text-white';
                                        $sticky2Class = 'bg-teal-950 text-emerald-300 font-extrabold text-base';
                                        $cellClass    = 'text-emerald-200 font-bold';
                                    } elseif ($isGrossProfit) {
                                        $rowClass     = 'row-fixed row-subtotal bg-indigo-50/90 text-indigo-950 font-bold text-xs border-t-2 border-b-2 border-indigo-200';
                                        $sticky1Class = 'bg-indigo-50 text-indigo-950';
                                        $sticky2Class = 'bg-indigo-100/80 text-indigo-950 font-bold';
                                        $cellClass    = 'text-indigo-950 font-semibold';
                                    } elseif ($isOpex) {
                                        $rowClass     = 'row-fixed row-subtotal bg-amber-50/90 text-amber-950 font-bold text-xs border-t-2 border-b-2 border-amber-200';
                                        $sticky1Class = 'bg-amber-50 text-amber-950';
                                        $sticky2Class = 'bg-amber-100/80 text-amber-950 font-bold';
                                        $cellClass    = 'text-amber-950 font-semibold';
                                    } else {
                                        $rowClass     = 'row-fixed row-subtotal bg-slate-100 text-slate-900 font-bold text-xs border-t border-b border-slate-300';
                                        $sticky1Class = 'bg-slate-100 text-slate-900';
                                        $sticky2Class = 'bg-slate-200/80 text-slate-900 font-bold';
                                        $cellClass    = 'text-slate-900 font-semibold';
                                    }

                                    $secTotals = $sec['totals'] ?? ($sec['balances'] ?? []);
                                    $totSubVal = $secTotals['total'] ?? 0;
                                @endphp
                                <tr class="{{ $rowClass }}" data-level="subtotal" data-section="{{ $secKey }}">
                                    <td class="col-sticky-1 {{ $sticky1Class }} py-2.5 px-4 font-bold text-xs uppercase tracking-wider">
                                        <div class="cell-content flex items-center justify-between">
                                            <span>{{ $sec['title'] }}</span>
                                            @if (!empty($sec['marginPercent']))
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-white/20 text-current mr-2">
                                                    {{ $sec['marginPercent']['total'] ?? 0 }}% GM
                                                </span>
                                            @elseif (!empty($sec['opexRatioPercent']))
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-white/20 text-current mr-2">
                                                    {{ $sec['opexRatioPercent']['total'] ?? 0 }}% Rasio
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="col-sticky-2 {{ $sticky2Class }} text-right py-2.5 px-3 num-cell font-bold">
                                        {{ $totSubVal < 0 ? '(' . number_format(abs($totSubVal), 2, ',', '.') . ')' : number_format($totSubVal, 2, ',', '.') }}
                                    </td>
                                    @foreach ($departments as $d)
                                        @php $dSubVal = $secTotals[$d->id] ?? 0; @endphp
                                        <td class="text-right py-2.5 px-3 num-cell {{ $cellClass }} col-dept-{{ $d->id }}">
                                            {{ $dSubVal < 0 ? '(' . number_format(abs($dSubVal), 2, ',', '.') . ')' : number_format($dSubVal, 2, ',', '.') }}
                                        </td>
                                    @endforeach
                                    @php $elimSubVal = $secTotals['eliminasi'] ?? 0; @endphp
                                    <td class="text-right py-2.5 px-3 num-cell text-amber-700 bg-amber-50/50 border-l border-slate-200">
                                        {{ $elimSubVal < 0 ? '(' . number_format(abs($elimSubVal), 2, ',', '.') . ')' : number_format($elimSubVal, 2, ',', '.') }}
                                    </td>
                                    @php $unSubVal = $secTotals['unassigned'] ?? 0; @endphp
                                    <td class="text-right py-2.5 px-3 num-cell text-slate-600 bg-slate-50/50">
                                        {{ $unSubVal < 0 ? '(' . number_format(abs($unSubVal), 2, ',', '.') . ')' : number_format($unSubVal, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @else
                                {{-- ========================================================
                                     HEADER SEKSI / LEVEL 1 GRUP AKUN
                                ======================================================== --}}
                                @php
                                    $secCode       = $sec['code'] ?? '';
                                    $secTitle      = $sec['title'] ?? ($sec['nama_akun'] ?? '');
                                    $secTotals     = $sec['totals'] ?? ($sec['balances'] ?? []);
                                    $groupAccounts = $sec['rows'] ?? ($sec['children'] ?? []);
                                    $vSecTot       = $secTotals['total'] ?? 0;
                                @endphp
                                <tr class="row-section row-group bg-gradient-to-r from-slate-900 to-indigo-950 text-white font-bold"
                                    data-section="sec-{{ $secKey }}" data-group="{{ $secCode }}" data-code="{{ $secCode }}" onclick="toggleSection('sec-{{ $secKey }}')">
                                    <td class="col-sticky-1 bg-slate-900 py-2.5 px-4 font-bold text-xs uppercase tracking-wider text-white">
                                        <div class="cell-content flex items-center gap-2">
                                            <span class="toggle-chevron open" id="chevron-sec-{{ $secKey }}" style="color: white;">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                            </span>
                                            <span>
                                                @if ($showAccountNumber)<span class="text-indigo-300 mr-1.5">{{ $secCode }}</span>@endif
                                                {{ $secTitle }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="col-sticky-2 bg-indigo-950 text-right py-2.5 px-3 num-cell font-bold text-white">
                                        {{ $vSecTot < 0 ? '(' . number_format(abs($vSecTot), 2, ',', '.') . ')' : number_format($vSecTot, 2, ',', '.') }}
                                    </td>
                                    @foreach ($departments as $d)
                                        @php $vSecD = $secTotals[$d->id] ?? 0; @endphp
                                        <td class="text-right py-2.5 px-3 num-cell font-semibold text-slate-100 col-dept-{{ $d->id }}">
                                            {{ $vSecD < 0 ? '(' . number_format(abs($vSecD), 2, ',', '.') . ')' : number_format($vSecD, 2, ',', '.') }}
                                        </td>
                                    @endforeach
                                    @php $vSecElim = $secTotals['eliminasi'] ?? 0; @endphp
                                    <td class="text-right py-2.5 px-3 num-cell font-semibold text-amber-300 bg-slate-950/60 border-l border-slate-700">
                                        {{ $vSecElim < 0 ? '(' . number_format(abs($vSecElim), 2, ',', '.') . ')' : number_format($vSecElim, 2, ',', '.') }}
                                    </td>
                                    @php $vSecUn = $secTotals['unassigned'] ?? 0; @endphp
                                    <td class="text-right py-2.5 px-3 num-cell font-semibold text-slate-300 bg-slate-900/60">
                                        {{ $vSecUn < 0 ? '(' . number_format(abs($vSecUn), 2, ',', '.') . ')' : number_format($vSecUn, 2, ',', '.') }}
                                    </td>
                                </tr>

                                {{-- AKUN (Level 2) --}}
                                @foreach ($groupAccounts as $acc)
                                    @php
                                        $accCode   = $acc['kode_akun'] ?? ($acc['kode'] ?? '');
                                        $accName   = $acc['nama_akun'] ?? ($acc['nama'] ?? '');
                                        $accTotals = $acc['balances'] ?? ($acc['totals'] ?? []);
                                        $subList   = !empty($acc['sub_accounts']) ? $acc['sub_accounts'] : ($acc['children'] ?? []);
                                        $hasChild  = !empty($subList);
                                        $isZeroAll = $acc['is_zero'] ?? true;
                                        $accRowId  = 'acc-' . $accCode;
                                    @endphp
                                    @if (!$hideAccountWithZero || !$isZeroAll)
                                        <tr class="row-account collapsible-row sec-{{ $secKey }}-rows bg-white hover:bg-slate-50 text-slate-700 text-xs border-b border-slate-100"
                                            data-account="{{ $accCode }}" data-account-id="{{ $accRowId }}" data-code="{{ $accCode }}"
                                            @if($hasChild) onclick="toggleAccount('{{ $accRowId }}')" @endif>

                                            <td class="col-sticky-1 bg-white py-2 px-4 pl-8 text-xs font-semibold text-slate-700">
                                                <div class="cell-content flex items-center gap-1.5">
                                                    @if ($hasChild)
                                                        <span class="toggle-chevron open" id="chevron-{{ $accRowId }}" style="color: #64748b;">
                                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                        </span>
                                                    @else
                                                        <span class="w-4 inline-block"></span>
                                                    @endif
                                                    <span class="truncate account-name-label">
                                                        @if ($showAccountNumber)<span class="text-slate-400 mr-1.5 account-code-text">{{ $accCode }}</span>@endif
                                                        {{ $accName }}
                                                    </span>
                                                </div>
                                            </td>
                                            {{-- Sticky Col 2: Total Konsolidasi --}}
                                            <td class="col-sticky-2 bg-slate-50 text-right py-2 px-3 num-cell font-bold text-slate-800" style="background: #f8fafc;">
                                                @php $totVal = $accTotals['total'] ?? 0; @endphp
                                                @if (abs($totVal) > 0.001)
                                                    <a href="{{ route('buku_besar.buku_besar_report', [
                                                        'start_date' => $startDate,
                                                        'end_date' => $endDate,
                                                        'selected_accounts' => $accCode
                                                    ]) }}" target="_blank" class="drilldown-link {{ $totVal < 0 ? 'num-negative' : 'num-positive' }}">
                                                        {{ $totVal < 0 ? '(' . number_format(abs($totVal), 2, ',', '.') . ')' : number_format($totVal, 2, ',', '.') }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-300">0,00</span>
                                                @endif
                                            </td>
                                            {{-- Branches --}}
                                            @foreach ($departments as $d)
                                                @php $dVal = $accTotals[$d->id] ?? 0; @endphp
                                                <td class="text-right py-2 px-3 num-cell font-semibold text-slate-600 col-dept-{{ $d->id }}">
                                                    @if (abs($dVal) > 0.001)
                                                        <a href="{{ route('buku_besar.buku_besar_report', [
                                                            'start_date' => $startDate,
                                                            'end_date' => $endDate,
                                                            'selected_accounts' => $accCode,
                                                            'departemen_id' => $d->id
                                                        ]) }}" target="_blank" class="drilldown-link {{ $dVal < 0 ? 'num-negative' : 'num-positive' }}">
                                                            {{ $dVal < 0 ? '(' . number_format(abs($dVal), 2, ',', '.') . ')' : number_format($dVal, 2, ',', '.') }}
                                                        </a>
                                                    @else
                                                        <span class="text-slate-300">0,00</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            {{-- Eliminasi --}}
                                            @php $elimVal = $accTotals['eliminasi'] ?? 0; @endphp
                                            <td class="text-right py-2 px-3 num-cell font-semibold text-amber-700 bg-amber-50/20 border-l border-slate-100">
                                                {{ $elimVal < 0 ? '(' . number_format(abs($elimVal), 2, ',', '.') . ')' : number_format($elimVal, 2, ',', '.') }}
                                            </td>
                                            {{-- Non-Dept --}}
                                            @php $unVal = $accTotals['unassigned'] ?? 0; @endphp
                                            <td class="text-right py-2 px-3 num-cell font-semibold text-slate-500 bg-slate-50/30">
                                                @if (abs($unVal) > 0.001)
                                                    <a href="{{ route('buku_besar.buku_besar_report', [
                                                        'start_date' => $startDate,
                                                        'end_date' => $endDate,
                                                        'selected_accounts' => $accCode,
                                                        'departemen_id' => 'unassigned'
                                                    ]) }}" target="_blank" class="drilldown-link {{ $unVal < 0 ? 'num-negative' : 'num-positive' }}">
                                                        {{ $unVal < 0 ? '(' . number_format(abs($unVal), 2, ',', '.') . ')' : number_format($unVal, 2, ',', '.') }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-300">0,00</span>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- SUB AKUN (Level 3) --}}
                                        @if ($hasChild)
                                            @foreach ($subList as $sub)
                                                @php
                                                    $subCode   = $sub['kode_akun'] ?? ($sub['kode'] ?? '');
                                                    $subName   = $sub['nama_akun'] ?? ($sub['nama'] ?? '');
                                                    $subTotals = $sub['balances'] ?? ($sub['totals'] ?? []);
                                                    $isSubZero = $sub['is_zero'] ?? true;
                                                    $subTot    = $subTotals['total'] ?? 0;
                                                @endphp
                                                @if (!$hideAccountWithZero || !$isSubZero)
                                                    <tr class="row-subaccount collapsible-row sec-{{ $secKey }}-rows {{ $accRowId }}-rows bg-slate-50/40 hover:bg-slate-100/70 text-slate-600 text-[11px] border-b border-slate-100"
                                                        data-parent-account="{{ $accRowId }}">
                                                        <td class="col-sticky-1 bg-slate-50/40 py-1.5 px-4 pl-14 text-[11px] text-slate-600">
                                                            <div class="cell-content flex items-center gap-1.5">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 mr-1 shrink-0"></span>
                                                                <span class="truncate account-name-label">
                                                                    @if ($showAccountNumber)<span class="text-slate-400 mr-1 account-code-text">{{ $subCode }}</span>@endif
                                                                    {{ $subName }}
                                                                </span>
                                                            </div>
                                                        </td>
                                                        {{-- Sub Total Konsolidasi Link --}}
                                                        <td class="col-sticky-2 bg-slate-50/40 text-right py-1.5 px-3 num-cell font-medium text-slate-700">
                                                            @if (abs($subTot) > 0.001)
                                                                <a href="{{ route('buku_besar.buku_besar_report', [
                                                                    'start_date' => $startDate,
                                                                    'end_date' => $endDate,
                                                                    'selected_accounts' => $subCode
                                                                ]) }}" target="_blank" class="drilldown-link {{ $subTot < 0 ? 'num-negative' : 'num-positive' }}">
                                                                    {{ $subTot < 0 ? '(' . number_format(abs($subTot), 2, ',', '.') . ')' : number_format($subTot, 2, ',', '.') }}
                                                                </a>
                                                            @else
                                                                <span class="text-slate-300">0,00</span>
                                                            @endif
                                                        </td>
                                                        {{-- Sub Branches Links --}}
                                                        @foreach ($departments as $d)
                                                            @php $subValD = $subTotals[$d->id] ?? 0; @endphp
                                                            <td class="text-right py-1.5 px-3 num-cell text-slate-500 col-dept-{{ $d->id }}">
                                                                @if (abs($subValD) > 0.001)
                                                                    <a href="{{ route('buku_besar.buku_besar_report', [
                                                                        'start_date' => $startDate,
                                                                        'end_date' => $endDate,
                                                                        'selected_accounts' => $subCode,
                                                                        'departemen_id' => $d->id
                                                                    ]) }}" target="_blank" class="drilldown-link {{ $subValD < 0 ? 'num-negative' : 'num-positive' }}">
                                                                        {{ $subValD < 0 ? '(' . number_format(abs($subValD), 2, ',', '.') . ')' : number_format($subValD, 2, ',', '.') }}
                                                                    </a>
                                                                @else
                                                                    <span class="text-slate-300">0,00</span>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                        {{-- Sub Eliminasi --}}
                                                        @php $subElim = $subTotals['eliminasi'] ?? 0; @endphp
                                                        <td class="text-right py-1.5 px-3 num-cell text-amber-600 bg-amber-50/20 border-l border-slate-100">
                                                            {{ $subElim < 0 ? '(' . number_format(abs($subElim), 2, ',', '.') . ')' : number_format($subElim, 2, ',', '.') }}
                                                        </td>
                                                        {{-- Sub Non-Dept Link --}}
                                                        @php $subUnass = $subTotals['unassigned'] ?? 0; @endphp
                                                        <td class="text-right py-1.5 px-3 num-cell text-slate-400 bg-slate-50/20">
                                                            @if (abs($subUnass) > 0.001)
                                                                <a href="{{ route('buku_besar.buku_besar_report', [
                                                                    'start_date' => $startDate,
                                                                    'end_date' => $endDate,
                                                                    'selected_accounts' => $subCode,
                                                                    'departemen_id' => 'unassigned'
                                                                ]) }}" target="_blank" class="drilldown-link {{ $subUnass < 0 ? 'num-negative' : 'num-positive' }}">
                                                                    {{ $subUnass < 0 ? '(' . number_format(abs($subUnass), 2, ',', '.') . ')' : number_format($subUnass, 2, ',', '.') }}
                                                                </a>
                                                            @else
                                                                <span class="text-slate-300">0,00</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                        @endforeach

                        {{-- BOTTOM LINE: LABA BERSIH TAHUN BERJALAN --}}
                        @php $vNetTot = $reconciliation['pnlPerDept']['total'] ?? 0; @endphp
                        <tr class="row-fixed row-total bg-gradient-to-r from-emerald-900 via-teal-950 to-emerald-900 text-white font-extrabold text-sm border-t-4 border-emerald-400" data-level="bottomline">
                            <td class="col-sticky-1 bg-emerald-900 py-3.5 px-4 font-bold text-xs uppercase tracking-wider text-white">
                                <div class="cell-content flex items-center justify-between">
                                    <span class="tracking-wider uppercase">LABA BERSIH TAHUN BERJALAN (NET PROFIT)</span>
                                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">
                                        PSAK 1
                                    </span>
                                </div>
                            </td>
                            <td class="col-sticky-2 bg-teal-950 text-right py-3.5 px-3 num-cell font-extrabold text-emerald-300 text-base">
                                {{ $vNetTot < 0 ? '(' . number_format(abs($vNetTot), 2, ',', '.') . ')' : number_format($vNetTot, 2, ',', '.') }}
                            </td>
                            @foreach ($departments as $d)
                                @php $vNetD = $reconciliation['pnlPerDept'][$d->id] ?? 0; @endphp
                                <td class="text-right py-3.5 px-3 num-cell text-emerald-200 font-bold col-dept-{{ $d->id }}">
                                    {{ $vNetD < 0 ? '(' . number_format(abs($vNetD), 2, ',', '.') . ')' : number_format($vNetD, 2, ',', '.') }}
                                </td>
                            @endforeach
                            @php $vNetElim = $reconciliation['pnlPerDept']['eliminasi'] ?? 0; @endphp
                            <td class="text-right py-3.5 px-3 num-cell text-amber-200 bg-emerald-950 border-l border-emerald-800">
                                {{ $vNetElim < 0 ? '(' . number_format(abs($vNetElim), 2, ',', '.') . ')' : number_format($vNetElim, 2, ',', '.') }}
                            </td>
                            @php $vNetUnass = $reconciliation['pnlPerDept']['unassigned'] ?? 0; @endphp
                            <td class="text-right py-3.5 px-3 num-cell text-emerald-100 bg-emerald-950">
                                {{ $vNetUnass < 0 ? '(' . number_format(abs($vNetUnass), 2, ',', '.') . ')' : number_format($vNetUnass, 2, ',', '.') }}
                            </td>
                        </tr>

                        {{-- BARIS REKONSILIASI DENGAN NERACA DEPARTEMEN --}}
                        @php $vNeracaTot = $reconciliation['neracaPerDept']['total'] ?? 0; @endphp
                        <tr class="row-fixed bg-slate-900 text-slate-300 text-xs border-t border-slate-700 font-medium" data-level="reconcile">
                            <td class="col-sticky-1 bg-slate-900 py-2.5 px-4 text-slate-400 text-xs font-semibold">
                                <div class="cell-content flex items-center gap-1.5">
                                    <span class="text-slate-400 text-[11px]">Laba Tahun Berjalan di Ekuitas Neraca Departemen</span>
                                </div>
                            </td>
                            <td class="col-sticky-2 bg-slate-900 text-right py-2.5 px-3 num-cell font-semibold text-slate-300">
                                {{ $vNeracaTot < 0 ? '(' . number_format(abs($vNeracaTot), 2, ',', '.') . ')' : number_format($vNeracaTot, 2, ',', '.') }}
                            </td>
                            @foreach ($departments as $d)
                                @php $vNeracaD = $reconciliation['neracaPerDept'][$d->id] ?? 0; @endphp
                                <td class="text-right py-2.5 px-3 num-cell text-slate-300 col-dept-{{ $d->id }}">
                                    {{ $vNeracaD < 0 ? '(' . number_format(abs($vNeracaD), 2, ',', '.') . ')' : number_format($vNeracaD, 2, ',', '.') }}
                                </td>
                            @endforeach
                            @php $vNeracaElim = $reconciliation['neracaPerDept']['eliminasi'] ?? 0; @endphp
                            <td class="text-right py-2.5 px-3 num-cell text-slate-400 bg-slate-950 border-l border-slate-700">
                                {{ $vNeracaElim < 0 ? '(' . number_format(abs($vNeracaElim), 2, ',', '.') . ')' : number_format($vNeracaElim, 2, ',', '.') }}
                            </td>
                            @php $vNeracaUnass = $reconciliation['neracaPerDept']['unassigned'] ?? 0; @endphp
                            <td class="text-right py-2.5 px-3 num-cell text-slate-400 bg-slate-900">
                                {{ $vNeracaUnass < 0 ? '(' . number_format(abs($vNeracaUnass), 2, ',', '.') . ')' : number_format($vNeracaUnass, 2, ',', '.') }}
                            </td>
                        </tr>

                        @php $vDiffTot = $reconciliation['diffPerDept']['total'] ?? 0; @endphp
                        <tr class="row-fixed bg-slate-950 text-[11px] text-slate-400 border-t border-slate-800" data-level="reconcile">
                            <td class="col-sticky-1 bg-slate-950 py-2 px-4 text-xs font-semibold">
                                <div class="cell-content flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full {{ ($reconciliation['isFullyReconciled'] ?? true) ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                    <span class="font-semibold text-slate-300">Selisih Rekonsiliasi (Discrepancy)</span>
                                </div>
                            </td>
                            <td class="col-sticky-2 bg-slate-950 text-right py-2 px-3 num-cell font-bold {{ ($reconciliation['isFullyReconciled'] ?? true) ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $vDiffTot < 0 ? '(' . number_format(abs($vDiffTot), 2, ',', '.') . ')' : number_format($vDiffTot, 2, ',', '.') }}
                            </td>
                            @foreach ($departments as $d)
                                @php $vDiffD = $reconciliation['diffPerDept'][$d->id] ?? 0; @endphp
                                <td class="text-right py-2 px-3 num-cell col-dept-{{ $d->id }} {{ abs($vDiffD) < 0.01 ? 'text-emerald-400' : 'text-rose-400 font-bold' }}">
                                    {{ $vDiffD < 0 ? '(' . number_format(abs($vDiffD), 2, ',', '.') . ')' : number_format($vDiffD, 2, ',', '.') }}
                                </td>
                            @endforeach
                            <td class="text-right py-2 px-3 num-cell text-slate-500 bg-slate-950 border-l border-slate-800">
                                0,00
                            </td>
                            @php $vDiffUnass = $reconciliation['diffPerDept']['unassigned'] ?? 0; @endphp
                            <td class="text-right py-2 px-3 num-cell text-slate-500 bg-slate-950">
                                {{ $vDiffUnass < 0 ? '(' . number_format(abs($vDiffUnass), 2, ',', '.') . ')' : number_format($vDiffUnass, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            @else
                {{-- ============================================================
                     MODE 2: SINGLE DEPARTMENT VIEW
                     Supports: Konsolidasi, Unassigned (Non-Dept), and Branch IDs
                ============================================================ --}}
                <div class="p-6">
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-indigo-50/60 p-4 rounded-xl border border-indigo-100">
                        <div>
                            <span class="text-xs font-semibold text-indigo-600 uppercase tracking-wider block">Laporan Unit Spesifik</span>
                            <h2 class="text-xl font-bold text-slate-900">{{ $selectedDept->kode ?? '' }} - {{ $selectedDept->deskripsi ?? 'Departemen' }}</h2>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-500 block">Laba Bersih Cabang</span>
                            <span class="text-xl font-bold text-emerald-600 num-cell">{{ $singleData['formattedNetProfit'] ?? 'Rp 0,00' }}</span>
                        </div>
                    </div>

                    <table class="w-full text-xs text-left border-collapse" id="singleReportTable">
                        <thead>
                            <tr class="bg-slate-900 text-white uppercase text-[11px]">
                                <th class="py-2.5 px-4 font-semibold" style="width: 140px;">Kode Akun</th>
                                <th class="py-2.5 px-4 font-semibold">Nama Akun</th>
                                <th class="py-2.5 px-4 font-semibold text-right" style="width: 180px;">Saldo Nominal</th>
                                <th class="py-2.5 px-4 font-semibold text-right" style="width: 180px;">% Terhadap Pendapatan (Rasio)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $singleLinkParams = [
                                    'start_date' => $startDate,
                                    'end_date' => $endDate,
                                ];
                                if ($selectedDeptId === 'unassigned') {
                                    $singleLinkParams['departemen_id'] = 'unassigned';
                                } elseif ($selectedDeptId !== 'konsolidasi' && is_numeric($selectedDeptId)) {
                                    $singleLinkParams['departemen_id'] = $selectedDeptId;
                                }
                            @endphp

                            @foreach ($sections as $secKey => $sec)
                                @if (!empty($sec['row_type']))
                                    @php
                                        $isNetProfit = ($sec['row_type'] === 'subtotal_net_profit');
                                        $rowVal = $sec['totals'][$selectedDeptId] ?? ($sec['balances'][$selectedDeptId] ?? ($sec['single_balance'] ?? 0));
                                        $pctVal = ($kpi['totalRevenue'] ?? 0) > 0 ? round(($rowVal / $kpi['totalRevenue']) * 100, 2) : 0;
                                    @endphp
                                    <tr class="row-fixed {{ $isNetProfit ? 'bg-emerald-900 text-white font-extrabold text-sm' : 'bg-indigo-50 text-indigo-950 font-bold text-xs' }}">
                                        <td colspan="2" class="py-2.5 px-4 uppercase tracking-wider">{{ $sec['title'] }}</td>
                                        <td class="py-2.5 px-4 text-right num-cell font-bold">
                                            {{ $rowVal < 0 ? '(' . number_format(abs($rowVal), 2, ',', '.') . ')' : number_format($rowVal, 2, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-4 text-right num-cell">{{ $pctVal }}%</td>
                                    </tr>
                                @else
                                    @php
                                        $secCode       = $sec['code'] ?? '';
                                        $secTitle      = $sec['title'] ?? ($sec['nama_akun'] ?? '');
                                        $secVal        = $sec['totals'][$selectedDeptId] ?? ($sec['balances'][$selectedDeptId] ?? ($sec['single_balance'] ?? 0));
                                        $secPct        = ($kpi['totalRevenue'] ?? 0) > 0 ? round(($secVal / $kpi['totalRevenue']) * 100, 2) : 0;
                                        $groupAccounts = $sec['rows'] ?? ($sec['children'] ?? []);
                                    @endphp
                                    <tr class="row-section row-group bg-slate-800 text-white font-bold text-xs"
                                        data-section="sec-{{ $secKey }}" data-group="{{ $secCode }}" data-code="{{ $secCode }}" onclick="toggleSection('sec-{{ $secKey }}')">
                                        <td colspan="2" class="py-2 px-4 uppercase tracking-wider">
                                            <div class="cell-content flex items-center gap-2">
                                                <span class="toggle-chevron open" id="chevron-single-sec-{{ $secKey }}" style="color: white;">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                </span>
                                                @if ($showAccountNumber)<span class="text-indigo-300 mr-1.5">{{ $secCode }}</span>@endif
                                                <span>{{ $secTitle }}</span>
                                            </div>
                                        </td>
                                        <td class="py-2 px-4 text-right num-cell text-white font-bold">
                                            {{ $secVal < 0 ? '(' . number_format(abs($secVal), 2, ',', '.') . ')' : number_format($secVal, 2, ',', '.') }}
                                        </td>
                                        <td class="py-2 px-4 text-right num-cell text-slate-300">{{ $secPct }}%</td>
                                    </tr>

                                    @foreach ($groupAccounts as $acc)
                                        @php
                                            $accCode   = $acc['kode_akun'] ?? ($acc['kode'] ?? '');
                                            $accName   = $acc['nama_akun'] ?? ($acc['nama'] ?? '');
                                            $accVal    = $acc['balances'][$selectedDeptId] ?? ($acc['totals'][$selectedDeptId] ?? ($acc['single_balance'] ?? 0));
                                            $accPct    = ($kpi['totalRevenue'] ?? 0) > 0 ? round(($accVal / $kpi['totalRevenue']) * 100, 2) : 0;
                                            $subList   = !empty($acc['sub_accounts']) ? $acc['sub_accounts'] : ($acc['children'] ?? []);
                                            $hasChild  = !empty($subList);
                                            $accRowId  = 'acc-' . $accCode;
                                        @endphp
                                        @if (!$hideAccountWithZero || abs($accVal) >= 0.01)
                                            <tr class="row-account collapsible-row sec-{{ $secKey }}-rows bg-white hover:bg-slate-50 text-slate-700 text-xs border-b border-slate-100"
                                                data-account="{{ $accCode }}" data-account-id="{{ $accRowId }}" data-code="{{ $accCode }}"
                                                @if($hasChild) onclick="toggleAccount('{{ $accRowId }}')" @endif>
                                                <td class="py-1.5 px-4 pl-6 text-slate-500">{{ $accCode }}</td>
                                                <td class="py-1.5 px-4 pl-6">
                                                    <div class="cell-content flex items-center gap-1.5">
                                                        @if ($hasChild)
                                                            <span class="toggle-chevron open" id="chevron-single-{{ $accRowId }}" style="color: #64748b;">
                                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                            </span>
                                                        @else
                                                            <span class="w-4 inline-block"></span>
                                                        @endif
                                                        <span class="account-name-label font-semibold">{{ $accName }}</span>
                                                    </div>
                                                </td>
                                                <td class="py-1.5 px-4 text-right num-cell font-bold">
                                                    @if (abs($accVal) > 0.001)
                                                        <a href="{{ route('buku_besar.buku_besar_report', array_merge($singleLinkParams, ['selected_accounts' => $accCode])) }}"
                                                            target="_blank" class="drilldown-link {{ $accVal < 0 ? 'num-negative' : 'num-positive' }}">
                                                            {{ $accVal < 0 ? '(' . number_format(abs($accVal), 2, ',', '.') . ')' : number_format($accVal, 2, ',', '.') }}
                                                        </a>
                                                    @else
                                                        <span class="text-slate-300">0,00</span>
                                                    @endif
                                                </td>
                                                <td class="py-1.5 px-4 text-right num-cell text-slate-400">{{ $accPct }}%</td>
                                            </tr>

                                            @if ($hasChild)
                                                @foreach ($subList as $sub)
                                                    @php
                                                        $subCode = $sub['kode_akun'] ?? ($sub['kode'] ?? '');
                                                        $subName = $sub['nama_akun'] ?? ($sub['nama'] ?? '');
                                                        $subVal  = $sub['balances'][$selectedDeptId] ?? ($sub['totals'][$selectedDeptId] ?? ($sub['single_balance'] ?? 0));
                                                        $subPct  = ($kpi['totalRevenue'] ?? 0) > 0 ? round(($subVal / $kpi['totalRevenue']) * 100, 2) : 0;
                                                    @endphp
                                                    @if (!$hideAccountWithZero || abs($subVal) >= 0.01)
                                                        <tr class="row-subaccount collapsible-row sec-{{ $secKey }}-rows {{ $accRowId }}-rows bg-slate-50/50 hover:bg-slate-100/50 text-slate-600 text-[11px] border-b border-slate-100"
                                                            data-parent-account="{{ $accRowId }}">
                                                            <td class="py-1 px-4 pl-12 text-slate-400">{{ $subCode }}</td>
                                                            <td class="py-1 px-4 pl-12">
                                                                <div class="cell-content flex items-center gap-1.5">
                                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 mr-1 shrink-0"></span>
                                                                    <span class="account-name-label">{{ $subName }}</span>
                                                                </div>
                                                            </td>
                                                            <td class="py-1 px-4 text-right num-cell">
                                                                @if (abs($subVal) > 0.001)
                                                                    <a href="{{ route('buku_besar.buku_besar_report', array_merge($singleLinkParams, ['selected_accounts' => $subCode])) }}"
                                                                        target="_blank" class="drilldown-link {{ $subVal < 0 ? 'num-negative' : 'num-positive' }}">
                                                                        {{ $subVal < 0 ? '(' . number_format(abs($subVal), 2, ',', '.') . ')' : number_format($subVal, 2, ',', '.') }}
                                                                    </a>
                                                                @else
                                                                    <span class="text-slate-300">0,00</span>
                                                                @endif
                                                            </td>
                                                            <td class="py-1 px-4 text-right num-cell text-slate-400 text-[10px]">{{ $subPct }}%</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Synced Floating Horizontal Scrollbar --}}
        <div class="floating-scrollbar-wrap overflow-x-auto no-print" id="floatingScrollbarWrap">
            <div class="floating-scrollbar-inner" id="floatingScrollbarTrack"></div>
        </div>
    </div>

</div>

{{-- MODAL AUDIT REKONSILIASI NERACA --}}
<div id="reconciliationModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-3xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 p-5 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-base leading-tight">Audit Rekonsiliasi Neraca vs Laba Rugi</h3>
                    <p class="text-xs text-indigo-200/80">Verifikasi Integritas Saldo Laba Tahun Berjalan</p>
                </div>
            </div>
            <button type="button" onclick="closeReconciliationModal()" class="text-slate-400 hover:text-white p-1 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-5 max-h-[70vh] overflow-y-auto">
            <div class="mb-4 p-3 rounded-xl {{ ($reconciliation['isFullyReconciled'] ?? true) ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800' }} flex items-center justify-between">
                <div>
                    <span class="font-bold text-sm block">Status Rekonsiliasi: {{ $reconciliation['statusText'] ?? 'Klop' }}</span>
                    <span class="text-xs text-slate-600">Selisih Maksimal: <strong>{{ $reconciliation['formattedMaxDiscrepancy'] ?? 'Rp 0,00' }}</strong></span>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg {{ ($reconciliation['isFullyReconciled'] ?? true) ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                    {{ ($reconciliation['isFullyReconciled'] ?? true) ? 'AUDITED 100%' : 'DISCREPANCY DETECTED' }}
                </span>
            </div>

            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 border-b border-slate-200">
                        <th class="py-2 px-3 font-semibold">Departemen / Unit</th>
                        <th class="py-2 px-3 font-semibold text-right">Laba Rugi (P&L)</th>
                        <th class="py-2 px-3 font-semibold text-right">Neraca (Equity)</th>
                        <th class="py-2 px-3 font-semibold text-right">Selisih</th>
                        <th class="py-2 px-3 font-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($reconciliation['departmentItems'] ?? [] as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2 px-3 font-medium text-slate-800">
                                <span class="text-slate-400 mr-1">{{ $item['kode'] }}</span>
                                {{ $item['name'] }}
                            </td>
                            <td class="py-2 px-3 num-cell text-right text-slate-700">Rp {{ number_format($item['pnl'], 2, ',', '.') }}</td>
                            <td class="py-2 px-3 num-cell text-right text-slate-700">Rp {{ number_format($item['neraca'], 2, ',', '.') }}</td>
                            <td class="py-2 px-3 num-cell text-right font-semibold {{ $item['isReconciled'] ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $item['formattedDiff'] }}
                            </td>
                            <td class="py-2 px-3 text-center">
                                @if ($item['isReconciled'])
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-100 text-emerald-800">&check; Klop</span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-rose-100 text-rose-800">&cross; Selisih</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-slate-50 p-4 border-t border-slate-200 text-right">
            <button type="button" onclick="closeReconciliationModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-xl transition">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let isFullscreen = false;
    const openSections = new Set();
    const openAccounts = new Set();

    // 1. Crosshair Highlight (Row + Column Header)
    function initCrosshairHighlight() {
        const tables = [document.getElementById('matrixReportTable'), document.getElementById('singleReportTable')].filter(Boolean);

        tables.forEach(table => {
            table.addEventListener('mouseover', function(e) {
                const cell = e.target.closest('td');
                if (!cell) return;
                const row = cell.closest('tr');
                if (row && !row.classList.contains('row-section') && !row.classList.contains('row-fixed') && !row.classList.contains('row-total') && !row.classList.contains('row-subtotal')) {
                    row.classList.add('crosshair-row');
                }
                const colIndex = cell.cellIndex;
                const thList = table.querySelectorAll('thead th');
                if (thList[colIndex] && colIndex >= 2) {
                    table.querySelectorAll('thead th.crosshair-col').forEach(th => th.classList.remove('crosshair-col'));
                    thList[colIndex].classList.add('crosshair-col');
                }
            });

            table.addEventListener('mouseout', function(e) {
                const cell = e.target.closest('td');
                if (!cell) return;
                const row = cell.closest('tr');
                if (row) row.classList.remove('crosshair-row');
                const colIndex = cell.cellIndex;
                const thList = table.querySelectorAll('thead th');
                if (thList[colIndex]) {
                    thList[colIndex].classList.remove('crosshair-col');
                }
            });

            table.addEventListener('mouseleave', function() {
                table.querySelectorAll('.crosshair-row').forEach(r => r.classList.remove('crosshair-row'));
                table.querySelectorAll('thead th.crosshair-col').forEach(th => th.classList.remove('crosshair-col'));
            });
        });
    }

    // 2. Synchronized Floating Horizontal Scrollbar
    function initFloatingScrollbar() {
        const tableContainer = document.getElementById('matrixTableContainer');
        const scrollbarWrap = document.getElementById('floatingScrollbarWrap');
        const scrollbarTrack = document.getElementById('floatingScrollbarTrack');
        const table = document.getElementById('matrixReportTable');

        if (!tableContainer || !scrollbarWrap || !scrollbarTrack || !table) return;

        function updateFloatingScrollbar() {
            scrollbarTrack.style.width = table.scrollWidth + 'px';

            if (isFullscreen) {
                scrollbarWrap.classList.remove('is-floating');
                scrollbarWrap.style.position = '';
                scrollbarWrap.style.left = '';
                scrollbarWrap.style.width = '';
                scrollbarWrap.style.display = 'block';
                return;
            }

            const rect = tableContainer.getBoundingClientRect();
            const winH = window.innerHeight;

            const isTableInView = rect.top < winH - 60 && rect.bottom > 100;
            const isBottomClipped = rect.bottom > winH;

            if (isTableInView && isBottomClipped) {
                scrollbarWrap.classList.add('is-floating');
                scrollbarWrap.style.position = 'fixed';
                scrollbarWrap.style.bottom = '0';
                scrollbarWrap.style.left = rect.left + 'px';
                scrollbarWrap.style.width = rect.width + 'px';
                scrollbarWrap.style.display = 'block';
            } else if (isTableInView && !isBottomClipped) {
                scrollbarWrap.classList.remove('is-floating');
                scrollbarWrap.style.position = '';
                scrollbarWrap.style.left = '';
                scrollbarWrap.style.width = '';
                scrollbarWrap.style.display = 'block';
            } else {
                scrollbarWrap.style.display = 'none';
            }
        }

        updateFloatingScrollbar();
        window.addEventListener('resize', updateFloatingScrollbar);
        window.addEventListener('scroll', updateFloatingScrollbar, { passive: true });

        let isSyncingTable = false;
        let isSyncingBar = false;

        tableContainer.addEventListener('scroll', function() {
            updateFloatingScrollbar();
            if (isSyncingTable) {
                isSyncingTable = false;
                return;
            }
            isSyncingBar = true;
            scrollbarWrap.scrollLeft = tableContainer.scrollLeft;
        });

        scrollbarWrap.addEventListener('scroll', function() {
            if (isSyncingBar) {
                isSyncingBar = false;
                return;
            }
            isSyncingTable = true;
            tableContainer.scrollLeft = scrollbarWrap.scrollLeft;
        });
    }

    // 3. Fullscreen Focus Mode
    function toggleFullscreen() {
        const container = document.getElementById('pnlReportContainer');
        const topBar = document.getElementById('fullscreenTopBar');
        const btn = document.getElementById('btnFullscreen');
        if (!container) return;

        isFullscreen = !isFullscreen;

        if (isFullscreen) {
            container.classList.add('pnl-fullscreen');
            if (topBar) topBar.classList.remove('hidden');
            if (btn) btn.classList.add('active');
        } else {
            container.classList.remove('pnl-fullscreen');
            if (topBar) topBar.classList.add('hidden');
            if (btn) btn.classList.remove('active');
        }

        setTimeout(() => {
            const table = document.getElementById('matrixReportTable');
            const scrollbarTrack = document.getElementById('floatingScrollbarTrack');
            const scrollbarWrap = document.getElementById('floatingScrollbarWrap');
            if (table && scrollbarTrack) scrollbarTrack.style.width = table.scrollWidth + 'px';
            if (scrollbarWrap) {
                if (isFullscreen) {
                    scrollbarWrap.classList.remove('is-floating');
                    scrollbarWrap.style.position = '';
                    scrollbarWrap.style.left = '';
                    scrollbarWrap.style.width = '';
                    scrollbarWrap.style.display = 'block';
                }
            }
        }, 100);
    }

    // 4. Branch Column Visibility
    function toggleColVisibilityMenu() {
        const menu = document.getElementById('colVisibilityMenu');
        if (menu) menu.classList.toggle('hidden');
    }

    function toggleDeptColumn(deptId, isVisible) {
        const elements = document.querySelectorAll('.col-dept-' + deptId);
        elements.forEach(el => {
            el.style.display = isVisible ? '' : 'none';
        });

        const totalChecked = document.querySelectorAll('.dept-col-toggle:checked').length;
        const badge = document.getElementById('colCountBadge');
        if (badge) badge.textContent = totalChecked;

        setTimeout(() => {
            const table = document.getElementById('matrixReportTable');
            const scrollbarTrack = document.getElementById('floatingScrollbarTrack');
            if (table && scrollbarTrack) scrollbarTrack.style.width = table.scrollWidth + 'px';
        }, 50);
    }

    function setAllDeptVisibility(selectAll) {
        const checkboxes = document.querySelectorAll('.dept-col-toggle');
        checkboxes.forEach(cb => {
            cb.checked = selectAll;
            toggleDeptColumn(cb.dataset.deptId, selectAll);
        });
    }

    // 5. Accordion Engine
    function toggleSection(secId) {
        const rows = document.querySelectorAll('.' + secId + '-rows');
        const chevron = document.getElementById('chevron-' + secId) || document.getElementById('chevron-single-' + secId);
        const isOpen = openSections.has(secId);

        if (isOpen) {
            rows.forEach(r => r.classList.add('hidden'));
            if (chevron) chevron.classList.remove('open');
            openSections.delete(secId);
        } else {
            rows.forEach(r => {
                // If it's a subaccount, only unhide if its parent account is open
                if (r.classList.contains('row-subaccount')) {
                    const parentAcc = r.dataset.parentAccount;
                    if (openAccounts.has(parentAcc)) {
                        r.classList.remove('hidden');
                    }
                } else {
                    r.classList.remove('hidden');
                }
            });
            if (chevron) chevron.classList.add('open');
            openSections.add(secId);
        }
    }

    function toggleAccount(accId) {
        const rows = document.querySelectorAll('.' + accId + '-rows');
        const chevron = document.getElementById('chevron-' + accId) || document.getElementById('chevron-single-' + accId);
        const isOpen = openAccounts.has(accId);

        if (isOpen) {
            rows.forEach(r => r.classList.add('hidden'));
            if (chevron) chevron.classList.remove('open');
            openAccounts.delete(accId);
        } else {
            rows.forEach(r => r.classList.remove('hidden'));
            if (chevron) chevron.classList.add('open');
            openAccounts.add(accId);
        }
    }

    function setLevel(lvl) {
        document.querySelectorAll('.ctrl-btn').forEach(b => {
            if (b.id && b.id.startsWith('btn-lvl-')) b.classList.remove('active');
        });
        if (document.getElementById('btn-lvl-' + lvl)) {
            document.getElementById('btn-lvl-' + lvl).classList.add('active');
        }

        // Pastikan baris fixed / subtotal tidak pernah tersembunyi
        document.querySelectorAll('.row-fixed').forEach(r => r.classList.remove('hidden'));

        if (lvl === 'all') {
            document.querySelectorAll('.collapsible-row').forEach(r => r.classList.remove('hidden'));
            document.querySelectorAll('.toggle-chevron').forEach(c => c.classList.add('open'));
            openSections.clear();
            document.querySelectorAll('[data-section]').forEach(el => openSections.add(el.dataset.section));
            openAccounts.clear();
            document.querySelectorAll('[data-account]').forEach(el => openAccounts.add(el.dataset.account));
        } else if (lvl === 2) {
            // Show Headers & Accounts, Hide Subaccounts
            document.querySelectorAll('.collapsible-row').forEach(r => r.classList.remove('hidden'));
            document.querySelectorAll('.row-subaccount').forEach(r => r.classList.add('hidden'));
            openSections.clear();
            document.querySelectorAll('[data-section]').forEach(el => {
                openSections.add(el.dataset.section);
                const ch = document.getElementById('chevron-' + el.dataset.section) || document.getElementById('chevron-single-' + el.dataset.section);
                if (ch) ch.classList.add('open');
            });
            openAccounts.clear();
            document.querySelectorAll('[data-account]').forEach(el => {
                const accId = el.dataset.account;
                const rows = document.querySelectorAll('.' + accId + '-rows');
                rows.forEach(r => r.classList.add('hidden'));
                const ch = document.getElementById('chevron-' + accId) || document.getElementById('chevron-single-' + accId);
                if (ch) ch.classList.remove('open');
            });
        } else if (lvl === 1) {
            // Show only Section Headers, hide Accounts and Subaccounts
            document.querySelectorAll('.row-account, .row-subaccount').forEach(r => r.classList.add('hidden'));
            openSections.clear();
            document.querySelectorAll('[data-section]').forEach(el => {
                openSections.add(el.dataset.section);
                const ch = document.getElementById('chevron-' + el.dataset.section) || document.getElementById('chevron-single-' + el.dataset.section);
                if (ch) ch.classList.add('open');
            });
            openAccounts.clear();
            document.querySelectorAll('[data-account]').forEach(el => {
                const ch = document.getElementById('chevron-' + el.dataset.account) || document.getElementById('chevron-single-' + el.dataset.account);
                if (ch) ch.classList.remove('open');
            });
        } else if (lvl === 0) {
            // Collapse all under Section Headers
            document.querySelectorAll('.collapsible-row').forEach(r => r.classList.add('hidden'));
            document.querySelectorAll('.toggle-chevron').forEach(c => c.classList.remove('open'));
            openSections.clear();
            openAccounts.clear();
        }
    }

    // 6. Quick Search with Auto-Expand
    function filterTableRows(query) {
        const q = query.trim().toLowerCase();
        const rows = document.querySelectorAll('#matrixReportTable tbody tr, #singleReportTable tbody tr');

        if (!q) {
            setLevel('all');
            return;
        }

        // Hide all collapsible rows initially; never hide row-section, row-subtotal, row-total, or row-fixed
        rows.forEach(row => {
            if (row.classList.contains('row-section') || row.classList.contains('row-total') || row.classList.contains('row-subtotal') || row.classList.contains('row-fixed')) {
                row.classList.remove('hidden');
                return;
            }
            row.classList.add('hidden');
        });

        // Search and auto-expand matching rows & their parents
        rows.forEach(row => {
            const label = row.querySelector('.account-name-label') || row.querySelector('td:first-child');
            if (label && label.textContent.toLowerCase().includes(q)) {
                row.classList.remove('hidden');

                // If account row matches, also unhide its child subaccounts
                if (row.hasAttribute('data-account')) {
                    const accId = row.dataset.account;
                    document.querySelectorAll('.' + accId + '-rows').forEach(subRow => subRow.classList.remove('hidden'));
                    const ch = document.getElementById('chevron-' + accId) || document.getElementById('chevron-single-' + accId);
                    if (ch) ch.classList.add('open');
                    openAccounts.add(accId);
                }

                // Auto-expand parents: find all class names on row
                row.classList.forEach(cls => {
                    if (cls.endsWith('-rows')) {
                        const parentId = cls.replace('-rows', '');
                        // If parent is account
                        const parentAcc = document.querySelector('[data-account="' + parentId + '"]');
                        if (parentAcc) {
                            parentAcc.classList.remove('hidden');
                            const ch = document.getElementById('chevron-' + parentId) || document.getElementById('chevron-single-' + parentId);
                            if (ch) ch.classList.add('open');
                            openAccounts.add(parentId);
                        }
                        // If parent is section
                        const parentSec = document.querySelector('[data-section="' + parentId + '"]');
                        if (parentSec) {
                            parentSec.classList.remove('hidden');
                            const ch = document.getElementById('chevron-' + parentId) || document.getElementById('chevron-single-' + parentId);
                            if (ch) ch.classList.add('open');
                            openSections.add(parentId);
                        }
                    }
                });
            }
        });
    }

    // 7. Reconciliation Modal
    function openReconciliationModal() {
        const modal = document.getElementById('reconciliationModal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeReconciliationModal() {
        const modal = document.getElementById('reconciliationModal');
        if (modal) modal.classList.add('hidden');
    }

    // Close Dropdown on Click Outside
    document.addEventListener('click', function(e) {
        const wrap = document.getElementById('colVisibilityDropdownWrap');
        const menu = document.getElementById('colVisibilityMenu');
        if (wrap && menu && !wrap.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });

    // Keyboard Shortcuts (Esc)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (isFullscreen) {
                toggleFullscreen();
            }
            const modal = document.getElementById('reconciliationModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeReconciliationModal();
            }
        }
    });

    // Initialize on DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
        initCrosshairHighlight();
        initFloatingScrollbar();

        // Populate initial open sets (Level All)
        document.querySelectorAll('[data-section]').forEach(el => openSections.add(el.dataset.section));
        document.querySelectorAll('[data-account]').forEach(el => openAccounts.add(el.dataset.account));
    });
</script>
@endpush
@endsection

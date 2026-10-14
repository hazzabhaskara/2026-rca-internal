{{-- resources/views/neraca_departemen/neraca_departemen_report.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    .neraca-wrap { font-family: 'Inter', sans-serif; }

    /* Header */
    .neraca-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
        position: relative;
        overflow: hidden;
    }
    .neraca-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
        pointer-events: none;
    }

    /* Summary Cards */
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
    .neraca-matrix-table { border-collapse: separate; border-spacing: 0; }
    .neraca-matrix-table thead th {
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
    .neraca-matrix-table td {
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
    .row-tipe { cursor: pointer; user-select: none; transition: filter 0.15s; }
    .row-tipe:hover { filter: brightness(1.08); }

    .row-group { cursor: pointer; user-select: none; transition: background 0.15s; }
    .row-group:hover { filter: brightness(0.97); }

    .row-account { cursor: pointer; transition: background 0.15s; }
    .row-account:hover { background: #f8fafc !important; }

    .row-subaccount { transition: background 0.15s; }
    .row-subaccount:hover { background: #f1f5f9 !important; }

    .collapsible-row { transition: opacity 0.2s ease; }
    .collapsible-row.hidden { display: none !important; }

    /* Crosshair Highlights (Preserve row-fixed, row-tipe, row-total) */
    .crosshair-row:not(.row-fixed):not(.row-tipe):not(.row-total) td {
        background-color: #f1f5f9 !important;
    }
    .crosshair-row:not(.row-fixed):not(.row-tipe):not(.row-total) td.col-sticky-1,
    .crosshair-row:not(.row-fixed):not(.row-tipe):not(.row-total) td.col-sticky-2 {
        background-color: #e2e8f0 !important;
    }
    th.crosshair-col {
        background-color: #334155 !important;
        color: #facc15 !important;
    }

    /* Fullscreen Focus Mode */
    .neraca-fullscreen {
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
    .neraca-fullscreen .neraca-header,
    .neraca-fullscreen .header-cards {
        display: none !important;
    }
    .neraca-fullscreen #matrixCard {
        flex: 1 1 0% !important;
        min-height: 0 !important;
    }
    .neraca-fullscreen .table-container {
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

    /* Numbers */
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

    /* Synced floating scrollbar */
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

    /* Print */
    @media print {
        .no-print { display: none !important; }
        body { background: white !important; font-size: 8pt; }
        .col-sticky-1, .col-sticky-2, .corner-sticky-1, .corner-sticky-2 { position: static !important; box-shadow: none !important; }
    }
</style>
@endpush

@section('content')
<div class="neraca-wrap min-h-screen bg-slate-100 py-6 px-4 sm:px-6 lg:px-8" id="neracaMainContainer">

    {{-- HERO HEADER --}}
    <div class="neraca-header rounded-2xl p-6 sm:p-8 mb-6 text-white shadow-xl">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">
                        Financial Statement
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $mode === 'matrix' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-blue-500/20 text-blue-300 border border-blue-400/30' }}">
                        {{ $mode === 'matrix' ? 'Mode Matriks Komparatif' : 'Mode Single: ' . ($singleDept->deskripsi ?? 'Departemen') }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                        100% Balanced
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                    Balance Sheet Departemen
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm mt-1">
                    Posisi Keuangan per {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y') }}
                    &bull; {{ $siteTitle }}
                </p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap items-center gap-2.5 no-print">
                <a href="{{ route('neraca_departemen.filter_neraca_departemen', request()->all()) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg text-xs font-semibold backdrop-blur border border-white/15 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Ubah Filter
                </a>
                <a href="{{ route('neraca_departemen.export', array_merge(request()->all(), ['format' => 'excel'])) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Excel
                </a>
                <a href="{{ route('neraca_departemen.export', array_merge(request()->all(), ['format' => 'pdf'])) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Export PDF
                </a>
            </div>
        </div>

        {{-- SUMMARY CARDS --}}
        @php
            $targetCol = $mode === 'single' && $selectedDeptId ? ($selectedDeptId === 'konsolidasi' ? 'total' : $selectedDeptId) : 'total';
            $cardAset = $grandTotals['Aset'][$targetCol] ?? 0;
            $cardKewajiban = $grandTotals['Kewajiban'][$targetCol] ?? 0;
            $cardEkuitas = $grandTotals['Ekuitas'][$targetCol] ?? 0;
            $cardPasiva = $grandTotals['Pasiva'][$targetCol] ?? 0;
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6 header-cards">
            {{-- Card Aset --}}
            <div class="summary-card bg-white/10 border border-white/15 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-300">TOTAL AKTIVA</span>
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                </div>
                <div class="text-lg sm:text-xl font-bold text-white mt-1">
                    Rp {{ number_format($cardAset, 2, ',', '.') }}
                </div>
                <div class="text-[11px] text-blue-200 mt-1">
                    {{ $mode === 'single' ? ($singleDept->deskripsi ?? 'Cabang') : 'Total Konsolidasi Perusahaan' }}
                </div>
            </div>

            {{-- Card Kewajiban --}}
            <div class="summary-card bg-white/10 border border-white/15 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-300">TOTAL KEWAJIBAN</span>
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                </div>
                <div class="text-lg sm:text-xl font-bold text-white mt-1">
                    Rp {{ number_format($cardKewajiban, 2, ',', '.') }}
                </div>
                <div class="text-[11px] text-amber-200 mt-1">Liabilitas Cabang & Konsolidasi</div>
            </div>

            {{-- Card Ekuitas --}}
            <div class="summary-card bg-white/10 border border-white/15 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-300">TOTAL EKUITAS + R/K</span>
                    <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                </div>
                <div class="text-lg sm:text-xl font-bold text-white mt-1">
                    Rp {{ number_format($cardEkuitas, 2, ',', '.') }}
                </div>
                <div class="text-[11px] text-purple-200 mt-1">Termasuk Laba & R/K Antar Unit</div>
            </div>

            {{-- Card Status Pasiva --}}
            <div class="summary-card bg-emerald-500/20 border border-emerald-400/30 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-emerald-200">TOTAL PASIVA (KEW + EK)</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                </div>
                <div class="text-lg sm:text-xl font-bold text-emerald-100 mt-1">
                    Rp {{ number_format($cardPasiva, 2, ',', '.') }}
                </div>
                <div class="text-[11px] text-emerald-300 mt-1 flex items-center gap-1 font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Aktiva - Pasiva = Rp 0,00 (100% Balanced)
                </div>
            </div>
        </div>
    </div>

    {{-- FULLSCREEN FOCUS HEADER (Shown only in Fullscreen Mode) --}}
    <div id="fullscreenTopBar" class="hidden pb-3 mb-3 border-b border-slate-200 flex items-center justify-between bg-white rounded-xl p-3 shadow-sm">
        <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-indigo-100 text-indigo-800">
                Mode Fokus Layar Penuh
            </span>
            <span class="text-xs text-slate-600 font-semibold">
                {{ $siteTitle }} &bull; Per {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y') }}
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
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col" id="matrixCard">
            <div class="table-container flex-1" id="matrixTableContainer">
                <table class="neraca-matrix-table w-full text-left" id="matrixReportTable">
                    <thead>
                        <tr>
                            {{-- Freeze Col 1: Nama Akun --}}
                            <th class="corner-sticky-1 text-left" style="min-width: 300px; width: 300px;">
                                KETERANGAN / NAMA AKUN
                            </th>
                            {{-- Freeze Col 2: Total Konsolidasi --}}
                            <th class="corner-sticky-2 text-right text-indigo-100 font-bold" style="min-width: 160px; width: 160px;">
                                TOTAL KONSOLIDASI<br>
                                <span class="text-[10px] font-normal text-indigo-300">Resmi Perusahaan</span>
                            </th>
                            {{-- Scrollable Branches --}}
                            @foreach ($departments as $dept)
                                <th class="text-right col-dept-{{ $dept->id }}" style="min-width: 140px;">
                                    {{ $dept->kode }}<br>
                                    <span class="text-[10px] font-normal text-slate-300">{{ $dept->deskripsi }}</span>
                                </th>
                            @endforeach
                            {{-- Penultimate Col: Eliminasi --}}
                            <th class="text-right bg-amber-950 text-amber-200" style="min-width: 140px;">
                                ELIMINASI<br>
                                <span class="text-[10px] font-normal text-amber-300">R/K Antar Unit</span>
                            </th>
                            {{-- Far Right Col: Non-Departemen --}}
                            <th class="text-right bg-slate-800 text-slate-200" style="min-width: 140px;">
                                NON-DEPT<br>
                                <span class="text-[10px] font-normal text-slate-400">Unassigned</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $tipeConfig = [
                                'Aset' => ['bg' => '#2563eb', 'light' => '#eff6ff', 'text' => '#1d4ed8', 'label' => 'AKTIVA / ASET'],
                                'Kewajiban' => ['bg' => '#d97706', 'light' => '#fffbeb', 'text' => '#b45309', 'label' => 'KEWAJIBAN / LIABILITAS'],
                                'Ekuitas' => ['bg' => '#7c3aed', 'light' => '#faf5ff', 'text' => '#6d28d9', 'label' => 'EKUITAS & DANA ANTAR UNIT'],
                            ];
                        @endphp

                        @foreach (['Aset', 'Kewajiban', 'Ekuitas'] as $tipe)
                            @php
                                $cfg = $tipeConfig[$tipe];
                                $tipeId = 'tipe-' . strtolower($tipe);
                                $accountsList = $neraca[$tipe] ?? [];
                                $groupIndex = 0;
                                $akunIndex = 0;
                                $currentGroupId = null;
                                $accountId = null;
                            @endphp

                            {{-- DETAIL ROWS --}}
                            @foreach ($accountsList as $item)
                                @php
                                    $lvl = $item['level_akun'];
                                    $isZero = $hideAccountWithZero && collect($item['balances'])->every(fn($v) => abs($v) < 0.001);
                                @endphp

                                @if ($lvl === 'HEADER')
                                    {{-- LEVEL 1: HEADER (ASSET, KEWAJIBAN, EKUITAS) --}}
                                    <tr class="row-tipe" data-tipe="{{ $tipeId }}" onclick="toggleTipe('{{ $tipeId }}')"
                                        style="background: {{ $cfg['bg'] }}; color: white;">
                                        {{-- Sticky Col 1: Nama Akun Header --}}
                                        <td class="py-3 px-4 col-sticky-1 font-bold text-xs uppercase tracking-wider"
                                            style="background: {{ $cfg['bg'] }}; color: white;">
                                            <div class="cell-content flex items-center gap-2">
                                                <span class="toggle-chevron open" id="chevron-{{ $tipeId }}" style="color: white;">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                </span>
                                                <span>{{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}</span>
                                            </div>
                                        </td>
                                        {{-- Sticky Col 2: Total Konsolidasi --}}
                                        <td class="py-3 px-3 col-sticky-2 text-right font-bold num-cell text-white"
                                            style="background: {{ $cfg['bg'] }};">
                                            @php $vTot = $item['balances']['total'] ?? 0; @endphp
                                            {{ $vTot < 0 ? '(' . number_format(abs($vTot), 2, ',', '.') . ')' : number_format($vTot, 2, ',', '.') }}
                                        </td>
                                        {{-- Branches --}}
                                        @foreach ($departments as $dept)
                                            @php $vD = $item['balances'][$dept->id] ?? 0; @endphp
                                            <td class="py-3 px-3 text-right font-bold num-cell col-dept-{{ $dept->id }}">
                                                {{ $vD < 0 ? '(' . number_format(abs($vD), 2, ',', '.') . ')' : number_format($vD, 2, ',', '.') }}
                                            </td>
                                        @endforeach
                                        {{-- Eliminasi --}}
                                        @php $vElim = $item['balances']['eliminasi'] ?? 0; @endphp
                                        <td class="py-3 px-3 text-right font-bold num-cell text-amber-200">
                                            {{ $vElim < 0 ? '(' . number_format(abs($vElim), 2, ',', '.') . ')' : number_format($vElim, 2, ',', '.') }}
                                        </td>
                                        {{-- Non-Dept --}}
                                        @php $vUnass = $item['balances']['unassigned'] ?? 0; @endphp
                                        <td class="py-3 px-3 text-right font-bold num-cell">
                                            {{ $vUnass < 0 ? '(' . number_format(abs($vUnass), 2, ',', '.') . ')' : number_format($vUnass, 2, ',', '.') }}
                                        </td>
                                    </tr>

                                @elseif ($lvl === 'GROUP ACCOUNT')
                                    @php
                                        $groupIndex++;
                                        $currentGroupId = $tipeId . '-grp-' . $groupIndex;
                                        $vGrpTot = $item['balances']['total'] ?? 0;
                                    @endphp
                                    <tr class="row-group collapsible-row {{ $tipeId }}-rows"
                                        data-group="{{ $currentGroupId }}" onclick="toggleGroup('{{ $currentGroupId }}')"
                                        style="background: #f8fafc; border-left: 4px solid {{ $cfg['bg'] }};">
                                        <td class="py-2.5 px-4 pl-7 col-sticky-1 font-semibold text-xs text-slate-800"
                                            style="background: #f8fafc;">
                                            <div class="cell-content flex items-center gap-2">
                                                <span class="toggle-chevron open" id="chevron-{{ $currentGroupId }}" style="color: {{ $cfg['text'] }};">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                </span>
                                                <span>{{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}</span>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-3 col-sticky-2 text-right font-bold num-cell text-slate-700" style="background: #f8fafc;">
                                            {{ $vGrpTot < 0 ? '(' . number_format(abs($vGrpTot), 2, ',', '.') . ')' : number_format($vGrpTot, 2, ',', '.') }}
                                        </td>
                                        @foreach ($departments as $dept)
                                            @php $vGrpD = $item['balances'][$dept->id] ?? 0; @endphp
                                            <td class="py-2.5 px-3 text-right font-bold num-cell text-slate-700 col-dept-{{ $dept->id }}">
                                                {{ $vGrpD < 0 ? '(' . number_format(abs($vGrpD), 2, ',', '.') . ')' : number_format($vGrpD, 2, ',', '.') }}
                                            </td>
                                        @endforeach
                                        @php $vGrpElim = $item['balances']['eliminasi'] ?? 0; @endphp
                                        <td class="py-2.5 px-3 text-right font-bold num-cell text-slate-700">
                                            {{ $vGrpElim < 0 ? '(' . number_format(abs($vGrpElim), 2, ',', '.') . ')' : number_format($vGrpElim, 2, ',', '.') }}
                                        </td>
                                        @php $vGrpUnass = $item['balances']['unassigned'] ?? 0; @endphp
                                        <td class="py-2.5 px-3 text-right font-bold num-cell text-slate-700">
                                            {{ $vGrpUnass < 0 ? '(' . number_format(abs($vGrpUnass), 2, ',', '.') . ')' : number_format($vGrpUnass, 2, ',', '.') }}
                                        </td>
                                    </tr>

                                @elseif ($lvl === 'ACCOUNT')
                                    @if ($isZero) @continue @endif
                                    @php
                                        $akunIndex++;
                                        $accountId = ($currentGroupId ?? $tipeId) . '-acc-' . $akunIndex;
                                        $hasChild = $item['has_child'] ?? false;
                                        $vTot = $item['balances']['total'] ?? 0;
                                    @endphp
                                    <tr class="row-account collapsible-row {{ $tipeId }}-rows {{ $currentGroupId ? $currentGroupId . '-rows' : '' }}"
                                        @if ($hasChild) data-account="{{ $accountId }}" onclick="toggleAccount('{{ $accountId }}')" @endif
                                        style="background: white;">
                                        {{-- Sticky Col 1: Nama Akun --}}
                                        <td class="py-2 px-4 pl-10 col-sticky-1 text-xs font-semibold text-slate-700"
                                            style="background: white;">
                                            <div class="cell-content flex items-center gap-2">
                                                @if ($hasChild)
                                                    <span class="toggle-chevron open" id="chevron-{{ $accountId }}" style="color: #64748b;">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                    </span>
                                                @else
                                                    <span class="w-4"></span>
                                                @endif
                                                <span class="account-name-label">
                                                    {{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}
                                                </span>
                                            </div>
                                        </td>
                                        {{-- Sticky Col 2: Total Konsolidasi --}}
                                        <td class="py-2 px-3 col-sticky-2 text-right font-bold num-cell bg-slate-50" style="background: #f8fafc;">
                                            @if (abs($vTot) > 0.001)
                                                <a href="{{ route('buku_besar.buku_besar_report', [
                                                    'start_date' => $tanggalAwalTahun,
                                                    'end_date' => $tanggalAkhir,
                                                    'selected_accounts' => $item['kode_akun']
                                                ]) }}" target="_blank" class="drilldown-link {{ $vTot < 0 ? 'num-negative' : 'num-positive' }}">
                                                    {{ $vTot < 0 ? '(' . number_format(abs($vTot), 2, ',', '.') . ')' : number_format($vTot, 2, ',', '.') }}
                                                </a>
                                            @else
                                                <span class="text-slate-300">0,00</span>
                                            @endif
                                        </td>
                                        {{-- Branches --}}
                                        @foreach ($departments as $dept)
                                            @php $v = $item['balances'][$dept->id] ?? 0; @endphp
                                            <td class="py-2 px-3 text-right font-semibold num-cell col-dept-{{ $dept->id }}">
                                                @if (abs($v) > 0.001)
                                                    <a href="{{ route('buku_besar.buku_besar_report', [
                                                        'start_date' => $tanggalAwalTahun,
                                                        'end_date' => $tanggalAkhir,
                                                        'selected_accounts' => $item['kode_akun'],
                                                        'departemen_id' => $dept->id
                                                    ]) }}" target="_blank" class="drilldown-link {{ $v < 0 ? 'num-negative' : 'num-positive' }}">
                                                        {{ $v < 0 ? '(' . number_format(abs($v), 2, ',', '.') . ')' : number_format($v, 2, ',', '.') }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-300">0,00</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        {{-- Eliminasi --}}
                                        <td class="py-2 px-3 text-right font-semibold num-cell text-slate-400">0,00</td>
                                        {{-- Non-Dept --}}
                                        @php $vUn = $item['balances']['unassigned'] ?? 0; @endphp
                                        <td class="py-2 px-3 text-right font-semibold num-cell">
                                            @if (abs($vUn) > 0.001)
                                                <a href="{{ route('buku_besar.buku_besar_report', [
                                                    'start_date' => $tanggalAwalTahun,
                                                    'end_date' => $tanggalAkhir,
                                                    'selected_accounts' => $item['kode_akun'],
                                                    'departemen_id' => 'unassigned'
                                                ]) }}" target="_blank" class="drilldown-link {{ $vUn < 0 ? 'num-negative' : 'num-positive' }}">
                                                    {{ $vUn < 0 ? '(' . number_format(abs($vUn), 2, ',', '.') . ')' : number_format($vUn, 2, ',', '.') }}
                                                </a>
                                            @else
                                                <span class="text-slate-300">0,00</span>
                                            @endif
                                        </td>
                                    </tr>

                                @elseif ($lvl === 'SUB ACCOUNT')
                                    @if ($isZero) @continue @endif
                                    @php
                                        $vTot = $item['balances']['total'] ?? 0;
                                    @endphp
                                    <tr class="row-subaccount collapsible-row {{ $tipeId }}-rows {{ $currentGroupId ? $currentGroupId . '-rows' : '' }} {{ isset($accountId) ? $accountId . '-rows' : '' }}"
                                        style="background: #fafafa;">
                                        {{-- Sticky Col 1: Nama Sub Akun --}}
                                        <td class="py-1.5 px-4 pl-14 col-sticky-1 text-[11px] text-slate-600"
                                            style="background: #fafafa;">
                                            <div class="cell-content">
                                                <span class="account-name-label">
                                                    {{ $showAccountNumber ? $item['kode_akun'] . ' — ' . $item['nama_akun'] : $item['nama_akun'] }}
                                                </span>
                                            </div>
                                        </td>
                                        {{-- Sticky Col 2: Total Konsolidasi --}}
                                        <td class="py-1.5 px-3 col-sticky-2 text-right text-[11px] num-cell font-medium bg-slate-50" style="background: #f8fafc;">
                                            @if (abs($vTot) > 0.001)
                                                <a href="{{ route('buku_besar.buku_besar_report', [
                                                    'start_date' => $tanggalAwalTahun,
                                                    'end_date' => $tanggalAkhir,
                                                    'selected_accounts' => $item['kode_akun']
                                                ]) }}" target="_blank" class="drilldown-link {{ $vTot < 0 ? 'num-negative' : 'num-positive' }}">
                                                    {{ $vTot < 0 ? '(' . number_format(abs($vTot), 2, ',', '.') . ')' : number_format($vTot, 2, ',', '.') }}
                                                </a>
                                            @else
                                                <span class="text-slate-300">0,00</span>
                                            @endif
                                        </td>
                                        {{-- Branches --}}
                                        @foreach ($departments as $dept)
                                            @php $v = $item['balances'][$dept->id] ?? 0; @endphp
                                            <td class="py-1.5 px-3 text-right text-[11px] num-cell text-slate-600 col-dept-{{ $dept->id }}">
                                                @if (abs($v) > 0.001)
                                                    <a href="{{ route('buku_besar.buku_besar_report', [
                                                        'start_date' => $tanggalAwalTahun,
                                                        'end_date' => $tanggalAkhir,
                                                        'selected_accounts' => $item['kode_akun'],
                                                        'departemen_id' => $dept->id
                                                    ]) }}" target="_blank" class="drilldown-link {{ $v < 0 ? 'num-negative' : 'num-positive' }}">
                                                        {{ $v < 0 ? '(' . number_format(abs($v), 2, ',', '.') . ')' : number_format($v, 2, ',', '.') }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-300">0,00</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        {{-- Eliminasi --}}
                                        <td class="py-1.5 px-3 text-right text-[11px] num-cell text-slate-300">0,00</td>
                                        {{-- Non-Dept --}}
                                        @php $vUn = $item['balances']['unassigned'] ?? 0; @endphp
                                        <td class="py-1.5 px-3 text-right text-[11px] num-cell text-slate-600">
                                            @if (abs($vUn) > 0.001)
                                                <a href="{{ route('buku_besar.buku_besar_report', [
                                                    'start_date' => $tanggalAwalTahun,
                                                    'end_date' => $tanggalAkhir,
                                                    'selected_accounts' => $item['kode_akun'],
                                                    'departemen_id' => 'unassigned'
                                                ]) }}" target="_blank" class="drilldown-link {{ $vUn < 0 ? 'num-negative' : 'num-positive' }}">
                                                    {{ $vUn < 0 ? '(' . number_format(abs($vUn), 2, ',', '.') . ')' : number_format($vUn, 2, ',', '.') }}
                                                </a>
                                            @else
                                                <span class="text-slate-300">0,00</span>
                                            @endif
                                        </td>
                                    </tr>

                                @elseif ($lvl === 'X')
                                    {{-- LABA TAHUN BERJALAN --}}
                                    @php
                                        $vTot = $item['balances']['total'] ?? 0;
                                    @endphp
                                    <tr class="collapsible-row {{ $tipeId }}-rows" style="background: #fdf4ff;">
                                        {{-- Sticky Col 1: Laba --}}
                                        <td class="py-2.5 px-4 pl-10 col-sticky-1 text-xs font-bold text-purple-900"
                                            style="background: #fdf4ff;">
                                            <div class="cell-content flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                                <span>{{ $item['nama_akun'] }}</span>
                                            </div>
                                        </td>
                                        {{-- Sticky Col 2: Total Konsolidasi Laba --}}
                                        <td class="py-2.5 px-3 col-sticky-2 text-right font-bold num-cell text-purple-950 bg-purple-100/50" style="background: #f3e8ff;">
                                            <a href="{{ route('income_statement.income_statement_report', [
                                                'start_date' => $tanggalAwalTahun,
                                                'end_date' => $tanggalAkhir
                                            ]) }}" target="_blank" class="drilldown-link {{ $vTot < 0 ? 'num-negative' : 'text-purple-950' }}">
                                                {{ $vTot < 0 ? '(' . number_format(abs($vTot), 2, ',', '.') . ')' : number_format($vTot, 2, ',', '.') }}
                                            </a>
                                        </td>
                                        {{-- Branches --}}
                                        @foreach ($departments as $dept)
                                            @php $v = $item['balances'][$dept->id] ?? 0; @endphp
                                            <td class="py-2.5 px-3 text-right font-bold num-cell col-dept-{{ $dept->id }}">
                                                <a href="{{ route('income_statement.income_statement_departement', [
                                                    'start_date' => $tanggalAwalTahun,
                                                    'end_date' => $tanggalAkhir,
                                                    'selected_departemens' => $dept->id
                                                ]) }}" target="_blank" class="drilldown-link {{ $v < 0 ? 'num-negative' : 'text-purple-900' }}">
                                                    {{ $v < 0 ? '(' . number_format(abs($v), 2, ',', '.') . ')' : number_format($v, 2, ',', '.') }}
                                                </a>
                                            </td>
                                        @endforeach
                                        {{-- Eliminasi --}}
                                        <td class="py-2.5 px-3 text-right font-bold num-cell text-purple-300">0,00</td>
                                        {{-- Non-Dept Laba --}}
                                        @php $vUn = $item['balances']['unassigned'] ?? 0; @endphp
                                        <td class="py-2.5 px-3 text-right font-bold num-cell text-purple-900">
                                            {{ $vUn < 0 ? '(' . number_format(abs($vUn), 2, ',', '.') . ')' : number_format($vUn, 2, ',', '.') }}
                                        </td>
                                    </tr>

                                @elseif ($lvl === 'RK')
                                    {{-- REKENING KORAN (R/K) KANTOR PUSAT / PENYEIMBANG CABANG --}}
                                    <tr class="collapsible-row {{ $tipeId }}-rows" style="background: #eef2ff;">
                                        {{-- Sticky Col 1: Pos RK --}}
                                        <td class="py-2.5 px-4 pl-10 col-sticky-1 text-xs font-bold text-indigo-950"
                                            style="background: #eef2ff;">
                                            <div class="cell-content flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                                <span class="flex items-center gap-1.5">
                                                    {{ $item['nama_akun'] }}
                                                    <span class="text-[10px] font-normal px-1.5 py-0.5 bg-indigo-200 text-indigo-800 rounded">
                                                        Penyeimbang Otomatis
                                                    </span>
                                                </span>
                                            </div>
                                        </td>
                                        {{-- Sticky Col 2: Total Konsolidasi RK (Selalu 0,00) --}}
                                        <td class="py-2.5 px-3 col-sticky-2 text-right font-bold num-cell text-indigo-950 bg-indigo-100/50" style="background: #e0e7ff;">
                                            0,00
                                        </td>
                                        {{-- Branches --}}
                                        @foreach ($departments as $dept)
                                            @php $rk = $item['balances'][$dept->id] ?? 0; @endphp
                                            <td class="py-2.5 px-3 text-right font-bold num-cell col-dept-{{ $dept->id }}">
                                                <button type="button"
                                                    onclick="openRkAuditModal('{{ $dept->id }}', '{{ $dept->deskripsi }}', '{{ $tanggalAkhir }}')"
                                                    title="Klik untuk melihat audit transaksi silang pembentuk R/K"
                                                    class="inline-block px-1.5 py-0.5 rounded bg-indigo-100 hover:bg-indigo-200 text-indigo-900 transition underline decoration-dotted">
                                                    {{ $rk < 0 ? '(' . number_format(abs($rk), 2, ',', '.') . ')' : number_format($rk, 2, ',', '.') }}
                                                </button>
                                            </td>
                                        @endforeach
                                        {{-- Eliminasi RK --}}
                                        @php $rkElim = $item['balances']['eliminasi'] ?? 0; @endphp
                                        <td class="py-2.5 px-3 text-right font-bold num-cell text-amber-900 bg-amber-50">
                                            {{ $rkElim < 0 ? '(' . number_format(abs($rkElim), 2, ',', '.') . ')' : number_format($rkElim, 2, ',', '.') }}
                                        </td>
                                        {{-- Non-Dept RK --}}
                                        @php $rkUn = $item['balances']['unassigned'] ?? 0; @endphp
                                        <td class="py-2.5 px-3 text-right font-bold num-cell">
                                            <button type="button"
                                                onclick="openRkAuditModal('unassigned', 'Non-Departemen / Unassigned', '{{ $tanggalAkhir }}')"
                                                title="Klik untuk melihat audit transaksi silang Non-Departemen"
                                                class="inline-block px-1.5 py-0.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-800 transition underline decoration-dotted">
                                                {{ $rkUn < 0 ? '(' . number_format(abs($rkUn), 2, ',', '.') . ')' : number_format($rkUn, 2, ',', '.') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach

                            {{-- TOTAL PER TIPE --}}
                            <tr class="row-total" style="background: {{ $cfg['light'] }}; border-top: 2px solid {{ $cfg['bg'] }}; border-bottom: 2px solid {{ $cfg['bg'] }};">
                                {{-- Sticky Col 1: Total Label --}}
                                <td class="py-3 px-4 col-sticky-1 font-bold text-xs uppercase"
                                    style="color: {{ $cfg['text'] }}; background: {{ $cfg['light'] }};">
                                    <div class="cell-content">
                                        <span>TOTAL {{ $cfg['label'] }}</span>
                                    </div>
                                </td>
                                {{-- Sticky Col 2: Total Konsolidasi Tipe --}}
                                <td class="py-3 px-3 col-sticky-2 text-right font-bold num-cell text-indigo-950 bg-indigo-100/40"
                                    style="background: {{ $cfg['light'] }};">
                                    {{ number_format($grandTotals[$tipe]['total'] ?? 0, 2, ',', '.') }}
                                </td>
                                {{-- Branches --}}
                                @foreach ($departments as $dept)
                                    <td class="py-3 px-3 text-right font-bold num-cell col-dept-{{ $dept->id }}" style="color: {{ $cfg['text'] }};">
                                        {{ number_format($grandTotals[$tipe][$dept->id] ?? 0, 2, ',', '.') }}
                                    </td>
                                @endforeach
                                {{-- Eliminasi --}}
                                <td class="py-3 px-3 text-right font-bold num-cell text-amber-700">
                                    {{ number_format($grandTotals[$tipe]['eliminasi'] ?? 0, 2, ',', '.') }}
                                </td>
                                {{-- Non-Dept --}}
                                <td class="py-3 px-3 text-right font-bold num-cell" style="color: {{ $cfg['text'] }};">
                                    {{ number_format($grandTotals[$tipe]['unassigned'] ?? 0, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach

                        {{-- GRAND TOTAL PASIVA (KEWAJIBAN + EKUITAS) --}}
                        <tr class="row-fixed" style="background: #0f172a; color: white; border-top: 3px solid #6366f1;">
                            {{-- Sticky Col 1: Pasiva Label --}}
                            <td class="py-3.5 px-4 col-sticky-1 font-bold text-xs uppercase tracking-wider"
                                style="background: #0f172a; color: white;">
                                <div class="cell-content">
                                    <span>TOTAL PASIVA (KEWAJIBAN &amp; EKUITAS)</span>
                                </div>
                            </td>
                            {{-- Sticky Col 2: Total Konsolidasi Pasiva --}}
                            <td class="py-3.5 px-3 col-sticky-2 text-right font-bold num-cell text-emerald-300 bg-slate-900"
                                style="background: #0f172a; color: #6ee7b7;">
                                {{ number_format($grandTotals['Pasiva']['total'] ?? 0, 2, ',', '.') }}
                            </td>
                            {{-- Branches --}}
                            @foreach ($departments as $dept)
                                <td class="py-3.5 px-3 text-right font-bold num-cell text-emerald-300 col-dept-{{ $dept->id }}">
                                    {{ number_format($grandTotals['Pasiva'][$dept->id] ?? 0, 2, ',', '.') }}
                                </td>
                            @endforeach
                            {{-- Eliminasi --}}
                            <td class="py-3.5 px-3 text-right font-bold num-cell text-amber-300">
                                {{ number_format($grandTotals['Pasiva']['eliminasi'] ?? 0, 2, ',', '.') }}
                            </td>
                            {{-- Non-Dept --}}
                            <td class="py-3.5 px-3 text-right font-bold num-cell text-emerald-300">
                                {{ number_format($grandTotals['Pasiva']['unassigned'] ?? 0, 2, ',', '.') }}
                            </td>
                        </tr>

                        {{-- BALANCE CHECK ROW --}}
                        <tr class="row-fixed" style="background: #f0fdf4; border-top: 1px solid #bbf7d0;">
                            {{-- Sticky Col 1: Balance Label --}}
                            <td class="py-2.5 px-4 col-sticky-1 font-semibold text-xs text-emerald-800"
                                style="background: #f0fdf4;">
                                <div class="cell-content flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>STATUS BALANCE (AKTIVA - PASIVA)</span>
                                </div>
                            </td>
                            {{-- Sticky Col 2: Balance Total Konsolidasi --}}
                            @php $diffTot = ($grandTotals['Aset']['total'] ?? 0) - ($grandTotals['Pasiva']['total'] ?? 0); @endphp
                            <td class="py-2.5 px-3 col-sticky-2 text-right font-bold text-xs num-cell text-emerald-800 bg-emerald-100/50"
                                style="background: #ecfdf5;">
                                {{ abs($diffTot) < 0.01 ? 'Rp 0,00' : number_format($diffTot, 2, ',', '.') }}
                            </td>
                            {{-- Branches --}}
                            @foreach ($departments as $dept)
                                @php $diff = ($grandTotals['Aset'][$dept->id] ?? 0) - ($grandTotals['Pasiva'][$dept->id] ?? 0); @endphp
                                <td class="py-2.5 px-3 text-right font-bold text-xs num-cell text-emerald-700 col-dept-{{ $dept->id }}">
                                    {{ abs($diff) < 0.01 ? 'Rp 0,00' : number_format($diff, 2, ',', '.') }}
                                </td>
                            @endforeach
                            {{-- Eliminasi --}}
                            <td class="py-2.5 px-3 text-right font-bold text-xs num-cell text-amber-700">0,00</td>
                            {{-- Non-Dept --}}
                            @php $diffUn = ($grandTotals['Aset']['unassigned'] ?? 0) - ($grandTotals['Pasiva']['unassigned'] ?? 0); @endphp
                            <td class="py-2.5 px-3 text-right font-bold text-xs num-cell text-emerald-700">
                                {{ abs($diffUn) < 0.01 ? 'Rp 0,00' : number_format($diffUn, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Sticky / Floating Horizontal Scrollbar --}}
            <div id="floatingScrollbarWrap" class="floating-scrollbar-wrap no-print">
                <div id="floatingScrollbarTrack" class="floating-scrollbar-inner"></div>
            </div>
        </div>

    @else
        {{-- ============================================================
             MODE 2: SINGLE DEPARTMENT VIEW (CLASSICAL 4-COLUMN)
             Supports: Konsolidasi, Unassigned (Non-Dept), and Branch IDs
        ============================================================ --}}
        @php
            $singleId = $singleData['targetCol'] ?? (($selectedDeptId === 'konsolidasi') ? 'total' : $selectedDeptId);
            $subTitleText = ($selectedDeptId === 'konsolidasi')
                ? 'Laporan Neraca Total Konsolidasi Perusahaan'
                : (($selectedDeptId === 'unassigned') ? 'Laporan Neraca Transaksi Non-Departemen' : 'Laporan Neraca Tunggal Cabang');
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800">
                        [{{ $singleDept->kode ?? '-' }}] {{ $singleDept->deskripsi ?? 'Departemen' }}
                    </h3>
                    <p class="text-xs text-slate-500">{{ $subTitleText }}</p>
                </div>
                <div class="px-3 py-1 bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-bold rounded-full flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    100% Balanced
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left" id="singleReportTable">
                    <thead>
                        <tr class="bg-slate-800 text-white text-xs uppercase tracking-wider">
                            <th class="py-3 px-4" style="min-width: 320px;">KETERANGAN</th>
                            <th class="py-3 px-4 text-right" style="width: 170px;">SUB ACCOUNT</th>
                            <th class="py-3 px-4 text-right" style="width: 170px;">ACCOUNT</th>
                            <th class="py-3 px-4 text-right" style="width: 180px;">GROUP ACCOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $tipeConfig = [
                                'Aset' => ['bg' => '#2563eb', 'light' => '#eff6ff', 'text' => '#1d4ed8', 'label' => 'AKTIVA / ASET'],
                                'Kewajiban' => ['bg' => '#d97706', 'light' => '#fffbeb', 'text' => '#b45309', 'label' => 'KEWAJIBAN / LIABILITAS'],
                                'Ekuitas' => ['bg' => '#7c3aed', 'light' => '#faf5ff', 'text' => '#6d28d9', 'label' => 'EKUITAS & DANA ANTAR UNIT'],
                            ];
                        @endphp

                        @foreach (['Aset', 'Kewajiban', 'Ekuitas'] as $tipe)
                            @php
                                $cfg = $tipeConfig[$tipe];
                                $tipeId = 'single-tipe-' . strtolower($tipe);
                                $accountsList = $neraca[$tipe] ?? [];
                                $groupIndex = 0;
                                $akunIndex = 0;
                                $currentGroupId = null;
                            @endphp

                            {{-- DETAIL ROWS --}}
                            @foreach ($accountsList as $item)
                                @php
                                    $lvl = $item['level_akun'];
                                    $vSelf = $item['balances_self'][$singleId] ?? 0;
                                    $vAgg = $item['balances'][$singleId] ?? 0;
                                    $isZero = $hideAccountWithZero && (abs($vAgg) < 0.001);
                                @endphp

                                @if ($lvl === 'HEADER')
                                    @php
                                        $hVal = $vAgg != 0 ? $vAgg : ($grandTotals[$tipe][$singleId] ?? 0);
                                    @endphp
                                    <tr class="row-tipe" data-tipe="{{ $tipeId }}" onclick="toggleTipe('{{ $tipeId }}')"
                                        style="background: {{ $cfg['bg'] }}; color: white; cursor: pointer;">
                                        <td colspan="3" class="py-3 px-4 font-bold text-xs uppercase tracking-wider text-white">
                                            <div class="cell-content flex items-center gap-2">
                                                <span class="toggle-chevron open" id="chevron-{{ $tipeId }}" style="color: white;">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                </span>
                                                <span>{{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-right font-bold num-cell text-white">
                                            {!! $hVal < 0
                                                ? '<span class="text-rose-200">(' . number_format(abs($hVal), 2, ',', '.') . ')</span>'
                                                : number_format($hVal, 2, ',', '.') !!}
                                        </td>
                                    </tr>

                                @elseif ($lvl === 'GROUP ACCOUNT')
                                    @php
                                        $groupIndex++;
                                        $currentGroupId = $tipeId . '-grp-' . $groupIndex;
                                    @endphp
                                    <tr class="row-group collapsible-row {{ $tipeId }}-rows"
                                        data-group="{{ $currentGroupId }}" onclick="toggleGroup('{{ $currentGroupId }}')"
                                        style="background: #f8fafc; border-left: 4px solid {{ $cfg['bg'] }}; cursor: pointer;">
                                        <td colspan="3" class="py-2.5 px-4 pl-7 font-semibold text-xs text-slate-800">
                                            <div class="cell-content flex items-center gap-2">
                                                <span class="toggle-chevron open" id="chevron-{{ $currentGroupId }}" style="color: {{ $cfg['text'] }};">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                </span>
                                                <span>{{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}</span>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-4 text-right font-bold text-slate-800 text-xs num-cell">
                                            {!! $vAgg < 0
                                                ? '<span class="num-negative">(' . number_format(abs($vAgg), 2, ',', '.') . ')</span>'
                                                : number_format($vAgg, 2, ',', '.') !!}
                                        </td>
                                    </tr>

                                @elseif ($lvl === 'ACCOUNT')
                                    @if ($isZero) @continue @endif
                                    @php
                                        $akunIndex++;
                                        $accountId = ($currentGroupId ?? $tipeId) . '-acc-' . $akunIndex;
                                        $hasChild = $item['has_child'] ?? false;
                                    @endphp
                                    <tr class="row-account collapsible-row {{ $tipeId }}-rows {{ $currentGroupId ? $currentGroupId . '-rows' : '' }}"
                                        @if ($hasChild) data-account="{{ $accountId }}" onclick="toggleAccount('{{ $accountId }}')" @endif
                                        style="background: white;">
                                        <td class="py-2 px-4 pl-10 text-xs font-semibold text-slate-700">
                                            <div class="cell-content flex items-center gap-2">
                                                @if ($hasChild)
                                                    <span class="toggle-chevron open" id="chevron-{{ $accountId }}" style="color: #64748b;">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                    </span>
                                                @else
                                                    <span class="w-4"></span>
                                                @endif
                                                <span class="account-name-label">
                                                    {{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}
                                                </span>
                                            </div>
                                        </td>
                                        <td></td>
                                        <td class="py-2 px-4 text-right font-bold num-cell">
                                            @if (abs($vAgg) > 0.001)
                                                @php
                                                    $linkParams = [
                                                        'start_date' => $tanggalAwalTahun,
                                                        'end_date' => $tanggalAkhir,
                                                        'selected_accounts' => $item['kode_akun'],
                                                    ];
                                                    if ($selectedDeptId === 'unassigned') {
                                                        $linkParams['departemen_id'] = 'unassigned';
                                                    } elseif ($selectedDeptId !== 'konsolidasi' && is_numeric($selectedDeptId)) {
                                                        $linkParams['departemen_id'] = $selectedDeptId;
                                                    }
                                                @endphp
                                                <a href="{{ route('buku_besar.buku_besar_report', $linkParams) }}"
                                                    target="_blank" class="drilldown-link {{ $vAgg < 0 ? 'num-negative' : 'num-positive' }}">
                                                    {{ $vAgg < 0 ? '(' . number_format(abs($vAgg), 2, ',', '.') . ')' : number_format($vAgg, 2, ',', '.') }}
                                                </a>
                                            @else
                                                <span class="text-slate-300">0,00</span>
                                            @endif
                                        </td>
                                        <td></td>
                                    </tr>

                                @elseif ($lvl === 'SUB ACCOUNT')
                                    @if ($isZero) @continue @endif
                                    <tr class="row-subaccount collapsible-row {{ $tipeId }}-rows {{ $currentGroupId ? $currentGroupId . '-rows' : '' }} {{ isset($accountId) ? $accountId . '-rows' : '' }}"
                                        style="background: #fafafa;">
                                        <td class="py-1.5 px-4 pl-14 text-[11px] text-slate-600">
                                            <div class="cell-content">
                                                <span class="account-name-label">
                                                    {{ $showAccountNumber ? $item['kode_akun'] . ' — ' . $item['nama_akun'] : $item['nama_akun'] }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-1.5 px-4 text-right text-[11px] num-cell text-slate-600">
                                            @if (abs($vSelf) > 0.001)
                                                @php
                                                    $linkParams = [
                                                        'start_date' => $tanggalAwalTahun,
                                                        'end_date' => $tanggalAkhir,
                                                        'selected_accounts' => $item['kode_akun'],
                                                    ];
                                                    if ($selectedDeptId === 'unassigned') {
                                                        $linkParams['departemen_id'] = 'unassigned';
                                                    } elseif ($selectedDeptId !== 'konsolidasi' && is_numeric($selectedDeptId)) {
                                                        $linkParams['departemen_id'] = $selectedDeptId;
                                                    }
                                                @endphp
                                                <a href="{{ route('buku_besar.buku_besar_report', $linkParams) }}"
                                                    target="_blank" class="drilldown-link {{ $vSelf < 0 ? 'num-negative' : 'num-positive' }}">
                                                    {{ $vSelf < 0 ? '(' . number_format(abs($vSelf), 2, ',', '.') . ')' : number_format($vSelf, 2, ',', '.') }}
                                                </a>
                                            @else
                                                <span class="text-slate-300">0,00</span>
                                            @endif
                                        </td>
                                        <td></td><td></td>
                                    </tr>

                                @elseif ($lvl === 'X')
                                    <tr class="collapsible-row {{ $tipeId }}-rows" style="background: #fdf4ff;">
                                        <td class="py-2.5 px-4 pl-10 text-xs font-bold text-purple-900">
                                            <div class="cell-content flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                                <span>{{ $item['nama_akun'] }}</span>
                                            </div>
                                        </td>
                                        <td></td>
                                        <td class="py-2.5 px-4 text-right font-bold num-cell">
                                            @php
                                                $incomeParams = [
                                                    'start_date' => $tanggalAwalTahun,
                                                    'end_date' => $tanggalAkhir,
                                                ];
                                                if ($selectedDeptId !== 'konsolidasi' && $selectedDeptId !== 'unassigned') {
                                                    $incomeParams['selected_departemens'] = $selectedDeptId;
                                                }
                                                $incomeRoute = ($selectedDeptId === 'konsolidasi' || $selectedDeptId === 'unassigned')
                                                    ? 'income_statement.income_statement_report'
                                                    : 'income_statement.income_statement_departement';
                                            @endphp
                                            <a href="{{ route($incomeRoute, $incomeParams) }}" target="_blank" class="drilldown-link {{ $vAgg < 0 ? 'num-negative' : 'text-purple-900' }}">
                                                {{ $vAgg < 0 ? '(' . number_format(abs($vAgg), 2, ',', '.') . ')' : number_format($vAgg, 2, ',', '.') }}
                                            </a>
                                        </td>
                                        <td></td>
                                    </tr>

                                @elseif ($lvl === 'RK')
                                    <tr class="collapsible-row {{ $tipeId }}-rows" style="background: #eef2ff;">
                                        <td class="py-2.5 px-4 pl-10 text-xs font-bold text-indigo-950">
                                            <div class="cell-content flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                                <span>{{ $item['nama_akun'] }}</span>
                                                <span class="text-[10px] font-normal px-1.5 py-0.5 bg-indigo-200 text-indigo-800 rounded">
                                                    Penyeimbang Otomatis
                                                </span>
                                            </div>
                                        </td>
                                        <td></td>
                                        <td class="py-2.5 px-4 text-right font-bold num-cell">
                                            @if ($selectedDeptId === 'konsolidasi')
                                                <span class="text-indigo-950 font-bold">0,00</span>
                                            @else
                                                <button type="button"
                                                    onclick="openRkAuditModal('{{ $singleId }}', '{{ $singleDept->deskripsi ?? 'Cabang' }}', '{{ $tanggalAkhir }}')"
                                                    title="Klik untuk melihat audit transaksi silang pembentuk R/K"
                                                    class="inline-block px-1.5 py-0.5 rounded bg-indigo-100 hover:bg-indigo-200 text-indigo-900 transition underline decoration-dotted">
                                                    {{ $vAgg < 0 ? '(' . number_format(abs($vAgg), 2, ',', '.') . ')' : number_format($vAgg, 2, ',', '.') }}
                                                </button>
                                            @endif
                                        </td>
                                        <td></td>
                                    </tr>
                                @endif
                            @endforeach

                            {{-- TOTAL PER TIPE --}}
                            <tr class="row-total" style="background: {{ $cfg['light'] }}; border-top: 2px solid {{ $cfg['bg'] }}; border-bottom: 2px solid {{ $cfg['bg'] }};">
                                <td colspan="3" class="py-3 px-4 font-bold text-xs uppercase" style="color: {{ $cfg['text'] }};">
                                    <div class="cell-content">
                                        <span>TOTAL {{ $cfg['label'] }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right font-bold num-cell" style="color: {{ $cfg['text'] }};">
                                    {{ number_format($grandTotals[$tipe][$singleId] ?? 0, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach

                        {{-- TOTAL PASIVA --}}
                        <tr class="row-fixed" style="background: #0f172a; color: white; border-top: 3px solid #6366f1;">
                            <td colspan="3" class="py-3.5 px-4 font-bold text-xs uppercase tracking-wider text-white">
                                <div class="cell-content">
                                    <span>TOTAL PASIVA (KEWAJIBAN &amp; EKUITAS)</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold num-cell text-emerald-300">
                                {{ number_format($grandTotals['Pasiva'][$singleId] ?? 0, 2, ',', '.') }}
                            </td>
                        </tr>

                        {{-- BALANCE STATUS --}}
                        @php
                            $diffSingle = ($grandTotals['Aset'][$singleId] ?? 0) - ($grandTotals['Pasiva'][$singleId] ?? 0);
                        @endphp
                        <tr class="row-fixed" style="background: #f0fdf4; border-top: 1px solid #bbf7d0;">
                            <td colspan="3" class="py-2.5 px-4 font-semibold text-xs text-emerald-800">
                                <div class="cell-content flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>STATUS BALANCE (AKTIVA - PASIVA)</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-4 text-right font-bold text-xs num-cell {{ abs($diffSingle) < 0.01 ? 'text-emerald-700' : 'text-red-600' }}">
                                {{ abs($diffSingle) < 0.01 ? 'Rp 0,00 (Balanced)' : number_format($diffSingle, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- QUICK AUDIT MODAL / DRAWER FOR REKENING KORAN (R/K) --}}
    <div id="rkAuditModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 no-print">
        <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            {{-- Modal Header --}}
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-white flex items-center gap-2">
                            Audit Transaksi Silang Rekening Koran (R/K)
                            <span class="px-2 py-0.5 rounded text-[10px] bg-indigo-500/30 text-indigo-200 border border-indigo-400/30" id="rkAuditDeptBadge">
                                Cabang
                            </span>
                        </h3>
                        <p class="text-[11px] text-slate-400" id="rkAuditSubtitle">
                            Daftar jurnal transfer dana dan mutasi antar cabang
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closeRkAuditModal()"
                    class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center transition text-xl leading-none">
                    &times;
                </button>
            </div>

            {{-- Modal Summary Stat Bar --}}
            <div class="px-6 py-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-4">
                    <div>
                        <span class="text-slate-500">Cabang:</span>
                        <span class="font-bold text-slate-800 ml-1" id="rkAuditDeptName">—</span>
                    </div>
                    <div>
                        <span class="text-slate-500">Cut-Off:</span>
                        <span class="font-bold text-slate-800 ml-1" id="rkAuditDate">—</span>
                    </div>
                    <div>
                        <span class="text-slate-500">Jumlah Transaksi:</span>
                        <span class="font-bold text-indigo-600 ml-1" id="rkAuditCount">0</span>
                    </div>
                </div>
                <div>
                    <span class="text-slate-500">Net Mutasi R/K:</span>
                    <span class="font-bold text-sm ml-1" id="rkAuditTotalNet">Rp 0,00</span>
                </div>
            </div>

            {{-- Modal Body / Content --}}
            <div class="p-6 overflow-y-auto flex-1" id="rkAuditContent">
                <div class="py-12 text-center text-slate-400">
                    <svg class="w-8 h-8 mx-auto animate-spin text-indigo-600 mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Memuat daftar transaksi silang...
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button type="button" onclick="closeRkAuditModal()"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>

{{-- SCRIPT: ACCORDION ENGINE, FULLSCREEN, FLOATING SCROLLBAR, COLUMN TOGGLE, QUICK SEARCH & RK AUDIT --}}
<script>
    // State sets for accordion hierarchy
    const openTipes = new Set(['tipe-aset', 'tipe-kewajiban', 'tipe-ekuitas', 'single-tipe-aset', 'single-tipe-kewajiban', 'single-tipe-ekuitas']);
    const openGroups = new Set();
    const openAccounts = new Set();
    let isFullscreen = false;

    document.addEventListener('DOMContentLoaded', function () {
        // Collect all groups and accounts to initialize open
        document.querySelectorAll('[data-group]').forEach(el => openGroups.add(el.dataset.group));
        document.querySelectorAll('[data-account]').forEach(el => openAccounts.add(el.dataset.account));

        // Initialize Crosshair Table Highlight
        initCrosshairHighlight();

        // Initialize Floating Scrollbar Synchronizer
        initFloatingScrollbar();

        // Close dropdown when clicked outside
        document.addEventListener('click', function(e) {
            const wrap = document.getElementById('colVisibilityDropdownWrap');
            if (wrap && !wrap.contains(e.target)) {
                const menu = document.getElementById('colVisibilityMenu');
                if (menu) menu.classList.add('hidden');
            }
        });

        // Listen for Escape key for Fullscreen & Modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (isFullscreen) {
                    toggleFullscreen();
                }
                const modal = document.getElementById('rkAuditModal');
                if (modal && !modal.classList.contains('hidden')) {
                    closeRkAuditModal();
                }
            }
        });
    });

    // 1. Crosshair Highlight (Row + Column Header)
    function initCrosshairHighlight() {
        const table = document.getElementById('matrixReportTable');
        const container = document.getElementById('matrixTableContainer');
        if (!table) return;

        table.addEventListener('mouseover', function(e) {
            const cell = e.target.closest('td');
            if (!cell) return;
            const row = cell.closest('tr');
            if (row && !row.classList.contains('row-tipe') && !row.classList.contains('row-fixed') && !row.classList.contains('row-total')) {
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

        // Ensure clean exit when mouse leaves the table entirely
        table.addEventListener('mouseleave', function() {
            table.querySelectorAll('.crosshair-row').forEach(r => r.classList.remove('crosshair-row'));
            table.querySelectorAll('thead th.crosshair-col').forEach(th => th.classList.remove('crosshair-col'));
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

            // Check if table is in viewport and its bottom is clipped below the screen fold
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
        const container = document.getElementById('neracaMainContainer');
        const topBar = document.getElementById('fullscreenTopBar');
        const btn = document.getElementById('btnFullscreen');
        if (!container) return;

        isFullscreen = !isFullscreen;

        if (isFullscreen) {
            container.classList.add('neraca-fullscreen');
            if (topBar) topBar.classList.remove('hidden');
            if (btn) btn.classList.add('active');
        } else {
            container.classList.remove('neraca-fullscreen');
            if (topBar) topBar.classList.add('hidden');
            if (btn) btn.classList.remove('active');
        }

        // Re-sync floating scrollbar width and position
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
            if (isVisible) {
                el.style.display = '';
            } else {
                el.style.display = 'none';
            }
        });

        // Update badge count
        const totalChecked = document.querySelectorAll('.dept-col-toggle:checked').length;
        const badge = document.getElementById('colCountBadge');
        if (badge) badge.textContent = totalChecked;

        // Re-sync scrollbar track width
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
    function toggleTipe(tipeId) {
        const rows = document.querySelectorAll('.' + tipeId + '-rows');
        const chevron = document.getElementById('chevron-' + tipeId);
        const isOpen = openTipes.has(tipeId);

        if (isOpen) {
            rows.forEach(r => r.classList.add('hidden'));
            if (chevron) chevron.classList.remove('open');
            openTipes.delete(tipeId);
        } else {
            rows.forEach(r => r.classList.remove('hidden'));
            if (chevron) chevron.classList.add('open');
            openTipes.add(tipeId);
        }
    }

    function toggleGroup(groupId) {
        const rows = document.querySelectorAll('.' + groupId + '-rows');
        const chevron = document.getElementById('chevron-' + groupId);
        const isOpen = openGroups.has(groupId);

        if (isOpen) {
            rows.forEach(r => r.classList.add('hidden'));
            if (chevron) chevron.classList.remove('open');
            openGroups.delete(groupId);
        } else {
            rows.forEach(r => r.classList.remove('hidden'));
            if (chevron) chevron.classList.add('open');
            openGroups.add(groupId);
        }
    }

    function toggleAccount(accountId) {
        const rows = document.querySelectorAll('.' + accountId + '-rows');
        const chevron = document.getElementById('chevron-' + accountId);
        const isOpen = openAccounts.has(accountId);

        if (isOpen) {
            rows.forEach(r => r.classList.add('hidden'));
            if (chevron) chevron.classList.remove('open');
            openAccounts.delete(accountId);
        } else {
            rows.forEach(r => r.classList.remove('hidden'));
            if (chevron) chevron.classList.add('open');
            openAccounts.add(accountId);
        }
    }

    function setLevel(lvl) {
        document.querySelectorAll('.ctrl-btn').forEach(b => {
            if (b.id && b.id.startsWith('btn-lvl-')) b.classList.remove('active');
        });
        if (document.getElementById('btn-lvl-' + lvl)) {
            document.getElementById('btn-lvl-' + lvl).classList.add('active');
        }

        // Always ensure fixed summary rows (Grand Total Pasiva, Status Balance) stay visible
        document.querySelectorAll('.row-fixed').forEach(r => r.classList.remove('hidden'));

        if (lvl === 'all') {
            document.querySelectorAll('.collapsible-row').forEach(r => r.classList.remove('hidden'));
            document.querySelectorAll('.toggle-chevron').forEach(c => c.classList.add('open'));
            openTipes.clear();
            document.querySelectorAll('[data-tipe]').forEach(el => openTipes.add(el.dataset.tipe));
            openGroups.clear();
            document.querySelectorAll('[data-group]').forEach(el => openGroups.add(el.dataset.group));
            openAccounts.clear();
            document.querySelectorAll('[data-account]').forEach(el => openAccounts.add(el.dataset.account));
        } else if (lvl === 2) {
            // Show Headers & Groups & Accounts, Hide Subaccounts
            document.querySelectorAll('.collapsible-row').forEach(r => r.classList.remove('hidden'));
            document.querySelectorAll('.row-subaccount').forEach(r => r.classList.add('hidden'));
            openTipes.clear();
            document.querySelectorAll('[data-tipe]').forEach(el => {
                openTipes.add(el.dataset.tipe);
                const ch = document.getElementById('chevron-' + el.dataset.tipe);
                if (ch) ch.classList.add('open');
            });
            openGroups.clear();
            document.querySelectorAll('[data-group]').forEach(el => {
                openGroups.add(el.dataset.group);
                const ch = document.getElementById('chevron-' + el.dataset.group);
                if (ch) ch.classList.add('open');
            });
            openAccounts.clear();
            document.querySelectorAll('[data-account]').forEach(el => {
                const accId = el.dataset.account;
                const rows = document.querySelectorAll('.' + accId + '-rows');
                rows.forEach(r => r.classList.add('hidden'));
                const ch = document.getElementById('chevron-' + accId);
                if (ch) ch.classList.remove('open');
            });
        } else if (lvl === 1) {
            // Show only Tipe and Headers, hide Groups, Accounts and Subaccounts
            document.querySelectorAll('.row-group, .row-account, .row-subaccount').forEach(r => r.classList.add('hidden'));
            openTipes.clear();
            document.querySelectorAll('[data-tipe]').forEach(el => {
                openTipes.add(el.dataset.tipe);
                const ch = document.getElementById('chevron-' + el.dataset.tipe);
                if (ch) ch.classList.add('open');
            });
            openGroups.clear();
            document.querySelectorAll('[data-group]').forEach(el => {
                const grpId = el.dataset.group;
                const rows = document.querySelectorAll('.' + grpId + '-rows');
                rows.forEach(r => r.classList.add('hidden'));
                const ch = document.getElementById('chevron-' + grpId);
                if (ch) ch.classList.remove('open');
            });
            openAccounts.clear();
            document.querySelectorAll('[data-account]').forEach(el => {
                const ch = document.getElementById('chevron-' + el.dataset.account);
                if (ch) ch.classList.remove('open');
            });
        } else if (lvl === 0) {
            // Collapse all under Tipe
            document.querySelectorAll('.collapsible-row').forEach(r => r.classList.add('hidden'));
            document.querySelectorAll('.toggle-chevron').forEach(c => c.classList.remove('open'));
            openTipes.clear();
            openGroups.clear();
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

        // Hide all collapsible rows initially; never hide row-tipe, row-total, or row-fixed
        rows.forEach(row => {
            if (row.classList.contains('row-tipe') || row.classList.contains('row-total') || row.classList.contains('row-fixed')) {
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
                    const ch = document.getElementById('chevron-' + accId);
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
                            const ch = document.getElementById('chevron-' + parentId);
                            if (ch) ch.classList.add('open');
                            openAccounts.add(parentId);
                        }
                        // If parent is group
                        const parentGrp = document.querySelector('[data-group="' + parentId + '"]');
                        if (parentGrp) {
                            parentGrp.classList.remove('hidden');
                            const ch = document.getElementById('chevron-' + parentId);
                            if (ch) ch.classList.add('open');
                            openGroups.add(parentId);
                        }
                        // If parent is tipe
                        const chTipe = document.getElementById('chevron-' + parentId);
                        if (chTipe) chTipe.classList.add('open');
                        openTipes.add(parentId);
                    }
                });
            }
        });
    }

    // 7. RK Quick Audit Modal
    function openRkAuditModal(deptId, deptName, cutOffDate) {
        const modal = document.getElementById('rkAuditModal');
        const badge = document.getElementById('rkAuditDeptBadge');
        const titleName = document.getElementById('rkAuditDeptName');
        const dateEl = document.getElementById('rkAuditDate');
        const countEl = document.getElementById('rkAuditCount');
        const totalNetEl = document.getElementById('rkAuditTotalNet');
        const contentEl = document.getElementById('rkAuditContent');

        badge.textContent = deptName;
        titleName.textContent = deptName;
        dateEl.textContent = cutOffDate;
        countEl.textContent = 'Memuat...';
        totalNetEl.textContent = '...';

        contentEl.innerHTML = `
            <div class="py-12 text-center text-slate-400">
                <svg class="w-8 h-8 mx-auto animate-spin text-indigo-600 mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Mengambil data jurnal transaksi silang...
            </div>
        `;

        modal.classList.remove('hidden');

        fetch(`{{ route('neraca_departemen.rk_audit') }}?departemen_id=${encodeURIComponent(deptId)}&end_date=${encodeURIComponent(cutOffDate)}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    countEl.textContent = data.count + ' jurnal';
                    const formattedNet = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.total_rk);
                    totalNetEl.textContent = (data.total_rk < 0 ? '(' + formattedNet.replace('-', '') + ')' : formattedNet);
                    totalNetEl.className = 'font-bold text-sm ml-1 ' + (data.total_rk < 0 ? 'text-red-600' : 'text-emerald-700');

                    if (data.journals.length === 0) {
                        contentEl.innerHTML = `
                            <div class="py-10 text-center text-slate-500">
                                <p class="font-medium text-sm">Tidak ada transaksi silang multi-departemen untuk cabang ini.</p>
                            </div>
                        `;
                        return;
                    }

                    let tableHtml = `
                        <table class="w-full text-left text-xs border border-slate-200 rounded-lg overflow-hidden">
                            <thead class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-2.5 px-3">Tanggal</th>
                                    <th class="py-2.5 px-3">No. Jurnal</th>
                                    <th class="py-2.5 px-3">Keterangan</th>
                                    <th class="py-2.5 px-3">Unit Lawan (Counterpart)</th>
                                    <th class="py-2.5 px-3 text-right">Debit Unit</th>
                                    <th class="py-2.5 px-3 text-right">Kredit Unit</th>
                                    <th class="py-2.5 px-3 text-right font-bold">Net (D - K)</th>
                                    <th class="py-2.5 px-2 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                    `;

                    data.journals.forEach(j => {
                        const debStr = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(j.dept_debit);
                        const crdStr = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(j.dept_credit);
                        const netStr = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(Math.abs(j.dept_net));
                        const netClass = j.dept_net < 0 ? 'text-red-600 font-bold' : 'text-slate-800 font-bold';
                        const netDisplay = j.dept_net < 0 ? `(${netStr})` : netStr;
                        const counterpartsBadge = j.counterparts.length > 0
                            ? j.counterparts.map(c => `<span class="inline-block px-1.5 py-0.5 rounded text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-200 mr-1">${c}</span>`).join('')
                            : '<span class="text-slate-400 italic">Internal</span>';

                        tableHtml += `
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-2 px-3 text-slate-600 whitespace-nowrap">${j.tanggal}</td>
                                <td class="py-2 px-3 font-semibold text-indigo-600 whitespace-nowrap">${j.source}</td>
                                <td class="py-2 px-3 text-slate-700">${j.comment}</td>
                                <td class="py-2 px-3">${counterpartsBadge}</td>
                                <td class="py-2 px-3 text-right num-cell">${debStr}</td>
                                <td class="py-2 px-3 text-right num-cell">${crdStr}</td>
                                <td class="py-2 px-3 text-right num-cell ${netClass}">${netDisplay}</td>
                                <td class="py-2 px-2 text-center whitespace-nowrap">
                                    <a href="/journal_entry/${j.id}" target="_blank"
                                        class="inline-flex items-center px-2 py-1 rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[10px] font-semibold transition"
                                        title="Buka rincian jurnal lengkap">
                                        Lihat
                                    </a>
                                </td>
                            </tr>
                        `;
                    });

                    tableHtml += `</tbody></table>`;
                    contentEl.innerHTML = tableHtml;
                } else {
                    contentEl.innerHTML = `<div class="p-6 text-center text-red-500 font-medium">Gagal memuat data audit.</div>`;
                }
            })
            .catch(err => {
                console.error(err);
                contentEl.innerHTML = `<div class="p-6 text-center text-red-500 font-medium">Terjadi kesalahan saat memuat data.</div>`;
            });
    }

    function closeRkAuditModal() {
        document.getElementById('rkAuditModal').classList.add('hidden');
    }
</script>
@endsection

{{-- resources/views/neraca/neraca_report.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    /* ============================================================
       NERACA — Premium Financial Report Styles
    ============================================================ */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    .neraca-wrap { font-family: 'Inter', sans-serif; }

    /* --- Header ------------------------------------------------ */
    .neraca-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        position: relative;
        overflow: hidden;
    }
    .neraca-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(99,102,241,0.12) 0%, transparent 70%);
        pointer-events: none;
    }

    /* --- Summary Cards ----------------------------------------- */
    .summary-card {
        backdrop-filter: blur(12px);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .summary-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }

    /* --- Table -------------------------------------------------- */
    .neraca-table { border-collapse: separate; border-spacing: 0; }
    .neraca-table thead th {
        position: sticky;
        top: 0;
        z-index: 20;
        background: #1e293b;
        color: white;
        font-size: 0.72rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 12px 16px;
        border-bottom: 2px solid #334155;
    }

    /* Level 1 — Tipe (ASET / KEWAJIBAN / EKUITAS) */
    .row-tipe {
        cursor: pointer;
        user-select: none;
        transition: background 0.15s;
    }
    .row-tipe:hover { filter: brightness(1.1); }

    /* Level 2 — Header / Group Account */
    .row-group {
        cursor: pointer;
        user-select: none;
        transition: background 0.15s;
    }
    .row-group:hover { filter: brightness(0.97); }

    /* Level 3 — Account */
    .row-account {
        cursor: pointer;
        transition: background 0.15s;
    }
    .row-account:hover { background: #f8fafc !important; }

    /* Level 4 — Sub Account */
    .row-subaccount { transition: background 0.15s; }
    .row-subaccount:hover { background: #f1f5f9 !important; }

    /* Collapse animation */
    .collapsible-row { transition: opacity 0.2s ease; }
    .collapsible-row.hidden { display: none; }

    /* Toggle icon */
    .toggle-chevron {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 4px;
        transition: transform 0.2s ease, background 0.15s;
        flex-shrink: 0;
    }
    .toggle-chevron.open { transform: rotate(90deg); }
    .toggle-chevron svg { width: 12px; height: 12px; }

    /* Negative number */
    .num-negative { color: #dc2626; }
    .num-positive { color: #111827; }
    .num-cell { font-variant-numeric: tabular-nums; font-size: 0.8rem; }

    /* Subtotal rows */
    .row-subtotal td { border-top: 1px solid #e2e8f0; }

    /* Total row */
    .row-total td { border-top: 2px solid rgba(255,255,255,0.3); }

    /* Progress bar */
    .prop-bar {
        height: 3px;
        border-radius: 2px;
        background: rgba(255,255,255,0.15);
        margin-top: 8px;
        overflow: hidden;
    }
    .prop-bar-fill {
        height: 100%;
        border-radius: 2px;
        transition: width 0.6s ease;
    }

    /* Quick search highlight */
    .search-highlight { background: #fef9c3; border-radius: 2px; }

    /* Control bar */
    .ctrl-btn {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
        border: 1px solid #e2e8f0;
        background: white;
        color: #374151;
    }
    .ctrl-btn:hover { background: #f1f5f9; border-color: #94a3b8; }
    .ctrl-btn.active { background: #1e293b; color: white; border-color: #1e293b; }

    /* Drill-down link */
    .drilldown-link {
        color: inherit;
        text-decoration: none;
        transition: color 0.15s;
    }
    .drilldown-link:hover { color: #3b82f6; text-decoration: underline; }

    /* ============================================================
       PRINT STYLES
    ============================================================ */
    @media print {
        .no-print { display: none !important; }
        .neraca-header { background: white !important; color: black !important; }
        .neraca-header * { color: black !important; }
        .summary-cards-section { display: none !important; }
        .neraca-table thead th { background: #1e293b !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .collapsible-row { display: table-row !important; opacity: 1 !important; }
        body { font-size: 11px; }
    }
</style>
@endpush

@section('content')
@php
    /* ---- helpers ---- */
    $fmt = fn($v) => number_format(abs($v), 2, ',', '.');
    $fmtSigned = fn($v) => $v < 0
        ? '<span class="num-negative">(' . number_format(abs($v), 2, ',', '.') . ')</span>'
        : '<span class="num-positive">' . number_format($v, 2, ',', '.') . '</span>';

    $totalKewEk = $grandTotalKewajiban + $grandTotalEkuitas;
    $isBalance  = abs($grandTotalAset - $totalKewEk) < 1;

    $propAset = $totalKewEk > 0 ? 100 : 0;
    $propKew  = $totalKewEk > 0 ? round($grandTotalKewajiban / max($totalKewEk,1) * 100, 1) : 0;
    $propEk   = $totalKewEk > 0 ? round($grandTotalEkuitas / max($totalKewEk,1) * 100, 1) : 0;

    $tipeColor = [
        'Aset'      => ['accent'=>'#3b82f6','light'=>'#eff6ff','border'=>'#3b82f6','text'=>'#1d4ed8','badge'=>'bg-blue-100 text-blue-800'],
        'Kewajiban' => ['accent'=>'#f59e0b','light'=>'#fffbeb','border'=>'#f59e0b','text'=>'#b45309','badge'=>'bg-amber-100 text-amber-800'],
        'Ekuitas'   => ['accent'=>'#10b981','light'=>'#ecfdf5','border'=>'#10b981','text'=>'#047857','badge'=>'bg-emerald-100 text-emerald-800'],
    ];
@endphp

<div class="neraca-wrap min-h-screen bg-slate-50">

    {{-- ============================================================
         PREMIUM HEADER
    ============================================================ --}}
    <div class="neraca-header px-6 py-5 no-print">
        <div class="max-w-full mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                {{-- Left: Title --}}
                <div class="flex items-center gap-4">
                    <img src="{{ asset('storage/' . \App\Setting::get('logo', 'logo.jpg')) }}"
                         alt="Logo" class="h-10 w-10 rounded-lg object-cover ring-2 ring-white/20">
                    <div>
                        <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">Balance Sheet</p>
                        <h1 class="text-white text-lg font-bold leading-tight">{{ $siteTitle }}</h1>
                        <p class="text-slate-300 text-xs mt-0.5">
                            Per {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y') }}
                        </p>
                    </div>
                </div>

                {{-- Right: Badge + Actions --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Balance Badge --}}
                    @if($isBalance)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold border border-emerald-500/30">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Balance
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-500/20 text-red-300 text-xs font-semibold border border-red-500/30">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            Tidak Balance
                        </span>
                    @endif

                    {{-- Action Buttons --}}
                    <a href="{{ route('neraca.export', ['end_date' => $tanggalAkhir, 'format' => 'excel']) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-medium border border-white/20 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </a>
                    <a href="{{ route('neraca.export', ['end_date' => $tanggalAkhir, 'format' => 'pdf']) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-medium border border-white/20 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        PDF
                    </a>
                    <button onclick="window.print()"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-medium border border-white/20 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print
                    </button>
                    <button onclick="document.getElementById('modifyModal').classList.remove('hidden')"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-500/80 hover:bg-indigo-500 text-white text-xs font-medium border border-indigo-400/30 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        Modify
                    </button>
                    <a href="{{ route('neraca.filter_neraca') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-medium border border-white/20 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                        Filter
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         SUMMARY CARDS
    ============================================================ --}}
    <div class="summary-cards-section px-6 py-4 no-print" style="background: linear-gradient(180deg, #0f172a 0%, #f8fafc 100%);">
        <div class="max-w-full mx-auto grid grid-cols-1 sm:grid-cols-3 gap-3">
            {{-- Aset --}}
            <div class="summary-card rounded-xl p-4" style="background:linear-gradient(135deg,#1d4ed8,#3b82f6); color:white;">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium opacity-80 uppercase tracking-wide">Total Aset</span>
                    <svg class="w-5 h-5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <p class="text-2xl font-bold leading-tight mt-1">{{ number_format($grandTotalAset, 2, ',', '.') }}</p>
                <div class="prop-bar mt-2"><div class="prop-bar-fill bg-white/60" style="width:100%"></div></div>
                <p class="text-xs opacity-70 mt-1.5">100% dari Total Aset</p>
            </div>

            {{-- Kewajiban --}}
            <div class="summary-card rounded-xl p-4" style="background:linear-gradient(135deg,#92400e,#f59e0b); color:white;">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium opacity-80 uppercase tracking-wide">Total Kewajiban</span>
                    <svg class="w-5 h-5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21l-7-7-7 7V5a2 2 0 012-2h10a2 2 0 012 2v16z"/></svg>
                </div>
                <p class="text-2xl font-bold leading-tight mt-1">{{ number_format($grandTotalKewajiban, 2, ',', '.') }}</p>
                <div class="prop-bar mt-2"><div class="prop-bar-fill bg-white/60" style="width:{{ $propKew }}%"></div></div>
                <p class="text-xs opacity-70 mt-1.5">{{ $propKew }}% dari Total Aset</p>
            </div>

            {{-- Ekuitas --}}
            <div class="summary-card rounded-xl p-4" style="background:linear-gradient(135deg,#065f46,#10b981); color:white;">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium opacity-80 uppercase tracking-wide">Total Ekuitas</span>
                    <svg class="w-5 h-5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <p class="text-2xl font-bold leading-tight mt-1">{{ number_format($grandTotalEkuitas, 2, ',', '.') }}</p>
                <div class="prop-bar mt-2"><div class="prop-bar-fill bg-white/60" style="width:{{ $propEk }}%"></div></div>
                <p class="text-xs opacity-70 mt-1.5">{{ $propEk }}% dari Total Aset</p>
            </div>
        </div>
    </div>

    {{-- ============================================================
         CONTROL BAR
    ============================================================ --}}
    <div class="px-6 pt-4 pb-2 no-print">
        <div class="max-w-full mx-auto flex flex-wrap items-center justify-between gap-3">
            {{-- Expand / Collapse Controls --}}
            <div class="flex items-center gap-1.5">
                <span class="text-xs text-slate-500 font-medium mr-1">Tampilkan:</span>
                <button class="ctrl-btn active" id="btn-expand-all" onclick="setLevel('all')">
                    Expand All
                </button>
                <button class="ctrl-btn" id="btn-level2" onclick="setLevel(2)">
                    Level 2
                </button>
                <button class="ctrl-btn" id="btn-level1" onclick="setLevel(1)">
                    Level 1
                </button>
                <button class="ctrl-btn" id="btn-collapse-all" onclick="setLevel(0)">
                    Collapse All
                </button>
            </div>

            {{-- Quick Search --}}
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="quickSearch" placeholder="Cari akun..."
                    class="pl-8 pr-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300 w-48">
            </div>
        </div>
    </div>

    {{-- ============================================================
         NERACA TABLE
    ============================================================ --}}
    <div class="px-6 pb-8">
        <div class="max-w-full mx-auto">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="neraca-table w-full text-sm" id="neracaTable">
                        <thead>
                            <tr>
                                <th class="text-left" style="min-width:320px">KETERANGAN</th>
                                <th class="text-right" style="min-width:160px">SUB ACCOUNT</th>
                                <th class="text-right" style="min-width:160px">ACCOUNT</th>
                                <th class="text-right" style="min-width:160px">GROUP ACCOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                            $norm = fn($v) => strtoupper(trim((string) $v));
                            $tipeIndex = 0;
                        @endphp

                        @foreach (['Aset', 'Kewajiban', 'Ekuitas'] as $tipe)
                            @if (!empty($neraca[$tipe]))
                            @php
                                $col = $tipeColor[$tipe];
                                $total = 0;
                                $currentGroupName = null;
                                $currentGroupTotal = 0;
                                $groupIndex = 0;
                                $tipeId = 'tipe-' . strtolower($tipe);
                                $tipeIndex++;

                                // Hitung grand total untuk tipe ini
                                $grandForTipe = $tipe === 'Aset' ? $grandTotalAset : ($tipe === 'Kewajiban' ? $grandTotalKewajiban : $grandTotalEkuitas);
                            @endphp

                            @foreach ($neraca[$tipe] as $akunIndex => $akun)

                                @if ($akun['level_akun'] === 'HEADER')
                                    {{-- ============================
                                         LEVEL 1: HEADER (ASSET, KEWAJIBAN, EKUITAS)
                                    ============================ --}}
                                    <tr class="row-tipe" data-tipe="{{ $tipeId }}" onclick="toggleTipe('{{ $tipeId }}')"
                                        style="background:#1e293b; color:white;">
                                        <td colspan="3" class="py-3 px-4" style="color:white;">
                                            <span class="flex items-center gap-2">
                                                <span class="toggle-chevron open" id="chevron-{{ $tipeId }}"
                                                    style="background:rgba(255,255,255,0.15); color:white;">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                </span>
                                                <span style="font-size:0.85rem; font-weight:700; letter-spacing:0.06em;">
                                                    {{ $showAccountNumber ? $akun['kode_akun'] . ' - ' . $akun['nama_akun'] : $akun['nama_akun'] }}
                                                </span>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-bold num-cell" style="color:white; font-size:0.85rem;">
                                            {!! ($akun['saldo'] ?? $grandForTipe) < 0
                                                ? '<span class="text-rose-300">(' . number_format(abs($akun['saldo'] ?? $grandForTipe), 2, ',', '.') . ')</span>'
                                                : number_format($akun['saldo'] ?? $grandForTipe, 2, ',', '.') !!}
                                        </td>
                                    </tr>

                                @elseif ($akun['level_akun'] === 'GROUP ACCOUNT')
                                    @php
                                        $currentGroupName = $akun['nama_akun'];
                                        $groupIndex++;
                                        $groupId = $tipeId . '-group-' . $groupIndex;
                                        $groupSaldo = $akun['saldo'] ?? 0;
                                    @endphp

                                    {{-- ============================
                                         LEVEL 2: GROUP ACCOUNT (collapsible)
                                    ============================ --}}
                                    <tr class="row-group collapsible-row {{ $tipeId }}-rows"
                                        data-group="{{ $groupId }}"
                                        onclick="toggleGroup('{{ $groupId }}')"
                                        style="background:#f8fafc; border-left:4px solid {{ $col['border'] }};">
                                        <td colspan="3" class="py-2.5 px-4 pl-8">
                                            <span class="flex items-center gap-2 font-semibold text-slate-700">
                                                <span class="toggle-chevron open" id="chevron-{{ $groupId }}"
                                                    style="background:{{ $col['light'] }}; color:{{ $col['accent'] }};">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                </span>
                                                {{ $showAccountNumber ? $akun['kode_akun'] . ' - ' . $akun['nama_akun'] : $akun['nama_akun'] }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-4 text-right font-bold num-cell text-slate-800 text-xs"
                                            id="{{ $groupId }}-total">
                                            {!! $groupSaldo < 0
                                                ? '<span class="num-negative">(' . number_format(abs($groupSaldo), 2, ',', '.') . ')</span>'
                                                : number_format($groupSaldo, 2, ',', '.') !!}
                                        </td>
                                    </tr>

                                @elseif ($akun['level_akun'] === 'ACCOUNT')
                                    @php
                                        $parentSaldo = $akun['saldo'] ?? 0;
                                        $parentCode  = (string) $akun['kode_akun'];
                                        $parentPrefix= rtrim($parentCode, '0');

                                        $childAccounts = collect($neraca[$tipe])->filter(
                                            fn($sub) => $norm($sub['level_akun']) === 'SUB ACCOUNT' &&
                                                \Illuminate\Support\Str::startsWith((string) $sub['kode_akun'], $parentPrefix)
                                        );
                                        $hasChild = $childAccounts->isNotEmpty();
                                        $accountId = $tipeId . '-account-' . $akunIndex;

                                        $total += $parentSaldo;
                                        $currentGroupTotal += $parentSaldo;
                                    @endphp

                                    @if ($hasChild)
                                        {{-- ============================
                                             LEVEL 3: ACCOUNT (collapsible, has sub accounts)
                                        ============================ --}}
                                        <tr class="row-account collapsible-row {{ $tipeId }}-rows {{ $groupId }}-rows"
                                            data-account="{{ $accountId }}"
                                            onclick="toggleAccount('{{ $accountId }}')"
                                            style="background:white; border-left:3px solid {{ $col['border'] }}33;">
                                            <td class="py-2 px-4 pl-14">
                                                <span class="flex items-center gap-2 text-slate-600">
                                                    <span class="toggle-chevron open" id="chevron-{{ $accountId }}"
                                                        style="background:#f1f5f9; color:{{ $col['accent'] }};">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                    </span>
                                                    {{ $showAccountNumber ? $akun['kode_akun'] . ' - ' . $akun['nama_akun'] : $akun['nama_akun'] }}
                                                </span>
                                            </td>
                                            <td></td>
                                            <td class="py-2 px-4 text-right font-medium num-cell text-slate-600">
                                                {!! $parentSaldo < 0
                                                    ? '<span class="num-negative">(' . number_format(abs($parentSaldo),2,',','.') . ')</span>'
                                                    : number_format($parentSaldo,2,',','.') !!}
                                            </td>
                                            <td></td>
                                        </tr>

                                        {{-- SUB ACCOUNTS --}}
                                        @foreach ($childAccounts as $sub)
                                            @php
                                                $showChild = !($hideAccountWithZero && ($sub['saldo_self'] ?? 0) == 0);
                                                $subSaldo  = $sub['saldo_self'] ?? ($sub['saldo'] ?? 0);
                                            @endphp
                                            @if ($showChild)
                                                {{-- ============================
                                                     LEVEL 4: SUB ACCOUNT
                                                ============================ --}}
                                                <tr class="row-subaccount collapsible-row {{ $tipeId }}-rows {{ $groupId }}-rows {{ $accountId }}-rows"
                                                    style="background:#fafafa; border-left:2px solid #e2e8f0;">
                                                    <td class="py-1.5 px-4 pl-20 text-slate-500 text-xs">
                                                        <a href="{{ route('buku_besar.buku_besar_report', [
                                                            'start_date'        => \Carbon\Carbon::parse($tanggalAkhir)->startOfYear()->toDateString(),
                                                            'end_date'          => $tanggalAkhir,
                                                            'selected_accounts' => $sub['kode_akun'],
                                                        ]) }}" class="drilldown-link flex items-center gap-1.5">
                                                            <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                            {{ $showAccountNumber ? $sub['kode_akun'] . ' — ' . $sub['nama_akun'] : $sub['nama_akun'] }}
                                                        </a>
                                                    </td>
                                                    <td class="py-1.5 px-4 text-right text-slate-500 num-cell text-xs">
                                                        {!! $subSaldo < 0
                                                            ? '<span class="num-negative">(' . number_format(abs($subSaldo),2,',','.') . ')</span>'
                                                            : number_format($subSaldo,2,',','.') !!}
                                                    </td>
                                                    <td></td><td></td>
                                                </tr>
                                            @endif
                                        @endforeach

                                    @else
                                        {{-- ACCOUNT tanpa sub account --}}
                                        <tr class="row-account collapsible-row {{ $tipeId }}-rows {{ $groupId }}-rows"
                                            style="background:white; border-left:3px solid {{ $col['border'] }}33;">
                                            <td class="py-2 px-4 pl-14">
                                                <a href="{{ route('buku_besar.buku_besar_report', [
                                                    'start_date'        => \Carbon\Carbon::parse($tanggalAkhir)->startOfYear()->toDateString(),
                                                    'end_date'          => $tanggalAkhir,
                                                    'selected_accounts' => $akun['kode_akun'],
                                                ]) }}" class="drilldown-link text-slate-600">
                                                    {{ $showAccountNumber ? $akun['kode_akun'] . ' - ' . $akun['nama_akun'] : $akun['nama_akun'] }}
                                                </a>
                                            </td>
                                            <td></td>
                                            <td class="py-2 px-4 text-right font-medium num-cell text-slate-600">
                                                {!! $parentSaldo < 0
                                                    ? '<span class="num-negative">(' . number_format(abs($parentSaldo),2,',','.') . ')</span>'
                                                    : number_format($parentSaldo,2,',','.') !!}
                                            </td>
                                            <td></td>
                                        </tr>
                                    @endif

                                @elseif ($akun['level_akun'] === 'SUB ACCOUNT')
                                    @continue

                                @else
                                    {{-- Level X = Laba Tahun Berjalan --}}
                                    @php
                                        $xSaldo = $akun['saldo'] ?? 0;
                                        $total += $xSaldo;
                                        $currentGroupTotal += $xSaldo;
                                    @endphp
                                    <tr class="collapsible-row {{ $tipeId }}-rows {{ $groupId }}-rows"
                                        style="background:white; border-left:3px solid {{ $col['border'] }}33;">
                                        <td class="py-2 px-4 pl-14 italic text-slate-600 text-xs">{{ $akun['nama_akun'] }}</td>
                                        <td></td>
                                        <td class="py-2 px-4 text-right font-medium num-cell text-slate-600">
                                            {!! $xSaldo < 0
                                                ? '<span class="num-negative">(' . number_format(abs($xSaldo),2,',','.') . ')</span>'
                                                : number_format($xSaldo,2,',','.') !!}
                                        </td>
                                        <td></td>
                                    </tr>
                                @endif
                            @endforeach

                            {{-- ============================
                                 TOTAL per TIPE
                            ============================ --}}
                            <tr class="row-total" style="background:{{ $col['accent'] }};">
                                <td class="py-3 px-4 font-bold text-white text-sm">
                                    TOTAL {{ strtoupper($tipe) }}
                                </td>
                                <td></td><td></td>
                                <td class="py-3 px-4 text-right font-bold text-white num-cell">
                                    {!! $grandForTipe < 0
                                        ? '(' . number_format(abs($grandForTipe),2,',','.') . ')'
                                        : number_format($grandForTipe,2,',','.') !!}
                                </td>
                            </tr>

                            {{-- Spacer --}}
                            <tr><td colspan="4" class="h-3 bg-slate-50"></td></tr>

                            @endif
                        @endforeach

                        {{-- ============================
                             RINGKASAN FINAL
                        ============================ --}}
                        <tr style="background:linear-gradient(90deg,#0f172a,#1e293b);">
                            <td class="py-4 px-4 font-bold text-white text-sm">TOTAL KEWAJIBAN DAN EKUITAS</td>
                            <td></td><td></td>
                            <td class="py-4 px-4 text-right font-bold text-white num-cell">
                                {{ number_format($grandTotalKewajiban + $grandTotalEkuitas, 2, ',', '.') }}
                            </td>
                        </tr>

                        {{-- Balance check row --}}
                        @if(!$isBalance)
                        <tr style="background:#fef2f2;">
                            <td colspan="4" class="py-2 px-4 text-center text-red-600 text-xs font-medium">
                                ⚠ Selisih: {{ number_format(abs($grandTotalAset - ($grandTotalKewajiban + $grandTotalEkuitas)), 2, ',', '.') }}
                                — Neraca tidak balance, harap periksa data.
                            </td>
                        </tr>
                        @endif

                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Legend --}}
            <div class="mt-3 flex flex-wrap gap-4 text-xs text-slate-400 no-print">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Aset
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Kewajiban
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Ekuitas
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Klik nama akun untuk buka Buku Besar
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="num-negative font-medium">(angka)</span> = nilai negatif
                </span>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL MODIFY
    ============================================================ --}}
    <div id="modifyModal"
        class="fixed inset-0 z-50 hidden bg-black/40 backdrop-blur-sm flex items-center justify-center no-print">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-xl mx-4">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-bold text-slate-800">Pengaturan Laporan</h3>
                <button onclick="document.getElementById('modifyModal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="GET" action="{{ route('neraca.neraca_report') }}" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Laporan</label>
                    <input type="date" name="end_date" value="{{ request('end_date', $tanggalAkhir) }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300" required>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="show_account_number" value="show_account_number"
                            class="rounded border-slate-300"
                            {{ request('show_account_number') == 'show_account_number' ? 'checked' : '' }}>
                        <span class="text-sm text-slate-700">Tampilkan Nomor Akun</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="hide_account_with_zero" value="hide_account_with_zero"
                            class="rounded border-slate-300"
                            {{ request('hide_account_with_zero') == 'hide_account_with_zero' ? 'checked' : '' }}>
                        <span class="text-sm text-slate-700">Sembunyikan Akun dengan Saldo Nol</span>
                    </label>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700">
                        Terapkan
                    </button>
                    <a href="{{ route('neraca.neraca_report') }}"
                        class="px-4 py-2 border border-slate-300 text-slate-700 text-sm rounded-lg hover:bg-slate-50 text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>{{-- /neraca-wrap --}}
@endsection

@push('scripts')
<script>
// ============================================================
//  NERACA — Interactive JS
// ============================================================

// ---- State tracking ----
const openGroups   = new Set();
const openAccounts = new Set();
const openTipes    = new Set();

// ---- Init: collect all IDs ----
document.addEventListener('DOMContentLoaded', function () {
    // All tipes start open
    document.querySelectorAll('[data-tipe]').forEach(el => {
        openTipes.add(el.dataset.tipe);
    });
    // All groups start open
    document.querySelectorAll('[data-group]').forEach(el => {
        openGroups.add(el.dataset.group);
        updateGroupTotal(el.dataset.group);
    });
    // All accounts start open
    document.querySelectorAll('[data-account]').forEach(el => {
        openAccounts.add(el.dataset.account);
    });

    setLevel('all', false); // init with all open, no animation
    updateAllLevelButtons('all');
    initQuickSearch();
});

// ---- Toggle TIPE (Level 1) ----
function toggleTipe(tipeId) {
    const rows    = document.querySelectorAll('.' + tipeId + '-rows');
    const chevron = document.getElementById('chevron-' + tipeId);

    if (openTipes.has(tipeId)) {
        rows.forEach(r => r.classList.add('hidden'));
        chevron.classList.remove('open');
        openTipes.delete(tipeId);
    } else {
        rows.forEach(r => {
            r.classList.remove('hidden');
            // Respect collapsed groups/accounts
            const gClass = [...r.classList].find(c => c.includes('-group-'));
            if (gClass && !openGroups.has(gClass.replace('-rows',''))) r.classList.add('hidden');
        });
        chevron.classList.add('open');
        openTipes.add(tipeId);
    }
}

// ---- Toggle GROUP (Level 2) ----
function toggleGroup(groupId) {
    const rows    = document.querySelectorAll('.' + groupId + '-rows');
    const chevron = document.getElementById('chevron-' + groupId);

    if (openGroups.has(groupId)) {
        rows.forEach(r => r.classList.add('hidden'));
        chevron && chevron.classList.remove('open');
        openGroups.delete(groupId);
    } else {
        rows.forEach(r => {
            r.classList.remove('hidden');
            // Respect collapsed accounts within this group
            const aClass = [...r.classList].find(c => c.includes('-account-'));
            if (aClass && !openAccounts.has(aClass.replace('-rows',''))) r.classList.add('hidden');
        });
        chevron && chevron.classList.add('open');
        openGroups.add(groupId);
        updateGroupTotal(groupId);
    }
}

// ---- Toggle ACCOUNT (Level 3) ----
function toggleAccount(accountId) {
    const rows    = document.querySelectorAll('.' + accountId + '-rows');
    const chevron = document.getElementById('chevron-' + accountId);

    if (openAccounts.has(accountId)) {
        rows.forEach(r => r.classList.add('hidden'));
        chevron && chevron.classList.remove('open');
        openAccounts.delete(accountId);
    } else {
        rows.forEach(r => r.classList.remove('hidden'));
        chevron && chevron.classList.add('open');
        openAccounts.add(accountId);
    }
}

// ---- Set Level (control panel) ----
function setLevel(level, animated = true) {
    const allGroupRows   = document.querySelectorAll('[class*="-group-"][class*="-rows"]');
    const allAccountRows = document.querySelectorAll('[class*="-account-"][class*="-rows"]');
    const allTipeRows    = document.querySelectorAll('[class$="-rows"]:not([class*="-group-"]):not([class*="-account-"])');

    openGroups.clear();
    openAccounts.clear();

    if (level === 0) {
        // Collapse All: hide everything inside tipe
        document.querySelectorAll('.collapsible-row').forEach(r => r.classList.add('hidden'));
        document.querySelectorAll('[id^="chevron-tipe-"]').forEach(c => c.classList.remove('open'));
        document.querySelectorAll('[id^="chevron-"][id*="-group-"]').forEach(c => c.classList.remove('open'));
        document.querySelectorAll('[id^="chevron-"][id*="-account-"]').forEach(c => c.classList.remove('open'));

    } else if (level === 1) {
        // Level 1: tipe rows visible, groups collapsed inside
        document.querySelectorAll('.collapsible-row').forEach(r => r.classList.add('hidden'));
        // Show only direct tipe-rows (not group/account rows)
        document.querySelectorAll('[data-tipe]').forEach(el => {
            const tipeId = el.dataset.tipe;
            openTipes.add(tipeId);
            // Show HEADER and GROUP rows (direct children of tipe)
            document.querySelectorAll('.' + tipeId + '-rows').forEach(r => {
                const cls = [...r.classList];
                const isGroupRow = cls.some(c => c.endsWith('-rows') && c.includes('-group-'));
                const isAccountRow = cls.some(c => c.endsWith('-rows') && c.includes('-account-'));
                if (!isGroupRow && !isAccountRow) r.classList.remove('hidden');
            });
            const ch = document.getElementById('chevron-' + tipeId);
            if (ch) ch.classList.add('open');
        });
        document.querySelectorAll('[id^="chevron-"][id*="-group-"]').forEach(c => c.classList.remove('open'));
        document.querySelectorAll('[id^="chevron-"][id*="-account-"]').forEach(c => c.classList.remove('open'));

    } else if (level === 2) {
        // Level 2: show groups, hide accounts
        document.querySelectorAll('.collapsible-row').forEach(r => r.classList.add('hidden'));
        document.querySelectorAll('[data-tipe]').forEach(el => {
            openTipes.add(el.dataset.tipe);
            const ch = document.getElementById('chevron-' + el.dataset.tipe);
            if (ch) ch.classList.add('open');
        });
        document.querySelectorAll('[data-group]').forEach(el => {
            const groupId = el.dataset.group;
            openGroups.add(groupId);
            const tipeId = groupId.split('-group-')[0];
            // Show tipe rows that are NOT account-specific
            document.querySelectorAll('.' + tipeId + '-rows').forEach(r => {
                const cls = [...r.classList];
                const isAccountRow = cls.some(c => c.endsWith('-rows') && c.includes('-account-'));
                if (!isAccountRow) r.classList.remove('hidden');
            });
            const ch = document.getElementById('chevron-' + groupId);
            if (ch) ch.classList.add('open');
        });
        document.querySelectorAll('[id^="chevron-"][id*="-account-"]').forEach(c => c.classList.remove('open'));
        document.querySelectorAll('[data-group]').forEach(el => updateGroupTotal(el.dataset.group));

    } else {
        // Expand All
        document.querySelectorAll('.collapsible-row').forEach(r => r.classList.remove('hidden'));
        document.querySelectorAll('[id^="chevron-"]').forEach(c => c.classList.add('open'));
        document.querySelectorAll('[data-tipe]').forEach(el => openTipes.add(el.dataset.tipe));
        document.querySelectorAll('[data-group]').forEach(el => {
            openGroups.add(el.dataset.group);
            updateGroupTotal(el.dataset.group);
        });
        document.querySelectorAll('[data-account]').forEach(el => openAccounts.add(el.dataset.account));
    }

    updateAllLevelButtons(level);
}

// ---- Update group total display ----
function updateGroupTotal(groupId) {
    const totalEl = document.getElementById(groupId + '-total');
    if (!totalEl) return;

    let sum = 0;
    document.querySelectorAll('.' + groupId + '-rows').forEach(r => {
        const accountCell = r.querySelector('td:nth-child(3)');
        if (accountCell && accountCell.textContent.trim() !== '') {
            const txt = accountCell.textContent.replace(/[^0-9,.-]/g,'').replace(/\./g,'').replace(',','.');
            const val = parseFloat(txt) || 0;
            // Check negative (parentheses)
            if (accountCell.querySelector('.num-negative')) sum -= val;
            else sum += val;
        }
    });
    // Show formatted
    totalEl.innerHTML = sum === 0 ? '—' :
        (sum < 0
            ? `<span class="num-negative">(${Math.abs(sum).toLocaleString('id-ID',{minimumFractionDigits:2})})</span>`
            : sum.toLocaleString('id-ID',{minimumFractionDigits:2}));
}

// ---- Control buttons state ----
function updateAllLevelButtons(active) {
    const map = { 'all': 'btn-expand-all', 0: 'btn-collapse-all', 1: 'btn-level1', 2: 'btn-level2' };
    Object.values(map).forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('active');
    });
    const target = map[active];
    if (target) {
        const el = document.getElementById(target);
        if (el) el.classList.add('active');
    }
}

// ---- Quick Search ----
function initQuickSearch() {
    const input = document.getElementById('quickSearch');
    if (!input) return;

    input.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#neracaTable tbody tr');

        rows.forEach(row => {
            if (!q) {
                row.style.display = '';
                return;
            }
            const text = row.textContent.toLowerCase();
            const isStructural = row.classList.contains('row-tipe') ||
                                 row.querySelector('[id^="chevron-tipe-"]');

            if (isStructural) {
                row.style.display = '';
            } else {
                row.style.display = text.includes(q) ? '' : 'none';
            }
        });
    });
}
</script>
@endpush

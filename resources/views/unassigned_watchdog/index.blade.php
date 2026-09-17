{{-- resources/views/unassigned_watchdog/index.blade.php --}}
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
        background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
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
        transform: translateX(-50%) translateY(120px);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 40;
    }
    .bulk-toolbar.active {
        transform: translateX(-50%) translateY(0);
    }
    .row-assigned {
        background-color: #ecfdf5 !important;
        transition: background-color 0.5s ease, opacity 0.5s ease;
    }
    .table-watchdog th {
        font-size: 0.73rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        background-color: #1e293b;
        color: #f8fafc;
        border-bottom: 2px solid #334155;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-800 pb-24">

    {{-- Header Banner --}}
    <div class="watchdog-header text-white px-6 py-8 mb-6 shadow-lg">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">
                        <i class="fas fa-shield-alt mr-1"></i> AUDIT &amp; GOVERNANCE
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/30 text-amber-200 border border-amber-400/30">
                        <i class="fas fa-exclamation-triangle mr-1"></i> WATCHDOG
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white flex items-center gap-2">
                    <span>Unassigned Watchdog</span>
                </h1>
                <p class="text-slate-300 text-sm mt-1">
                    Pusat Kendali &amp; Penugasan Transaksi Jurnal Tanpa Departemen (<code class="text-amber-300">departemen_akun_id IS NULL</code>)
                </p>
            </div>

            @php
                $queryParams = request()->query();
                $exportExcelUrl = \Illuminate\Support\Facades\Route::has('unassigned_watchdog.export')
                    ? route('unassigned_watchdog.export', array_merge($queryParams, ['format' => 'excel']))
                    : url('/unassigned_watchdog/export?' . http_build_query(array_merge($queryParams, ['format' => 'excel'])));
                $exportPdfUrl = \Illuminate\Support\Facades\Route::has('unassigned_watchdog.export')
                    ? route('unassigned_watchdog.export', array_merge($queryParams, ['format' => 'pdf']))
                    : url('/unassigned_watchdog/export?' . http_build_query(array_merge($queryParams, ['format' => 'pdf'])));
                $resetUrl = \Illuminate\Support\Facades\Route::has('unassigned_watchdog.index')
                    ? route('unassigned_watchdog.index')
                    : url('/unassigned_watchdog');
            @endphp

            <div class="flex flex-wrap items-center gap-2">
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
                    <i class="fas fa-sync-alt mr-2"></i> Reset Filter
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        {{-- KPI Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Card 1: Total Unassigned --}}
            <div class="kpi-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Unassigned</p>
                        <h3 class="text-3xl font-extrabold text-amber-600 mt-1" id="kpi-total-display">
                            {{ number_format($kpis['total'] ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center text-xl shadow-inner">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center text-xs text-slate-500">
                    <span class="inline-flex items-center text-amber-700 font-medium">
                        <i class="fas fa-clock mr-1"></i> Perlu Penugasan Unit
                    </span>
                    <span class="mx-1.5 text-slate-300">•</span>
                    <span>Seluruh Tahun</span>
                </div>
            </div>

            {{-- Card 2: Akun Riil (Neraca) --}}
            <div class="kpi-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Akun Riil (Neraca)</p>
                        <h3 class="text-3xl font-extrabold text-blue-600 mt-1" id="kpi-real-display">
                            {{ number_format($kpis['real'] ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center text-xl shadow-inner">
                        <i class="fas fa-landmark"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center text-xs text-slate-500">
                    <span class="inline-flex items-center text-blue-700 font-medium">
                        <i class="fas fa-balance-scale mr-1"></i> Aset, Hutang, Ekuitas
                    </span>
                    <span class="mx-1.5 text-slate-300">•</span>
                    <span>Neraca Cabang</span>
                </div>
            </div>

            {{-- Card 3: Akun Nominal (Laba Rugi) --}}
            <div class="kpi-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Akun Nominal (L/R)</p>
                        <h3 class="text-3xl font-extrabold text-purple-600 mt-1" id="kpi-nominal-display">
                            {{ number_format($kpis['nominal'] ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-xl flex items-center justify-center text-xl shadow-inner">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center text-xs text-slate-500">
                    <span class="inline-flex items-center text-purple-700 font-medium">
                        <i class="fas fa-receipt mr-1"></i> Pendapatan &amp; Beban
                    </span>
                    <span class="mx-1.5 text-slate-300">•</span>
                    <span>Laba Rugi Wahana</span>
                </div>
            </div>

            {{-- Card 4: Total Mutasi Unassigned --}}
            <div class="kpi-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Nilai Debit</p>
                        <h3 class="text-lg font-bold text-slate-800 mt-1" title="Rp {{ number_format($kpis['total_debit'] ?? 0, 2, ',', '.') }}">
                            Rp {{ number_format(($kpis['total_debit'] ?? 0) / 1000000, 1, ',', '.') }} Jt
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Kredit: <strong class="text-slate-700">Rp {{ number_format(($kpis['total_credit'] ?? 0) / 1000000, 1, ',', '.') }} Jt</strong>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center text-xl shadow-inner">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
                <div class="mt-2 text-xs text-slate-400 truncate">
                    Total Rp {{ number_format($kpis['total_debit'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>

        {{-- Filter Panel --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-6">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-filter text-indigo-500 text-sm"></i>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Filter Transaksi Watchdog</h2>
                </div>
                <span class="text-xs text-slate-500">
                    Menampilkan <strong class="text-slate-800">{{ number_format($filteredCount, 0, ',', '.') }}</strong> dari {{ number_format($kpis['total'], 0, ',', '.') }} baris unassigned
                </span>
            </div>

            <form action="{{ \Illuminate\Support\Facades\Route::has('unassigned_watchdog.index') ? route('unassigned_watchdog.index') : url('/unassigned_watchdog') }}" method="GET" class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    {{-- Tanggal Awal --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                               class="w-full text-xs rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>

                    {{-- Tanggal Akhir --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Sampai</label>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                               class="w-full text-xs rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>

                    {{-- Kategori Akun --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori Akun</label>
                        <select name="kategori" class="w-full text-xs rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="all" {{ $kategori === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                            <option value="real" {{ $kategori === 'real' ? 'selected' : '' }}>Akun Riil (Neraca)</option>
                            <option value="nominal" {{ $kategori === 'nominal' ? 'selected' : '' }}>Akun Nominal (L/R)</option>
                        </select>
                    </div>

                    {{-- Tipe Akun --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tipe Akun</label>
                        <select name="tipe_akun" class="w-full text-xs rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="all" {{ $tipeAkun === 'all' ? 'selected' : '' }}>Semua Tipe Akun</option>
                            @foreach ($tipeAkunList as $t)
                                <option value="{{ $t }}" {{ $tipeAkun === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Voucher Prefix --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Prefix Source</label>
                        <select name="source_prefix" class="w-full text-xs rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="all" {{ $sourcePrefix === 'all' ? 'selected' : '' }}>Semua Prefix</option>
                            @foreach ($sourcePrefixes as $sp)
                                <option value="{{ $sp->prefix }}" {{ $sourcePrefix === $sp->prefix ? 'selected' : '' }}>
                                    {{ $sp->prefix }} ({{ $sp->count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Per Page --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tampilkan</label>
                        <select name="per_page" class="w-full text-xs rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 baris</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 baris</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 baris</option>
                            <option value="250" {{ $perPage == 250 ? 'selected' : '' }}>250 baris</option>
                        </select>
                    </div>
                </div>

                {{-- Keyword Search & Actions --}}
                <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="relative w-full sm:w-96">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Cari voucher, kode akun, memo..."
                               class="pl-9 w-full text-xs rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>

                    <div class="flex items-center space-x-2 w-full sm:w-auto justify-end">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors duration-150">
                            <i class="fas fa-filter mr-1.5"></i> Terapkan Filter
                        </button>
                        <a href="{{ \Illuminate\Support\Facades\Route::has('unassigned_watchdog.index') ? route('unassigned_watchdog.index') : url('/unassigned_watchdog') }}"
                           class="inline-flex items-center px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium transition-colors duration-150">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center space-x-3">
                    <label class="inline-flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" id="check-all" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span>Pilih Semua di Halaman Ini</span>
                    </label>
                    <span class="text-slate-300">|</span>
                    <span class="text-xs text-slate-500">
                        Total Terpilih: <strong id="selected-counter" class="text-indigo-600">0</strong>
                    </span>
                </div>
                <div class="text-xs text-slate-500">
                    Halaman {{ $details->currentPage() }} dari {{ $details->lastPage() }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-watchdog text-xs">
                    <thead>
                        <tr>
                            <th class="py-3 px-3 w-10 text-center">
                                <span class="sr-only">Pilih</span>
                            </th>
                            <th class="py-3 px-3 w-12 text-center">NO</th>
                            <th class="py-3 px-3 w-28 text-center">TANGGAL</th>
                            <th class="py-3 px-3 w-36">NO. VOUCHER</th>
                            <th class="py-3 px-3 w-32">KODE AKUN</th>
                            <th class="py-3 px-3">NAMA AKUN</th>
                            <th class="py-3 px-3 w-28 text-center">KATEGORI</th>
                            <th class="py-3 px-3">KETERANGAN</th>
                            <th class="py-3 px-3 w-32 text-right">DEBIT (RP)</th>
                            <th class="py-3 px-3 w-32 text-right">KREDIT (RP)</th>
                            <th class="py-3 px-4 w-72 text-center">PENUGASAN DEPARTEMEN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700" id="watchdog-table-body">
                        @forelse ($details as $index => $row)
                            @php
                                $account = $row->chartOfAccount;
                                $tipe = $account->tipe_akun ?? '-';
                                $isReal = in_array($tipe, ['Aset', 'Kewajiban', 'Ekuitas']);
                                $memo = !empty($row->comment) ? $row->comment : ($row->journalEntry->comment ?? '-');
                            @endphp
                            <tr id="row-detail-{{ $row->id }}"
                                class="hover:bg-slate-50/80 transition-colors duration-150"
                                data-id="{{ $row->id }}"
                                data-category="{{ $isReal ? 'real' : 'nominal' }}">
                                {{-- Checkbox --}}
                                <td class="py-3 px-3 text-center">
                                    <input type="checkbox"
                                           class="row-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer"
                                           value="{{ $row->id }}">
                                </td>

                                {{-- Number --}}
                                <td class="py-3 px-3 text-center text-slate-400 font-mono">
                                    {{ $details->firstItem() + $index }}
                                </td>

                                {{-- Tanggal --}}
                                <td class="py-3 px-3 text-center whitespace-nowrap text-slate-600">
                                    {{ $row->journalEntry->tanggal ? \Carbon\Carbon::parse($row->journalEntry->tanggal)->format('d/m/Y') : '-' }}
                                </td>

                                {{-- Voucher --}}
                                <td class="py-3 px-3 font-semibold text-indigo-950 whitespace-nowrap">
                                    {{ $row->journalEntry->source ?? '-' }}
                                </td>

                                {{-- Kode Akun --}}
                                <td class="py-3 px-3 font-mono font-medium text-slate-900 whitespace-nowrap">
                                    {{ $row->kode_akun }}
                                </td>

                                {{-- Nama Akun --}}
                                <td class="py-3 px-3 font-medium text-slate-800">
                                    {{ $account->nama_akun ?? '-' }}
                                </td>

                                {{-- Kategori & Tipe --}}
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    @if ($isReal)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-700">
                                            Riil &bull; {{ $tipe }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-100 text-purple-700">
                                            Nominal &bull; {{ $tipe }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Keterangan --}}
                                <td class="py-3 px-3 text-slate-500 max-w-xs truncate" title="{{ $memo }}">
                                    {{ $memo }}
                                </td>

                                {{-- Debit --}}
                                <td class="py-3 px-3 text-right font-mono font-medium text-slate-800 whitespace-nowrap">
                                    {{ $row->debits > 0 ? number_format($row->debits, 2, ',', '.') : '-' }}
                                </td>

                                {{-- Kredit --}}
                                <td class="py-3 px-3 text-right font-mono font-medium text-slate-800 whitespace-nowrap">
                                    {{ $row->credits > 0 ? number_format($row->credits, 2, ',', '.') : '-' }}
                                </td>

                                {{-- Action Column --}}
                                <td class="py-2.5 px-4 text-center whitespace-nowrap action-cell">
                                    <div class="flex items-center space-x-1.5 justify-center">
                                        <select class="single-dept-select text-xs rounded-lg border-slate-300 py-1 px-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm bg-white"
                                                id="dept-select-{{ $row->id }}">
                                            <option value="">-- Pilih Unit/Dept --</option>
                                            @foreach ($departments as $dept)
                                                <option value="{{ $dept->id }}">
                                                    {{ $dept->kode }} - {{ $dept->deskripsi }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button"
                                                class="btn-assign-single inline-flex items-center px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-all duration-150"
                                                data-id="{{ $row->id }}">
                                            <i class="fas fa-check mr-1"></i> Tugaskan
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-2xl mb-3 shadow-inner">
                                            <i class="fas fa-check-double"></i>
                                        </div>
                                        <h3 class="text-base font-bold text-slate-800">Semua Transaksi Telah Ditugaskan!</h3>
                                        <p class="text-xs text-slate-500 mt-1 max-w-md">
                                            Tidak ada transaksi jurnal tanpa departemen yang sesuai dengan kriteria filter saat ini.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Links --}}
            @if ($details->hasPages())
                <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
                    {{ $details->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- Floating Bulk Action Toolbar --}}
    <div id="bulk-toolbar" class="bulk-toolbar">
        <div class="bg-slate-900 text-white rounded-2xl shadow-2xl border border-slate-700 px-6 py-3.5 flex flex-wrap items-center gap-4">
            <div class="flex items-center space-x-2">
                <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs font-bold" id="bulk-badge-count">
                    0
                </span>
                <span class="text-xs font-semibold text-slate-200">
                    Transaksi Terpilih
                </span>
            </div>

            <div class="h-5 w-px bg-slate-700"></div>

            <div class="flex items-center space-x-2">
                <label for="bulk-dept-select" class="text-xs text-slate-300 font-medium">Tugaskan ke:</label>
                <select id="bulk-dept-select" class="text-xs bg-slate-800 text-white border-slate-600 rounded-lg py-1 px-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Pilih Departemen Tujuan --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">
                            {{ $dept->kode }} - {{ $dept->deskripsi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button type="button" id="btn-bulk-execute"
                        class="inline-flex items-center px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold shadow-md transition-colors duration-150">
                    <i class="fas fa-tags mr-1.5"></i> Tugaskan Masal
                </button>
                <button type="button" id="btn-bulk-cancel"
                        class="inline-flex items-center px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-xs font-medium transition-colors duration-150">
                    <i class="fas fa-times mr-1"></i> Batal
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // CSRF Token header for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const assignUrl = "{{ \Illuminate\Support\Facades\Route::has('unassigned_watchdog.assign') ? route('unassigned_watchdog.assign') : url('/unassigned_watchdog/assign') }}";
    const assignBulkUrl = "{{ \Illuminate\Support\Facades\Route::has('unassigned_watchdog.assign_bulk') ? route('unassigned_watchdog.assign_bulk') : url('/unassigned_watchdog/assign_bulk') }}";

    // Helper: update KPI counters on the UI
    function updateKpis(kpis) {
        if (!kpis) return;
        if (typeof kpis.total !== 'undefined') {
            $('#kpi-total-display').text(new Intl.NumberFormat('id-ID').format(kpis.total));
        }
        if (typeof kpis.real !== 'undefined') {
            $('#kpi-real-display').text(new Intl.NumberFormat('id-ID').format(kpis.real));
        }
        if (typeof kpis.nominal !== 'undefined') {
            $('#kpi-nominal-display').text(new Intl.NumberFormat('id-ID').format(kpis.nominal));
        }
    }

    // Single Assign Handler
    $(document).on('click', '.btn-assign-single', function () {
        const btn = $(this);
        const detailId = btn.data('id');
        const deptSelect = $('#dept-select-' + detailId);
        const deptId = deptSelect.val();

        if (!deptId) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Departemen',
                text: 'Silakan pilih departemen tujuan terlebih dahulu.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Proses...');

        $.ajax({
            url: assignUrl,
            type: 'POST',
            data: {
                detail_id: detailId,
                departemen_id: deptId
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 3000
                    });

                    // Highlight row green & show badge
                    const row = $('#row-detail-' + detailId);
                    row.addClass('row-assigned');
                    row.find('.action-cell').html(
                        '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">' +
                        '<i class="fas fa-check-circle mr-1"></i> ' + response.departemen_nama +
                        '</span>'
                    );

                    // Uncheck and update bulk selection
                    row.find('.row-checkbox').prop('checked', false).prop('disabled', true);
                    syncBulkSelection();

                    // Update live KPI counters
                    updateKpis(response.kpis);

                    // Fade out row smoothly after a brief pause
                    setTimeout(function () {
                        row.fadeOut(400, function () {
                            $(this).remove();
                            // If table is now empty, reload to show clean state or refresh pagination
                            if ($('#watchdog-table-body tr').length === 0) {
                                window.location.reload();
                            }
                        });
                    }, 1200);
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).html(originalHtml);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menugaskan',
                    text: msg,
                    confirmButtonColor: '#4f46e5'
                });
            }
        });
    });

    // Checkbox Selection & Bulk Toolbar Management
    function syncBulkSelection() {
        const checkedBoxes = $('.row-checkbox:checked');
        const count = checkedBoxes.length;
        $('#selected-counter').text(count);
        $('#bulk-badge-count').text(count);

        if (count > 0) {
            $('#bulk-toolbar').addClass('active');
        } else {
            $('#bulk-toolbar').removeClass('active');
            $('#check-all').prop('checked', false);
        }
    }

    $('#check-all').on('change', function () {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox:not(:disabled)').prop('checked', isChecked);
        syncBulkSelection();
    });

    $(document).on('change', '.row-checkbox', function () {
        syncBulkSelection();
        const total = $('.row-checkbox:not(:disabled)').length;
        const checked = $('.row-checkbox:checked').length;
        $('#check-all').prop('checked', total > 0 && total === checked);
    });

    $('#btn-bulk-cancel').on('click', function () {
        $('.row-checkbox').prop('checked', false);
        $('#check-all').prop('checked', false);
        syncBulkSelection();
    });

    // Bulk Execute Handler
    $('#btn-bulk-execute').on('click', function () {
        const checkedBoxes = $('.row-checkbox:checked');
        const detailIds = [];
        checkedBoxes.each(function () {
            detailIds.push($(this).val());
        });

        if (detailIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Ada Baris Terpilih',
                text: 'Pilih minimal satu baris transaksi untuk ditugaskan masal.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        const deptId = $('#bulk-dept-select').val();
        const deptText = $('#bulk-dept-select option:selected').text().trim();

        if (!deptId) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Departemen Tujuan',
                text: 'Silakan pilih departemen tujuan untuk penugasan masal.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Penugasan Masal',
            html: 'Tugaskan <strong>' + detailIds.length + ' transaksi</strong> ke departemen <strong>' + deptText + '</strong>?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fas fa-check mr-1"></i> Ya, Tugaskan Sekarang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = $('#btn-bulk-execute');
                const origHtml = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

                $.ajax({
                    url: assignBulkUrl,
                    type: 'POST',
                    data: {
                        detail_ids: detailIds,
                        departemen_id: deptId
                    },
                    success: function (response) {
                        btn.prop('disabled', false).html(origHtml);
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                confirmButtonColor: '#4f46e5'
                            });

                            // Highlight rows
                            if (response.assigned_ids) {
                                response.assigned_ids.forEach(function (id) {
                                    const row = $('#row-detail-' + id);
                                    row.addClass('row-assigned');
                                    row.find('.action-cell').html(
                                        '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">' +
                                        '<i class="fas fa-check-circle mr-1"></i> ' + response.departemen_nama +
                                        '</span>'
                                    );
                                    row.find('.row-checkbox').prop('checked', false).prop('disabled', true);
                                });
                            }

                            syncBulkSelection();
                            updateKpis(response.kpis);

                            // Fade out assigned rows after brief pause
                            setTimeout(function () {
                                if (response.assigned_ids) {
                                    response.assigned_ids.forEach(function (id) {
                                        $('#row-detail-' + id).fadeOut(300, function () {
                                            $(this).remove();
                                        });
                                    });
                                }
                            }, 1000);
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html(origHtml);
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memproses bulk assign.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Bulk Assign',
                            text: msg,
                            confirmButtonColor: '#4f46e5'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush

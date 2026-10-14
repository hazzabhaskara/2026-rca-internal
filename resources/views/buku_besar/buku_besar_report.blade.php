@extends('layouts.app')

@section('content')
    <style>
        @media print {
            nav, header, .no-print, .action-bar, #fileModify, #quickViewModal, .quick-jump-bar, .accordion-toggle-btn, .quick-view-btn, #bulkDeleteToolbar, .delete-voucher-btn {
                display: none !important;
            }
            body {
                background: #fff !important;
                font-size: 10pt !important;
            }
            .account-card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                margin-bottom: 20px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .account-card-body {
                display: block !important;
            }
            table {
                width: 100% !important;
                font-size: 8.5pt !important;
            }
            th, td {
                padding: 4px 6px !important;
            }
        }
        .rotate-180 {
            transform: rotate(180deg);
        }
        .contra-hidden {
            display: none !important;
        }
        /* Column resize handle */
        th.resizable-col { position: relative; }
        .col-resize-handle {
            position: absolute; right: 0; top: 0; bottom: 0;
            width: 5px; cursor: col-resize; z-index: 5;
            background: transparent;
        }
        .col-resize-handle:hover, .col-resize-handle.dragging { background: #6366f1; }
        /* Inline editable cells */
        .gl-editable {
            display: inline-flex; align-items: center; gap: 3px;
            cursor: pointer; border-radius: 4px; padding: 1px 4px;
            transition: background .15s, outline .15s;
        }
        .gl-editable:hover { background: #fef9c3; outline: 1.5px dashed #fbbf24; }
        .gl-editable .gl-edit-icon { opacity: 0; font-size: 9px; color: #f59e0b; transition: opacity .15s; }
        .gl-editable:hover .gl-edit-icon { opacity: 1; }
        .gl-editable.dept-editable:hover { background: #ede9fe; outline-color: #8b5cf6; }
        .gl-editable.dept-editable .gl-edit-icon { color: #7c3aed; }
        /* saving overlay */
        tr.gl-saving { opacity: .5; pointer-events: none; }
    </style>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                {{-- Action Bar --}}
                <div class="action-bar mb-4 pb-3 border-b border-gray-200">
                    <ul class="flex flex-wrap items-center justify-between gap-3 text-sm font-medium text-gray-600">
                        <div class="flex items-center space-x-4">
                            <li>
                                <a id="exportExcelBtn" href="{{ route('buku_besar.export', array_merge(request()->query(), [
                                    'start_date' => $start_date,
                                    'end_date' => $end_date,
                                    'departemen_id' => $selected_departemen_id ?? request('departemen_id'),
                                    'show_lawan' => ($show_lawan ?? false) ? 1 : 0,
                                    'format' => 'excel',
                                ])) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-300 rounded-lg hover:bg-emerald-100 transition shadow-sm">
                                    <i class="far fa-file-excel text-emerald-600 mr-1.5"></i> Export to Excel
                                </a>
                            </li>
                            <li>
                                <a id="exportPdfBtn" href="{{ route('buku_besar.export', array_merge(request()->query(), [
                                    'start_date' => $start_date,
                                    'end_date' => $end_date,
                                    'departemen_id' => $selected_departemen_id ?? request('departemen_id'),
                                    'show_lawan' => ($show_lawan ?? false) ? 1 : 0,
                                    'format' => 'pdf',
                                ])) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-300 rounded-lg hover:bg-rose-100 transition shadow-sm">
                                    <i class="far fa-file-pdf text-rose-600 mr-1.5"></i> Export to PDF
                                </a>
                            </li>
                            <li>
                                <button type="button" onclick="window.print()" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-50 border border-gray-300 rounded-lg hover:bg-gray-100 transition shadow-sm">
                                    <i class="fas fa-print text-gray-500 mr-1.5"></i> Cetak (Print)
                                </button>
                            </li>
                            <li>
                                <button type="button" onclick="document.getElementById('fileModify').classList.toggle('hidden')" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-300 rounded-lg hover:bg-blue-100 transition shadow-sm">
                                    <i class="fas fa-sliders-h text-blue-500 mr-1.5"></i> Modify Filter
                                </button>
                            </li>
                            <li>
                                <button type="button" onclick="toggleContraColumn()" id="btnToggleContra" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg border transition shadow-sm {{ ($show_lawan ?? false) ? 'text-indigo-700 bg-indigo-50 border-indigo-300 hover:bg-indigo-100' : 'text-gray-700 bg-gray-50 border-gray-300 hover:bg-gray-100' }}" title="Tampilkan atau sembunyikan kolom Lawan Transaksi">
                                    <i class="fas fa-columns mr-1.5 {{ ($show_lawan ?? false) ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                                    <span>Lawan Transaksi: </span>
                                    <span id="labelToggleContra" class="ml-1 font-bold {{ ($show_lawan ?? false) ? 'text-indigo-700' : 'text-gray-500' }}">{{ ($show_lawan ?? false) ? 'Aktif' : 'Off' }}</span>
                                </button>
                            </li>
                        </div>

                        @php
                            $rawBackUrl = request('back_url');
                            if (is_string($rawBackUrl) && \Illuminate\Support\Str::startsWith($rawBackUrl, '/') && !\Illuminate\Support\Str::startsWith($rawBackUrl, '//') && !\Illuminate\Support\Str::startsWith($rawBackUrl, '/\\')) {
                                $rawBackUrl = url($rawBackUrl);
                            }
                            $appUrl = url('/');
                            $isValidBackUrl = $rawBackUrl && is_string($rawBackUrl) && \Illuminate\Support\Str::startsWith($rawBackUrl, $appUrl) && (
                                $rawBackUrl === $appUrl ||
                                \Illuminate\Support\Str::startsWith($rawBackUrl, $appUrl . '/') ||
                                \Illuminate\Support\Str::startsWith($rawBackUrl, $appUrl . '?')
                            );
                        @endphp
                        @if($isValidBackUrl)
                            <div>
                                <a href="{{ $rawBackUrl }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                                    <i class="fas fa-arrow-left mr-1.5 text-gray-500"></i> Kembali ke Laporan Laba Rugi
                                </a>
                            </div>
                        @endif
                    </ul>
                </div>

                {{-- Header Title & Active Filter Badges --}}
                <div class="mb-6 pb-4 border-b border-gray-200">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-extrabold text-gray-800 tracking-tight">LAPORAN BUKU BESAR (GENERAL LEDGER)</h3>
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                    <i class="far fa-calendar-alt mr-1.5 text-blue-500"></i>
                                    Periode: {{ \Carbon\Carbon::parse($start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}
                                </span>

                                @if(empty($selected_departemen_id) || $selected_departemen_id === 'all')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-300">
                                        <i class="fas fa-building mr-1.5 text-gray-500"></i>
                                        {{ $departemen_name ?? 'Semua Departemen (Konsolidasi)' }}
                                    </span>
                                @elseif($selected_departemen_id === 'unassigned' || $selected_departemen_id === 'none')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-800 border border-amber-300">
                                        <i class="fas fa-exclamation-triangle mr-1.5 text-amber-600"></i>
                                        {{ $departemen_name ?? 'Non-Departemen / Unassigned' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="fas fa-map-marker-alt mr-1.5 text-indigo-500"></i>
                                        {{ $departemen_name }}
                                    </span>
                                @endif

                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-300">
                                    <i class="fas fa-sort mr-1.5 text-slate-500"></i>
                                    Urutan: {{ ($sort_by ?? 'date') === 'transaction_number' ? 'No. Voucher' : 'Tanggal' }}
                                </span>

                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ ($hide_zero ?? true) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-orange-50 text-orange-700 border border-orange-200' }}">
                                    <i class="fas fa-filter mr-1.5"></i>
                                    {{ ($hide_zero ?? true) ? 'Saldo Nol & Tanpa Mutasi: Disembunyikan' : 'Semua Akun Ditampilkan' }}
                                </span>

                                <span id="badgeLawanTransaksi" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 {{ ($show_lawan ?? false) ? '' : 'hidden' }}">
                                    <i class="fas fa-exchange-alt mr-1.5 text-indigo-500"></i>
                                    Kolom Lawan: Ditampilkan
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KPI Summary Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 no-print">
                    <div class="bg-gradient-to-br from-white to-blue-50 border border-blue-100 rounded-xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Akun Aktif</p>
                                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $kpiSummary['total_active_accounts'] ?? $groupedByAccount->count() }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                                <i class="fas fa-book-open text-lg"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Akun dengan mutasi / saldo awal</p>
                    </div>

                    <div class="bg-gradient-to-br from-white to-emerald-50 border border-emerald-100 rounded-xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Mutasi Debet</p>
                                <p class="text-xl font-bold text-emerald-700 mt-1">Rp {{ number_format($kpiSummary['total_debit'] ?? $rows->sum('debits'), 2, ',', '.') }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                                <i class="fas fa-arrow-down text-lg"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Total mutasi debit periode</p>
                    </div>

                    <div class="bg-gradient-to-br from-white to-amber-50 border border-amber-100 rounded-xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Mutasi Kredit</p>
                                <p class="text-xl font-bold text-amber-700 mt-1">Rp {{ number_format($kpiSummary['total_credit'] ?? $rows->sum('credits'), 2, ',', '.') }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                                <i class="fas fa-arrow-up text-lg"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Total mutasi kredit periode</p>
                    </div>

                    <div class="bg-gradient-to-br from-white to-indigo-50 border border-indigo-100 rounded-xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Net Movement</p>
                                @php
                                    $netMove = $kpiSummary['net_movement'] ?? ($rows->sum('debits') - $rows->sum('credits'));
                                @endphp
                                <p class="text-xl font-bold {{ $netMove >= 0 ? 'text-indigo-700' : 'text-rose-700' }} mt-1">
                                    Rp {{ number_format($netMove, 2, ',', '.') }}
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                                <i class="fas fa-balance-scale text-lg"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Selisih mutasi debit - kredit</p>
                    </div>
                </div>

                {{-- Toolbar: Sticky Navigator, Accordion Controls, & In-Table Search --}}
                <div class="quick-jump-bar sticky top-2 z-20 bg-white/95 backdrop-blur border border-gray-200 rounded-xl p-3 shadow-sm mb-6 flex flex-wrap items-center justify-between gap-3 no-print">
                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Dropdown Lompat ke Akun --}}
                        <div class="relative min-w-[260px]">
                            <select id="quickJumpAccount" onchange="jumpToAccount(this.value)" class="w-full text-xs border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Lompat ke Akun --</option>
                                @foreach ($groupedByAccount as $kAkunKey => $aRows)
                                    @php
                                        $akModel = $aRows->account ?? optional($aRows->first())->chartOfAccount ?? $accounts->firstWhere('kode_akun', $kAkunKey);
                                        $kAkun = $akModel->kode_akun ?? $kAkunKey;
                                        $nAkun = $akModel->nama_akun ?? '-';
                                    @endphp
                                    <option value="account-card-{{ $kAkun }}">{{ $kAkun }} - {{ $nAkun }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Accordion Global Controls --}}
                        <div class="inline-flex rounded-md shadow-sm">
                            <button type="button" onclick="expandAllAccounts()" class="px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-50 focus:z-10 focus:ring-2 focus:ring-blue-500">
                                <i class="fas fa-expand-alt mr-1 text-gray-500"></i> Buka Semua
                            </button>
                            <button type="button" onclick="collapseAllAccounts()" class="px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-l-0 border-gray-300 rounded-r-lg hover:bg-gray-50 focus:z-10 focus:ring-2 focus:ring-blue-500">
                                <i class="fas fa-compress-alt mr-1 text-gray-500"></i> Tutup Semua
                            </button>
                        </div>
                    </div>

                    {{-- Live In-Table Quick Search --}}
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                        <input type="text" id="glTableSearch" placeholder="Cari voucher, lawan transaksi, dept, ket, debit, kredit..."
                            class="w-full pl-9 pr-8 py-2 text-xs border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" onclick="clearGlSearch()" id="clearGlSearchBtn" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 hidden">
                            <i class="fas fa-times-circle text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- Account Cards List --}}
                @if ($groupedByAccount->count() > 0)
                    @foreach ($groupedByAccount as $kodeAkunKey => $akunRows)
                        @php
                            $akunModel = $akunRows->account ?? optional($akunRows->first())->chartOfAccount ?? $accounts->firstWhere('kode_akun', $kodeAkunKey);
                            $kodeAkun = $akunModel->kode_akun ?? $kodeAkunKey;
                            $namaAkun = $akunModel->nama_akun ?? '-';
                            $tipeAkun = strtolower($akunModel->tipe_akun ?? '');
                            $saldoAwal = $saldoAwalPerAkun[$kodeAkun] ?? 0;
                            $saldoBerjalan = $saldoAwal;
                            $totalDebit = $akunRows->sum('debits');
                            $totalKredit = $akunRows->sum('credits');

                            if (in_array($tipeAkun, ['aset', 'beban'])) {
                                $saldoAkhir = $saldoAwal + ($totalDebit - $totalKredit);
                            } else {
                                $saldoAkhir = $saldoAwal + ($totalKredit - $totalDebit);
                            }
                            $isConsolidated = empty($selected_departemen_id) || $selected_departemen_id === 'all';
                        @endphp

                        <div class="account-card bg-white rounded-xl border border-gray-200 shadow-sm mb-5 overflow-hidden transition" id="account-card-{{ $kodeAkun }}" data-account-code="{{ $kodeAkun }}">
                            {{-- Card Header / Accordion Trigger --}}
                            <div class="bg-gray-50/80 px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3 cursor-pointer select-none hover:bg-gray-100/70 transition" onclick="toggleAccountCard('{{ $kodeAkun }}')">
                                <div class="flex items-center space-x-3">
                                    <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none transition-transform duration-200" id="chevron-{{ $kodeAkun }}">
                                        <i class="fas fa-chevron-down text-sm"></i>
                                    </button>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <h4 class="text-sm font-bold text-gray-900">{{ $kodeAkun }} - {{ $namaAkun }}</h4>
                                            @if($tipeAkun)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider bg-slate-200 text-slate-700">
                                                    {{ $tipeAkun }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-gray-500 mt-0.5">
                                            {{ $akunRows->count() }} Transaksi di periode ini
                                        </p>
                                    </div>
                                </div>

                                {{-- Quick Badges on Header --}}
                                <div class="flex flex-wrap items-center gap-2" onclick="event.stopPropagation()">
                                    {{-- Mini Breakdown Departemen Button (Khusus Mode Konsolidasi) --}}
                                    @if ($isConsolidated && !empty($accountDeptBreakdowns[$kodeAkun]))
                                        <div class="relative" x-data="{ open: false }">
                                            <button type="button" onclick="toggleDeptBreakdown('{{ $kodeAkun }}')" class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition shadow-2xs">
                                                <i class="fas fa-sitemap mr-1.5 text-indigo-500"></i>
                                                Dept Breakdown ({{ count($accountDeptBreakdowns[$kodeAkun]) }})
                                            </button>
                                            {{-- Mini Breakdown Popover / Panel --}}
                                            <div id="dept-breakdown-{{ $kodeAkun }}" class="hidden absolute right-0 mt-2 z-30 w-72 bg-white rounded-lg shadow-xl border border-gray-200 p-3 text-xs">
                                                <div class="flex items-center justify-between pb-2 mb-2 border-b border-gray-200">
                                                    <span class="font-bold text-gray-700">Kontribusi Departemen</span>
                                                    <button type="button" onclick="toggleDeptBreakdown('{{ $kodeAkun }}')" class="text-gray-400 hover:text-gray-600">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <div class="space-y-1.5 max-h-48 overflow-y-auto">
                                                    @foreach ($accountDeptBreakdowns[$kodeAkun] as $bItem)
                                                        <div class="flex items-center justify-between py-1 border-b border-gray-100 text-[11px]">
                                                            <div>
                                                                <span class="font-semibold text-indigo-700">{{ $bItem['kode'] }}</span>
                                                                <span class="text-gray-500 block text-[10px] truncate max-w-[120px]" title="{{ $bItem['deskripsi'] }}">{{ $bItem['deskripsi'] }}</span>
                                                            </div>
                                                            <div class="text-right">
                                                                <div class="text-emerald-600 font-medium">D: {{ number_format($bItem['debit'], 2, ',', '.') }}</div>
                                                                <div class="text-amber-600 font-medium">K: {{ number_format($bItem['kredit'], 2, ',', '.') }}</div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="hidden sm:flex items-center space-x-1.5 text-xs">
                                        <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-medium border border-gray-200" title="Saldo Awal">
                                            Awal: <span class="font-bold">Rp {{ number_format($saldoAwal, 2, ',', '.') }}</span>
                                        </span>
                                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-medium border border-emerald-200" title="Total Debit">
                                            D: <span class="font-bold">Rp {{ number_format($totalDebit, 2, ',', '.') }}</span>
                                        </span>
                                        <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 font-medium border border-amber-200" title="Total Kredit">
                                            K: <span class="font-bold">Rp {{ number_format($totalKredit, 2, ',', '.') }}</span>
                                        </span>
                                        <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-800 font-bold border border-blue-300" title="Saldo Akhir">
                                            Akhir: Rp {{ number_format($saldoAkhir, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Card Body / Accordion Table Container --}}
                            <div class="account-card-body overflow-x-auto" id="card-body-{{ $kodeAkun }}">
                                <table class="w-full text-left text-xs gl-resizable-table" data-account="{{ $kodeAkun }}">
                                    <thead class="bg-gray-100/90 text-gray-700 font-semibold border-b border-gray-300">
                                        <tr>
                                            @can('journal_entry.delete')
                                                <th class="w-10 px-2 py-2 border-r border-gray-200 text-center no-print resizable-col">
                                                    <input type="checkbox" class="check-all-account rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer" data-account="{{ $kodeAkun }}" title="Pilih Semua di Akun Ini">
                                                    <span class="col-resize-handle"></span>
                                                </th>
                                            @endcan
                                            <th class="w-24 px-3 py-2 border-r border-gray-200 resizable-col">Tanggal<span class="col-resize-handle"></span></th>
                                            <th class="w-36 px-3 py-2 border-r border-gray-200 text-center resizable-col">Source<span class="col-resize-handle"></span></th>
                                            @if ($isConsolidated)
                                                <th class="w-28 px-3 py-2 border-r border-gray-200 text-center resizable-col">Departemen<span class="col-resize-handle"></span></th>
                                            @endif
                                            <th class="contra-col w-48 px-3 py-2 border-r border-gray-200 text-left bg-indigo-50/60 text-indigo-900 resizable-col {{ ($show_lawan ?? false) ? '' : 'contra-hidden' }}">
                                                <div class="flex items-center justify-between">
                                                    <span>Lawan Transaksi</span>
                                                    <i class="fas fa-tag text-[10px] text-indigo-400" title="Nama Akun Lawan"></i>
                                                </div>
                                                <span class="col-resize-handle"></span>
                                            </th>
                                            <th class="px-3 py-2 border-r border-gray-200 resizable-col">Comment<span class="col-resize-handle"></span></th>
                                            <th class="w-28 px-3 py-2 border-r border-gray-200 text-right resizable-col">Debits<span class="col-resize-handle"></span></th>
                                            <th class="w-28 px-3 py-2 border-r border-gray-200 text-right resizable-col">Credits<span class="col-resize-handle"></span></th>
                                            <th class="w-32 px-3 py-2 text-right resizable-col">Balance<span class="col-resize-handle"></span></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        {{-- Baris Saldo Awal --}}
                                        <tr class="bg-gray-50/70 font-semibold text-gray-600">
                                            @can('journal_entry.delete')
                                                <td class="px-2 py-2 border-r border-gray-200 text-center no-print"></td>
                                            @endcan
                                            <td colspan="{{ ($isConsolidated ? 6 : 5) + (($show_lawan ?? false) ? 1 : 0) }}" class="saldo-awal-cell px-3 py-2 text-center italic border-r border-gray-200" data-base-colspan="{{ $isConsolidated ? 6 : 5 }}">
                                                Saldo Awal per {{ \Carbon\Carbon::parse($start_date)->format('d M Y') }}
                                            </td>
                                            <td class="px-3 py-2 text-right font-bold text-gray-800">
                                                {{ number_format($saldoBerjalan, 2, ',', '.') }}
                                            </td>
                                        </tr>

                                        {{-- Baris Transaksi --}}
                                        @forelse ($akunRows as $row)
                                            @php
                                                $debit = (float) $row->debits;
                                                $kredit = (float) $row->credits;

                                                if (in_array($tipeAkun, ['aset', 'beban'])) {
                                                    $saldoBerjalan += $debit - $kredit;
                                                } else {
                                                    $saldoBerjalan += $kredit - $debit;
                                                }

                                                $dept = $row->departemenAkun ? $row->departemenAkun->departemen : null;
                                                $commentText = ($showComment == 'transaction_comment')
                                                    ? (optional($row->journalEntry)->comment ?? '-')
                                                    : ($row->comment ?? '-');
                                                $voucherSource = $row->source ?? optional($row->journalEntry)->source ?? '-';
                                                $entryId = optional($row->journalEntry)->id;

                                                $contraSearchText = ($row->contra_account_name ?? '') . ' ' . ($row->contra_codes ?? '');
                                                $deptSearchText = $dept ? ($dept->kode . ' ' . $dept->deskripsi) : 'non-dept unassigned';
                                                $debitSearchText = $debit > 0 ? (number_format($debit, 2, ',', '.') . ' ' . number_format($debit, 0, ',', '.') . ' ' . (int)$debit) : '';
                                                $kreditSearchText = $kredit > 0 ? (number_format($kredit, 2, ',', '.') . ' ' . number_format($kredit, 0, ',', '.') . ' ' . (int)$kredit) : '';
                                                $rowSearchText = strtolower(trim(implode(' ', array_filter([
                                                    $voucherSource,
                                                    $deptSearchText,
                                                    $contraSearchText,
                                                    $commentText,
                                                    $debitSearchText,
                                                    $kreditSearchText,
                                                ]))));
                                            @endphp

                                            <tr class="gl-row hover:bg-blue-50/40 transition" id="gl-row-{{ $row->id }}" data-detail-id="{{ $row->id }}" data-search-text="{{ $rowSearchText }}">
                                                @can('journal_entry.delete')
                                                    <td class="px-2 py-1.5 border-r border-gray-200 text-center no-print">
                                                        @if ($entryId)
                                                            <input type="checkbox" class="gl-row-checkbox rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer"
                                                                data-account="{{ $kodeAkun }}"
                                                                data-source="{{ $voucherSource }}"
                                                                data-detail-id="{{ $row->id }}"
                                                                value="{{ $entryId }}">
                                                        @endif
                                                    </td>
                                                @endcan
                                                <td class="px-3 py-1.5 border-r border-gray-200 text-gray-600 whitespace-nowrap">
                                                    {{ optional($row->journalEntry)->tanggal }}
                                                </td>
                                                <td class="px-3 py-1.5 border-r border-gray-200 whitespace-nowrap">
                                                    <div class="flex items-center justify-between">
                                                        @if ($entryId)
                                                            <a href="{{ route('journal_entry.edit', $entryId) . '?back_url=' . urlencode(request()->fullUrl()) }}"
                                                               class="font-medium text-blue-600 hover:text-blue-800 hover:underline">
                                                                {{ $voucherSource }}
                                                            </a>
                                                            <div class="flex items-center">
                                                                <button type="button" onclick="openQuickView({{ $entryId }})" title="Quick Preview Voucher" class="quick-view-btn ml-1 p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition">
                                                                    <i class="far fa-eye text-xs"></i>
                                                                </button>
                                                                @can('journal_entry.delete')
                                                                    <button type="button" onclick="confirmDeleteVoucher({{ $entryId }}, '{{ addslashes($voucherSource) }}')" title="Hapus Voucher" class="delete-voucher-btn ml-0.5 p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition">
                                                                        <i class="far fa-trash-alt text-xs"></i>
                                                                    </button>
                                                                @endcan
                                                            </div>
                                                        @else
                                                            <span class="text-gray-400">-</span>
                                                        @endif
                                                    </div>
                                                </td>

                                                {{-- Kolom Departemen (Khusus Mode Konsolidasi) --}}
                                                @if ($isConsolidated)
                                                    <td class="px-3 py-1.5 border-r border-gray-200 text-center whitespace-nowrap">
                                                        @php
                                                            $deptAkunId = $row->departemen_akun_id;
                                                            $deptKode = $dept ? $dept->kode : '';
                                                            $deptNama = $dept ? $dept->deskripsi : '';
                                                        @endphp
                                                        <div class="gl-editable dept-editable inline-flex"
                                                            onclick="glOpenDeptModal(this)"
                                                            data-detail-id="{{ $row->id }}"
                                                            data-dept-akun-id="{{ $deptAkunId ?? '' }}"
                                                            data-dept-kode="{{ $deptKode }}"
                                                            data-dept-nama="{{ $deptNama }}"
                                                            data-akun-kode="{{ $kodeAkun }}"
                                                            title="Klik untuk ubah departemen">
                                                            @if ($dept)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">{{ $deptKode }}</span>
                                                            @else
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-500 border border-gray-200">Non-Dept</span>
                                                            @endif
                                                            <i class="fas fa-pen gl-edit-icon"></i>
                                                        </div>
                                                    </td>
                                                @endif

                                                {{-- Kolom Lawan Transaksi --}}
                                                <td class="contra-col px-3 py-1.5 border-r border-gray-200 text-gray-800 {{ ($show_lawan ?? false) ? '' : 'contra-hidden' }}">
                                                    @if (($row->contra_count ?? 0) === 0 || empty($row->contra_first) || $row->contra_first === '-')
                                                        <span class="text-gray-400 italic text-[11px]">-</span>
                                                    @elseif (($row->contra_count ?? 0) === 1)
                                                        {{-- Single contra: editable inline --}}
                                                        <div class="gl-editable inline-flex max-w-[160px]"
                                                            onclick="glOpenContraModal(this)"
                                                            data-detail-id="{{ $row->id }}"
                                                            data-contra-nama="{{ $row->contra_first }}"
                                                            title="Klik untuk ubah lawan transaksi">
                                                            <span class="font-medium text-gray-800 truncate">{{ $row->contra_first }}</span>
                                                            <i class="fas fa-pen gl-edit-icon shrink-0"></i>
                                                        </div>
                                                    @else
                                                        {{-- Multi-contra: tampilkan tapi lock (tidak bisa inline edit) --}}
                                                        <div class="flex items-center gap-1 max-w-[170px]" title="Akun Lawan: {{ $row->contra_account_name }}">
                                                            <span class="font-medium text-gray-800 truncate">{{ $row->contra_first }}</span>
                                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 whitespace-nowrap cursor-help shrink-0" title="{{ $row->contra_account_name }}">
                                                                +{{ ($row->contra_count - 1) }}
                                                            </span>
                                                            <i class="fas fa-lock text-[9px] text-gray-400 shrink-0" title="Multi-contra: edit melalui halaman jurnal"></i>
                                                        </div>
                                                    @endif
                                                </td>

                                                <td class="px-3 py-1.5 border-r border-gray-200 text-gray-700 truncate max-w-xs" title="{{ $commentText }}">
                                                    {{ $commentText }}
                                                </td>

                                                <td class="px-3 py-1.5 border-r border-gray-200 text-right whitespace-nowrap">
                                                    @if ($entryId && $debit > 0)
                                                        <a href="{{ route('journal_entry.show', $entryId) . '?back_url=' . urlencode(request()->fullUrl()) }}" class="text-emerald-700 font-medium hover:underline">
                                                            {{ number_format($debit, 2, ',', '.') }}
                                                        </a>
                                                    @elseif ($debit > 0)
                                                        <span class="text-emerald-700 font-medium">{{ number_format($debit, 2, ',', '.') }}</span>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>

                                                <td class="px-3 py-1.5 border-r border-gray-200 text-right whitespace-nowrap">
                                                    @if ($entryId && $kredit > 0)
                                                        <a href="{{ route('journal_entry.show', $entryId) . '?back_url=' . urlencode(request()->fullUrl()) }}" class="text-amber-700 font-medium hover:underline">
                                                            {{ number_format($kredit, 2, ',', '.') }}
                                                        </a>
                                                    @elseif ($kredit > 0)
                                                        <span class="text-amber-700 font-medium">{{ number_format($kredit, 2, ',', '.') }}</span>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>

                                                <td class="px-3 py-1.5 text-right font-medium text-gray-800 whitespace-nowrap">
                                                    {{ number_format($saldoBerjalan, 2, ',', '.') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ ($isConsolidated ? 7 : 6) + (($show_lawan ?? false) ? 1 : 0) + (auth()->check() && auth()->user()->can('journal_entry.delete') ? 1 : 0) }}" class="empty-row-cell px-4 py-4 text-center text-gray-500 italic bg-gray-50/50" data-base-colspan="{{ ($isConsolidated ? 7 : 6) + (auth()->check() && auth()->user()->can('journal_entry.delete') ? 1 : 0) }}">
                                                    <i class="fas fa-info-circle mr-1.5 text-blue-500"></i>
                                                    Tidak ada mutasi transaksi pada periode ini. Saldo akhir tetap sama dengan saldo awal.
                                                </td>
                                            </tr>
                                        @endforelse

                                        {{-- Baris Total Mutasi --}}
                                        <tr class="bg-gray-100 font-bold text-gray-800 border-t-2 border-gray-300">
                                            @can('journal_entry.delete')
                                                <td class="px-2 py-2 border-r border-gray-200 text-center no-print"></td>
                                            @endcan
                                            <td colspan="{{ ($isConsolidated ? 4 : 3) + (($show_lawan ?? false) ? 1 : 0) }}" class="total-mutasi-cell px-3 py-2 text-right border-r border-gray-200 uppercase tracking-wider text-[11px]" data-base-colspan="{{ $isConsolidated ? 4 : 3 }}">
                                                Total Mutasi {{ $kodeAkun }}:
                                            </td>
                                            <td class="px-3 py-2 text-right text-emerald-700 border-r border-gray-200 whitespace-nowrap">
                                                {{ number_format($totalDebit, 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 text-right text-amber-700 border-r border-gray-200 whitespace-nowrap">
                                                {{ number_format($totalKredit, 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 text-right font-extrabold text-blue-900 whitespace-nowrap">
                                                {{ number_format($saldoAkhir, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto text-gray-400 mb-4">
                            <i class="fas fa-folder-open text-2xl"></i>
                        </div>
                        <h4 class="text-base font-semibold text-gray-700">Tidak ada data untuk ditampilkan</h4>
                        <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">
                            Tidak ditemukan transaksi atau saldo akun pada kriteria filter yang dipilih. Coba non-aktifkan "Sembunyikan Akun Saldo Nol" atau ubah rentang tanggal filter.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Quick Preview Voucher Modal --}}
    <div id="quickViewModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-fade-in-down">
            {{-- Modal Header --}}
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-file-invoice text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <span>Voucher:</span>
                            <span id="qvSource" class="text-blue-600">---</span>
                            <span id="qvBalanceBadge" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                Balanced
                            </span>
                        </h3>
                        <p class="text-xs text-gray-500">Tanggal: <span id="qvTanggal" class="font-medium text-gray-700">---</span></p>
                    </div>
                </div>
                <button type="button" onclick="closeQuickView()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 overflow-y-auto space-y-4 flex-1">
                {{-- Voucher Comment --}}
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Keterangan / Memo Voucher</p>
                    <p id="qvComment" class="text-xs font-medium text-gray-800 mt-0.5">---</p>
                </div>

                {{-- Voucher Details Table --}}
                <div class="border rounded-lg overflow-hidden border-gray-200">
                    <table class="min-w-full text-xs text-left divide-y divide-gray-200">
                        <thead class="bg-gray-100 font-semibold text-gray-700">
                            <tr>
                                <th class="px-3 py-2">Kode Akun</th>
                                <th class="px-3 py-2">Nama Akun</th>
                                <th class="px-3 py-2">Departemen</th>
                                <th class="px-3 py-2">Keterangan Baris</th>
                                <th class="px-3 py-2 text-right">Debet</th>
                                <th class="px-3 py-2 text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="qvDetailsTableBody" class="divide-y divide-gray-200 bg-white">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                        <tfoot class="bg-gray-50 font-bold border-t-2 border-gray-300 text-gray-800">
                            <tr>
                                <td colspan="4" class="px-3 py-2 text-right uppercase text-[11px]">Total Transaksi Voucher:</td>
                                <td id="qvTotalDebits" class="px-3 py-2 text-right text-emerald-700">0.00</td>
                                <td id="qvTotalCredits" class="px-3 py-2 text-right text-amber-700">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <a id="qvEditLink" href="#" target="_blank" rel="opener" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-lg shadow transition">
                        <i class="fas fa-external-link-alt mr-2"></i> Buka & Edit di Tab Baru
                    </a>
                    <a id="qvEditDirectLink" href="#" class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-lg border border-gray-300 transition">
                        <i class="fas fa-edit mr-1.5 text-gray-500"></i> Edit di Tab Ini
                    </a>
                    @can('journal_entry.delete')
                        <button type="button" id="qvDeleteBtn" onclick="deleteCurrentQuickViewVoucher()" class="inline-flex items-center px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 font-semibold text-xs rounded-lg border border-red-300 transition">
                            <i class="fas fa-trash-alt mr-1.5 text-red-600"></i> Hapus Voucher
                        </button>
                    @endcan
                </div>
                <button type="button" onclick="closeQuickView()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-semibold text-xs rounded-lg hover:bg-gray-50 transition shadow-2xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- Sticky Floating Bulk Action Bar --}}
    @can('journal_entry.delete')
        <div id="bulkDeleteToolbar" class="fixed bottom-6 inset-x-0 z-40 flex justify-center pointer-events-none transition-all duration-300 transform translate-y-24 opacity-0 no-print">
            <div class="bg-gray-900/95 backdrop-blur-md text-white px-5 py-3 rounded-2xl shadow-2xl border border-gray-700/60 flex items-center gap-4 pointer-events-auto max-w-xl w-full mx-4 justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white shadow-inner" id="bulkSelectedCount">0</span>
                    <div>
                        <p class="text-xs font-semibold text-gray-100"><span id="bulkTitleCount">0</span> Transaksi Terpilih</p>
                        <p class="text-[11px] text-gray-400">Pilih tindakan serentak:</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                    <button type="button" onclick="glOpenBulkDeptModal()" class="inline-flex items-center px-3 py-1.5 bg-violet-600 hover:bg-violet-700 active:bg-violet-800 text-white text-xs font-bold rounded-lg shadow transition">
                        <i class="fas fa-building mr-1.5"></i> Departemen (<span class="bulkBtnCount">0</span>)
                    </button>
                    <button type="button" onclick="glOpenBulkContraModal()" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-bold rounded-lg shadow transition">
                        <i class="fas fa-tag mr-1.5"></i> Lawan (<span class="bulkBtnCount">0</span>)
                    </button>
                    <button type="button" id="btnBulkDeleteExecute" onclick="executeBulkDelete()" class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white text-xs font-bold rounded-lg shadow transition">
                        <i class="fas fa-trash-alt mr-1.5"></i> Hapus (<span class="bulkVoucherCount">0</span>)
                    </button>
                    <button type="button" onclick="clearBulkSelection()" class="px-2.5 py-1.5 text-xs text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition font-medium">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endcan

    {{-- Modify Filter Modal --}}
    <div id="fileModify" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-5xl max-h-[90vh] overflow-y-auto animate-fade-in-down">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="fas fa-sliders-h mr-2 text-blue-600"></i> Modify Filter Buku Besar
                </h3>
                <button type="button" onclick="document.getElementById('fileModify').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form method="GET" action="{{ route('buku_besar.buku_besar_report') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                {{-- Periode Buku --}}
                <div>
                    <label for="periode" class="block text-xs font-semibold text-gray-700 mb-1">Periode Buku</label>
                    <select name="periode_buku" id="periode" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">---Pilih Periode---</option>
                        @foreach ($tahun_buku as $item)
                            <option value="{{ $item->id }}" data-tahun="{{ trim($item->tahun) }}"
                                {{ request('periode_buku') == $item->id ? 'selected' : '' }}>
                                {{ $item->tahun }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal Awal --}}
                <div>
                    <label for="modal_start_date" class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Awal</label>
                    <input type="date" id="modal_start_date" name="start_date"
                        value="{{ request('start_date', \Carbon\Carbon::parse($start_date)->format('Y-m-d')) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                {{-- Tanggal Akhir --}}
                <div>
                    <label for="modal_end_date" class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Akhir</label>
                    <input type="date" id="modal_end_date" name="end_date"
                        value="{{ request('end_date', \Carbon\Carbon::parse($end_date)->format('Y-m-d')) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                {{-- Filter Departemen --}}
                <div>
                    <label for="modal_departemen_id" class="block text-xs font-semibold text-gray-700 mb-1">Departemen</label>
                    <select name="departemen_id" id="modal_departemen_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" {{ empty($selected_departemen_id) || $selected_departemen_id === 'all' ? 'selected' : '' }}>Semua Departemen (Konsolidasi)</option>
                        <option value="unassigned" {{ $selected_departemen_id === 'unassigned' || $selected_departemen_id === 'none' ? 'selected' : '' }}>Non-Departemen / Unassigned</option>
                        @foreach ($departemen as $dep)
                            <option value="{{ $dep->id }}" {{ (string)($selected_departemen_id ?? request('departemen_id')) === (string)$dep->id ? 'selected' : '' }}>
                                {{ $dep->kode }} - {{ $dep->deskripsi }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Quick Date Presets in Modal --}}
                <div class="col-span-full">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Preset Tanggal Cepat</label>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="setModalDatePreset('today')" class="px-2.5 py-1 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition">Hari Ini</button>
                        <button type="button" onclick="setModalDatePreset('this_month')" class="px-2.5 py-1 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition">Bulan Ini</button>
                        <button type="button" onclick="setModalDatePreset('last_month')" class="px-2.5 py-1 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition">Bulan Lalu</button>
                        <button type="button" onclick="setModalDatePreset('this_quarter')" class="px-2.5 py-1 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition">Triwulan Ini</button>
                        <button type="button" onclick="setModalDatePreset('ytd')" class="px-2.5 py-1 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition">Tahun Berjalan (YTD)</button>
                        <button type="button" onclick="setModalDatePreset('last_year')" class="px-2.5 py-1 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition">Tahun Lalu</button>
                    </div>
                </div>

                @if(request()->filled('back_url'))
                    <input type="hidden" name="back_url" value="{{ request('back_url') }}">
                @endif

                {{-- Select Account --}}
                <div class="col-span-full">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Akun Tertentu (Opsional)</label>
                    <input type="hidden" name="selected_accounts" id="modal_selected_accounts" value="{{ request('selected_accounts') }}">

                    <input type="text" id="modal_search-account" placeholder="Cari akun..." class="border border-gray-300 p-2 text-xs rounded mb-2 w-full bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500" />

                    <div class="border rounded-lg shadow-2xs max-h-48 overflow-y-auto">
                        <table class="min-w-full text-xs text-left text-gray-700" id="modal_account-table">
                            <thead class="bg-gray-100 sticky top-0 font-semibold">
                                <tr>
                                    <th class="px-3 py-1.5 w-10">
                                        <input type="checkbox" id="modal_select-all" class="rounded text-blue-600">
                                    </th>
                                    <th class="px-3 py-1.5">Account</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $selectedAccountCodesList = array_values(array_filter(array_map(function($item) {
                                        $p = explode(' - ', $item);
                                        return trim($p[0] ?? '');
                                    }, explode(',', request('selected_accounts', '')))));
                                @endphp
                                @foreach ($accounts as $akun)
                                    <tr class="hover:bg-gray-50 border-b border-gray-100" data-level="{{ $akun->level_akun }}" data-tipe="{{ strtolower($akun->tipe_akun) }}">
                                        <td class="px-3 py-1">
                                            <input type="checkbox" class="modal-account-checkbox rounded text-blue-600"
                                                value="{{ $akun->kode_akun }}"
                                                {{ in_array($akun->kode_akun, $selectedAccountCodesList) ? 'checked' : '' }}>
                                        </td>
                                        <td class="px-3 py-1">
                                            <span class="font-semibold text-gray-800">{{ $akun->kode_akun }}</span> - {{ $akun->nama_akun }}
                                            <span class="text-[10px] text-gray-400">({{ $akun->tipe_akun }})</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Sort By --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Sort By (Pengurutan)</label>
                    <div class="space-y-1">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="sort_by" value="date" class="text-blue-600 focus:ring-blue-500"
                                {{ ($sort_by ?? 'date') == 'date' ? 'checked' : '' }}>
                            <span class="ml-2 text-xs text-gray-700">Tanggal Transaksi</span>
                        </label>
                        <br>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="sort_by" value="transaction_number" class="text-blue-600 focus:ring-blue-500"
                                {{ ($sort_by ?? '') == 'transaction_number' ? 'checked' : '' }}>
                            <span class="ml-2 text-xs text-gray-700">Nomor Voucher</span>
                        </label>
                    </div>
                </div>

                {{-- Journal Entry Options --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Keterangan Jurnal</label>
                    <div class="space-y-1">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="show_comment" value="line_comment" class="text-blue-600 focus:ring-blue-500"
                                {{ ($showComment ?? 'line_comment') == 'line_comment' ? 'checked' : '' }}>
                            <span class="ml-2 text-xs text-gray-700">Line Comment (Detail)</span>
                        </label>
                        <br>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="show_comment" value="transaction_comment" class="text-blue-600 focus:ring-blue-500"
                                {{ ($showComment ?? '') == 'transaction_comment' ? 'checked' : '' }}>
                            <span class="ml-2 text-xs text-gray-700">Transaction Comment (Header)</span>
                        </label>
                    </div>
                </div>

                {{-- Zero-balance Toggle Filter --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Integritas Akun</label>
                    <div class="mt-1">
                        <input type="hidden" name="hide_zero" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="hide_zero" value="1"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                {{ ($hide_zero ?? true) ? 'checked' : '' }}>
                            <span class="ml-2 text-xs text-gray-700 font-medium">Sembunyikan Akun Saldo Nol & Tanpa Mutasi</span>
                        </label>
                        <p class="text-[11px] text-gray-500 mt-0.5">Jika tidak dicentang, seluruh akun tetap ditampilkan walau saldo Rp 0.</p>
                    </div>
                </div>

                {{-- Kolom Lawan Transaksi Filter --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kolom Lawan Transaksi</label>
                    <div class="mt-1">
                        <input type="hidden" name="show_lawan" value="0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="show_lawan" id="modal_show_lawan" value="1"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                {{ ($show_lawan ?? false) ? 'checked' : '' }}>
                            <span class="ml-2 text-xs text-gray-700 font-medium">Tampilkan Kolom Lawan Transaksi (Nama Akun)</span>
                        </label>
                        <p class="text-[11px] text-gray-500 mt-0.5">Menampilkan nama akun penyeimbang (offset) dari transaksi.</p>
                    </div>
                </div>

                {{-- Tombol Filter --}}
                <div class="col-span-full flex items-center justify-between border-t border-gray-200 pt-4 mt-2">
                    <div class="flex items-center gap-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold text-xs rounded-lg shadow hover:bg-blue-700 transition">
                            <i class="fas fa-filter mr-1.5"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('buku_besar.buku_besar_report') }}" class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 font-medium text-xs rounded-lg hover:bg-gray-200 transition">
                            <i class="fas fa-undo mr-1.5"></i> Reset
                        </a>
                    </div>
                    <button type="button" onclick="document.getElementById('fileModify').classList.add('hidden')" class="text-xs text-gray-500 hover:text-gray-700">
                        Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
    {{-- Modal: Inline Edit Lawan Transaksi --}}
    <div id="glContraModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md animate-fade-in-down">
            <div class="flex items-center justify-between mb-4">
                <h3 id="glContraModalTitle" class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-pencil-alt text-amber-500"></i> Ubah Lawan Transaksi
                </h3>
                <button type="button" onclick="glCloseContraModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            {{-- Context info --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 mb-4 text-xs">
                <div id="glContraCurrentLabel" class="text-gray-400 text-[10px] uppercase font-bold mb-0.5">Lawan transaksi saat ini</div>
                <div id="glContraCurrentName" class="font-semibold text-gray-700">—</div>
            </div>
            {{-- Search --}}
            <div class="mb-3">
                <div class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider mb-1.5">Pilih Akun Lawan Baru</div>
                <input type="text" id="glContraSearch" placeholder="🔍 Cari kode atau nama akun..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400"
                    oninput="glFilterCoa(this.value)">
            </div>
            <div id="glCoaList" class="max-h-52 overflow-y-auto border border-gray-200 rounded-lg mb-4 text-xs">
                <div class="px-4 py-3 text-center text-gray-400"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat akun...</div>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="glCloseContraModal()" class="px-3 py-1.5 text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">Batal</button>
                <button type="button" id="glContraSaveBtn" onclick="glConfirmContra()" class="px-4 py-1.5 text-xs text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg font-bold">Simpan</button>
            </div>
        </div>
    </div>

    {{-- Modal: Inline Edit Departemen --}}
    <div id="glDeptModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm animate-fade-in-down">
            <div class="flex items-center justify-between mb-4">
                <h3 id="glDeptModalTitle" class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-pencil-alt text-violet-500"></i> Ubah Departemen
                </h3>
                <button type="button" onclick="glCloseDeptModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 mb-4 text-xs">
                <div id="glDeptCurrentLabel" class="text-gray-400 text-[10px] uppercase font-bold mb-0.5">Departemen saat ini</div>
                <div id="glDeptCurrentName" class="font-semibold text-gray-700">—</div>
            </div>
            <div class="text-[10px] font-bold text-violet-600 uppercase tracking-wider mb-1.5">Pilih Departemen Baru</div>
            <div id="glDeptList" class="max-h-52 overflow-y-auto border border-gray-200 rounded-lg mb-4 text-xs">
                {{-- Populated by JS from PHP-injected departemen data --}}
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="glCloseDeptModal()" class="px-3 py-1.5 text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">Batal</button>
                <button type="button" id="glDeptSaveBtn" onclick="glConfirmDept()" class="px-4 py-1.5 text-xs text-white bg-violet-600 hover:bg-violet-700 rounded-lg font-bold">Simpan</button>
            </div>
        </div>
    </div>

    {{-- Confirm Modal (shared for both inline edits) --}}
    <div id="glConfirmModal" class="fixed inset-0 z-[60] hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-xs animate-fade-in-down">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center text-amber-500 text-sm"><i class="fas fa-exclamation-triangle"></i></div>
                <h3 class="text-sm font-bold text-gray-800">Konfirmasi Perubahan</h3>
            </div>
            <div id="glConfirmContent" class="text-xs text-gray-600 bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 mb-3 leading-relaxed"></div>
            <p class="text-[10px] text-gray-400 mb-4">Perubahan langsung tersimpan ke database dan tidak bisa di-undo dari sini.</p>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="glCancelConfirm()" class="px-3 py-1.5 text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">Batal</button>
                <button type="button" id="glConfirmExecBtn" class="px-4 py-1.5 text-xs text-white bg-rose-600 hover:bg-rose-700 rounded-lg font-bold">Ya, Simpan</button>
            </div>
        </div>
    </div>

    {{-- Client-side Interactive Scripts --}}
    <script>
        // Instant Toggle Kolom Lawan Transaksi
        let isContraVisible = {{ ($show_lawan ?? false) ? 'true' : 'false' }};

        function toggleContraColumn() {
            isContraVisible = !isContraVisible;

            // Toggle element kolom
            const cols = document.querySelectorAll('.contra-col');
            cols.forEach(el => {
                if (isContraVisible) {
                    el.classList.remove('contra-hidden');
                } else {
                    el.classList.add('contra-hidden');
                }
            });

            // Update Saldo Awal colspans
            document.querySelectorAll('.saldo-awal-cell').forEach(cell => {
                const base = parseInt(cell.getAttribute('data-base-colspan') || 5, 10);
                cell.colSpan = base + (isContraVisible ? 1 : 0);
            });

            // Update Empty row colspans
            document.querySelectorAll('.empty-row-cell').forEach(cell => {
                const base = parseInt(cell.getAttribute('data-base-colspan') || 6, 10);
                cell.colSpan = base + (isContraVisible ? 1 : 0);
            });

            // Update Total Mutasi colspans
            document.querySelectorAll('.total-mutasi-cell').forEach(cell => {
                const base = parseInt(cell.getAttribute('data-base-colspan') || 4, 10);
                cell.colSpan = base + (isContraVisible ? 1 : 0);
            });

            // Update Action Bar Button & Label
            const btn = document.getElementById('btnToggleContra');
            const label = document.getElementById('labelToggleContra');
            const icon = btn ? btn.querySelector('i') : null;
            const badge = document.getElementById('badgeLawanTransaksi');

            if (btn && label) {
                if (isContraVisible) {
                    btn.className = "inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg border transition shadow-sm text-indigo-700 bg-indigo-50 border-indigo-300 hover:bg-indigo-100";
                    if (icon) icon.className = "fas fa-columns mr-1.5 text-indigo-600";
                    label.className = "ml-1 font-bold text-indigo-700";
                    label.innerText = "Aktif";
                } else {
                    btn.className = "inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg border transition shadow-sm text-gray-700 bg-gray-50 border-gray-300 hover:bg-gray-100";
                    if (icon) icon.className = "fas fa-columns mr-1.5 text-gray-400";
                    label.className = "ml-1 font-bold text-gray-500";
                    label.innerText = "Off";
                }
            }

            if (badge) {
                if (isContraVisible) {
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }

            // Sync with Modify Filter modal checkbox
            const modalCheck = document.getElementById('modal_show_lawan');
            if (modalCheck) modalCheck.checked = isContraVisible;

            // Sync Export links
            ['exportExcelBtn', 'exportPdfBtn'].forEach(id => {
                const a = document.getElementById(id);
                if (a && a.href) {
                    try {
                        const url = new URL(a.href, window.location.origin);
                        url.searchParams.set('show_lawan', isContraVisible ? '1' : '0');
                        a.href = url.pathname + url.search;
                    } catch (e) {}
                }
            });

            // Update browser URL without page reload
            try {
                const currentUrl = new URL(window.location.href);
                if (isContraVisible) {
                    currentUrl.searchParams.set('show_lawan', '1');
                } else {
                    currentUrl.searchParams.delete('show_lawan');
                }
                window.history.replaceState({}, '', currentUrl.pathname + currentUrl.search);
            } catch (e) {}
        }

        // Toggle Accordion per Account Card
        function toggleAccountCard(code) {
            const body = document.getElementById('card-body-' + code);
            const chevron = document.getElementById('chevron-' + code);
            if (!body) return;

            if (body.classList.contains('hidden')) {
                body.classList.remove('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
            } else {
                body.classList.add('hidden');
                if (chevron) chevron.classList.add('rotate-180');
            }
        }

        // Global Accordion Expand All
        function expandAllAccounts() {
            document.querySelectorAll('.account-card-body').forEach(el => el.classList.remove('hidden'));
            document.querySelectorAll('[id^="chevron-"]').forEach(el => el.classList.remove('rotate-180'));
        }

        // Global Accordion Collapse All
        function collapseAllAccounts() {
            document.querySelectorAll('.account-card-body').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('[id^="chevron-"]').forEach(el => el.classList.add('rotate-180'));
        }

        // Jump to Account
        function jumpToAccount(cardId) {
            if (!cardId) return;
            const targetEl = document.getElementById(cardId);
            if (targetEl) {
                // Ensure target card is expanded
                const code = targetEl.getAttribute('data-account-code');
                const body = document.getElementById('card-body-' + code);
                const chevron = document.getElementById('chevron-' + code);
                if (body && body.classList.contains('hidden')) {
                    body.classList.remove('hidden');
                    if (chevron) chevron.classList.remove('rotate-180');
                }

                // Smooth scroll to target
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                targetEl.classList.add('ring-2', 'ring-blue-500');
                setTimeout(() => {
                    targetEl.classList.remove('ring-2', 'ring-blue-500');
                }, 2000);
            }
        }

        // Toggle Department Breakdown Popover
        function toggleDeptBreakdown(code) {
            const el = document.getElementById('dept-breakdown-' + code);
            if (el) el.classList.toggle('hidden');
        }

        // Live In-Table Quick Search
        const searchInput = document.getElementById('glTableSearch');
        const clearBtn = document.getElementById('clearGlSearchBtn');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const rawKeyword = this.value.trim().toLowerCase();
                if (rawKeyword.length > 0) {
                    clearBtn.classList.remove('hidden');
                } else {
                    clearBtn.classList.add('hidden');
                }

                // Normalisasi angka (hilangkan titik/koma/spasi untuk pencarian nominal fleksibel)
                const cleanNum = rawKeyword.replace(/[.,\s]/g, '');
                const isPotentialNumber = /^\d+$/.test(cleanNum) && cleanNum.length >= 2;

                const cards = document.querySelectorAll('.account-card');
                cards.forEach(card => {
                    const rows = card.querySelectorAll('tbody tr.gl-row');
                    let cardHasMatch = false;

                    rows.forEach(row => {
                        const rowText = (row.getAttribute('data-search-text') || row.innerText).toLowerCase();
                        const matches = !rawKeyword ||
                            rowText.includes(rawKeyword) ||
                            (isPotentialNumber && rowText.includes(cleanNum));

                        if (matches) {
                            row.style.display = '';
                            cardHasMatch = true;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    // If searching and card has matching rows, ensure card is visible & expanded
                    if (rawKeyword) {
                        const body = card.querySelector('.account-card-body');
                        const code = card.getAttribute('data-account-code');
                        const chevron = document.getElementById('chevron-' + code);
                        if (cardHasMatch) {
                            card.style.display = '';
                            if (body) body.classList.remove('hidden');
                            if (chevron) chevron.classList.remove('rotate-180');
                        } else {
                            card.style.display = 'none';
                        }
                    } else {
                        card.style.display = '';
                    }
                });

                // Sinkronkan status checkbox dan toolbar sesuai baris yang terlihat
                syncBulkSelection();
            });
        }

        function clearGlSearch() {
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.focus();
            }
        }

        // Quick Preview Voucher Modal
        let currentQvJournalId = null;
        let currentQvSource = '';

        function openQuickView(journalId) {
            const modal = document.getElementById('quickViewModal');
            const tbody = document.getElementById('qvDetailsTableBody');
            if (!modal) return;

            currentQvJournalId = journalId;
            currentQvSource = '';

            // Set link edit secara sinkron dan langsung dengan back_url ke halaman Buku Besar saat ini
            let currentUrl = window.location.href;
            const hashIdx = currentUrl.indexOf('#');
            if (hashIdx !== -1) {
                currentUrl = currentUrl.substring(0, hashIdx);
            }
            while (currentUrl.indexOf('&amp;') !== -1) {
                currentUrl = currentUrl.replace(/&amp;/g, '&');
            }

            const editDirectUrl = `{{ url('journal_entry') }}/${journalId}/edit?back_url=${encodeURIComponent(currentUrl)}`;
            const editTabUrl = `{{ url('journal_entry') }}/${journalId}/edit?back_url=${encodeURIComponent(currentUrl)}&from_new_tab=1`;

            const directLink = document.getElementById('qvEditDirectLink');
            if (directLink) directLink.href = editDirectUrl;

            const tabLink = document.getElementById('qvEditLink');
            if (tabLink) {
                tabLink.href = editTabUrl;
                tabLink.onclick = function(e) {
                    e.preventDefault();
                    window.open(editTabUrl, '_blank');
                    return false;
                };
            }

            // Reset modal data to loading state
            document.getElementById('qvSource').innerText = 'Memuat...';
            document.getElementById('qvTanggal').innerText = '...';
            document.getElementById('qvComment').innerText = 'Mengambil data rincian jurnal...';
            document.getElementById('qvTotalDebits').innerText = '0.00';
            document.getElementById('qvTotalCredits').innerText = '0.00';
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i> Mengambil rincian transaksi...</td></tr>';

            modal.classList.remove('hidden');

            fetch(`{{ url('journal_entry') }}/${journalId}/quick-view?back_url=${encodeURIComponent(currentUrl)}`)
                .then(res => {
                    if (!res.ok) throw new Error('Gagal memuat rincian voucher');
                    return res.json();
                })
                .then(data => {
                    currentQvSource = data.source || '';
                    document.getElementById('qvSource').innerText = data.source || '-';
                    document.getElementById('qvTanggal').innerText = data.tanggal || '-';
                    document.getElementById('qvComment').innerText = data.comment || '-';
                    document.getElementById('qvTotalDebits').innerText = data.total_debits_formatted || '0.00';
                    document.getElementById('qvTotalCredits').innerText = data.total_credits_formatted || '0.00';

                    const badge = document.getElementById('qvBalanceBadge');
                    if (data.is_balanced) {
                        badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800';
                        badge.innerText = 'Balanced';
                    } else {
                        badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-rose-100 text-rose-800';
                        badge.innerText = 'Unbalanced';
                    }

                    let html = '';
                    (data.details || []).forEach(d => {
                        html += `
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-1.5 font-semibold text-gray-800 whitespace-nowrap">${d.kode_akun}</td>
                                <td class="px-3 py-1.5">${d.nama_akun}</td>
                                <td class="px-3 py-1.5 text-gray-600"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700">${d.departemen}</span></td>
                                <td class="px-3 py-1.5 text-gray-600">${d.comment}</td>
                                <td class="px-3 py-1.5 text-right text-emerald-700 font-medium whitespace-nowrap">${d.debits > 0 ? d.debits_formatted : '-'}</td>
                                <td class="px-3 py-1.5 text-right text-amber-700 font-medium whitespace-nowrap">${d.credits > 0 ? d.credits_formatted : '-'}</td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = html;
                })
                .catch(err => {
                    tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-4 text-center text-rose-600 font-semibold"><i class="fas fa-exclamation-circle mr-2"></i> ${err.message}</td></tr>`;
                });
        }

        function closeQuickView() {
            const modal = document.getElementById('quickViewModal');
            if (modal) modal.classList.add('hidden');
        }

        function deleteCurrentQuickViewVoucher() {
            if (!currentQvJournalId) return;
            const jId = currentQvJournalId;
            const vSource = currentQvSource || document.getElementById('qvSource').innerText;
            closeQuickView();
            confirmDeleteVoucher(jId, vSource);
        }

        function confirmDeleteVoucher(journalId, voucherSource) {
            if (!journalId) return;
            const label = voucherSource ? `<strong>${voucherSource}</strong>` : 'voucher ini';
            if (typeof Swal === 'undefined') {
                if (confirm(`Apakah Anda yakin ingin menghapus voucher ${voucherSource || ''}? Semua baris debit & kredit pada voucher ini akan dihapus permanen.`)) {
                    submitDeleteVoucher(journalId);
                }
                return;
            }

            Swal.fire({
                title: 'Hapus Voucher?',
                html: `Apakah Anda yakin ingin menghapus voucher ${label}?<br><br><span class="text-xs text-gray-500">Semua baris debit & kredit pada voucher ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus Voucher',
                cancelButtonText: 'Batal',
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    submitDeleteVoucher(journalId);
                }
            });
        }

        function submitDeleteVoucher(journalId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Menghapus Voucher...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            let currentUrl = window.location.href;
            fetch(`{{ url('journal_entry') }}/${journalId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    back_url: currentUrl
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    sessionStorage.setItem('rca_pending_toast', data.message || 'Voucher berhasil dihapus.');
                    window.location.reload();
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menghapus',
                            text: data.message || 'Terjadi kesalahan saat menghapus voucher.',
                            confirmButtonColor: '#3b82f6'
                        });
                    } else {
                        alert(data.message || 'Gagal menghapus voucher.');
                    }
                }
            })
            .catch(err => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan Sistem',
                        text: err.message || 'Gagal menghubungi server.',
                        confirmButtonColor: '#3b82f6'
                    });
                } else {
                    alert('Gagal menghubungi server: ' + err.message);
                }
            });
        }

        // ===== CHECKLIST & BULK ACTIONS =====
        function syncBulkSelection() {
            const checkedBoxes = document.querySelectorAll('.gl-row-checkbox:checked');
            const uniqueVoucherIds = new Set();
            const detailIds = [];
            checkedBoxes.forEach(cb => {
                if (cb.value) uniqueVoucherIds.add(cb.value);
                const dId = cb.getAttribute('data-detail-id');
                if (dId) detailIds.push(dId);
            });

            const countRows = detailIds.length || checkedBoxes.length;
            const countVouchers = uniqueVoucherIds.size;
            const toolbar = document.getElementById('bulkDeleteToolbar');
            const countBadge = document.getElementById('bulkSelectedCount');
            const titleCount = document.getElementById('bulkTitleCount');

            if (countBadge) countBadge.innerText = countRows;
            if (titleCount) titleCount.innerText = countRows;
            document.querySelectorAll('.bulkBtnCount').forEach(el => el.innerText = countRows);
            document.querySelectorAll('.bulkVoucherCount').forEach(el => el.innerText = countVouchers);

            if (toolbar) {
                if (countRows > 0) {
                    toolbar.classList.remove('translate-y-24', 'opacity-0', 'pointer-events-none');
                    toolbar.classList.add('translate-y-0', 'opacity-100');
                } else {
                    toolbar.classList.add('translate-y-24', 'opacity-0', 'pointer-events-none');
                    toolbar.classList.remove('translate-y-0', 'opacity-100');
                }
            }

            // Sync header checkboxes per account (hanya evaluasi baris yang sedang terlihat)
            document.querySelectorAll('.check-all-account').forEach(masterCb => {
                const acc = masterCb.getAttribute('data-account');
                const visibleCheckboxes = Array.from(document.querySelectorAll(`.gl-row-checkbox[data-account="${acc}"]`))
                    .filter(cb => {
                        const tr = cb.closest('tr');
                        return tr && tr.style.display !== 'none';
                    });
                const visibleChecked = visibleCheckboxes.filter(cb => cb.checked);

                masterCb.checked = visibleCheckboxes.length > 0 && visibleCheckboxes.length === visibleChecked.length;
                masterCb.indeterminate = visibleChecked.length > 0 && visibleChecked.length < visibleCheckboxes.length;
            });
        }

        function handleRowCheckboxChange(cb) {
            syncBulkSelection();
        }

        function handleCheckAllAccount(masterCb) {
            const acc = masterCb.getAttribute('data-account');
            const isChecked = masterCb.checked;
            // Hanya centang/uncentang baris yang SEDANG TERLIHAT (hasil filter pencarian)
            document.querySelectorAll(`.gl-row-checkbox[data-account="${acc}"]`).forEach(cb => {
                const tr = cb.closest('tr');
                if (tr && tr.style.display !== 'none') {
                    cb.checked = isChecked;
                }
            });
            syncBulkSelection();
        }

        function clearBulkSelection() {
            document.querySelectorAll('.gl-row-checkbox').forEach(cb => cb.checked = false);
            document.querySelectorAll('.check-all-account').forEach(cb => {
                cb.checked = false;
                cb.indeterminate = false;
            });
            syncBulkSelection();
        }

        function getSelectedVoucherIds() {
            const checkedBoxes = document.querySelectorAll('.gl-row-checkbox:checked');
            const uniqueIds = new Set();
            checkedBoxes.forEach(cb => {
                if (cb.value) uniqueIds.add(parseInt(cb.value, 10));
            });
            return Array.from(uniqueIds);
        }

        function getSelectedDetailIds() {
            const checkedBoxes = document.querySelectorAll('.gl-row-checkbox:checked');
            const ids = [];
            checkedBoxes.forEach(cb => {
                const dId = cb.getAttribute('data-detail-id');
                if (dId) ids.push(parseInt(dId, 10));
            });
            return ids;
        }

        function executeBulkDelete() {
            const selectedIds = getSelectedVoucherIds();
            if (selectedIds.length === 0) return;

            if (typeof Swal === 'undefined') {
                if (confirm(`Hapus ${selectedIds.length} voucher terpilih secara permanen? Semua baris debit & kredit terkait akan dihapus.`)) {
                    submitBulkDeleteVouchers(selectedIds);
                }
                return;
            }

            Swal.fire({
                title: `Hapus ${selectedIds.length} Voucher?`,
                html: `Apakah Anda yakin ingin menghapus secara permanen <strong class="text-red-600">${selectedIds.length} voucher</strong> yang dipilih?<br><br><span class="text-xs text-gray-500">Semua baris debit & kredit pada voucher-voucher tersebut akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: `<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus ${selectedIds.length} Voucher`,
                cancelButtonText: 'Batal',
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    submitBulkDeleteVouchers(selectedIds);
                }
            });
        }

        function submitBulkDeleteVouchers(selectedIds) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Menghapus Voucher Terpilih...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            let currentUrl = window.location.href;
            fetch(`{{ route('journal_entry.bulk_delete') }}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ids: selectedIds,
                    back_url: currentUrl
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    sessionStorage.setItem('rca_pending_toast', data.message || `${selectedIds.length} voucher berhasil dihapus.`);
                    window.location.reload();
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menghapus',
                            text: data.message || 'Terjadi kesalahan saat menghapus voucher.',
                            confirmButtonColor: '#3b82f6'
                        });
                    } else {
                        alert(data.message || 'Gagal menghapus voucher.');
                    }
                }
            })
            .catch(err => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan Sistem',
                        text: err.message || 'Gagal menghubungi server.',
                        confirmButtonColor: '#3b82f6'
                    });
                } else {
                    alert('Gagal menghubungi server: ' + err.message);
                }
            });
        }

        // Global event delegation for checkboxes
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('gl-row-checkbox')) {
                handleRowCheckboxChange(e.target);
            } else if (e.target && e.target.classList.contains('check-all-account')) {
                handleCheckAllAccount(e.target);
            }
        });

        // Otomatis refresh halaman Buku Besar jika voucher diperbarui dari tab lain
        function handleJournalUpdated(val) {
            if (!val || !val.time) return;
            const lastHandled = parseInt(sessionStorage.getItem('rca_last_handled_time') || '0', 10);
            if (val.time > lastHandled) {
                sessionStorage.setItem('rca_last_handled_time', String(val.time));
                if (val.message) {
                    sessionStorage.setItem('rca_pending_toast', val.message);
                }
                try { localStorage.removeItem('rca_journal_updated'); } catch(e) {}

                if (val.url && window.location.href !== val.url) {
                    window.location.href = val.url;
                } else {
                    window.location.reload();
                }
            }
        }

        window.addEventListener('storage', function(e) {
            if (e.key === 'rca_journal_updated' && e.newValue) {
                try {
                    const val = JSON.parse(e.newValue);
                    handleJournalUpdated(val);
                } catch(err) {
                    console.error('Error handling storage event:', err);
                }
            }
        });

        window.addEventListener('focus', function() {
            const rawUpdate = localStorage.getItem('rca_journal_updated');
            if (rawUpdate) {
                try {
                    const val = JSON.parse(rawUpdate);
                    handleJournalUpdated(val);
                } catch(err) {
                    try { localStorage.removeItem('rca_journal_updated'); } catch(e) {}
                }
            }
        });

        // Tampilkan toast jika ada update dari tab lain
        document.addEventListener('DOMContentLoaded', function() {
            const rawUpdate = localStorage.getItem('rca_journal_updated');
            if (rawUpdate) {
                try {
                    const val = JSON.parse(rawUpdate);
                    const lastHandled = parseInt(sessionStorage.getItem('rca_last_handled_time') || '0', 10);
                    if (val.time && val.time > lastHandled && (Date.now() - val.time < 30000)) {
                        sessionStorage.setItem('rca_last_handled_time', String(val.time));
                        if (val.message) {
                            sessionStorage.setItem('rca_pending_toast', val.message);
                        }
                    }
                    localStorage.removeItem('rca_journal_updated');
                } catch(e) {}
            }

            const pendingToast = sessionStorage.getItem('rca_pending_toast');
            if (pendingToast) {
                sessionStorage.removeItem('rca_pending_toast');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: pendingToast,
                        confirmButtonText: 'OK'
                    });
                }
            }
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeQuickView();
                const modifyModal = document.getElementById('fileModify');
                if (modifyModal) modifyModal.classList.add('hidden');
            }
        });

        // Modal Date Presets
        function setModalDatePreset(preset) {
            const today = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const formatDate = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

            let start, end;
            const y = today.getFullYear();
            const m = today.getMonth();

            if (preset === 'today') {
                start = end = formatDate(today);
            } else if (preset === 'this_month') {
                start = formatDate(new Date(y, m, 1));
                end = formatDate(new Date(y, m + 1, 0));
            } else if (preset === 'last_month') {
                start = formatDate(new Date(y, m - 1, 1));
                end = formatDate(new Date(y, m, 0));
            } else if (preset === 'this_quarter') {
                const qStartMonth = Math.floor(m / 3) * 3;
                start = formatDate(new Date(y, qStartMonth, 1));
                end = formatDate(new Date(y, qStartMonth + 3, 0));
            } else if (preset === 'ytd') {
                start = formatDate(new Date(y, 0, 1));
                end = formatDate(today);
            } else if (preset === 'last_year') {
                start = formatDate(new Date(y - 1, 0, 1));
                end = formatDate(new Date(y - 1, 11, 31));
            }

            if (start && end) {
                const sEl = document.getElementById('modal_start_date');
                const eEl = document.getElementById('modal_end_date');
                if (sEl) sEl.value = start;
                if (eEl) eEl.value = end;
            }
        }

        // Modal Account Table Checkbox & Search handlers
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('modal_select-all');
            const checkboxes = document.querySelectorAll('.modal-account-checkbox');
            const hiddenInput = document.getElementById('modal_selected_accounts');

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = this.checked);
                    updateModalSelectedAccounts();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const row = cb.closest('tr');
                    const level = row.dataset.level;
                    const tipe = row.dataset.tipe;

                    if (level && level.toLowerCase() === 'header') {
                        const allSameType = document.querySelectorAll(
                            `#modal_account-table tbody tr[data-tipe="${tipe}"] .modal-account-checkbox`
                        );
                        allSameType.forEach(cb2 => {
                            cb2.checked = cb.checked;
                        });
                    }
                    updateModalSelectedAccounts();
                });
            });

            function updateModalSelectedAccounts() {
                const selected = [];
                checkboxes.forEach(cb => {
                    if (cb.checked) selected.push(cb.value);
                });
                if (hiddenInput) hiddenInput.value = selected.join(',');
            }

            const modalSearch = document.getElementById('modal_search-account');
            if (modalSearch) {
                modalSearch.addEventListener('keyup', function() {
                    const keyword = this.value.toLowerCase();
                    const rows = document.querySelectorAll('#modal_account-table tbody tr');
                    rows.forEach(function(row) {
                        const text = row.innerText.toLowerCase();
                        row.style.display = text.includes(keyword) ? '' : 'none';
                    });
                });
            }

            // Periode Buku change handler
            const periodeSelect = document.getElementById('periode');
            const startInput = document.getElementById('modal_start_date');
            const endInput = document.getElementById('modal_end_date');
            if (periodeSelect && startInput && endInput) {
                periodeSelect.addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    const tahun = opt?.getAttribute('data-tahun')?.trim();
                    if (/^\d{4}$/.test(tahun)) {
                        startInput.min = `${tahun}-01-01`;
                        startInput.max = `${tahun}-12-31`;
                        endInput.min = `${tahun}-01-01`;
                        endInput.max = `${tahun}-12-31`;
                        startInput.value = `${tahun}-01-01`;
                        endInput.value = `${tahun}-12-31`;
                    }
                });
            }
        });

        // ===== COLUMN DRAG-RESIZE =====
        (function () {
            let startX, startW, th;
            document.addEventListener('mousedown', function (e) {
                if (!e.target.classList.contains('col-resize-handle')) return;
                e.preventDefault();
                th = e.target.parentElement;
                startX = e.clientX;
                startW = th.offsetWidth;
                e.target.classList.add('dragging');
                document.addEventListener('mousemove', onDragMove);
                document.addEventListener('mouseup', onDragUp);
            });
            function onDragMove(e) {
                if (!th) return;
                const newW = Math.max(50, startW + (e.clientX - startX));
                th.style.width = newW + 'px';
                th.style.minWidth = newW + 'px';
            }
            function onDragUp() {
                document.querySelectorAll('.col-resize-handle.dragging').forEach(h => h.classList.remove('dragging'));
                document.removeEventListener('mousemove', onDragMove);
                document.removeEventListener('mouseup', onDragUp);
                th = null;
            }
        })();

        // ===== INLINE & BULK EDIT: LAWAN TRANSAKSI =====
        const _glContraUrl     = '{{ route("buku_besar.update_contra", ["id" => "__ID__"]) }}';
        const _glBulkContraUrl = '{{ route("buku_besar.bulk_update_contra") }}';
        const _glCoaSearch     = '{{ route("coa.search") }}';
        let _glContraDetailId = null, _glContraCell = null;
        let _glSelectedCoaKode = null, _glSelectedCoaNama = null;
        let _glCoaCache = {}, _glCoaDebounce = null;
        let _glIsBulkContra = false, _glBulkContraDetailIds = [];

        function glOpenContraModal(cell) {
            _glIsBulkContra = false;
            _glBulkContraDetailIds = [];
            _glContraCell = cell;
            _glContraDetailId = cell.dataset.detailId;
            _glSelectedCoaKode = null; _glSelectedCoaNama = null;
            document.getElementById('glContraModalTitle').innerHTML = '<i class="fas fa-pencil-alt text-amber-500 mr-1.5"></i> Ubah Lawan Transaksi';
            document.getElementById('glContraCurrentLabel').textContent = 'Lawan transaksi saat ini';
            document.getElementById('glContraCurrentName').textContent = cell.dataset.contraNama || '—';
            document.getElementById('glContraSearch').value = '';
            glFetchCoa('');
            document.getElementById('glContraModal').classList.remove('hidden');
            setTimeout(() => document.getElementById('glContraSearch').focus(), 100);
        }

        function glOpenBulkContraModal() {
            _glBulkContraDetailIds = getSelectedDetailIds();
            if (_glBulkContraDetailIds.length === 0) {
                glShowToast('Pilih transaksi terlebih dahulu melalui centang checkbox', 'error');
                return;
            }
            _glIsBulkContra = true;
            _glContraCell = null;
            _glContraDetailId = null;
            _glSelectedCoaKode = null; _glSelectedCoaNama = null;
            document.getElementById('glContraModalTitle').innerHTML = `<i class="fas fa-tag text-indigo-500 mr-1.5"></i> Ubah Lawan Transaksi Serentak (${_glBulkContraDetailIds.length} Transaksi)`;
            document.getElementById('glContraCurrentLabel').textContent = 'Cakupan perubahan serentak';
            document.getElementById('glContraCurrentName').textContent = `${_glBulkContraDetailIds.length} transaksi terpilih (centang)`;
            document.getElementById('glContraSearch').value = '';
            glFetchCoa('');
            document.getElementById('glContraModal').classList.remove('hidden');
            setTimeout(() => document.getElementById('glContraSearch').focus(), 100);
        }

        function glCloseContraModal() {
            document.getElementById('glContraModal').classList.add('hidden');
        }

        function glFilterCoa(q) {
            clearTimeout(_glCoaDebounce);
            _glCoaDebounce = setTimeout(() => glFetchCoa(q.trim()), 250);
        }

        function glFetchCoa(q) {
            const cacheKey = q;
            if (_glCoaCache[cacheKey] !== undefined) { glRenderCoaList(_glCoaCache[cacheKey], q); return; }
            document.getElementById('glCoaList').innerHTML = '<div class="px-4 py-3 text-center text-gray-400"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat...</div>';
            fetch(_glCoaSearch + '?q=' + encodeURIComponent(q) + '&page=1')
                .then(r => r.json())
                .then(data => {
                    _glCoaCache[cacheKey] = data.results || [];
                    glRenderCoaList(_glCoaCache[cacheKey], q);
                })
                .catch(() => {
                    document.getElementById('glCoaList').innerHTML = '<div class="px-4 py-3 text-center text-red-400">Gagal memuat data akun</div>';
                });
        }

        function glRenderCoaList(items, q) {
            const list = document.getElementById('glCoaList');
            if (!items.length) { list.innerHTML = '<div class="px-4 py-3 text-center text-gray-400">Tidak ada hasil</div>'; return; }
            list.innerHTML = items.map(a => `
                <div class="px-3 py-2 cursor-pointer border-b border-gray-100 last:border-0 hover:bg-indigo-50 transition gl-coa-item ${_glSelectedCoaKode === a.kode ? 'bg-indigo-50 font-semibold' : ''}"
                     onclick="glSelectCoa('${a.kode.replace(/'/g,"\\'")}', '${(a.nama||'').replace(/'/g,"\\'")}', this)">
                    <span class="text-[10px] font-bold text-indigo-600 mr-1">${a.kode}</span>
                    <span class="text-gray-700">${a.nama || ''}</span>
                </div>`).join('');
        }

        function glSelectCoa(kode, nama, el) {
            _glSelectedCoaKode = kode; _glSelectedCoaNama = nama;
            document.querySelectorAll('.gl-coa-item').forEach(i => i.classList.remove('bg-indigo-50', 'font-semibold'));
            el.classList.add('bg-indigo-50', 'font-semibold');
        }

        function glConfirmContra() {
            if (!_glSelectedCoaKode) { glShowToast('Pilih akun terlebih dahulu', 'error'); return; }
            glCloseContraModal();
            if (_glIsBulkContra) {
                document.getElementById('glConfirmContent').innerHTML =
                    `Ubah lawan transaksi untuk <b>${_glBulkContraDetailIds.length} transaksi terpilih</b> menjadi:<br><b class="text-indigo-700">${_glSelectedCoaKode} — ${_glSelectedCoaNama}</b><br><span class="text-[11px] text-gray-500 mt-1 block">* Transaksi dengan multi-lawan (multi-contra) akan otomatis dilewati demi keamanan.</span>`;
                document.getElementById('glConfirmExecBtn').onclick = glExecuteBulkContra;
            } else {
                document.getElementById('glConfirmContent').innerHTML =
                    `Ganti lawan transaksi menjadi:<br><b class="text-indigo-700">${_glSelectedCoaKode} — ${_glSelectedCoaNama}</b>`;
                document.getElementById('glConfirmExecBtn').onclick = glExecuteContra;
            }
            document.getElementById('glConfirmModal').classList.remove('hidden');
        }

        function glExecuteContra() {
            document.getElementById('glConfirmModal').classList.add('hidden');
            if (!_glContraDetailId || !_glSelectedCoaKode) return;
            const row = _glContraCell.closest('tr');
            row.classList.add('gl-saving');
            const url = _glContraUrl.replace('__ID__', _glContraDetailId);
            fetch(url, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ new_kode_akun: _glSelectedCoaKode })
            })
            .then(r => r.json())
            .then(data => {
                row.classList.remove('gl-saving');
                if (data.success) {
                    _glContraCell.querySelector('span').textContent = data.nama_akun || _glSelectedCoaNama;
                    _glContraCell.dataset.contraNama = data.nama_akun || _glSelectedCoaNama;
                    const curSearchText = (row.getAttribute('data-search-text') || '');
                    row.setAttribute('data-search-text', (curSearchText + ' ' + (data.nama_akun || _glSelectedCoaNama) + ' ' + _glSelectedCoaKode).toLowerCase());
                    glShowToast('✅ Lawan transaksi diubah', 'success');
                } else {
                    glShowToast(data.message || 'Gagal menyimpan', 'error');
                }
            })
            .catch(() => { row.classList.remove('gl-saving'); glShowToast('Gagal menghubungi server', 'error'); });
        }

        function glExecuteBulkContra() {
            document.getElementById('glConfirmModal').classList.add('hidden');
            if (!_glBulkContraDetailIds.length || !_glSelectedCoaKode) return;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Menyimpan Perubahan...',
                    text: `Memperbarui lawan transaksi untuk ${_glBulkContraDetailIds.length} transaksi...`,
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
            }

            fetch(_glBulkContraUrl, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ detail_ids: _glBulkContraDetailIds, new_kode_akun: _glSelectedCoaKode })
            })
            .then(r => r.json())
            .then(data => {
                if (typeof Swal !== 'undefined') Swal.close();
                if (data.success) {
                    (data.updated_ids || []).forEach(dId => {
                        const row = document.getElementById('gl-row-' + dId) || document.querySelector(`tr[data-detail-id="${dId}"]`);
                        if (row) {
                            const contraCell = row.querySelector('.contra-col .gl-editable');
                            if (contraCell) {
                                const span = contraCell.querySelector('span');
                                if (span) span.textContent = data.nama_akun || _glSelectedCoaNama;
                                contraCell.dataset.contraNama = data.nama_akun || _glSelectedCoaNama;
                            }
                            const curSearch = row.getAttribute('data-search-text') || '';
                            row.setAttribute('data-search-text', (curSearch + ' ' + (data.nama_akun || _glSelectedCoaNama) + ' ' + _glSelectedCoaKode).toLowerCase());
                        }
                    });

                    clearBulkSelection();
                    glShowToast(data.message, 'success');
                } else {
                    glShowToast(data.message || 'Gagal mengubah lawan transaksi serentak', 'error');
                }
            })
            .catch(err => {
                if (typeof Swal !== 'undefined') Swal.close();
                glShowToast('Gagal menghubungi server: ' + err.message, 'error');
            });
        }

        // ===== INLINE & BULK EDIT: DEPARTEMEN =====
        const _glDeptUrl     = '{{ route("buku_besar.update_departemen", ["id" => "__ID__"]) }}';
        const _glBulkDeptUrl = '{{ route("buku_besar.bulk_update_departemen") }}';
        @php
            $glDeptOptions = \App\DepartemenAkun::with('departemen')
                ->get()
                ->map(function ($da) {
                    return [
                        'id'      => $da->id,
                        'kode'    => optional($da->departemen)->kode ?? '-',
                        'nama'    => optional($da->departemen)->deskripsi ?? '-',
                        'dept_id' => $da->departemen_id,
                    ];
                })
                ->unique('dept_id')
                ->values();
        @endphp
        const _glDeptOptions = {!! json_encode($glDeptOptions) !!};
        let _glDeptDetailId = null, _glDeptCell = null;
        let _glSelectedDeptAkunId = null, _glSelectedDeptKode = null, _glSelectedDeptNama = null;
        let _glIsBulkDept = false, _glBulkDeptDetailIds = [];

        function glOpenDeptModal(cell) {
            _glIsBulkDept = false;
            _glBulkDeptDetailIds = [];
            _glDeptCell = cell; _glDeptDetailId = cell.dataset.detailId;
            _glSelectedDeptAkunId = null;
            const curKode = cell.dataset.deptKode || '';
            const curNama = cell.dataset.deptNama || '';
            document.getElementById('glDeptModalTitle').innerHTML = '<i class="fas fa-pencil-alt text-violet-500 mr-1.5"></i> Ubah Departemen';
            document.getElementById('glDeptCurrentLabel').textContent = 'Departemen saat ini';
            document.getElementById('glDeptCurrentName').textContent = curKode ? `${curKode} — ${curNama}` : 'Non-Departemen';
            glRenderDeptList();
            document.getElementById('glDeptModal').classList.remove('hidden');
        }

        function glOpenBulkDeptModal() {
            _glBulkDeptDetailIds = getSelectedDetailIds();
            if (_glBulkDeptDetailIds.length === 0) {
                glShowToast('Pilih baris transaksi terlebih dahulu melalui centang checkbox', 'error');
                return;
            }
            _glIsBulkDept = true;
            _glDeptCell = null;
            _glDeptDetailId = null;
            _glSelectedDeptAkunId = null;
            document.getElementById('glDeptModalTitle').innerHTML = `<i class="fas fa-building text-violet-500 mr-1.5"></i> Ubah Departemen Serentak (${_glBulkDeptDetailIds.length} Baris)`;
            document.getElementById('glDeptCurrentLabel').textContent = 'Cakupan perubahan serentak';
            document.getElementById('glDeptCurrentName').textContent = `${_glBulkDeptDetailIds.length} baris transaksi terpilih (centang)`;
            glRenderDeptList();
            document.getElementById('glDeptModal').classList.remove('hidden');
        }

        function glCloseDeptModal() {
            document.getElementById('glDeptModal').classList.add('hidden');
        }

        function glRenderDeptList() {
            const list = document.getElementById('glDeptList');
            const rows = [{ id: 0, kode: '—', nama: 'Non-Departemen (Hapus)' }, ..._glDeptOptions];
            list.innerHTML = rows.map(d => `
                <div class="px-3 py-2 cursor-pointer border-b border-gray-100 last:border-0 hover:bg-violet-50 transition gl-dept-item ${_glSelectedDeptAkunId === d.id ? 'bg-violet-50 font-semibold' : ''}"
                     onclick="glSelectDept(${d.id || 0}, '${(d.kode||'').replace(/'/g,"\\'")}', '${(d.nama||'').replace(/'/g,"\\'")}', this)">
                    <span class="inline-block text-[10px] font-bold bg-violet-100 text-violet-700 rounded px-1.5 mr-1.5">${d.kode}</span>
                    <span class="text-gray-700">${d.nama}</span>
                </div>`).join('');
        }

        function glSelectDept(id, kode, nama, el) {
            _glSelectedDeptAkunId = id; _glSelectedDeptKode = kode; _glSelectedDeptNama = nama;
            document.querySelectorAll('.gl-dept-item').forEach(i => i.classList.remove('bg-violet-50', 'font-semibold'));
            el.classList.add('bg-violet-50', 'font-semibold');
        }

        function glConfirmDept() {
            if (_glSelectedDeptAkunId === null) { glShowToast('Pilih departemen terlebih dahulu', 'error'); return; }
            glCloseDeptModal();
            const label = _glSelectedDeptAkunId === 0 ? 'Non-Departemen' : `${_glSelectedDeptKode} — ${_glSelectedDeptNama}`;
            if (_glIsBulkDept) {
                document.getElementById('glConfirmContent').innerHTML =
                    `Ubah departemen untuk <b>${_glBulkDeptDetailIds.length} baris transaksi terpilih</b> menjadi:<br><b class="text-violet-700">${label}</b>`;
                document.getElementById('glConfirmExecBtn').onclick = glExecuteBulkDept;
            } else {
                document.getElementById('glConfirmContent').innerHTML =
                    `Ganti departemen menjadi:<br><b class="text-violet-700">${label}</b>`;
                document.getElementById('glConfirmExecBtn').onclick = glExecuteDept;
            }
            document.getElementById('glConfirmModal').classList.remove('hidden');
        }

        function glExecuteDept() {
            document.getElementById('glConfirmModal').classList.add('hidden');
            if (!_glDeptDetailId) return;
            const row = _glDeptCell.closest('tr');
            row.classList.add('gl-saving');
            const url = _glDeptUrl.replace('__ID__', _glDeptDetailId);
            const body = { departemen_akun_id: _glSelectedDeptAkunId || null };
            fetch(url, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify(body)
            })
            .then(r => r.json())
            .then(data => {
                row.classList.remove('gl-saving');
                if (data.success) {
                    const isNone = !data.departemen;
                    _glDeptCell.dataset.deptAkunId = isNone ? '' : (_glSelectedDeptAkunId || '');
                    _glDeptCell.dataset.deptKode   = isNone ? '' : _glSelectedDeptKode;
                    _glDeptCell.dataset.deptNama   = isNone ? '' : _glSelectedDeptNama;
                    const badge = _glDeptCell.querySelector('span');
                    if (badge) {
                        if (isNone) {
                            badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-500 border border-gray-200';
                            badge.textContent = 'Non-Dept';
                        } else {
                            badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200';
                            badge.textContent = _glSelectedDeptKode;
                        }
                    }
                    const curSearchText = (row.getAttribute('data-search-text') || '');
                    const addedDeptText = isNone ? 'non-dept unassigned' : (_glSelectedDeptKode + ' ' + _glSelectedDeptNama);
                    row.setAttribute('data-search-text', (curSearchText + ' ' + addedDeptText).toLowerCase());
                    glShowToast('✅ Departemen diubah', 'success');
                } else {
                    glShowToast(data.message || 'Gagal menyimpan', 'error');
                }
            })
            .catch(() => { row.classList.remove('gl-saving'); glShowToast('Gagal menghubungi server', 'error'); });
        }

        function glExecuteBulkDept() {
            document.getElementById('glConfirmModal').classList.add('hidden');
            if (!_glBulkDeptDetailIds.length) return;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Menyimpan Perubahan...',
                    text: `Memperbarui departemen untuk ${_glBulkDeptDetailIds.length} baris...`,
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
            }

            const body = { detail_ids: _glBulkDeptDetailIds, departemen_akun_id: _glSelectedDeptAkunId || null };
            fetch(_glBulkDeptUrl, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify(body)
            })
            .then(r => r.json())
            .then(data => {
                if (typeof Swal !== 'undefined') Swal.close();
                if (data.success) {
                    const isNone = !data.departemen;
                    (data.updated_ids || _glBulkDeptDetailIds).forEach(dId => {
                        const row = document.getElementById('gl-row-' + dId) || document.querySelector(`tr[data-detail-id="${dId}"]`);
                        if (row) {
                            const deptCell = row.querySelector('.dept-editable');
                            if (deptCell) {
                                deptCell.dataset.deptAkunId = isNone ? '' : (_glSelectedDeptAkunId || '');
                                deptCell.dataset.deptKode   = isNone ? '' : _glSelectedDeptKode;
                                deptCell.dataset.deptNama   = isNone ? '' : _glSelectedDeptNama;
                                const badge = deptCell.querySelector('span');
                                if (badge) {
                                    if (isNone) {
                                        badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-500 border border-gray-200';
                                        badge.textContent = 'Non-Dept';
                                    } else {
                                        badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200';
                                        badge.textContent = _glSelectedDeptKode;
                                    }
                                }
                            }
                            const curSearch = row.getAttribute('data-search-text') || '';
                            const addedDeptText = isNone ? 'non-dept unassigned' : (_glSelectedDeptKode + ' ' + _glSelectedDeptNama);
                            row.setAttribute('data-search-text', (curSearch + ' ' + addedDeptText).toLowerCase());
                        }
                    });

                    clearBulkSelection();
                    glShowToast(data.message, 'success');
                } else {
                    glShowToast(data.message || 'Gagal mengubah departemen serentak', 'error');
                }
            })
            .catch(err => {
                if (typeof Swal !== 'undefined') Swal.close();
                glShowToast('Gagal menghubungi server: ' + err.message, 'error');
            });
        }

        function glCancelConfirm() {
            document.getElementById('glConfirmModal').classList.add('hidden');
        }

        // ===== TOAST =====
        let _glToastTimer;
        function glShowToast(msg, type) {
            // Reuse SweetAlert if available, otherwise simple native toast
            if (typeof Swal !== 'undefined' && type) {
                Swal.fire({ toast: true, position: 'bottom-end', icon: type === 'success' ? 'success' : 'error',
                    title: msg, showConfirmButton: false, timer: 2500, timerProgressBar: true });
                return;
            }
            let t = document.getElementById('glToast');
            if (!t) {
                t = document.createElement('div');
                t.id = 'glToast';
                t.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;padding:10px 18px;border-radius:10px;font-size:12px;font-weight:600;color:#fff;box-shadow:0 8px 24px #0003;transition:.3s;opacity:0;transform:translateY(8px)';
                document.body.appendChild(t);
            }
            t.textContent = msg;
            t.style.background = type === 'success' ? '#065f46' : type === 'error' ? '#7f1d1d' : '#1e293b';
            t.style.opacity = '1'; t.style.transform = 'translateY(0)';
            clearTimeout(_glToastTimer);
            _glToastTimer = setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateY(8px)'; }, 2800);
        }

        // Close new modals on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                glCloseContraModal(); glCloseDeptModal(); glCancelConfirm();
            }
        });
    </script>
@endsection

@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-md p-4">
                <div>
                    <ul class="flex border-b mb-3 space-x-4 text-[10px] font-medium text-gray-600">
                        <li>
                            <a href="{{ route('arus_kas.export', [
                                    'start_date' => $tanggalAwal,
                                    'end_date' => $tanggalAkhir,
                                    'selected_accounts' => request('selected_accounts'),
                                    'display_mode' => $displayMode,
                                    'format' => 'excel',
                                ]) }}"
                                class="tab-link text-green-600 hover:text-green-800">
                                <i class="fas fa-file-excel mr-1"></i> Export Excel
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('arus_kas.export', [
                                    'start_date' => $tanggalAwal,
                                    'end_date' => $tanggalAkhir,
                                    'selected_accounts' => request('selected_accounts'),
                                    'display_mode' => $displayMode,
                                    'format' => 'pdf',
                                ]) }}"
                                class="tab-link text-red-600 hover:text-red-800">
                                <i class="fas fa-file-pdf mr-1"></i> Export PDF
                            </a>
                        </li>
                        <li><a onclick="document.getElementById('fileModify').classList.toggle('hidden')"
                                class="tab-link cursor-pointer">Modify</a></li>
                    </ul>
                </div>
                <div id="fileModify"
                    class="fixed inset-0 z-50 hidden bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center">
                    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-7xl">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-file-alt mr-2 text-blue-400"></i> File Aksi
                        </h3>
                        <div class="space-y-3 text-sm text-gray-700">
                            <form method="GET" action="{{ route('arus_kas.arus_kas_report') }}"
                                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="periode" class="block text-sm font-semibold text-gray-700 mb-1">Periode
                                        Buku</label>
                                    <select name="periode_buku" id="periode"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">---Pilih---</option>
                                    </select>
                                </div>

                                {{-- Tanggal Awal --}}
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                                        Awal</label>
                                    <input type="date" id="start_date" name="start_date"
                                        value="{{ request('start_date', \Carbon\Carbon::parse($tanggalAwal)->format('Y-m-d')) }}"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                {{-- Tanggal Akhir --}}
                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                                        Akhir</label>
                                    <input type="date" id="end_date" name="end_date"
                                        value="{{ request('end_date', \Carbon\Carbon::parse($tanggalAkhir)->format('Y-m-d')) }}"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <input type="hidden" name="selected_accounts" value="{{ request('selected_accounts') }}">
                                <input type="hidden" name="display_mode" value="{{ $displayMode }}">

                                {{-- Tombol Filter --}}
                                <div class="sm:col-span-3 flex items-center gap-2 mt-4">
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-md shadow hover:bg-blue-700">
                                        <i class="fas fa-filter mr-2"></i> Ok
                                    </button>
                                    <a href="{{ route('arus_kas.filter_arus_kas') }}"
                                        class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 font-medium text-sm rounded-md hover:bg-gray-200">
                                        <i class="fas fa-undo mr-2"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                        <div class="mt-4 text-right">
                            <button onclick="document.getElementById('fileModify').classList.add('hidden')"
                                class="text-sm text-gray-500 hover:text-gray-700">Tutup</button>
                        </div>
                    </div>
                </div>
                
                <h3 class="text-lg font-bold mb-2">LAPORAN CASH FLOW</h3>
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-[10px]">
                        <span class="font-semibold">Periode:</span>
                        {{ \Carbon\Carbon::parse($tanggalAwal)->format('d M Y') }} -
                        {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d M Y') }}
                    </p>
                    <div class="text-[10px]">
                        <span class="font-semibold">Mode:</span>
                        @if($displayMode == 'source')
                            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-[10px]">Detail per Source</span>
                        @elseif($displayMode == 'account')
                            <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded text-[10px]">Per Account Kas/Bank</span>
                        @elseif($displayMode == 'direct')
                            <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded text-[10px]">Metode Langsung</span>
                        @else
                            <span class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded text-[10px]">Universal</span>
                        @endif
                    </div>
                </div>

                {{-- ========================================= --}}
                {{-- MODE 1: DETAIL PER SOURCE (DEFAULT) --}}
                {{-- ========================================= --}}
                @if($displayMode == 'source')
                    @php
                        $grouped = collect($rows)->groupBy('source');
                        $grandCashIn = 0;
                        $grandCashOut = 0;
                    @endphp

                    <table class="min-w-full border-collapse border border-gray-300 text-[10px]">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-2 py-1 w-20">Tanggal</th>
                                <th class="border px-2 py-1 w-32">Source</th>
                                <th class="border px-2 py-1">Akun Kas/Bank</th>
                                <th class="border px-2 py-1">Lawan Akun</th>
                                <th class="border px-2 py-1">Keterangan</th>
                                <th class="border px-2 py-1 text-right w-24">Cash In</th>
                                <th class="border px-2 py-1 text-right w-24">Cash Out</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($grouped as $source => $items)
                                {{-- Header Source --}}
                                <tr class="bg-gray-200 font-semibold">
                                    <td colspan="7" class="px-2 py-1 text-left">
                                        Source: {{ $source }}
                                    </td>
                                </tr>

                                @php
                                    $subtotalIn = 0;
                                    $subtotalOut = 0;
                                @endphp

                                {{-- Baris Detail --}}
                                @foreach ($items as $r)
                                    <tr class="hover:bg-gray-50">
                                        <td class="border px-2 py-1">{{ \Carbon\Carbon::parse($r['tanggal'])->format('d/m/Y') }}</td>
                                        <td class="border px-2 py-1">{{ $r['source'] }}</td>
                                        <td class="border px-2 py-1">{{ $r['akun_kas'] }}</td>
                                        <td class="border px-2 py-1">{{ $r['lawan_akun'] }}</td>
                                        <td class="border px-2 py-1">{{ $r['keterangan'] }}</td>
                                        <td class="border px-2 py-1 text-right">{{ number_format($r['cash_in'], 2) }}</td>
                                        <td class="border px-2 py-1 text-right">{{ number_format($r['cash_out'], 2) }}</td>
                                    </tr>

                                    @php
                                        $subtotalIn += $r['cash_in'];
                                        $subtotalOut += $r['cash_out'];
                                    @endphp
                                @endforeach

                                {{-- Subtotal per Source --}}
                                <tr class="font-semibold bg-gray-100">
                                    <td colspan="5" class="px-2 py-1 text-right">Subtotal ({{ $source }})</td>
                                    <td class="border px-2 py-1 text-right">{{ number_format($subtotalIn, 2) }}</td>
                                    <td class="border px-2 py-1 text-right">{{ number_format($subtotalOut, 2) }}</td>
                                </tr>

                                @php
                                    $grandCashIn += $subtotalIn;
                                    $grandCashOut += $subtotalOut;
                                @endphp
                            @endforeach
                        </tbody>

                        {{-- Total Keseluruhan --}}
                        <tfoot>
                            <tr class="bg-gray-300 font-bold">
                                <td colspan="5" class="px-2 py-1 text-right">TOTAL KESELURUHAN</td>
                                <td class="border px-2 py-1 text-right">{{ number_format($grandCashIn, 2) }}</td>
                                <td class="border px-2 py-1 text-right">{{ number_format($grandCashOut, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                {{-- ========================================= --}}
                {{-- MODE 2: PER ACCOUNT KAS/BANK --}}
                {{-- ========================================= --}}
                @elseif($displayMode == 'account')
                    @php
                        $grouped = collect($rows)->groupBy('akun_kas');
                        $grandCashIn = 0;
                        $grandCashOut = 0;
                        $accountIndex = 0;
                    @endphp

                    {{-- Global Toggle Button --}}
                    <div class="mb-2 flex gap-2" x-data="{ allExpanded: false }">
                        <button type="button" 
                            @click="allExpanded = !allExpanded; document.querySelectorAll('[data-detail-rows]').forEach(el => el.style.display = allExpanded ? 'table-row' : 'none'); document.querySelectorAll('[data-toggle-icon]').forEach(el => el.classList.toggle('fa-chevron-right', !allExpanded)); document.querySelectorAll('[data-toggle-icon]').forEach(el => el.classList.toggle('fa-chevron-down', allExpanded))"
                            class="px-3 py-1 text-[10px] bg-blue-600 text-white rounded hover:bg-blue-700">
                            <i class="fas fa-expand-alt mr-1"></i> 
                            <span x-text="allExpanded ? 'Collapse All' : 'Expand All'">Expand All</span>
                        </button>
                    </div>

                    <table class="min-w-full border-collapse border border-gray-300 text-[10px]">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-2 py-1 w-20">Tanggal</th>
                                <th class="border px-2 py-1 w-32">Source</th>
                                <th class="border px-2 py-1">Lawan Akun</th>
                                <th class="border px-2 py-1">Line Comment</th>
                                <th class="border px-2 py-1 text-right w-24">Cash In</th>
                                <th class="border px-2 py-1 text-right w-24">Cash Out</th>
                                <th class="border px-2 py-1 text-right w-24">Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($grouped as $akunKas => $items)
                                @php
                                    $subtotalIn = $items->sum('cash_in');
                                    $subtotalOut = $items->sum('cash_out');
                                    $netCashFlow = $subtotalIn - $subtotalOut;
                                    $grandCashIn += $subtotalIn;
                                    $grandCashOut += $subtotalOut;
                                    $accountIndex++;
                                @endphp

                                {{-- Header Account (Clickable) --}}
                                <tr class="bg-blue-100 font-semibold cursor-pointer hover:bg-blue-200" 
                                    onclick="toggleAccountRows({{ $accountIndex }})">
                                    <td colspan="4" class="px-2 py-1 text-left">
                                        <i class="fas fa-chevron-right mr-1 text-blue-600 transition-transform" data-toggle-icon data-account="{{ $accountIndex }}"></i>
                                        <i class="fas fa-wallet mr-1"></i>{{ $akunKas }}
                                    </td>
                                    <td class="border px-2 py-1 text-right">{{ number_format($subtotalIn, 2) }}</td>
                                    <td class="border px-2 py-1 text-right">{{ number_format($subtotalOut, 2) }}</td>
                                    <td class="border px-2 py-1 text-right {{ $netCashFlow >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                        {{ number_format($netCashFlow, 2) }}
                                    </td>
                                </tr>

                                {{-- Baris Detail (Hidden by default) --}}
                                @foreach ($items as $r)
                                    <tr class="hover:bg-gray-50" data-detail-rows data-account="{{ $accountIndex }}" style="display: none;">
                                        <td class="border px-2 py-1">{{ \Carbon\Carbon::parse($r['tanggal'])->format('d/m/Y') }}</td>
                                        <td class="border px-2 py-1">{{ $r['source'] }}</td>
                                        <td class="border px-2 py-1">{{ $r['lawan_akun'] }}</td>
                                        <td class="border px-2 py-1">{{ $r['line_comment'] }}</td>
                                        <td class="border px-2 py-1 text-right">{{ number_format($r['cash_in'], 2) }}</td>
                                        <td class="border px-2 py-1 text-right">{{ number_format($r['cash_out'], 2) }}</td>
                                        <td class="border px-2 py-1 text-right"></td>
                                    </tr>
                                @endforeach

                                {{-- Subtotal per Account (Hidden by default) --}}
                                <tr class="font-semibold bg-blue-50" data-detail-rows data-account="{{ $accountIndex }}" style="display: none;">
                                    <td colspan="4" class="px-2 py-1 text-right">Subtotal</td>
                                    <td class="border px-2 py-1 text-right">{{ number_format($subtotalIn, 2) }}</td>
                                    <td class="border px-2 py-1 text-right">{{ number_format($subtotalOut, 2) }}</td>
                                    <td class="border px-2 py-1 text-right {{ $netCashFlow >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($netCashFlow, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        {{-- Total Keseluruhan --}}
                        <tfoot>
                            <tr class="bg-gray-300 font-bold">
                                <td colspan="4" class="px-2 py-1 text-right">TOTAL KESELURUHAN</td>
                                <td class="border px-2 py-1 text-right">{{ number_format($grandCashIn, 2) }}</td>
                                <td class="border px-2 py-1 text-right">{{ number_format($grandCashOut, 2) }}</td>
                                <td class="border px-2 py-1 text-right {{ ($grandCashIn - $grandCashOut) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($grandCashIn - $grandCashOut, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <script>
                        function toggleAccountRows(accountId) {
                            const rows = document.querySelectorAll(`[data-detail-rows][data-account="${accountId}"]`);
                            const icon = document.querySelector(`[data-toggle-icon][data-account="${accountId}"]`);
                            const isHidden = rows[0]?.style.display === 'none';
                            
                            rows.forEach(row => row.style.display = isHidden ? 'table-row' : 'none');
                            icon?.classList.toggle('fa-chevron-right', !isHidden);
                            icon?.classList.toggle('fa-chevron-down', isHidden);
                        }
                    </script>

                {{-- ========================================= --}}
                {{-- MODE 3: UNIVERSAL (TABEL LENGKAP) --}}
                {{-- ========================================= --}}
                @elseif($displayMode == 'universal')
                    @php
                        $grandCashIn = collect($rows)->sum('cash_in');
                        $grandCashOut = collect($rows)->sum('cash_out');
                    @endphp

                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse border border-gray-300 text-[10px]">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border px-2 py-1 w-20">Tanggal</th>
                                    <th class="border px-2 py-1 w-32">Source</th>
                                    <th class="border px-2 py-1">Akun Kas/Bank</th>
                                    <th class="border px-2 py-1">Lawan Akun</th>
                                    <th class="border px-2 py-1">Line Comment</th>
                                    <th class="border px-2 py-1">Keterangan</th>
                                    <th class="border px-2 py-1 text-right w-24">Cash In</th>
                                    <th class="border px-2 py-1 text-right w-24">Cash Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $r)
                                    <tr class="hover:bg-gray-50">
                                        <td class="border px-2 py-1">{{ \Carbon\Carbon::parse($r['tanggal'])->format('d/m/Y') }}</td>
                                        <td class="border px-2 py-1">{{ $r['source'] }}</td>
                                        <td class="border px-2 py-1">{{ $r['akun_kas'] }}</td>
                                        <td class="border px-2 py-1">{{ $r['lawan_akun'] }}</td>
                                        <td class="border px-2 py-1">{{ $r['line_comment'] }}</td>
                                        <td class="border px-2 py-1">{{ $r['keterangan'] }}</td>
                                        <td class="border px-2 py-1 text-right">{{ number_format($r['cash_in'], 2) }}</td>
                                        <td class="border px-2 py-1 text-right">{{ number_format($r['cash_out'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>

                            {{-- Total Keseluruhan --}}
                            <tfoot>
                                <tr class="bg-gray-300 font-bold">
                                    <td colspan="6" class="px-2 py-1 text-right">TOTAL KESELURUHAN</td>
                                    <td class="border px-2 py-1 text-right">{{ number_format($grandCashIn, 2) }}</td>
                                    <td class="border px-2 py-1 text-right">{{ number_format($grandCashOut, 2) }}</td>
                                </tr>
                                <tr class="bg-gray-200 font-semibold">
                                    <td colspan="6" class="px-2 py-1 text-right">NET CASH FLOW</td>
                                    <td colspan="2" class="border px-2 py-1 text-right {{ ($grandCashIn - $grandCashOut) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($grandCashIn - $grandCashOut, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif

                {{-- ========================== METODE LANGSUNG (DIRECT METHOD) ========================== --}}
                @if ($displayMode === 'direct')
                    @php
                        $grandCashIn = collect($rows)->sum('cash_in');
                        $grandCashOut = collect($rows)->sum('cash_out');
                        $netCashFlow = $grandCashIn - $grandCashOut;
                        
                        // Group rows by aktivitas and klasifikasi for detail view
                        $rowsByKlasifikasi = collect($rows)->groupBy('klasifikasi_akun');
                    @endphp

                    <div class="overflow-x-auto" x-data="{ expandedItems: {} }">
                        {{-- Toggle All Button --}}
                        <div class="mb-2 flex gap-2">
                            <button type="button" 
                                @click="Object.keys(expandedItems).length > 0 ? expandedItems = {} : expandedItems = Object.fromEntries([...document.querySelectorAll('[data-klasifikasi-id]')].map(el => [el.dataset.klasifikasiId, true]))"
                                class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                <i class="fas fa-expand-alt mr-1"></i> Toggle Semua Detail
                            </button>
                        </div>
                        
                        <table class="min-w-full border-collapse text-sm">
                            <thead>
                                <tr>
                                    <th class="text-left px-4 py-2 font-bold text-lg text-gray-800 bg-gray-100" colspan="4">
                                        LAPORAN ARUS KAS - METODE LANGSUNG
                                    </th>
                                </tr>
                                <tr>
                                    <th class="text-left px-4 py-1 text-gray-600 text-xs" colspan="4">
                                        Periode: {{ \Carbon\Carbon::parse($tanggalAwal)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d M Y') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $runningTotal = 0; $klasifikasiIndex = 0; @endphp
                                
                                @foreach ($aktivitasData as $aktKey => $akt)
                                    @php
                                        $aktNetFlow = $akt['cash_in'] - $akt['cash_out'];
                                        $runningTotal += $aktNetFlow;
                                    @endphp
                                    
                                    {{-- Header Aktivitas --}}
                                    <tr class="bg-blue-50">
                                        <td colspan="4" class="px-4 py-3 font-bold text-blue-800 border-t-2 border-blue-200">
                                            <i class="fas fa-folder-open mr-2"></i>{{ $akt['label'] }}
                                        </td>
                                    </tr>
                                    
                                    {{-- Items per Klasifikasi --}}
                                    @foreach ($akt['items'] as $klasifikasiKey => $item)
                                        @php
                                            $itemNet = $item['cash_in'] - $item['cash_out'];
                                            $klasifikasiId = 'klas_' . $klasifikasiIndex;
                                            $klasifikasiIndex++;
                                            $detailRows = $rowsByKlasifikasi->get($klasifikasiKey, collect());
                                        @endphp
                                        
                                        {{-- Klasifikasi Row (Clickable) --}}
                                        <tr class="hover:bg-gray-50 cursor-pointer" 
                                            @click="expandedItems['{{ $klasifikasiId }}'] = !expandedItems['{{ $klasifikasiId }}']"
                                            data-klasifikasi-id="{{ $klasifikasiId }}">
                                            <td class="px-6 py-2 text-gray-700">
                                                <i class="fas mr-2 text-gray-400" 
                                                   :class="expandedItems['{{ $klasifikasiId }}'] ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                                                <span class="font-medium">{{ $item['label'] }}</span>
                                                <span class="text-xs text-gray-400 ml-2">({{ $detailRows->count() }} transaksi)</span>
                                            </td>
                                            <td class="px-4 py-2 text-right {{ $itemNet >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                                @if ($itemNet >= 0)
                                                    {{ number_format($itemNet, 0, ',', '.') }}
                                                @else
                                                    ({{ number_format(abs($itemNet), 0, ',', '.') }})
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 w-32"></td>
                                            <td class="px-4 py-2 w-16"></td>
                                        </tr>
                                        
                                        {{-- Detail Rows (Hidden by default) --}}
                                        <tr x-show="expandedItems['{{ $klasifikasiId }}']" x-cloak>
                                            <td colspan="4" class="p-0">
                                                <table class="w-full bg-gray-50 text-xs">
                                                    <thead class="bg-gray-200">
                                                        <tr>
                                                            <th class="px-6 py-1 text-left w-24">Tanggal</th>
                                                            <th class="px-2 py-1 text-left w-32">Source</th>
                                                            <th class="px-2 py-1 text-left">Lawan Akun</th>
                                                            <th class="px-2 py-1 text-left">Keterangan</th>
                                                            <th class="px-2 py-1 text-right w-24">Cash In</th>
                                                            <th class="px-2 py-1 text-right w-24">Cash Out</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($detailRows as $detail)
                                                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                                                <td class="px-6 py-1">{{ \Carbon\Carbon::parse($detail['tanggal'])->format('d/m/Y') }}</td>
                                                                <td class="px-2 py-1 text-blue-600">{{ $detail['source'] }}</td>
                                                                <td class="px-2 py-1">{{ $detail['nama_lawan'] ?? '' }}</td>
                                                                <td class="px-2 py-1 text-gray-500">{{ $detail['line_comment'] ?: $detail['keterangan'] }}</td>
                                                                <td class="px-2 py-1 text-right text-green-600">
                                                                    @if($detail['cash_in'] > 0) {{ number_format($detail['cash_in'], 0, ',', '.') }} @endif
                                                                </td>
                                                                <td class="px-2 py-1 text-right text-red-600">
                                                                    @if($detail['cash_out'] > 0) {{ number_format($detail['cash_out'], 0, ',', '.') }} @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    @endforeach
                                    
                                    {{-- Subtotal Aktivitas --}}
                                    <tr class="bg-gray-100 font-semibold">
                                        <td class="px-6 py-2 text-gray-800" colspan="2">
                                            <span class="ml-4">Arus Kas Bersih dari {{ $akt['label'] }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-right {{ $aktNetFlow >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                            @if ($aktNetFlow >= 0)
                                                {{ number_format($aktNetFlow, 0, ',', '.') }}
                                            @else
                                                ({{ number_format(abs($aktNetFlow), 0, ',', '.') }})
                                            @endif
                                        </td>
                                        <td class="px-4 py-2"></td>
                                    </tr>
                                    
                                    {{-- Spacer --}}
                                    <tr><td colspan="4" class="py-2"></td></tr>
                                @endforeach
                                
                                {{-- Grand Total --}}
                                <tr class="bg-green-100 font-bold border-t-2 border-green-300">
                                    <td class="px-4 py-3 text-green-800" colspan="2">KENAIKAN/PENURUNAN BERSIH KAS</td>
                                    <td class="px-4 py-3 text-right {{ $netCashFlow >= 0 ? 'text-green-800' : 'text-red-800' }}">
                                        @if ($netCashFlow >= 0)
                                            {{ number_format($netCashFlow, 0, ',', '.') }}
                                        @else
                                            ({{ number_format(abs($netCashFlow), 0, ',', '.') }})
                                        @endif
                                    </td>
                                    <td class="px-4 py-3"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Summary Box --}}
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg border">
                        <h4 class="font-semibold text-gray-700 mb-3">Ringkasan Arus Kas</h4>
                        <div class="grid grid-cols-3 gap-4 text-sm">
                            @foreach ($aktivitasData as $aktKey => $akt)
                                @php $aktNetFlow = $akt['cash_in'] - $akt['cash_out']; @endphp
                                <div class="p-3 rounded {{ $aktNetFlow >= 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }} border">
                                    <div class="text-xs text-gray-600">{{ $aktKey }}</div>
                                    <div class="font-bold {{ $aktNetFlow >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                        Rp {{ number_format($aktNetFlow, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection

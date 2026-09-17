@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div>
            <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600">
                <li><a
                        href="{{ route('income_statement.export', ['start_date' => $tanggalAwal, 'end_date' => $tanggalAkhir, 'format' => 'excel']) }}">Export
                        to Excel</a></li>
                <li><a
                        href="{{ route('income_statement.export', ['start_date' => $tanggalAwal, 'end_date' => $tanggalAkhir, 'format' => 'pdf']) }}">Export
                        to PDF</a></li>
                {{-- <li><a href="#pricing" class="tab-link">Print</a></li> --}}
                <li><a onclick="document.getElementById('fileModify').classList.toggle('hidden')" class="tab-link">Modify</a>
                </li>
                <li><a href="#linked" class="tab-link"></a></li>
            </ul>
        </div>
        <div id="fileModify"
            class="fixed inset-0 z-50 hidden bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-7xl">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-file-alt mr-2 text-blue-400"></i> File Aksi
                </h3>
                <div class="space-y-3 text-sm text-gray-700">
                    <form method="GET" action="{{ route('income_statement.income_statement_report') }}"
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">

                        {{-- tanggal awal --}}
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                                Awal</label>
                            <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        {{-- Tanggal Akhir --}}
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                            <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                required>
                        </div>

                        {{-- Tombol Filter --}}
                        <div class="sm:col-span-3 flex items-center gap-2 mt-4">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-md shadow hover:bg-blue-700">
                                <i class="fas fa-filter mr-2"></i> Filter
                            </button>

                            <a href=""
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
        <h1 class="text-2xl font-bold mb-2">
            {{ $siteTitle }}
        </h1>
        <h3 class="text-2xl font-bold mb-4">LAPORAN LABA RUGI</h3>
        <p class="text-gray-600 mb-6">
            Periode: {{ \Carbon\Carbon::parse($tanggalAwal)->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d M Y') }}
        </p>

        {{-- Tabel Laporan Laba Rugi dengan Hierarki --}}
        <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
            <table class="w-full text-sm" id="incomeStatementTable">
                <thead>
                    <tr class="bg-gradient-to-r from-slate-700 to-slate-800 text-white">
                        <th class="text-left py-3 px-4 font-semibold w-1/2">KETERANGAN</th>
                        <th class="text-right py-3 px-4 font-semibold w-1/8">SUB ACCOUNT</th>
                        <th class="text-right py-3 px-4 font-semibold w-1/8">ACCOUNT</th>
                        <th class="text-right py-3 px-4 font-semibold w-1/4">GROUP ACCOUNT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    {{-- =======================
                         Bagian PENDAPATAN
                    ======================== --}}
                    @if (!empty($groupsPendapatan))
                        {{-- Header PENDAPATAN --}}
                        <tr class="bg-emerald-600 text-white">
                            <td colspan="4" class="py-2.5 px-4 font-bold text-base tracking-wide">PENDAPATAN</td>
                        </tr>
                        @foreach ($groupsPendapatan as $groupIndex => $group)
                            {{-- GROUP ACCOUNT row - Clickable untuk expand/collapse --}}
                            <tr class="bg-emerald-50 hover:bg-emerald-100 transition-colors cursor-pointer group-toggle border-l-4 border-emerald-400"
                                 data-target="pendapatan-group-{{ $groupIndex }}">
                                <td class="py-2.5 px-4">
                                    <span class="flex items-center font-semibold text-emerald-800">
                                        <span class="toggle-icon mr-2 text-emerald-600 w-5 h-5 flex items-center justify-center bg-emerald-200 rounded text-xs font-bold">+</span>
                                        {{ $group['group'] }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-right"></td>
                                <td class="py-2.5 px-4 text-right"></td>
                                <td class="py-2.5 px-4 text-right font-bold text-emerald-800">{{ number_format($group['saldo_group'], 2, ',', '.') }}</td>
                            </tr>
                            
                            {{-- ACCOUNT level rows - Default HIDDEN (collapsed) --}}
                            @foreach ($group['accounts'] as $accountIndex => $account)
                                @php
                                    $hasSubAccounts = !empty($account['sub_accounts']);
                                    $accountId = "pendapatan-account-{$groupIndex}-{$accountIndex}";
                                @endphp
                                
                                @if ($hasSubAccounts)
                                    {{-- ACCOUNT dengan SUB ACCOUNTS - bisa di-expand --}}
                                    <tr class="hidden pendapatan-group-{{ $groupIndex }} bg-white hover:bg-gray-50 transition-colors cursor-pointer account-toggle border-l-4 border-emerald-200"
                                         data-target="{{ $accountId }}">
                                        <td class="py-2 px-4 pl-10">
                                            <span class="flex items-center text-gray-700">
                                                <span class="toggle-icon mr-2 text-gray-400 w-4 h-4 flex items-center justify-center bg-gray-100 rounded text-xs">+</span>
                                                {{ $account['nama_akun'] }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-4 text-right"></td>
                                        <td class="py-2 px-4 text-right font-medium text-gray-700">{{ number_format($account['saldo_account'], 2, ',', '.') }}</td>
                                        <td class="py-2 px-4 text-right"></td>
                                    </tr>
                                    
                                    {{-- SUB ACCOUNTS rows - Default HIDDEN --}}
                                    @foreach ($account['sub_accounts'] as $sub)
                                        <tr class="hidden {{ $accountId }} bg-gray-50 hover:bg-gray-100 transition-colors border-l-4 border-gray-200">
                                            <td class="py-1.5 px-4 pl-16">
                                                <a href="{{ route('buku_besar.buku_besar_report', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'selected_accounts' => $sub['kode_akun']]) }}"
                                                   class="text-gray-600 hover:text-blue-600 hover:underline">
                                                    {{ $sub['kode_akun'] }} - {{ $sub['nama_akun'] }}
                                                </a>
                                            </td>
                                            <td class="py-1.5 px-4 text-right text-gray-600">{{ number_format($sub['saldo'], 2, ',', '.') }}</td>
                                            <td class="py-1.5 px-4 text-right"></td>
                                            <td class="py-1.5 px-4 text-right"></td>
                                        </tr>
                                    @endforeach
                                @else
                                    {{-- ACCOUNT tanpa SUB ACCOUNTS --}}
                                    <tr class="hidden pendapatan-group-{{ $groupIndex }} bg-white hover:bg-gray-50 transition-colors border-l-4 border-emerald-200">
                                        <td class="py-2 px-4 pl-10">
                                            <a href="{{ route('buku_besar.buku_besar_report', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'selected_accounts' => $account['kode_akun']]) }}"
                                               class="text-gray-700 hover:text-blue-600 hover:underline">
                                                {{ $account['kode_akun'] }} - {{ $account['nama_akun'] }}
                                            </a>
                                        </td>
                                        <td class="py-2 px-4 text-right"></td>
                                        <td class="py-2 px-4 text-right font-medium text-gray-700">{{ number_format($account['saldo_account'], 2, ',', '.') }}</td>
                                        <td class="py-2 px-4 text-right"></td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach

                        {{-- Total PENDAPATAN --}}
                        <tr class="bg-emerald-700 text-white">
                            <td class="py-3 px-4 font-bold">TOTAL PENDAPATAN</td>
                            <td class="py-3 px-4 text-right"></td>
                            <td class="py-3 px-4 text-right"></td>
                            <td class="py-3 px-4 text-right font-bold text-lg">{{ number_format($totalPendapatan, 2, ',', '.') }}</td>
                        </tr>
                    @endif

                    {{-- Spacer row --}}
                    <tr class="h-4 bg-gray-100"><td colspan="4"></td></tr>

                    {{-- ===================
                         Bagian BEBAN
                    ==================== --}}
                    @if (!empty($groupsBeban))
                        {{-- Header BEBAN --}}
                        <tr class="bg-rose-600 text-white">
                            <td colspan="4" class="py-2.5 px-4 font-bold text-base tracking-wide">BEBAN</td>
                        </tr>
                        @foreach ($groupsBeban as $groupIndex => $group)
                            {{-- GROUP ACCOUNT row - Clickable untuk expand/collapse --}}
                            <tr class="bg-rose-50 hover:bg-rose-100 transition-colors cursor-pointer group-toggle border-l-4 border-rose-400"
                                 data-target="beban-group-{{ $groupIndex }}">
                                <td class="py-2.5 px-4">
                                    <span class="flex items-center font-semibold text-rose-800">
                                        <span class="toggle-icon mr-2 text-rose-600 w-5 h-5 flex items-center justify-center bg-rose-200 rounded text-xs font-bold">+</span>
                                        {{ $group['group'] }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-right"></td>
                                <td class="py-2.5 px-4 text-right"></td>
                                <td class="py-2.5 px-4 text-right font-bold text-rose-800">{{ number_format($group['saldo_group'], 2, ',', '.') }}</td>
                            </tr>
                            
                            {{-- ACCOUNT level rows - Default HIDDEN (collapsed) --}}
                            @foreach ($group['accounts'] as $accountIndex => $account)
                                @php
                                    $hasSubAccounts = !empty($account['sub_accounts']);
                                    $accountId = "beban-account-{$groupIndex}-{$accountIndex}";
                                @endphp
                                
                                @if ($hasSubAccounts)
                                    {{-- ACCOUNT dengan SUB ACCOUNTS - bisa di-expand --}}
                                    <tr class="hidden beban-group-{{ $groupIndex }} bg-white hover:bg-gray-50 transition-colors cursor-pointer account-toggle border-l-4 border-rose-200"
                                         data-target="{{ $accountId }}">
                                        <td class="py-2 px-4 pl-10">
                                            <span class="flex items-center text-gray-700">
                                                <span class="toggle-icon mr-2 text-gray-400 w-4 h-4 flex items-center justify-center bg-gray-100 rounded text-xs">+</span>
                                                {{ $account['nama_akun'] }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-4 text-right"></td>
                                        <td class="py-2 px-4 text-right font-medium text-gray-700">{{ number_format($account['saldo_account'], 2, ',', '.') }}</td>
                                        <td class="py-2 px-4 text-right"></td>
                                    </tr>
                                    
                                    {{-- SUB ACCOUNTS rows - Default HIDDEN --}}
                                    @foreach ($account['sub_accounts'] as $sub)
                                        <tr class="hidden {{ $accountId }} bg-gray-50 hover:bg-gray-100 transition-colors border-l-4 border-gray-200">
                                            <td class="py-1.5 px-4 pl-16">
                                                <a href="{{ route('buku_besar.buku_besar_report', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'selected_accounts' => $sub['kode_akun']]) }}"
                                                   class="text-gray-600 hover:text-blue-600 hover:underline">
                                                    {{ $sub['kode_akun'] }} - {{ $sub['nama_akun'] }}
                                                </a>
                                            </td>
                                            <td class="py-1.5 px-4 text-right text-gray-600">{{ number_format($sub['saldo'], 2, ',', '.') }}</td>
                                            <td class="py-1.5 px-4 text-right"></td>
                                            <td class="py-1.5 px-4 text-right"></td>
                                        </tr>
                                    @endforeach
                                @else
                                    {{-- ACCOUNT tanpa SUB ACCOUNTS --}}
                                    <tr class="hidden beban-group-{{ $groupIndex }} bg-white hover:bg-gray-50 transition-colors border-l-4 border-rose-200">
                                        <td class="py-2 px-4 pl-10">
                                            <a href="{{ route('buku_besar.buku_besar_report', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'selected_accounts' => $account['kode_akun']]) }}"
                                               class="text-gray-700 hover:text-blue-600 hover:underline">
                                                {{ $account['kode_akun'] }} - {{ $account['nama_akun'] }}
                                            </a>
                                        </td>
                                        <td class="py-2 px-4 text-right"></td>
                                        <td class="py-2 px-4 text-right font-medium text-gray-700">{{ number_format($account['saldo_account'], 2, ',', '.') }}</td>
                                        <td class="py-2 px-4 text-right"></td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach

                        {{-- Total BEBAN --}}
                        <tr class="bg-rose-700 text-white">
                            <td class="py-3 px-4 font-bold">TOTAL BEBAN</td>
                            <td class="py-3 px-4 text-right"></td>
                            <td class="py-3 px-4 text-right"></td>
                            <td class="py-3 px-4 text-right font-bold text-lg">{{ number_format($totalBeban, 2, ',', '.') }}</td>
                        </tr>
                    @endif

                    {{-- Spacer row --}}
                    <tr class="h-4 bg-gray-100"><td colspan="4"></td></tr>

                    {{-- ==========================
                         RINGKASAN AKHIR
                    =========================== --}}
                    <tr class="bg-slate-100 border-t-2 border-slate-300">
                        <td class="py-3 px-4 font-bold text-slate-700">LABA SEBELUM PAJAK PENGHASILAN</td>
                        <td class="py-3 px-4 text-right"></td>
                        <td class="py-3 px-4 text-right"></td>
                        <td class="py-3 px-4 text-right font-bold text-slate-800 text-lg">{{ number_format($labaSebelumPajak, 2, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-amber-50 border-l-4 border-amber-400">
                        <td class="py-3 px-4 font-semibold text-amber-800">BEBAN PAJAK PENGHASILAN</td>
                        <td class="py-3 px-4 text-right"></td>
                        <td class="py-3 px-4 text-right"></td>
                        <td class="py-3 px-4 text-right font-bold text-amber-800">{{ number_format($bebanPajak, 2, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-gradient-to-r from-blue-700 to-indigo-800 text-white">
                        <td class="py-4 px-4 font-bold text-base">LABA BERSIH SETELAH PAJAK PENGHASILAN</td>
                        <td class="py-4 px-4 text-right"></td>
                        <td class="py-4 px-4 text-right"></td>
                        <td class="py-4 px-4 text-right font-bold text-xl">{{ number_format($labaSetelahPajak, 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Legend --}}
        <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-500">
            <span class="flex items-center"><span class="w-3 h-3 bg-emerald-200 rounded mr-1"></span> Pendapatan</span>
            <span class="flex items-center"><span class="w-3 h-3 bg-rose-200 rounded mr-1"></span> Beban</span>
            <span class="flex items-center"><i class="fas fa-plus-square text-gray-400 mr-1"></i> Klik untuk expand/collapse</span>
        </div>
    </div>

    {{-- JavaScript untuk Toggle Expand/Collapse --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle GROUP ACCOUNT
            document.querySelectorAll('.group-toggle').forEach(function(el) {
                el.addEventListener('click', function(e) {
                    // Prevent toggle if clicking on a link
                    if (e.target.tagName === 'A') return;
                    
                    const targetClass = this.getAttribute('data-target');
                    const targets = document.querySelectorAll('.' + targetClass);
                    const icon = this.querySelector('.toggle-icon');
                    
                    targets.forEach(function(target) {
                        if (target.classList.contains('hidden')) {
                            target.classList.remove('hidden');
                        } else {
                            target.classList.add('hidden');
                            // Also hide any sub-accounts that might be open
                            const subTarget = target.getAttribute('data-target');
                            if (subTarget) {
                                document.querySelectorAll('.' + subTarget).forEach(function(sub) {
                                    sub.classList.add('hidden');
                                });
                                const subIcon = target.querySelector('.toggle-icon');
                                if (subIcon) subIcon.textContent = '+';
                            }
                        }
                    });
                    
                    icon.textContent = icon.textContent === '+' ? '-' : '+';
                });
            });

            // Toggle ACCOUNT (untuk yang punya SUB ACCOUNTS)
            document.querySelectorAll('.account-toggle').forEach(function(el) {
                el.addEventListener('click', function(e) {
                    // Prevent toggle if clicking on a link
                    if (e.target.tagName === 'A') return;
                    
                    const targetClass = this.getAttribute('data-target');
                    const targets = document.querySelectorAll('.' + targetClass);
                    const icon = this.querySelector('.toggle-icon');
                    
                    targets.forEach(function(target) {
                        if (target.classList.contains('hidden')) {
                            target.classList.remove('hidden');
                        } else {
                            target.classList.add('hidden');
                        }
                    });
                    
                    icon.textContent = icon.textContent === '+' ? '-' : '+';
                });
            });
        });
    </script>
@endsection

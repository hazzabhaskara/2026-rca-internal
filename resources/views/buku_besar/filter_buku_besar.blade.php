@extends('layouts.app')

@section('content')
    {{-- Alpine.js --}}
    <script src="//unpkg.com/alpinejs" defer></script>

    {{-- Notifikasi Sukses --}}
    @if (session('success'))
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div
                class="flex items-start bg-green-50 border-l-4 border-green-600 rounded-lg shadow-md p-4 space-x-3 animate-fade-in-down">
                <i class="fas fa-check-circle text-green-600 text-xl mt-1"></i>
                <div>
                    <p class="font-semibold text-green-800">Berhasil!</p>
                    <p class="text-green-700 text-sm">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('not_found'))
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow">
                <div class="flex items-center">
                    <i class="fas fa-info-circle mr-2 text-red-600"></i>
                    <p class="text-sm">{{ session('not_found') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Konten Utama --}}
    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-200 shadow-md rounded-xl p-6">

                <h2 class="text-xl font-semibold text-gray-800 mb-4">Filter Transaksi</h2>

                <form method="GET" action="{{ route('buku_besar.buku_besar_report') }}"
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">

                    <div>
                        <label for="periode" class="block text-sm font-semibold text-gray-700 mb-1">Periode Buku</label>
                        <select name="periode_buku" id="periode"
                            class="w-3/6 border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">---Pilih---</option>
                            @foreach ($tahun_buku as $item)
                                <option value="{{ $item->id }}" data-tahun="{{ trim($item->tahun) }}">
                                    {{ $item->tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal Awal --}}
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Awal</label>
                        <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>

                    {{-- Tanggal Akhir --}}
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                        <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>

                    {{-- Filter Departemen --}}
                    <div class="sm:col-span-1">
                        <label for="departemen_id" class="block text-sm font-medium text-gray-700 mb-1">Departemen</label>
                        <select name="departemen_id" id="departemen_id"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="" {{ empty(request('departemen_id')) || request('departemen_id') === 'all' ? 'selected' : '' }}>Semua Departemen (Konsolidasi)</option>
                            <option value="unassigned" {{ request('departemen_id') === 'unassigned' ? 'selected' : '' }}>Non-Departemen / Unassigned</option>
                            @foreach ($departemen as $dep)
                                <option value="{{ $dep->id }}" {{ (string)request('departemen_id') === (string)$dep->id ? 'selected' : '' }}>
                                    {{ $dep->kode }} - {{ $dep->deskripsi }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(request()->filled('back_url'))
                        <input type="hidden" name="back_url" value="{{ request('back_url') }}">
                    @endif

                    {{-- Select Account --}}
                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Account</label>

                        {{-- Input hidden untuk kirim array akun terpilih --}}
                        <input type="hidden" name="selected_accounts" id="selected_accounts" value="{{ request('selected_accounts') }}">

                        <!-- Search Input -->
                        <input type="text" id="search-account" placeholder="Cari akun..."
                            class="border p-2 rounded mb-3 w-full" />

                        <!-- Table Container -->
                        <div class="border rounded shadow-sm max-h-60 overflow-y-auto">
                            <table class="min-w-full text-sm text-left text-gray-700" id="account-table">
                                <thead class="bg-gray-100 sticky top-0">
                                    <tr>
                                        <th class="px-2 py-1">
                                            <input type="checkbox" id="select-all" class="form-checkbox">
                                        </th>
                                        <th class="px-2 py-1">Account</th>
                                    </tr>
                                </thead>
                                @php
                                    $selectedAccountCodesList = array_values(array_filter(array_map(function($item) {
                                        $p = explode(' - ', $item);
                                        return trim($p[0] ?? '');
                                    }, explode(',', request('selected_accounts', '')))));
                                @endphp
                                <tbody>
                                    @foreach ($account as $akun)
                                        <tr class="hover:bg-gray-50" data-level="{{ $akun->level_akun }}"
                                            data-tipe="{{ strtolower($akun->tipe_akun) }}">
                                            <td class="px-2 py-1">
                                                <input type="checkbox" class="account-checkbox form-checkbox"
                                                    value="{{ $akun->kode_akun }}"
                                                    {{ in_array($akun->kode_akun, $selectedAccountCodesList) ? 'checked' : '' }}>
                                            </td>
                                            <td class="px-2 py-1">
                                                {{ $akun->kode_akun }} - {{ $akun->nama_akun }}
                                                {{-- <span class="text-xs text-gray-400">({{ $akun->level_akun }},
                                                    {{ $akun->tipe_akun }})</span> --}}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Quick Date Presets --}}
                    <div class="sm:col-span-2 md:col-span-3">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Preset Tanggal Cepat</label>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" onclick="setDatePreset('today')" class="px-2.5 py-1 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition">Hari Ini</button>
                            <button type="button" onclick="setDatePreset('this_month')" class="px-2.5 py-1 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition">Bulan Ini</button>
                            <button type="button" onclick="setDatePreset('last_month')" class="px-2.5 py-1 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition">Bulan Lalu</button>
                            <button type="button" onclick="setDatePreset('this_quarter')" class="px-2.5 py-1 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition">Triwulan Ini</button>
                            <button type="button" onclick="setDatePreset('ytd')" class="px-2.5 py-1 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition">Tahun Berjalan (YTD)</button>
                            <button type="button" onclick="setDatePreset('last_year')" class="px-2.5 py-1 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition">Tahun Lalu</button>
                        </div>
                    </div>

                    {{-- Sort By --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                        <div class="space-y-1">
                            <label class="inline-flex items-center">
                                <input type="radio" name="sort_by" value="date"
                                    class="text-blue-600 focus:ring-blue-500"
                                    {{ request('sort_by', 'date') == 'date' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm">Date</span>
                            </label>
                            <br>
                            <label class="inline-flex items-center">
                                <input type="radio" name="sort_by" value="transaction_number"
                                    class="text-blue-600 focus:ring-blue-500"
                                    {{ request('sort_by') == 'transaction_number' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm">Transaction Number</span>
                            </label>
                        </div>
                    </div>

                    {{-- Journal Entry Options --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">For General Journal Entries</label>
                        <div class="space-y-1">
                            <label class="inline-flex items-center">
                                <input type="radio" name="show_comment" value="transaction_comment"
                                    class="text-blue-600 focus:ring-blue-500"
                                    {{ request('show_comment') == 'transaction_comment' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm">Show Transaction Comment</span>
                            </label>
                            <br>
                            <label class="inline-flex items-center">
                                <input type="radio" name="show_comment" value="line_comment"
                                    class="text-blue-600 focus:ring-blue-500"
                                    {{ request('show_comment', 'line_comment') == 'line_comment' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm">Show Line Comment</span>
                            </label>
                        </div>
                    </div>

                    {{-- Zero-balance Toggle Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Integritas Laporan</label>
                        <div class="mt-1">
                            <input type="hidden" name="hide_zero" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="hide_zero" value="1"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                    {{ request('hide_zero', '1') == '1' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Sembunyikan Akun Saldo Nol & Tanpa Mutasi</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Jika tidak dicentang, seluruh akun tetap ditampilkan walau saldo Rp 0.</p>
                        </div>
                    </div>

                    {{-- Kolom Lawan Transaksi Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kolom Lawan Transaksi</label>
                        <div class="mt-1">
                            <input type="hidden" name="show_lawan" value="0">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="show_lawan" value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    {{ request('show_lawan', '0') == '1' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Tampilkan Kolom Lawan Transaksi</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Menampilkan nama akun penyeimbang (offset) dari transaksi.</p>
                        </div>
                    </div>

                    {{-- Tombol Filter --}}
                    <div class="sm:col-span-3 flex items-center gap-2 mt-4">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-md shadow hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i> Ok
                        </button>

                        <a href=""
                            class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 font-medium text-sm rounded-md hover:bg-gray-200">
                            <i class="fas fa-undo mr-2"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const periodeSelect = document.getElementById('periode');
            const tanggalInput = document.getElementById('start_date');
            const tanggalAkhir = document.getElementById('end_date');

            periodeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const tahun = selectedOption.getAttribute('data-tahun')?.trim();

                if (/^\d{4}$/.test(tahun)) {
                    // Set batas minimal & maksimal
                    tanggalInput.min = `${tahun}-01-01`;
                    tanggalInput.max = `${tahun}-12-31`;
                    tanggalAkhir.min = `${tahun}-01-01`;
                    tanggalAkhir.max = `${tahun}-12-31`;

                    // Isi otomatis awal & akhir tahun
                    tanggalInput.value = `${tahun}-01-01`;
                    tanggalAkhir.value = `${tahun}-12-31`;
                } else {
                    // Reset kalau user pilih "---Pilih---"
                    tanggalInput.min = '';
                    tanggalInput.max = '';
                    tanggalAkhir.min = '';
                    tanggalAkhir.max = '';
                    tanggalInput.value = '';
                    tanggalAkhir.value = '';
                }
            });
        });
    </script>
    {{-- Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.account-checkbox');
            const hiddenInput = document.getElementById('selected_accounts');

            // Toggle semua checkbox
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateSelectedAccounts();
            });

            // Saat checkbox akun diklik
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const row = cb.closest('tr');
                    const level = row.dataset.level;
                    const tipe = row.dataset.tipe;

                    // Kalau akun ini level Header → toggle semua akun dengan tipe sama
                    if (level && level.toLowerCase() === 'header') {
                        const allSameType = document.querySelectorAll(
                            `#account-table tbody tr[data-tipe="${tipe}"] .account-checkbox`
                        );
                        allSameType.forEach(cb2 => {
                            cb2.checked = cb.checked;
                        });
                    }

                    updateSelectedAccounts();
                });
            });

            function updateSelectedAccounts() {
                const selected = [];
                checkboxes.forEach(cb => {
                    if (cb.checked) selected.push(cb.value);
                });
                hiddenInput.value = selected.join(',');
            }
        });

        // Search/filter functionality
        document.getElementById('search-account').addEventListener('keyup', function() {
            var keyword = this.value.toLowerCase();
            var rows = document.querySelectorAll('#account-table tbody tr');

            rows.forEach(function(row) {
                var text = row.innerText.toLowerCase();
                row.style.display = text.includes(keyword) ? '' : 'none';
            });
        });

        function setDatePreset(preset) {
            const today = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const formatDate = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

            let start, end;
            const y = today.getFullYear();
            const m = today.getMonth(); // 0-indexed

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
                const startDateEl = document.getElementById('start_date');
                const endDateEl = document.getElementById('end_date');
                if (startDateEl) startDateEl.value = start;
                if (endDateEl) endDateEl.value = end;
            }
        }
    </script>
@endsection

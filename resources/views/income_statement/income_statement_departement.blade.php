@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4">
        <div>
            <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600">
                <li><a
                        href="{{ route('income_statement_departement.export', ['start_date' => $start_date, 'end_date' => $end_date, 'format' => 'excel']) }}">Export
                        to Excel</a></li>
                <li><a
                        href="{{ route('income_statement_departement.export', ['start_date' => $start_date, 'end_date' => $end_date, 'format' => 'pdf']) }}">Export
                        to PDF</a></li>
                <li><a onclick="document.getElementById('fileModify').classList.toggle('hidden')" class="tab-link">Modify</a>
                </li>
            </ul>
        </div>

        {{-- === Modal Filter === --}}
        <div id="fileModify"
            class="fixed inset-0 z-50 hidden bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-7xl">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-file-alt mr-2 text-blue-400"></i> File Aksi
                </h3>
                <div class="space-y-3 text-sm text-gray-700">
                    <form method="GET" action="{{ route('income_statement.income_statement_departement') }}"
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">

                        {{-- tanggal awal --}}
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                                Awal</label>
                            <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        {{-- tanggal akhir --}}
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                            <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                required>
                        </div>

                        {{-- pilih departemen --}}
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Departemen</label>
                            <input type="hidden" name="selected_departemens" id="selected_departemens">

                            <input type="text" id="search-account" placeholder="Cari departemen..."
                                class="border p-2 rounded mb-3 w-full" />

                            <div class="border rounded shadow-sm max-h-60 overflow-y-auto">
                                @php
                                    $selectedDepartemens = explode(',', request('selected_departemens', ''));
                                @endphp
                                <table class="min-w-full text-sm text-left text-gray-700" id="account-table">
                                    <thead class="bg-gray-100 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-2"><input type="checkbox" id="select-all"
                                                    class="form-checkbox"></th>
                                            <th class="px-4 py-2">Departemen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($allDepartemens as $depart)
                                            <tr>
                                                <td class="px-4 py-2">
                                                    <input type="checkbox" class="account-checkbox form-checkbox"
                                                        value="{{ $depart->id }}"
                                                        {{ in_array($depart->id, $selectedDepartemens) ? 'checked' : '' }}>
                                                </td>
                                                <td class="px-4 py-2">{{ $depart->kode }} - {{ $depart->deskripsi }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

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

        {{-- === Report === --}}
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold">Departmental Income Statement</h1>
            <p class="text-gray-600">Periode: {{ $start_date }} s/d {{ $end_date }}</p>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="table-auto w-full">
                <thead>
                    <tr class="text-center">
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Total</th>
                        @foreach ($filteredDepartemens as $dept)
                            <th>{{ $dept->deskripsi }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incomeStatement as $row)
                        <tr>
                            <td class="text-center">{{ $row['kode_akun'] }}</td>
                            <td class="text-center">{{ $row['nama_akun'] }}</td>
                            <td class="text-right">
                                <a href="{{ route('buku_besar.buku_besar_report', [
                                    'start_date' => $start_date,
                                    'end_date' => $end_date,
                                    'selected_accounts' => $row['kode_akun'],
                                    'departemen_id' => '',
                                    'back_url' => request()->fullUrl(),
                                ]) }}" class="text-slate-800 hover:text-indigo-600 hover:underline font-semibold" title="Buku Besar Total Konsolidasi: {{ $row['kode_akun'] }}">
                                    {{ number_format($row['saldo'], 2, ',', '.') }}
                                </a>
                            </td>
                            @foreach ($filteredDepartemens as $dept)
                                <td class="text-right">
                                    <a href="{{ route('buku_besar.buku_besar_report', [
                                        'start_date' => $start_date,
                                        'end_date' => $end_date,
                                        'selected_accounts' => $row['kode_akun'],
                                        'departemen_id' => $dept->id,
                                        'back_url' => request()->fullUrl(),
                                    ]) }}" class="text-indigo-600 hover:text-indigo-900 hover:underline font-medium" title="Buku Besar {{ $dept->deskripsi }}: {{ $row['kode_akun'] }}">
                                        {{ number_format($row['per_departemen'][$dept->id] ?? 0, 2, ',', '.') }}
                                    </a>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + count($filteredDepartemens) }}" class="text-center py-4 text-gray-500">
                                @if(request()->filled('selected_accounts'))
                                    Tidak ada mutasi transaksi untuk akun 
                                    <a href="{{ route('buku_besar.buku_besar_report', [
                                        'start_date' => $start_date,
                                        'end_date' => $end_date,
                                        'selected_accounts' => request('selected_accounts'),
                                        'departemen_id' => '',
                                        'back_url' => request()->fullUrl(),
                                    ]) }}" class="text-indigo-600 hover:text-indigo-900 underline font-semibold">
                                        {{ request('selected_accounts') }}
                                    </a> pada periode ini (lihat di Buku Besar).
                                @else
                                    Tidak ada data untuk ditampilkan pada periode ini.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-100 font-semibold">
                    <tr>
                        <td colspan="2" class="text-center">Total Pendapatan</td>
                        <td class="text-right">{{ number_format($totalPendapatan, 2, ',', '.') }}</td>
                        @foreach ($filteredDepartemens as $dept)
                            <td class="text-right">
                                {{ number_format(
                                    collect($incomeStatement)->where('tipe_akun', 'Pendapatan')->sum(fn($r) => $r['per_departemen'][$dept->id] ?? 0),
                                    2,
                                    ',',
                                    '.',
                                ) }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="2" class="text-center">Total Beban</td>
                        <td class="text-right">{{ number_format($totalBeban, 2, ',', '.') }}</td>
                        @foreach ($filteredDepartemens as $dept)
                            <td class="text-right">
                                {{ number_format(
                                    collect($incomeStatement)->where('tipe_akun', 'Beban')->sum(fn($r) => $r['per_departemen'][$dept->id] ?? 0),
                                    2,
                                    ',',
                                    '.',
                                ) }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="2" class="text-center">Laba Bersih</td>
                        <td class="text-right">{{ number_format($labaBersih, 2, ',', '.') }}</td>
                        @foreach ($filteredDepartemens as $dept)
                            <td class="text-right">
                                {{ number_format(
                                    collect($incomeStatement)->where('tipe_akun', 'Pendapatan')->sum(fn($r) => $r['per_departemen'][$dept->id] ?? 0) -
                                        collect($incomeStatement)->where('tipe_akun', 'Beban')->sum(fn($r) => $r['per_departemen'][$dept->id] ?? 0),
                                    2,
                                    ',',
                                    '.',
                                ) }}
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deptCheckboxes = document.querySelectorAll('.account-checkbox');
            const hiddenDeptInput = document.getElementById('selected_departemens');
            const selectAll = document.getElementById('select-all');

            function updateSelectedDepts() {
                const selected = [];
                deptCheckboxes.forEach(cb => {
                    if (cb.checked) selected.push(cb.value);
                });
                hiddenDeptInput.value = selected.join(',');
            }

            deptCheckboxes.forEach(cb => cb.addEventListener('change', updateSelectedDepts));

            if (selectAll) {
                selectAll.addEventListener('change', function(e) {
                    deptCheckboxes.forEach(cb => cb.checked = e.target.checked);
                    updateSelectedDepts();
                });
            }

            updateSelectedDepts();
        });

        document.getElementById('search-account').addEventListener('keyup', function() {
            var keyword = this.value.toLowerCase();
            var rows = document.querySelectorAll('#account-table tbody tr');
            rows.forEach(function(row) {
                var text = row.innerText.toLowerCase();
                row.style.display = text.includes(keyword) ? '' : 'none';
            });
        });
    </script>
@endsection

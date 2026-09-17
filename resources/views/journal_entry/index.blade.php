@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                @php
                    $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                @endphp

                <div class="sticky top-0 z-20 px-6 py-5 border-b border-gray-100 flex justify-between items-center"
                    style="background: {{ $themeColor }};">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-list mr-3 text-white text-xl"></i>
                        General Journal
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <!-- Filter Button -->
                        <button onclick="document.getElementById('filterPanel').classList.toggle('hidden')"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-filter text-gray-500 mr-2"></i> Filter
                        </button>
                        <!-- File Button -->
                        <button onclick="document.getElementById('fileModal').classList.remove('hidden')"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-file-export text-blue-500 mr-2"></i> File
                        </button>
                        @can('journal_entry.delete')
                            {{-- Dropdown Hapus --}}
                            <div class="relative" id="deleteDropdownWrapper">
                                <button type="button" id="deleteDropdownBtn"
                                    onclick="document.getElementById('deleteDropdownMenu').classList.toggle('hidden')"
                                    class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-red-300 bg-red-50 text-red-700 hover:bg-red-100 font-bold ml-2">
                                    <i class="fas fa-trash-alt mr-2"></i> Hapus
                                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
                                </button>
                                <div id="deleteDropdownMenu"
                                    class="hidden absolute right-0 mt-1 w-52 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                                    {{-- Hapus Semua --}}
                                    <button type="button"
                                        onclick="document.getElementById('deleteDropdownMenu').classList.add('hidden'); document.getElementById('modalHapusSemua').classList.remove('hidden')"
                                        class="w-full flex items-center px-4 py-2.5 text-sm text-red-700 hover:bg-red-50 rounded-t-lg">
                                        <i class="fas fa-trash mr-2 text-red-500"></i> Hapus Semua
                                    </button>
                                    <hr class="border-gray-100">
                                    {{-- Hapus Range --}}
                                    <button type="button"
                                        onclick="document.getElementById('deleteDropdownMenu').classList.add('hidden'); document.getElementById('modalHapusRange').classList.remove('hidden')"
                                        class="w-full flex items-center px-4 py-2.5 text-sm text-orange-700 hover:bg-orange-50 rounded-b-lg">
                                        <i class="fas fa-calendar-times mr-2 text-orange-500"></i> Hapus Range Tanggal
                                    </button>
                                </div>
                            </div>
                        @endcan
                        <!-- Add Button -->
                        {{-- <a href="{{ route('journal_entry') }}"
                            class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 font-semibold rounded-lg shadow hover:bg-gray-100 transition-all">
                            <i class="fas fa-plus mr-2"></i> Tambah Journal Entry
                        </a> --}}
                    </div>
                </div>

                <div id="filterPanel"
                    class="{{ request('search') || request('filter_tipe') ? '' : 'hidden' }} px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <form method="GET" action="{{ route('journal_entry.index') }}">
                        <div class="flex flex-wrap gap-4">
                            <!-- Search Input -->
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Cari level,nama, dan sifat"
                                    class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm w-64 shadow-sm" />
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-sm"></i>
                                </div>
                            </div>
                            {{-- Filter Bulan --}}
                            <select name="filter_bulan"
                                class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                                <option value="">Semua Bulan</option>
                                <option value="1" {{ request('filter_bulan') == '1' ? 'selected' : '' }}>Januari
                                </option>
                                <option value="2" {{ request('filter_bulan') == '2' ? 'selected' : '' }}>Februari
                                </option>
                                <option value="3" {{ request('filter_bulan') == '3' ? 'selected' : '' }}>Maret
                                </option>
                                <option value="4" {{ request('filter_bulan') == '4' ? 'selected' : '' }}>April
                                </option>
                                <option value="5" {{ request('filter_bulan') == '5' ? 'selected' : '' }}>Mei</option>
                                <option value="6" {{ request('filter_bulan') == '6' ? 'selected' : '' }}>Juni</option>
                                <option value="7" {{ request('filter_bulan') == '7' ? 'selected' : '' }}>Juli
                                </option>
                                <option value="8" {{ request('filter_bulan') == '8' ? 'selected' : '' }}>Agustus
                                </option>
                                <option value="9" {{ request('filter_bulan') == '9' ? 'selected' : '' }}>September
                                </option>
                                <option value="10" {{ request('filter_bulan') == '10' ? 'selected' : '' }}>Oktober
                                </option>
                                <option value="11" {{ request('filter_bulan') == '11' ? 'selected' : '' }}>November
                                </option>
                                <option value="12" {{ request('filter_bulan') == '12' ? 'selected' : '' }}>Desember
                                </option>
                            </select>

                            {{-- Filter Tahun --}}
                            <select name="filter_tahun"
                                class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                                <option value="">Semua Tahun</option>
                                @foreach ($tahunList as $tahun)
                                    <option value="{{ $tahun }}" {{ request('filter_tahun') == $tahun ? 'selected' : '' }}>
                                        {{ $tahun }}
                                    </option>
                                @endforeach
                            </select>


                            <!-- Tombol Filter -->
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded shadow-sm hover:bg-blue-600 text-sm">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>

                            <!-- Tombol Reset -->
                            <a href="{{ route('journal_entry.index') }}"
                                class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-times mr-1 text-gray-400"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Table Container -->
                <div class="relative overflow-x-auto" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th scope="col"
                                    class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    #
                                </th>
                                <th scope="col"
                                    class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Source
                                </th>
                                <th scope="col"
                                    class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th scope="col"
                                    class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Comment
                                </th>

                                <th scope="col"
                                    class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($data as $item)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-2 py-1 text-center whitespace-nowrap text-sm text-gray-500">
                                        {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-2 py-1 text-center whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->source }}</div>
                                    </td>
                                    <td class="px-2 py-1 text-center whitespace-nowrap">
                                        {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-2 py-1 text-center whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $item->comment }}</div>
                                    </td>

                                    <td class="px-2 py-1 text-center whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-3">
                                            @can('journal_entry.update')
                                                <a href="{{ route('journal_entry.show', $item->id) }}"
                                                    class="text-blue-500 hover:text-blue-700 p-2 rounded-full hover:bg-blue-50 transition-colors"
                                                    title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan

                                            @can('journal_entry.update')
                                                @php
                                                    $backUrl = route('journal_entry.index') . (request()->getQueryString() ? '?' . request()->getQueryString() : '');
                                                @endphp
                                                <a href="{{ route('journal_entry.edit', $item->id) . '?back_url=' . urlencode($backUrl) }}"
                                                    class="text-yellow-500 hover:text-yellow-700 p-2 rounded-full hover:bg-yellow-50 transition-colors"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('journal_entry.delete')
                                                @php
                                                    $backUrlDelete = route('journal_entry.index') . (request()->getQueryString() ? '?' . request()->getQueryString() : '');
                                                @endphp
                                                <form id="delete-form-{{ $item->id }}"
                                                    action="{{ route('journal_entry.destroy', $item->id) }}" method="POST"
                                                    style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="back_url" value="{{ $backUrlDelete }}">
                                                </form>

                                                <button type="button" onclick="confirmDelete({{ $item->id }})"
                                                    class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition-colors"
                                                    title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <i class="fas fa-box-open text-4xl mb-4"></i>
                                            <p class="text-lg font-medium text-gray-500">No Journal Entry found</p>
                                            <a href="{{ route('journal_entry.create') }}"
                                                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                                                <i class="fas fa-plus mr-2"></i> Create First Journal Entry
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-gray-100">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <span class="text-sm text-gray-600">
                            Menampilkan {{ $data->firstItem() ?? 0 }} - {{ $data->lastItem() ?? 0 }}
                            dari {{ $data->total() }} data
                        </span>
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- ===================== MODAL HAPUS SEMUA ===================== --}}
    <div id="modalHapusSemua"
        class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Hapus Semua Journal</h3>
                    <p class="text-sm text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-6">
                Apakah Anda yakin ingin menghapus <strong class="text-red-600">SELURUH</strong> data General Journal?
                Semua transaksi akan hilang permanen.
            </p>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('modalHapusSemua').classList.add('hidden')"
                    class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
                    Batal
                </button>
                <form action="{{ route('journal_entry.truncate') }}" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700">
                        <i class="fas fa-trash mr-1"></i> Ya, Hapus Semua
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL HAPUS RANGE ===================== --}}
    <div id="modalHapusRange"
        class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center mb-5">
                <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center mr-4">
                    <i class="fas fa-calendar-times text-orange-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Hapus Range Tanggal</h3>
                    <p class="text-sm text-gray-500">Hapus jurnal berdasarkan rentang waktu</p>
                </div>
            </div>

            <form action="{{ route('journal_entry.delete_range') }}" method="POST" id="formHapusRange">
                @csrf
                @method('DELETE')

                {{-- Toggle opsi --}}
                <div class="flex rounded-lg border border-gray-200 overflow-hidden mb-5">
                    <label class="flex-1 text-center">
                        <input type="radio" name="delete_type" value="range" class="sr-only" checked
                            onchange="toggleDeleteType('range')">
                        <span id="tabRange"
                            class="block px-4 py-2 text-sm font-medium cursor-pointer bg-orange-500 text-white transition-colors">
                            <i class="fas fa-calendar-alt mr-1"></i> Range Tanggal
                        </span>
                    </label>
                    <label class="flex-1 text-center">
                        <input type="radio" name="delete_type" value="year" class="sr-only"
                            onchange="toggleDeleteType('year')">
                        <span id="tabYear"
                            class="block px-4 py-2 text-sm font-medium cursor-pointer bg-white text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-calendar mr-1"></i> Per Tahun
                        </span>
                    </label>
                </div>

                {{-- Bagian Range Tanggal --}}
                <div id="sectionRange" class="space-y-3 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="rangeStartDate"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                        <input type="date" name="end_date" id="rangeEndDate"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    </div>
                </div>

                {{-- Bagian Per Tahun --}}
                <div id="sectionYear" class="hidden mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Tahun</label>
                    <select name="delete_year" id="deleteYear"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        <option value="">--- Pilih Tahun ---</option>
                        @foreach($tahunList as $tahun)
                            <option value="{{ $tahun }}">{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="bg-orange-50 border border-orange-200 rounded-lg px-4 py-2 mb-5">
                    <p class="text-xs text-orange-700">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        Semua Journal Entry dalam rentang yang dipilih akan dihapus permanen.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modalHapusRange').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="button" onclick="konfirmasiHapusRange()"
                        class="flex-1 px-4 py-2 rounded-lg bg-orange-600 text-white text-sm font-semibold hover:bg-orange-700">
                        <i class="fas fa-trash-alt mr-1"></i> Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL FILE ===================== --}}
    <div id="fileModal"
        class="fixed inset-0 z-50 hidden bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                <i class="fas fa-file-alt mr-2 text-blue-400"></i> File Aksi
            </h3>
            <div class="space-y-3 text-sm text-gray-700">
                <a href="{{ asset('template/journal_entry_import_template.xlsx') }}" download
                    class="block hover:bg-gray-50 p-2 rounded-lg">
                    <i class="fas fa-file-excel mr-2 text-green-600"></i> Download Template Excel
                </a>

                <a href="{{ route('export.journal_entry') }}" class="block hover:bg-gray-50 p-2 rounded-lg">
                    <i class="fas fa-file-download mr-2 text-blue-500"></i> Export
                </a>

                <form action="{{ route('import.journal_entry') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-2">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700">Import File Excel:</label>
                    <input type="file" name="file" class="block w-full text-sm border rounded px-2 py-1" required>
                    <button type="submit" class="bg-green-500 text-white w-full py-1 rounded hover:bg-green-600 text-sm">
                        <i class="fas fa-file-upload mr-1"></i> Import
                    </button>
                </form>
            </div>
            <div class="mt-4 text-right">
                <button onclick="document.getElementById('fileModal').classList.add('hidden')"
                    class="text-sm text-gray-500 hover:text-gray-700">Tutup</button>
            </div>
        </div>
    </div>


    <script>
        // ===== Tutup dropdown hapus jika klik di luar =====
        document.addEventListener('click', function (e) {
            const wrapper = document.getElementById('deleteDropdownWrapper');
            const menu = document.getElementById('deleteDropdownMenu');
            if (wrapper && menu && !wrapper.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        // ===== Konfirmasi hapus per baris =====
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus Journal Entry ini?')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }

        // ===== Toggle mode hapus range: tanggal / tahun =====
        function toggleDeleteType(type) {
            const sectionRange = document.getElementById('sectionRange');
            const sectionYear = document.getElementById('sectionYear');
            const tabRange = document.getElementById('tabRange');
            const tabYear = document.getElementById('tabYear');

            if (type === 'range') {
                sectionRange.classList.remove('hidden');
                sectionYear.classList.add('hidden');
                tabRange.classList.add('bg-orange-500', 'text-white');
                tabRange.classList.remove('bg-white', 'text-gray-600');
                tabYear.classList.add('bg-white', 'text-gray-600');
                tabYear.classList.remove('bg-orange-500', 'text-white');
            } else {
                sectionRange.classList.add('hidden');
                sectionYear.classList.remove('hidden');
                tabYear.classList.add('bg-orange-500', 'text-white');
                tabYear.classList.remove('bg-white', 'text-gray-600');
                tabRange.classList.add('bg-white', 'text-gray-600');
                tabRange.classList.remove('bg-orange-500', 'text-white');
            }
        }

        // ===== Konfirmasi sebelum submit hapus range =====
        function konfirmasiHapusRange() {
            const typeEl = document.querySelector('input[name="delete_type"]:checked');
            if (!typeEl) return;

            const type = typeEl.value;
            let pesan = '';

            if (type === 'range') {
                const start = document.getElementById('rangeStartDate').value;
                const end = document.getElementById('rangeEndDate').value;
                if (!start || !end) {
                    alert('Harap isi Tanggal Mulai dan Tanggal Akhir.');
                    return;
                }
                pesan = `Hapus semua Journal Entry dari ${start} s/d ${end}?\nTindakan ini tidak dapat dibatalkan.`;
            } else {
                const year = document.getElementById('deleteYear').value;
                if (!year) {
                    alert('Harap pilih Tahun yang ingin dihapus.');
                    return;
                }
                pesan = `Hapus semua Journal Entry tahun ${year}?\nTindakan ini tidak dapat dibatalkan.`;
            }

            if (confirm(pesan)) {
                document.getElementById('formHapusRange').submit();
            }
        }
    </script>
@endsection
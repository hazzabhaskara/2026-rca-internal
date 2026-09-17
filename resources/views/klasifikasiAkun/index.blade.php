@extends('layouts.app')
@php use App\Setting; @endphp


@section('content')
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center">
            <i class="fas fa-layer-group mr-3 text-blue-500"></i> Klasifikasi Akun
        </h2>
    </x-slot>
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
                        Classification Account
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
                        <!-- Add Button -->
                        @can('klasifikasi_akun.create')
                            <a href="{{ route('klasifikasiAkun.create') }}"
                                class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 font-semibold rounded-lg shadow hover:bg-gray-100 transition-all">
                                <i class="fas fa-plus mr-2"></i> Add Klasifikasi Akun
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- Filter Panel -->
                <div id="filterPanel" class="hidden px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex flex-wrap gap-4">
                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Cari..."
                                class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm w-64 shadow-sm" />
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-search text-gray-400 text-sm"></i>
                            </div>
                        </div>

                        <!-- Filter Tipe Akun -->
                        <select id="filterTipeAkun" class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                            <option value="">Semua Grup</option>
                            @foreach ($numberingAccounts as $tipe)
                                <option value="{{ strtolower($tipe) }}">{{ $tipe }}</option>
                            @endforeach
                        </select>

                    </div>
                </div>

                <div class="relative overflow-x-auto" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <table class="min-w-full divide-y divide-gray-200 text-base">
                        <thead class="bg-gray-100 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-4 text-center font-medium text-gray-600 uppercase">
                                    No</th>

                                <th class="px-6 py-4 text-center font-medium text-gray-600 uppercase">
                                    Grup</th>
                                <th class="px-6 py-4 text-center font-medium text-gray-600 uppercase">
                                    Nama</th>
                                {{-- <th
                                    class="px-6 py-4 text-center font-medium text-gray-600 uppercase">
                                    Parent</th> --}}
                                <th class="px-6 py-4 text-center font-medium text-gray-600 uppercase">
                                    Aktif</th>
                                <th class="px-6 py-4 text-center font-medium text-gray-600 uppercase">
                                    Deskripsi</th>
                                <th class="px-6 py-4 text-center font-medium text-gray-600 uppercase">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($klasifikasis as $akun)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-2 py-1 text-center text-gray-700">{{ $loop->iteration }}</td>
                                    <td class="px-2 py-1 text-center">
                                        {{ strtoupper($akun->numberingAccount->nama_grup ?? '-') }}</td>
                                    <td class="px-2 py-1 text-center">{{ strtoupper($akun->nama_klasifikasi) }}</td>
                                    {{-- <td class="px-3 py-1">{{ $akun->parent->nama_klasifikasi ?? '-' }}</td> --}}
                                    <td class="px-2 py-1 text-center">
                                        <span class="{{ $akun->aktif ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $akun->aktif ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-1 text-center">{{ $akun->deskripsi }}</td>
                                    <td class="px-2 py-1 text-center">
                                        <div class="flex justify-end space-x-3">
                                            @can('klasifikasi_akun.view')
                                                <a href="{{ route('klasifikasiAkun.show', $akun->id) }}"
                                                    class="text-blue-500 hover:text-blue-700 p-2 rounded-full hover:bg-blue-50 transition-colors"
                                                    title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan
                                            @can('klasifikasi_akun.update')
                                                <a href="{{ route('klasifikasiAkun.edit', $akun->id) }}"
                                                    class="text-yellow-500 hover:text-yellow-700 p-2 rounded-full hover:bg-yellow-50 transition-colors"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('klasifikasi_akun.delete')
                                                <form id="delete-form-{{ $akun->id }}"
                                                    action="{{ route('klasifikasiAkun.destroy', $akun->id) }}" method="POST"
                                                    style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>

                                                <button type="button" onclick="confirmDelete({{ $akun->id }})"
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
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center text-gray-400">
                                            <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                                            <p class="text-lg font-medium">Belum ada klasifikasi akun</p>
                                            <a href="{{ route('klasifikasiAkun.create') }}"
                                                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                                                <i class="fas fa-plus mr-2"></i> Tambah Sekarang
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-center">
                    {{ $klasifikasis->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- File Modal -->
    <div id="fileModal"
        class="fixed inset-0 z-50 hidden bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                <i class="fas fa-file-alt mr-2 text-blue-400"></i> File Aksi
            </h3>
            <div class="space-y-3 text-sm text-gray-700">
                <a href="{{ asset('template/template_import_klasifikasi_akun.xlsx') }}" download
                    class="block hover:bg-gray-50 p-2 rounded-lg">
                    <i class="fas fa-file-excel mr-2 text-green-600"></i> Download Template Excel
                </a>
                <a href="{{ route('export.klasifikasiAkun') }}" class="block hover:bg-gray-50 p-2 rounded-lg">
                    <i class="fas fa-file-download mr-2 text-blue-500"></i> Export Klasifikasi Akun
                </a>
                <form action="{{ route('import.klasifikasiAkun') }}" method="POST" enctype="multipart/form-data"
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

    <!-- JS for dropdown toggle -->
    <script>
        document.getElementById('menu-button').addEventListener('click', function() {
            document.getElementById('dropdown-menu').classList.toggle('hidden');
        });

        window.addEventListener('click', function(e) {
            const button = document.getElementById('menu-button');
            const menu = document.getElementById('dropdown-menu');
            if (!button.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>

    <style>
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(5px);
            background-color: rgba(255, 255, 255, 0.9);
        }

        table thead {
            position: sticky;
            top: 68px;
            z-index: 10;
        }

        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        tr {
            transition: background-color 0.2s ease;
        }
    </style>
    <!-- Search Filter Script -->
    <script>
        const searchInput = document.getElementById('searchInput');
        const filterTipeAkun = document.getElementById('filterTipeAkun');
        const rows = document.querySelectorAll('tbody tr');

        function filterTable() {
            const searchValue = searchInput.value.toLowerCase();
            const tipeValue = filterTipeAkun.value.toLowerCase();

            rows.forEach(row => {
                const rowText = row.innerText.toLowerCase();
                const tipeCell = row.querySelectorAll('td')[3]?.innerText.toLowerCase(); // kolom ke-3: tipe akun

                const matchSearch = rowText.includes(searchValue);
                const matchTipe = tipeValue === '' || tipeCell === tipeValue;

                row.style.display = (matchSearch && matchTipe) ? '' : 'none';
            });
        }

        searchInput.addEventListener('keyup', filterTable);
        filterTipeAkun.addEventListener('change', filterTable);
    </script>
@endsection

@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <!-- Header & Controls -->
                @php
                    $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                @endphp

                <div class="sticky top-0 z-20 px-6 py-5 border-b border-gray-100 flex justify-between items-center"
                    style="background: {{ $themeColor }};">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-list mr-3 text-white text-xl"></i>
                        Bonus Employee
                    </h3>
                    <div class="flex flex-wrap gap-2">

                        <button onclick="document.getElementById('filterPanel').classList.toggle('hidden')"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-filter text-gray-500 mr-2"></i> Filter
                        </button>

                    </div>

                </div>
                <div id="filterPanel"
                    class="{{ request('search') || request('filter_tipe') ? '' : 'hidden' }} px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <form method="GET" action="{{ route('bonus_karyawan.index') }}">
                        <div class="flex flex-wrap gap-4">
                            <!-- Search Input -->
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Cari wahana, dan jenis hari"
                                    class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm w-64 shadow-sm" />
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-sm"></i>
                                </div>
                            </div>


                            <select name="filter_wahana"
                                class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                                <option value="">Semua wahana </option>
                                @foreach ($wahana as $whn)
                                    <option value="{{ $whn }}"
                                        {{ request('filter_tipe') == $whn ? 'selected' : '' }}>
                                        {{ $whn }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="filter_jenis_hari"
                                class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                                <option value="">Semua jenis hari </option>
                                @foreach ($jenis_hari as $js)
                                    <option value="{{ $js }}"
                                        {{ request('filter_tipe') == $js ? 'selected' : '' }}>
                                        {{ $js }}
                                    </option>
                                @endforeach
                            </select>




                            <!-- Tombol Filter -->
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded shadow-sm hover:bg-blue-600 text-sm">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>

                            <!-- Tombol Reset -->
                            <a href="{{ route('bonus_karyawan.index') }}"
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
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    #
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Karyawan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Wahana
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jenis Hari
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bonus
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Keterangan
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($data as $item)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-2 py-1 text-center whitespace-nowrap text-sm text-gray-500">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-2 py-1 text-center whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y') : '-' }}
                                        </div>
                                    </td>
                                    <td class="px-2 py-1 text-center whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->employee->nama_karyawan }}
                                        </div>
                                    </td>
                                    <td class="px-2 py-1 text-center whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $item->transaksiWahana->wahana->nama_wahana ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-2 py-1 text-center whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $item->jenisHari->nama }}
                                        </div>
                                    </td>
                                    <td class="px-2 py-1 text-center whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($item->bonus) }}
                                        </div>
                                    </td>
                                    <td class="px-2 py-1 text-center whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $item->keterangan ?? 'Tidak Ada' }}
                                        </div>
                                    </td>


                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <i class="fas fa-box-open text-4xl mb-4"></i>
                                            <p class="text-lg font-medium text-gray-500">Tidak ada bonus</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- File Modal -->
                <div id="fileModal"
                    class="fixed inset-0 z-50 hidden bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center">
                    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-file-alt mr-2 text-blue-400"></i> File Aksi
                        </h3>
                        <div class="space-y-3 text-sm text-gray-700">
                            <a href="{{ asset('template/template_Departemen_import.xlsx') }}" download
                                class="block hover:bg-gray-50 p-2 rounded-lg">
                                <i class="fas fa-file-excel mr-2 text-green-600"></i> Download Template Excel
                            </a>
                            <a href="{{ route('export.Departemen') }}" class="block hover:bg-gray-50 p-2 rounded-lg">
                                <i class="fas fa-file-download mr-2 text-blue-500"></i> Export
                            </a>
                            <form action="{{ route('import.Departemen') }}" method="POST" enctype="multipart/form-data"
                                class="space-y-2">
                                @csrf
                                <label class="block text-sm font-medium text-gray-700">Import File Excel:</label>
                                <input type="file" name="file" class="block w-full text-sm border rounded px-2 py-1"
                                    required>
                                <button type="submit"
                                    class="bg-green-500 text-white w-full py-1 rounded hover:bg-green-600 text-sm">
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


            </div>
        </div>
    </div>

    <style>
        /* Improved sticky header implementation */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(5px);
            background-color: rgba(255, 255, 255, 0.9);
        }

        /* Table header sticky positioning */
        table thead {
            position: sticky;
            top: 68px;
            /* Height of the sticky-header */
            z-index: 10;
        }

        /* Beautiful scrollbar */
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

        /* Smooth transitions */
        tr {
            transition: background-color 0.2s ease;
        }
    </style>
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
@endsection

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
                        Account List
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
                        @can('chart_of_account.delete')
                            <!-- Truncate Button -->
                            <form action="{{ route('chartOfAccount.truncate') }}" method="POST" class="inline" onsubmit="return confirm('PENTING: Apakah Anda yakin ingin menghapus SELURUH data Account List? Tindakan ini tidak dapat dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-red-300 bg-red-50 text-red-700 hover:bg-red-100 font-bold ml-2">
                                    <i class="fas fa-trash-alt mr-2"></i> Kosongkan Semua
                                </button>
                            </form>
                        @endcan
                        @can('chart_of_account.create')
                            <!-- Add Button -->
                            <a href="{{ route('chartOfAccount.create') }}"
                                class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 font-semibold rounded-lg shadow hover:bg-gray-100 transition-all">
                                <i class="fas fa-plus mr-2"></i> Add Account
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- Filter Panel -->
                <div id="filterPanel"
                    class="{{ request('search') || request('filter_tipe') ? '' : 'hidden' }} px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <form method="GET" action="{{ route('chartOfAccount.index') }}">
                        <div class="flex flex-wrap gap-4">
                            <!-- Search Input -->
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..."
                                    class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm w-64 shadow-sm" />
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-sm"></i>
                                </div>
                            </div>

                            <!-- Filter Tipe Akun -->
                            <select name="filter_tipe"
                                class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                                <option value="">Semua Tipe</option>
                                @foreach ($tipeAkunOptions as $tipe)
                                    <option value="{{ $tipe }}"
                                        {{ request('filter_tipe') == $tipe ? 'selected' : '' }}>
                                        {{ $tipe }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Tombol Filter -->
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded shadow-sm hover:bg-blue-600 text-sm">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>

                            <!-- Tombol Reset -->
                            <a href="{{ route('chartOfAccount.index') }}"
                                class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-times mr-1 text-gray-400"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>


                <!-- Table -->
                <div class="relative overflow-x-auto" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <table class="min-w-full divide-y">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-2 py-1 text-center font-medium text-gray-600 uppercase">Kode Akun
                                </th>
                                <th class="px-2 py-1 text-left font-medium text-gray-600 uppercase">Nama Akun
                                </th>
                                <th class="px-2 py-1 text-center font-medium text-gray-600 uppercase">Tipe Akun
                                </th>
                                <th class="px-2 py-1 text-center font-medium text-gray-600 uppercase">Level Akun
                                </th>
                                <th class="px-2 py-1 text-center font-medium text-gray-600 uppercase">Klasifikasi
                                    Akun</th>
                                <th class="px-2 py-1 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($chartOfAccounts as $chartOfAccount)
                                @php
                                    $levelMap = [
                                        'header' => 0,
                                        'group account' => 1,
                                        'account' => 2,
                                        'sub account' => 3,
                                        'sub account total' => 3,
                                        'account total' => 3,
                                    ];

                                    $lowerLevel = strtolower($chartOfAccount->level_akun);
                                    $levelIndent = $levelMap[$lowerLevel] ?? 0;
                                    $margin = $levelIndent * 20;

                                    $iconMap = [
                                        'header' => '📁',
                                        'group account' => '📂',
                                        'account' => '📄',
                                        'sub account' => '🔸',
                                        'sub account total' => '🔸',
                                        'account total' => '🔹',
                                    ];
                                    $icon = $iconMap[$lowerLevel] ?? '📄';
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="text-center whitespace-nowrap">{{ $chartOfAccount->kode_akun }}
                                    </td>
                                    <td class="text-center whitespace-nowrap">
                                        <div class="flex items-center" style="margin-left: {{ $margin }}px">
                                            {!! $icon !!}
                                            &nbsp;
                                            @if ($lowerLevel === 'header')
                                                <span
                                                    class="font-bold uppercase text-gray-800">{{ $chartOfAccount->nama_akun }}</span>
                                            @elseif($lowerLevel === 'grup')
                                                <span
                                                    class="font-semibold text-gray-700">{{ $chartOfAccount->nama_akun }}</span>
                                            @else
                                                <span class="text-gray-600">{{ $chartOfAccount->nama_akun }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center whitespace-nowrap">
                                        {{ strtoupper($chartOfAccount->tipe_akun) }}
                                    </td>
                                    <td class="text-center whitespace-nowrap">{{ $chartOfAccount->level_akun }}
                                    </td>
                                    <td class="text-center whitespace-nowrap">
                                        {{ strtoupper($chartOfAccount->klasifikasiAkun->nama_klasifikasi ?? '-') }}
                                    </td>
                                    <td class="text-center whitespace-nowrap text-right">
                                        <div class="flex justify-end space-x-2">
                                            @can('chart_of_account.view')
                                                <a href="{{ route('chartOfAccount.show', $chartOfAccount->id) }}"
                                                    class="text-blue-500 hover:text-blue-700 p-2 rounded-full hover:bg-blue-50">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan
                                            @can('chart_of_account.update')
                                                <a href="{{ route('chartOfAccount.edit', $chartOfAccount->id) }}"
                                                    class="text-yellow-500 hover:text-yellow-700 p-2 rounded-full hover:bg-yellow-50">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('chart_of_account.delete')
                                                <form id="delete-form-{{ $chartOfAccount->id }}"
                                                    action="{{ route('chartOfAccount.destroy', $chartOfAccount->id) }}"
                                                    method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>

                                                <button type="button" onclick="confirmDelete({{ $chartOfAccount->id }})"
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
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-box-open text-4xl mb-4 text-gray-400"></i>
                                        No Chart Of Account found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>

                {{-- <!-- Pagination -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-center">
                    {{ $chartOfAccounts->appends(request()->input())->links() }}

                </div> --}}
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
                <a href="{{ asset('template/template_chart_of_account.xlsx') }}" download
                    class="block hover:bg-gray-50 p-2 rounded-lg">
                    <i class="fas fa-file-excel mr-2 text-green-600"></i> Download Template Excel
                </a>
                <a href="{{ route('export.chartOfAccount') }}" class="block hover:bg-gray-50 p-2 rounded-lg">
                    <i class="fas fa-file-download mr-2 text-blue-500"></i> Export Account
                </a>
                <form action="{{ route('import.chartOfAccount') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-2">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700">Import File Excel:</label>
                    <input type="file" name="file" class="block w-full text-sm border rounded" required>
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

    <!-- Style -->
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
@endsection

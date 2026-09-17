@extends('layouts.app')
@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <!-- Sticky Card Header -->
                <div
                    class="sticky top-0 z-20 px-6 py-5 border-b border-gray-100 
                    bg-gradient-to-r from-indigo-500 to-blue-600 
                     flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-list mr-3 text-white text-xl"></i>
                        Items
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <!-- File Button -->
                        <button onclick="document.getElementById('fileModal').classList.remove('hidden')"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-file-export text-blue-500 mr-2"></i> File
                        </button>
                        <a href="{{ route('items.create') }}"
                            class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 font-semibold rounded-lg shadow hover:bg-gray-100 transition-all">
                            <i class="fas fa-plus mr-2"></i> Add Items
                        </a>
                    </div>

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
                        @foreach ($data as $tipe)
                            <option value="{{ strtolower($tipe) }}">{{ $tipe }}</option>
                        @endforeach
                    </select>

                </div>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Item Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nama Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Akun</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Image</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Barcode</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($items as $item)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-sm text-gray-800">{{ $item->item_number }}</td>
                            <td class="px-6 py-4 text-sm">{{ $item->item_name }}</td>
                            <td class="px-6 py-4 text-sm">Rp {{ number_format($item->base_price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm">{{ $item->category->nama_kategori ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $item->account->nama_akun ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm"><img src="{{ asset('storage/items/' . $item->image) }}"
                                    alt="Image" class="w-20 h-20 object-cover rounded">
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {!! DNS1D::getBarcodeHTML($item->item_number, 'C128') !!}
                                <div class="text-xs text-gray-500 mt-1">{{ $item->item_number }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="inline-block px-3 py-1 text-xs font-semibold rounded-full
                                        {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-600' }}">
                                    {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('items.show', $item->id) }}"
                                        class="text-blue-500 hover:text-blue-700" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('items.edit', $item->id) }}"
                                        class="text-yellow-500 hover:text-yellow-700" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form id="delete-form-{{ $item->id }}"
                                        action="{{ route('items.destroy', $item->id) }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>

                                    <button type="button" onclick="confirmDelete({{ $item->id }})"
                                        class="text-red-500 hover:text-red-700  hover:bg-red-50 transition-colors"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-6 text-center text-sm text-gray-500">
                                Belum ada data item.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
                <a href="{{ asset('template/template_items_import') }}" download
                    class="block hover:bg-gray-50 p-2 rounded-lg">
                    <i class="fas fa-file-excel mr-2 text-green-600"></i> Download Template Excel
                </a>
                <a href="{{ route('export.items') }}" class="block hover:bg-gray-50 p-2 rounded-lg">
                    <i class="fas fa-file-download mr-2 text-blue-500"></i> Export items
                </a>
                <form action="{{ route('import.items') }}" method="POST" enctype="multipart/form-data" class="space-y-2">
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
                const tipeCell = row.querySelectorAll('td')[4]?.innerText.toLowerCase(); // kolom ke-3: tipe akun

                const matchSearch = rowText.includes(searchValue);
                const matchTipe = tipeValue === '' || tipeCell === tipeValue;

                row.style.display = (matchSearch && matchTipe) ? '' : 'none';
            });
        }

        searchInput.addEventListener('keyup', filterTable);
        filterTipeAkun.addEventListener('change', filterTable);
    </script>
@endsection

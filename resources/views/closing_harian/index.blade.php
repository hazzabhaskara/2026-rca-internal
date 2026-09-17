@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <!-- Sticky Card Header -->
                @php
                    $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                @endphp

                <div class="sticky top-0 z-20 px-6 py-5 border-b border-gray-100 flex justify-between items-center"
                    style="background: {{ $themeColor }};">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-list mr-3 text-white text-xl"></i>
                        Closing Harian List
                    </h3>
                    <div class="flex flex-wrap gap-2">

                        {{-- <button onclick="document.getElementById('fileModal').classList.remove('hidden')"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-file-export text-blue-500 mr-2"></i> File
                        </button> --}}
                        @can('closing_harian.create')
                            <a href="{{ route('closing_harian.create') }}"
                                class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 font-semibold rounded-lg shadow hover:bg-gray-100 transition-all">
                                <i class="fas fa-plus mr-2"></i> Add Closing Harian
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
            <div id="fileModal"
                class="fixed inset-0 z-50 hidden bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center">
                <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-file-alt mr-2 text-blue-400"></i> File Aksi
                    </h3>
                    <div class="space-y-3 text-sm text-gray-700">
                        <a href="{{ asset('template/template_import_closing_harian.xlsx') }}" download
                            class="block hover:bg-gray-50 p-2 rounded-lg">
                            <i class="fas fa-file-excel mr-2 text-green-600"></i> Download Template Excel
                        </a>
                        <a href="{{ route('export.fiscal') }}" class="block hover:bg-gray-50 p-2 rounded-lg">
                            <i class="fas fa-file-download mr-2 text-blue-500"></i> Export
                        </a>
                        <form action="{{ route('import.fiscal') }}" method="POST" enctype="multipart/form-data"
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

            <div class="relative overflow-x-auto" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unit Kerja</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($data as $item)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-4 py-2 text-sm text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2">{{ $item->tanggal }}</td>
                                <td class="px-4 py-2">{{ $item->unitKerja->nama_unit ?? '-' }}</td>
                                <td class="px-4 py-2 text-right">
                                    <div class="flex justify-end space-x-3">
                                        <button onclick="openFilePrint({{ $item->id }})"
                                            class="text-green-500 hover:text-green-700 p-2 rounded-full hover:bg-green-50 transition-colors"
                                            title="Print">
                                            <i class="fas fa-print text-green-500 mr-2"></i>
                                        </button>
                                        <a href="{{ route('closing_harian.show', $item->id) }}"
                                            class="text-blue-500 hover:text-blue-700 p-2 rounded-full hover:bg-blue-50 transition-colors"
                                            title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('closing_harian.update')
                                            <a href="{{ route('closing_harian.edit', $item->id) }}"
                                                class="text-yellow-500 hover:text-yellow-700 p-2 rounded-full hover:bg-yellow-50 transition-colors"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan

                                        @can('closing_harian.delete')
                                            <form id="delete-form-{{ $item->id }}"
                                                action="{{ route('closing_harian.destroy', $item->id) }}" method="POST"
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
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
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-gray-400">
                                        <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                                        <p class="text-lg font-medium">Belum ada fiscal account</p>
                                        <a href="{{ route('closing_harian.create') }}"
                                            class="mt-4 inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                                            <i class="fas fa-plus mr-2"></i> Tambah
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <div id="filePrint"
        class="fixed inset-0 z-50 hidden bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                <i class="fas fa-file-alt mr-2 text-blue-400"></i> File Aksi
            </h3>
            <div class="space-y-3 text-sm text-gray-700">
                <a id="printLink" href="#" target="_blank" class="block hover:bg-gray-50 p-2 rounded-lg">
                    <i class="fas fa-file-download mr-2 text-green-500"></i> Print
                </a>
                <a id="pdfLink" href="#" target="_blank" class="block hover:bg-gray-50 p-2 rounded-lg">
                    <i class="fas fa-file-download mr-2 text-red-500"></i> Download PDF
                </a>
            </div>
            <div class="mt-4 text-right">
                <button onclick="document.getElementById('filePrint').classList.add('hidden')"
                    class="text-sm text-gray-500 hover:text-gray-700">Tutup</button>
            </div>
        </div>
    </div>

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
    <script>
        function openFilePrint(id) {
            // Tampilkan modal
            document.getElementById('filePrint').classList.remove('hidden');

            // Ubah link dinamis berdasarkan ID yang diklik
            document.getElementById('printLink').href = `/closing_harian/${id}/print`;
            document.getElementById('pdfLink').href = `/closing_harian/${id}/pdf`;
        }
    </script>
@endsection

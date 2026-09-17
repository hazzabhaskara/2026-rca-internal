@extends('layouts.app')
@section('content')
    {{-- Include Alpine.js --}}
    <script src="//unpkg.com/alpinejs" defer></script>



    @if (session('not_found'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
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
            {{-- Kartu Form Filter --}}
            <div class="bg-white border border-gray-200 shadow-md rounded-xl p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4"></h2>

                <form method="GET" action="{{ route('project.view_project') }}"
                    class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                    <div>
                        <label for="tanggal_awal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                            Awal</label>
                        <input type="date" id="tanggal_awal" name="tanggal_awal" value="{{ request('tanggal_awal') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:ring-primary-500 focus:border-primary-500"
                            required>
                    </div>

                    <div>
                        <label for="tanggal_akhir" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                            Akhir</label>
                        <input type="date" id="tanggal_akhir" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:ring-primary-500 focus:border-primary-500"
                            required>
                    </div>



                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-md shadow hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i> Filter
                        </button>

                        <a href="{{ route('project.view_project') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 font-medium text-sm rounded-md hover:bg-gray-200">
                            <i class="fas fa-undo mr-2"></i> Reset
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Modal Hasil Filter --}}

    @if (request()->filled('tanggal_awal') && request()->filled('tanggal_akhir') && $entries->isNotEmpty())
        <div x-data="{ open: true }">
            <!-- Overlay -->
            <div x-show="open" class="fixed inset-0 bg-black bg-opacity-40 z-40"></div>

            <!-- Modal Content -->
            <div x-show="open" x-transition class="fixed inset-0 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6 relative">
                    <button @click="open = false" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>

                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Hasil Filter Project</h3>

                    <div class="overflow-auto max-h-[400px]">
                        <table class="w-full text-sm text-left text-gray-700 border">
                            <thead class="bg-gray-100 text-xs uppercase">
                                <tr>
                                    <th class="px-4 py-2 border">Tanggal</th>
                                    <th class="px-4 py-2 border">Project</th>
                                    <th class="px-4 py-2 border">Revenue</th>
                                    <th class="px-4 py-2 border">Expense</th>
                                    <th class="px-4 py-2 border">Status</th>
                                    <th class="px-4 py-2 border text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($entries as $entry)
                                    <tr>
                                        <td class="px-4 py-2 border">{{ $entry->start_date }}</td>
                                        <td class="px-4 py-2 border">{{ $entry->nama_project }}</td>
                                        <td class="px-4 py-2 border">{{ number_format($entry->revenue) }}</td>
                                        <td class="px-4 py-2 border">{{ number_format($entry->expens) }}</td>
                                        <td class="px-4 py-2 border">{{ $entry->status }}</td>
                                        <td class="px-4 py-2 border text-center">
                                            <a href="{{ route('project.show', $entry->id) }}"
                                                class="text-green-600 hover:underline font-semibold text-sm">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 text-right">
                        <button @click="open = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

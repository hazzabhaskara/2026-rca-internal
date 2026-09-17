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
                        Payment Method List
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <!-- Add Button -->
                        @can('payment_method.create')
                            <a href="{{ route('PaymentMethod.create') }}"
                                class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 font-semibold rounded-lg shadow hover:bg-gray-100 transition-all">
                                <i class="fas fa-plus mr-2"></i> Add Payment Method
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="relative overflow-x-auto" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    #</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kode Method</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Method</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($data as $item)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-2 py-1 text-center text-sm text-gray-500">{{ $loop->iteration }}</td>
                                    <td class="px-2 py-1 text-center">{{ $item->kode_jenis }}</td>
                                    <td class="px-2 py-1 text-center">{{ $item->nama_jenis }}</td>
                                    <td class="px-2 py-1 text-right">
                                        <div class="flex justify-end space-x-3">
                                            @can('payment_method.view')
                                                <a href="{{ route('PaymentMethod.show', $item->id) }}"
                                                    class="text-blue-500 hover:text-blue-700 p-2 rounded-full hover:bg-blue-50 transition-colors"
                                                    title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan

                                            @if ($item->status_payment === 1)
                                                <button
                                                    class="text-gray-400 cursor-not-allowed p-2 rounded-full bg-gray-100"
                                                    title="Edit Disabled" disabled>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    class="text-gray-400 cursor-not-allowed p-2 rounded-full bg-gray-100"
                                                    title="Delete Disabled" disabled>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @else
                                                @can('payment_method.update')
                                                    <a href="{{ route('PaymentMethod.edit', $item->id) }}"
                                                        class="text-yellow-500 hover:text-yellow-700 p-2 rounded-full hover:bg-yellow-50 transition-colors"
                                                        title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('payment_method.delete')
                                                    <form id="delete-form-{{ $item->id }}"
                                                        action="{{ route('PaymentMethod.destroy', $item->id) }}" method="POST"
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
                                            @endif

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center text-gray-400">
                                            <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                                            <p class="text-lg font-medium">Belum ada Payment Method</p>
                                            <a href="{{ route('PaymentMethod.create') }}"
                                                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                                                <i class="fas fa-plus mr-2"></i> Tambah
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

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
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const rowText = row.innerText.toLowerCase();
                row.style.display = rowText.includes(searchValue) ? '' : 'none';
            });
        });
    </script>
@endsection

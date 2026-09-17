@extends('layouts.app')
@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <!-- Sticky Card Header -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                    <!-- Sticky Card Header -->
                    @php
                        $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                    @endphp

                    <div class="sticky top-0 z-20 px-6 py-5 border-b border-gray-100 flex justify-between items-center"
                        style="background: {{ $themeColor }};">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-list mr-3 text-white text-xl"></i>
                            Purchase Order
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <!-- File Button -->
                            {{-- <button onclick="document.getElementById('fileModal').classList.remove('hidden')"
                                class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-file-export text-blue-500 mr-2"></i> File
                            </button> --}}
                            <button onclick="document.getElementById('filterPanel').classList.toggle('hidden')"
                                class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-filter text-gray-500 mr-2"></i> Filter
                            </button>
                            @can('purchase_order.create')
                                <a href="{{ route('purchase_order.create') }}"
                                    class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 font-semibold rounded-lg shadow hover:bg-gray-100 transition-all">
                                    <i class="fas fa-plus mr-2"></i> Add Purchase Order
                                </a>
                            @endcan
                        </div>
                    </div>

                    <div id="filterPanel"
                        class="{{ request('search') || request('filter_tipe') ? '' : 'hidden' }} px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <form method="GET" action="{{ route('purchase_order.index') }}">
                            <div class="flex flex-wrap gap-4">
                                <!-- Search Input -->
                                <div class="relative">
                                    <input type="text" name="search" value="{{ request('search') }}"
                                        placeholder="Cari No Order,Tanggal Order"
                                        class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm w-64 shadow-sm" />
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-search text-gray-400 text-sm"></i>
                                    </div>
                                </div>

                                <!-- Filter Tipe Akun -->
                                <select name="filter_location"
                                    class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                                    <option value="">Location</option>
                                    @foreach ($location as $loc)
                                        <option value="{{ $loc }}"
                                            {{ request('filter_location') == $loc ? 'selected' : '' }}>
                                            {{ $loc }}
                                        </option>
                                    @endforeach
                                </select>

                                <select name="filter_status"
                                    class="py-2 px-3 border rounded-lg text-sm border-gray-300 shadow-sm">
                                    <option value="">Status</option>
                                    <option value="0" {{ request('filter_status') == 0 ? 'selected' : '' }}>
                                        Menunggu
                                    </option>
                                    <option value="1" {{ request('filter_status') == 1 ? 'selected' : '' }}>
                                        Invoice
                                    </option>
                                    <option value="2" {{ request('filter_status') == 2 ? 'selected' : '' }}>
                                        Pembayaran
                                    </option>
                                    <option value="3" {{ request('filter_status') == 3 ? 'selected' : '' }}>
                                        Selesai
                                    </option>
                                </select>


                                <!-- Tombol Filter -->
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded shadow-sm hover:bg-blue-600 text-sm">
                                    <i class="fas fa-search mr-1"></i> Filter
                                </button>

                                <!-- Tombol Reset -->
                                <a href="{{ route('purchase_order.index') }}"
                                    class="inline-flex items-center px-3 py-2 text-sm rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-times mr-1 text-gray-400"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                    <div class="relative overflow-x-auto" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        #</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date Order</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Order Number</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Shipping Date</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Location Inventory</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Vendor</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Payment Method</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Shipping Address</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Early Payment Terms</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Messages</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($data as $item)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4">{{ $item->date_order }}</td>
                                        <td class="px-6 py-4">{{ $item->order_number }}</td>
                                        <td class="px-6 py-4">{{ $item->shipping_date }}</td>
                                        <td class="px-6 py-4">{{ $item->locationInventory->kode_lokasi ?? '-' }}</td>
                                        <td class="px-6 py-4">{{ $item->vendor->nama_vendors ?? '-' }}</td>
                                        <td class="px-6 py-4">{{ $item->jenisPembayaran->nama_jenis }}</td>
                                        <td class="px-6 py-4">{{ $item->shipping_address }}</td>
                                        <td class="px-6 py-4">{{ $item->early_payment_terms }}</td>
                                        <td class="px-6 py-4">
                                            @if ($item->status_purchase == 0)
                                                <span
                                                    class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-full text-sm">Menunggu</span>
                                            @elseif ($item->status_purchase == 1)
                                                <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm">
                                                    Invoice</span>
                                            @elseif ($item->status_purchase == 2)
                                                <span class="px-4 py-2 bg-orange-100 text-green-700 rounded-full text-sm">
                                                    Pembayaran</span>
                                            @elseif ($item->status_purchase == 3)
                                                <span
                                                    class="px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm">Selesai</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">{{ $item->messages }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end space-x-3">
                                                <button onclick="openFilePrint({{ $item->id }})"
                                                    class="text-green-500 hover:text-green-700 p-2 rounded-full hover:bg-green-50 transition-colors"
                                                    title="Print">
                                                    <i class="fas fa-print text-green-500 mr-2"></i>
                                                </button>
                                                @can('purchase_order.view')
                                                    <a href="{{ route('purchase_order.show', $item->id) }}"
                                                        class="text-blue-500 hover:text-blue-700 p-2 rounded-full hover:bg-blue-50 transition-colors"
                                                        title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan
                                                @if ($item->status_purchase == 0)
                                                    {{-- tombol edit dan hapus aktif --}}
                                                    @can('purchase_order.update')
                                                        <a href="{{ route('purchase_order.edit', $item->id) }}"
                                                            class="text-yellow-500 hover:text-yellow-700 p-2 rounded-full hover:bg-yellow-50 transition-colors"
                                                            title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan

                                                    @can('purchase_order.delete')
                                                        <form id="delete-form-{{ $item->id }}"
                                                            action="{{ route('purchase_order.destroy', $item->id) }}"
                                                            method="POST" style="display: none;">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                        <button type="button" onclick="confirmDelete({{ $item->id }})"
                                                            class="text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors"
                                                            title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endcan
                                                @elseif ($item->status_purchase == 1 || $item->status_purchase == 2 || $item->status_purchase == 3)
                                                    {{-- tombol edit dan hapus dinonaktifkan --}}
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
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="15" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center text-gray-400">
                                                <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                                                <p class="text-lg font-medium">Belum ada Purchase Orders</p>
                                                <a href="{{ route('purchase_order.create') }}"
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
        <!-- File Modal -->
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
                document.getElementById('printLink').href = `/purchase_order/${id}/print`;
                document.getElementById('pdfLink').href = `/purchase_order/${id}/pdf`;
            }
        </script>
    @endsection

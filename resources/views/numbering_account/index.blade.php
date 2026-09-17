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
                        Numbering Account List
                    </h3>
                    @can('numbering.create')
                        @if ($numberingAccount->count() == 0)
                            <a href="{{ route('numbering_account.create') }}"
                                class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 font-semibold rounded-lg shadow hover:bg-gray-100 transition-all">
                                <i class="fas fa-plus mr-2"></i> Add Numbering Account
                            </a>
                        @else
                            <span
                                class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 font-semibold rounded-lg shadow hover:bg-gray-100 transition-all">Numbering
                                account sudah ditetapkan, tidak bisa tambah
                                baru.</span>
                        @endif
                    @endcan
                </div>

                <!-- Table Container -->
                <div class="relative w-full overflow-x-auto" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <table class="min-w-max w-full divide-y divide-gray-200 text-base">
                        <thead class="bg-gray-100 sticky top-0 z-10">
                            <tr>
                                <th class="px-2 py-1 text-left text-gray-600 uppercase">#</th>
                                <th class="px-2 py-1 text-left text-gray-600 uppercase">Account Group</th>
                                <th class="px-2 py-1 text-center text-gray-600 uppercase">Jumlah Digit</th>
                                <th class="px-2 py-1 text-center text-gray-600 uppercase">Starting Account Number</th>
                                <th class="px-2 py-1 text-center text-gray-600 uppercase">Ending Account Number</th>
                                <th class="px-2 py-1 text-right text-gray-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($numberingAccount as $numberingAccounts)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-2 py-1 text-left text-gray-700">{{ $loop->iteration }}</td>
                                    <td class="px-2 py-1 text-left font-semibold text-gray-900">
                                        {{ strtoupper($numberingAccounts->nama_grup) }}
                                    </td>
                                    <td class="px-2 py-1 text-center text-gray-800">
                                        {{ $numberingAccounts->jumlah_digit }}
                                    </td>
                                    <td class="px-2 py-1 text-center font-medium text-gray-900">
                                        {{ $numberingAccounts->nomor_akun_awal }}
                                    </td>
                                    <td class="px-2 py-1 text-center font-medium text-gray-900">
                                        {{ $numberingAccounts->nomor_akun_akhir }}
                                    </td>
                                    @can('numbering.view')
                                        <td class="px-2 py-1 text-right">
                                            <div class="flex justify-end space-x-3">
                                                <a href="{{ route('numbering_account.show', $numberingAccounts->id) }}"
                                                    class="text-indigo-600 hover:text-indigo-800 p-2 rounded-full hover:bg-indigo-50 transition-colors"
                                                    title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <i class="fas fa-box-open text-5xl mb-4"></i>
                                            <p class="text-xl font-semibold text-gray-500">No numbering account found</p>
                                            <a href="{{ route('numbering_account.create') }}"
                                                class="mt-6 inline-flex items-center px-5 py-3 bg-indigo-600 text-white text-lg rounded-lg hover:bg-indigo-700 transition">
                                                <i class="fas fa-plus mr-3"></i> Create First Numbering Account
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-5 bg-gray-50 border-t border-gray-100 flex justify-center">
                    {{ $numberingAccount->links() }}
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
@endsection

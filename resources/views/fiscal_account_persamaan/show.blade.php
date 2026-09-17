@extends('layouts.app')

@section('content')
    <div class="py-10">
        @php
            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
        @endphp
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <!-- Informasi Payment Method -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Informasi Fiscal Account Persamaan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">

                        <div>
                            <span class="font-medium">Kode Akun:</span>
                            <span class="ml-2">{{ $fiscal->kode_akun }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Nama Akun:</span>
                            <span class="ml-2">{{ $fiscal->nama_akun }}</span>
                        </div>
                    </div>
                </div>

                <!-- Detail Account -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Akun Terkait</h3>
                    <table class="w-full border-collapse border text-sm">
                        @php
                            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                        @endphp
                        <thead
                            class="bg-gradient-to-r bg-[{{ $themeColor }}]  to-blue-600 text-white text-sm font-semibold">
                            <tr>
                                <th class="border px-3 py-2 text-left w-[20%]">Kode Akun</th>
                                <th class="border px-3 py-2 text-left w-[30%]">Nama Akun</th>
                                <th class="border px-3 py-2 text-left w-[20%]">Tipe Akun</th>
                                <th class="border px-3 py-2 text-left w-[30%]">Level Akun</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($detail as $item)
                                <tr>
                                    <td class="border px-3 py-2">
                                        {{ $item->kode_akun ?? '-' }}
                                    </td>
                                    <td class="border px-3 py-2">
                                        {{ $item->nama_akun ?? '-' }}
                                    </td>
                                    <td class="border px-3 py-2">
                                        {{ $item->tipe_akun ?? '-' }}
                                    </td>
                                    <td class="border px-3 py-2">
                                        {{ $item->level_akun ?? '-' }}
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="border px-3 py-2 text-center text-gray-500">
                                        Tidak ada akun terkait
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">

                <a href="{{ route('fiscal_account_persamaan.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>
    </div>
@endsection

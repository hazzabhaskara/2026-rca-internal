@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <!-- Informasi Klasifikasi Akun -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Informasi Klasifikasi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <div>
                            <span class="font-medium">Nama Klasifikasi:</span>
                            <span class="ml-2">{{ $klasifikasi->nama_klasifikasi }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Group Numbering:</span>
                            <span class="ml-2">
                                {{ $klasifikasi->numberingAccount->nama_grup ?? '-' }}
                                ({{ $klasifikasi->numberingAccount->nomor_akun_awal ?? '' }} -
                                {{ $klasifikasi->numberingAccount->nomor_akun_akhir ?? '' }})
                            </span>
                        </div>
                        <div>
                            <span class="font-medium">Status:</span>
                            <span class="ml-2">
                                <span class="{{ $klasifikasi->aktif ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $klasifikasi->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="font-medium">Deskripsi:</span>
                            <span class="ml-2">{{ $klasifikasi->deskripsi ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('klasifikasiAkun.edit', $klasifikasi->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('klasifikasiAkun.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')


@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form action="{{ route('klasifikasiAkun.update', $klasifikasi->id) }}" method="POST">
                    @csrf
                    @method('PUT')


                    <h2 class="font-bold text-lg mb-3">Edit Klasifikasi Akun</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="kode_klasifikasi" class="block text-sm font-medium text-gray-700">Kode
                                Klasifikasi</label>
                            <input type="text" name="kode_klasifikasi" id="kode_klasifikasi"
                                value="{{ old('kode_klasifikasi', $klasifikasi->kode_klasifikasi) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('kode_klasifikasi')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="nama_klasifikasi" class="block text-sm font-medium text-gray-700">Nama
                                Klasifikasi</label>
                            <input type="text" name="nama_klasifikasi" id="nama_klasifikasi"
                                value="{{ old('nama_klasifikasi', $klasifikasi->nama_klasifikasi) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('nama_klasifikasi')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="numbering_account_id" class="block text-sm font-medium text-gray-700">Group
                                Numbering</label>
                            <select name="numbering_account_id" id="numbering_account_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Group --</option>
                                @foreach ($numberingAccounts as $group)
                                    <option value="{{ $group->id }}"
                                        {{ $klasifikasi->numbering_account_id == $group->id ? 'selected' : '' }}>
                                        {{ $group->nama_grup }}
                                    </option>
                                @endforeach
                            </select>
                            @error('numbering_account_id')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>



                        <div>
                            <label for="aktif" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="aktif" id="aktif"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="1" {{ $klasifikasi->aktif ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ !$klasifikasi->aktif ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                            @error('aktif')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="3" placeholder="Masukkan deskripsi"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('deskripsi', $klasifikasi->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="{{ route('klasifikasiAkun.index') }}"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-blue-700 transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

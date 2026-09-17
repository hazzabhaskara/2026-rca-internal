@extends('layouts.app')


@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form action="{{ route('jabatan.update', $jabatan->kd_jabatan) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Edit Jabatan
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="kd_jabatan" class="block font-medium">Kode vendors</label>
                            <input type="text" name="kd_jabatan" id="kd_jabatan"
                                value="{{ old('kd_jabatan', $jabatan->kd_jabatan) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('kd_jabatan') border-red-500 @enderror">
                            @error('kd_jabatan')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="desc_jabatan" class="block font-medium">Nama Jabatan</label>
                            <input type="text" name="nama_jabatan" id="nama_jabatan"
                                value="{{ old('nama_jabatan', $jabatan->nama_jabatan) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('nama_jabatan') border-red-500 @enderror"
                                required>
                            @error('nama_jabatan')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="desc_jabatan" class="block font-medium">Deskripsi Jabatan</label>
                            <input type="text" name="desc_jabatan" id="desc_jabatan"
                                value="{{ old('desc_jabatan', $jabatan->desc_jabatan) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('desc_jabatan') border-red-500 @enderror"
                                required>
                            @error('desc_jabatan')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('jabatan.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                            <i class="fas fa-save mr-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

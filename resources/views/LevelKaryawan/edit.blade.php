@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">

                @php
                    $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                @endphp
                <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                    <form action="{{ route('LevelKaryawan.update', $levelKaryawan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                            Employee Level Edit
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                            <div>
                                <label for="nama_klasifikasi" class="block text-sm font-medium text-gray-700">Nama</label>
                                <input type="text" name="nama_level" id="nama_klasifikasi"
                                    value="{{ old('nama_level', $levelKaryawan->nama_level) }}"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('nama_level')
                                    <span class="text-sm text-red-600">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                        <div class="mt-6">
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('deskripsi', $levelKaryawan->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mt-6 flex justify-end space-x-4">
                            <a href="{{ route('LevelKaryawan.index') }}"
                                class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                Process
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endsection

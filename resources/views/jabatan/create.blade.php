@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form method="POST"
                    action="{{ isset($jabatans) ? route('jabatan.update', $jabatans->id) : route('jabatan.store') }}">
                    @csrf
                    @if (isset($jabatans))
                        @method('PUT')
                    @endif

                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Create Jabatan
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- kode jabatans -->
                        <div class="mb-4" id="manual_kd">
                            <label for="kd_jabatan" class="block text-gray-700 font-medium mb-1">Kode jabatan</label>
                            <input type="text" id="kd_jabatan" name="kd_jabatan"
                                placeholder="Masukkan kode jabatan atau generate otomatis"
                                value="{{ old('kd_jabatan', $jabatans->kd_jabatan ?? '') }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="auto_generate" class="form-checkbox text-blue-600"
                                    onchange="toggleAutoGenerate()">
                                <span class="ml-2 text-sm text-gray-700">Generate kode jabatan secara otomatis</span>
                            </label>
                            @error('kd_jabatan')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama jabatan -->
                        <div class="mb-4">
                            <label for="nama_jabatan" class="block text-gray-700 font-medium mb-1">Nama jabatan</label>
                            <input type="text" id="name" name="nama_jabatan" placeholder="Masukkan nama jabatan"
                                required value="{{ old('nama_jabatan', $jabatans->nama_jabatan ?? '') }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('nama_jabatan')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- deskripsi jabatan -->
                        <div class="mb-4 md:col-span-2">
                            <label for="desc_jabatan" class="block text-gray-700 font-medium mb-1">Deskripsi</label>
                            <textarea id="desc_jabatan" name="desc_jabatan" placeholder="Masukkan deskripsi(opsional)" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('desc_jabatan', $jabatans->desc_jabatan ?? '') }}</textarea>
                            @error('desc_jabatan')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="{{ route('jabatan.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition">
                            {{ isset($jabatans) ? 'Update' : 'Process' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function toggleAutoGenerate() {
            const checkbox = document.getElementById('auto_generate');
            const manualField = document.getElementById('manual_kd');

            if (checkbox.checked) {
                manualField.style.display = 'none';
            } else {
                manualField.style.display = 'block';
            }
        }

        // Inisialisasi saat halaman pertama kali dimuat
        window.onload = toggleAutoGenerate;
    </script>
@endsection

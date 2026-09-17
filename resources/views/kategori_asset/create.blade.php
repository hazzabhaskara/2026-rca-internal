@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form method="POST"
                    action="{{ isset($kategori) ? route('kategori_asset.update', $kategori->id) : route('kategori_asset.store') }}">
                    @csrf
                    @if (isset($kategori))
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- kode kategori -->
                        <div class="mb-4" id="manual_kd">
                            <label for="kode_kategori" class="block text-gray-700 font-medium mb-1">Kode Kategori</label>
                            <input type="text" id="kode_kategori" name="kode_kategori"
                                value="{{ old('kode_kategori', $kategori->kode_kategori ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="auto_generate" class="form-checkbox text-blue-600"
                                    onchange="toggleAutoGenerate()">
                                <span class="ml-2 text-sm text-gray-700">Generate kode Kategori secara otomatis</span>
                            </label>
                            @error('kode_kategori')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama kategori_asset -->
                        <div class="mb-4">
                            <label for="nama_kategori" class="block text-gray-700 font-medium mb-1">Nama Kategori</label>
                            <input type="text" id="name" name="nama_kategori" required
                                value="{{ old('nama_kategori', $kategori->nama_kategori ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('nama_kategori')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- deskripsi kategori_asset -->
                        <div class="mb-4 md:col-span-2">
                            <label for="deskripsi" class="block text-gray-700 font-medium mb-1">Deksripsi</label>
                            <textarea id="deskripsi" name="deskripsi" rows="3"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('deskripsi', $kategori->deskripsi ?? '') }}</textarea>
                            @error('deskripsi')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="mt-6 flex space-x-4">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                            {{ isset($kategori) ? 'Update' : 'Create' }} kategori
                        </button>
                        <a href="{{ route('kategori_asset.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Cancel
                        </a>
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

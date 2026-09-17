@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form method="POST"
                    action="{{ isset($asset) ? route('tangibble_asset.update', $asset->id) : route('tangible_asset.store') }}">
                    @csrf
                    @if (isset($asset))
                        @method('PUT')
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- kode asset -->
                        <div class="mb-4" id="manual_kd">
                            <label for="kode_asset" class="block text-gray-700 font-medium mb-1">Kode asset</label>
                            <input type="text" id="kode_asset" name="kode_asset"
                                value="{{ old('kode_asset', $asset->kode_asset ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="auto_generate" class="form-checkbox text-blue-600"
                                    onchange="toggleAutoGenerate()">
                                <span class="ml-2 text-sm text-gray-700">Generate kode asset secara otomatis</span>
                            </label>
                            @error('kode_asset')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama asset_asset -->
                        <div class="mb-4">
                            <label for="nama_asset" class="block text-gray-700 font-medium mb-1">Nama asset</label>
                            <input type="text" id="name" name="nama_asset" required
                                value="{{ old('nama_asset', $asset->nama_asset ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('nama_asset')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- deskripsi asset_asset -->
                        <div class="mb-4">
                            <label for="kategori_id" class="block text-gray-700 font-medium mb-1">Kategori</label>
                            <select name="kategori_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach ($kategori as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="spesifikasi" class="block text-gray-700 font-medium mb-1">Component</label>
                            <input type="text" id="name" name="components" required
                                value="{{ old('components', $asset->components ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('components')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="spesifikasi" class="block text-gray-700 font-medium mb-1">Capacity</label>
                            <input type="text" id="name" name="capacity" required
                                value="{{ old('capacity', $asset->capacity ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('capacity')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="spesifikasi" class="block text-gray-700 font-medium mb-1">Brand</label>
                            <input type="text" id="name" name="merk" required
                                value="{{ old('merk', $asset->merk ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('merk')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="spesifikasi" class="block text-gray-700 font-medium mb-1">Type</label>
                            <input type="text" id="name" name="type" required
                                value="{{ old('type', $asset->type ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('type')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>



                        <!-- Lokasi -->
                        <div class="mb-4">
                            <label for="lokasi_id" class="block text-gray-700 font-medium mb-1">Lokasi</label>
                            <select name="lokasi_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach ($lokasi as $l)
                                    <option value="{{ $l->id }}">{{ $l->nama_lokasi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Golongan -->
                        <div class="mb-4">
                            <label for="golongan_id" class="block text-gray-700 font-medium mb-1">Golongan</label>
                            <select name="golongan_id" id="golongan_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Golongan --</option>
                                @foreach ($golongan as $l)
                                    <option value="{{ $l->id }}" data-masa="{{ $l->masa_tahun }}"
                                        data-tarif="{{ $l->tarif_penyusutan }}">
                                        {{ $l->nama_golongan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="spesifikasi" class="block text-gray-700 font-medium mb-1">Masa Manfaat(Dalam
                                Tahun)</label>
                            <input type="text" id="dalam_tahun" name="dalam_tahun" required
                                value="{{ old('dalam_tahun', $asset->dalam_tahun ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('dalam_tahun')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Metode Penyusutan -->
                        <div class="mb-4">
                            <label for="metode_penyusutan_id" class="block text-gray-700 font-medium mb-1">Metode
                                Penyusutan</label>
                            <select name="metode_penyusutan_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach ($metode_penyusutan as $l)
                                    <option value="{{ $l->id }}">{{ $l->nama_metode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="spesifikasi" class="block text-gray-700 font-medium mb-1">Tarif Penyusutan(%)
                            </label>
                            <input type="text" id="tarif_penyusutan" name="tarif_penyusutan" required
                                value="{{ old('tarif_penyusutan', $asset->tarif_penyusutan ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('tarif_penyusutan')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="mt-6 flex space-x-4">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                            {{ isset($asset) ? 'Update' : 'Create' }} asset
                        </button>
                        <a href="{{ route('tangible_asset.index') }}"
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectGolongan = document.getElementById('golongan_id');
            const inputTahun = document.getElementById('dalam_tahun');
            const inputTarif = document.getElementById('tarif_penyusutan');

            selectGolongan.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const masaTahun = selectedOption.getAttribute('data-masa');
                const masaTarif = selectedOption.getAttribute('data-tarif');

                inputTahun.value = masaTahun ?? '';
                inputTarif.value = masaTarif ?? '';
            });
        });
    </script>
@endsection

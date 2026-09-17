@extends('layouts.app')

@section('content')

    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form method="POST" action="{{ route('tangible_asset.update', $asset->id) }}">
                    @csrf
                    @method('PUT')

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

                        <!-- Kode Asset -->
                        <div class="mb-4">
                            <label for="kode_asset" class="block text-gray-700 font-medium mb-1">Kode Asset</label>
                            <input type="text" id="kode_asset" name="kode_asset"
                                value="{{ old('kode_asset', $asset->kode_asset) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Nama Asset -->
                        <div class="mb-4">
                            <label for="nama_asset" class="block text-gray-700 font-medium mb-1">Nama Asset</label>
                            <input type="text" id="nama_asset" name="nama_asset"
                                value="{{ old('nama_asset', $asset->nama_asset) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Kategori -->
                        <div class="mb-4">
                            <label for="kategori_id" class="block text-gray-700 font-medium mb-1">Kategori</label>
                            <select name="kategori_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach ($kategori as $k)
                                    <option value="{{ $k->id }}"
                                        {{ $k->id == $asset->kategori_id ? 'selected' : '' }}>
                                        {{ $k->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Components -->
                        <div class="mb-4">
                            <label for="components" class="block text-gray-700 font-medium mb-1">Component</label>
                            <input type="text" id="components" name="components"
                                value="{{ old('components', $asset->components) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Capacity -->
                        <div class="mb-4">
                            <label for="capacity" class="block text-gray-700 font-medium mb-1">Capacity</label>
                            <input type="text" id="capacity" name="capacity"
                                value="{{ old('capacity', $asset->capacity) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Merk -->
                        <div class="mb-4">
                            <label for="merk" class="block text-gray-700 font-medium mb-1">Brand</label>
                            <input type="text" id="merk" name="merk" value="{{ old('merk', $asset->merk) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Type -->
                        <div class="mb-4">
                            <label for="type" class="block text-gray-700 font-medium mb-1">Type</label>
                            <input type="text" id="type" name="type" value="{{ old('type', $asset->type) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Lokasi -->
                        <div class="mb-4">
                            <label for="lokasi_id" class="block text-gray-700 font-medium mb-1">Lokasi</label>
                            <select name="lokasi_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach ($lokasi as $l)
                                    <option value="{{ $l->id }}"
                                        {{ $l->id == $asset->lokasi_id ? 'selected' : '' }}>
                                        {{ $l->nama_lokasi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Golongan -->
                        <div class="mb-4">
                            <label for="golongan_id" class="block text-gray-700 font-medium mb-1">Golongan</label>
                            <select name="golongan_id" id="golongan_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                onchange="isiOtomatis()">
                                @foreach ($golongan as $g)
                                    <option value="{{ $g->id }}" data-masa="{{ $g->masa_tahun }}"
                                        data-tarif="{{ $g->tarif_penyusutan }}"
                                        {{ $g->id == $asset->golongan_id ? 'selected' : '' }}>
                                        {{ $g->nama_golongan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Masa Tahun -->
                        <div class="mb-4">
                            <label for="dalam_tahun" class="block text-gray-700 font-medium mb-1">Masa Manfaat
                                (Tahun)</label>
                            <input type="number" id="dalam_tahun" name="dalam_tahun"
                                value="{{ old('dalam_tahun', $asset->dalam_tahun) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Metode Penyusutan -->
                        <div class="mb-4">
                            <label for="metode_penyusutan_id" class="block text-gray-700 font-medium mb-1">Metode
                                Penyusutan</label>
                            <select name="metode_penyusutan_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach ($metode_penyusutan as $m)
                                    <option value="{{ $m->id }}"
                                        {{ $m->id == $asset->metode_penyusutan_id ? 'selected' : '' }}>
                                        {{ $m->nama_metode }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tarif Penyusutan -->
                        <div class="mb-4">
                            <label for="tarif_penyusutan" class="block text-gray-700 font-medium mb-1">Tarif Penyusutan
                                (%)</label>
                            <input type="text" id="tarif_penyusutan" name="tarif_penyusutan"
                                value="{{ old('tarif_penyusutan', $asset->tarif_penyusutan) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Tombol -->
                    <div class="mt-6 flex space-x-4">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                            Update Asset
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
        function isiOtomatis() {
            const select = document.getElementById('golongan_id');
            const selectedOption = select.options[select.selectedIndex];
            const masa = selectedOption.getAttribute('data-masa');
            const tarif = selectedOption.getAttribute('data-tarif');

            document.getElementById('dalam_tahun').value = masa;
            document.getElementById('tarif_penyusutan').value = tarif;
        }
    </script>
@endsection

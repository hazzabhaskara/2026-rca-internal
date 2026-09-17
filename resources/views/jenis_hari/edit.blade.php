@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form action="{{ route('jenis_hari.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Jenis Hari Edit
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="unit_kerja_id" class="block font-medium">Unit Kerja</label>
                            <select name="unit_kerja_id" id="unit_kerja_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih --</option>
                                @foreach ($unitKerja as $g)
                                    <option value="{{ $g->id }}"
                                        {{ isset($data) && $data->unit_kerja_id == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="nama" class="block font-medium">Nama</label>
                            <input type="text" name="nama" id="nama" value="{{ old('nama', $data->nama) }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none
                                focus:ring-2 focus:ring-blue-500 @error('nama') border-red-500 @enderror"
                                required>
                            @error('nama')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-6">
                            <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">
                                Jam Mulai

                            </label>
                            <input type="time" id="jam_mulai" name="jam_mulai"
                                value="{{ $data->jam_mulai ? \Carbon\Carbon::parse($data->jam_mulai)->format('H:i') : '' }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="mb-6">
                            <label for="jam_selesai" class="block text-sm font-medium text-gray-700 mb-1">
                                Jam Selesai

                            </label>
                            <input type="time" id="jam_selesai" name="jam_selesai"
                                value="{{ $data->jam_selesai ? \Carbon\Carbon::parse($data->jam_selesai)->format('H:i') : '' }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="jumlah_pengunjung_min" class="block font-medium">Jumlah Pengunjung Minimal</label>
                            <input type="number" name="jumlah_pengunjung_min" id="jumlah_pengunjung_min"
                                value="{{ old('jumlah_pengunjung_min', $data->jumlah_pengunjung_min) }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
                                          @error('jumlah_pengunjung_min') border-red-500 @enderror">
                            @error('jumlah_pengunjung_min')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="jumlah_pengunjung_max" class="block font-medium">Jumlah Pengunjung Maksimal</label>
                            <input type="number" name="jumlah_pengunjung_max" id="jumlah_pengunjung_max"
                                value="{{ old('jumlah_pengunjung_max', $data->jumlah_pengunjung_max) }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
                                          @error('jumlah_pengunjung_max') border-red-500 @enderror">
                            @error('jumlah_pengunjung_max')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="deskripsi" class="block font-medium">Deskripsi</label>
                            <input type="text" name="deskripsi" id="deskripsi"
                                value="{{ old('deskripsi', $data->deskripsi) }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
                                          @error('deskripsi') border-red-500 @enderror">
                            @error('deskripsi')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('jenis_hari.index') }}"
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

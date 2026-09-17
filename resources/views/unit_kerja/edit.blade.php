@extends('layouts.app')


@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form action="{{ route('unit_kerja.update', $unit_kerja->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Unit Kerja Edit
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="group_unit_id" class="block text-sm font-medium text-gray-700">Group Unit</label>
                            <select name="group_unit_id" id="group_unit_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih --</option>
                                @foreach ($groupUnit as $g)
                                    <option value="{{ $g->id }}"
                                        {{ isset($unit_kerja) && $unit_kerja->group_unit_id == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="kode_unit" class="block text-sm font-medium text-gray-700">Kode</label>
                            <input type="text" name="kode_unit" id="kode_unit"
                                value="{{ old('kode_unit', $unit_kerja->kode_unit) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('kode_unit')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label for="nama_unit" class="block text-sm font-medium text-gray-700">Nama</label>
                            <input type="text" name="nama_unit" id="nama_unit"
                                value="{{ old('nama_unit', $unit_kerja->nama_unit) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('nama_unit')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    <div class="mt-6">
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('deskripsi', $unit_kerja->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="urutan" class="block text-sm font-medium text-gray-700">Urutan</label>
                        <input type="number" name="urutan" id="urutan"
                            value="{{ old('urutan', $unit_kerja->urutan) }}"
                            class="w-1/3 border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('urutan')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label for="format_closing">Format Closing</label>
                        <select name="format_closing"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            id="format_closing" required>
                            <option value="1"
                                {{ old('format_closing', $unit_kerja->format_closing) == '1' ? 'selected' : '' }}>Format
                                DUNIA
                                FANTASI
                            </option>
                            <option value="2"
                                {{ old('format_closing', $unit_kerja->format_closing) == '2' ? 'selected' : '' }}>
                                Format OCEAN DREAM SAMUDRA</option>
                            <option value="3"
                                {{ old('format_closing', $unit_kerja->format_closing) == '3' ? 'selected' : '' }}>
                                Format AWA</option>
                            <option value="4" {{ old('format_closing', $unit_kerja->tipe) == '4' ? 'selected' : '' }}>
                                Format JAKARTA BIRD LAND</option>

                        </select>
                    </div>

                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="{{ route('unit_kerja.index') }}"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                            <i class="fas fa-arrow-left mr-1"></i> Cancel
                        </a>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                            <i class="fas fa-save mr-1"></i> Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

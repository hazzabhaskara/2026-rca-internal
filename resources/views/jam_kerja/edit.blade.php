@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form action="{{ route('jam_kerja.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="unit_kerja_id" class="block text-sm font-medium text-gray-700">Unit Kerja</label>
                            <select name="unit_kerja_id" id="unit_kerja_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih --</option>
                                @foreach ($unit_kerja as $g)
                                    <option value="{{ $g->id }}"
                                        {{ isset($data) && $data->unit_kerja_id == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="jam_masuk" class="block font-medium">Jam Masuk</label>
                            <input type="time" name="jam_masuk" id="jam_masuk"
                                value="{{ old('jam_masuk', $data->jam_masuk) }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none
                                focus:ring-2 focus:ring-blue-500 @error('jam_masuk') border-red-500 @enderror"
                                required>
                            @error('jam_masuk')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="jam_keluar" class="block font-medium">Jam Keluar</label>
                            <input type="time" name="jam_keluar" id="jam_keluar"
                                value="{{ old('jam_keluar', $data->jam_keluar) }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none
                                focus:ring-2 focus:ring-blue-500 @error('jam_keluar') border-red-500 @enderror"
                                required>
                            @error('jam_keluar')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('jam_kerja.index') }}"
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

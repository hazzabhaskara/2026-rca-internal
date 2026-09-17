@extends('layouts.app')
@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form action="{{ route('item_category.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="kode_kategori" class="block font-medium">Kode Item Category</label>
                            <input type="text" name="kode_kategori" id="kode_kategori"
                                value="{{ old('kode_kategori', $data->kode_kategori) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('kode_kategori') border-red-500 @enderror"
                                required>
                            @error('kode_kategori')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nama_kategori" class="block font-medium">Nama Item Category</label>
                            <input type="text" name="nama_kategori" id="nama_kategori"
                                value="{{ old('nama_kategori', $data->nama_kategori) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('nama_kategori') border-red-500 @enderror"
                                required>
                            @error('nama_kategori')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="deskripsi" class="block font-medium">Deskripsi</label>
                            <input type="text" name="deskripsi" id="deskripsi"
                                value="{{ old('deskripsi', $data->deskripsi) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('deskripsi') border-red-500 @enderror">
                            @error('deskripsi')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="1" {{ $data->status ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ !$data->status ? 'selected' : '' }}>Non Aktif</option>
                            </select>
                            @error('status')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('item_category.index') }}"
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

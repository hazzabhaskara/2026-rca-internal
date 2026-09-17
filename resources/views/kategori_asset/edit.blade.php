<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Kategori Asset
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form action="{{ route('kategori_asset.update', $data->kode_kategori) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="kode_kategori" class="block font-medium">Kode Kategori</label>
                            <input type="text" name="kode_kategori" id="kode_kategori" value="{{ old('kode_kategori', $data->kode_kategori) }}"
                                class="mt-1 block w-full rounded border-gray-300 shadow-sm 
                                          focus:border-indigo-500 focus:ring-indigo-500 
                                          @error('kode_kategori') border-red-500 @enderror">
                            @error('kode_kategori')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nama_kategori" class="block font-medium">Nama Jabatan</label>
                            <input type="text" name="nama_kategori" id="nama_kategori" value="{{ old('nama_kategori', $data->nama_kategori) }}"
                                class="mt-1 block w-full rounded border-gray-300 shadow-sm 
                                          focus:border-indigo-500 focus:ring-indigo-500 
                                          @error('nama_kategori') border-red-500 @enderror" required>
                            @error('nama_kategori')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="deskripsi" class="block font-medium">Deskripsi</label>
                            <input type="text" name="deskripsi" id="deskripsi" value="{{ old('deskripsi', $data->deskripsi) }}"
                                class="mt-1 block w-full rounded border-gray-300 shadow-sm 
                                          focus:border-indigo-500 focus:ring-indigo-500 
                                          @error('deskripsi') border-red-500 @enderror">
                            @error('deskripsi')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('kategori_asset.index') }}"
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
</x-app-layout>
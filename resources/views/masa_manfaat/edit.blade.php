@extends('layouts.app')


@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form action="{{ route('masa_manfaat.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="masa_tahun" class="block font-medium">Dalam Tahun </label>
                            <input type="number" name="masa_tahun" id="masa_tahun"
                                value="{{ old('masa_tahun', $data->masa_tahun) }}"
                                class="mt-1 block w-full rounded border-gray-300 shadow-sm 
                                          focus:border-indigo-500 focus:ring-indigo-500 
                                          @error('masa_tahun') border-red-500 @enderror"
                                required>
                            @error('masa_tahun')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="masa_bulan" class="block font-medium">Dalam Bulan </label>
                            <input type="number" name="masa_bulan" id="masa_bulan"
                                value="{{ old('masa_bulan', $data->masa_bulan) }}"
                                class="mt-1 block w-full rounded border-gray-300 shadow-sm 
                                          focus:border-indigo-500 focus:ring-indigo-500 
                                          @error('masa_bulan') border-red-500 @enderror"
                                required>
                            @error('masa_bulan')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="keterangan" class="block font-medium">Keterangan</label>
                            <input type="text" name="keterangan" id="keterangan"
                                value="{{ old('keterangan', $data->keterangan) }}"
                                class="mt-1 block w-full rounded border-gray-300 shadow-sm 
                                          focus:border-indigo-500 focus:ring-indigo-500 
                                          @error('keterangan') border-red-500 @enderror">
                            @error('keterangan')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('masa_manfaat.index') }}"
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tahunInput = document.getElementById('masa_tahun');
            const bulanInput = document.getElementById('masa_bulan');

            function hitungBulan() {
                const tahun = parseInt(tahunInput.value) || 0;
                bulanInput.value = tahun * 12;
            }

            tahunInput.addEventListener('input', hitungBulan);

            // Jalankan saat awal jika sudah ada nilai
            hitungBulan();
        });
    </script>
@endsection

@extends('layouts.app')


@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form action="{{ route('start_new_year.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')


                    <h2 class="font-bold text-lg mb-4">Edit Year Book</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="tahun" class="block font-medium"> Tahun </label>
                            <input type="number" name="tahun" id="tahun" value="{{ old('tahun', $data->tahun) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('tahun') border-red-500 @enderror"
                                required>
                            @error('tahun')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="awal_periode" class="block font-medium">Awal Periode </label>
                            <input type="date" name="awal_periode" id="awal_periode"
                                value="{{ old('awal_periode', $data->awal_periode) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('awal_periode') border-red-500 @enderror"
                                required>
                            @error('awal_periode')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="akhir_periode" class="block font-medium">Akhir Periode </label>
                            <input type="date" name="akhir_periode" id="akhir_periode"
                                value="{{ old('akhir_periode', $data->akhir_periode) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('akhir_periode') border-red-500 @enderror"
                                required>
                            @error('akhir_periode')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="status" class="block text-gray-700 font-medium mb-1">Status</label>
                            <select name="status" id="status" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih --</option>
                                <option value="Opening" {{ old('status', $data->status) == 'Opening' ? 'selected' : '' }}>
                                    Opening</option>
                                <option value="Closing" {{ old('status', $data->status) == 'Closing' ? 'selected' : '' }}>
                                    Closing</option>
                            </select>
                        </div>

                    </div>
                    <div class="mt-6 flex justify-end gap-4">
                        <a href="{{ route('start_new_year.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                            Kembali
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded hover:bg-yellow-600">
                            Simpan
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

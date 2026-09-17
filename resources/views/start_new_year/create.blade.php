@extends('layouts.app')


@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form method="POST" action="{{ route('start_new_year.store') }}">
                    @csrf

                    @if ($errors->any())
                        <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <h2 class="font-bold text-lg">Create Year Book</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <div class="mb-4">
                            <label for="tahun" class="block text-gray-700 font-medium mb-1">Tahun</label>
                            <input type="number" name="tahun"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline:ring-2 focus:ring-blue-500"
                                placeholder="Masukkan Tahun .....">
                        </div>
                        <div class="mb-4">
                            <label for="awal_periode" class="block text-gray-700 font-medium mb-1">Awal Periode</label>
                            <input type="date" name="awal_periode"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline:ring-2 focus:ring-blue-500"
                                placeholder="Masukkan awal periode .....">
                        </div>
                        <div class="mb-4">
                            <label for="akhir_periode" class="block text-gray-700 font-medium mb-1">Akhir Periode</label>
                            <input type="date" name="akhir_periode"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline:ring-2 focus:ring-blue-500"
                                placeholder="Masukkan Akhir Periode .....">
                            <input type="hidden" name="status" value="Opening">
                        </div>

                        <!-- Jenis -->
                        {{-- <div class="mb-4">
                            <label for="status" class="block text-gray-700 font-medium mb-1">Status</label>
                            <select name="status" id="status" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih --</option>
                                <option value="Opening">Opening</option>
                                <option value="Closing">Closing</option>
                            </select>
                            @error('status')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div> --}}
                    </div>

                    <!-- Submit -->
                    <div class="mt-6 flex justify-end gap-4">
                        <a href="{{ route('start_new_year.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2 ml-2 bg-green-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

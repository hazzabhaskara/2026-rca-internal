@extends('layouts.app')

@section('content')
    <div class="py-10 max-w-full mx-auto px-6">
        <h2 class="text-lg font-bold mb-4">PTKP Create</h2>

        <form action="{{ route('ptkp.store') }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm space-y-6">
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

            <div>
                <label class="block font-medium text-gray-700">Nama</label>
                <input type="text" name="nama"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>
            <div class="mb-4">
                <label for="tipe" class="block text-gray-700 font-medium mb-1">Golongan TER</label>
                <select name="kategori" id="kategori" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih --</option>
                    <option value="TER A" {{ old('kategori', $data->kategori ?? '') == 'TER A' ? 'selected' : '' }}>
                        TER A</option>
                    <option value="TER B" {{ old('kategori', $data->kategori ?? '') == 'TER B' ? 'selected' : '' }}>
                        TER B</option>
                    <option value="TER C" {{ old('kategori', $data->kategori ?? '') == 'TER C' ? 'selected' : '' }}>
                        TER C</option>
                </select>
                @error('tipe')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block font-medium text-gray-700">Nilai</label>
                <input type="number" name="nilai"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <div class="text-right">
                <a href="{{ route('ptkp.index') }}" class="btn btn-secondary mr-2">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection

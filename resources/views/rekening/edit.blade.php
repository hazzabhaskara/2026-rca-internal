@extends('layouts.app')

@section('content')
    <div class="py-10 max-w-full mx-auto px-6">
        @php
            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
        @endphp
        <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
            <form action="{{ route('rekening.update', $data->id) }}" method="POST"
                class="bg-white p-6 rounded-xl shadow-sm space-y-6">
                @csrf
                @method('PUT')

                <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                    Edit Rekening
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block font-medium text-gray-700">Atas Nama</label>
                        <input type="text" name="atas_nama" placeholder="Masukkan nama"
                            value="{{ old('atas_nama', $data->atas_nama) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700">Nama Bank</label>
                        <input type="text" name="nama_bank" placeholder="Masukkan nama bank"
                            value="{{ old('nama_bank', $data->nama_bank) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700">Nomor Rekening</label>
                        <input type="number" name="no_rek" value="{{ old('no_rek', $data->no_rek) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Masukkan nomor rekening" required>
                    </div>
                </div>
                <div class="text-right">
                    <a href="{{ route('rekening.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                    <button type="submit" class="btn btn-success">Process</button>
                </div>
            </form>
        </div>
    </div>
@endsection

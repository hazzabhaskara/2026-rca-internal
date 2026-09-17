@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6"> @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
                <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                    <form action="{{ route('fiscal_account.update', $data->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')


                        <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                            Fiscal Account Edit
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="kode_akun" class="block font-medium">Kode Akun</label>
                                <input type="text" name="kode_akun" id="kode_akun"
                                    value="{{ old('kode_akun', $data->kode_akun) }}"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none
                                focus:ring-2 focus:ring-blue-500 @error('kode_akun') border-red-500 @enderror"
                                    required>
                                @error('kode_akun')
                                    <p class="text-red-500 text-sm">{{ $message }}</p>
                                @enderror
                            </div>


                            <div>
                                <label for="nama_akun" class="block font-medium">Nama Akun</label>
                                <input type="text" name="nama_akun" id="nama_akun"
                                    value="{{ old('nama_akun', $data->nama_akun) }}"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
                                          @error('nama_akun') border-red-500 @enderror">
                                @error('nama_akun')
                                    <p class="text-red-500 text-sm">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <a href="{{ route('fiscal_account.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded hover:bg-yellow-600">
                                Process
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endsection

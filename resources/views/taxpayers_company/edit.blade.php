@extends('layouts.app')


@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form action="{{ route('taxpayers_company.update', $taxpayers->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Informasi Kontak
                    </h4>
                    <hr>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="nama_perusahaan" class="block font-medium">Nama Perusahaan</label>
                            <input type="text" name="nama_perusahaan" id="nama_perusahaan"
                                value="{{ old('nama_perusahaan', $taxpayers->nama_perusahaan) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                focus:border-indigo-500 focus:ring-indigo-500
                                @error('nama_perusahaan') border-red-500 @enderror">
                            @error('nama_perusahaan')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="phone_number" class="block font-medium">No.Telepon</label>
                            <input type="number" name="phone_number" id="phone_number"
                                value="{{ old('phone_number', $taxpayers->phone_number) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                focus:border-indigo-500 focus:ring-indigo-500
                                @error('phone_number') border-red-500 @enderror" required>
                            @error('phone_number')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block font-medium">Email</label>
                            <input type="email" name="email" id="email"
                                value="{{ old('email', $taxpayers->email) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror"
                                required>
                            @error('email')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Informasi Alamat
                    </h4>
                    <hr>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="mb-4">
                            <label for="provinsi_id" class="block text-gray-700 font-medium mb-1">Provinsi</label>
                            <select name="id_provinsi" id="provinsi_id" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Provinsi --</option>
                                @foreach ($provinsi as $pro)
                                    <option value="{{ $pro->id }}"
                                        {{ old('id_provinsi', $taxpayers->id_provinsi ?? '') == $pro->id ? 'selected' : '' }}>
                                        {{ $pro->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_provinsi')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="kota_id" class="block text-gray-700 font-medium mb-1">Kota</label>
                            <select name="id_kota" id="kota_id" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Kota --</option>
                                @foreach ($kota as $kot)
                                    <option value="{{ $kot->id }}"
                                        {{ old('id_kota', $taxpayers->id_kota ?? '') == $kot->id ? 'selected' : '' }}>
                                        {{ $kot->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kota')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="kecamatan_id" class="block text-gray-700 font-medium mb-1">Kecamatan</label>
                            <select name="id_kecamatan" id="kecamatan_id" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Kecamatan --</option>
                                @foreach ($kecamatan as $kec)
                                    <option value="{{ $kec->id }}"
                                        {{ old('id_kecamatan', $taxpayers->id_kecamatan ?? '') == $kec->id ? 'selected' : '' }}>
                                        {{ $kec->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kecamatan')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="kelurahan_id" class="block text-gray-700 font-medium mb-1">Kelurahan</label>
                            <select name="id_kelurahan" id="kelurahan_id" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Kelurahan --</option>
                                @foreach ($kelurahan as $kel)
                                    <option value="{{ $kel->id }}"
                                        {{ old('id_kelurahan', $taxpayers->id_kelurahan ?? '') == $kel->id ? 'selected' : '' }}>
                                        {{ $kel->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kelurahan')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jalan" class="block font-medium">Alamat</label>
                            <input type="text" name="jalan" id="jalan"
                                value="{{ old('jalan', $taxpayers->jalan) }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('jalan') border-red-500 @enderror"
                                required>
                            @error('jalan')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="kode_pos" class="block font-medium">Kode Pos</label>
                            <input type="text" name="kode_pos" id="kode_pos"
                                value="{{ old('kode_pos', $taxpayers->kode_pos) }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('kode_pos') border-red-500 @enderror"
                                required>
                            @error('kode_pos')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Informasi Pajak
                    </h4>
                    <hr>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="bentuk_badan_hukum" class="block font-medium">Bentuk Badan Hukum</label>
                            <input type="text" name="bentuk_badan_hukum" id="bentuk_badan_hukum"
                                value="{{ old('bentuk_badan_hukum', $taxpayers->bentuk_badan_hukum) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                focus:border-indigo-500 focus:ring-indigo-500
                                @error('bentuk_badan_hukum') border-red-500 @enderror" required>
                            @error('bentuk_badan_hukum')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="npwp" class="block font-medium">NPWP</label>
                            <input type="text" name="npwp" id="npwp"
                                value="{{ old('npwp', $taxpayers->npwp) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                focus:border-indigo-500 focus:ring-indigo-500 @error('npwp') border-red-500 @enderror"
                                required>
                            @error('npwp')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="klu_code" class="block font-medium">KLU Code</label>
                            <input type="text" name="klu_code" id="klu_code"
                                value="{{ old('klu_code', $taxpayers->klu_code) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                focus:border-indigo-500 focus:ring-indigo-500 @error('klu_code') border-red-500 @enderror"
                                required>
                            @error('klu_code')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="klu_description" class="block font-medium">KLU Deskripsi</label>
                            <input type="text" name="klu_description" id="klu_description"
                                value="{{ old('klu_description', $taxpayers->klu_description) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                focus:border-indigo-500 focus:ring-indigo-500
                                @error('klu_description') border-red-500 @enderror" required>
                            @error('klu_description')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="tax_office" class="block font-medium">Tax Office</label>
                            <input type="text" name="tax_office" id="tax_office"
                                value="{{ old('tax_office', $taxpayers->tax_office) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                focus:border-indigo-500 focus:ring-indigo-500 @error('tax_office') border-red-500 @enderror"
                                required>
                            @error('tax_office')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
            </div>
            <div class="mt-6 flex justify-end gap-6">
                <a href="{{ route('taxpayers_company.show', $taxpayers->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-yellow-600">
                    <i class="fas fa-save mr-1"></i> Update
                </button>
            </div>
            </form>
        </div>
    </div>
    </div>
@endsection

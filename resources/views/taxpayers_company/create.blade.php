@extends('layouts.app')
@section('content')
    <div class="py-10 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <h2 class="font-bold text-2xl">Create Taxpayers Company</h2>
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4 border-blue-600">
                <form method="POST" enctype="multipart/form-data"
                    action="{{ isset($taxpayers) ? route('taxpayers_company.update', $taxpayers->id) : route('taxpayers_company.store') }}">

                    @csrf
                    @if (isset($taxpayers))
                        @method('PUT')
                    @endif
                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Informasi Kontak
                    </h4>
                    <hr>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Nama jabatan -->
                        <div class="mb-4">
                            <label for="nama_perusahaan" class="block text-gray-700 font-medium mb-1">Nama Wajib
                                Pajak</label>
                            <input type="text" id="name" name="nama_perusahaan"
                                placeholder="Masukkan nama wajib pajak" required
                                value="{{ old('nama_perusahaan', $taxpayers->nama_perusahaan ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('nama_perusahaan')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="font-weight-bold">Logo</label>
                            <input type="file" class="form-control @error('logo') is-invalid @enderror" name="logo">

                            <!-- error message untuk title -->
                            @error('logo')
                                <div class="alert alert-danger mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="npwp" class="block text-gray-700 font-medium mb-1">NPWP</label>
                            <input type="text" id="npwp" name="npwp" required placeholder="Masukkan NPWP"
                                value="{{ old('npwp', $taxpayers->npwp ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('npwp')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="phone_number" class="block text-gray-700 font-medium mb-1">No.Tlp/Hp (Tercatat
                                di
                                Coretax)</label>
                            <input type="number" id="name" name="phone_number" placeholder="Masukkan No.Telepon"
                                required value="{{ old('phone_number', $taxpayers->phone_number ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('phone_number')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 font-medium mb-1">Email (Tercatat di
                                Coretax)</label>
                            <input type="email" id="name" name="email" placeholder="Masukkan Email" required
                                value="{{ old('email', $taxpayers->email ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('email')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>


                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Informasi Alamat
                    </h4>
                    <hr>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="mb-4">
                            <label for="provinsi" class="block text-gray-700 font-medium mb-1">Provinsi</label>
                            <select name="id_provinsi" id="provinsi_id" class="w-full border rounded px-2 py-1 text-sm"
                                required>
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
                            <label for="provinsi" class="block text-gray-700 font-medium mb-1">Kota</label>
                            <select name="id_kota" id="kota_id" class="w-full border rounded px-2 py-1 text-sm" required>
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
                            <label for="kecamatan" class="block text-gray-700 font-medium mb-1">Kecamatan</label>
                            <select name="id_kecamatan" id="kecamatan_id" class="w-full border rounded px-2 py-1 text-sm"
                                required>
                                <option value="">-- kecamatan --</option>
                                @foreach ($kecamatan as $kel)
                                    <option value="{{ $kel->id }}"
                                        {{ old('id_kecamatan', $taxpayers->id_kecamatan ?? '') == $kel->id ? 'selected' : '' }}>
                                        {{ $kel->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kecamatan')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="kelurahan" class="block text-gray-700 font-medium mb-1">Kelurahan</label>
                            <select name="id_kelurahan" id="kelurahan_id" class="w-full border rounded px-2 py-1 text-sm"
                                required>
                                <option value="">-- kelurahan --</option>
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

                        <div class="mb-4">
                            <label for="kode_pos" class="block text-gray-700 font-medium mb-1">Kode Pos</label>
                            <input type="number" id="name" name="kode_pos" placeholder="Masukkan Kode Pos" required
                                value="{{ old('kode_pos', $company_profile->kode_pos ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('kode_pos')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- deskripsi jabatan -->
                        <div class="mb-4 md:col-span-2">
                            <label for="jalan" class="block text-gray-700 font-medium mb-1">Alamat</label>
                            <textarea id="jalan" name="jalan" rows="3" placeholder="Masukkan alamat"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('jalan', $company_profile->jalan ?? '') }}</textarea>
                            @error('jalan')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Informasi Pajak
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                        <div class="mb-4">
                            <label for="bentuk_badan_hukum" class="block text-gray-700 font-medium mb-1">Bentuk Badan
                                Hukum</label>
                            <input type="text" id="bentuk_badan_hukum" name="bentuk_badan_hukum" required
                                placeholder="Masukkan Bentuk Badan Hukum"
                                value="{{ old('bentuk_badan_hukum', $taxpayers->bentuk_badan_hukum ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('bentuk_badan_hukum')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="klu_code" class="block text-gray-700 font-medium mb-1">KLU Code</label>
                            <input type="text" id="klu_code" name="klu_code" required
                                placeholder="Masukkan KLU Code" value="{{ old('klu_code', $taxpayers->klu_code ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('klu_code')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4 md:col-span-2">
                            <label for="klu_description" class="block text-gray-700 font-medium mb-1">KLU
                                Description</label>
                            <textarea id="klu_description" name="klu_description" rows="3" placeholder="Masukkan KLU Description"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('klu_description', $taxpayers->klu_description ?? '') }}</textarea>
                            @error('klu_description')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="tax_office" class="block text-gray-700 font-medium mb-1">Tax Office</label>
                            <input type="text" id="tax_office" name="tax_office" required
                                placeholder="Masukkan Tax Office"
                                value="{{ old('tax_office', $taxpayers->tax_office ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('tax_office')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
            </div>

            <!-- Buttons -->
            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('taxpayers_company.index') }}"
                    class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                    {{ isset($taxpayers) ? 'Update' : 'Process' }}
                </button>

            </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        function toggleAutoGenerate() {
            const checkbox = document.getElementById('auto_generate');
            const manualField = document.getElementById('manual_kd');

            if (checkbox.checked) {
                manualField.style.display = 'none';
            } else {
                manualField.style.display = 'block';
            }
        }

        // Inisialisasi saat halaman pertama kali dimuat
        window.onload = toggleAutoGenerate;
    </script>


    <script>
        $(document).ready(function() {
            function initSelect2(selector, url, mapper, placeholder) {
                $(selector).select2({
                    placeholder: placeholder,
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.map(mapper)
                            };
                        },
                        cache: true
                    },
                    allowClear: true,
                    width: '100%'
                });
            }

            // ✅ Customers
            initSelect2(
                '#provinsi_id',
                '{{ route('company.searchProvinsi') }}',
                function(provinsi) {
                    return {
                        id: provinsi.id,
                        text: provinsi.name
                    };
                },
                "-- Provinsi --"
            );

            // ✅ Employees
            initSelect2(
                '#kota_id',
                '{{ route('company.searchKota') }}',
                function(kota) {
                    return {
                        id: kota.id,
                        text: kota.name
                    };
                },
                "-- Kota --"
            );
            initSelect2(
                '#kelurahan_id',
                '{{ route('company.searchKelurahan') }}',
                function(kelurahan) {
                    return {
                        id: kelurahan.id,
                        text: kelurahan.name
                    };
                },
                "-- kelurahan --"
            );
            initSelect2(
                '#kecamatan_id',
                '{{ route('company.searchKecamatan') }}',
                function(kecamatan) {
                    return {
                        id: kecamatan.id,
                        text: kecamatan.name
                    };
                },
                "-- kecamatan --"
            );
        });
    </script>
@endsection

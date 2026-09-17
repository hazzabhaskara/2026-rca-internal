@extends('layouts.app')


@section('content')
    <div class="py-10 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            {{-- Tab Navigation --}}
            <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                <li><a href="#company_data" class="tab-link">Company Data</a></li>
                <li><a href="#legal_document" class="tab-link">Legal Document</a></li>
            </ul>
            <form method="POST" enctype="multipart/form-data"
                action="{{ isset($company_profile) ? route('company_profile.update', $company_profile->id) : route('company_profile.store') }}">

                @csrf
                @if (isset($company_profile))
                    @method('PUT')
                @endif

                <div class=" tab-content" id="company_data">
                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Informasi Kontak
                    </h4>
                    <hr>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="mb-4">
                            <label for="nama_perusahaan" class="block text-gray-700 font-medium mb-1">Nama
                                Perusahaan</label>
                            <input type="text" id="name" name="nama_perusahaan"
                                placeholder="Masukkan Nama Perusahaan"
                                value="{{ old('nama_perusahaan', $company_profile->nama_perusahaan ?? '') }}"
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
                            <label for="phone_number" class="block text-gray-700 font-medium mb-1">Nomor
                                Handphone</label>
                            <input type="number" id="name" name="phone_number" placeholder="Masukkan nomor handphone"
                                value="{{ old('phone_number', $company_profile->phone_number ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('phone_number')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                            <input type="email" id="name" name="email" placeholder="Masukkan email"
                                value="{{ old('email', $company_profile->email ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('email')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <!-- Nama jabatan -->

                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Informasi Alamat
                    </h4>
                    <hr>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="mb-4">
                            <label for="provinsi" class="block text-gray-700 font-medium mb-1">Provinsi</label>
                            <select name="id_provinsi" id="provinsi_id" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Provinsi --</option>
                                @foreach ($provinsi as $pro)
                                    <option value="{{ $pro->id }}"
                                        {{ old('id_provinsi', $company_profile->id_provinsi ?? '') == $pro->id ? 'selected' : '' }}>
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
                            <select name="id_kota" id="kota_id" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Kota --</option>
                                @foreach ($kota as $kot)
                                    <option value="{{ $kot->id }}"
                                        {{ old('id_kota', $company_profile->id_kota ?? '') == $kot->id ? 'selected' : '' }}>
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
                            <select name="id_kecamatan" id="kecamatan_id" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- kecamatan --</option>
                                @foreach ($kecamatan as $kel)
                                    <option value="{{ $kel->id }}"
                                        {{ old('id_kecamatan', $company_profile->id_kecamatan ?? '') == $kel->id ? 'selected' : '' }}>
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
                            <select name="id_kelurahan" id="kelurahan_id" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- kelurahan --</option>
                                @foreach ($kelurahan as $kel)
                                    <option value="{{ $kel->id }}"
                                        {{ old('id_kelurahan', $company_profile->id_kelurahan ?? '') == $kel->id ? 'selected' : '' }}>
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
                            <input type="number" id="name" name="kode_pos" placeholder="Masukkan Kode Pos"
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
                    <hr>
                </div>

                <div class="tab-content hidden grid grid-cols-1 md:grid-cols-4 gap-6" id="legal_document">


                    <!-- Nama jabatan -->
                    <div class="mb-4">
                        <label for="akte_pendirian" class="block text-gray-700 font-medium mb-1">Data Pendirian
                        </label>
                        <input type="file" id="name" name="akte_pendirian" accept="application/pdf"
                            value="{{ old('akte_pendirian', $company_profile->akte_pendirian ?? '') }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('akte_pendirian')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="akte_perubahan_terakhir" class="block text-gray-700 font-medium mb-1">Akte
                            Perubahan Terakhir

                        </label>
                        <input type="file" id="name" name="akte_perubahan_terakhir" accept="application/pdf"
                            value="{{ old('akte_perubahan_terakhir', $company_profile->akte_perubahan_terakhir ?? '') }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('akte_perubahan_terakhir')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="skkemenkumhan" class="block text-gray-700 font-medium mb-1">SKKEMENKUMHAN
                        </label>
                        <input type="file" id="name" name="skkemenkumhan" accept="application/pdf"
                            value="{{ old('skkemenkumhan', $company_profile->skkemenkumhan ?? '') }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('skkemenkumhan')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="bnri" class="block text-gray-700 font-medium mb-1">BNRI
                        </label>
                        <input type="file" id="name" name="bnri" accept="application/pdf"
                            value="{{ old('bnri', $company_profile->bnri ?? '') }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('bnri')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="nib" class="block text-gray-700 font-medium mb-1">NIB


                        </label>
                        <input type="file" id="name" name="nib" accept="application/pdf"
                            value="{{ old('nib', $company_profile->nib ?? '') }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('nib')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="npwp_perusahaan" class="block text-gray-700 font-medium mb-1">NPWP Perusahaan


                        </label>
                        <input type="file" id="name" name="npwp_perusahaan" accept="application/pdf"
                            value="{{ old('npwp_perusahaan', $company_profile->npwp_perusahaan ?? '') }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('npwp_perusahaan')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="sppl" class="block text-gray-700 font-medium mb-1">SPPL


                        </label>
                        <input type="file" id="name" name="sppl" accept="application/pdf"
                            value="{{ old('sppl', $company_profile->sppl ?? '') }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('sppl')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="sptataruang" class="block text-gray-700 font-medium mb-1">SPTATARUANG

                        </label>
                        <input type="file" id="name" name="sptataruang" accept="application/pdf"
                            value="{{ old('sptataruang', $company_profile->sptataruang ?? '') }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('sptataruang')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="ktp_pemegang_saham" class="block text-gray-700 font-medium mb-1">KTP Pemegang
                            Saham

                        </label>
                        <input type="file" id="name" name="ktp_pemegang_saham" accept="application/pdf"
                            value="{{ old('ktp_pemegang_saham', $company_profile->ktp_pemegang_saham ?? '') }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('ktp_pemegang_saham')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="K3L" class="block text-gray-700 font-medium mb-1">K3L
                        </label>
                        <input type="file" id="name" name="K3L" accept="application/pdf"
                            value="{{ old('K3L', $company_profile->K3L ?? '') }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('K3L')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Buttons -->
                <div class="mt-6 flex justify-end space-x-4">
                    <a href="{{ route('company_profile.index') }}"
                        class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                        {{ isset($company_profile) ? 'Update' : 'Process' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
    {{-- Script Tab Navigation --}}
    <script>
        const links = document.querySelectorAll('.tab-link');
        const tabs = document.querySelectorAll('.tab-content');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                tabs.forEach(tab => tab.classList.add('hidden'));
                const id = this.getAttribute('href');
                document.querySelector(id).classList.remove('hidden');
            });
        });
    </script>
@endsection

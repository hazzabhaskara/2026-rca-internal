    @extends('layouts.app')
    @section('content')
        <div class="py-10">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">
                @php
                    $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                @endphp
                <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                    <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                        <li><a href="#company_data" class="tab-link">Company Data</a></li>
                        <li><a href="#legal_document" class="tab-link">Legal Document</a></li>
                    </ul>
                    <form action="{{ route('company_profile.update', $informasiPerusahaan->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <h2 class="font-bold text-lg">Company Profile Edit</h2>
                        <div class="tab-content" id="company_data">
                            <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                                Informasi Kontak
                            </h4>
                            <hr>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                                <div>
                                    <label for="nama_perusahaan" class="block font-medium">Nama Perusahaan</label>
                                    <input type="text" name="nama_perusahaan" id="nama_perusahaan"
                                        value="{{ old('nama_perusahaan', $informasiPerusahaan->nama_perusahaan) }}"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
                                            @error('nama_perusahaan') border-red-500 @enderror">
                                    @error('nama_perusahaan')
                                        <p class="text-red-500 text-sm">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="phone_number" class="block font-medium">No.Telepon</label>
                                    <input type="number" name="phone_number" id="phone_number"
                                        value="{{ old('phone_number', $informasiPerusahaan->phone_number) }}"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
                                            @error('phone_number') border-red-500 @enderror"
                                        required>
                                    @error('phone_number')
                                        <p class="text-red-500 text-sm">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="email" class="block font-medium">Email</label>
                                    <input type="email" name="email" id="email"
                                        value="{{ old('email', $informasiPerusahaan->email) }}"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
                                            @error('email') border-red-500 @enderror"
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
                                    <select name="id_provinsi" id="provinsi_id"
                                        class="w-full border rounded px-2 py-1 text-sm">
                                        <option value="">-- Provinsi --</option>
                                        @foreach ($provinsi as $pro)
                                            <option value="{{ $pro->id }}"
                                                {{ old('id_provinsi', $informasiPerusahaan->id_provinsi ?? '') == $pro->id ? 'selected' : '' }}>
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
                                                {{ old('id_kota', $informasiPerusahaan->id_kota ?? '') == $kot->id ? 'selected' : '' }}>
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
                                    <select name="id_kecamatan" id="kecamatan_id"
                                        class="w-full border rounded px-2 py-1 text-sm">
                                        <option value="">-- Kecamatan --</option>
                                        @foreach ($kecamatan as $kec)
                                            <option value="{{ $kec->id }}"
                                                {{ old('id_kecamatan', $informasiPerusahaan->id_kecamatan ?? '') == $kec->id ? 'selected' : '' }}>
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
                                    <select name="id_kelurahan" id="kelurahan_id"
                                        class="w-full border rounded px-2 py-1 text-sm">
                                        <option value="">-- Kelurahan --</option>
                                        @foreach ($kelurahan as $kel)
                                            <option value="{{ $kel->id }}"
                                                {{ old('id_kelurahan', $informasiPerusahaan->id_kelurahan ?? '') == $kel->id ? 'selected' : '' }}>
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
                                        value="{{ old('jalan', $informasiPerusahaan->jalan) }}"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('jalan') border-red-500 @enderror"
                                        required>
                                    @error('jalan')
                                        <p class="text-red-500 text-sm">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="kode_pos" class="block font-medium">Kode Pos</label>
                                    <input type="text" name="kode_pos" id="kode_pos"
                                        value="{{ old('kode_pos', $informasiPerusahaan->kode_pos) }}"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('kode_pos') border-red-500 @enderror"
                                        required>
                                    @error('kode_pos')
                                        <p class="text-red-500 text-sm">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                </div>

                <div class="tab-content hidden grid grid-cols-1 md:grid-cols-2 gap-6" id="legal_document">
                    @php
                        $documents = $informasiPerusahaan->legalDocuments->keyBy('jenis_dokumen');
                    @endphp
                    <div class="mb-3">
                        <label class="block font-medium">Akte Pendirian</label>
                        @if ($documents->has('Akte Pendirian'))
                            <p class="text-sm mb-1">
                                📄 <a href="{{ asset('storage/' . $documents['Akte Pendirian']->file_path) }}"
                                    target="_blank" class="text-blue-600 underline">Lihat</a>
                                | <a href="{{ asset('storage/' . $documents['Akte Pendirian']->file_path) }}" download
                                    class="text-green-600 underline">Download</a>
                            </p>
                        @endif
                        <input type="file" name="akte_pendirian" accept="application/pdf"
                            class="w-full border px-3 py-2 rounded">
                    </div>


                    <div class="mb-3">
                        <label class="block font-medium">Akte Perubahan Terakhir</label>
                        @if ($documents->has('Akte Perubahan Terakhir'))
                            <p class="text-sm mb-1">
                                📄 <a href="{{ asset('storage/' . $documents['Akte Perubahan Terakhir']->file_path) }}"
                                    target="_blank" class="text-blue-600 underline">Lihat</a>
                                | <a href="{{ asset('storage/' . $documents['Akte Perubahan Terakhir']->file_path) }}"
                                    download class="text-green-600 underline">Download</a>
                            </p>
                        @endif
                        <input type="file" name="akta_perubahan_terakhir" accept="application/pdf"
                            class="w-full border px-3 py-2 rounded">
                    </div>
                    <div class="mb-3">
                        <label class="block font-medium">SKKEMENKUMHAN</label>
                        @if ($documents->has('SKKEMENKUMHAN'))
                            <p class="text-sm mb-1">
                                📄 <a href="{{ asset('storage/' . $documents['SKKEMENKUMHAN']->file_path) }}"
                                    target="_blank" class="text-blue-600 underline">Lihat</a>
                                | <a href="{{ asset('storage/' . $documents['SKKEMENKUMHAN']->file_path) }}" download
                                    class="text-green-600 underline">Download</a>
                            </p>
                        @endif
                        <input type="file" name="skkemenkumhan" accept="application/pdf"
                            class="w-full border px-3 py-2 rounded">
                    </div>
                    <div class="mb-3">
                        <label class="block font-medium">BNRI</label>
                        @if ($documents->has('BNRI'))
                            <p class="text-sm mb-1">
                                📄 <a href="{{ asset('storage/' . $documents['BNRI']->file_path) }}" target="_blank"
                                    class="text-blue-600 underline">Lihat</a>
                                | <a href="{{ asset('storage/' . $documents['BNRI']->file_path) }}" download
                                    class="text-green-600 underline">Download</a>
                            </p>
                        @endif
                        <input type="file" name="bnri" accept="application/pdf"
                            class="w-full border px-3 py-2 rounded">
                    </div>
                    <div class="mb-3">
                        <label class="block font-medium">NIB</label>
                        @if ($documents->has('NIB'))
                            <p class="text-sm mb-1">
                                📄 <a href="{{ asset('storage/' . $documents['NIB']->file_path) }}" target="_blank"
                                    class="text-blue-600 underline">Lihat</a>
                                | <a href="{{ asset('storage/' . $documents['NIB']->file_path) }}" download
                                    class="text-green-600 underline">Download</a>
                            </p>
                        @endif
                        <input type="file" name="nib" accept="application/pdf"
                            class="w-full border px-3 py-2 rounded">
                    </div>
                    <div class="mb-3">
                        <label class="block font-medium">NPWP Perusahaan</label>
                        @if ($documents->has('NPWP Perusahaan'))
                            <p class="text-sm mb-1">
                                📄 <a href="{{ asset('storage/' . $documents['NPWP Perusahaan']->file_path) }}"
                                    target="_blank" class="text-blue-600 underline">Lihat</a>
                                | <a href="{{ asset('storage/' . $documents['NPWP Perusahaan']->file_path) }}" download
                                    class="text-green-600 underline">Download</a>
                            </p>
                        @endif
                        <input type="file" name="npwp_perusahaan" accept="application/pdf"
                            class="w-full border px-3 py-2 rounded">
                    </div>
                    <div class="mb-3">
                        <label class="block font-medium">SPPL</label>
                        @if ($documents->has('SPPL'))
                            <p class="text-sm mb-1">
                                📄 <a href="{{ asset('storage/' . $documents['SPPL']->file_path) }}" target="_blank"
                                    class="text-blue-600 underline">Lihat</a>
                                | <a href="{{ asset('storage/' . $documents['SPPL']->file_path) }}" download
                                    class="text-green-600 underline">Download</a>
                            </p>
                        @endif
                        <input type="file" name="sppl" accept="application/pdf"
                            class="w-full border px-3 py-2 rounded">
                    </div>
                    <div class="mb-3">
                        <label class="block font-medium">KTP Pemegang Saham</label>
                        @if ($documents->has('KTP Pemegang Saham'))
                            <p class="text-sm mb-1">
                                📄 <a href="{{ asset('storage/' . $documents['KTP Pemegang Saham']->file_path) }}"
                                    target="_blank" class="text-blue-600 underline">Lihat</a>
                                | <a href="{{ asset('storage/' . $documents['KTP Pemegang Saham']->file_path) }}" download
                                    class="text-green-600 underline">Download</a>
                            </p>
                        @endif
                        <input type="file" name="ktp_pemegang_saham" accept="application/pdf"
                            class="w-full border px-3 py-2 rounded">
                    </div>
                    <div class="mb-3">
                        <label class="block font-medium">K3L</label>
                        @if ($documents->has('K3L'))
                            <p class="text-sm mb-1">
                                📄 <a href="{{ asset('storage/' . $documents['K3L']->file_path) }}" target="_blank"
                                    class="text-blue-600 underline">Lihat</a>
                                | <a href="{{ asset('storage/' . $documents['K3L']->file_path) }}" download
                                    class="text-green-600 underline">Download</a>
                            </p>
                        @endif
                        <input type="file" name="k3l" accept="application/pdf"
                            class="w-full border px-3 py-2 rounded">
                    </div>
                </div>
                <div class="mt-6 flex justify-between">
                    <a href="{{ route('company_profile.show', $informasiPerusahaan->id) }}"
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
    @endsection

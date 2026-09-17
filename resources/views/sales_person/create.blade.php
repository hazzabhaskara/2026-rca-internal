@extends('layouts.app')

@section('content')
    <div class="py-10 max-w-full mx-auto px-6">
        @php
            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
        @endphp
        <form action="{{ route('sales_person.store') }}" enctype="multipart/form-data" method="POST"
            class="bg-white shadow-lg
            rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">

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

            {{-- Data Pribadi --}}

            <h2 class="font-bold text-lg">Create Sales Person</h2>

            <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                Informasi Pribadi
            </h4>
            <div class="grid grid-cols-4 gap-4 mb-4">
                <div>
                    <label>Kode Karyawan</label>
                    <input type="text" name="kode_karyawan" class="w-full border p-2"
                        placeholder="Masukkan kode karyawan" required>
                </div>
                <div>
                    <label>Nama Karyawan</label>
                    <input type="text" name="nama_karyawan" class="w-full border p-2"
                        placeholder="Masukkan nama karyawan" required>
                </div>
                <div>
                    <label>User Login</label>
                    <select name="user_id" id="user_id" class="w-full border p-2">
                        <option value="">--Pilih--</option>
                        @foreach ($user as $us)
                            <option value="{{ $us->id }}">{{ $us->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>NIK</label>
                    <input type="text" name="nik" class="w-full border p-2" placeholder="Masukkan Nik" required>
                </div>
                <div>
                    <label>Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" placeholder="Masukkan tempat lahir" class="w-full border p-2">
                </div>
                <div>
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="w-full border p-2">
                </div>
                <div>
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="w-full border p-2">
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label>Golongan Darah</label>
                    <select name="golongan_darah" id="golongan_darah" class="w-full border p-2">
                        <option value="A">A</option>
                        <option value="AB">AB</option>
                        <option value="O">O</option>
                        <option value="B">B</option>
                    </select>
                </div>
                <div>
                    <label>Tinggi Badan</label>
                    <input type="text" name="tinggi_badan" placeholder="Masukkan tinggi badan" class="w-full border p-2">
                </div>

                <div>
                    <label>Agama</label>
                    <select name="agama" id="agama" class="w-full border p-2">
                        <option value="Islam">Islam</option>
                        <option value="Kristen">Kristen</option>
                        <option value="Kristen Protestan">Kristen Protestan</option>
                        <option value="Konghucu">Konghucu</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Buddha">Buddha</option>
                    </select>
                </div>
                <div>
                    <label>Kewarganegaraan</label>
                    <select name="kewarganegaraan" id="kewarganegaraan" class="w-full border p-2">
                        <option value="wni">WNI</option>
                        <option value="wna">WNA</option>
                    </select>
                </div>
            </div>

            <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                Informasi Kontak
            </h4>
            {{-- Kontak --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label>No.Telepon</label>
                    <input type="text" name="telepon" placeholder="Masukkan No.Telepon" class="w-full border p-2">
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Masukkan email" class="w-full border p-2">
                </div>
                <div class="mb-4">
                    <label>Alamat</label>
                    <textarea name="alamat" placeholder="Masukkan alamat" class="w-full border p-2"></textarea>
                </div>
            </div>

            {{-- Lain-lain --}}
            <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                Status & Kondisi Sosial
            </h4>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label>Status Pernikahan</label>
                    <select name="status_pernikahan" id="status_pernikahan" class="w-full border p-2">
                        <option value="">--Pilih--</option>
                        <option value="Belum Kawin">Belum Kawin</option>
                        <option value="Kawin">Kawin</option>
                        <option value="Cerai Hidup">Cerai Hidup</option>
                        <option value="Cerai Mati">Cerai Mati</option>
                    </select>
                </div>

                <div>
                    <label>PTKP</label>
                    <select name="ptkp_id" class="w-full border p-2">
                        @foreach ($ptkps as $ptkp)
                            <option value="{{ $ptkp->id }}">{{ $ptkp->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                Informasi Jabatan & Kepegawaian
            </h4>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label>Jabatan</label>
                    <select name="jabatan_id" id="jabatan_id" class="w-full border p-2">
                        @foreach ($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Level Kepegawaian</label>
                    <select name="level_kepegawaian_id" class="w-full border p-2">
                        @foreach ($levels as $level)
                            <option value="{{ $level->id }}">{{ $level->nama_level }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Unit Kerja</label>
                    <select name="unit_kerja_id" class="w-full border p-2">
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Status Pegawai</label>
                    <select name="status_pegawai" id="status_pegawai" class="w-full border p-2">
                        <option value="">--Pilih--</option>
                        <option value="Pegawai Tetap">Pegawai Tetap</option>
                        <option value="Pegawai Kontrak">Pegawai Kontrak</option>
                        <option value="Freelance">Freelance</option>
                        <option value="Magang">Magang</option>
                    </select>
                </div>
                <div>
                    <label>Atasan</label>
                    <select name="supervisor_id" id="employee_id" class="w-full border p-2">
                        @foreach ($employee as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->nama_karyawan }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                Riwayat Kepegawaian
            </h4>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label>Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" class="w-full border p-2">
                </div>
                <div>
                    <label>Tanggal Keluar</label>
                    <input type="date" name="tanggal_keluar" class="w-full border p-2">
                </div>
            </div>

            <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                Dokumen Dan Berkas Pendukung
            </h4>
            <div class="grid grid-cols-3 gap-4 mb-4">

                <div>
                    <label>Sertifikat</label>
                    <input type="text" name="sertifikat" class="w-full border p-2">
                </div>
                <div>
                    <label>Foto</label>
                    <input type="file" name="photo" class="w-full border p-2">
                    @error('photo')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror

                </div>
                <div>
                    <label>Foto KTP</label>
                    <input type="file" name="foto_ktp" class="w-full border p-2">
                    @error('foto_ktp')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror

                </div>
            </div>
            <div>
                <label for="">Tempelkan Kartu RFID</label>
                <input type="text" name="rfid_code" placeholder="Masukkan nomor rfid (opsional)"
                    class="w-1/2 border p-2">
                @error('rfid_code')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="text-right">
                <a href="{{ route('sales_person.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                <button type="submit" class="btn btn-success">Process</button>
            </div>
    </div>
    </form>
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
                '#jabatan_id',
                '{{ route('jabatan.search') }}',
                function(jabatan) {
                    return {
                        id: jabatan.id,
                        text: jabatan.nama_jabatan
                    };
                },
                "-- Jabatan --"
            );

            // ✅ Employees
            initSelect2(
                '#employee_id',
                '{{ route('employee.search') }}',
                function(employee) {
                    return {
                        id: employee.id,
                        text: employee.nama_karyawan
                    };
                },
                "-- Employees --"
            );
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const rfidInput = document.getElementById("rfid");
            let buffer = "";

            // Tangkap inputan keyboard
            document.addEventListener("keypress", function(e) {
                const char = String.fromCharCode(e.which || e.keyCode);

                if (e.key === "Enter") {
                    // Ketika RFID selesai dikirim (biasanya diakhiri ENTER)
                    if (buffer.length >= 8) {
                        rfidInput.value = buffer.trim();
                        buffer = "";
                    }
                    e.preventDefault(); // mencegah form terkirim otomatis
                } else {
                    buffer += char;
                }
            });
        });
    </script>

@endsection

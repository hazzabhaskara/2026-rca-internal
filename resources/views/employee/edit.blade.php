@extends('layouts.app')

@section('content')
    <div class="py-10 max-w-full mx-auto px-6">
        @php
            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
        @endphp
        <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
            <form method="POST"
                action="{{ isset($employee) ? route('employee.update', $employee->id) : route('employee.store') }}">
                @csrf
                @if (isset($employee))
                    @method('PUT')
                @endif
                @if ($errors->any())
                    <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <h2 class="font-bold text-lg">Edit Employee</h2>

                <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                    Informasi Pribadi
                </h4>

                {{-- Data Pribadi --}}
                <div class="grid grid-cols-4 gap-4 mb-4">
                    <div>
                        <label>Kode Karyawan</label>
                        <input type="text" name="kode_karyawan" class="w-full border p-2"
                            value="{{ $employee->kode_karyawan }}" required>
                    </div>
                    <div>
                        <label>Nama Karyawan Sesuai KTP</label>
                        <input type="text" name="nama_karyawan" class="w-full border p-2"
                            value="{{ $employee->nama_karyawan }}" required>
                    </div>
                    <div>
                        <label>Nama Panggilan</label>
                        <input type="text" name="nama_panggilan" class="w-full border p-2"
                            value="{{ $employee->nama_panggilan }}" required>
                    </div>
                    <div>
                        <label>User Login</label>
                        <select name="user_id" id="user_id" class="w-full border p-2">
                            <option value="">--Pilih--</option>
                            @foreach ($user as $us)
                                <option value="{{ $us->id }}"
                                    {{ isset($employee) && $employee->user_id == $us->id ? 'selected' : '' }}>
                                    {{ $us->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>NIK</label>
                        <input type="text" name="nik" class="w-full border p-2" value="{{ $employee->nik }}"
                            required>
                    </div>
                    <div>
                        <label>Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="w-full border p-2"
                            value="{{ $employee->tempat_lahir }}">
                    </div>
                    <div>
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="w-full border p-2"
                            value="{{ $employee->tanggal_lahir }}">
                    </div>
                    <div>
                        <label>Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="w-full border p-2">
                            <option value="Laki Laki"
                                {{ old('jenis_kelamin', $employee->jenis_kelamin) == 'Laki Laki' ? 'selected' : '' }}>
                                Laki Laki</option>
                            <option value="Perempuan"
                                {{ old('jenis_kelamin', $employee->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>
                                Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label>Golongan Darah</label>
                        <select name="golongan_darah" class="w-full border p-2">
                            <option value="A"
                                {{ old('golongan_darah', $employee->golongan_darah) == 'A' ? 'selected' : '' }}>
                                A</option>
                            <option value="B"
                                {{ old('golongan_darah', $employee->jenis_kelamin) == 'B' ? 'selected' : '' }}>
                                B</option>
                            <option value="O"
                                {{ old('golongan_darah', $employee->jenis_kelamin) == 'O' ? 'selected' : '' }}>
                                O</option>
                            <option value="AB"
                                {{ old('golongan_darah', $employee->jenis_kelamin) == 'AB' ? 'selected' : '' }}>
                                AB</option>
                        </select>
                    </div>
                    <div>
                        <label>Tinggi Badan</label>
                        <input type="text" name="tinggi_badan" class="w-full border p-2"
                            value="{{ $employee->tinggi_badan }}">
                    </div>
                    <div>
                        <label>Agama</label>
                        <select name="agama" id="agama" class="w-full border p-2">
                            <option value="Islam" {{ old('agama', $employee->agama) == 'Islam' ? 'selected' : '' }}>
                                Islam</option>
                            <option value="Kristen Protestan"
                                {{ old('agama', $employee->agama) == 'Kristen Protestan' ? 'selected' : '' }}>
                                Kristen Protestan</option>
                            <option value="Katolik" {{ old('agama', $employee->agama) == 'Katolik' ? 'selected' : '' }}>
                                Katolik</option>
                            <option value="Buddha" {{ old('agama', $employee->agama) == 'Buddha' ? 'selected' : '' }}>
                                Buddha</option>
                            <option value="Hindu" {{ old('agama', $employee->agama) == 'Hindu' ? 'selected' : '' }}>
                                Hindu</option>
                            <option value="Konghucu" {{ old('agama', $employee->agama) == 'Konghucu' ? 'selected' : '' }}>
                                Konghucu</option>
                        </select>
                    </div>
                    <div>
                        <label>Kewarganegaraan</label>
                        <select name="kewarganegaraan" id="kewarganegaraan" class="w-full border p-2">
                            <option value="Wni"
                                {{ old('kewarganegaraan', $employee->kewarganegaraan) == 'Wni' ? 'selected' : '' }}>
                                WNI</option>
                            <option value="Wna"
                                {{ old('kewarganegaraan', $employee->kewarganegaraan) == 'Wna' ? 'selected' : '' }}>
                                WNA</option>
                        </select>
                    </div>
                </div>

                {{-- Kontak --}}

                <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                    Informasi Kontak
                </h4>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label>Telepon</label>
                        <input type="text" name="telepon" class="w-full border p-2" value="{{ $employee->telepon }}">
                    </div>
                    <div>
                        <label>Email</label>
                        <input type="email" name="email" class="w-full border p-2" value="{{ $employee->email }}">
                    </div>
                    <div class="mb-4">
                        <label>Alamat</label>
                        <textarea name="alamat" class="w-full border p-2" value="{{ $employee }}"></textarea>
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
                            <option value="Belum Kawin"
                                {{ old('status_pernikahan', $employee->status_pernikahan) == 'Belum Kawin' ? 'selected' : '' }}>
                                Belum Kawin</option>
                            <option value="Kawin"
                                {{ old('status_pernikahan', $employee->status_pernikahan) == 'Kawin' ? 'selected' : '' }}>
                                Kawin</option>
                            <option value="Cerai Hidup"
                                {{ old('status_pernikahan', $employee->status_pernikahan) == 'Cerai Hidup' ? 'selected' : '' }}>
                                Cerai Hidup</option>
                            <option value="Cerai Mati"
                                {{ old('status_pernikahan', $employee->status_pernikahan) == 'Cerai Mati' ? 'selected' : '' }}>
                                Cerai Mati</option>
                        </select>
                    </div>
                    <div>
                        <label>PTKP</label>
                        <select name="ptkp_id" class="w-full border p-2">
                            @foreach ($ptkps as $ptkp)
                                <option value="{{ $ptkp->id }}"
                                    {{ isset($employee) && $employee->ptkp_id == $ptkp->id ? 'selected' : '' }}>
                                    {{ $ptkp->nama }}
                                </option>
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
                                <option value="{{ $jabatan->id }}"
                                    {{ isset($employee) && $employee->jabatan_id == $jabatan->id ? 'selected' : '' }}>
                                    {{ $jabatan->nama_jabatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Level Kepegawaian</label>
                        <select name="level_kepegawaian_id" class="w-full border p-2">
                            @foreach ($levels as $level)
                                <option value="{{ $level->id }}"
                                    {{ isset($employee) && $employee->level_kepegawaian_id == $level->id ? 'selected' : '' }}>
                                    {{ $level->nama_level }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Unit Kerja</label>
                        <select name="unit_kerja_id" class="w-full border p-2">
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}"
                                    {{ isset($employee) && $employee->unit_kerja_id == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Status Pegawai</label>
                        <select name="status_pegawai" id="status_pegawai" class="w-full border p-2">
                            <option value="Pegawai Tetap"
                                {{ old('status_pegawai', $employee->status_pegawai) == 'Pegawai Tetap' ? 'selected' : '' }}>
                                Pegawai Tetap</option>
                            <option value="Pegawai Kontrak"
                                {{ old('status_pegawai', $employee->status_pegawai) == 'Pegawai Kontrak' ? 'selected' : '' }}>
                                Pegawai Kontrak</option>
                            <option value="Freelance"
                                {{ old('status_pegawai', $employee->status_pegawai) == 'Freelance' ? 'selected' : '' }}>
                                Freelance</option>
                            <option value="Magang"
                                {{ old('status_pegawai', $employee->status_pegawai) == 'Magang' ? 'selected' : '' }}>
                                Magang</option>
                        </select>
                    </div>
                    <div>
                        <label>Atasan</label>
                        <select name="supervisor_id" id="employee_id" class="w-full border p-2">
                            @foreach ($atasan as $emp)
                                <option value="{{ $emp->id }}"
                                    {{ isset($employee) && $employee->supervisor_id == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->nama_karyawan }}
                                </option>
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
                        <input type="date" name="tanggal_masuk" class="w-full border p-2"
                            value="{{ $employee->tanggal_masuk }}">
                    </div>
                    <div>
                        <label>Tanggal Keluar</label>
                        <input type="date" name="tanggal_keluar" class="w-full border p-2"
                            value="{{ $employee->tanggal_keluar }}">
                    </div>
                </div>
                <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                    Dokumen Dan Berkas Pendukung
                </h4>

                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label>Sertifikat</label>
                        <input type="text" name="sertifikat" class="w-full border p-2"
                            value="{{ $employee->sertifikat }}">
                    </div>
                    <div>
                        <label>Foto</label>
                        <input type="file" name="photo" class="w-full border p-2" value="{{ $employee->photo }}">
                        @error('photo')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror

                    </div>
                    <div>
                        <label>Foto KTP</label>
                        <input type="file" name="foto_ktp" class="w-full border p-2"
                            value="{{ $employee->foto_ktp }}">
                        @error('foto_ktp')
                            <p class="text-red-500 text-sm">{{ $message }}</p>
                        @enderror

                    </div>

                    <div>
                        <label>RFID Code</label>
                        <input type="text" name="rfid_code" id="rfid" class="w-full border p-2"
                            value="{{ $employee->rfid_code }}">
                    </div>
                </div>

                <div class="text-right">
                    <a href="{{ route('employee.index') }}" class="btn btn-secondary mr-2">Kembali</a>
                    <button type="submit" class="btn btn-success">Simpan</button>
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

@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <!-- Informasi Klasifikasi Akun -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Informasi Employee</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-700">

                        <div>
                            <span class="font-medium">Kode Karyawan:</span>
                            <span class="ml-2">{{ $employee->kode_karyawan }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Nama Karyawan:</span>
                            <span class="ml-2">{{ $employee->nama_karyawan }}</span>
                        </div>
                        <div>
                            <span class="font-medium">NIK:</span>
                            <span class="ml-2">{{ $employee->nik }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Tempat Lahir:</span>
                            <span class="ml-2">{{ $employee->tempat_lahir }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Tanggal Lahir:</span>
                            <span class="ml-2">{{ $employee->tanggal_lahir }}</span>
                        </div>

                        <div class="md:col-span-2">
                            <span class="font-medium">Jenis Kelamin:</span>
                            <span class="ml-2">{{ $employee->jenis_kelamin }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="font-medium">Golongan Darah:</span>
                            <span class="ml-2">{{ $employee->golongan_darah }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Tinggi Badan:</span>
                            <span class="ml-2">{{ $employee->tinggi_badan }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Alamat:</span>
                            <span class="ml-2">{{ $employee->alamat }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Telepon:</span>
                            <span class="ml-2">{{ $employee->telepon }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Email:</span>
                            <span class="ml-2">{{ $employee->email }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Agama:</span>
                            <span class="ml-2">{{ $employee->agama }}</span>
                        </div>

                        <div class="md:col-span-2">
                            <span class="font-medium">Kewarganegaraan:</span>
                            <span class="ml-2">{{ $employee->kewarganegaraan }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="font-medium"> Status Pernikahan:</span>
                            <span class="ml-2">{{ $employee->status_pernikahan }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="font-medium">PTKP:</span>
                            <span class="ml-2">{{ $employee->ptkp->nama ?? '-' }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="font-medium">Jabatan:</span>
                            <span class="ml-2">{{ $employee->jabatan->nama_jabatan }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Tanggal Masuk:</span>
                            <span class="ml-2">{{ $employee->tanggal_masuk }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Tanggal Keluar:</span>
                            <span class="ml-2">{{ $employee->tanggal_keluar }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Status Pegawai:</span>
                            <span class="ml-2">{{ $employee->status_pegawai }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Level Kepegawaian:</span>
                            <span class="ml-2">{{ $employee->levelKaryawan->nama_level }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Sertifikat:</span>
                            <span class="ml-2">{{ $employee->sertifikat }}</span>
                        </div>

                        <div class="md:col-span-2">
                            <span class="font-medium"> Foto Profil:</span>
                            @if ($employee->photo)
                                <img src="{{ asset('storage/' . $employee->photo) }}" alt="Foto KTP"
                                    class="w-16 h-16 object-cover rounded">
                            @else
                                <span class="ml-2">Tidak ada foto KTP</span>
                            @endif
                        </div>
                        <div class="md:col-span-2">
                            <span class="font-medium"> Foto KTP:</span>
                            @if ($employee->foto_ktp)
                                <img src="{{ asset('storage/' . $employee->foto_ktp) }}" alt="Foto KTP"
                                    class="w-16 h-16 object-cover rounded">
                            @else
                                <span class="ml-2">Tidak ada foto KTP</span>
                            @endif
                        </div>
                        <div>
                            <span class="font-medium">RFID Code:</span>
                            <span class="ml-2">{{ $employee->rfid_code }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Atasan:</span>
                            <span class="ml-2">{{ $employee->supervisor->nama_karyawan }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('employee.edit', $employee->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('employee.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

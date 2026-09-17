@extends('layouts.app')


@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @php
            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
        @endphp
        <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
            <div class="px-6 py-4 border-b">
                <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                    Scheduling Personnel Detail
                </h4>
            </div>
            <div class="px-6 py-4">
                <table class="w-full text-sm text-left text-gray-700">
                    <tbody>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Nama Karyawan</th>
                            <td class="py-2">{{ $data->karyawan->nama_karyawan }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Unit Kerja</th>
                            <td class="py-2">{{ $data->unitKerja->nama_unit }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Wahana</th>
                            <td class="py-2">{{ $data->wahana->nama_wahana }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Tanggal</th>
                            <td class="py-2">{{ $data->tanggal }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Jenis Hari </th>
                            <td class="py-2">{{ $data->jenisHari->nama }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Jam Mulai</th>
                            <td class="py-2">{{ $data->jam_mulai }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Jam Selesai</th>
                            <td class="py-2">{{ $data->jam_selesai }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Lama Jam</th>
                            <td class="py-2">{{ $data->lama_jam }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Persentase Jam</th>
                            <td class="py-2">{{ $data->persentase_jam ?? 'Tidak Ada' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('shift_karyawan.edit', $data->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('shift_karyawan.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

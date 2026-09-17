@extends('layouts.app')


@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @php
            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
        @endphp
        <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
            <div class="px-6 py-4 border-b">
                <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                    Target Wahana Detail
                </h4>
            </div>
            <div class="px-6 py-4">
                <table class="w-full text-sm text-left text-gray-700">
                    <tbody>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Wahana</th>
                            <td class="py-2">{{ $target_wahana->wahana->nama_wahana }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Unit Kerja</th>
                            <td class="py-2">{{ $target_wahana->unit->nama_unit }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Jenis Hari</th>
                            <td class="py-2">{{ $target_wahana->jenis_hari->nama }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Bulan</th>
                            <td class="py-2">{{ $target_wahana->bulan }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Tahun</th>
                            <td class="py-2">{{ $target_wahana->tahun }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Target Harian </th>
                            <td class="py-2">{{ $target_wahana->target_harian }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Keterangan </th>
                            <td class="py-2">{{ $target_wahana->keterangan ?? 'Tidak Ada' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('target_wahana.edit', $target_wahana->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('target_wahana.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

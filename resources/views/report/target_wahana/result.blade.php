@extends('layouts.app')

@section('content')
    <div class="p-6 bg-white rounded shadow">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold">📊 Hasil Report Target Wahana</h2>

            <div class="flex gap-2">
                <a href="{{ route('report.target_wahana.pdf', request()->all()) }}"
                    class="inline-flex items-center bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm font-medium shadow">
                    <i class="fa-solid fa-file-pdf mr-2"></i> Download PDF
                </a>

                <a href="{{ route('report.target_wahana.excel', request()->all()) }}"
                    class="inline-flex items-center bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded text-sm font-medium shadow">
                    <i class="fa-solid fa-file-excel mr-2"></i> Download Excel
                </a>
            </div>
        </div>

        @if ($results->isEmpty())
            <p class="text-gray-600 italic">Tidak ada data ditemukan.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full border border-gray-200 text-sm rounded shadow-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="border px-3 py-2 text-left">Unit</th>
                            <th class="border px-3 py-2 text-left">Wahana</th>
                            <th class="border px-3 py-2 text-left">Jenis Hari</th>
                            <th class="border px-3 py-2 text-right">Target Harian</th>
                            <th class="border px-3 py-2 text-center">Bulan</th>
                            <th class="border px-3 py-2 text-center">Tahun</th>
                            <th class="border px-3 py-2 text-left">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="border px-3 py-2">{{ $row->wahana->unitKerja->nama_unit }}</td>
                                <td class="border px-3 py-2">{{ $row->wahana->nama_wahana }}</td>
                                <td class="border px-3 py-2">{{ $row->jenis_hari->nama }}</td>
                                <td class="border px-3 py-2 text-right">{{ number_format($row->target_harian) }}</td>
                                <td class="border px-3 py-2 text-center">{{ $row->bulan }}</td>
                                <td class="border px-3 py-2 text-center">{{ $row->tahun }}</td>
                                <td class="border px-3 py-2">{{ $row->keterangan }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-6">
            <a href="{{ route('report.target_wahana.filter') }}"
                class="inline-flex items-center text-blue-600 hover:underline text-sm">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Filter
            </a>
        </div>
    </div>
@endsection

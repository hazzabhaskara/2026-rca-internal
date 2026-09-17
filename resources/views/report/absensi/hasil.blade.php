@extends('layouts.app')

@section('content')
    <div class="p-6 bg-white rounded-lg shadow mt-2">
        <h2 class="text-xl font-bold mb-4">Hasil Rekap Absensi</h2>

        <p class="mb-4 text-sm text-gray-600">
            Periode: {{ $startDate }} s/d {{ $endDate }} ({{ ucfirst($filterType) }}) <br>
            Unit: <strong>{{ $unitName }}</strong>
        </p>

        <div class="flex justify-between items-center mb-4">
            <a href="{{ route('report.absensi.filter') }}" class="text-blue-600">
                ← Kembali ke Filter
            </a>
            <div class="space-x-2">
                <a href="{{ route('report.absensi.pdf', request()->all()) }}"
                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                    Download PDF
                </a>
                <a href="{{ route('report.absensi.excel', request()->all()) }}"
                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                    Download Excel
                </a>
            </div>
        </div>


        <table class="w-full border-collapse border text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1">Nama Karyawan</th>
                    <th class="border px-2 py-1">Level</th>
                    <th class="border px-2 py-1">Unit</th>
                    <th class="border px-2 py-1">Total Hari Kerja</th>
                    <th class="border px-2 py-1">Total Jam Lembur</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekap as $r)
                    <tr>
                        <td class="border px-2 py-1">{{ $r['pegawai'] }}</td>
                        <td class="border px-2 py-1">{{ $r['level'] }}</td>
                        <td class="border px-2 py-1">{{ $r['unit'] }}</td>
                        <td class="border px-2 py-1">{{ $r['total_hari'] }}</td>
                        <td class="border px-2 py-1">{{ $r['total_lembur'] }} Jam</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center p-4">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <!-- Sticky Card Header -->
                @php
                    $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                @endphp

                <div class="sticky top-0 z-20 px-6 py-5 border-b border-gray-100 flex justify-between items-center"
                    style="background: {{ $themeColor }};">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-list mr-3 text-white text-xl"></i>
                        Fiscal Report
                    </h3>
                </div>

                <div class="relative overflow-x-auto" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Kode Akun</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Nama Akun</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Nilai
                                    (Komersial)</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">TTOP(Tidak
                                    Termasuk Objek Pajak)</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">PPH Bersifat
                                    Final</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Objek Pajak
                                    Tidak Final</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Penyesuaian
                                    Fiscal (+)</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Penyesuaian
                                    Fiscal (-)</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Kode
                                    Penyesuaian</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Nilai Fiscal
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report as $i => $row)
                                <tr>
                                    <td class="px-4 py-2 text-center">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2 text-center">{{ $row['kode_akun'] }}</td>
                                    <td class="px-4 py-2">{{ $row['nama_akun'] }}</td>
                                    <td class="px-4 py-2 text-right">
                                        {{ number_format($row['nilai_komersial'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($row['non_tax'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($row['pph_final'], 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        {{ number_format($row['objek_tidak_final'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($row['koreksi_plus'], 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-right">{{ number_format($row['koreksi_minus'], 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-center">{{ $row['kode_fiscal'] ?? '-' }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($row['nilai_fiscal'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach

                            <!-- TOTAL -->
                            <tr class="bg-gray-100 font-bold">
                                <td colspan="3" class="px-4 py-2 text-center">TOTAL</td>
                                <td class="px-4 py-2 text-right">
                                </td>
                                <td class="px-4 py-2 text-right">{{ number_format($total['non_tax'], 2, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($total['pph_final'], 2, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right">
                                    {{ number_format($total['objek_tidak_final'], 2, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($total['koreksi_plus'], 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-right">{{ number_format($total['koreksi_minus'], 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-center">-</td>
                                <td class="px-4 py-2 text-right">{{ number_format($total['nilai_fiscal'], 2, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

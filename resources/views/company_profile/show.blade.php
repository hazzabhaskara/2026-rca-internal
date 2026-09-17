@extends('layouts.app')



@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @php
            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
        @endphp
        <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
            <div class="px-6 py-4 border-b">
                <h2 class="text-2xl font-semibold text-gray-800">Informasi Perusahaan</h2>
            </div>
            <div class="px-6 py-4">
                <table class="w-full text-sm text-left text-gray-700">
                    <tbody>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Nama Perusahaan</th>
                            <td class="py-2">{{ $informasiPerusahaans->nama_perusahaan }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">No. Telepon</th>
                            <td class="py-2">{{ $informasiPerusahaans->phone_number }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Email</th>
                            <td class="py-2">{{ $informasiPerusahaans->email }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Provinsi</th>
                            <td class="py-2">{{ $informasiPerusahaans->provinsi->name ?? '' }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Kota</th>
                            <td class="py-2">{{ $informasiPerusahaans->kota->name ?? '' }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Kecamatan</th>
                            <td class="py-2">{{ $informasiPerusahaans->kecamatan->name ?? '' }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Kelurahan</th>
                            <td class="py-2">{{ $informasiPerusahaans->kelurahan->name ?? '' }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Kode Pos</th>
                            <td class="py-2">{{ $informasiPerusahaans->kode_pos }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Jalan</th>
                            <td class="py-2">{{ $informasiPerusahaans->jalan }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Logo</th>
                            <td class="py-2">
                                @if ($informasiPerusahaans->logo)
                                    <img src="{{ asset('storage/informasi_perusahaan/' . $informasiPerusahaans->logo) }}"
                                        class="h-24 rounded border">
                                @else
                                    <span class="text-gray-500">Belum ada logo</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{-- Dokumen Legal --}}
                <h3 class="text-lg font-semibold mt-8 mb-2">Dokumen Legal</h3>

                @if ($informasiPerusahaans->legalDocuments->isEmpty())
                    <p class="text-gray-500">Belum ada dokumen legal diunggah.</p>
                @else
                    <table class="table-auto w-full text-sm text-left text-gray-700 border mt-2">
                        <thead class="bg-gray-100 text-gray-800">
                            <tr>
                                <th class="px-4 py-2">Jenis Dokumen</th>
                                <th class="px-4 py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($informasiPerusahaans->legalDocuments as $doc)
                                <tr class="border-t">
                                    <td class="px-4 py-2">{{ $doc->jenis_dokumen }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                            class="text-blue-600 hover:underline mr-4">
                                            📄 Lihat
                                        </a>
                                        <a href="{{ asset('storage/' . $doc->file_path) }}" download
                                            class="text-green-600 hover:underline">
                                            ⬇️ Download
                                        </a>
                                        <a href="" target="_blank" class="text-red-600 hover:underline">
                                            🖨️ Cetak
                                        </a>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('company_profile.edit', $informasiPerusahaans->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    Edit
                </a>
            </div>
        </div>
    </div>
@endsection

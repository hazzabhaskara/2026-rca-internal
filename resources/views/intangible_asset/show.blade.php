@extends('layouts.app')
@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-2xl font-semibold text-gray-800">Informasi Intangible Asset</h2>
            </div>
            <div class="px-6 py-4">
                <table class="w-full text-sm text-left text-gray-700">
                    <tbody>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Kode Asset</th>
                            <td class="py-2">{{ $data->kode_asset }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Nama Asset</th>
                            <td class="py-2">{{ $data->nama_asset }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Kategori </th>
                            <td class="py-2">{{ $data->kategori->nama_kategori }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Deskripsi</th>
                            <td class="py-2">{{ $data->deskripsi }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Brand</th>
                            <td class="py-2">{{ $data->brand }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Location </th>
                            <td class="py-2">{{ $data->lokasi->nama_lokasi }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Golongan</th>
                            <td class="py-2">{{ $data->golongan->nama_golongan }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Masa Manfaat(dalam tahun)</th>
                            <td class="py-2">{{ $data->dalam_tahun }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Metode Penyusutan </th>
                            <td class="py-2">{{ $data->metode_penyusutan->nama_metode }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Tarif Penyusutan</th>
                            <td class="py-2">{{ $data->tarif_amortisasi }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Asset Full Name</th>
                            <td class="py-2">{{ $data->asset_full_name }}</td>
                        </tr>

                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('intangible_asset.edit', $data->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('intangible_asset.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

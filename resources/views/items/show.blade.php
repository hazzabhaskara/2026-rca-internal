@extends('layouts.app')
@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-2xl font-semibold text-gray-800">Informasi Item </h2>
            </div>
            <div class="px-6 py-4">
                <table class="w-full text-sm text-left text-gray-700">
                    <tbody>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Code Item </th>
                            <td class="py-2">{{ $data->item_number }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Nama Item </th>
                            <td class="py-2">{{ $data->item_name }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Unit </th>
                            <td class="py-2">{{ $data->unit }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Base Price </th>
                            <td class="py-2">{{ number_format($data->base_price) }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Tax Rate </th>
                            <td class="py-2">{{ $data->tax_rate }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Account </th>
                            <td class="py-2">{{ $data->account->nama_akun }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Category </th>
                            <td class="py-2">{{ $data->category->nama_kategori }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Brand </th>
                            <td class="py-2">{{ $data->brand }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Stok Qty </th>
                            <td class="py-2">{{ $data->stock_quantity }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Barcode </th>
                            <td class="py-2">
                                {!! DNS1D::getBarcodeHTML($data->item_number, 'C128') !!}
                            </td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Logo </th>
                            <td class="py-2"><img src="{{ asset('storage/items/' . $data->image) }}" alt="Image"
                                    class="w-20 h-20 object-cover rounded"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('items.edit', $data->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('items.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="font-bold text-lg mb-4">Detail Item</h2>

                {{-- Info Utama --}}
                <table class="min-w-full text-sm">
                    <tr>
                        <td class="font-semibold pr-4">Kode Item</td>
                        <td>{{ $item->item_number }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold pr-4">Nama Item</td>
                        <td>{{ $item->item_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold pr-4">Deskripsi</td>
                        <td>{{ $item->item_description ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold pr-4">Tipe</td>
                        <td>{{ ucfirst($item->type) }}</td>
                    </tr>
                </table>

                {{-- Gambar --}}
                <div class="mt-4 flex space-x-6">
                    @if ($item->picture_path)
                        <div>
                            <p class="font-semibold">Picture</p>
                            <img src="{{ asset('storage/' . $item->picture_path) }}" alt="Picture"
                                class="w-40 h-40 object-cover border rounded">
                        </div>
                    @endif
                    @if ($item->thubmnail_path)
                        <div>
                            <p class="font-semibold">Thumbnail</p>
                            <img src="{{ asset('storage/' . $item->thubmnail_path) }}" alt="Thumbnail"
                                class="w-20 h-20 object-cover border rounded">
                        </div>
                    @endif
                </div>

                {{-- Quantities --}}
                <h3 class="font-semibold mt-6 mb-2">Quantities</h3>
                @if ($quantity)
                    <table class="min-w-full text-sm border">
                        <tr>
                            <td class="p-2 border">Lokasi</td>
                            <td class="p-2 border">{{ $quantity->location_id }}</td>
                        </tr>
                        <tr>
                            <td class="p-2 border">On Hand Qty</td>
                            <td class="p-2 border">{{ $quantity->on_hand_qty }}</td>
                        </tr>
                        <tr>
                            <td class="p-2 border">On Hand Value</td>
                            <td class="p-2 border">{{ number_format($quantity->on_hand_value, 2) }}</td>
                        </tr>
                    </table>
                @else
                    <p class="text-gray-500">Tidak ada data quantity.</p>
                @endif

                {{-- Prices --}}
                <h3 class="font-semibold mt-6 mb-2">Harga</h3>
                @if ($item->prices->count())
                    <table class="min-w-full text-sm border">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-2 border text-left">Price List</th>
                                <th class="p-2 border text-right">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($item->prices as $price)
                                <tr>
                                    <td class="p-2 border">{{ $price->price_list_name }}</td>
                                    <td class="p-2 border text-right">{{ number_format($price->price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500">Tidak ada data harga.</p>
                @endif

                {{-- Vendor --}}


                {{-- Accounts --}}
                <h3 class="font-semibold mt-6 mb-2">Akun</h3>
                @if ($item->accounts)
                    <ul class="list-disc ml-6 text-sm">
                        <li>Asset Account: {{ optional($item->accounts->assetAccount)->kode_akun ?? '-' }}</li>
                        <li>Revenue Account: {{ optional($item->accounts->revenueAccount)->kode_akun ?? '-' }}</li>
                        <li>COGS Account: {{ optional($item->accounts->cogsAccount)->kode_akun ?? '-' }}</li>
                    </ul>
                @else
                    <p class="text-gray-500">Tidak ada akun terkait.</p>
                @endif

                {{-- Taxes --}}
                <h3 class="font-semibold mt-6 mb-2">Pajak</h3>
                @if ($item->taxes->count())
                    <ul class="list-disc ml-6 text-sm">
                        @foreach ($item->taxes as $tax)
                            <li>{{ $tax->tax_name }}
                                @if ($tax->is_exempt)
                                    <span class="text-red-500">(Exempt)</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500">Tidak ada data pajak.</p>
                @endif

                {{-- Tombol Kembali --}}
                <div class="mt-6">
                    <a href="{{ route('inventory.index') }}"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">Kembali</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <!-- Informasi Klasifikasi Akun -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Closing Harian View</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">

                        <div>
                            <span class="text-1xl font-bold">Tanggal:</span>
                            <span class="text-1xl font-bold ml-2">{{ $data->tanggal }}</span>
                        </div>

                        <div class="md:col-span-2">
                            <span class="text-1xl font-bold">Unit Kerja:</span>
                            <span class="text-1xl font-bold ml-2">{{ $data->unitKerja->nama_unit ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    @php
                        $items = $data->details->pluck('wahanaItem.nama_item')->unique();
                    @endphp
                    <div style="overflow-x: auto; width: 100%;">
                        <table class="table-fixed border-collapse  w-full text-center text-sm min-w-[3000px]">
                            <thead class="bg-blue-300 text-black">
                                <tr>
                                    <th rowspan="2" class="px-4 py-2 bg-green-200">Wahana</th>
                                    @foreach ($items as $item)
                                        <th colspan="3" class="px-4 py-2 bg-blue-100">{{ $item }}</th>
                                    @endforeach

                                    <th rowspan="2" class="px-4 py-2 bg-green-200">TOTAL OMSET</th>
                                    <th colspan="2" class="px-4 py-2 bg-yellow-200">PAYMENT TYPE <br>DARI
                                        PENGUNJUNG</th>
                                    <th colspan="2" class="px-4 py-2 bg-gray-200">PEMBAGIAN <br>SHARING OMSET</th>
                                    @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                                        <th rowpsan="2" class="px-4 py-2 bg-gray-200">TITIPAN <br>OMSET</th>
                                    @endif
                                    <th rowspan="2" class="px-4 py-2 bg-pink-200">LEBIH (KURANG) <br>DANA CASH
                                        <br>SETOR
                                        TUNAI
                                        KE<br>MERCHANDISE
                                    </th>
                                </tr>
                                <tr>
                                    @foreach ($items as $item)
                                        <th class="px-4 py-2 w-[100px]">QTY</th>
                                        <th class="px-4 py-2 w-[120px]">HARGA</th>
                                        <th class="px-4 py-2 w-[150px]">JUMLAH</th>
                                    @endforeach
                                    <th class="px-4 py-2 w-[150px]">QRIS</th>
                                    <th class="px-4 py-2 w-[150px]">CASH</th>
                                    <th class="px-4 py-2 w-[150px]">MERCHANDISE</th>
                                    <th class="px-4 py-2 w-[150px]">RCA</th>
                                    @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                                        <th class="px-4 py-2 w-[150px]">{{ $data->unitKerja->nama_unit }}<br>(SETOR TUNAI)
                                    @endif
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data->details->groupBy('wahanaItem.wahana_id') as $wahanaId => $group)
                                    <tr>
                                        <td class="text-center font-semibold px-2 py-1">
                                            {{ $group->first()->wahanaItem->wahana->nama_wahana ?? '-' }}
                                        </td>

                                        @foreach ($items as $itemName)
                                            @php
                                                $detail = $group->firstWhere('wahanaItem.nama_item', $itemName);
                                            @endphp

                                            <td class="text-center px-2 py-1">
                                                {{ number_format($detail->qty ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td class="text-right px-2 py-1">
                                                {{ number_format($detail->harga ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td class="text-right px-2 py-1">
                                                {{ number_format($detail->jumlah ?? 0, 0, ',', '.') }}
                                            </td>
                                        @endforeach
                                        <td class="text-right px-2 py-1">
                                            {{ number_format($detail->omset_total ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-right px-2 py-1">
                                            {{ number_format($detail->qris ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-right px-2 py-1">
                                            {{ number_format($detail->cash ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-right px-2 py-1">
                                            {{ number_format($detail->merch ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-right px-2 py-1">
                                            {{ number_format($detail->rca ?? 0, 0, ',', '.') }}</td>
                                        @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                                            <td class="text-right px-2 py-1">
                                                {{ number_format($detail->titipan ?? 0, 0, ',', '.') }}</td>
                                        @endif
                                        <td class="text-right px-2 py-1">
                                            {{ number_format($detail->lebih_kurang ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-100 font-bold">
                                {{-- SUBTOTAL PER ITEM --}}
                                <tr class="bg-blue-50">
                                    <td class="text-left px-2 py-1">
                                        @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                                            SUBTOTAL
                                        @else
                                            GRAND TOTAL
                                        @endif
                                    </td>

                                    @foreach ($items as $itemName)
                                        @php
                                            $subtotalQty = $data->details
                                                ->where('wahanaItem.nama_item', $itemName)
                                                ->sum('qty');
                                            $subtotalJumlah = $data->details
                                                ->where('wahanaItem.nama_item', $itemName)
                                                ->sum('jumlah');
                                            // bisa hitung harga rata-rata kalau mau
                                            $subtotalHarga = $subtotalQty > 0 ? $subtotalJumlah / $subtotalQty : 0;
                                        @endphp

                                        <td class="text-center px-2 py-1">
                                            {{ number_format($subtotalQty, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right px-2 py-1">
                                            {{ number_format($subtotalHarga, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right px-2 py-1 bg-yellow-50">
                                            {{ number_format($subtotalJumlah, 0, ',', '.') }}
                                        </td>
                                    @endforeach

                                    {{-- Total omset semua item --}}
                                    @php
                                        $subtotalOmset = 0;
                                        $subtotalCash = 0;
                                        $subtotalQris = 0;
                                        $subtotalMerch = 0;
                                        $subtotalRca = 0;
                                        $subtotalTitipan = 0;
                                        $subtotalLebihKurang = 0;

                                        foreach ($data->details->groupBy('wahanaItem.wahana_id') as $group) {
                                            $detail = $group->first();
                                            $subtotalOmset += $detail->omset_total ?? 0;
                                            $subtotalCash += $detail->cash ?? 0;
                                            $subtotalQris += $detail->qris ?? 0;
                                            $subtotalMerch += $detail->merch ?? 0;
                                            $subtotalRca += $detail->rca ?? 0;
                                            $subtotalTitipan += $detail->titipan ?? 0;
                                            $subtotalLebihKurang += $detail->lebih_kurang ?? 0;
                                        }
                                    @endphp

                                    <td class="text-right px-2 py-1 bg-green-100">
                                        {{ number_format($subtotalOmset, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right px-2 py-1 bg-green-100">
                                        {{ number_format($subtotalQris, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right px-2 py-1 bg-green-100">
                                        {{ number_format($subtotalCash, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right px-2 py-1 bg-green-100">
                                        {{ number_format($subtotalMerch, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right px-2 py-1 bg-green-100">
                                        {{ number_format($subtotalRca, 0, ',', '.') }}
                                    </td>
                                    @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                                        <td class="text-right px-2 py-1 bg-green-100">
                                            {{ number_format($subtotalTitipan, 0, ',', '.') }}
                                        </td>
                                    @endif
                                    <td class="text-right px-2 py-1 bg-green-100">
                                        {{ number_format($subtotalLebihKurang, 0, ',', '.') }}
                                    </td>
                                </tr>

                                {{-- MDR dan Subtotal setelah MDR --}}
                                @php
                                    $subtotalTitipan = 0;
                                    foreach ($data->details->groupBy('wahanaItem.wahana_id') as $group) {
                                        $detail = $group->first();
                                        $subtotalTitipan += $detail->titipan ?? 0;
                                    }
                                    $totalTitipan = 0;
                                    $mdrAmount = $subtotalTitipan * 0.007;
                                    $subtotalAfterMdr = $totalTitipan - $mdrAmount;
                                @endphp
                                @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                                    <tr class="bg-gray-50">
                                        <td class="text-left px-2 py-1">MDR 0,7%</td>
                                        @foreach ($items as $itemName)
                                            <td colspan="3" class="px-2 py-1"></td>
                                        @endforeach
                                        <td colspan="5" class="px-2 py-1"></td>
                                        <td class="text-right px-2 py-1 text-red-500">
                                            -{{ number_format($mdrAmount, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right px-2 py-1 text-red-500">
                                            -{{ number_format($mdrAmount, 0, ',', '.') }}
                                        </td>

                                    </tr>
                                @endif


                                {{-- GRAND TOTAL --}}
                                @php
                                    $grandtotalOmset = 0;
                                    $grandCash = 0;
                                    $grandQris = 0;
                                    $grandMerch = 0;
                                    $grandRca = 0;
                                    $grandTitipan = 0;
                                    $grandLebihKurang = 0;

                                    foreach ($data->details->groupBy('wahanaItem.wahana_id') as $group) {
                                        $detail = $group->first();
                                        $grandtotalOmset += $detail->omset_total ?? 0;
                                        $grandCash += $detail->cash ?? 0;
                                        $grandQris += $detail->qris ?? 0;
                                        $grandMerch += $detail->merch ?? 0;
                                        $grandRca += $detail->rca ?? 0;
                                        $grandTitipan += $detail->titipan ?? 0;
                                        $grandLebihKurang += $detail->lebih_kurang ?? 0;
                                    }

                                    $mdrAmount = $grandTitipan * 0.007;
                                    $grandAfterMdr = $grandTitipan - $mdrAmount;
                                    $grandLebihKurangAfterMdr = $grandLebihKurang + $mdrAmount;

                                @endphp


                                @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                                    <tr class="bg-blue-200">
                                        <td class="text-left px-2 py-1 font-semibold">GRAND TOTAL</td>
                                        @foreach ($items as $itemName)
                                            <td colspan="3" class="px-2 py-1"></td>
                                        @endforeach
                                        <td class="text-right px-2 py-1 bg-gray-300">
                                            {{ number_format($grandtotalOmset, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right px-2 py-1 bg-gray-300">
                                            {{ number_format($grandQris, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right px-2 py-1 bg-gray-300">
                                            {{ number_format($grandCash, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right px-2 py-1 bg-gray-300">
                                            {{ number_format($grandMerch, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right px-2 py-1 bg-gray-300">
                                            {{ number_format($grandRca, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right px-2 py-1 bg-gray-300">
                                            {{ number_format($grandAfterMdr, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right px-2 py-1 bg-gray-300">
                                            {{ number_format($grandLebihKurangAfterMdr, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif
                            </tfoot>

                        </table>
                    </div>
                </div>
            </div>

            <style>
                table,
                th,
                td {
                    border: 2px solid black !important;
                    border-collapse: collapse;
                }
            </style>
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('closing_harian.edit', $data->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('closing_harian.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

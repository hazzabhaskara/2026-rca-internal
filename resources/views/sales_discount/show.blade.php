@extends('layouts.app')

@section('content')
    <div class="container mx-auto py-6">
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-2xl font-semibold mb-6">Sales Discount Details</h2>
            <!-- Tab Detail -->
            <div>
                <!-- Informasi Utama Sales Order -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <strong>Nama Discount:</strong>
                        <p>{{ $salesDiscount->nama_diskon }}</p>
                    </div>
                    <div>
                        <strong>Jenis Discount:</strong>
                        <p>{{ $salesDiscount->jenis_diskon ?? '-' }}</p>
                    </div>
                    <div>
                        <strong>Deskripsi:</strong>
                        <p>{{ $salesDiscount->deskripsi ?? 'Tidak Ada' }}</p>
                    </div>
                    <div>
                        <strong>Aktif:</strong>
                        <p>{{ $salesDiscount->aktif ?? '-' }}</p>
                    </div>
                </div>

                <!-- Detail Items -->
                <h3 class="text-xl font-semibold mb-2">Sales Discount Detail</h3>
                <div class="overflow-auto">
                    <table class="table-auto w-full border-collapse border border-gray-200 text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                @if ($salesDiscount->jenis_diskon === 'early_payment')
                                    <th class="border px-3 py-2">Hari Ke</th>
                                @endif
                                <th class="border px-3 py-2">Tipe Nilai</th>
                                <th class="border px-3 py-2">Nilai Discount</th>
                                @if ($salesDiscount->jenis_diskon === 'berlapis' || $salesDiscount->jenis_diskon === 'early_payment')
                                    <th class="border px-3 py-2">Urutan</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($salesDiscount->details as $item)
                                <tr>
                                    @if ($salesDiscount->jenis_diskon === 'early_payment')
                                        <td class="border px-3 py-2">{{ $item->hari_ke }}</td>
                                    @endif

                                    <td class="border px-3 py-2">{{ $item->tipe_nilai }}</td>
                                    <td class="border px-3 py-2">
                                        {{ $item->nilai_diskon }}
                                        @if ($item->tipe_nilai === 'persen')
                                            %
                                        @endif
                                    </td>

                                    @if ($salesDiscount->jenis_diskon === 'berlapis' || $salesDiscount->jenis_diskon === 'early_payment')
                                        <td class="border px-3 py-2">{{ $item->urutan }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>


            <!-- Tombol Kembali -->
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('sales_discount.edit', $salesDiscount->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('sales_discount.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

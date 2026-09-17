@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div x-data="{ tab: 'details' }" class="bg-white p-6 rounded shadow w-full">
                <div class=" bg-white p-6 rounded shadow">
                    <h2 class="text-2xl font-semibold mb-6">Sales Order Details</h2>

                    <!-- Tabs -->
                    <div class="border-b mb-4 flex space-x-4">
                        <button @click="tab = 'details'"
                            :class="tab === 'details' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
                            class="px-4 py-2 focus:outline-none">
                            Detail Order
                        </button>
                        <button @click="tab = 'documents'"
                            :class="tab === 'documents' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
                            class="px-4 py-2 focus:outline-none">
                            Dokumen
                        </button>
                    </div>

                    <!-- Tab Detail -->
                    <div x-show="tab === 'details'">
                        <!-- Informasi Utama Sales Order -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                            <div>
                                <strong>Order Number:</strong>
                                <p>{{ $salesOrder->order_number }}</p>
                            </div>
                            <div>
                                <strong>Date Order:</strong>
                                <p>{{ \Carbon\Carbon::parse($salesOrder->date_order)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <strong>Shipping Date:</strong>
                                <p>{{ \Carbon\Carbon::parse($salesOrder->shipping_date)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <strong>Location Inventory:</strong>
                                <p>{{ $salesOrder->locationInventory->kode_lokasi ?? '-' }}</p>
                            </div>
                            <div>
                                <strong>Customer:</strong>
                                <p>{{ $salesOrder->customer->nama_customers ?? '-' }}</p>
                            </div>
                            <div>
                                <strong>Sales Person:</strong>
                                <p>{{ $salesOrder->salesPerson->nama_karyawan ?? '-' }}</p>
                            </div>
                            <div>
                                <strong>Payment Method:</strong>
                                <p>{{ $salesOrder->jenisPembayaran->nama_jenis ?? '-' }}</p>
                            </div>
                            <div class="md:col-span-3">
                                <strong>Shipping Address:</strong>
                                <p>{{ $salesOrder->shipping_address }}</p>
                            </div>
                            <div>
                                <strong>Early Payment Terms:</strong>
                                <p>{{ $salesOrder->early_payment_terms }}</p>
                            </div>
                            <div class="md:col-span-3">
                                <strong>Messages:</strong>
                                <p>{{ $salesOrder->messages }}</p>
                            </div>
                        </div>

                        <!-- Detail Items -->
                        <h3 class="text-xl font-semibold mb-2">Order Items</h3>
                        <div class="overflow-auto">
                            <table class="table-auto w-full border-collapse border border-gray-200 text-sm">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th class="border px-3 py-2">Item</th>
                                        <th class="border px-3 py-2">Order</th>
                                        <th class="border px-3 py-2">Unit</th>
                                        <th class="border px-3 py-2">Description</th>
                                        <th class="border px-3 py-2">Base Price</th>
                                        <th class="border px-3 py-2">Discount</th>
                                        <th class="border px-3 py-2">Price</th>
                                        <th class="border px-3 py-2">Amount</th>
                                        <th class="border px-3 py-2">Tax</th>
                                        <th class="border px-3 py-2">Account</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($salesOrder->details as $item)
                                        <tr>
                                            <td class="border px-3 py-2">{{ $item->item->item_description ?? '-' }}</td>
                                            <td class="border px-3 py-2 text-center">{{ $item->order }}</td>
                                            <td class="border px-3 py-2 text-center">{{ $item->unit }}</td>
                                            <td class="border px-3 py-2">{{ $item->item_description }}</td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->base_price) }}
                                            </td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->discount) }}
                                            </td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->price) }}</td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->amount) }}</td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->tax) }}</td>
                                            <td class="border px-3 py-2">{{ $item->account->nama_akun ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    @php
                                        // Inisialisasi
                                        $subtotal = 0;
                                        $totalInputTax = 0; // PPN (+)
                                        $totalWithholding = 0; // PPh (+ di sini, tapi akan dikurangkan saat net)
                                    @endphp

                                    @foreach ($salesOrder->details as $item)
                                        @php
                                            // Jumlahkan subtotal (sebelum pajak)
                                            $subtotal += (float) ($item->amount ?? 0);

                                            // Ambil tipe pajak dari relasi yang BENAR (ganti 'sales_taxes' sesuai nama relasi di model)
                                            $taxType = optional($item->sales_taxes)->type; // 'input_tax' | 'withholding_tax' | null
                                            $taxAmt = (float) ($item->tax ?? 0); // pastikan selalu disimpan positif di DB

                                            if ($taxType === 'input_tax') {
                                                $totalInputTax += $taxAmt; // PPN ditambah
                                            } elseif ($taxType === 'withholding_tax') {
                                                $totalWithholding += $taxAmt; // PPh diakumulasi terpisah (positif di sini)
                                            }
                                        @endphp
                                    @endforeach

                                    @php
                                        $freight = (float) ($salesOrder->freight ?? 0);
                                        $totalTaxNet = $totalInputTax - $totalWithholding; // PPN (+) dikurangi PPh (–)
                                        $grandTotal = $subtotal + $totalTaxNet + $freight;
                                    @endphp@php
                                        // Inisialisasi
                                        $subtotal = 0;
                                        $totalInputTax = 0; // PPN (+)
                                        $totalWithholding = 0; // PPh (+ di sini, tapi akan dikurangkan saat net)
                                    @endphp

                                    @foreach ($salesOrder->details as $item)
                                        @php
                                            // Jumlahkan subtotal (sebelum pajak)
                                            $subtotal += (float) ($item->amount ?? 0);

                                            // Ambil tipe pajak dari relasi yang BENAR (ganti 'sales_taxes' sesuai nama relasi di model)
                                            $taxType = optional($item->sales_taxes)->type; // 'input_tax' | 'withholding_tax' | null
                                            $taxAmt = (float) ($item->tax ?? 0); // pastikan selalu disimpan positif di DB

                                            if ($taxType === 'input_tax') {
                                                $totalInputTax += $taxAmt; // PPN ditambah
                                            } elseif ($taxType === 'withholding_tax') {
                                                $totalWithholding += $taxAmt; // PPh diakumulasi terpisah (positif di sini)
                                            }
                                        @endphp
                                    @endforeach

                                    @php
                                        $freight = (float) ($salesOrder->freight ?? 0);
                                        $totalTaxNet = $totalInputTax - $totalWithholding; // PPN (+) dikurangi PPh (–)
                                        $grandTotal = $subtotal + $totalTaxNet + $freight;
                                    @endphp

                                    <tr>
                                        <td colspan="7" class="border px-3 py-2 text-right font-bold">Subtotal</td>
                                        <td class="border px-3 py-2 text-right font-bold">{{ number_format($subtotal) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="border px-3 py-2 text-right font-bold">Total Pajak</td>
                                        <td class="border px-3 py-2 text-right font-bold">
                                            {{ number_format($totalTaxNet) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="border px-3 py-2 text-right font-bold">Freight</td>
                                        <td class="border px-3 py-2 text-right font-bold">{{ number_format($freight) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="border px-3 py-2 text-right font-bold">Grand Total</td>
                                        <td class="border px-3 py-2 text-right font-bold">{{ number_format($grandTotal) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Dokumen -->
                    <div x-show="tab === 'documents'">
                        <h3 class="text-xl font-semibold mb-4">Dokumen Sales Order</h3>

                        <a href="{{ route('sales_order.pdf', $salesOrder->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition">
                            <i class="fas fa-file-pdf mr-2"></i>Download PDF
                        </a>

                    </div>

                    <!-- Tombol Kembali -->
                    <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">

                        @if ($salesOrder->status_sales === 0)
                            <a href="{{ route('sales_order.edit', $salesOrder->id) }}"
                                class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                                <i class="fas fa-edit mr-2"></i>Edit
                            </a>
                        @endif
                        <a href="{{ route('sales_order.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

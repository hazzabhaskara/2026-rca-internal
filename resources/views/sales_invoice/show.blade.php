@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            <div class="mx-auto py-6" x-data="{ tab: 'details' }">
                @php
                    $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                @endphp
                <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                    <h2 class="text-2xl font-semibold mb-6">Sales Invoice Details</h2>

                    <!-- Tabs -->
                    <div class="border-b mb-4 flex space-x-4">
                        <button @click="tab = 'details'"
                            :class="tab === 'details' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
                            class="px-4 py-2 focus:outline-none">
                            Detail Invoice
                        </button>
                        <button @click="tab = 'documents'"
                            :class="tab === 'documents' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
                            class="px-4 py-2 focus:outline-none">
                            Dokumen
                        </button>
                    </div>

                    <!-- Informasi Utama Sales Order -->
                    <div x-show="tab === 'details'">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <strong>Invoice Number:</strong>
                                <p>{{ $salesInvoice->invoice_number }}</p>
                            </div>
                            <div>
                                <strong>Invoice Date:</strong>
                                <p>{{ \Carbon\Carbon::parse($salesInvoice->invoice_date)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <strong>Shipping Date:</strong>
                                <p>{{ \Carbon\Carbon::parse($salesInvoice->shipping_date)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <strong>Customer:</strong>
                                <p>{{ $salesInvoice->customer->nama_customers ?? '-' }}</p>
                            </div>
                            <div>
                                <strong>Sales Person:</strong>
                                <p>{{ $salesInvoice->salesPerson->nama_karyawan ?? '-' }}</p>
                            </div>
                            <div>
                                <strong>Payment Method:</strong>
                                <p>{{ $salesInvoice->jenisPembayaran->nama_jenis ?? '-' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <strong>Shipping Address:</strong>
                                <p>{{ $salesInvoice->shipping_address }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <strong>Location Inventory:</strong>
                                <p>{{ $salesInvoice->lokasi_inventory->kode_lokasi ?? '-' }}</p>
                            </div>
                            <div>
                                <strong>Early Payment Terms:</strong>
                                <p>{{ $salesInvoice->early_payment_terms }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <strong>Messages:</strong>
                                <p>{{ $salesInvoice->messages }}</p>
                            </div>
                        </div>

                        <!-- Detail Items -->
                        <h3 class="text-xl font-semibold mb-2">Order Items</h3>
                        <div class="overflow-auto">
                            <table class="table-auto w-full border-collapse border border-gray-200 text-sm">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th class="border px-3 py-2">Item</th>
                                        <th class="border px-3 py-2">Qty</th>
                                        <th class="border px-3 py-2">Order</th>
                                        <th class="border px-3 py-2">Back Order</th>
                                        <th class="border px-3 py-2">Unit</th>
                                        <th class="border px-3 py-2">Description</th>
                                        <th class="border px-3 py-2 text-right">Base Price</th>
                                        <th class="border px-3 py-2 text-right">Discount</th>
                                        <th class="border px-3 py-2 text-right">Price</th>
                                        <th class="border px-3 py-2 text-right">Amount</th>
                                        <th class="border px-3 py-2 text-right">Rate</th>
                                        <th class="border px-3 py-2 text-right">Tax</th>
                                        <th class="border px-3 py-2">Account</th>
                                        <th class="border px-3 py-2">Project</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $subtotal = 0;
                                        $totalInputTax = 0; // PPN (+)
                                        $totalWithholding = 0; // PPh (-)
                                        $total_tax = 0;
                                    @endphp
                                    @foreach ($salesInvoice->details as $item)
                                        @php
                                            // Ambil tipe pajak dari relasi
                                            $taxType = optional($item->sales_taxes)->type;
                                            $taxAmt = (float) ($item->tax_amount ?? 0);

                                            // Hitung subtotal per item
                                            $amount = ($item->price - $item->discount) * $item->order;
                                            $subtotal += $amount;

                                            // Klasifikasikan pajak
                                            if ($taxType === 'input_tax') {
                                                $totalInputTax += $taxAmt; // PPN → tambah
                                            } elseif ($taxType === 'withholding_tax') {
                                                $totalWithholding += $taxAmt; // PPh → simpan untuk dikurangkan nanti
                                            }
                                        @endphp
                                        <tr>
                                            <td class="border px-3 py-2">{{ $item->item->item_description ?? '-' }}</td>
                                            <td class="border px-3 py-2 text-left">{{ $item->quantity }}</td>
                                            <td class="border px-3 py-2 text-left">{{ $item->order_quantity }}</td>
                                            <td class="border px-3 py-2 text-left">{{ $item->back_order }}</td>
                                            <td class="border px-3 py-2 text-left">{{ $item->unit }}</td>
                                            <td class="border px-3 py-2">{{ $item->description }}</td>
                                            <td class="border px-3 py-2 text-right">
                                                {{ number_format($item->base_price) }}
                                            </td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->discount) }}
                                            </td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->price) }}
                                            </td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->amount) }}
                                            </td>
                                            <td class="border px-3 py-2 text-right">{{ $item->sales_taxes->rate ?? '-' }} %
                                            </td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->tax) }}
                                            </td>
                                            <td class="border px-3 py-2">{{ $item->account->nama_akun ?? '-' }}</td>
                                            <td class="border px-3 py-2">{{ $item->project->nama_project ?? 'Tidak Ada' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    @php
                                        $amount = $salesInvoice->details->sum('amount');
                                        $totalTax = $salesInvoice->details->sum('tax');
                                        $subtotal = $amount + $totalTax;
                                        $freight = $salesInvoice->freight ?? 0;

                                        $withholding_rate = optional($salesInvoice->withholding)->rate ?? 0;
                                        $witholding_value = $subtotal * ($withholding_rate / 100);
                                        $grandTotal = $subtotal - $witholding_value + $freight;
                                    @endphp
                                    <tr>
                                        <td colspan="9" class="border px-3 py-2 text-right font-bold">Subtotal</td>
                                        <td class="border px-3 py-2 text-right font-bold">{{ number_format($subtotal) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="border px-3 py-2 text-right font-bold">Tax</td>
                                        <td class="border px-3 py-2 text-right font-bold">
                                            {{ $salesInvoice->withholding->rate ?? '' }}%
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="border px-3 py-2 text-right font-bold">Tax Value</td>
                                        <td class="border px-3 py-2 text-right font-bold">
                                            {{ number_format($witholding_value) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="border px-3 py-2 text-right font-bold">Freight</td>
                                        <td class="border px-3 py-2 text-right font-bold">{{ number_format($freight) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="border px-3 py-2 text-right font-bold">Grand Total</td>
                                        <td class="border px-3 py-2 text-right font-bold">{{ number_format($grandTotal) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div x-show="tab === 'documents'">
                        <h3 class="text-xl font-semibold mb-4">Dokumen Sales Invoice</h3>

                        <a href="{{ route('sales_invoice.pdf', $salesInvoice->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition">
                            <i class="fas fa-file-pdf mr-2"></i>Download PDF
                        </a>
                    </div>

                    <!-- Tombol Kembali -->
                    <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                        @if ($salesInvoice->status === 0)
                            <a href="{{ route('sales_invoice.edit', $salesInvoice->id) }}"
                                class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                                <i class="fas fa-edit mr-2"></i>Edit
                            </a>
                        @endif

                        <a href="{{ route('sales_invoice.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

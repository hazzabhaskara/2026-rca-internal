@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="mx-auto py-6" x-data="{ tab: 'details' }">
                <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                    <h2 class="text-2xl font-semibold mb-6">Purchase Order Details</h2>
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

                    <!-- Informasi Utama purchase Order -->
                    <div x-show="tab === 'details'">

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                            <div>
                                <strong>Order Number:</strong>
                                <p>{{ $purchaseOrder->order_number }}</p>
                            </div>
                            <div>
                                <strong>Date Order:</strong>
                                <p>{{ \Carbon\Carbon::parse($purchaseOrder->date_order)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <strong>Shipping Date:</strong>
                                <p>{{ \Carbon\Carbon::parse($purchaseOrder->shipping_date)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <strong>Location Inventory:</strong>
                                <p>{{ $purchaseOrder->locationInventory->kode_lokasi ?? '-' }}</p>
                            </div>
                            <div>
                                <strong>Vendor:</strong>
                                <p>{{ $purchaseOrder->vendor->nama_vendors ?? '-' }}</p>
                            </div>

                            <div>
                                <strong>Payment Method:</strong>
                                <p>{{ $purchaseOrder->jenisPembayaran->nama_jenis ?? '-' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <strong>Shipping Address:</strong>
                                <p>{{ $purchaseOrder->shipping_address }}</p>
                            </div>
                            {{-- <div>
                                <strong>Freight:</strong>
                                <p>{{ number_format($purchaseOrder->freight, 2) }}</p>
                            </div> --}}
                            <div>
                                <strong>Early Payment Terms:</strong>
                                <p>{{ $purchaseOrder->early_payment_terms }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <strong>Messages:</strong>
                                <p>{{ $purchaseOrder->messages }}</p>
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
                                        {{-- <th class="border px-3 py-2">Discount</th> --}}
                                        <th class="border px-3 py-2">Price</th>
                                        <th class="border px-3 py-2">Discount</th>
                                        <th class="border px-3 py-2">Amount</th>
                                        <th class="border px-3 py-2">Tax</th>
                                        <th class="border px-3 py-2">Total Tax</th>
                                        <th class="border px-3 py-2">Total Amount</th>
                                        <th class="border px-3 py-2">Account</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $subtotal = 0;
                                        $totalInputTax = 0; // PPN (+)
                                        $totalWithholding = 0; // PPh (-)
                                    @endphp

                                    @foreach ($purchaseOrder->details as $item)
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
                                            <td class="border px-3 py-2">{{ $item->item_description ?? '-' }}</td>
                                            <td class="border px-3 py-2 text-center">{{ $item->order }}</td>
                                            <td class="border px-3 py-2 text-center">{{ $item->unit }}</td>
                                            <td class="border px-3 py-2 text-center">{{ $item->item_description }}</td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->price, 2) }}
                                            </td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->discount, 2) }}
                                            </td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($amount, 2) }}</td>
                                            <td class="border px-3 py-2 text-right">
                                                {{ optional($item->sales_taxes)->rate ? $item->sales_taxes->rate . '%' : '-' }}
                                            </td>
                                            <td class="border px-3 py-2 text-right">
                                                {{ number_format($item->tax_amount, 2) }}</td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->amount, 2) }}
                                            </td>
                                            <td class="border px-3 py-2">{{ $item->account->nama_akun ?? '-' }}</td>
                                        </tr>
                                    @endforeach

                                    @php
                                        // 🚀 Setelah looping baru total akhir dihitung
                                        $totalTaxNet = $totalInputTax - $totalWithholding; // PPN (+), PPh (–)
                                        $grandTotal = $subtotal + $totalTaxNet + ($purchaseOrder->freight ?? 0);
                                    @endphp
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td colspan="8"></td>
                                        <td class="pr-3 text-right font-semibold">Subtotal :</td>
                                        <td class="w-32 border rounded text-right px-2 py-1 bg-gray-100">
                                            {{ number_format($subtotal, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8"></td>
                                        <td class="pr-3 text-right font-semibold">Total Tax :</td>
                                        <td class="w-32 border rounded text-right px-2 py-1">
                                            {{ number_format($totalTaxNet, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8"></td>
                                        <td class="pr-3 text-right font-semibold">Freight :</td>
                                        <td class="w-32 border rounded text-right px-2 py-1 bg-gray-100">
                                            {{ number_format($purchaseOrder->freight, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8"></td>
                                        <td class="pr-3 text-right font-semibold">Grand Total :</td>
                                        <td class="w-32 border rounded text-right px-2 py-1 bg-gray-100">
                                            {{ number_format($grandTotal, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>

                            </table>
                        </div>
                    </div>

                    <div x-show="tab === 'documents'">
                        <h3 class="text-xl font-semibold mb-4">Dokumen purchase Order</h3>
                        <!-- Form Upload -->
                        <form action="{{ route('purchase_order.documents.store', $purchaseOrder->id) }}" method="POST"
                            enctype="multipart/form-data" class="space-y-4 mb-6">
                            @csrf
                            <div>
                                <label for="document_name" class="block text-sm font-medium text-gray-700">Nama
                                    Dokumen</label>
                                <input type="text" name="document_name" id="document_name"
                                    class="mt-1 w-full border rounded p-2" required>
                            </div>
                            <div>
                                <label for="file" class="block text-sm font-medium text-gray-700">File</label>
                                <input type="file" name="file" id="file" class="mt-1 w-full border rounded p-2"
                                    required>
                                <p class="text-xs text-gray-500">Format: pdf, docx, xlsx, jpg, png (maks. 2MB)</p>
                            </div>
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                                <textarea name="description" id="description" class="mt-1 w-full border rounded p-2"></textarea>
                            </div>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Upload</button>
                        </form>

                        <!-- Tabel Dokumen -->
                        <table class="table-auto w-full border-collapse border border-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border px-3 py-2">Nama</th>
                                    <th class="border px-3 py-2 text-center">File</th>
                                    <th class="border px-3 py-2">Deskripsi</th>
                                    <th class="border px-3 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseOrder->documents as $doc)
                                    <tr>
                                        <td class="border px-3 py-2">{{ $doc->document_name }}</td>
                                        <td class="border px-3 py-2">
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                                class="text-blue-600 hover:underline mr-4">
                                                📄 View
                                            </a>
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" download
                                                class="text-green-600 hover:underline">
                                                ⬇️ Download
                                            </a>
                                        </td>
                                        <td class="border px-3 py-2">{{ $doc->description }}</td>
                                        <td class="border px-3 py-2">
                                            <form
                                                action="{{ route('purchase_order.documents.destroy', [$purchaseOrder->id, $doc->id]) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:underline">Hapus</button>
                                            </form>

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="border px-3 py-2 text-center text-gray-500">Belum ada
                                            dokumen</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Tombol Kembali -->
                    <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                        {{-- <a href="{{ route('purchase_order.edit', $purchaseOrder->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a> --}}
                        <a href="{{ route('purchase_order.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="mx-auto py-6">
                <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}"
                    x-data="{ tab: 'details' }">
                    <h2 class="text-2xl font-semibold mb-6">Purchase Invoice Details</h2>
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

                    <!-- Informasi Utama Sales Order -->
                    <div x-show="tab === 'details'">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <strong>Invoice Number:</strong>
                                <p>{{ $purchaseInvoice->invoice_number }}</p>
                            </div>
                            <div>
                                <strong>Invoice Date:</strong>
                                <p>{{ \Carbon\Carbon::parse($purchaseInvoice->date_invoice)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <strong>Shipping Date:</strong>
                                <p>{{ \Carbon\Carbon::parse($purchaseInvoice->shipping_date)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <strong>Vendor:</strong>
                                <p>{{ $purchaseInvoice->vendor->nama_vendors ?? '-' }}</p>
                            </div>
                            <div>
                                <strong>Payment Method:</strong>
                                <p>{{ $purchaseInvoice->jenisPembayaran->nama_jenis ?? '-' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <strong>Shipping Address:</strong>
                                <p>{{ $purchaseInvoice->shipping_address }}</p>
                            </div>
                            {{-- <div>
                                <strong>Freight:</strong>
                                <p>{{ number_format($purchaseInvoice->freight, 2) }}</p>
                            </div> --}}
                            <div>
                                <strong>Early Payment Terms:</strong>
                                <p>{{ $purchaseInvoice->early_payment_terms ?? 'Tidak Ada' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <strong>Messages:</strong>
                                <p>{{ $purchaseInvoice->messages ?? 'Tidak ada' }}</p>
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
                                        <th class="border px-3 py-2">Price</th>
                                        <th class="border px-3 py-2">Discount</th>
                                        <th class="border px-3 py-2">Vat</th>
                                        <th class="border px-3 py-2">Vat Value</th>
                                        <th class="border px-3 py-2">Amount</th>
                                        <th class="border px-3 py-2">Account</th>
                                        <th class="border px-3 py-2">Project</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @php
                                        $total_tax = 0;
                                        $amount = 0;
                                        $subtotal = 0;
                                    $total = 0; @endphp
                                    @foreach ($purchaseInvoice->details as $item)
                                        @php
                                            $amount = ($item->price - $item->discount) * $item->quantity;
                                            $total_tax += $item->tax_amount;
                                            $final = $amount + $total_tax;
                                            $subtotal += $final;
                                            $withholding_tax = optional($purchaseInvoice->withholding)->rate ?? 0;
                                            $withholding_value = $subtotal * ($withholding_tax / 100);
                                            $total = $subtotal - $withholding_value + $purchaseInvoice->freight;
                                        @endphp
                                        <tr>
                                            <td class="border px-3 py-2">{{ $item->item->item_description ?? '-' }}</td>
                                            <td class="border px-3 py-2 text-center">{{ $item->quantity }}</td>
                                            <td class="border px-3 py-2 text-center">{{ $item->order }}</td>
                                            <td class="border px-3 py-2 text-center">{{ $item->back_order }}</td>
                                            <td class="border px-3 py-2 text-center">{{ $item->unit }}</td>
                                            <td class="border px-3 py-2">{{ $item->item_description }}</td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->price) }}</td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->discount) }}
                                            </td>
                                            <td class="border px-3 py-2 text-right">
                                                {{ optional($item->sales_taxes)->rate ? $item->sales_taxes->rate . '%' : '-' }}
                                            </td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->tax_amount) }}
                                            </td>
                                            <td class="border px-3 py-2 text-right">{{ number_format($item->amount) }}</td>
                                            <td class="border px-3 py-2">{{ $item->account->nama_akun ?? '-' }}</td>
                                            <td class="border px-3 py-2">{{ $item->project->nama_project ?? 'Tidak Ada' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="9"></td>
                                        <td class="pr-3 text-right font-semibold">Subtotal :</td>
                                        <td class="w-32 border rounded text-right px-2 py-1 bg-gray-100">
                                            {{ number_format($subtotal, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="9"></td>
                                        <td class="pr-3 text-right font-semibold">Tax :</td>
                                        <td class="w-32 border rounded text-right px-2 py-1">
                                            {{ $withholding_tax }}%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="9"></td>
                                        <td class="pr-3 text-right font-semibold">Tax Value :</td>
                                        <td class="w-32 border rounded text-right px-2 py-1">
                                            {{ number_format($withholding_value, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="9"></td>
                                        <td class="pr-3 text-right font-semibold">Freight :</td>
                                        <td class="w-32 border rounded text-right px-2 py-1 bg-gray-100">
                                            {{ number_format($purchaseInvoice->freight, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="9"></td>
                                        <td class="pr-3 text-right font-semibold">Total :</td>
                                        <td class="w-32 border rounded text-right px-2 py-1 bg-gray-100">
                                            {{ number_format($total, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div x-show="tab === 'documents'">
                        <h3 class="text-xl font-semibold mb-4">Dokumen Purchase Invoice</h3>
                        <!-- Form Upload -->
                        <form action="{{ route('purchase_invoice.documents.store', $purchaseInvoice->id) }}" method="POST"
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
                                @forelse($purchaseInvoice->documents as $doc)
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
                                                action="{{ route('purchase_invoice.documents.destroy', [$purchaseInvoice->id, $doc->id]) }}"
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
                        @if ($purchaseInvoice->status_purchase == 0)
                            <a href="{{ route('purchase_invoice.edit', $purchaseInvoice->id) }}"
                                class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                                <i class="fas fa-edit mr-2"></i>Edit
                            </a>
                        @endif
                        <a href="{{ route('purchase_invoice.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

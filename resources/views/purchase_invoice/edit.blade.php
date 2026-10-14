@extends('layouts.app')

@section('content')
    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-full mx-auto">
        @php
            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
        @endphp

        <!-- 📑 HEADER & TABS NAVIGATION -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 mb-6 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight flex items-center">
                    <i class="fas fa-edit mr-3 text-indigo-600"></i>
                    Edit Purchase Invoice: <span class="ml-2 font-mono text-indigo-700">{{ $purchaseInvoice->invoice_number }}</span>
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Perbarui rincian pengadaan barang, verifikasi faktur pajak Coretax, dan kelola dokumen digital.
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-2 bg-gray-100 p-1.5 rounded-xl border border-gray-200">
                <a href="#select_item" class="tab-link active px-4 py-2 text-xs font-semibold rounded-lg transition-all text-white bg-indigo-600 shadow-sm flex items-center">
                    <i class="fas fa-edit mr-2"></i> Form Invoice
                </a>
                <a href="#journal_report" class="tab-link px-4 py-2 text-xs font-semibold rounded-lg transition-all text-gray-600 hover:text-gray-900 flex items-center">
                    <i class="fas fa-book mr-2"></i> Pratinjau Jurnal
                </a>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" action="{{ route('purchase_invoice.update', $purchaseInvoice->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm text-sm text-red-800">
                    <div class="flex items-center font-bold mb-1">
                        <i class="fas fa-exclamation-circle mr-2 text-red-600"></i> Mohon lengkapi data yang belum valid:
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 ml-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="select_item" class="tab-content space-y-6">

                <!-- 🏢 CARD 1: INFORMASI TRANSAKSI & REKANAN -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50 border-b border-gray-200 flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-700">1. Informasi Dokumen & Rekanan</h2>
                        </div>
                        <span class="text-xs bg-gray-200 text-gray-700 px-2.5 py-1 rounded-full font-mono font-semibold">
                            PO: {{ $purchaseInvoice->purchaseOrder->order_number ?? '-' }}
                        </span>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                            <!-- Invoice Number -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Invoice Number
                                </label>
                                <input type="text" name="invoice_number"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100 font-mono font-semibold text-gray-700 cursor-not-allowed"
                                    value="{{ $purchaseInvoice->invoice_number }}" readonly>
                            </div>

                            <!-- Invoice Date -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Tanggal Invoice <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="date_invoice" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    value="{{ $purchaseInvoice->date_invoice }}">
                            </div>

                            <!-- Shipping Date -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Tanggal Kirim / Tiba
                                </label>
                                <input type="date" name="shipping_date"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    value="{{ $purchaseInvoice->shipping_date }}">
                            </div>

                            <!-- Location Inventory -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Gudang Penyimpanan <span class="text-red-500">*</span>
                                </label>
                                <select name="location_id" id="location_id" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    @foreach ($locationInventory as $loc)
                                        <option value="{{ $loc->id }}"
                                            {{ $purchaseInvoice->location_id == $loc->id ? 'selected' : '' }}>
                                            {{ $loc->kode_lokasi }} ({{ $loc->nama_lokasi ?? 'Gudang' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Vendor -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Nama Vendor / Supplier <span class="text-red-500">*</span>
                                </label>
                                <select name="vendor_id" id="vendor_id" required
                                    class="w-full border border-gray-300 rounded-lg text-sm">
                                    @foreach ($vendor as $ven)
                                        <option value="{{ $ven->id }}"
                                            {{ $purchaseInvoice->vendor_id == $ven->id ? 'selected' : '' }}>
                                            {{ $ven->nama_vendors }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Metode Pembayaran <span class="text-red-500">*</span>
                                </label>
                                <select id="jenis_pembayaran_id" name="jenis_pembayaran_id" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($jenis_pembayaran as $jenis)
                                        <option value="{{ $jenis->id }}"
                                            {{ $purchaseInvoice->jenis_pembayaran_id == $jenis->id ? 'selected' : '' }}>
                                            {{ $jenis->nama_jenis }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Payment Source Account -->
                            <div id="account-wrapper" class="hidden">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Rekening Sumber Bayar (COA) <span class="text-red-500">*</span>
                                </label>
                                <select id="account_id" name="payment_method_account_id" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih Account --</option>
                                </select>
                            </div>

                            <!-- Purchase Order Ref -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Referensi PO
                                </label>
                                <input type="hidden" name="purchase_order_id" value="{{ $purchaseInvoice->purchase_order_id }}">
                                <input type="text"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600 font-mono"
                                    value="{{ $purchaseInvoice->purchaseOrder->order_number ?? 'Tidak Ada PO' }}" readonly>
                            </div>

                            <!-- Shipping Address -->
                            <div class="md:col-span-4">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Alamat Pengiriman
                                </label>
                                <input type="text" name="shipping_address"
                                    placeholder="Alamat pengiriman / catatan gudang"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    value="{{ $purchaseInvoice->shipping_address }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 📦 CARD 2: RINCIAN BARANG PEMBELIAN (ORDER ITEMS) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50 border-b border-gray-200 flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-700">2. Rincian Barang & Nilai Pembelian</h2>
                        </div>
                        <span class="text-xs bg-emerald-100 text-emerald-800 font-semibold px-2.5 py-0.5 rounded-full">
                            {{ count($purchaseInvoice->details) }} Item Terdaftar
                        </span>
                    </div>

                    <div class="p-6">
                        <div class="overflow-x-auto border border-gray-200 rounded-xl shadow-inner">
                            <table class="min-w-full divide-y divide-gray-200 text-sm whitespace-nowrap">
                                <thead class="bg-slate-800 text-white text-xs font-semibold uppercase tracking-wider">
                                    <tr>
                                        <th class="px-3 py-3 text-left w-56">Item / Bahan Baku</th>
                                        <th class="px-2 py-3 text-center w-24">Qty Beli</th>
                                        <th class="px-2 py-3 text-center w-20">Order</th>
                                        <th class="px-2 py-3 text-center w-24">Satuan</th>
                                        <th class="px-3 py-3 text-left w-48">Deskripsi</th>
                                        <th class="px-3 py-3 text-right w-32">Harga Satuan (Rp)</th>
                                        <th class="px-2 py-3 text-right w-24">Diskon</th>
                                        <th class="px-2 py-3 text-center w-28">Pajak (PPN)</th>
                                        <th class="px-3 py-3 text-right w-28">Nilai PPN</th>
                                        <th class="px-3 py-3 text-right w-36">Subtotal (Rp)</th>
                                        <th class="px-3 py-3 text-left w-40">Alokasi Akun (COA)</th>
                                        <th class="px-2 py-3 text-center w-32">Specpose</th>
                                    </tr>
                                </thead>
                                <tbody id="item-table-body" class="bg-white divide-y divide-gray-100 text-xs">
                                    @foreach ($purchaseInvoice->details as $i => $detail)
                                        <tr class="hover:bg-gray-50 transition">
                                            <!-- Item -->
                                            <td class="p-2 border-b border-gray-200">
                                                <input type="hidden" name="items[{{ $i }}][item_id]" value="{{ $detail->item_id }}">
                                                <span class="font-medium text-gray-800">{{ $detail->item->item_description }}</span>
                                            </td>

                                            <!-- Qty -->
                                            <td class="p-2 border-b border-gray-200">
                                                <input type="number" name="items[{{ $i }}][quantity]"
                                                    class="qty-{{ $i }} w-full border border-gray-300 rounded text-xs px-2 py-1 text-center font-medium focus:ring-1 focus:ring-indigo-500"
                                                    value="{{ $detail->quantity }}">
                                            </td>

                                            <!-- Order -->
                                            <td class="p-2 border-b border-gray-200">
                                                <input type="number" name="items[{{ $i }}][order]"
                                                    class="order-{{ $i }} w-full border border-gray-200 bg-gray-100 rounded text-xs px-2 py-1 text-center text-gray-500"
                                                    value="{{ $detail->order }}" readonly>
                                            </td>

                                            <!-- Unit -->
                                            <td class="p-2 border-b border-gray-200">
                                                <input type="text" readonly
                                                    class="w-full border border-gray-200 bg-gray-100 rounded text-xs px-2 py-1 text-center font-medium text-gray-600"
                                                    name="items[{{ $i }}][unit]"
                                                    value="{{ $detail->unit }}">
                                            </td>

                                            <!-- Description -->
                                            <td class="p-2 border-b border-gray-200">
                                                <input type="text" readonly
                                                    class="w-full border border-gray-200 bg-gray-100 rounded text-xs px-2 py-1 text-gray-600"
                                                    name="items[{{ $i }}][item_description]"
                                                    value="{{ $detail->item_description }}">
                                            </td>

                                            <!-- Price -->
                                            <td class="p-2 border-b border-gray-200">
                                                <input type="text"
                                                    class="price-{{ $i }} w-full border border-gray-300 rounded text-xs px-2 py-1 text-right font-medium focus:ring-1 focus:ring-indigo-500"
                                                    name="items[{{ $i }}][price]"
                                                    value="{{ $detail->price }}">
                                            </td>

                                            <!-- Discount -->
                                            <td class="p-2 border-b border-gray-200">
                                                <input type="text"
                                                    class="discount-{{ $i }} w-full border border-gray-300 rounded text-xs px-2 py-1 text-right focus:ring-1 focus:ring-indigo-500"
                                                    name="items[{{ $i }}][discount]"
                                                    value="{{ $detail->discount }}">
                                            </td>

                                            <!-- Tax -->
                                            <td class="p-2 border-b border-gray-200">
                                                <select name="items[{{ $i }}][tax_id]"
                                                    class="w-full border border-gray-300 rounded text-xs px-1 py-1 bg-white tax-{{ $i }}">
                                                    <option value="">(Non-PPN)</option>
                                                    @foreach ($sales_taxes as $tax)
                                                        <option value="{{ $tax->id }}"
                                                            data-rate="{{ $tax->rate }}"
                                                            data-type="{{ $tax->type }}"
                                                            data-account="{{ $tax->purchase_account_id }}"
                                                            data-account-code="{{ $tax->purchaseAccount->kode_akun ?? '' }}"
                                                            data-account-name="{{ $tax->purchaseAccount->nama_akun ?? '' }}"
                                                            {{ $detail->tax_id == $tax->id ? 'selected' : '' }}>
                                                            {{ $tax->name }} ({{ $tax->rate }}%)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <!-- Tax Amount -->
                                            <td class="p-2 border-b border-gray-200">
                                                <input type="text" readonly
                                                    class="tax_amount-display-{{ $i }} w-full border border-gray-200 bg-gray-50 rounded text-xs px-2 py-1 text-right text-gray-700"
                                                    value="{{ number_format($detail->tax_amount, 0, ',', '.') }}">
                                                <input type="hidden" class="tax_amount-hidden"
                                                    name="items[{{ $i }}][tax_amount]"
                                                    value="{{ $detail->tax_amount }}">
                                            </td>

                                            <!-- Amount -->
                                            <td class="p-2 border-b border-gray-200">
                                                <input type="text" readonly
                                                    class="amount-display-{{ $i }} w-full border border-gray-200 bg-gray-50 rounded text-xs px-2 py-1 text-right font-bold text-gray-800"
                                                    value="{{ number_format($detail->amount, 0, ',', '.') }}">
                                                <input type="hidden" class="amount-hidden"
                                                    name="items[{{ $i }}][amount]"
                                                    value="{{ $detail->amount }}">
                                            </td>

                                            <!-- Account -->
                                            <td class="p-2 border-b border-gray-200">
                                                <input type="text" readonly
                                                    class="w-full border border-gray-200 rounded bg-gray-50 text-xs px-2 py-1 text-gray-700"
                                                    value="{{ optional($detail->account)->kode_akun }}-{{ optional($detail->account)->nama_akun }}">
                                                <input type="hidden" name="items[{{ $i }}][account_id]"
                                                    value="{{ $detail->account_id }}">
                                            </td>

                                            <!-- Specpose -->
                                            <td class="p-2 border-b border-gray-200">
                                                <select name="items[{{ $i }}][project_id]"
                                                    class="w-full border border-gray-300 rounded text-xs px-1 py-1 bg-white project-{{ $i }}">
                                                    <option value="">-- Specpose --</option>
                                                    @foreach ($project as $pro)
                                                        <option value="{{ $pro->id }}"
                                                            {{ $detail->project_id == $pro->id ? 'selected' : '' }}>
                                                            {{ $pro->nama_project }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 🏛️ CARD 3: KEPATUHAN CORETAX & LAMPIRAN DIGITAL FISIK (PILAR 1 & 3) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-700">3. Kepatuhan Pajak Coretax & Lampiran Digital Transaksi</h2>
                        </div>
                        <span class="text-xs bg-indigo-100 text-indigo-800 font-medium px-2.5 py-0.5 rounded-full">
                            Pilar Automasi 2026
                        </span>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Nomor Faktur Pajak -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                <label for="nomor_faktur_pajak" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                    Nomor Faktur Pajak Coretax (16 Digit)
                                </label>
                                <input type="text" id="nomor_faktur_pajak" name="nomor_faktur_pajak"
                                    placeholder="Cth: 010.000-26.12345678"
                                    value="{{ old('nomor_faktur_pajak', $purchaseInvoice->nomor_faktur_pajak ?? '') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                
                                <div class="mt-3 pt-2.5 border-t border-gray-200">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" id="is_non_faktur_pajak" name="is_non_faktur_pajak" value="1"
                                            {{ old('is_non_faktur_pajak', $purchaseInvoice->is_non_faktur_pajak ?? 0) ? 'checked' : '' }}
                                            onchange="toggleNonFakturWarning()"
                                            class="form-checkbox h-4 w-4 text-amber-600 rounded border-gray-300">
                                        <span class="ml-2 text-xs font-bold text-amber-800">Non-Faktur Pajak (Nota/Bon Manual)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Upload Foto Invoice Fisik -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                <label for="file_invoice" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                    Upload Bukti Invoice / Nota Baru
                                </label>
                                <input type="file" id="file_invoice" name="file_invoice" accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                <p class="text-2xs text-gray-500 mt-2">
                                    Format: JPG, PNG, PDF (Maks. 5MB). File akan ditambahkan ke daftar lampiran.
                                </p>
                            </div>

                            <!-- Upload Faktur Pajak Coretax -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                <label for="file_faktur_pajak" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                    Upload File PDF Faktur Pajak Coretax
                                </label>
                                <input type="file" id="file_faktur_pajak" name="file_faktur_pajak" accept=".pdf"
                                    class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                <p class="text-2xs text-gray-500 mt-2">
                                    File PDF resmi e-Faktur dari Coretax DJP.
                                </p>
                            </div>
                        </div>

                        <!-- Alert Pre-emptive Audit Pajak -->
                        <div id="non-faktur-alert" class="mt-4 p-3.5 bg-amber-50 border-l-4 border-amber-500 rounded-lg text-xs text-amber-900 {{ old('is_non_faktur_pajak', $purchaseInvoice->is_non_faktur_pajak ?? 0) ? '' : 'hidden' }}">
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-exclamation-triangle text-amber-600 mt-0.5 text-sm"></i>
                                <div>
                                    <span class="font-bold">PERINGATAN AUDIT PAJAK (PILAR 3):</span>
                                    Transaksi ini ditandai sebagai bukti sementara (Nota/Bon Non-Faktur). Berdasarkan SOP Pengadaan RCA 2026, bagian Purchasing wajib segera memintakan e-Faktur resmi dari vendor sebelum tutup masa pajak!
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 💰 CARD 4: CATATAN TRANSAKSI & RINGKASAN PEMBAYARAN -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Sisi Kiri: Catatan & Terms -->
                    <div class="lg:col-span-7 bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-600"></span>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-700">Catatan & Syarat Pembayaran</h2>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                Early Payment Terms / Syarat Pembayaran
                            </label>
                            <input type="text" name="early_payment_terms"
                                placeholder="Contoh: Net 30 / Transfer BCA"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                value="{{ $purchaseInvoice->early_payment_terms }}">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                Keterangan Tambahan / Pesan
                            </label>
                            <textarea name="messages" rows="3"
                                placeholder="Masukkan catatan pengadaan barang, kondisi fisik, atau nomor referensi internal"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ $purchaseInvoice->messages }}</textarea>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Financial Summary Card -->
                    <div class="lg:col-span-5 bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-3">
                        <div class="flex items-center space-x-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-700">Ringkasan Total Pembayaran</h2>
                        </div>

                        <!-- Subtotal -->
                        <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Subtotal Barang + PPN:</span>
                            <div class="w-44">
                                <input type="text" id="subtotal" readonly
                                    class="w-full border border-gray-200 rounded-lg text-right px-3 py-1.5 bg-gray-50 font-semibold text-gray-800 text-sm">
                            </div>
                        </div>

                        <!-- PPh Withholding -->
                        <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Potongan Pajak (PPh):</span>
                            <div class="w-44 flex space-x-1">
                                <select name="withholding_tax" id="global-tax"
                                    class="w-1/2 border border-gray-300 rounded-lg text-xs px-1.5 py-1 bg-white">
                                    <option value="">(None)</option>
                                    @foreach ($withholding as $tax)
                                        <option value="{{ $tax->id }}"
                                            data-rate="{{ $tax->rate }}"
                                            data-type="{{ $tax->type }}"
                                            data-account="{{ $tax->purchase_account_id }}"
                                            data-account-code="{{ $tax->purchaseAccount->kode_akun ?? '' }}"
                                            data-account-name="{{ $tax->purchaseAccount->nama_akun ?? '' }}"
                                            {{ $purchaseInvoice->withholding_tax == $tax->id ? 'selected' : '' }}>
                                            {{ $tax->rate }}%
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" id="global-tax-value" name="withholding_value" readonly
                                    class="w-1/2 border border-gray-200 rounded-lg text-right px-2 py-1 bg-gray-50 text-xs font-medium"
                                    value="{{ old('withholding_value', $purchaseInvoice->withholding_value ?? '') }}">
                            </div>
                        </div>

                        <!-- Freight / Ongkos Kirim -->
                        <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Ongkos Kirim (Freight):</span>
                            <div class="w-44">
                                <input type="hidden" id="freight" name="freight" value="{{ $purchaseInvoice->freight ?? 0 }}">
                                <input type="text" id="freight-display" placeholder="0.00"
                                    class="w-full border border-gray-300 rounded-lg text-right px-3 py-1.5 text-sm bg-white focus:ring-2 focus:ring-indigo-500"
                                    value="{{ number_format($purchaseInvoice->freight ?? 0, 0, '.', ',') }}">
                            </div>
                        </div>

                        <!-- Grand Total -->
                        <div class="flex items-center justify-between pt-3">
                            <span class="text-base font-bold text-gray-900">Total Tagihan (Rp):</span>
                            <div class="w-44">
                                <input type="text" id="grand-total" readonly
                                    class="w-full border-2 border-indigo-600 rounded-lg text-right px-3 py-2 bg-indigo-50 font-extrabold text-indigo-900 text-base shadow-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 🚀 ACTION BUTTONS -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('purchase_invoice.index') }}"
                        class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg shadow-sm hover:bg-gray-50 transition text-sm">
                        <i class="fas fa-times mr-1.5"></i> Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-lg shadow hover:bg-indigo-700 transition text-sm flex items-center">
                        <i class="fas fa-check-circle mr-2"></i> Simpan Perubahan
                    </button>
                </div>

            </div>

            <!-- 📑 PRATINJAU JURNAL OTOMATIS (TAB CONTENT) -->
            <div id="journal_report" class="tab-content hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-200">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 flex items-center">
                            <i class="fas fa-balance-scale mr-2 text-indigo-600"></i> Pratinjau Jurnal Otomatis (Live Simulation)
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Simulasi pencatatan debit & kredit di buku besar berdasarkan item, akun persediaan, PPN, dan kas/bank yang dipilih.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-slate-800 text-white text-xs font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Kode & Nama Akun (COA)</th>
                                <th class="px-4 py-3 text-right w-44">Debit (Rp)</th>
                                <th class="px-4 py-3 text-right w-44">Kredit (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="journal-body bg-white divide-y divide-gray-100 text-xs">
                            <tr>
                                <td colspan="3" class="text-center py-4 text-gray-400 italic">
                                    Memuat simulasi jurnal...
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50 font-bold text-xs">
                            <tr>
                                <td class="px-4 py-2.5 text-right uppercase tracking-wider text-gray-700">Total Seimbang:</td>
                                <td class="px-4 py-2.5 text-right total-debit text-emerald-700 font-mono">0.00</td>
                                <td class="px-4 py-2.5 text-right total-credit text-blue-700 font-mono">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </form>
    </div>

    <!-- JQUERY & SELECT2 ASSETS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#vendor_id').select2({
                placeholder: "-- Pilih Vendor --",
                ajax: {
                    url: '{{ route('vendors.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(vendor) {
                                return {
                                    id: vendor.id,
                                    text: vendor.nama_vendors
                                };
                            })
                        };
                    },
                    cache: true
                },
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    <script>
        // Tab switching
        document.querySelectorAll('.tab-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                document.querySelectorAll('.tab-link').forEach(el => {
                    el.classList.remove('active', 'bg-indigo-600', 'text-white', 'shadow-sm');
                    el.classList.add('text-gray-600');
                });
                document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));

                this.classList.add('active', 'bg-indigo-600', 'text-white', 'shadow-sm');
                this.classList.remove('text-gray-600');
                const target = document.querySelector(this.getAttribute('href'));
                target.classList.remove('hidden');
            });
        });

        function toggleNonFakturWarning() {
            const chk = document.getElementById('is_non_faktur_pajak');
            const alertBox = document.getElementById('non-faktur-alert');
            const noFakturInput = document.getElementById('nomor_faktur_pajak');
            if (chk && chk.checked) {
                alertBox.classList.remove('hidden');
                noFakturInput.placeholder = '(Non-Faktur / Bon Manual)';
            } else {
                alertBox.classList.add('hidden');
                noFakturInput.placeholder = 'Cth: 010.000-26.12345678';
            }
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const $pmSelect = $('#jenis_pembayaran_id');
            const $account = $('#account_id');
            const $wrapper = $('#account-wrapper');

            const freightAccount = {
                id: "{{ $freightAccount->akun_id ?? '' }}",
                kode: "{{ $freightAccount->akun->kode_akun ?? '-' }}",
                name: "{{ $freightAccount->akun->nama_akun ?? 'Freight Expense' }}"
            };

            function formatNumber(num) {
                return Number(num).toLocaleString('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }

            function clearAccounts() {
                $account.empty().append('<option value="">-- Pilih Account --</option>');
                $wrapper.addClass('hidden');
            }

            function loadAccounts(pmId) {
                if (!pmId) {
                    clearAccounts();
                    return;
                }

                $.getJSON("{{ route('payment-methods.accounts', ['id' => 'PM_ID']) }}".replace('PM_ID', pmId))
                    .done(function(res) {
                        clearAccounts();
                        (res.accounts || []).forEach(function(a) {
                            const text = `${a.kode_akun || '-'} - ${a.nama_akun || '-'}`;
                            $account.append(`<option value="${a.detail_id}">${text}</option>`);
                        });

                        const oldVal = "{{ old('payment_method_account_id', $purchaseInvoice->payment_method_account_id ?? '') }}";
                        if (oldVal) {
                            $account.val(oldVal);
                        }

                        $wrapper.removeClass('hidden');
                        generateJournalPreview();
                    })
                    .fail(function() {
                        clearAccounts();
                    });
            }

            $pmSelect.on('change', function() {
                loadAccounts($(this).val());
            });

            if ($pmSelect.val()) {
                loadAccounts($pmSelect.val());
            }

            function calculateBackOrder(index) {
                const qty = parseFloat($(`.qty-${index}`).val()) || 0;
                const order = parseFloat($(`.order-${index}`).val()) || 0;
            }

            function calculateAmount(index) {
                const qty = parseFloat($(`.qty-${index}`).val()) || 0;
                const price = parseFloat($(`.price-${index}`).val()) || 0;
                const discount = parseFloat($(`.discount-${index}`).val()) || 0;
                const taxSelect = document.querySelector(`.tax-${index}`);
                const taxPercent = parseFloat($(`.tax-${index} option:selected`).data('rate')) || 0;
                const taxType = taxSelect?.selectedOptions[0]?.dataset.type || 'input_tax';
                const baseAmount = (price - discount) * qty;
                let taxAmount = (baseAmount * taxPercent) / 100;
                let finalValue = baseAmount;

                if (taxType === 'input_tax') {
                    finalValue += taxAmount;
                } else if (taxType === 'withholding_tax') {
                    finalValue -= taxAmount;
                    if (finalValue < 0) finalValue = 0;
                }

                $(`.amount-display-${index}`).val(formatNumber(baseAmount));
                $(`.tax_amount-display-${index}`).val(formatNumber(taxAmount));

                $(`.amount-hidden[name="items[${index}][amount]"]`).val(baseAmount);
                $(`.tax_amount-hidden[name="items[${index}][tax_amount]"]`).val(taxAmount);

                let $finalHidden = $(`.final-hidden-${index}`);
                if ($finalHidden.length === 0) {
                    $(`.amount-hidden[name="items[${index}][amount]"]`).after(
                        `<input type="hidden" class="final-hidden-${index} final-hidden" value="${finalValue}">`
                    );
                } else {
                    $finalHidden.val(finalValue);
                }

                calculateTotals();
                generateJournalPreview();
            }

            function parseNumber(val) {
                return parseFloat(String(val).replace(/,/g, '')) || 0;
            }

            function calculateTotals() {
                let subtotal = 0;
                let totalTax = 0;

                $('.final-hidden').each(function() {
                    subtotal += parseNumber($(this).val());
                });

                $('.tax_amount-hidden').each(function() {
                    totalTax += parseNumber($(this).val());
                });

                const globalTaxSelect = document.getElementById('global-tax');
                const globalTaxRate = parseNumber(globalTaxSelect?.selectedOptions[0]?.dataset.rate);
                const withholdingValue = subtotal * (globalTaxRate / 100);
                $('#global-tax-value').val(formatNumber(withholdingValue));

                const freight = parseNumber($('#freight').val());
                const grandTotal = subtotal - withholdingValue + freight;

                $('#subtotal').val(formatNumber(subtotal));
                $('#grand-total').val(formatNumber(grandTotal));
                $('#freight-display').val(formatNumber(freight));
            }

            const globalTaxSelectEl = document.getElementById('global-tax');
            if (globalTaxSelectEl) {
                globalTaxSelectEl.addEventListener('change', calculateTotals);
            }

            function generateJournalPreview() {
                const $journalBody = $('.journal-body');
                $journalBody.empty();

                let totalDebit = 0,
                    totalCredit = 0,
                    rows = [];

                $('tbody#item-table-body tr').each(function() {
                    const amount = parseFloat($(this).find('.amount-hidden').val()) || 0;
                    const taxAmount = parseFloat($(this).find('.tax_amount-hidden').val()) || 0;
                    const accountName = $(this).find('input[name$="[account_id]"]').siblings('input[type=text]').val();

                    const $taxSelect = $(this).find('select[name$="[tax_id]"]');
                    const $taxOption = $taxSelect.find('option:selected');
                    const taxAccountName = $taxOption.data('account-name') || $taxOption.text();
                    const taxAccountCode = $taxOption.data('account-code') || '';
                    const taxType = $taxOption.data('type') || 'input_tax';

                    if (amount > 0) {
                        rows.push({
                            accountCode: taxAccountCode,
                            account: accountName,
                            debit: amount,
                            credit: 0
                        });
                        totalDebit += amount;
                    }

                    if (taxAmount > 0) {
                        if (taxType === 'input_tax') {
                            rows.push({
                                accountCode: taxAccountCode,
                                account: `${taxAccountCode}-${taxAccountName}`,
                                debit: taxAmount,
                                credit: 0
                            });
                            totalDebit += taxAmount;
                        } else if (taxType === 'withholding_tax') {
                            rows.push({
                                accountCode: taxAccountCode,
                                account: `${taxAccountCode}-${taxAccountName}`,
                                debit: 0,
                                credit: taxAmount
                            });
                            totalCredit += taxAmount;
                        }
                    }
                });

                const freight = parseFloat($('#freight').val()) || 0;
                if (freight > 0) {
                    rows.push({
                        account: `${freightAccount.kode} - ${freightAccount.name}`,
                        debit: freight,
                        credit: 0,
                        account_id: freightAccount.id
                    });
                    totalDebit += freight;
                }

                const globalTaxSelect = document.getElementById('global-tax');
                const withholdingRate = parseNumber(globalTaxSelect?.selectedOptions[0]?.dataset.rate);
                const withholdingValue = parseNumber(document.getElementById('global-tax-value')?.value);
                if (withholdingValue > 0) {
                    const withholdingAccountName = globalTaxSelect?.selectedOptions[0]?.dataset.accountName || 'PPh Dipotong';
                    const withholdingAccountCode = globalTaxSelect?.selectedOptions[0]?.dataset.accountCode || 'PPH';

                    rows.push({
                        account: `${withholdingAccountCode} - ${withholdingAccountName}`,
                        debit: 0,
                        credit: withholdingValue
                    });
                    totalCredit += withholdingValue;
                }

                const paymentAccount = $('#account_id option:selected').text();
                const grandTotal = parseFloat($('#grand-total').val().replace(/,/g, '')) || 0;
                if (grandTotal > 0 && paymentAccount && paymentAccount !== '-- Pilih Account --') {
                    rows.push({
                        account: paymentAccount,
                        debit: 0,
                        credit: grandTotal
                    });
                    totalCredit += grandTotal;
                }

                if (rows.length === 0) {
                    $journalBody.append(
                        '<tr><td colspan="3" class="text-center py-4 text-gray-400 italic">Memuat simulasi jurnal...</td></tr>'
                    );
                } else {
                    rows.forEach(r => {
                        $journalBody.append(`
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-2 text-gray-800 font-medium">${r.account}</td>
                                <td class="px-4 py-2 text-right font-mono text-emerald-700">${r.debit.toLocaleString()}</td>
                                <td class="px-4 py-2 text-right font-mono text-blue-700">${r.credit.toLocaleString()}</td>
                            </tr>
                        `);
                    });
                }

                $('.total-debit').text(totalDebit.toLocaleString());
                $('.total-credit').text(totalCredit.toLocaleString());
            }

            $(document).on('input', 'input[name^="items"][name$="[quantity]"], input[name^="items"][name$="[order]"], input[name^="items"][name$="[price]"], input[name^="items"][name$="[discount]"]', function() {
                const index = $(this).closest('tr').index();
                calculateBackOrder(index);
                calculateAmount(index);
            });

            $(document).on('change', 'select[name^="items"][name$="[tax_id]"]', function() {
                const index = $(this).closest('tr').index();
                calculateAmount(index);
            });

            $('#freight-display').on('input', function() {
                const val = this.value.replace(/,/g, '');
                const num = parseFloat(val) || 0;
                $('#freight').val(num);
                calculateTotals();
                generateJournalPreview();
            });

            $('#account_id').on('change', function() {
                generateJournalPreview();
            });

            @foreach ($purchaseInvoice->details as $i => $detail)
                calculateAmount({{ $i }});
            @endforeach
            calculateTotals();
            generateJournalPreview();
        });
    </script>
@endsection

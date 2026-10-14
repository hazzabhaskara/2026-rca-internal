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
                    <i class="fas fa-file-invoice mr-3 text-indigo-600"></i>
                    {{ isset($purchase_invoice) ? 'Edit Purchase Invoice' : 'Tambah Purchase Invoice' }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Kelola pengadaan barang persediaan, kepatuhan e-Faktur Coretax, dan mutasi stok secara terintegrasi.
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

        <form method="POST" enctype="multipart/form-data"
            action="{{ isset($purchase_invoice) ? route('purchase_invoice.update', $purchase_invoice->id) : route('purchase_invoice.store') }}"
            class="space-y-6">
            @csrf
            @if (isset($purchase_invoice))
                @method('PUT')
            @endif

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
                    <div class="px-6 py-4 bg-slate-50 border-b border-gray-200 flex flex-wrap justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-700">1. Informasi Dokumen & Rekanan</h2>
                        </div>
                        <div class="flex items-center space-x-3 text-sm">
                            <label class="inline-flex items-center cursor-pointer select-none text-gray-700 font-medium">
                                <input type="checkbox" id="use-order-number" class="form-checkbox h-4 w-4 text-indigo-600 rounded border-gray-300 mr-2">
                                Tarik Data dari Purchase Order (PO)
                            </label>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Baris PO Wrapper jika dicentang -->
                        <div id="order-number-wrapper" class="hidden mb-5 p-3.5 bg-indigo-50 border border-indigo-200 rounded-lg">
                            <label for="purchase_order_id" class="block text-xs font-bold uppercase tracking-wider text-indigo-900 mb-1">
                                Pilih Nomor Purchase Order (PO)
                            </label>
                            <select name="purchase_order_id"
                                class="w-full md:w-1/2 border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Pilih Purchase Order --</option>
                                @foreach ($purchase_order as $data)
                                    <option value="{{ $data->id }}"
                                        {{ old('purchase_order_id', $purchase_invoice->purchase_order_id ?? '') == $data->id ? 'selected' : '' }}>
                                        {{ $data->order_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Grid Utama Input Header -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                            <!-- Invoice Number -->
                            <div>
                                <label for="invoice_number" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Invoice Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="invoice_number" name="invoice_number"
                                    placeholder="Contoh: 11029146"
                                    value="{{ old('invoice_number', $purchase_invoice->invoice_number ?? '') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                
                                <div class="mt-1.5 flex items-center">
                                    <input type="checkbox" id="auto_generate" name="auto_generate" value="1"
                                        class="h-3.5 w-3.5 text-indigo-600 rounded border-gray-300" onchange="toggleAutoGenerate()">
                                    <label for="auto_generate" class="ml-1.5 text-xs text-gray-600 cursor-pointer select-none">
                                        Auto-generate nomor
                                    </label>
                                </div>
                            </div>

                            <!-- Invoice Date -->
                            <div>
                                <label for="date_invoice" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Tanggal Invoice <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="date_invoice" name="date_invoice" required
                                    value="{{ old('date_invoice', $purchase_invoice->date_invoice ?? now()->toDateString()) }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Shipping Date -->
                            <div>
                                <label for="shipping_date" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Tanggal Kirim / Tiba
                                </label>
                                <input type="date" id="shipping_date" name="shipping_date"
                                    value="{{ old('shipping_date', $purchase_invoice->shipping_date ?? now()->toDateString()) }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Location Inventory -->
                            <div>
                                <label for="location_id" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Gudang Penyimpanan <span class="text-red-500">*</span>
                                </label>
                                <select name="location_id" id="location_id" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih Lokasi Gudang --</option>
                                    @foreach ($lokasi_inventory as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('location_id', $purchase_invoice->location_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->kode_lokasi }} ({{ $level->nama_lokasi ?? 'Gudang' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Vendor -->
                            <div>
                                <label for="vendor_id" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Nama Vendor / Supplier <span class="text-red-500">*</span>
                                </label>
                                <select name="vendor_id" id="vendor_id" required
                                    class="w-full border border-gray-300 rounded-lg text-sm">
                                    <option value="">-- Pilih Vendor --</option>
                                    @foreach ($vendor as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('vendor_id', $purchase_invoice->vendor_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->nama_vendors }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label for="jenis_pembayaran_id" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Metode Pembayaran <span class="text-red-500">*</span>
                                </label>
                                <select id="jenis_pembayaran_id" name="jenis_pembayaran_id" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih Metode --</option>
                                    @foreach ($jenis_pembayaran as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('jenis_pembayaran_id', $purchase_invoice->jenis_pembayaran_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->nama_jenis }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Payment Source Account -->
                            <div id="pm-account-panel" class="{{ old('jenis_pembayaran_id', $purchase_invoice->jenis_pembayaran_id ?? '') ? '' : 'hidden' }}">
                                <label for="pm-account-id" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Rekening Sumber Bayar (COA) <span class="text-red-500">*</span>
                                </label>
                                <select id="pm-account-id" name="header_account_id" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Pilih Rekening Bayar --</option>
                                </select>
                            </div>

                            <!-- Shipping Address -->
                            <div class="md:col-span-1">
                                <label for="shipping_address" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                    Alamat Pengiriman (Opsional)
                                </label>
                                <input type="text" id="shipping_address" name="shipping_address"
                                    placeholder="Alamat gudang/wahana tujuan"
                                    value="{{ old('shipping_address', $purchase_invoice->shipping_address ?? '') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 📦 CARD 2: RINCIAN BARANG PEMBELIAN (ORDER ITEMS) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50 border-b border-gray-200 flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-700">2. Rincian Barang & Kalkulasi Nilai Beli</h2>
                        </div>
                        <button type="button" id="add-row"
                            class="inline-flex items-center px-3.5 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-lg shadow-sm hover:bg-emerald-700 transition">
                            <i class="fas fa-plus mr-1.5"></i> Tambah Baris Barang
                        </button>
                    </div>

                    <div class="p-6">
                        <div class="overflow-x-auto border border-gray-200 rounded-xl shadow-inner">
                            <table class="min-w-full divide-y divide-gray-200 text-sm whitespace-nowrap">
                                <thead class="bg-slate-800 text-white text-xs font-semibold uppercase tracking-wider">
                                    <tr>
                                        <th class="px-3 py-3 text-left w-56">Item / Bahan Baku</th>
                                        <th class="px-2 py-3 text-center w-24">Qty Beli</th>
                                        <th class="px-2 py-3 text-center w-20">Order</th>
                                        <th class="px-2 py-3 text-center w-20">Back Order</th>
                                        <th class="px-2 py-3 text-center w-24">Satuan</th>
                                        <th class="px-3 py-3 text-left w-48">Deskripsi</th>
                                        <th class="px-3 py-3 text-right w-32">Harga Satuan (Rp)</th>
                                        <th class="px-2 py-3 text-right w-24">Diskon</th>
                                        <th class="px-2 py-3 text-center w-28">Pajak (PPN)</th>
                                        <th class="px-3 py-3 text-right w-28">Nilai PPN</th>
                                        <th class="px-3 py-3 text-right w-36">Subtotal (Rp)</th>
                                        <th class="px-3 py-3 text-left w-40">Alokasi Akun (COA)</th>
                                        <th class="px-2 py-3 text-center w-32">Specpose</th>
                                        <th class="px-2 py-3 text-center w-12"><i class="fas fa-trash-alt"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="items-body" class="bg-white divide-y divide-gray-100 text-xs">
                                    <!-- Diisi lewat JavaScript -->
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
                                    value="{{ old('nomor_faktur_pajak', $purchase_invoice->nomor_faktur_pajak ?? '') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                
                                <div class="mt-3 pt-2.5 border-t border-gray-200">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" id="is_non_faktur_pajak" name="is_non_faktur_pajak" value="1"
                                            {{ old('is_non_faktur_pajak', $purchase_invoice->is_non_faktur_pajak ?? 0) ? 'checked' : '' }}
                                            onchange="toggleNonFakturWarning()"
                                            class="form-checkbox h-4 w-4 text-amber-600 rounded border-gray-300">
                                        <span class="ml-2 text-xs font-bold text-amber-800">Non-Faktur Pajak (Nota/Bon Manual)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Upload Foto Invoice Fisik -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                <label for="file_invoice" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                                    Upload Foto Invoice / Nota Fisik
                                </label>
                                <input type="file" id="file_invoice" name="file_invoice" accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                <p class="text-2xs text-gray-500 mt-2">
                                    Format: JPG, PNG, PDF (Maks. 5MB). Lampiran langsung ditautkan ke voucher jurnal.
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
                                    File PDF resmi e-Faktur dari Coretax DJP untuk bukti verifikasi PPN Masukan.
                                </p>
                            </div>
                        </div>

                        <!-- Alert Pre-emptive Audit Pajak -->
                        <div id="non-faktur-alert" class="mt-4 p-3.5 bg-amber-50 border-l-4 border-amber-500 rounded-lg text-xs text-amber-900 {{ old('is_non_faktur_pajak', $purchase_invoice->is_non_faktur_pajak ?? 0) ? '' : 'hidden' }}">
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
                            <label for="early_payment_terms" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                Early Payment Terms / Syarat Pembayaran
                            </label>
                            <input type="text" id="early_payment_terms" name="early_payment_terms"
                                placeholder="Contoh: Net 30 / Transfer BCA"
                                value="{{ old('early_payment_terms', $purchase_invoice->early_payment_terms ?? '') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="messages" class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">
                                Keterangan Tambahan / Pesan
                            </label>
                            <textarea id="messages" name="messages" rows="3"
                                placeholder="Masukkan catatan pengadaan barang, kondisi fisik, atau nomor referensi internal"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('messages', $purchase_invoice->messages ?? '') }}</textarea>
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
                                    @foreach ($withholding as $item)
                                        <option value="{{ $item->id }}"
                                            data-rate="{{ $item->rate }}"
                                            data-type="{{ $item->type }}"
                                            data-account="{{ $item->purchase_account_id }}"
                                            data-account-code="{{ $item->purchaseAccount->kode_akun ?? '' }}"
                                            data-account-name="{{ $item->purchaseAccount->nama_akun ?? '' }}">
                                            {{ $item->rate }}%
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" id="global-tax-value" name="withholding_value" readonly
                                    class="w-1/2 border border-gray-200 rounded-lg text-right px-2 py-1 bg-gray-50 text-xs font-medium">
                            </div>
                        </div>

                        <!-- Freight / Ongkos Kirim -->
                        <div class="flex items-center justify-between text-sm py-1.5 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Ongkos Kirim (Freight):</span>
                            <div class="w-44">
                                <input type="hidden" id="freight" name="freight" value="0">
                                <input type="text" id="freight-display" placeholder="0.00"
                                    class="w-full border border-gray-300 rounded-lg text-right px-3 py-1.5 text-sm bg-white focus:ring-2 focus:ring-indigo-500">
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
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ isset($purchase_invoice) ? 'Simpan Perubahan' : 'Proses & Simpan Invoice' }}
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
                                    Tambahkan item barang untuk melihat simulasi jurnal...
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
            // Select2 untuk Vendor
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

        // Non-faktur warning toggle
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
        // Payment Method & Account Handler
        (function() {
            const $pmSelect = $('#jenis_pembayaran_id');
            const $panel = $('#pm-account-panel');
            const $select = $('#pm-account-id');

            function clearAccount() {
                $select.empty().append('<option value="">-- Pilih Rekening Bayar --</option>');
                $panel.addClass('hidden');
            }

            function loadPMAccounts(pmId) {
                if (!pmId) {
                    clearAccount();
                    return;
                }

                $.getJSON("{{ route('payment-methods.accounts', ['id' => 'PM_ID']) }}".replace('PM_ID', pmId))
                    .done(function(res) {
                        $select.empty().append('<option value="">-- Pilih Rekening Bayar --</option>');

                        (res.accounts || []).forEach(function(a) {
                            const text = `${a.kode_akun || '-'} - ${a.nama_akun || '-'}`;
                            $select.append(`<option value="${a.detail_id}">${text}</option>`);
                        });

                        const oldVal = "{{ old('payment_method_account_id', $purchase_invoice->payment_method_account_id ?? '') }}";
                        if (oldVal) $select.val(oldVal);

                        $panel.removeClass('hidden');
                    })
                    .fail(function() {
                        clearAccount();
                    });
            }

            $pmSelect.on('change', function() {
                loadPMAccounts($(this).val());
            });

            const initial = $pmSelect.val();
            if (initial) loadPMAccounts(initial);
        })();
    </script>

    <script>
        function toggleAutoGenerate() {
            const checkbox = document.getElementById('auto_generate');
            const invoiceInput = document.getElementById('invoice_number');

            if (checkbox && checkbox.checked) {
                invoiceInput.readOnly = true;
                invoiceInput.value = 'Auto-generated';
                invoiceInput.classList.add('bg-gray-100', 'text-gray-500');
            } else if (invoiceInput) {
                invoiceInput.readOnly = false;
                if (invoiceInput.value === 'Auto-generated') invoiceInput.value = '';
                invoiceInput.classList.remove('bg-gray-100', 'text-gray-500');
            }
        }

        window.onload = function() {
            toggleAutoGenerate();
        };
    </script>

    <script>
        // Order Items & Calculations Script
        document.addEventListener('DOMContentLoaded', function() {
            const useOrderCheckbox = document.getElementById('use-order-number');
            const orderWrapper = document.getElementById('order-number-wrapper');
            const selectOrder = document.querySelector('select[name="purchase_order_id"]');
            const tbody = document.getElementById('items-body');
            let rowIndex = 0;

            const freightAccount = {
                id: "{{ $freightAccount->akun_id ?? '' }}",
                kode: "{{ $freightAccount->akun->kode_akun ?? '-' }}",
                name: "{{ $freightAccount->akun->nama_akun ?? 'Freight Expense' }}"
            };

            const formatNumber = num => new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);

            useOrderCheckbox.addEventListener('change', function() {
                tbody.innerHTML = '';
                if (this.checked) {
                    orderWrapper.classList.remove('hidden');
                } else {
                    orderWrapper.classList.add('hidden');
                    addEmptyRow();
                }
            });

            selectOrder.addEventListener('change', function() {
                const orderId = this.value;
                tbody.innerHTML = '';
                if (!orderId) return;

                fetch(`/purchase_invoice/get-items/${orderId}`)
                    .then(res => res.json())
                    .then(data => {
                        data.items.forEach(item => {
                            addRowFromPO(item);
                        });
                        calculateTotals();
                        generateJournalPreview();
                    });
            });

            function addEmptyRow() {
                const index = rowIndex++;
                const row = `
                <tr class="item-row hover:bg-gray-50 transition" data-index="${index}">
                    <td class="p-2 border-b border-gray-200">
                        <select name="items[${index}][item_id]" class="item-select w-full border border-gray-300 rounded text-xs" data-index="${index}"></select>
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="number" name="items[${index}][quantity]" class="qty-${index} w-full border border-gray-300 rounded text-xs px-2 py-1 text-center font-medium focus:ring-1 focus:ring-indigo-500" />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="number" name="items[${index}][order]" class="w-full border border-gray-200 bg-gray-100 rounded text-xs px-2 py-1 text-center text-gray-500" readonly />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="number" name="items[${index}][back_order]" class="w-full border border-gray-200 bg-gray-100 rounded text-xs px-2 py-1 text-center text-gray-500" readonly />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="text" name="items[${index}][unit]" class="unit-${index} w-full border border-gray-200 bg-gray-100 rounded text-xs px-2 py-1 text-center font-medium text-gray-600" readonly />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="text" name="items[${index}][item_description]" class="desc-${index} w-full border border-gray-200 bg-gray-100 rounded text-xs px-2 py-1 text-gray-600" readonly />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="number" step="0.01" name="items[${index}][price]" class="purchase-${index} w-full border border-gray-300 rounded text-xs px-2 py-1 text-right font-medium focus:ring-1 focus:ring-indigo-500" />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="number" step="0.01" name="items[${index}][discount]" class="disc-${index} w-full border border-gray-300 rounded text-xs px-2 py-1 text-right focus:ring-1 focus:ring-indigo-500" />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <select name="items[${index}][tax_id]" class="tax-${index} w-full border border-gray-300 rounded text-xs px-1 py-1 bg-white">
                            <option value="">(Non-PPN)</option>
                            @foreach ($sales_taxes as $item)
                                <option value="{{ $item->id }}" 
                                        data-rate="{{ $item->rate }}" 
                                        data-type="{{ $item->type }}"
                                        data-account="{{ $item->purchase_account_id }}"  
                                        data-account-code="{{ $item->purchaseAccount->kode_akun ?? '' }}"
                                        data-account-name="{{ $item->purchaseAccount->nama_akun ?? '' }}">
                                    {{ $item->name }} ({{ $item->rate }}%)
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="text" name="items[${index}][tax_amount_display]" class="tax_amount-${index} w-full border border-gray-200 bg-gray-50 rounded text-xs px-2 py-1 text-right text-gray-700" readonly />
                        <input type="hidden" name="items[${index}][tax_amount]" class="tax_amount_raw-${index}" />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="text" name="items[${index}][amount_display]" class="amount-${index} w-full border border-gray-200 bg-gray-50 rounded text-xs px-2 py-1 text-right font-bold text-gray-800" readonly />
                        <input type="hidden" name="items[${index}][amount]" class="amount_raw-${index}" />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="text" class="w-full border border-gray-200 rounded bg-gray-50 text-xs px-2 py-1 text-gray-700 account-name-${index}" readonly />
                        <input type="hidden" name="items[${index}][account_id]" class="account-id-${index}" />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <select name="items[${index}][project_id]" class="w-full border border-gray-300 rounded text-xs px-1 py-1 bg-white">
                            <option value="">-- Specpose --</option>
                            @foreach ($project as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_project }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="p-2 border-b border-gray-200 text-center">
                        <button type="button" class="remove-row p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition" data-index="${index}">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </td>
                </tr>`;
                tbody.insertAdjacentHTML('beforeend', row);
                attachSelect2(index);
            }

            function addRowFromPO(item) {
                const index = rowIndex++;
                const row = `
                <tr class="item-row hover:bg-gray-50 transition" data-index="${index}">
                    <td class="p-2 border-b border-gray-200">
                        <input type="hidden" name="items[${index}][item_id]" value="${item.id}">
                        <span class="font-medium text-gray-800">${item.item_number}</span>
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="number" name="items[${index}][quantity]" value="${item.quantity}" class="qty-${index} w-full border border-gray-300 rounded text-xs px-2 py-1 text-center font-medium focus:ring-1 focus:ring-indigo-500" />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="number" name="items[${index}][order]" value="${item.order}" class="order-${index} w-full border border-gray-200 bg-gray-100 rounded text-xs px-2 py-1 text-center text-gray-500" readonly/>
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="number" name="items[${index}][back_order]" value="${item.back_order}" class="back-${index} w-full border border-gray-200 bg-gray-100 rounded text-xs px-2 py-1 text-center text-gray-500" readonly />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="text" name="items[${index}][unit]" value="${item.unit}" class="unit-${index} w-full border border-gray-200 bg-gray-100 rounded text-xs px-2 py-1 text-center font-medium text-gray-600" readonly/>
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="text" name="items[${index}][item_description]" value="${item.description}" class="desc-${index} w-full border border-gray-200 bg-gray-100 rounded text-xs px-2 py-1 text-gray-600" readonly />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="number" step="0.01" name="items[${index}][price]" value="${item.price}" class="purchase-${index} w-full border border-gray-300 rounded text-xs px-2 py-1 text-right font-medium focus:ring-1 focus:ring-indigo-500" />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="number" step="0.01" name="items[${index}][discount]" value="${item.discount}" class="disc-${index} w-full border border-gray-300 rounded text-xs px-2 py-1 text-right focus:ring-1 focus:ring-indigo-500" />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <select name="items[${index}][tax_id]" class="tax-${index} w-full border border-gray-300 rounded text-xs px-1 py-1 bg-white">
                            <option value="">(Non-PPN)</option>
                            @foreach ($sales_taxes as $tax)
                                <option value="{{ $tax->id }}" ${item.tax_id == {{ $tax->id }} ? 'selected' : ''} 
                                        data-rate="{{ $tax->rate }}" 
                                        data-type="{{ $tax->type }}"
                                        data-account="{{ $tax->purchase_account_id }}"  
                                        data-account-code="{{ $tax->purchaseAccount->kode_akun ?? '' }}"
                                        data-account-name="{{ $tax->purchaseAccount->nama_akun ?? '' }}">
                                    {{ $tax->name }} ({{ $tax->rate }}%)
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="text" name="items[${index}][tax_amount_display]" value="${formatNumber(item.tax_amount)}" class="tax_amount-${index} w-full border border-gray-200 bg-gray-50 rounded text-xs px-2 py-1 text-right text-gray-700" readonly />
                        <input type="hidden" name="items[${index}][tax_amount]" value="${item.tax_amount}" class="tax_amount_raw-${index}" />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="text" name="items[${index}][amount_display]" value="${formatNumber(item.amount)}" class="amount-${index} w-full border border-gray-200 bg-gray-50 rounded text-xs px-2 py-1 text-right font-bold text-gray-800" readonly />
                        <input type="hidden" name="items[${index}][amount]" value="${item.amount}" class="amount_raw-${index}" />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <input type="text" value="${item.account_code}-${item.account_name}" class="w-full border border-gray-200 rounded bg-gray-50 text-xs px-2 py-1 text-gray-700 account-name-${index}" readonly />
                        <input type="hidden" name="items[${index}][account_id]" value="${item.account_id}" />
                    </td>
                    <td class="p-2 border-b border-gray-200">
                        <select name="items[${index}][project_id]" class="w-full border border-gray-300 rounded text-xs px-1 py-1 bg-white">
                            <option value="">-- Specpose --</option>
                            @foreach ($project as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_project }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="p-2 border-b border-gray-200 text-center">
                        <button type="button" class="remove-row p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition" data-index="${index}">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </td>
                </tr>`;
                tbody.insertAdjacentHTML('beforeend', row);
                calculateBackOrder(index);
                calculateAmount(index);
            }

            function attachSelect2(index) {
                $(`select[data-index="${index}"]`).select2({
                    placeholder: 'Pilih / Cari Item...',
                    ajax: {
                        url: '/search-item',
                        dataType: 'json',
                        delay: 250,
                        data: params => ({
                            q: params.term,
                            context: 'purchase',
                            location_id: $('#location_id').val()
                        }),
                        processResults: data => ({
                            results: data.map(item => ({
                                id: item.id,
                                text: `${item.item_number} - ${item.item_description}`,
                                item_description: item.item_description,
                                unit: item.unit,
                                purchase_price: item.purchase_price ?? 0,
                                tax_rate: item.tax_rate,
                                discount: item.discount,
                                account_id: item.account_id,
                                account_name: item.account_name,
                                account_code: item.account_code
                            }))
                        }),
                        cache: true
                    },
                    width: '100%'
                }).on('select2:select', function(e) {
                    const data = e.params.data;
                    $(`.desc-${index}`).val(data.item_description);
                    $(`.unit-${index}`).val(data.unit);
                    $(`.purchase-${index}`).val(data.purchase_price);
                    $(`.account-name-${index}`).val(`${data.account_code} - ${data.account_name}`);
                    $(`.account-id-${index}`).val(data.account_id);
                    calculateAmount(index);
                });
            }

            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                calculateTotals();
                generateJournalPreview();
            });

            function calculateBackOrder(index) {
                const qty = parseFloat($(`.qty-${index}`).val()) || 0;
                const order = parseFloat($(`.order-${index}`).val()) || 0;
                const backOrder = qty - order;
                $(`.back-${index}`).val(backOrder >= 0 ? backOrder : 0);
            }

            function calculateAmount(index) {
                const qty = parseFloat($(`.qty-${index}`).val()) || 0;
                const price = parseFloat($(`.purchase-${index}`).val()) || 0;
                const discount = parseFloat($(`.disc-${index}`).val()) || 0;
                const taxRate = parseFloat($(`.tax-${index} option:selected`).data('rate')) || 0;
                const taxType = $(`.tax-${index} option:selected`).data('type') || 'input_tax';

                const baseAmount = (price - discount) * qty;

                let taxAmount = baseAmount * (taxRate / 100);
                if (taxType === 'withholding_tax') {
                    taxAmount = -Math.abs(taxAmount);
                }

                $(`.amount_raw-${index}`).val(baseAmount.toFixed(2));
                $(`.tax_amount_raw-${index}`).val(taxAmount.toFixed(2));

                $(`.amount-${index}`).val(formatNumber(baseAmount));
                $(`.tax_amount-${index}`).val(formatNumber(Math.abs(taxAmount)));

                calculateTotals();
                generateJournalPreview();
            }

            function parseNumber(val) {
                return parseFloat(String(val).replace(/,/g, '')) || 0;
            }

            function calculateTotals() {
                let subtotal = 0;
                let totalTax = 0;

                $('tr.item-row').each(function() {
                    const index = $(this).data('index');
                    const base = parseFloat($(`.amount_raw-${index}`).val()) || 0;
                    const tax = parseFloat($(`.tax_amount_raw-${index}`).val()) || 0;

                    const final = base + tax;
                    subtotal += final;
                    totalTax += tax;
                });

                const globalTaxSelect = document.getElementById('global-tax');
                const globalTaxRate = parseNumber(globalTaxSelect?.selectedOptions[0]?.dataset.rate);
                const withholdingValueInput = document.getElementById('global-tax-value');

                const withholdingValue = subtotal * (globalTaxRate / 100);
                if (withholdingValueInput) {
                    withholdingValueInput.value = formatNumber(withholdingValue);
                }

                const freight = parseFloat($('#freight').val()) || 0;
                const grandTotal = subtotal - withholdingValue + freight;

                $('#subtotal').val(formatNumber(subtotal));
                $('#grand-total').val(formatNumber(grandTotal));

                generateJournalPreview();
            }

            const globalTaxEl = document.getElementById('global-tax');
            if (globalTaxEl) {
                globalTaxEl.addEventListener('change', calculateTotals);
            }

            function generateJournalPreview() {
                const journalBody = document.querySelector('.journal-body');
                if (!journalBody) return;
                journalBody.innerHTML = '';

                let journalRows = [];
                let totalDebit = 0;
                let totalCredit = 0;

                $('tr.item-row').each(function() {
                    const index = $(this).data('index');
                    const accountName = $(`.account-name-${index}`).val();
                    const accountCode = $(`.account-name-${index}`).data('kode') || '';
                    const baseAmount = parseFloat($(`.amount_raw-${index}`).val()) || 0;
                    const taxAmount = parseFloat($(`.tax_amount_raw-${index}`).val()) || 0;
                    const taxType = $(`.tax-${index} option:selected`).data('type') || null;
                    const taxAccountName = $(`.tax-${index} option:selected`).data('account-name') || 'Tax';
                    const taxAccountCode = $(`.tax-${index} option:selected`).data('account-code') || '';

                    if (baseAmount > 0) {
                        journalRows.push({
                            accountCode: accountCode,
                            account: accountName || 'Persediaan / Beban Barang',
                            debit: baseAmount,
                            credit: 0
                        });
                        totalDebit += baseAmount;
                    }

                    if (taxAmount !== 0 && taxType) {
                        if (taxType === 'input_tax') {
                            journalRows.push({
                                accountCode: taxAccountCode,
                                account: taxAccountName || 'PPN Pajak Masukan',
                                debit: Math.abs(taxAmount),
                                credit: 0
                            });
                            totalDebit += Math.abs(taxAmount);
                        } else if (taxType === 'withholding_tax') {
                            journalRows.push({
                                accountCode: taxAccountCode,
                                account: taxAccountName || 'PPh Potongan',
                                debit: 0,
                                credit: Math.abs(taxAmount)
                            });
                            totalCredit += Math.abs(taxAmount);
                        }
                    }
                });

                const freight = parseFloat($('#freight').val()) || 0;
                if (freight > 0) {
                    journalRows.push({
                        accountCode: freightAccount.kode,
                        account: freightAccount.name,
                        debit: freight,
                        credit: 0
                    });
                    totalDebit += freight;
                }

                const globalTaxSelect = document.getElementById('global-tax');
                const withholdingValue = parseNumber(document.getElementById('global-tax-value')?.value);
                if (withholdingValue > 0) {
                    const withholdingAccountName = globalTaxSelect?.selectedOptions[0]?.dataset.accountName || 'PPh Dipotong';
                    const withholdingAccountCode = globalTaxSelect?.selectedOptions[0]?.dataset.accountCode || 'PPH';

                    journalRows.push({
                        account: `${withholdingAccountCode} - ${withholdingAccountName}`,
                        debit: 0,
                        credit: withholdingValue
                    });
                    totalCredit += withholdingValue;
                }

                const paymentAccountName = $('#pm-account-id option:selected').text();
                const paymentAccountCode = $('#pm-account-id option:selected').data('kode') || '';
                if (paymentAccountName && paymentAccountName !== '-- Pilih Rekening Bayar --') {
                    const payableAmount = totalDebit - totalCredit;
                    journalRows.push({
                        accountCode: paymentAccountCode,
                        account: paymentAccountName,
                        debit: 0,
                        credit: payableAmount
                    });
                    totalCredit += payableAmount;
                }

                if (journalRows.length === 0) {
                    journalBody.innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-400 italic">
                                Tambahkan item barang untuk melihat simulasi jurnal...
                            </td>
                        </tr>
                    `;
                } else {
                    journalRows.forEach(row => {
                        const displayAccount = row.accountCode ? `${row.accountCode} - ${row.account}` : row.account;
                        journalBody.insertAdjacentHTML('beforeend', `
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-2 text-gray-800 font-medium">${displayAccount}</td>
                                <td class="px-4 py-2 text-right font-mono text-emerald-700">${formatNumber(row.debit)}</td>
                                <td class="px-4 py-2 text-right font-mono text-blue-700">${formatNumber(row.credit)}</td>
                            </tr>
                        `);
                    });
                }

                const totalDebitEl = document.querySelector('.total-debit');
                const totalCreditEl = document.querySelector('.total-credit');
                if (totalDebitEl) totalDebitEl.textContent = formatNumber(totalDebit);
                if (totalCreditEl) totalCreditEl.textContent = formatNumber(totalCredit);
            }

            $(document).on('input change', '#items-body input, #items-body select', function() {
                const index = $(this).closest('tr').data('index');
                if (index !== undefined) {
                    calculateBackOrder(index);
                    calculateAmount(index);
                }
            });

            $(document).on('input', 'input[name^="items"][name$="[discount]"], input[name^="items"][name$="[quantity]"]', function() {
                const index = $(this).closest('tr').data('index');
                calculateAmount(index);
            });

            $(document).on('change', 'select[name^="items"][name$="[tax_id]"]', function() {
                const index = $(this).closest('tr').data('index');
                calculateAmount(index);
            });

            $('#freight-display').on('input', function() {
                const val = parseFloat($(this).val().replace(/,/g, '')) || 0;
                $('#freight').val(val);
                calculateTotals();
                generateJournalPreview();
            });

            $('#pm-account-id').on('change', function() {
                generateJournalPreview();
            });

            $('#add-row').on('click', function() {
                addEmptyRow();
            });

            if (!useOrderCheckbox.checked) {
                addEmptyRow();
            }
        });
    </script>
@endsection

@extends('layouts.app')
@section('content')
    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div id="tabs" class="type-section">
                <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                    <li><a href="#select_item" class="tab-link active">Proces purchase invoice</a></li>
                    <li><a href="#journal_report" class="tab-link">Journal report</a></li>
                </ul>
            </div>
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form method="POST"
                    action="{{ isset($purchase_invoice) ? route('purchase_invoice.update', $purchase_invoice->id) : route('purchase_invoice.store') }}">
                    @csrf
                    @if (isset($purchase_invoice))
                        @method('PUT')
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <h2 class="font-bold text-lg">Purchase Invoice Create</h2>
                    <div id="select_item" class="tab-content">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" id="use-order-number" class="mr-2">
                                    Gunakan data purchase order?
                                </label>
                            </div>
                            <div id="order-number-wrapper" class="mb-4 hidden">
                                <label for="purchase_order_id" class="block text-gray-700 font-medium mb-1">Order
                                    Number</label>
                                <select name="purchase_order_id"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($purchase_order as $data)
                                        <option value="{{ $data->id }}"
                                            {{ old('purchase_order_id', $purchase_invoice->purchase_order_id ?? '') == $data->id ? 'selected' : '' }}>
                                            {{ $data->order_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="invoice_number" class="block text-gray-700 font-medium mb-1">Invoice
                                    Number <span class="text-red-600">*</span></label>
                                <input type="text" id="invoice_number" name="invoice_number"
                                    value="{{ old('invoice_number', $purchase_invoice->invoice_number ?? '') }}"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                                <label class="inline-flex items-center mt-2">
                                    <input type="checkbox" id="auto_generate" name="auto_generate" value="1"
                                        class="form-checkbox text-blue-600" onchange="toggleAutoGenerate()">
                                    <span class="ml-2 text-sm text-gray-700">Generate Invoice Number secara otomatis</span>
                                </label>

                                @error('invoice_number')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>


                            <!-- Nama purchase_invoice_asset -->
                            <div>
                                <label class="block font-medium mb-1">Payment Method <span
                                        class="text-red-600">*</span></label>
                                <select id="jenis_pembayaran_id" name="jenis_pembayaran_id"
                                    class="w-full border rounded px-2 py-1 text-sm" required>
                                    <option value="">-- Payment Method --</option>
                                    @foreach ($jenis_pembayaran as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('jenis_pembayaran_id', $purchase_invoice->jenis_pembayaran_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->nama_jenis }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Kolom Kanan: Account (otomatis terisi, 1 saja) --}}
                            <div id="pm-account-panel"
                                class="{{ old('jenis_pembayaran_id', $purchase_invoice->jenis_pembayaran_id ?? '') ? '' : 'hidden' }}">
                                <label class="block font-medium mb-1">Account</label>
                                <select id="pm-account-id" name="header_account_id" required
                                    class="w-full border rounded px-2 py-1 text-sm">
                                    <option value="">-- Pilih Account --</option>
                                </select>
                            </div>
                            <div>
                                <label for="location_id" class="block text-gray-700 font-medium mb-1">Location Inventory
                                    <span class="text-red-600">*</span>
                                </label>
                                <select name="location_id" id="location_id"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Location Inventory--</option>
                                    @foreach ($lokasi_inventory as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('location_id', $purchase_invoice->location_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->kode_lokasi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="Vendor" class="block text-gray-700 font-medium mb-1">Vendor <span
                                        class="text-red-600">*</span>
                                </label>
                                <select name="vendor_id" id="vendor_id"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                    <option value="">-- Vendor--</option>
                                    @foreach ($vendor as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('vendor_id', $purchase_invoice->vendor_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->nama_vendors }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vendor_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="date_invoice" class="block text-gray-700 font-medium mb-1">Invoice Date <span
                                        class="text-red-600">*</span>
                                </label>
                                <input type="date" id="name" name="date_invoice" required
                                    value="{{ old('date_invoice', $purchase_invoice->date_invoice ?? now()->toDateString()) }}"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('date_invoice')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="shipping_date" class="block text-gray-700 font-medium mb-1">Shipping Date <span
                                        class="text-red-600"></span>
                                </label>
                                <input type="date" id="name" name="shipping_date"
                                    value="{{ old('shipping_date', $purchase_invoice->shipping_date ?? now()->toDateString()) }}"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('shipping_date')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- deskripsi purchase_invoice_asset -->
                            <div class="mb-4 md:col-span-2">
                                <label for="shipping_address" class="block text-gray-700 font-medium mb-1">Shipping
                                    Address <span class="text-red-600"></span></label>
                                <textarea id="shipping_address" name="shipping_address" rows="3"
                                    placeholder="Masukkan Shipping Address(Opsional)"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('shipping_address', $purchase_invoice->shipping_address ?? '') }}</textarea>
                                @error('shipping_address')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Order Items</h3>
                            <div class="overflow-x-auto border rounded-lg shadow-sm mt-6">
                                <table class="min-w-max border-collapse border text-sm whitespace-nowrap">
                                    @php
                                        $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                                    @endphp
                                    <thead
                                        class="bg-gradient-to-r bg-[{{ $themeColor }}]  to-blue-600 text-white text-sm font-semibold">
                                        <tr>
                                            <th class="border px-4 py-2 w-56">Item Number</th>
                                            <th class="border px-2 py-1 text-center w-24">Qty</th>
                                            <th class="border px-2 py-1 text-center w-24">Order</th>
                                            <th class="border px-2 py-1 text-center w-24">Back Order</th>
                                            <th class="border px-2 py-1 text-center w-28">Unit</th>
                                            <th class="border px-2 py-1 text-center w-38">Description</th>
                                            <th class="border px-2 py-1 text-center">Price</th>
                                            <th class="border px-2 py-1 text-center">Discount</th>
                                            <th class="border px-2 py-1 text-center">Vat</th>
                                            <th class="border px-2 py-1 text-center">Vat Value</th>
                                            <th class="border px-2 py-1 text-center">Amount</th>
                                            <th class="border px-2 py-1 text-center">Account</th>
                                            <th class="border px-2 py-1 text-center">Specpose</th>
                                            <th class="border px-2 py-1">#</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-body" class="bg-white">
                                        <!-- Akan diisi lewat JavaScript -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="9"></td>
                                            <td class="pr-3 text-right font-semibold">Subtotal :</td>
                                            <td><input type="text" id="subtotal" readonly
                                                    class="w-full border rounded text-right px-2 py-1 bg-gray-100"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="9"></td>

                                            <td class="pr-3 text-right font-semibold">Tax :</td>
                                            <td>
                                                <select name="withholding_tax" id="global-tax"
                                                    class="w-full border rounded text-right">
                                                    <option value="">-- Pilih Pajak --</option>
                                                    @foreach ($withholding as $item)
                                                        <option value="{{ $item->id }}"
                                                            data-rate="{{ $item->rate }}"
                                                            data-type="{{ $item->type }}"
                                                            data-account="{{ $item->purchase_account_id }}"
                                                            data-account-code="{{ $item->purchaseAccount->kode_akun ?? '' }}"
                                                            data-account-name="{{ $item->purchaseAccount->nama_akun ?? '' }}">
                                                            ({{ $item->rate }}%)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="9"></td>

                                            <td class="pr-3 text-right font-semibold">Tax Value
                                            </td>
                                            <td class="border px-2 py-1">
                                                <input type="text" id="global-tax-value" name="withholding_value"
                                                    class="w-full border rounded text-right" readonly>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="9"></td>
                                            <td class="pr-3 text-right font-semibold">Freight :</td>
                                            <td>
                                                <!-- input asli (hidden) -->
                                                <input type="hidden" id="freight" name="freight" value="0">

                                                <!-- input tampilan -->
                                                <input type="text" id="freight-display"
                                                    class="w-full border rounded text-right px-2 py-1 bg-gray-100">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan="9"></td>
                                            <td class="pr-3 text-right font-semibold">Total :</td>
                                            <td><input type="text" id="grand-total" readonly
                                                    class="w-full border rounded text-right px-2 py-1 bg-gray-100 font-bold">
                                            </td>
                                        </tr>
                                    </tfoot>

                                </table>
                            </div>
                            <!-- Button Tambah -->
                            <div class="mt-2">
                                <button type="button" id="add-row"
                                    class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700">
                                    + Tambah Baris
                                </button>
                            </div>
                            <div>
                                <label for="early_payment_terms" class="block text-gray-700 font-medium mb-1">Early
                                    Payments Terms
                                </label>
                                <input type="text" id="name" name="early_payment_terms"
                                    value="{{ old('early_payment_terms', $purchase_invoice->early_payment_terms ?? '') }}"
                                    class="w-1/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('early_payment_terms')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-4 md:col-span-2 ">
                                <label for="messages" class="block text-gray-700 font-medium mb-1">Messages
                                </label>
                                <textarea id="messages" name="messages" rows="3" placeholder="Masukkan messages(opsional)"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('messages', $purchase_invoice->messages ?? '') }}</textarea>
                                @error('messages')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div id="journal_report" class="tab-content hidden">
                        <h2 class="text-lg font-semibold mb-4">Journal Report</h2>
                        <table class="w-full border border-gray-300 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border text-left px-2 py-1">Account</th>
                                    <th class="border text-right px-2 py-1">Debit</th>
                                    <th class="border text-right px-2 py-1">Credit</th>
                                </tr>
                            </thead>
                            <tbody class="journal-body">
                                <tr>
                                    <td colspan="3" class="text-center py-2 text-gray-500">
                                        Tidak ada journal
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50 font-semibold">
                                <tr>
                                    <td class="border px-2 py-1 text-right">Total</td>
                                    <td class="border px-2 py-1 text-right total-debit">0.00</td>
                                    <td class="border px-2 py-1 text-right total-credit">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- Order Items Table -->

                    <!-- Buttons -->
                    <div class="mt-6 justify-end flex space-x-4">
                        <a href="{{ route('purchase_invoice.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition">
                            {{ isset($purchase_invoice) ? 'Update' : 'Process' }}
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JQUERY DULU -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script>
        $(document).ready(function() {
            $('#vendor_id').select2({
                placeholder: "-- Vendor --",
                ajax: {
                    url: '{{ route('vendors.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        }; // query keyword
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

                // Reset semua tab
                document.querySelectorAll('.tab-link').forEach(el => el.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));

                // Aktifkan tab yang diklik
                this.classList.add('active');
                const target = document.querySelector(this.getAttribute('href'));
                target.classList.remove('hidden');
            });
        });
    </script>

    <script>
        (function() {
            const $pmSelect = $('#jenis_pembayaran_id');
            const $panel = $('#pm-account-panel');
            const $disp = $('#pm-account-display');
            const $hiddenId = $('#pm-account-id');

            function clearAccount() {
                $disp.val('');
                $hiddenId.val('');
                $panel.addClass('hidden');
            }

            function setAccount(a) {
                const text = `${a.kode_akun || '-'} - ${a.nama_akun || '-'}`;
                $disp.val(text);
                $hiddenId.val(a.account_id || '');
                $panel.removeClass('hidden');
            }

            function pickOne(accounts) {
                if (!accounts || !accounts.length) return null;
                // 1) cari default
                const def = accounts.find(x => x.is_default);
                if (def) return def;
                // 2) kalau tidak ada default, ambil yang pertama
                return accounts[0];
            }

            function loadPMAccounts(pmId) {
                if (!pmId) {
                    clearAccount();
                    return;
                }

                $.getJSON("{{ route('payment-methods.accounts', ['id' => 'PM_ID']) }}".replace('PM_ID', pmId))
                    .done(function(res) {
                        const $select = $('#pm-account-id');
                        $select.empty().append('<option value="">-- Pilih Account --</option>');

                        (res.accounts || []).forEach(function(a) {
                            const text =
                                `${a.kode_akun || '-'} - ${a.nama_akun || '-'}`;
                            $select.append(`<option value="${a.detail_id}">${text}</option>`);
                        });

                        // kalau form edit, bisa auto-select berdasarkan value lama
                        const oldVal =
                            "{{ old('payment_method_account_id', $purchase_invoice->payment_method_account_id ?? '') }}";
                        if (oldVal) $select.val(oldVal);

                        $panel.removeClass('hidden');
                    })
                    .fail(function() {
                        clearAccount();
                        alert('Gagal memuat account dari Payment Method.');
                    });
            }


            // on change
            $pmSelect.on('change', function() {
                loadPMAccounts($(this).val());
            });

            // initial load (untuk edit form)
            const initial = $pmSelect.val();
            if (initial) loadPMAccounts(initial);
        })();
    </script>
    <script>
        function toggleAutoGenerate() {
            const checkbox = document.getElementById('auto_generate');
            const invoiceInput = document.getElementById('invoice_number');

            if (checkbox.checked) {
                invoiceInput.readOnly = true;
                invoiceInput.value = 'Auto-generated'; // opsional: tampilkan teks dummy
            } else {
                invoiceInput.readOnly = false;
                invoiceInput.value = '';
            }
        }

        // Jalankan saat halaman dimuat
        window.onload = function() {
            toggleAutoGenerate();

            // Tambahkan value agar checkbox bisa dikenali server
            const autoCheckbox = document.getElementById('auto_generate');
            autoCheckbox.name = "auto_generate";
            autoCheckbox.value = 1;
        };
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const useOrderCheckbox = document.getElementById('use-order-number');
            const orderWrapper = document.getElementById('order-number-wrapper');
            const selectOrder = document.querySelector('select[name="purchase_order_id"]');
            const tbody = document.getElementById('items-body');
            let rowIndex = 0;

            // 🚚 akun Freight dari backend (lempar via Blade)
            const freightAccount = {
                id: "{{ $freightAccount->akun_id ?? '' }}",
                kode: "{{ $freightAccount->akun->kode_akun ?? '-' }}",
                name: "{{ $freightAccount->akun->nama_akun ?? 'Freight Expense' }}"
            };

            // 🔢 helper number format
            const formatNumber = num => new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);

            // Toggle mode PO / Manual
            useOrderCheckbox.addEventListener('change', function() {
                tbody.innerHTML = '';
                if (this.checked) {
                    orderWrapper.classList.remove('hidden');
                } else {
                    orderWrapper.classList.add('hidden');
                    addEmptyRow();
                }
            });

            // Load item dari PO
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

            // Tambah row kosong (manual)
            function addEmptyRow() {
                const index = rowIndex++;
                const row = `
                <tr class="item-row" data-index="${index}">
                    <td class="border px-4 py-2"><select name="items[${index}][item_id]" class="item-select w-full border rounded" data-index="${index}"></select></td>
                    <td class="border px-4 py-2"><input type="number" name="items[${index}][quantity]" class="qty-${index} w-full border rounded" /></td>
                    <td class="border px-4 py-2"><input type="number" name="items[${index}][order]" class="w-full border bg-gray-100 rounded"  readonly/></td>
                    <td class="border px-4 py-2"><input type="number" name="items[${index}][back_order]" class="w-full bg-gray-100 border rounded" readonly /></td>
                    <td class="border px-4 py-2"><input type="text" name="items[${index}][unit]" class="unit-${index} w-full border bg-gray-100 rounded" readonly /></td>
                    <td class="border px-4 py-2"><input type="text" name="items[${index}][item_description]" class="desc-${index} w-full border bg-gray-100 rounded" readonly /></td>
                    <td class="border px-4 py-2"><input type="number" step="0.01" name="items[${index}][price]" class="purchase-${index} w-full border rounded" /></td>
                    <td class="border px-4 py-2"><input type="number" step="0.01" name="items[${index}][discount]" class="disc-${index} w-full border rounded" /></td>
                    <td class="border px-4 py-2">
                        <select name="items[${index}][tax_id]" class="tax-${index} w-full border rounded">
                            <option value="">-- Pilih Pajak --</option>
                            @foreach ($sales_taxes as $item)
                                <option value="{{ $item->id }}" 
                                        data-rate="{{ $item->rate }}" 
                                        data-type="{{ $item->type }}"
                                        data-account="{{ $item->purchase_account_id }}"  
                                        data-account-code="{{ $item->purchaseAccount->kode_akun ?? '' }}"
                                        data-account-name="{{ $item->purchaseAccount->nama_akun ?? '' }}">
                                    ({{ $item->rate }}%)
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="border px-4 py-2"><input type="text" name="items[${index}][tax_amount_display]" class="tax_amount-${index} w-full border bg-gray-100 rounded text-right" readonly />
                        <input type="hidden" name="items[${index}][tax_amount]" class="tax_amount_raw-${index}" />
                    </td>
                    <td class="border px-4 py-2"><input type="text" name="items[${index}][amount_display]" class="amount-${index} w-full border bg-gray-100 rounded text-right" readonly />
                        <input type="hidden" name="items[${index}][amount]" class="amount_raw-${index}" />
                    </td>
                    <td class="border px-4 py-2">
                        <input type="text" class="w-full border rounded bg-gray-100 account-name-${index}" readonly />
                        <input type="hidden" name="items[${index}][account_id]" class="account-id-${index}" />
                    </td>
                    <td class="border px-4 py-2">
                        <select name="items[${index}][project_id]" class="w-full border rounded">
                            <option value="">-- Pilih Specpose --</option>
                            @foreach ($project as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_project }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center border px-4 py-2">
                        <button type="button" class="remove-row px-2 py-1 bg-red-500 text-white rounded" data-index="${index}">X</button>
                    </td>
                </tr>`;
                tbody.insertAdjacentHTML('beforeend', row);
                attachSelect2(index);
            }

            // Tambah row dari PO
            function addRowFromPO(item) {
                const index = rowIndex++;
                const row = `
            <tr class="item-row" data-index="${index}">
                <td class="border px-4 py-2"><input type="hidden" name="items[${index}][item_id]" value="${item.id}">${item.item_number}</td>
                <td class="border px-4 py-2"><input type="number" name="items[${index}][quantity]" value="${item.quantity}" class="qty-${index} w-full border rounded" /></td>
                <td class="border px-4 py-2"><input type="number" name="items[${index}][order]" value="${item.order}" class="order-${index} w-full border bg-gray-200 rounded" readonly/></td>
                <td class="border px-4 py-2"><input type="number" name="items[${index}][back_order]" value="${item.back_order}" class="back-${index} w-full border bg-gray-100 bg-gray-200 rounded" readonly /></td>
                <td class="border px-4 py-2"><input type="text" name="items[${index}][unit]" value="${item.unit}" class="unit-${index} w-full bg-gray-100 border rounded" readonly/></td>
                <td class="border px-4 py-2"><input type="text" name="items[${index}][item_description]" value="${item.description}" class="desc-${index} w-full bg-gray-100 border rounded" readonly /></td>
                <td class="border px-4 py-2"><input type="number" step="0.01" name="items[${index}][price]" value="${item.price}" class="purchase-${index} w-full border rounded" /></td>
                <td class="border px-4 py-2"><input type="number" step="0.01" name="items[${index}][discount]" value="${item.discount}" class="disc-${index} w-full border rounded" /></td>
                <td class="border px-4 py-2">
                    <select name="items[${index}][tax_id]" class="tax-${index} w-full border rounded">
                        <option value="">-- Pilih Pajak --</option>
                        @foreach ($sales_taxes as $tax)
                            <option value="{{ $tax->id }}" ${item.tax_id == {{ $tax->id }} ? 'selected' : ''} 
                                    data-rate="{{ $tax->rate }}" 
                                    data-type="{{ $tax->type }}"
                                    data-account="{{ $tax->purchase_account_id }}"  
                                    data-account-code="{{ $tax->purchaseAccount->kode_akun ?? '' }}"
                                    data-account-name="{{ $tax->purchaseAccount->nama_akun ?? '' }}">
                                 ({{ $tax->rate }}%)
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="border px-4 py-2"><input type="text" name="items[${index}][tax_amount_display]" value="${formatNumber(item.tax_amount)}" class="tax_amount-${index} w-full border bg-gray-100 rounded text-right" readonly />
                    <input type="hidden" name="items[${index}][tax_amount]" value="${item.tax_amount}" class="tax_amount_raw-${index}" />
                </td>
                <td class="border px-4 py-2"><input type="text" name="items[${index}][amount_display]" value="${formatNumber(item.amount)}" class="amount-${index} w-full border bg-gray-100 rounded text-right" readonly />
                    <input type="hidden" name="items[${index}][amount]" value="${item.amount}" class="amount_raw-${index}" />
                </td>
                <td class="border px-4 py-2">
                    <input type="text"
                            value="${item.account_code}-${item.account_name}"
                            class="w-full border rounded bg-gray-100 account-name-${index}"
                            readonly />
                    <input type="hidden" name="items[${index}][account_id]" value="${item.account_id}" />
                    </td>

                <td class="border px-4 py-2">
                    <select name="items[${index}][project_id]" class="w-full border rounded">
                        <option value="">-- Pilih Specpose --</option>
                        @foreach ($project as $item)
                            <option value="{{ $item->id }}">{{ $item->nama_project }}</option>
                        @endforeach
                    </select>
                </td>
                 <td class="text-center border px-4 py-2">
                        <button type="button" class="remove-row px-2 py-1 bg-red-500 text-white rounded" data-index="${index}">X</button>
                    </td>
            </tr>`;
                tbody.insertAdjacentHTML('beforeend', row);
                calculateBackOrder(index);
                calculateAmount(index);
            }

            // Attach Select2
            function attachSelect2(index) {
                $(`select[data-index="${index}"]`).select2({
                    placeholder: 'Cari item...',
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
                    }
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

            // Hapus row
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                calculateTotals();
                generateJournalPreview();
            });

            // --- Perhitungan ---
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

                // 💰 Nilai dasar (sebelum pajak)
                const baseAmount = (price - discount) * qty;

                // 💸 Pajak
                let taxAmount = baseAmount * (taxRate / 100);
                if (taxType === 'withholding_tax') {
                    taxAmount = -Math.abs(taxAmount); // potongan
                }

                // Simpan raw (tanpa efek pajak)
                $(`.amount_raw-${index}`).val(baseAmount.toFixed(2));
                $(`.tax_amount_raw-${index}`).val(taxAmount.toFixed(2));

                // Untuk tampilan di tabel
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

                    final = base + tax;
                    subtotal += final;
                    totalTax += tax; // withholding sudah negatif
                });


                const globalTaxSelect = document.getElementById('global-tax');
                const globalTaxRate = parseNumber(globalTaxSelect?.selectedOptions[0]?.dataset.rate);
                const withholdingValueInput = document.getElementById('global-tax-value');

                const withholdingValue = subtotal * (globalTaxRate / 100);
                withholdingValueInput.value = formatNumber(withholdingValue);

                const freight = parseFloat($('#freight').val()) || 0;
                const grandTotal = subtotal - withholdingValue + freight;

                $('#subtotal').val(formatNumber(subtotal));
                $('#grand-total').val(formatNumber(grandTotal));

                generateJournalPreview();

            }

            document.getElementById('global-tax').addEventListener('change', calculateTotals);


            function generateJournalPreview() {
                const journalBody = document.querySelector('.journal-body');
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

                    // --- Barang / Asset (Debit) ---
                    if (baseAmount > 0) {
                        journalRows.push({
                            accountCode: accountCode,
                            account: accountName,
                            debit: baseAmount,
                            credit: 0
                        });
                        totalDebit += baseAmount;
                    }

                    // --- Pajak ---
                    if (taxAmount !== 0 && taxType) {
                        if (taxType === 'input_tax') {
                            journalRows.push({
                                accountCode: taxAccountCode,
                                account: taxAccountName,
                                debit: Math.abs(taxAmount),
                                credit: 0
                            });
                            totalDebit += Math.abs(taxAmount);
                        } else if (taxType === 'withholding_tax') {
                            journalRows.push({
                                accountCode: taxAccountCode,
                                account: taxAccountName,
                                debit: 0,
                                credit: Math.abs(taxAmount)
                            });
                            totalCredit += Math.abs(taxAmount);
                        }
                    }
                });

                // --- Freight ---
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
                const withholdingRate = parseNumber(globalTaxSelect?.selectedOptions[0]?.dataset.rate);
                const withholdingValue = parseNumber(document.getElementById('global-tax-value')?.value);
                if (withholdingValue > 0) {
                    const withholdingAccountName = globalTaxSelect?.selectedOptions[0]?.dataset.accountName ||
                        'PPh Dipotong';
                    const withholdingAccountCode = globalTaxSelect?.selectedOptions[0]?.dataset.accountCode ||
                        'PPH';

                    journalRows.push({
                        account: `${withholdingAccountCode} - ${withholdingAccountName}`,
                        debit: 0,
                        credit: withholdingValue
                    });
                    totalCredit += withholdingValue;
                }


                // --- Payment / Kas ---
                const paymentAccountName = $('#pm-account-id option:selected').text();
                const paymentAccountCode = $('#pm-account-id option:selected').data('kode') || '';
                if (paymentAccountName) {
                    const payableAmount = totalDebit - totalCredit;
                    journalRows.push({
                        accountCode: paymentAccountCode,
                        account: paymentAccountName,
                        debit: 0,
                        credit: payableAmount
                    });
                    totalCredit += payableAmount;
                }

                // --- Render ---
                journalRows.forEach(row => {
                    const displayAccount = row.accountCode ?
                        `${row.accountCode} - ${row.account}` : row.account;
                    journalBody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="border px-2 py-1">${displayAccount}</td>
                    <td class="border px-2 py-1 text-right">${formatNumber(row.debit)}</td>
                    <td class="border px-2 py-1 text-right">${formatNumber(row.credit)}</td>
                </tr>
            `);
                });

                document.querySelector('.total-debit').textContent = formatNumber(totalDebit);
                document.querySelector('.total-credit').textContent = formatNumber(totalCredit);
            }


            // fallback universal untuk perubahan apa pun di table body
            $(document).on('input change', '#items-body input, #items-body select', function() {
                const index = $(this).closest('tr').data('index');
                if (index !== undefined) {
                    calculateBackOrder(index);
                    calculateAmount(index);
                }
            });


            $(document).on('input',
                'input[name^="items"][name$="[discount]"], input[name^="items"][name$="[quantity]"]',
                function() {
                    const index = $(this).closest('tr').data('index');
                    calculateAmount(index);
                });


            $(document).on('change', 'select[name^="items"][name$="[tax_id]"]', function() {
                const index = $(this).closest('tr').data('index');
                calculateAmount(index);
            });

            $('#freight-display').on('input', function() {
                const val = parseFloat($(this).val()) || 0;
                $('#freight').val(val);
                calculateTotals();
                generateJournalPreview();
            });

            $('#pm-account-id').on('change', function() {
                generateJournalPreview();
            });

            function addRow() {
                const newRow = addEmptyRow(rowIndex);
                $('#item-table-body').append(newRow);
                attachSelect2(rowIndex);
                rowIndex++;
            }
            $('#add-row').on('click', function() {
                addEmptyRow();
            });

            // Baris awal
            if (!useOrderCheckbox.checked) {
                addEmptyRow();
            }
        });
    </script>

@endsection

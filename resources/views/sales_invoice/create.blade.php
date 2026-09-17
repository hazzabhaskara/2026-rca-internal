@extends('layouts.app')

@section('content')

    <div class="py-4">
        <div class="w-full px-2">
            <div class="bg-white shadow rounded p-4 h-full flex flex-col">
                <div id="tabs" class="type-section">
                    <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                        <li><a href="#select_item" class="tab-link active">Proces Sales invoice</a></li>
                        <li><a href="#journal_report" class="tab-link">Journal report</a></li>
                    </ul>
                </div>

                <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                    Create Sales Invoice
                </h4>

                <form method="POST"
                    action="{{ isset($sales_invoice) ? route('sales_invoice.update', $sales_invoice->id) : route('sales_invoice.store') }}"
                    class="flex flex-col h-full">
                    @csrf
                    @if (isset($sales_invoice))
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

                    <div id="select_item" class="tab-content">
                        <div class="grid grid-cols-4 gap-4 text-sm">
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" id="use-order-number" class="mr-2">
                                    Gunakan Sales Order?
                                </label>
                            </div>
                            <div id="order-number-wrapper"class="mb-4 hidden">
                                <label for="nama_metode" class="block text-gray-700 font-medium mb-1">Order Number
                                </label>
                                <select name="sales_order_id"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Pilih--</option>
                                    @foreach ($sales_order as $data)
                                        <option value="{{ $data->id }}"
                                            {{ old('sales_order_id', $sales_invoice->sales_order_id ?? '') == $data->id ? 'selected' : '' }}>
                                            {{ $data->order_number }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sales_order_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="invoice_number" class="block text-gray-700 font-medium mb-1">Invoice
                                    Number</label>
                                <input type="text" id="invoice_number" name="invoice_number"
                                    value="{{ old('invoice_number', $sales_invoice->invoice_number ?? '') }}"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                                <label class="inline-flex items-center mt-2">
                                    <input type="checkbox" id="auto_generate" name="auto_generate" value="1"
                                        class="form-checkbox text-blue-600" onchange="toggleAutoGenerate()">
                                    <span class="ml-2 text-sm text-gray-700">Generate Invoice Number secara otomatis</span>
                                </label>

                                @error('invoice_number')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nama sales_invoice_asset -->
                            <div>
                                <label class="block font-medium mb-1">Payment Method</label>
                                <select id="jenis_pembayaran_id" name="jenis_pembayaran_id"
                                    class="w-full border rounded px-2 py-1 text-sm" required>
                                    <option value="">-- Payment Method --</option>
                                    @foreach ($jenis_pembayaran as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('jenis_pembayaran_id', $sales_invoice->jenis_pembayaran_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->nama_jenis }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Kolom Kanan: Account (otomatis terisi, 1 saja) --}}
                            <div id="pm-account-panel"
                                class="{{ old('jenis_pembayaran_id', $sales_invoice->jenis_pembayaran_id ?? '') ? '' : 'hidden' }}">
                                <label class="block font-medium mb-1">Account</label>
                                <select id="pm-account-id" name="header_account_id"
                                    class="w-full border rounded px-2 py-1 text-sm">
                                    <option value="">-- Pilih Account --</option>
                                </select>
                            </div>
                            <div>
                                <label for="customers" class="block text-gray-700 font-medium mb-1">Customers
                                </label>
                                <select name="customers_id" id="customer_id"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                    <option value="">-- Customers--</option>
                                    @foreach ($customer as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('customer_id', $sales_invoice->customer_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->nama_customers }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="location" class="block text-gray-700 font-medium mb-1">Location Inventory
                                </label>
                                <select name="location_id" id="location_id"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                    <option value="">-- Location--</option>
                                    @foreach ($lokasi_inventory as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('location_id', $sales_invoice->location_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->kode_lokasi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="invoice_date" class="block text-gray-700 font-medium mb-1">Invoice Date
                                </label>
                                <input type="date" id="name" name="invoice_date" required
                                    value="{{ old('invoice_date', $sales_invoice->invoice_date ?? now()->toDateString()) }}"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('invoice_date')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="shipping_date" class="block text-gray-700 font-medium mb-1">Shipping Date
                                </label>
                                <input type="date" id="name" name="shipping_date"
                                    value="{{ old('shipping_date', $sales_invoice->shipping_date ?? now()->toDateString()) }}"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('shipping_date')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="Employee" class="block text-gray-700 font-medium mb-1">Employee
                                </label>
                                <select name="sales_person_id" id="employee_id"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Employee--</option>
                                    @foreach ($employee as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('employee_id', $sales_invoice->employee_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->nama_karyawan }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- deskripsi sales_invoice_asset -->
                            <div class="col-span-3">
                                <label class="block font-medium mb-1">Shipping Address</label>
                                <textarea name="shipping_address" rows="2" class="w-full border rounded px-2 py-1 text-sm">{{ old('shipping_address', $sales_invoice->shipping_address ?? '') }}</textarea>
                            </div>
                        </div>

                        <!-- Order Items Table -->
                        <div class="mt-4 flex-1 flex flex-col overflow-hidden">
                            <h3 class="text-lg font-semibold mb-4">Order Items</h3>

                            <!-- Scrollable Table -->
                            <div>
                                <div class="overflow-x-auto border rounded-lg shadow-sm mt-6">
                                    <table class="min-w-max border-collapse border text-sm whitespace-nowrap">

                                        @php
                                            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                                            $textFooter = \App\Setting::get(
                                                'text_footer',
                                                'ANTS LITE+ ©2025_AN NAML CORP.',
                                            );
                                        @endphp
                                        <thead
                                            class="bg-gradient-to-r bg-[{{ $themeColor }}]  to-blue-600 text-white text-sm font-semibold">
                                            <tr>
                                                <th class="px-2 py-1 border w-40">Item Number</th>
                                                <th class="px-2 py-1 border">Description</th>
                                                <th class="px-2 py-1 border">Qty</th>
                                                <th class="px-2 py-1 border">Order</th>
                                                <th class="px-2 py-1 border">Back Order</th>
                                                <th class="px-2 py-1 border">Unit</th>
                                                <th class="px-2 py-1 border">Base Price</th>
                                                <th class="px-2 py-1 border">Discount</th>
                                                <th class="px-2 py-1 border">Price</th>
                                                <th class="px-2 py-1 border">Amount</th>
                                                <th class="px-2 py-1 border">Vat</th>
                                                <th class="px-2 py-1 border">Vat Value</th>
                                                <th class="px-2 py-1 border">Subtotal</th>
                                                <th class="px-2 py-1 border">Account</th>
                                                <th class="px-2 py-1 border">Specpose</th>
                                                <th class="px-2 py-1 border">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="items-body">
                                            <!-- Akan diisi lewat JavaScript -->
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="9" class="text-right font-bold border px-2 py-1">Subtotal
                                                </td>
                                                <td colspan="2" class="border px-2 py-1">
                                                    <input type="text" id="subtotal"
                                                        class="w-full border rounded text-right bg-gray-100" readonly>
                                                </td>
                                                <td colspan="4" class="border px-2 py-1"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="9" class="text-right font-bold border px-2 py-1">
                                                    Tax</td>
                                                <td colspan="2" class="border px-2 py-1">
                                                    <select name="withholding_tax" id="global-tax"
                                                        class="w-full border rounded text-right">
                                                        <option value="">--Pilih--</option>
                                                        @foreach ($withholding as $item)
                                                            <option value="{{ $item->id }}"
                                                                data-rate="{{ $item->rate }}"
                                                                data-account-code="{{ $item->salesAccount->kode_akun ?? '' }}"
                                                                data-account-name="{{ $item->salesAccount->nama_akun ?? '' }}">
                                                                ({{ $item->rate }}%)
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                </td>

                                                <td colspan="4" class="border px-2 py-1"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="9" class="text-right font-bold border px-2 py-1">Tax Value
                                                </td>
                                                <td colspan="2" class="border px-2 py-1">
                                                    <input type="text" id="global-tax-value" name="withholding_value"
                                                        class="w-full border rounded text-right bg-gray-100 mt-1" readonly>
                                                </td>
                                                <td colspan="4" class="border px-2 py-1"></td>

                                            </tr>


                                            <!-- Freight row -->
                                            <tr>
                                                <td colspan="9" class="text-right font-bold border px-2 py-1">Freight
                                                </td>
                                                <td colspan="2" class="border px-2 py-1">
                                                    <input type="text" id="freight" name="freight"
                                                        class="w-full border rounded text-right" value="0">
                                                </td>
                                                <td colspan="4" class="border px-2 py-1"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="9" class="text-right font-bold border px-2 py-1">Grand
                                                    Total
                                                </td>
                                                <td colspan="2" class="border px-2 py-1">
                                                    <input type="text" id="grand-total"
                                                        class="w-full border rounded text-right bg-gray-100 font-bold"
                                                        readonly>
                                                </td>
                                                <td colspan="4" class="border px-2 py-1"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="mt-2">
                                    <button type="button" id="add-row"
                                        class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700">
                                        + Tambah Baris
                                    </button>
                                </div>

                                <div class="grid grid-cols-3 gap-4 text-sm mt-4">
                                    <div>
                                        <label for="early_payment_terms"
                                            class="block text-gray-700 font-medium mb-1">Early
                                            Payments Terms
                                        </label>
                                        <input type="text" id="name" name="early_payment_terms"
                                            value="{{ old('early_payment_terms', $sales_invoice->early_payment_terms ?? '') }}"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        @error('early_payment_terms')
                                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="mb-4 md:col-span-2 ">
                                        <label for="messages" class="block text-gray-700 font-medium mb-1">Messages
                                        </label>
                                        <textarea id="messages" name="messages" rows="3"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('messages', $sales_invoice->messages ?? '') }}</textarea>
                                        @error('messages')
                                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
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

                    <!-- Buttons -->
                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="{{ route('sales_invoice.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition">
                            {{ isset($sales_invoice) ? 'Update' : 'Process' }}
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
        $(document).ready(function() {
            function initSelect2(selector, url, mapper, placeholder) {
                $(selector).select2({
                    placeholder: placeholder,
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.map(mapper)
                            };
                        },
                        cache: true
                    },
                    allowClear: true,
                    width: '100%'
                });
            }

            // ✅ Customers
            initSelect2(
                '#customer_id',
                '{{ route('customers.search') }}',
                function(customer) {
                    return {
                        id: customer.id,
                        text: customer.nama_customers
                    };
                },
                "-- Customers --"
            );

            // ✅ Employees
            initSelect2(
                '#employee_id',
                '{{ route('employee.search') }}',
                function(employee) {
                    return {
                        id: employee.id,
                        text: employee.nama_karyawan
                    };
                },
                "-- Employees --"
            );
        });
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
            const selectOrder = document.querySelector('select[name="sales_order_id"]');
            const tbody = document.getElementById('items-body');
            const subtotalInput = document.getElementById('subtotal');
            const totalTaxInput = document.getElementById('total-tax');
            const freightInput = document.getElementById('freight');
            const grandTotalInput = document.getElementById('grand-total');
            const withholdingSelect = document.getElementById('withholding');
            const withholdingInput = document.getElementById('withholding-value');

            const journalBody = document.querySelector('.journal-body');

            const $pmSelect = $('#jenis_pembayaran_id');
            const $pmAccount = $('#pm-account-id');
            const $panel = $('#pm-account-panel');
            const $disp = $('#pm-account-display');
            let rowIndex = 0;

            function formatNumber(num) {
                return Number(num).toLocaleString('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }

            // 🚚 akun Freight dari backend (lempar via Blade)
            const freightAccount = {
                id: "{{ $freightAccount->akun_id ?? '' }}",
                kode: "{{ $freightAccount->akun->kode_akun ?? '-' }}",
                name: "{{ $freightAccount->akun->nama_akun ?? 'Freight Revenue' }}"
            };

            function parseNumber(val) {
                return parseFloat(String(val).replace(/,/g, '')) || 0;
            }

            function loadPMAccounts(pmId) {
                if (!pmId) {
                    $pmAccount.empty().append('<option value="">-- Pilih Account --</option>');
                    $disp.val('');
                    $panel.addClass('hidden');
                    return;
                }

                $.getJSON("{{ route('payment-methods.accounts', ['id' => 'PM_ID']) }}".replace('PM_ID', pmId))
                    .done(function(res) {
                        $pmAccount.empty().append('<option value="">-- Pilih Account --</option>');

                        (res.accounts || []).forEach(function(a) {
                            const text = `${a.kode_akun} - ${a.nama_akun}`;
                            // ⬇️ gunakan detail_id untuk kompatibel dgn validasi exists:payment_method_details,id
                            $pmAccount.append(`<option value="${a.detail_id}">${text}</option>`);
                        });

                        const def = (res.accounts || []).find(x => x.is_default) || (res.accounts || [])[0];
                        if (def) {
                            $pmAccount.val(def.detail_id);
                            // console.log('✅ Auto pilih account:', def);
                        }

                        $panel.removeClass('hidden');
                        generateJournalPreview(); // refresh jurnal setelah load akun
                    })
                    .fail(function() {
                        alert('Gagal load akun dari Payment Method.');
                    });
            }

            $pmSelect.on('change', function() {
                loadPMAccounts($(this).val());
            });

            if ($pmSelect.val()) {
                loadPMAccounts($pmSelect.val());
            }

            function calculateBackOrder(index) {
                const qty = parseNumber(document.querySelector(`.qty-${index}`).value);
                const order = parseNumber(document.querySelector(`.order-${index}`).value);
                // ⬇️ back order = order - qty (minimal 0)
                const back = Math.max(order - qty, 0);
                document.querySelector(`.back-${index}`).value = formatNumber(back);
            }

            function calculateAmount(index) {
                const qty = parseNumber(document.querySelector(`.qty-${index}`).value);
                const order = parseNumber(document.querySelector(`.order-${index}`).value);
                const basePrice = parseNumber(document.querySelector(`.purchase-${index}`).value);
                const discount = parseNumber(document.querySelector(`.disc-${index}`).value);

                const taxSelect = document.querySelector(`.tax-${index}`);
                const taxPercent = parseNumber(taxSelect?.selectedOptions[0]?.dataset.rate);
                // ⬇️ baca tipe pajak
                const taxType = (taxSelect?.selectedOptions[0]?.dataset.type) || 'input_tax';

                // console.log(`calculateAmount(${index}) called`, {
                //     qty,
                //     basePrice,
                //     discount,
                //     taxPercent,
                //     taxType
                // });


                // ✅ batasi qty tidak boleh melebihi order saat mode PO
                if (useOrderCheckbox.checked && qty > order) {
                    alert('Qty tidak boleh melebihi Order.');
                    document.querySelector(`.qty-${index}`).value = formatNumber(order);
                    calculateBackOrder(index);
                    // continue with corrected qty
                }

                // price after discount
                const price = Math.max(basePrice - discount, 0);
                document.querySelector(`.price-${index}`).value = formatNumber(price);

                // amount = qty * price
                const qtyNow = parseNumber(document.querySelector(`.qty-${index}`).value); // ambil setelah clamp
                const amount = qtyNow * price;
                document.querySelector(`.amount-${index}`).value = formatNumber(amount);

                // tax value
                const taxValue = amount * (taxPercent / 100);
                document.querySelector(`.taxval-${index}`).value = formatNumber(taxValue);

                // final berdasarkan type
                let final = amount;
                if (taxType === 'input_tax') {
                    final = amount + taxValue; // PPN tambah
                } else if (taxType === 'withholding_tax') {
                    final = Math.max(amount - taxValue, 0); // PPh kurang
                }
                document.querySelector(`.final-${index}`).value = formatNumber(final);

                // back order realtime
                calculateBackOrder(index);

                calculateTotals();
            }

            function calculateTotals() {
                let subtotal = 0;
                let totalInputTax = 0;

                // 🔹 Loop semua item
                document.querySelectorAll('#items-body .item-row').forEach(row => {
                    const idx = row.dataset.index;
                    const amount = parseNumber(document.querySelector(`.amount-${idx}`).value);
                    const finalVal = parseNumber(document.querySelector(`.final-${idx}`).value);
                    const taxVal = parseNumber(document.querySelector(`.taxval-${idx}`).value);
                    const taxType = document.querySelector(`.tax-${idx}`)?.selectedOptions[0]?.dataset
                        .type || 'input_tax';

                    subtotal += finalVal;
                    if (taxType === 'input_tax') totalInputTax += taxVal;
                });

                subtotalInput.value = formatNumber(subtotal);

                // 🔹 Global withholding (per invoice)
                const globalTaxSelect = document.getElementById('global-tax');
                const globalTaxRate = parseNumber(globalTaxSelect?.selectedOptions[0]?.dataset.rate);
                const withholdingValueInput = document.getElementById('global-tax-value');

                const withholdingValue = subtotal * (globalTaxRate / 100);
                withholdingValueInput.value = formatNumber(withholdingValue);

                // 🔹 Freight
                const freight = parseNumber(freightInput.value);

                // 🔹 Grand total = subtotal + input tax + freight - withholding
                const grandTotal = subtotal - withholdingValue + freight;
                grandTotalInput.value = formatNumber(grandTotal);

                generateJournalPreview();
            }

            // 🧩 Event listeners
            document.getElementById('global-tax').addEventListener('change', calculateTotals);
            document.getElementById('freight').addEventListener('input', calculateTotals);

            // 🔹 Optional: hitung ulang saat item berubah
            document.querySelectorAll('#items-body').forEach(el => {
                el.addEventListener('input', () => calculateTotals());
            });

            function generateJournalPreview() {
                journalBody.innerHTML = '';
                let journalRows = [];
                let totalDebit = 0,
                    totalCredit = 0;

                // ========================
                // 1️⃣ Hitung akun per item (pendapatan & PPN keluaran)
                // ========================
                document.querySelectorAll('#items-body .item-row').forEach(row => {
                    const idx = row.dataset.index;
                    const accountName = document.querySelector(`.account-name-${idx}`)?.value ||
                        'Pendapatan';
                    const accountCode = $(`.account-name-${idx}`).data('kode') || '';
                    const amount = parseNumber(document.querySelector(`.amount-${idx}`)?.value);
                    const taxAmount = parseNumber(document.querySelector(`.taxval-${idx}`)?.value);
                    const taxSelect = document.querySelector(`.tax-${idx}`);
                    const taxType = taxSelect?.selectedOptions[0]?.dataset.type || 'input_tax';
                    const taxAccountName = taxSelect?.selectedOptions[0]?.dataset.accountName || 'Tax';
                    const taxAccountCode = taxSelect?.selectedOptions[0]?.dataset.accountCode || 'Tax';


                    // ========================
                    // 4️⃣ Kas / Bank diterima (DEBIT)
                    // ========================
                    const paymentAccountName = document.querySelector('#pm-account-id option:checked')
                        ?.text || '';
                    const grandTotal = parseNumber(grandTotalInput.value);
                    if (grandTotal > 0 && paymentAccountName) {
                        journalRows.push({
                            account: paymentAccountName,
                            debit: grandTotal,
                            credit: 0
                        });
                        totalDebit += grandTotal;
                    }
                    // Pendapatan (Credit)
                    if (amount > 0) {
                        journalRows.push({
                            account: `${accountName}`,
                            debit: 0,
                            credit: amount
                        });
                        totalCredit += amount;
                    }

                    // Pajak keluaran (Credit)
                    if (taxAmount > 0 && taxType === 'input_tax') {
                        journalRows.push({
                            account: `${taxAccountCode} - ${taxAccountName}`,
                            debit: 0,
                            credit: taxAmount
                        });
                        totalCredit += taxAmount;
                    }

                    // HPP (COGS) untuk barang inventory
                    const type = row.dataset.type;
                    const qty = parseNumber(document.querySelector(`.qty-${idx}`)?.value);
                    const unitCost = parseNumber(row.dataset.unitCost);
                    const cogsAccount = row.dataset.cogsAccountName || 'COGS';
                    const cogsCode = row.dataset.cogsAccountCode || '';
                    const assetAccount = row.dataset.assetAccountName || 'Inventory';
                    const assetCode = row.dataset.assetAccountCode || '';

                    if (type === 'inventory' && unitCost > 0 && qty > 0) {
                        const hpp = qty * unitCost;
                        // Debit COGS
                        journalRows.push({
                            account: `${cogsCode} - ${cogsAccount}`,
                            debit: hpp,
                            credit: 0
                        });
                        totalDebit += hpp;
                        // Credit Inventory
                        journalRows.push({
                            account: `${assetCode} - ${assetAccount}`,
                            debit: 0,
                            credit: hpp
                        });
                        totalCredit += hpp;
                    }
                });

                // ========================
                // 2 Freight GLOBAL → CREDIT
                // ========================
                const freight = parseNumber(freightInput.value);
                if (freight > 0) {
                    journalRows.push({
                        account: `${freightAccount.kode} - ${freightAccount.name}`,
                        debit: 0,
                        credit: freight
                    });
                    totalCredit += freight;
                }

                // ========================
                // 3 Pajak withholding (PPh) GLOBAL → DEBIT
                // ========================
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
                        debit: withholdingValue,
                        credit: 0
                    });
                    totalDebit += withholdingValue;
                }



                // ========================
                // 5️⃣ Render hasil
                // ========================
                if (journalRows.length === 0) {
                    journalBody.innerHTML =
                        `<tr><td colspan="3" class="text-center py-2 text-gray-500">Tidak ada journal</td></tr>`;
                } else {
                    journalRows.forEach(row => {
                        journalBody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="border px-2 py-1">${row.account}</td>
                    <td class="border px-2 py-1 text-right">${formatNumber(row.debit)}</td>
                    <td class="border px-2 py-1 text-right">${formatNumber(row.credit)}</td>
                </tr>
            `);
                    });
                }

                document.querySelector('.total-debit').textContent = formatNumber(totalDebit);
                document.querySelector('.total-credit').textContent = formatNumber(totalCredit);
            }


            function attachInputListeners(index) {
                ['qty', 'order', 'disc', 'purchase'].forEach(cls => {
                    const el = document.querySelector(`.${cls}-${index}`);
                    if (el) {
                        el.addEventListener('input', function() {
                            calculateBackOrder(index);
                            calculateAmount(index);
                        });
                    }
                });
                // khusus tax pakai change
                const taxEl = document.querySelector(`.tax-${index}`);
                if (taxEl) {
                    taxEl.addEventListener('change', function() {
                        calculateAmount(index);
                    });
                }
            }

            freightInput.addEventListener('input', calculateTotals);

            // toggle mode PO / manual
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

                fetch(`/sales_invoice/get-items/${orderId}`)
                    .then(res => res.json())
                    .then(data => {
                        data.items.forEach(item => addRowFromPO(item));
                        calculateTotals();
                    });
            });

            function addEmptyRow() {
                const index = rowIndex++;
                const row = `
                <tr class="item-row" 
                    data-index="${index}" 
                    data-type="" 
                    data-unit-cost="0" 
                    data-cogs-account-name="" 
                    data-asset-account-name="">
                    <td><select name="items[${index}][item_id]" data-index="${index}" class="w-full border rounded"></select></td>
                    <td><input type="text" name="items[${index}][description]" class="w-full border rounded  desc-${index}" readonly></td>
                    <td><input type="text" name="items[${index}][quantity]" class="w-full border rounded  qty-${index}"></td>
                    <td><input type="text" name="items[${index}][order_quantity]" class="w-full border bg-gray-100 rounded  order-${index}" readonly></td>
                    <td><input type="text" name="items[${index}][back_order]" class="w-full border bg-gray-100 rounded  back-${index}" readonly></td>
                    <td><input type="text" name="items[${index}][unit]" class="w-full border rounded  unit-${index}" readonly></td>
                    <td><input type="text" name="items[${index}][base_price]" class="w-full border rounded  purchase-${index}"></td>
                    <td><input type="text" name="items[${index}][discount]" class="w-full border rounded  disc-${index}"></td>
                    <td><input type="text" name="items[${index}][price]" class="w-full border rounded bg-gray-100  price-${index}" readonly></td>
                    <td><input type="text" name="items[${index}][amount]" class="w-full border bg-gray-100 rounded  amount-${index}" readonly></td>
                    <td>
                        <select name="items[${index}][tax_id]" class="tax-${index} w-full border rounded ">
                            <option value="">-- Pilih Pajak --</option>
                            @foreach ($sales_taxes as $item)
                                <option value="{{ $item->id }}" 
                                        data-rate="{{ $item->rate }}" 
                                        data-type="{{ $item->type }}" 
                                        data-account="{{ $item->sales_account_id }}"  
                                        data-account-code="{{ $item->salesAccount->kode_akun ?? '' }}"
                                        data-account-name="{{ $item->salesAccount->nama_akun ?? '' }}">
                                    ({{ $item->rate }}%)
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="items[${index}][tax_value]" class="taxval-${index} w-full border rounded bg-gray-100 text-right" readonly></td>
                    <td><input type="text" name="items[${index}][final]" class="final-${index} w-full border rounded text-right bg-gray-100" readonly></td>
                    <td><input type="text" class="w-full border bg-gray-100  account-name-${index}" readonly><input type="hidden" name="items[${index}][account_id]" class="account-id-${index}"></td>
                    <td>
                        <select name="items[${index}][project_id]" class="w-full border rounded ">
                            <option value="">-- Pilih Specpose --</option>
                            @foreach ($project as $prj)
                                <option value="{{ $prj->id }}">{{ $prj->nama_project }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center">
                    <button type="button" class="remove-row px-2 py-1 bg-red-500 text-white rounded" data-index="${index}">X</button>
                </td>
                </tr>`;
                tbody.insertAdjacentHTML('beforeend', row);
                attachSelect2(index);
                attachInputListeners(index);
            }

            function addRowFromPO(item) {
                const index = rowIndex++;
                const row = `
             <tr class="item-row" data-index="${index}" data-type="${item.type}" 
                data-unit-cost="${item.unit_cost}" 
                data-cogs-account-name="${item.cogs_account_name}" 
                data-cogs-account-code="${item.cogs_account_code}" 
                data-asset-account-name="${item.asset_account_name}"
                data-asset-account-code="${item.asset_account_code}">
                
                <td class="border px-2 py-1">
                    <input type="hidden" name="items[${index}][item_id]" value="${item.id}">
                    ${item.item_number ?? ''}
                </td>
                <td class="border px-2 py-1">
                    <input type="text" name="items[${index}][description]" value="${item.description ?? ''}" 
                        class="w-full border rounded" readonly>
                </td>
                <td class="border px-2 py-1">
                    <input type="text" name="items[${index}][quantity]" value="${formatNumber(item.quantity ?? 0)}" 
                        class="w-full border rounded qty-${index}">
                </td>
                <td class="border px-2 py-1">
                    <input type="text" name="items[${index}][order_quantity]" value="${formatNumber(item.order ?? 0)}" 
                        class="w-full bg-gray-100 border rounded order-${index}" readonly>
                </td>
                <td class="border px-2 py-1">
                    <input type="text" name="items[${index}][back_order]" value="${formatNumber(item.back_order ?? 0)}" 
                        class="w-full border bg-gray-100 rounded back-${index}" readonly>
                </td>
                <td class="border px-2 py-1">
                    <input type="text" name="items[${index}][unit]" value="${item.unit ?? ''}" 
                        class="w-full border rounded" readonly>
                </td>
                <td class="border px-2 py-1">
                    <input type="hidden" name="items[${index}][base_price]" class="base-hidden-${index}" value="${item.base_price ?? 0}">
                    <input type="text" value="${formatNumber(item.base_price ??0)}" class="w-full border rounded purchase-${index}">
                </td>
                <td class="border px-2 py-1">
                    <input type="text" name="items[${index}][discount]" value="${formatNumber(item.discount ?? 0)}" 
                        class="w-full border rounded disc-${index}">
                </td>
                <td class="border px-2 py-1">
                    <input type="text" name="items[${index}][price]" value="${formatNumber(item.price ?? 0)}" 
                        class="w-full bg-gray-100 border rounded price-${index}" readonly>
                </td>
                <td class="border px-2 py-1">
                    <input type="text" name="items[${index}][amount]" value="${formatNumber(item.amount ?? 0)}" 
                        class="w-full bg-gray-100 border rounded amount-${index}" readonly>
                </td>

                <!-- Pajak -->
                <td>
                    <select name="items[${index}][tax_id]" class="tax-${index} w-full border rounded">
                        <option value="">-- Pilih Pajak --</option>
                        @foreach ($sales_taxes as $tax)
                            <option value="{{ $tax->id }}" 
                                    data-rate="{{ $tax->rate }}"
                                    data-type="{{ $tax->type }}"
                                    data-account="{{ $tax->sales_account_id }}"  
                                    data-account-code="{{ $tax->salesAccount->kode_akun ?? '' }}"
                                    data-account-name="{{ $tax->salesAccount->nama_akun ?? '' }}">
                                {{ $tax->name }} ({{ $tax->rate }}%)
                            </option>
                        @endforeach
                    </select>
                </td>

                <td>
                    <input type="text" name="items[${index}][tax_value]" 
                        value="${formatNumber(item.tax ?? 0)}" 
                        class="taxval-${index} w-full bg-gray-100 border rounded text-right" readonly>
                </td>
                <td>
                    <input type="text" name="items[${index}][final]" 
                        value="${formatNumber(item.final ?? 0)}" 
                        class="final-${index} w-full bg-gray-100 border rounded text-right" readonly>
                </td>

                <td class="border px-2 py-1">
                    <input type="text" value="${item.account_code}-${item.account_name}" 
                        class="account-name-${index} w-full border rounded bg-gray-100" readonly>
                    <input type="hidden" name="items[${index}][account_id]" value="${item.account_id}">
                </td>

                <td class="border px-2 py-1">
                    <select name="items[${index}][project_id]" class="w-full border rounded px-2 py-1">
                        <option value="">-- Pilih Specpose --</option>
                        @foreach ($project as $prj)
                            <option value="{{ $prj->id }}">{{ $prj->nama_project }}</option>
                        @endforeach
                    </select>
                </td>

                <td class="text-center">
                    <button type="button" class="remove-row px-2 py-1 bg-red-500 text-white rounded" data-index="${index}">X</button>
                </td>
            </tr>`;

                tbody.insertAdjacentHTML('beforeend', row);

                // ✅ Set tax_id kalau ada dari SO
                const taxSelect = tbody.querySelector(`.tax-${index}`);
                if (item.tax_id) {
                    taxSelect.value = item.tax_id;
                }

                attachInputListeners(index);
                // hitung awal utk baris ini
                calculateBackOrder(index);
                calculateAmount(index);
            }

            function attachSelect2(index) {
                $(`select[data-index="${index}"]`).select2({
                    placeholder: 'Cari item...',
                    ajax: {
                        url: '/search-item',
                        dataType: 'json',
                        delay: 250,
                        data: params => ({
                            q: params.term,
                            context: 'sales', // ✅ supaya akun ambil revenue
                            location_id: $('#location_id').val() // ✅ ikutkan lokasi
                        }),
                        processResults: data => ({
                            results: data.map(item => ({
                                id: item.id,
                                text: `${item.item_number} - ${item.item_description}`,
                                item_name: item.item_description,
                                unit: item.unit,
                                purchase_price: item.purchase_price,
                                tax_rate: item.tax,
                                account_id: item.account_id,
                                account_name: item.account_name,
                                account_code: item.account_code,
                                stock_quantity: item.on_hand_qty,

                                // Tambahan utk HPP
                                type: item.type, // inventory / service
                                unit_cost: item.unit_cost, // dari controller
                                cogs_account_name: item.cogs_account_name,
                                asset_account_name: item.asset_account_name,
                                cogs_account_code: item.cogs_account_code,
                                asset_account_code: item.asset_account_code
                            }))
                        }),
                        cache: true
                    }
                }).on('select2:select', function(e) {
                    const data = e.params.data;

                    // ✅ Update input field
                    $(`.desc-${index}`).val(data.item_name);
                    $(`.unit-${index}`).val(data.unit);
                    // ($`.tax-${index}`).val(data.tax_rate); // biarkan user pilih pajak dari dropdown (id), jangan paksa rate
                    $(`.account-name-${index}`).val(`${data.account_code} - ${data.account_name}`);
                    $(`.account-id-${index}`).val(data.account_id);
                    $(`.qty-${index}`).val(formatNumber(data.stock_quantity));

                    // ✅ Update atribut data-* di <tr>
                    const tr = document.querySelector(`tr[data-index="${index}"]`);
                    tr.dataset.type = data.type || '';
                    tr.dataset.unitCost = data.unit_cost || 0;
                    tr.dataset.cogsAccountName = data.cogs_account_name || 'COGS';
                    tr.dataset.cogsAccountCode = data.cogs_account_code || '';
                    tr.dataset.assetAccountName = data.asset_account_name || 'Inventory';
                    tr.dataset.assetAccountCode = data.asset_account_code || '';

                    // Hitung ulang amount
                    calculateAmount(index);
                });
            }

            // function addRow() {
            //     const newRow = generateRow(rowIndex);
            //     $('#item-table-body').append(newRow);
            //     attachSelect2(rowIndex);
            //     attachEvents(rowIndex);
            //     rowIndex++;
            // }

            $('#add-row').on('click', function() {
                addEmptyRow();
            });

            // hapus row
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                calculateTotals();
            });

            if (!useOrderCheckbox.checked) {
                addEmptyRow();
            }
        });
    </script>

@endsection

@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp

            <div id="tabs" class="type-section">
                <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                    <li><a href="#select_item" class="tab-link active">Proces Sales invoice</a></li>
                    <li><a href="#journal_report" class="tab-link">Journal report</a></li>
                </ul>
            </div>
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form method="POST" action="{{ route('sales_invoice.update', $salesInvoice->id) }}">
                    @csrf
                    @method('PUT')

                    @if (session('error'))
                        <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                            {{ session('error') }}
                        </div>
                    @endif
                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Edit Sales Invoice
                    </h4>
                    <div id="select_item" class="tab-content">

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Payment Method</label>
                                <select id="jenis_pembayaran_id" name="jenis_pembayaran_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 ">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($jenis_pembayaran as $jenis)
                                        <option value="{{ $jenis->id }}"
                                            {{ $salesInvoice->jenis_pembayaran_id == $jenis->id ? 'selected' : '' }}>
                                            {{ $jenis->nama_jenis }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="account-wrapper" class="hidden">
                                <label class="font-medium text-gray-700 block mb-1">Account</label>
                                <select id="account_id" name="payment_method_account_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 ">
                                    <option value="">-- Pilih Account --</option>
                                </select>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Location Inventory</label>
                                <select name="location_id" id="location_id" disabled required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach ($location_inventory as $loc)
                                        <option value="{{ $loc->id }}"
                                            {{ $salesInvoice->location_id == $loc->id ? 'selected' : '' }}>
                                            {{ $loc->kode_lokasi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Invoice Number</label>
                                <input type="text" name="invoice_number"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    readonly value="{{ $salesInvoice->invoice_number }}" required>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Customer</label>
                                <select name="customers_id" id="customer_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach ($customers as $cust)
                                        <option value="{{ $cust->id }}"
                                            {{ $salesInvoice->customer_id == $cust->id ? 'selected' : '' }}>
                                            {{ $cust->nama_customers }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Order Number</label>
                                <input type="hidden" name="sales_order_id"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    value="{{ $salesInvoice->sales_order_id }}" required>
                                <input type="text"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    readonly value="{{ $salesInvoice->salesInvoice->order_number ?? 'Tidak menggunakan' }}"
                                    required>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Invoice Date</label>
                                <input type="date" name="invoice_date" readonly
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    value="{{ $salesInvoice->invoice_date }}" required>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Shipping Date</label>
                                <input type="date" name="shipping_date"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    value="{{ $salesInvoice->shipping_date }}">
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Sales Person</label>
                                <select name="sales_person_id" id="employee_id"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp->id }}"
                                            {{ $salesInvoice->sales_person_id == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->nama_karyawan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="font-medium text-gray-700 block mb-1">Shipping Address</label>
                                <textarea name="shipping_address" rows="2"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $salesInvoice->shipping_address }}</textarea>
                            </div>
                        </div>

                        {{-- TABEL ITEM --}}
                        <div class="mt-8">
                            <h3 class="font-semibold text-lg mb-2">🛒 Order Items</h3>
                            <div class="overflow-x-auto border rounded-lg shadow-sm mt-6">
                                <table class="min-w-max border-collapse border text-sm whitespace-nowrap">
                                    <thead
                                        class="bg-gradient-to-r bg-[{{ $themeColor }}]  to-blue-600 text-white text-sm font-semibold">
                                        <tr>
                                            <th class="p-2">Item</th>
                                            <th>Qty</th>
                                            <th>Order</th>
                                            <th>Back Order</th>
                                            <th>Unit</th>
                                            <th>Description</th>
                                            <th>Base Price</th>
                                            <th>Discount</th>
                                            <th>Price</th>
                                            <th>Amount</th>
                                            <th>Vat </th>
                                            <th>Vat Value</th>
                                            <th>Subtotal</th>
                                            <th>Account</th>
                                            <th>Specpose</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="item-table-body">
                                        @foreach ($salesInvoice->details as $i => $detail)
                                            <tr class="item-row bg-white even:bg-gray-50 border-b"
                                                data-index="{{ $i }}" data-type="{{ $detail->item_type }}"
                                                data-unit-cost="{{ $detail->computed_unit_cost ?? 0 }}"
                                                data-cogs-account-name="{{ $detail->cogs_account_name }}"
                                                data-cogs-account-code="{{ $detail->cogs_account_code }}"
                                                data-asset-account-name="{{ $detail->asset_account_name }}"
                                                data-asset-account-code="{{ $detail->asset_account_code }}">

                                                <!-- Item ID -->
                                                <td>
                                                    <input type="hidden" name="items[{{ $i }}][item_id]"
                                                        value="{{ $detail->item_id }}">
                                                    <input type="text" class="w-full border rounded"
                                                        value="{{ $detail->item->item_description }}">
                                                </td>

                                                <!-- Quantity -->
                                                <td>
                                                    <input type="number" name="items[{{ $i }}][quantity]"
                                                        class="w-full border rounded qty-{{ $i }}"
                                                        value="{{ $detail->quantity }}"
                                                        oninput="calculateBackOrder({{ $i }}); calculateAmount({{ $i }});">
                                                </td>

                                                <!-- Order -->
                                                <td>
                                                    <input type="number"
                                                        name="items[{{ $i }}][order_quantity]"
                                                        class="w-full border bg-gray-100 rounded order-{{ $i }}"
                                                        readonly value="{{ $detail->order_quantity }}"
                                                        oninput="calculateBackOrder({{ $i }}); calculateAmount({{ $i }});">
                                                </td>

                                                <!-- Back Order -->
                                                <td>
                                                    <input type="number" readonly
                                                        class="w-full border rounded bg-gray-100 back-{{ $i }}"
                                                        name="items[{{ $i }}][back_order]"
                                                        value="{{ $detail->back_order }}">
                                                </td>

                                                <!-- Unit -->
                                                <td>
                                                    <input type="text" readonly
                                                        class="w-full border rounded bg-gray-100"
                                                        name="items[{{ $i }}][unit]"
                                                        value="{{ $detail->unit }}">
                                                </td>

                                                <!-- Description -->
                                                <td>
                                                    <input type="text" readonly
                                                        class="w-full border rounded bg-gray-100"
                                                        name="items[{{ $i }}][description]"
                                                        value="{{ $detail->item->item_description }}">
                                                </td>

                                                <!-- Base Price -->
                                                <td>
                                                    <input type="text"
                                                        class="w-full border rounded text-right  base-display-{{ $i }}"
                                                        value="{{ number_format($detail->base_price, 0, '.', ',') }}">
                                                    <input type="hidden" name="items[{{ $i }}][base_price]"
                                                        class="base-hidden-{{ $i }}"
                                                        value="{{ $detail->base_price }}">
                                                </td>

                                                <!-- Discount -->
                                                <td>
                                                    <input type="text"
                                                        class="w-full border rounded text-right disc-display-{{ $i }}"
                                                        value="{{ number_format($detail->discount, 0, '.', ',') }}"
                                                        oninput="calculateAmount({{ $i }}); this.value = this.value.replace(/[^0-9,]/g,'');">
                                                    <input type="hidden" name="items[{{ $i }}][discount]"
                                                        class="disc-hidden-{{ $i }}"
                                                        value="{{ $detail->discount }}">
                                                </td>

                                                <!-- Price -->
                                                <td>
                                                    <input type="text" readonly
                                                        class="w-full border rounded text-right bg-gray-100 price-display-{{ $i }}"
                                                        value="{{ number_format($detail->price, 0, '.', ',') }}">
                                                    <input type="hidden" name="items[{{ $i }}][price]"
                                                        class="price-hidden-{{ $i }}"
                                                        value="{{ $detail->price }}">
                                                </td>

                                                <!-- Amount -->
                                                <td>
                                                    <input type="text" readonly
                                                        class="w-full border rounded text-right bg-gray-100 amount-display-{{ $i }}"
                                                        value="{{ number_format($detail->amount, 0, '.', ',') }}">
                                                    <input type="hidden" name="items[{{ $i }}][amount]"
                                                        class="amount-hidden-{{ $i }}"
                                                        value="{{ $detail->amount }}">
                                                </td>

                                                <!-- Tax -->
                                                <td>
                                                    <select name="items[{{ $i }}][tax_id]"
                                                        class="w-full border rounded tax-{{ $i }}">
                                                        <option value="">-- Pilih Pajak --</option>
                                                        @foreach ($sales_taxes as $tax)
                                                            <option value="{{ $tax->id }}"
                                                                data-rate="{{ $tax->rate }}"
                                                                data-type="{{ $tax->type }}"
                                                                data-account="{{ $tax->sales_account_id }}"
                                                                data-account-code="{{ $tax->salesAccount->kode_akun ?? '' }}"
                                                                data-account-name="{{ $tax->salesAccount->nama_akun ?? '' }}"
                                                                {{ $detail->tax_id == $tax->id ? 'selected' : '' }}>
                                                                ({{ $tax->rate }}%)
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>

                                                <!-- Tax Value -->
                                                <td>
                                                    <input type="text" readonly
                                                        class="w-full border rounded text-right bg-gray-100 taxval-display-{{ $i }}"
                                                        value="{{ number_format($detail->tax ?? 0, 0, '.', ',') }}">
                                                    <input type="hidden" name="items[{{ $i }}][tax_value]"
                                                        class="taxval-hidden-{{ $i }}"
                                                        value="{{ $detail->tax ?? 0 }}">
                                                </td>

                                                <!-- Final -->
                                                <td>
                                                    <input type="text" readonly
                                                        class="w-full border text-right rounded bg-gray-100 final-display-{{ $i }}"
                                                        value="{{ number_format($detail->final ?? 0, 0, '.', ',') }}">
                                                    <input type="hidden" name="items[{{ $i }}][final]"
                                                        class="final-hidden-{{ $i }}"
                                                        value="{{ $detail->final ?? 0 }}">
                                                </td>

                                                <!-- Account -->
                                                <td>
                                                    <input type="text" readonly
                                                        class="w-full border rounded bg-gray-100"
                                                        value="{{ optional($detail->account)->nama_akun }}">
                                                    <input type="hidden" name="items[{{ $i }}][account]"
                                                        value="{{ $detail->account_id }}">
                                                </td>


                                                <td>
                                                    <select name="items[{{ $i }}][project_id]"
                                                        class="w-full border rounded project-{{ $i }}">
                                                        <option value="">-- Pilih Specpose --</option>
                                                        @foreach ($project as $pro)
                                                            <option value="{{ $pro->id }}"
                                                                {{ $detail->project_id == $pro->id ? 'selected' : '' }}>
                                                                {{ $pro->nama_project }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>

                                                <!-- Remove -->
                                                {{-- <td class="text-center">
                                                    <button type="button" class="remove-row text-red-500 font-bold"
                                                        data-index="{{ $i }}">×</button>
                                                </td> --}}
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="9" class="text-right font-bold border px-2 py-1">Subtotal</td>
                                            <td colspan="2" class="border px-2 py-1">
                                                <input type="text" id="subtotal"
                                                    class="w-full border rounded text-right bg-gray-100" readonly>
                                            </td>
                                            <td colspan="3" class="border px-2 py-1"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="9" class="text-right font-bold border px-2 py-1">Tax
                                            </td>
                                            <td colspan="2" class="border px-2 py-1">
                                                <select name="withholding_tax" id="global-tax"
                                                    class="w-full border rounded text-right">
                                                    <option value="">--
                                                        Pilih Pajak --</option>
                                                    @foreach ($withholding as $tax)
                                                        <option value="{{ $tax->id }}"
                                                            data-rate="{{ $tax->rate }}"
                                                            data-type="{{ $tax->type }}"
                                                            data-account="{{ $tax->sales_account_id }}"
                                                            data-account-code="{{ $tax->salesAccount->kode_akun ?? '' }}"
                                                            data-account-name="{{ $tax->salesAccount->nama_akun ?? '' }}"
                                                            {{ $salesInvoice->withholding_tax == $tax->id ? 'selected' : '' }}>
                                                            ({{ $tax->rate }}%)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td colspan="3" class="border px-2 py-1"></td>
                                        </tr>
                                        <!-- tax value-->
                                        <tr>
                                            <td colspan="9" class="text-right font-bold border px-2 py-1">Tax Value
                                            </td>
                                            <td colspan="2" class="border px-2 py-1">
                                                <input type="text" id="global-tax-value" name="withholding_value"
                                                    class="w-full border rounded text-right bg-gray-100 mt-1"
                                                    value="{{ old('withholding_value', $salesInvoice->withholding_value ?? '') }}"
                                                    readonly>
                                            </td>
                                            <td colspan="3" class="border px-2 py-1"></td>
                                        </tr>
                                        <!-- Freight row -->
                                        <tr>
                                            <td colspan="9" class="text-right font-bold border px-2 py-1">Freight</td>
                                            <td colspan="2" class="border px-2 py-1">
                                                <input type="text" name="freight" id="freight"
                                                    class="w-full border rounded text-right"
                                                    value="{{ old('freight', $salesInvoice->freight ?? '') }}">
                                            </td>
                                            <td colspan="3" class="border px-2 py-1"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="9" class="text-right font-bold border px-2 py-1">Grand Total
                                            </td>
                                            <td colspan="2" class="border px-2 py-1">
                                                <input type="text" id="grand-total"
                                                    class="w-full border rounded text-right bg-gray-100 font-bold"
                                                    readonly>
                                            </td>
                                            <td colspan="3" class="border px-2 py-1"></td>
                                        </tr>
                                    </tfoot>
                                </table>

                            </div>
                        </div>

                        {{-- Info Tambahan --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Early Payment Terms</label>
                                <input type="text" name="early_payment_terms"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    value="{{ $salesInvoice->early_payment_terms }}">
                            </div>
                            <div class="md:col-span-2">
                                <label class="font-medium text-gray-700 block mb-1">Messages</label>
                                <textarea name="messages" rows="3"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $salesInvoice->messages }}</textarea>
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

                    {{-- Tombol --}}
                    <div class="mt-8 flex justify-end space-x-4">
                        <a href="{{ route('sales_invoice.index') }}"
                            class="px-6 py-2 bg-gray-300 rounded-lg shadow hover:bg-gray-400 transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition">
                            Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
        document.addEventListener('DOMContentLoaded', function() {

            const $pmSelect = $('#jenis_pembayaran_id');
            const $account = $('#account_id');
            const $wrapper = $('#account-wrapper');

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

                        // ✅ Preselect account lama setelah option terisi
                        setTimeout(() => {
                            const oldVal =
                                "{{ old('payment_method_account_id', $salesInvoice->payment_method_account_id ?? '') }}";
                            if (oldVal) {
                                $account.val(oldVal).trigger('change');
                            }
                            $wrapper.removeClass('hidden');
                            // re-render jurnal setelah akun termuat
                            generateJournalPreview();
                        }, 100);
                    })
                    .fail(function() {
                        clearAccounts();
                        alert('Gagal memuat account untuk Payment Method ini.');
                    });
            }

            // Event change
            $pmSelect.on('change', function() {
                loadAccounts($(this).val());
            });

            // Ketika user memilih account secara manual, update jurnal
            $account.on('change', generateJournalPreview);

            const freightAccount = {
                id: "{{ $freightAccount->akun_id ?? '' }}",
                kode: "{{ $freightAccount->akun->kode_akun ?? '-' }}",
                name: "{{ $freightAccount->akun->nama_akun ?? 'Freight Revenue' }}"
            };

            // ✅ Auto load saat edit
            if ($pmSelect.val()) {
                loadAccounts($pmSelect.val());
            }

            // Helper functions
            const formatNumber = num => new Intl.NumberFormat('id-ID').format(num);

            // ✅ Versi aman untuk format Indonesia
            const parseNumber = (val) => {
                if (val === null || val === undefined) return 0;
                let s = val.toString().trim();
                if (s === '') return 0;

                // Jika ada kedua separator: asumsikan format id-ID "1.234,56"
                if (s.includes('.') && s.includes(',')) {
                    s = s.replace(/\./g, '').replace(',', '.');
                } else if (s.includes(',')) {
                    // Hanya koma: tentukan dia ribuan atau desimal
                    // Jika koma diikuti tepat 3 digit (pattern ribuan), buang semua koma
                    if (/,(\d{3})(?!\d)/.test(s)) {
                        s = s.replace(/,/g, '');
                    } else {
                        // anggap sebagai desimal
                        s = s.replace(',', '.');
                    }
                } else {
                    // Hanya titik: tentukan ribuan atau desimal
                    if (/\.(\d{3})(?!\d)/.test(s)) {
                        s = s.replace(/\./g, '');
                    } // else: biarkan titik sebagai desimal
                }

                const n = Number(s);
                return Number.isFinite(n) ? n : 0;
            };


            function calculateAmount(index) {
                // 🔁 BACA dari DISPLAY, bukan hidden
                const baseDisplayEl = document.querySelector(`.base-display-${index}`);
                const discDisplayEl = document.querySelector(`.disc-display-${index}`);

                const qty = parseNumber(document.querySelector(`.qty-${index}`)?.value);
                const basePrice = parseNumber(baseDisplayEl?.value);
                const discount = parseNumber(discDisplayEl?.value);

                // ✅ sinkronkan ke hidden supaya saat submit, server terima angka benar
                const baseHiddenEl = document.querySelector(`.base-hidden-${index}`);
                const discHiddenEl = document.querySelector(`.disc-hidden-${index}`);
                if (baseHiddenEl) baseHiddenEl.value = basePrice;
                if (discHiddenEl) discHiddenEl.value = discount;

                const price = Math.max(basePrice - discount, 0);
                const amount = price * qty;

                const taxSelect = document.querySelector(`.tax-${index}`);
                const taxRate = parseFloat(taxSelect?.selectedOptions[0]?.dataset.rate || 0);
                const taxType = taxSelect?.selectedOptions[0]?.dataset.type || 'input_tax';

                let taxValue = (amount * taxRate) / 100;
                let finalValue = amount;

                if (taxType === 'input_tax') {
                    finalValue += taxValue;
                } else if (taxType === 'withholding_tax') {
                    finalValue = Math.max(amount - taxValue, 0);
                }

                // Update display & hidden hasil hitung
                document.querySelector(`.price-display-${index}`).value = formatNumber(price);
                document.querySelector(`.price-hidden-${index}`).value = price;

                document.querySelector(`.amount-display-${index}`).value = formatNumber(amount);
                document.querySelector(`.amount-hidden-${index}`).value = amount;

                document.querySelector(`.taxval-display-${index}`).value = formatNumber(taxValue);
                document.querySelector(`.taxval-hidden-${index}`).value = taxValue;

                document.querySelector(`.final-display-${index}`).value = formatNumber(finalValue);
                document.querySelector(`.final-hidden-${index}`).value = finalValue;

                calculateTotals();
            }


            function calculateTotals() {
                let subtotal = 0,
                    totalTax = 0,
                    grandTotal = 0;
                totalInputTax = 0;

                document.querySelectorAll('tr.item-row').forEach(row => {
                    const index = row.dataset.index;
                    const final = parseNumber(document.querySelector(`.final-hidden-${index}`)?.value);
                    const amount = parseNumber(document.querySelector(`.amount-hidden-${index}`)?.value);
                    const taxVal = parseNumber(document.querySelector(`.taxval-hidden-${index}`)?.value);
                    const taxType = document.querySelector(`.tax-${index}`)?.selectedOptions[0]?.dataset
                        .type || 'input_tax';

                    subtotal += final;
                    if (taxType === 'input_tax') totalInputTax += taxVal;
                });

                const freight = parseNumber(document.getElementById('freight')?.value);
                const globalTaxSelect = document.getElementById('global-tax');
                const globalTaxRate = parseNumber(globalTaxSelect?.selectedOptions[0]?.dataset.rate);
                const withholdingValueInput = document.getElementById('global-tax-value');

                const withholdingValue = subtotal * (globalTaxRate / 100);
                withholdingValueInput.value = formatNumber(withholdingValue);

                grandTotal = subtotal - withholdingValue + freight;

                document.getElementById('subtotal').value = formatNumber(subtotal);
                document.getElementById('grand-total').value = formatNumber(grandTotal);

                generateJournalPreview();
            }

            document.getElementById('global-tax').addEventListener('change', calculateTotals);

            function generateJournalPreview() {
                const journalBody = document.querySelector('.journal-body');
                const freightInput = document.getElementById('freight');
                const grandTotalInput = document.getElementById('grand-total');
                journalBody.innerHTML = '';

                let journalRows = [];
                let totalDebit = 0,
                    totalCredit = 0;

                document.querySelectorAll('#item-table-body .item-row').forEach(row => {
                    const idx = row.dataset.index;
                    const accountName =
                        row.querySelector('td:nth-last-child(2) input[type="text"]')?.value || 'Item';
                    const amount = parseNumber(document.querySelector(`.amount-hidden-${idx}`)?.value);
                    const taxAmount = parseNumber(document.querySelector(`.taxval-hidden-${idx}`)?.value);

                    // ===== DEBUG HEADER PER ITEM =====
                    // console.groupCollapsed(`🧾 Item #${idx}: ${accountName}`);
                    // console.log('Row data:', {
                    //     type: row.dataset.type,
                    //     qty: document.querySelector(`.qty-${idx}`)?.value,
                    //     unitCost: row.dataset.unitCost,
                    //     amount,
                    //     taxAmount
                    // });

                    // ✅ Payment → Debit
                    const paymentOption = document.querySelector('#account_id option:checked');
                    const paymentAccountName = paymentOption?.text?.trim() || '';
                    const grandTotal = parseNumber(grandTotalInput.value);

                    // console.log('Debug payment:', {
                    //     paymentAccountName,
                    //     grandTotal
                    // });

                    if (grandTotal > 0 && paymentAccountName) {
                        journalRows.push({
                            account: paymentAccountName,
                            debit: grandTotal,
                            credit: 0
                        });
                        totalDebit += grandTotal;
                    } else if (!paymentAccountName) {
                        // console.warn('⚠️ Payment account kosong — pastikan sudah memilih metode pembayaran.');
                    }


                    // Pajak
                    if (taxAmount > 0) {
                        const taxSelect = document.querySelector(`.tax-${idx}`);
                        const opt = taxSelect?.selectedOptions[0];
                        const taxType = opt?.dataset.type || 'input_tax';
                        const taxAccountName = opt?.dataset.accountName || 'Tax';
                        const taxAccountCode = opt?.dataset.accountCode || '';
                        if (!opt?.dataset.type) {
                            // console.warn(
                            //     `⚠️ [Item #${idx}] Tax type undefined. Check SalesTaxes.type in DB or HTML data-type attribute.`
                            // );
                        }

                        // console.log('Tax detail:', {
                        //     taxType,
                        //     taxAccountName,
                        //     rate: opt?.dataset.rate,
                        //     accountId: opt?.dataset.account
                        // });

                        if (taxType === 'withholding_tax') {
                            journalRows.push({
                                account: `${taxAccountCode}-${taxAccountName}`,
                                debit: taxAmount,
                                credit: 0
                            });
                            totalDebit += taxAmount;
                        } else {
                            journalRows.push({
                                account: `${taxAccountCode}-${taxAccountName}`,
                                debit: 0,
                                credit: taxAmount
                            });
                            totalCredit += taxAmount;
                        }
                    }
                    // Freight → Credit
                    const freight = parseNumber(freightInput.value);
                    if (freight > 0) {
                        journalRows.push({
                            account: `${freightAccount.kode}-${freightAccount.name}`,
                            debit: 0,
                            credit: freight
                        });
                        totalCredit += freight;
                    }

                    // HPP / Inventory
                    const type = row.dataset.type;
                    const qty = parseNumber(document.querySelector(`.qty-${idx}`)?.value);
                    const unitCost = parseNumber(row.dataset.unitCost);
                    const cogsAccount = row.dataset.cogsAccountName || 'COGS';
                    const cogsCode = row.dataset.cogsAccountCode || '';
                    const assetAccount = row.dataset.assetAccountName || 'Inventory';
                    const assetCode = row.dataset.assetAccountCode || '';

                    if (type === 'inventory' && unitCost > 0 && qty > 0) {
                        const hpp = qty * unitCost;
                        // console.log(`💰 HPP aktif: ${hpp} (${qty} x ${unitCost})`);

                        journalRows.push({
                            account: `${cogsCode}-${cogsAccount}`,
                            debit: hpp,
                            credit: 0
                        });
                        totalDebit += hpp;
                        journalRows.push({
                            account: `${assetCode}-${assetAccount}`,
                            debit: 0,
                            credit: hpp
                        });
                        totalCredit += hpp;
                    } else {
                        // console.warn(`⚠️ [Item #${idx}] COGS skipped. Reason(s):`, {
                        //     type,
                        //     unitCost,
                        //     qty,
                        //     condition: !(type === 'inventory' && unitCost > 0 && qty > 0) ?
                        //         'type!=inventory or unitCost/qty=0' : 'ok'
                        // });
                    }
                    // Pendapatan → Credit
                    if (amount > 0) {
                        journalRows.push({
                            account: accountName,
                            debit: 0,
                            credit: amount
                        });
                        totalCredit += amount;
                    }
                    const globalTaxSelect = document.getElementById('global-tax');
                    const withholdingRate = parseNumber(globalTaxSelect?.selectedOptions[0]?.dataset.rate);
                    const withholdingValue = parseNumber(document.getElementById('global-tax-value')
                        ?.value);
                    if (withholdingValue > 0) {
                        const withholdingAccountName = globalTaxSelect?.selectedOptions[0]?.dataset
                            .accountName ||
                            'PPh Dipotong';
                        const withholdingAccountCode = globalTaxSelect?.selectedOptions[0]?.dataset
                            .accountCode ||
                            'PPH';

                        journalRows.push({
                            account: `${withholdingAccountCode} - ${withholdingAccountName}`,
                            debit: withholdingValue,
                            credit: 0
                        });
                        totalDebit += withholdingValue;
                    }

                    // console.groupEnd();
                });





                // Render tabel
                if (journalRows.length === 0) {
                    journalBody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center py-2 text-gray-500">
                    Tidak ada journal
                </td>
            </tr>
        `;
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

                // console.groupCollapsed('📘 JOURNAL SUMMARY');
                // console.log('Journal Rows:', journalRows);
                // console.log('Total Debit:', totalDebit);
                // console.log('Total Credit:', totalCredit);
                // console.log('Balance:', totalDebit - totalCredit);
                // console.groupEnd();
            }


            // Event binding untuk semua input yang relevan
            document.querySelectorAll('tr.item-row').forEach(row => {
                const index = row.dataset.index;

                // ✅ Binding untuk Quantity
                const qtyInput = row.querySelector('.qty-' + index);
                if (qtyInput) qtyInput.addEventListener('input', () => calculateAmount(index));

                // Base Price, Discount, Order, Tax select
                ['.base-display-', '.disc-display-', '.order-'].forEach(prefix => {
                    const el = row.querySelector(prefix + index);
                    if (el) el.addEventListener('input', () => calculateAmount(index));
                });

                const taxSelect = row.querySelector('.tax-' + index);
                if (taxSelect) taxSelect.addEventListener('change', () => calculateAmount(index));

                // Jalankan perhitungan awal untuk baris ini
                calculateAmount(index);
            });

            // Update total kalau Freight berubah
            document.getElementById('freight')?.addEventListener('input', calculateTotals);
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
@endsection

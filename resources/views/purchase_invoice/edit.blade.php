@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <div id="tabs" class="type-section">
                    <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                        <li><a href="#select_item" class="tab-link active">Proces purchase invoice</a></li>
                        <li><a href="#journal_report" class="tab-link">Journal report</a></li>
                    </ul>
                </div>
                <form method="POST" action="{{ route('purchase_invoice.update', $purchaseInvoice->id) }}">
                    @csrf
                    @method('PUT')

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

                        <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                            Purchase Invoice Edit
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Invoice Number</label>
                                <input type="text" name="invoice_number"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500s"
                                    value="{{ $purchaseInvoice->invoice_number }}" readonly>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Payment Method</label>
                                <select id="jenis_pembayaran_id" name="jenis_pembayaran_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 ">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($jenis_pembayaran as $jenis)
                                        <option value="{{ $jenis->id }}"
                                            {{ $purchaseInvoice->jenis_pembayaran_id == $jenis->id ? 'selected' : '' }}>
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
                                <label class="font-medium text-gray-700 block mb-1">location Inventory</label>
                                <select name="location_id" id="location_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500s">
                                    @foreach ($locationInventory as $loc)
                                        <option value="{{ $loc->id }}"
                                            {{ $purchaseInvoice->location_id == $loc->id ? 'selected' : '' }}>
                                            {{ $loc->kode_lokasi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Vendor</label>
                                <select name="vendor_id" id="vendor_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500s">
                                    @foreach ($vendor as $ven)
                                        <option value="{{ $ven->id }}"
                                            {{ $purchaseInvoice->vendor_id == $ven->id ? 'selected' : '' }}>
                                            {{ $ven->nama_vendors }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Order Number</label>
                                <input type="hidden" name="purchase_order_id"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500s"
                                    value="{{ $purchaseInvoice->purchase_order_id }}" required>
                                <input type="text"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500s"
                                    value="{{ $purchaseInvoice->purchaseOrder->order_number ?? '-' }}" readonly>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Invoice Date</label>
                                <input type="date" name="date_invoice"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500s"
                                    value="{{ $purchaseInvoice->date_invoice }}" required>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Shipping Date</label>
                                <input type="date" name="shipping_date"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500s"
                                    value="{{ $purchaseInvoice->shipping_date }}">
                            </div>
                            <div class="md:col-span-2">
                                <label class="font-medium text-gray-700 block mb-1">Shipping Address</label>
                                <textarea name="shipping_address" rows="2"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500s">{{ $purchaseInvoice->shipping_address }}</textarea>
                            </div>

                        </div>

                        {{-- TABEL ITEM --}}
                        <div class="mt-8">
                            <h3 class="font-semibold text-lg mb-2">🛒 Order Items</h3>
                            <div class="overflow-auto">
                                <table class="w-full border text-sm text-left shadow-md">
                                    @php
                                        $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                                    @endphp
                                    <thead
                                        class="bg-gradient-to-r bg-[{{ $themeColor }}]  to-blue-600 text-white text-sm font-semibold">
                                        <tr>
                                            <th class=" border px-4 py-2 p-2">Item</th>
                                            <th class="border px-4 py-2">Qty</th>
                                            <th class="border px-4 py-2">Order</th>
                                            {{-- <th class="border px-4 py-2">Back Order</th> --}}
                                            <th class="border px-4 py-2">Unit</th>
                                            <th class="border px-4 py-2">Description</th>
                                            <th class="border px-4 py-2">Price</th>
                                            <th class="border px-4 py-2">Discount</th>
                                            <th class="border px-4 py-2">Tax</th>
                                            <th class="border px-4 py-2">Tax Amount</th>
                                            <th class="border px-4 py-2">Amount</th>
                                            <th class="border px-4 py-2">Account</th>
                                            <th class="border px-4 py-2">Specpose</th>
                                            <th class="border px-4 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="item-table-body">
                                        @foreach ($purchaseInvoice->details as $i => $detail)
                                            <tr class="bg-white even:bg-gray-50 border-b">
                                                <!-- Item -->
                                                <td>
                                                    <input type="hidden" name="items[{{ $i }}][item_id]"
                                                        value="{{ $detail->item_id }}">
                                                    <input type="text" readonly
                                                        class="w-full border px-2 py-1 bg-gray-50"
                                                        value="{{ $detail->item->item_description }}">
                                                </td>

                                                <!-- Qty -->
                                                <td>
                                                    <input type="number" name="items[{{ $i }}][quantity]"
                                                        class="qty-{{ $i }} w-full border px-2 py-1"
                                                        value="{{ $detail->quantity }}">
                                                </td>

                                                <!-- Order -->
                                                <td>
                                                    <input type="number" name="items[{{ $i }}][order]"
                                                        class="order-{{ $i }} w-full border px-2 py-1"
                                                        value="{{ $detail->order }}">
                                                </td>

                                                <!-- Back Order -->
                                                {{-- <td>
                                                    <input type="number" readonly
                                                        class="back-{{ $i }} w-full border px-2 py-1 bg-gray-50"
                                                        name="items[{{ $i }}][back_order]"
                                                        value="{{ $detail->back_order }}">
                                                </td> --}}

                                                <!-- Unit -->
                                                <td>
                                                    <input type="text" readonly
                                                        class="w-full border px-2 py-1 bg-gray-50"
                                                        name="items[{{ $i }}][unit]"
                                                        value="{{ $detail->unit }}">
                                                </td>

                                                <!-- Description -->
                                                <td>
                                                    <input type="text" readonly
                                                        class="w-full border px-2 py-1 bg-gray-50"
                                                        name="items[{{ $i }}][item_description]"
                                                        value="{{ $detail->item_description }}">
                                                </td>

                                                <!-- Price -->
                                                <td>
                                                    <input type="text"
                                                        class="price-{{ $i }} w-full border px-2 py-1 text-right"
                                                        name="items[{{ $i }}][price]"
                                                        value="{{ $detail->price }}">
                                                </td>
                                                {{-- discount --}}
                                                <td>
                                                    <input type="text"
                                                        class="discount-{{ $i }} w-full border px-2 py-1 text-right"
                                                        name="items[{{ $i }}][discount]"
                                                        value="{{ $detail->discount }}">
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
                                                <td>
                                                    <input type="text" readonly
                                                        class="tax_amount-display-{{ $i }} w-full border px-2 py-1 bg-gray-50 text-right"
                                                        value="{{ number_format($detail->tax_amount, 0, ',', '.') }}">
                                                    <input type="hidden" class="tax_amount-hidden"
                                                        name="items[{{ $i }}][tax_amount]"
                                                        value="{{ $detail->tax_amount }}">
                                                </td>

                                                <!-- Amount -->
                                                <td>
                                                    <input type="text" readonly
                                                        class="amount-display-{{ $i }} w-full border px-2 py-1 bg-gray-50 text-right"
                                                        value="{{ number_format($detail->amount, 0, ',', '.') }}">
                                                    <input type="hidden" class="amount-hidden"
                                                        name="items[{{ $i }}][amount]"
                                                        value="{{ $detail->amount }}">
                                                </td>

                                                <!-- Account -->
                                                <td>
                                                    <input type="text" readonly
                                                        class="w-full border px-2 py-1 bg-gray-50"
                                                        value="{{ optional($detail->account)->kode_akun }}-{{ optional($detail->account)->nama_akun }}">
                                                    <input type="hidden" name="items[{{ $i }}][account_id]"
                                                        value="{{ $detail->account_id }}">
                                                </td>

                                                <!-- Specpose -->
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
                                            <td colspan="8"></td>
                                            <td class="pr-3 text-right font-semibold">Subtotal :</td>
                                            <td><input type="text" id="subtotal" readonly
                                                    class="w-full border rounded text-right px-2 py-1 bg-gray-100"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="8"></td>
                                            <td class="pr-3 text-right font-semibold">Tax :</td>
                                            <td class="border px-2 py-1">
                                                <select name="withholding_tax" id="global-tax"
                                                    class="w-full border rounded text-right">
                                                    <option value="">--
                                                        Pilih Pajak --</option>
                                                    @foreach ($withholding as $tax)
                                                        <option value="{{ $tax->id }}"
                                                            data-rate="{{ $tax->rate }}"
                                                            data-type="{{ $tax->type }}"
                                                            data-account="{{ $tax->purchase_account_id }}"
                                                            data-account-code="{{ $tax->purchaseAccount->kode_akun ?? '' }}"
                                                            data-account-name="{{ $tax->purchaseAccount->nama_akun ?? '' }}"
                                                            {{ $purchaseInvoice->withholding_tax == $tax->id ? 'selected' : '' }}>
                                                            ({{ $tax->rate }}%)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        <!-- tax value-->
                                        <tr>
                                            <td colspan="8"></td>
                                            <td class="pr-3 text-right font-semibold">Tax Value :</td>
                                            <td class="border px-2 py-1">
                                                <input type="text" id="global-tax-value" name="withholding_value"
                                                    class="w-full border rounded text-right bg-gray-100 mt-1"
                                                    value="{{ old('withholding_value', $purchaseInvoice->withholding_value ?? '') }}"
                                                    readonly>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="8"></td>
                                            <td class="pr-3 text-right font-semibold">Freight :</td>
                                            <td>
                                                <input type="hidden" id="freight" name="freight"
                                                    value="{{ $purchaseInvoice->freight ?? 0 }}">
                                                <input type="text" id="freight-display"
                                                    class="w-full border rounded text-right px-2 py-1 bg-gray-100"
                                                    value="{{ number_format($purchaseInvoice->freight ?? 0, 0, '.', ',') }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="8"></td>
                                            <td class="pr-3 text-right font-semibold">Grand Total :</td>
                                            <td><input type="text" id="grand-total" readonly
                                                    class="w-full border rounded text-right px-2 py-1 bg-gray-100 font-bold">
                                            </td>
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
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500s"
                                    value="{{ $purchaseInvoice->early_payment_terms }}">
                            </div>
                            <div class="md:col-span-2">
                                <label class="font-medium text-gray-700 block mb-1">Messages</label>
                                <textarea name="messages" rows="3"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500s">{{ $purchaseInvoice->messages }}</textarea>
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
                        <a href="{{ route('purchase_invoice.index') }}"
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

                        // ✅ Preselect account lama
                        const oldVal =
                            "{{ old('payment_method_account_id', $purchaseInvoice->payment_method_account_id ?? '') }}";
                        if (oldVal) {
                            $account.val(oldVal);
                        }

                        $wrapper.removeClass('hidden');
                        generateJournalPreview(); // update journal setelah load
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

            // ✅ Auto load saat edit
            if ($pmSelect.val()) {
                loadAccounts($pmSelect.val());
            }

            // ====== Calculation & Journal Preview ======
            function calculateBackOrder(index) {
                const qty = parseFloat($(`.qty-${index}`).val()) || 0;
                const order = parseFloat($(`.order-${index}`).val()) || 0;
                // $(`.back-${index}`).val(qty - order);
            }

            function calculateAmount(index) {
                const qty = parseFloat($(`.qty-${index}`).val()) || 0;
                const order = parseFloat($(`.order-${index}`).val()) || 0;
                const price = parseFloat($(`.price-${index}`).val()) || 0;
                const discount = parseFloat($(`.discount-${index}`).val()) || 0;
                const taxSelect = document.querySelector(`.tax-${index}`);
                const taxPercent = parseFloat($(`.tax-${index} option:selected`).data('rate')) || 0;
                const taxType = taxSelect?.selectedOptions[0]?.dataset.type || 'input_tax';
                const baseAmount = (price - discount) * qty;
                let taxAmount = (baseAmount * taxPercent) / 100;
                let finalValue = baseAmount;

                if (taxType === 'input_tax') {
                    finalValue += taxAmount; // PPN → tambah
                } else if (taxType === 'withholding_tax') {
                    finalValue -= taxAmount; // PPh → kurang
                    if (finalValue < 0) finalValue = 0;
                }

                $(`.amount-display-${index}`).val(formatNumber(baseAmount));
                $(`.tax_amount-display-${index}`).val(formatNumber(taxAmount));

                $(`.amount-hidden[name="items[${index}][amount]"]`).val(baseAmount);
                $(`.tax_amount-hidden[name="items[${index}][tax_amount]"]`).val(taxAmount);


                let $finalHidden = $(`.final-hidden-${index}`);
                if ($finalHidden.length === 0) {
                    // kalau belum ada hidden final, tambahkan dinamis
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

                // Hitung subtotal dari final (bukan dari amount)
                $('.final-hidden').each(function() {
                    subtotal += parseNumber($(this).val());
                });

                // Hitung total pajak (opsional)
                $('.tax_amount-hidden').each(function(index) {
                    totalTax += parseNumber($(this).val());
                });

                // Ambil withholding global
                const globalTaxSelect = document.getElementById('global-tax');
                const globalTaxRate = parseNumber(globalTaxSelect?.selectedOptions[0]?.dataset.rate);
                const withholdingValue = subtotal * (globalTaxRate / 100);
                $('#global-tax-value').val(formatNumber(withholdingValue));

                // Freight
                const freight = parseNumber($('#freight').val());

                // Grand total
                const grandTotal = subtotal - withholdingValue + freight;

                // Update tampilan
                $('#subtotal').val(formatNumber(subtotal));
                $('#grand-total').val(formatNumber(grandTotal));
                $('#freight-display').val(formatNumber(freight));
            }
            document.getElementById('global-tax').addEventListener('change', calculateTotals);

            function generateJournalPreview() {
                const $journalBody = $('.journal-body');
                $journalBody.empty();

                let totalDebit = 0,
                    totalCredit = 0,
                    rows = [];

                // Item rows
                $('tbody#item-table-body tr').each(function(index) {
                    const amount = parseFloat($(this).find('.amount-hidden').val()) || 0;
                    const taxAmount = parseFloat($(this).find('.tax_amount-hidden').val()) || 0;
                    const accountName = $(this).find('input[name$="[account_id]"]').siblings(
                        'input[type=text]').val();

                    const $taxSelect = $(this).find('select[name$="[tax_id]"]');
                    const $taxOption = $taxSelect.find('option:selected');
                    const taxAccountName = $taxOption.data('account-name') || $taxOption.text();
                    const taxAccountCode = $taxOption.data('account-code') || '';
                    const taxType = $taxOption.data('type') || 'input_tax';

                    // Item utama
                    if (amount > 0) {
                        rows.push({
                            accountCode: taxAccountCode,
                            account: accountName,
                            debit: amount,
                            credit: 0
                        });
                        totalDebit += amount;
                    }

                    // Pajak: sesuaikan arah jurnal
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

                // Freight
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
                const withholdingValue = parseNumber(document.getElementById('global-tax-value')
                    ?.value);
                if (withholdingValue > 0) {
                    const withholdingAccountName = globalTaxSelect?.selectedOptions[0]?.dataset
                        .accountName ||
                        'PPh Dipotong';
                    const withholdingAccountCode = globalTaxSelect?.selectedOptions[0]?.dataset
                        .accountCode ||
                        'PPH';

                    rows.push({
                        account: `${withholdingAccountCode} - ${withholdingAccountName}`,
                        debit: 0,
                        credit: withholdingValue
                    });
                    totalCredit += withholdingValue;
                }

                // Payment Method Account
                const paymentAccount = $('#account_id option:selected').text();
                const grandTotal = parseFloat($('#grand-total').val().replace(/,/g, '')) || 0;
                if (grandTotal > 0 && paymentAccount) {
                    rows.push({
                        account: paymentAccount,
                        debit: 0,
                        credit: grandTotal
                    });
                    totalCredit += grandTotal;
                }

                // Render rows
                if (rows.length === 0) {
                    $journalBody.append(
                        '<tr><td colspan="3" class="text-center py-2 text-gray-500">Tidak ada journal</td></tr>'
                    );
                } else {
                    rows.forEach(r => {
                        $journalBody.append(`
                <tr>
                    <td class="border px-2 py-1">${r.account}</td>
                    <td class="border px-2 py-1 text-right">${r.debit.toLocaleString()}</td>
                    <td class="border px-2 py-1 text-right">${r.credit.toLocaleString()}</td>
                </tr>
            `);
                    });
                }

                $('.total-debit').text(totalDebit.toLocaleString());
                $('.total-credit').text(totalCredit.toLocaleString());
            }


            // Delegation
            $(document).on('input',
                'input[name^="items"][name$="[quantity]"], input[name^="items"][name$="[order]"], input[name^="items"][name$="[price]"]',
                function() {
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

            // 🔹 Tambah listener untuk account header berubah
            $('#account_id').on('change', function() {
                generateJournalPreview();
            });

            // Initial load
            @foreach ($purchaseInvoice->details as $i => $detail)
                calculateAmount({{ $i }});
            @endforeach
            calculateTotals();
            generateJournalPreview();
        });
    </script>
@endsection

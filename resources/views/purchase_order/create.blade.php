@extends('layouts.app')
@section('content')
    <div class="py-10">
        <div class="w-full px-1">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">

                <!-- Judul Menu -->
                <h2 class="text-base font-bold mb-2">Purchase Order</h2>
                <form method="POST"
                    action="{{ isset($purchase_order) ? route('purchase_order.update', $purchase_order->id) : route('purchase_order.store') }}">
                    @csrf
                    @if (isset($purchase_order))
                        @method('PUT')
                    @endif

                    @if ($errors->any())
                        <div class="mb-2 text-red-600 bg-red-100 p-2 rounded-md text-sm">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-4 gap-2 text-xs">
                        <!-- Vendor -->
                        <div>
                            <label class="block font-medium mb-1">Vendor<span class="text-red-500">*</span></label>

                            <select id="vendor_id" name="vendor_id"
                                class="w-full border border-gray-300 rounded px-2 py-1 bg-gray-50 text-xs" required>
                                @if (isset($purchase_order) && $purchase_order->vendor)
                                    <option value="{{ $purchase_order->vendor->id }}" selected>
                                        {{ $purchase_order->vendor->nama_vendors }}
                                    </option>
                                @endif
                            </select>
                        </div>



                        <!-- Payment Method -->
                        {{-- Kolom Kiri: Payment Method --}}
                        <div>
                            <label class="block font-medium mb-1">Payment Method<span class="text-red-500">*</span></label>
                            <select id="jenis_pembayaran_id" name="jenis_pembayaran_id"
                                class="w-full border rounded px-2 py-1 text-sm" required>
                                <option value="">-- Payment Method --</option>
                                @foreach ($jenis_pembayaran as $level)
                                    <option value="{{ $level->id }}"
                                        {{ old('jenis_pembayaran_id', $purchase_order->jenis_pembayaran_id ?? '') == $level->id ? 'selected' : '' }}>
                                        {{ $level->nama_jenis }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kolom Kanan: Account (otomatis terisi, 1 saja) --}}
                        <div id="pm-account-panel"
                            class="{{ old('jenis_pembayaran_id', $purchase_order->jenis_pembayaran_id ?? '') ? '' : 'hidden' }}">
                            <label class="block font-medium mb-1">Account<span class="text-red-500">*</span></label>
                            <select id="pm-account-id" name="account_id" class="w-full border rounded px-2 py-1 text-sm"
                                required>
                                <option value="">-- Pilih Account --</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium mb-1">Location Inventory<span
                                    class="text-red-500">*</span></label>
                            <select id="location_id" name="location_id" class="w-full border rounded px-2 py-1 text-sm"
                                required>
                                <option value="">-- Location Inventory --</option>
                                @foreach ($locationInventory as $level)
                                    <option value="{{ $level->id }}"
                                        {{ old('location_id', $purchase_order->location_id ?? '') == $level->id ? 'selected' : '' }}>
                                        {{ $level->kode_lokasi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>



                        <!-- Order Number -->
                        <div>
                            <label for="order_number" class="block text-gray-700 font-medium mb-1">Order
                                Number<span class="text-red-500">*</span></label>
                            <input type="text" id="order_number" name="order_number"
                                value="{{ old('order_number', $purchase_order->order_number ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                            <label class="inline-flex items-center mt-2">
                                <input type="checkbox" id="auto_generate" name="auto_generate" value="1"
                                    class="form-checkbox text-blue-600" onchange="toggleAutoGenerate()">
                                <span class="ml-2 text-sm text-gray-700">Generate Order Number secara otomatis</span>
                            </label>

                            @error('order_number')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date Order -->
                        <div>
                            <label for="date_order" class="block text-gray-700 font-medium mb-1">Date Order<span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="date_order" required
                                value="{{ old('date_order', $purchase_order->date_order ?? now()->toDateString()) }}"
                                class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">

                        </div>

                        <!-- Shipping Date -->
                        <div>
                            <label for="shipping_date" class="block text-gray-700 font-medium mb-1">Shipping
                                Date<span class="text-red-500"></span></label>
                            <input type="date" name="shipping_date"
                                value="{{ old('shipping_date', $purchase_order->shipping_date ?? now()->toDateString()) }}"
                                class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">

                        </div>
                        <!-- Shipping Address -->
                        <div class="col-span-2">
                            <label for="shipping_address" class="block text-gray-700 font-medium mb-1">Shipping
                                Address <span class="text-red-500"></span></label>
                            <textarea id="shipping_address" name="shipping_address" rows="2" placeholder="Masukkan shipping date"
                                class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('shipping_address', $purchase_order->shipping_address ?? '') }}</textarea>
                            @error('shipping_address')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Order Items Table -->
                    <div class="mt-4">
                        <h3 class="text-base font-semibold mb-2">Order Items</h3>
                        <div class="overflow-x-auto border rounded shadow-sm">
                            <table class="min-w-full table-auto border-collapse text-xs text-left">
                                @php
                                    $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                                @endphp
                                <thead
                                    class="bg-gradient-to-r bg-[{{ $themeColor }}]  to-blue-600 text-white text-sm font-semibold">
                                    <tr>
                                        <th class="border px-2 py-1 w-40">Item</th>
                                        <th class="border px-2 py-1 w-16 text-center">Order</th>
                                        <th class="border px-2 py-1 w-20">Unit</th>
                                        <th class="border px-2 py-1 w-40">Description</th>
                                        <th class="border px-2 py-1 w-24 text-right">Price</th>
                                        <th class="border px-2 py-1 w-24 text-right">Discount</th>
                                        <th class="border px-2 py-1 w-20 text-right">Tax</th>
                                        <th class="border px-2 py-1 w-24 text-right">Tax Amt</th>
                                        <th class="border px-2 py-1 w-28 text-right">Amount</th>
                                        <th class="border px-2 py-1 w-28">Account</th>
                                        <th class="border px-2 py-1 w-28 tex-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="item-table-body" class="bg-white">
                                    <!-- Dynamic rows by JS -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8"></td>
                                        <td class="pr-3 text-right font-semibold">Subtotal :</td>
                                        <td><input type="text" id="subtotal" readonly
                                                class="w-32 border rounded text-right px-2 py-1 bg-gray-100"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="8"></td>

                                        <td class="pr-3 text-right font-semibold">Total Tax :</td>
                                        <td><input type="text" id="grand-tax" readonly
                                                class="w-32 border rounded text-right px-2 py-1 bg-gray-100"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="8"></td>
                                        <td class="pr-3 text-right font-semibold">Freight :</td>
                                        <td>
                                            <!-- input asli (hidden) -->
                                            <input type="hidden" id="freight" name="freight" value="0">

                                            <!-- input tampilan -->
                                            <input type="text" id="freight-display"
                                                class="w-32 border rounded text-right px-2 py-1 bg-gray-100">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="8"></td>
                                        <td class="pr-3 text-right font-semibold">Total :</td>
                                        <td><input type="text" id="grand-total" readonly
                                                class="w-32 border rounded text-right px-2 py-1 bg-gray-100 font-bold">
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>


                    <!-- Button Tambah -->
                    <div class="mt-2">
                        <button type="button" id="add-row"
                            class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700">
                            + Tambah Baris
                        </button>
                    </div>
                    <!-- Early Payment Terms -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div class="mb-2">
                            <label for="early_payment_terms" class="block text-gray-700 font-medium mb-1">Early Payments
                                Terms</label>
                            <input type="text" name="early_payment_terms"
                                value="{{ old('early_payment_terms', $purchase_order->early_payment_terms ?? '') }}"
                                class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>

                        <!-- Messages -->
                        <div class="mb-2">
                            <label for="messages" class="block text-gray-700 font-medium mb-1">Messages</label>
                            <textarea id="messages" name="messages" rows="2"
                                class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('messages', $purchase_order->messages ?? '') }}</textarea>
                        </div>
                    </div>
                    <!-- Buttons -->
                    <div class="mt-6 flex justify-end space-x-4">

                        <a href="{{ route('purchase_order.index') }}"
                            class="px-3 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-3 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition">
                            {{ isset($purchase_orders) ? 'Update' : 'Process' }}
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
    {{-- <script>
        $(document).ready(function() {
            $('#jenis_pembayaran_id').select2({
                placeholder: "-- Pilih --",
                allowClear: true,
                width: '100%'
            });
        });
    </script> --}}


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
        function toggleAutoGenerate() {
            const checkbox = document.getElementById('auto_generate');
            const invoiceInput = document.getElementById('order_number');

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
                            "{{ old('account_detail_coa_id', $purchase_order->account_detail_coa_id ?? '') }}";
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
        let rowIndex = 0;

        function generateRow(index) {
            return `
        <tr class="item-row" data-index="${index}">
            <td class="border px-2 py-1">
                <select name="items[${index}][item_id]" class="item-select w-full border rounded" data-index="${index}"></select>
            </td>
            <td class="border px-2 py-1"><input type="number" name="items[${index}][order]" class="order-${index} w-full border rounded" /></td>
            <td class="border px-2 py-1"><input type="text" name="items[${index}][unit]" class="unit-${index} w-full border bg-gray-100 rounded" readonly /></td>
            <td class="border px-2 py-1"><input type="text" name="items[${index}][description]" class="desc-${index} w-full border bg-gray-100 rounded" readonly  /></td>
            <td class="border px-2 py-1"><input type="text" name="items[${index}][price]" class="price-${index} w-full border rounded text-right"  /></td>
            <td class="border px-2 py-1"><input type="text" name="items[${index}][discount]" class="disc-${index} w-full border rounded text-right"  /></td>
            <td class="border px-2 py-1">
              <select name="items[${index}][tax_id]" class="tax-${index} w-full border rounded">
                <option value="">-- Pilih Pajak --</option>
                @foreach ($sales_taxes as $item)
                    <option value="{{ $item->id }}" data-rate="{{ $item->rate }}" data-type="{{ $item->type }}">
                       ({{ $item->rate }}%)
                    </option>
                @endforeach
            </select>
            </td>
            <td class="border px-2 py-1"><input type="text" name="items[${index}][tax_amount]" class="tax_amount-${index} w-full border bg-gray-100 rounded text-right" readonly /></td>
            <td class="border px-2 py-1"><input type="text" name="items[${index}][amount]" class="amount-${index} w-full border rounded bg-gray-100 text-right" readonly /></td>
            <td class="border px-2 py-1">
                <input type="text" class="w-full border rounded bg-gray-100 account-name-${index}" readonly />
                <input type="hidden" name="items[${index}][account]" class="account-id-${index}" />
            </td>
            <td class="border px-2 py-1 text-center">
                <button type="button" class="remove-row px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600" data-index="${index}">X</button>
            </td>
        </tr>`;
        }

        function attachSelect2(index) {
            $(`select[data-index="${index}"]`).select2({
                placeholder: 'Cari item...',
                width: '100%',
                ajax: {
                    url: '/search-item',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        q: params.term,
                        context: 'purchase',
                        location_id: $('#location_id').val() // ⬅️ biar controller tahu ini untuk purchase
                    }),
                    processResults: data => ({
                        results: data.map(item => ({
                            id: item.id,
                            text: `${item.item_number} - ${item.item_description}`,
                            item_description: item.item_description,
                            unit: item.unit,
                            purchase_price: item.purchase_price ?? 0,
                            tax_rate: item.tax_rate,
                            account_id: item.account_id,
                            account_name: item.account_name,
                            stock_quantity: item.on_hand_qty
                        }))
                    }),
                    cache: true
                }
            }).on('select2:select', function(e) {
                const data = e.params.data;
                console.log("Data dari server:", data);
                $(`.desc-${index}`).val(data.item_description);
                $(`.unit-${index}`).val(data.unit);
                $(`.price-${index}`).val(formatNumber(data.purchase_price));
                $(`.tax-${index}`).val(data.tax_rate);
                $(`.account-name-${index}`).val(data.account_name);
                $(`.account-id-${index}`).val(data.account_id);
                calculateAmount(index);
                calculateBackOrder(index);
            });
        }

        // ✅ Event delegation agar semua row (lama & baru) terupdate real-time
        $(document).on('input change', '.item-row input, .item-row select', function() {
            const index = $(this).closest('tr').data('index');
            calculateAmount(index);
            calculateBackOrder(index);
        });

        function calculateBackOrder(index) {
            const qty = parseFloat($(`.qty-${index}`).val()) || 0;
            const order = parseFloat($(`.order-${index}`).val()) || 0;
            const backOrder = qty - order;
            $(`.back-${index}`).val(backOrder >= 0 ? backOrder : 0);
        }

        function calculateAmount(index) {
            const order = parseFloat($(`.order-${index}`).val()) || 0;
            const price = parseFloat(cleanNumber($(`.price-${index}`).val())) || 0;
            const discount = parseFloat(cleanNumber($(`.disc-${index}`).val())) || 0;
            const $selectedTax = $(`.tax-${index} option:selected`);
            const taxRate = parseFloat($selectedTax.data('rate')) || 0;
            const taxType = $selectedTax.data('type') || 'input_tax';

            const baseAmount = (price - discount) * order;

            let taxValue = (baseAmount * taxRate) / 100;
            let finalValue = baseAmount;

            if (taxType === 'input_tax') {
                // contoh: PPN → tambah
                finalValue = baseAmount + taxValue;
            } else if (taxType === 'withholding_tax') {
                // contoh: PPh → kurang
                finalValue = baseAmount - taxValue;
                // jangan sampai negatif
                if (finalValue < 0) finalValue = 0;
            }
            $(`.tax_amount-${index}`).val(formatNumber(taxValue));
            $(`.amount-${index}`).val(formatNumber(finalValue));

            updateTotal();
        }

        function updateTotal() {
            let subtotal = 0;
            let totalInputTax = 0; // PPN (tambah)
            let totalWithholdingTax = 0; // PPh (kurang)

            $('.item-row').each(function() {
                const index = $(this).data('index');
                const order = parseFloat($(`.order-${index}`).val()) || 0;
                const price = cleanNumber($(`.price-${index}`).val());
                const discount = cleanNumber($(`.disc-${index}`).val());
                const $selectedTax = $(`.tax-${index} option:selected`);
                const taxType = $selectedTax.data('type') || 'input_tax';
                const taxAmount = cleanNumber($(`.tax_amount-${index}`).val());

                subtotal += (price - discount) * order;

                // Bedakan tipe pajak
                if (taxType === 'input_tax') {
                    totalInputTax += taxAmount; // PPN -> menambah total
                } else if (taxType === 'withholding_tax') {
                    totalWithholdingTax += taxAmount; // PPh -> mengurangi total
                }
            });

            const freight = cleanNumber($('#freight').val());

            const grandTotal = subtotal + totalInputTax - totalWithholdingTax + freight;
            const totalTax = totalInputTax - totalWithholdingTax; // tampilkan neto di field tax

            $('#subtotal').val(formatNumber(subtotal));
            $('#grand-tax').val(formatNumber(totalTax));
            $('#grand-total').val(formatNumber(grandTotal));
        }

        // event input di display
        $('#freight-display').on('input', function() {
            let raw = cleanNumber($(this).val());
            let value = parseFloat(raw) || 0;
            $('#freight').val(value); // simpan nilai asli ke hidden input
            $(this).val(formatNumber(value)); // tampilkan sudah diformat
            updateTotal();
        });

        $(document).on('blur', 'input[class*="price-"], input[class*="disc-"]', function() {
            const val = cleanNumber($(this).val());
            $(this).val(formatNumber(val));
        });


        function addRow() {
            const newRow = generateRow(rowIndex);
            $('#item-table-body').append(newRow);
            attachSelect2(rowIndex);
            rowIndex++;
        }

        $('#add-row').on('click', function() {
            addRow();
        });

        $(document).on('click', '.remove-row', function() {
            const index = $(this).data('index');
            $(`tr[data-index="${index}"]`).remove();
            updateTotal();
        });

        $(document).ready(function() {
            addRow();
        });

        function cleanNumber(value) {
            return parseFloat(value.toString().replace(/[^0-9.-]+/g, "")) || 0;
        }

        function formatNumber(value) {
            return parseFloat(value).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        $('form').on('submit', function() {
            $('.item-row').each(function() {
                const index = $(this).data('index');
                $(`.price-${index}`).val(cleanNumber($(`.price-${index}`).val()).toFixed(2));
                $(`.amount-${index}`).val(cleanNumber($(`.amount-${index}`).val()).toFixed(2));
                $(`.tax_amount-${index}`).val(cleanNumber($(`.tax_amount-${index}`).val()).toFixed(2));
            });
            $('#grand-total').val(cleanNumber($('#grand-total').val()).toFixed(2));
            $('#grand-tax').val(cleanNumber($('#grand-tax').val()).toFixed(2));
        });
    </script>


@endsection

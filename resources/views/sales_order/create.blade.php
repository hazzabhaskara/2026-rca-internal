@extends('layouts.app')

@section('content')

    <div class="py-4">
        <div class="w-full px-2">
            <div class="bg-white shadow rounded p-4">

                <!-- Judul Menu -->
                <h2 class="text-lg font-bold mb-4">Sales Order</h2>

                <form method="POST"
                    action="{{ isset($sales_order) ? route('sales_order.update', $sales_order->id) : route('sales_order.store') }}">
                    @csrf
                    @if (isset($sales_order))
                        @method('PUT')
                    @endif

                    <!-- Error -->
                    @if ($errors->any())
                        <div class="mb-2 text-red-600 bg-red-100 p-2 rounded text-sm">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Form Grid -->
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <!-- Customers -->
                        <div>
                            <label class="block font-medium mb-1">Customers</label>
                            <select name="customer_id" id="customer_id" class="w-full border rounded px-2 py-1 text-sm"
                                required>
                                <option value="">-- Customers --</option>
                                @foreach ($customer as $level)
                                    <option value="{{ $level->id }}"
                                        {{ old('customer_id', $sales_order->customer_id ?? '') == $level->id ? 'selected' : '' }}>
                                        {{ $level->nama_customers }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium mb-1">Location Inventory</label>
                            <select name="location_id" id="location_id" class="w-full border rounded px-2 py-1 text-sm"
                                required>
                                <option value="">-- Location --</option>
                                @foreach ($lokasi_inventory as $level)
                                    <option value="{{ $level->id }}"
                                        {{ old('location_id', $sales_order->location_id ?? '') == $level->id ? 'selected' : '' }}>
                                        {{ $level->kode_lokasi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Payment -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Kolom Kiri: Payment Method --}}
                            <div>
                                <label class="block font-medium mb-1">Payment Method</label>
                                <select id="jenis_pembayaran_id" name="jenis_pembayaran_id"
                                    class="w-full border rounded px-2 py-1 text-sm" required>
                                    <option value="">-- Payment Method --</option>
                                    @foreach ($jenis_pembayaran as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('jenis_pembayaran_id', $sales_order->jenis_pembayaran_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->nama_jenis }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Kolom Kanan: Account (otomatis terisi, 1 saja) --}}
                            <div id="pm-account-panel"
                                class="{{ old('jenis_pembayaran_id', $sales_order->jenis_pembayaran_id ?? '') ? '' : 'hidden' }}">
                                <label class="block font-medium mb-1">Account</label>
                                <select id="pm-account-id" name="payment_method_account_id" required
                                    class="w-full border rounded px-2 py-1 text-sm">
                                    <option value="">-- Pilih Account --</option>
                                </select>
                            </div>

                        </div>

                        <!-- Employee -->
                        <div>
                            <label class="block font-medium mb-1">Employee</label>
                            <select name="sales_person_id" id="employee_id" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Employee --</option>
                                @foreach ($employee as $level)
                                    <option value="{{ $level->id }}"
                                        {{ old('employee_id', $sales_order->employee_id ?? '') == $level->id ? 'selected' : '' }}>
                                        {{ $level->nama_karyawan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Order Number -->
                        <div>
                            <label for="order_number" class="block text-gray-700 font-medium mb-1">Order
                                Number</label>
                            <input type="text" id="order_number" name="order_number"
                                value="{{ old('order_number', $sales_order->order_number ?? '') }}"
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
                            <label class="block font-medium mb-1">Date Order</label>
                            <input type="date" name="date_order"
                                value="{{ old('date_order', $sales_order->date_order ?? now()->toDateString()) }}"
                                class="w-full border rounded px-2 py-1 text-sm" required>
                        </div>

                        <!-- Shipping Date -->
                        <div>
                            <label class="block font-medium mb-1">Shipping Date</label>
                            <input type="date" name="shipping_date"
                                value="{{ old('shipping_date', $sales_order->shipping_date ?? now()->toDateString()) }}"
                                class="w-full border rounded px-2 py-1 text-sm">
                        </div>

                        <!-- Shipping Address -->
                        <div class="col-span-3">
                            <label class="block font-medium mb-1">Shipping Address</label>
                            <textarea name="shipping_address" rows="2" class="w-full border rounded px-2 py-1 text-sm">{{ old('shipping_address', $sales_order->shipping_address ?? '') }}</textarea>
                        </div>
                    </div>

                    <!-- Order Items Table -->
                    <div class="mt-4">
                        <h3 class="font-semibold text-sm mb-2">Order Items</h3>
                        <div class="overflow-x-auto border rounded-lg shadow-sm mt-6">
                            <table class="min-w-max border-collapse border text-sm whitespace-nowrap">

                                @php
                                    $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                                    $textFooter = \App\Setting::get('text_footer', 'ANTS LITE+ ©2025_AN NAML CORP.');
                                @endphp
                                <thead
                                    class="bg-gradient-to-r bg-[{{ $themeColor }}]  to-blue-600 text-white text-sm font-semibold">
                                    <tr>
                                        <th class="border px-2 py-1 w-80">Item</th>
                                        {{-- <th class="border px-2 py-1">Qty</th> --}}
                                        <th class="border px-2 py-1">Order</th>
                                        {{-- <th class="border px-2 py-1">BackOrder</th> --}}
                                        <th class="border px-2 py-1">Unit</th>
                                        <th class="border px-2 py-1">Desc</th>
                                        <th class="border px-2 py-1">Base Price</th>
                                        <th class="border px-2 py-1">Disc</th>
                                        <th class="border px-2 py-1">Price</th>
                                        <th class="border px-2 py-1">Amount</th>
                                        <th class="border px-2 py-1">Tax Rate</th>
                                        <th class="border px-2 py-1">Tax Value</th>
                                        <th class="border px-2 py-1">Final</th>
                                        <th class="border px-2 py-1">Account</th>
                                        <th class="border px-2 py-1">#</th>
                                    </tr>
                                </thead>
                                <tbody id="item-table-body" class="divide-y divide-gray-200"></tbody>
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
                                        <td colspan="9" class="text-right font-bold border px-2 py-1">Total Pajak</td>
                                        <td colspan="2" class="border px-2 py-1">
                                            <input type="text" id="total-tax"
                                                class="w-full border rounded text-right bg-gray-100" readonly>
                                        </td>
                                        <td colspan="3" class="border px-2 py-1"></td>
                                    </tr>
                                    <!-- Freight row -->
                                    <tr>
                                        <td colspan="9" class="text-right font-bold border px-2 py-1">Freight</td>
                                        <td colspan="2" class="border px-2 py-1">
                                            <input type="text" name="freight" id="freight"
                                                class="w-full border rounded text-right"
                                                value="{{ old('freight', $sales_order->freight ?? '') }}">
                                        </td>
                                        <td colspan="3" class="border px-2 py-1"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="text-right font-bold border px-2 py-1">Grand Total</td>
                                        <td colspan="2" class="border px-2 py-1">
                                            <input type="text" id="grand-total"
                                                class="w-full border rounded text-right bg-gray-100 font-bold" readonly>
                                        </td>
                                        <td colspan="3" class="border px-2 py-1"></td>
                                    </tr>
                                </tfoot>

                            </table>
                        </div>
                        <button type="button" id="add-row"
                            class="mt-2 px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">+ Add
                            Row</button>
                    </div>
                    <!-- Bottom Form -->
                    <div class="grid grid-cols-3 gap-4 text-sm mt-4">
                        <div>
                            <label class="block font-medium mb-1">Early Payment Terms</label>
                            <input type="text" name="early_payment_terms"
                                value="{{ old('early_payment_terms', $sales_order->early_payment_terms ?? '') }}"
                                class="w-full border rounded px-2 py-1 text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block font-medium mb-1">Messages</label>
                            <textarea name="messages" rows="2" class="w-full border rounded px-2 py-1 text-sm">{{ old('messages', $sales_order->messages ?? '') }}</textarea>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="mt-4 flex justify-end gap-2 space-x-2">

                        <a href="{{ route('sales_order.index') }}"
                            class="px-4 py-1 bg-gray-300 text-sm text-gray-700 rounded hover:bg-gray-400">Cancel</a>
                        <button type="submit"
                            class="px-4 py-1 bg-green-600 text-white text-sm font-semibold rounded hover:bg-blue-700">
                            {{ isset($sales_order) ? 'Update' : 'Process' }}
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
                            "{{ old('account_detail_coa_id', $sales_order->account_detail_coa_id ?? '') }}";
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
            <td class="border px-2 py-1 odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition">
                <select name="items[${index}][item_id]" class="item-select w-full border rounded" data-index="${index}"></select>
            </td>
            <td class="border px-2 py-1 odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition"><input type="number" name="items[${index}][order]" class="order-${index} w-full border rounded" /></td>
            <td class="border px-2 py-1 odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition"><input type="text" name="items[${index}][unit]" class="unit-${index} w-full border rounded" /></td>
            <td class="border px-2 py-1 odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition"><input type="text" name="items[${index}][description]" class="desc-${index} w-full border rounded" /></td>

            <td class="border px-2 py-1 odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition"><input type="text" name="items[${index}][base_price]" class="base-${index} w-full border rounded text-right" /></td>
            <td class="border px-2 py-1 odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition"><input type="text" name="items[${index}][discount]" class="disc-${index} w-full border rounded text-right" value="0" /></td>

            <td class="border px-2 py-1 odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition"><input type="text" name="items[${index}][price]" class="price-${index} w-full border rounded text-right" readonly /></td>
            <td class="border px-2 py-1 odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition"><input type="text" name="items[${index}][amount]" class="amount-${index} w-full border rounded text-right" readonly /></td>

            <!-- Tax dropdown -->
           <td class="border px-2 py-1 odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition">
              <select name="items[${index}][tax_id]" class="tax-${index} w-full border rounded">
                <option value="">-- Pilih Pajak --</option>
                @foreach ($sales_taxes as $item)
                    <option value="{{ $item->id }}" data-rate="{{ $item->rate }}" data-type="{{ $item->type }}">
                        ({{ $item->rate }}%)
                    </option>
                @endforeach
            </select>
            </td>

            <!-- Nilai pajak -->
            <td class="border px-2 py-1 odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition">
                <input type="text" name="items[${index}][tax_value]" class="taxval-${index} w-full border rounded text-right" readonly />
            </td>

            <!-- Final (amount + tax) -->
            <td class="border px-2 py-1 odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition">
                <input type="text" name="items[${index}][final]" class="final-${index} w-full border rounded text-right" readonly />
            </td>

            <td class="border px-2 py-1 odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition">
                <input type="text" class="w-full border rounded bg-gray-100 account-name-${index}" />
                <input type="hidden" name="items[${index}][account]" class="account-id-${index}" />
            </td>
            <td class="border px-2 py-1 odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition text-center">
                <button type="button" class="remove-row px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600" data-index="${index}">X</button>
            </td>
        </tr>`;
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
                            tax_rate: item.tax_rate,
                            account_id: item.account_id,
                            account_name: item.account_name,
                            stock_quantity: item.on_hand_qty,

                            // Tambahan utk HPP
                            type: item.type, // inventory / service
                            unit_cost: item
                                .unit_cost, // sudah dihitung di controller per lokasi
                            cogs_account_name: item.cogs_account_name,
                            asset_account_name: item.asset_account_name
                        }))
                    }),
                    cache: true
                }
            }).on('select2:select', function(e) {
                const data = e.params.data;

                // ✅ Update input field
                $(`.desc-${index}`).val(data.item_name);
                $(`.unit-${index}`).val(data.unit);
                $(`.tax-${index}`).val(data.tax_rate);
                $(`.account-name-${index}`).val(data.account_name);
                $(`.account-id-${index}`).val(data.account_id);
                $(`.qty-${index}`).val(formatNumber(data.stock_quantity));

                // ✅ Update atribut data-* di <tr>
                const tr = document.querySelector(`tr[data-index="${index}"]`);
                tr.dataset.type = data.type || '';
                tr.dataset.unitCost = data.unit_cost || 0;
                tr.dataset.cogsAccountName = data.cogs_account_name || 'COGS';
                tr.dataset.assetAccountName = data.asset_account_name || 'Inventory';

                // Hitung ulang amount
                calculateAmount(index);
            });
        }


        function attachEvents(index) {
            // Input numerik yang mempengaruhi amount
            $(`.qty-${index}, .order-${index}, .disc-${index}, .base-${index}`)
                .on('input', function() {
                    calculateAmount(index);
                    calculateBackOrder(index);
                })
                .on('blur', function() {
                    // Format ulang angka saat selesai input
                    const cls = $(this).attr("class");
                    let val = parseNumber($(this).val());
                    if (cls.includes("base-") || cls.includes("disc-")) {
                        $(this).val(formatNumber(val));
                    }
                });

            // Dropdown pajak harus trigger hitung ulang
            $(`.tax-${index}`).on('change', function() {
                calculateAmount(index);
            });
        }

        function calculateBackOrder(index) {
            const qty = parseFloat($(`.qty-${index}`).val()) || 0;
            const order = parseFloat($(`.order-${index}`).val()) || 0;
            const backOrder = qty - order;
            $(`.back-${index}`).val(backOrder >= 0 ? backOrder : 0);
        }

        function calculateAmount(index) {
            const order = parseFloat($(`.order-${index}`).val()) || 0;
            const base_price = parseNumber($(`.base-${index}`).val());
            const disc = parseNumber($(`.disc-${index}`).val());

            // Subtotal kotor
            const price = base_price - disc;
            const subtotal = order * price;

            // Total setelah diskon
            const amount = Math.max(subtotal, 0);

            // Harga akhir per unit
            const pricePerUnit = order > 0 ? amount / order : 0;

            $(`.price-${index}`).val(formatNumber(pricePerUnit));
            $(`.amount-${index}`).val(formatNumber(amount));

            // Pajak dihitung dari amount
            const $selectedTax = $(`.tax-${index} option:selected`);
            const taxRate = parseFloat($selectedTax.data('rate')) || 0;
            const taxType = $selectedTax.data('type') || 'input_tax';

            let taxValue = (amount * taxRate) / 100;
            let finalValue = amount;

            if (taxType === 'input_tax') {
                // contoh: PPN → tambah
                finalValue = amount + taxValue;
            } else if (taxType === 'withholding_tax') {
                // contoh: PPh → kurang
                finalValue = amount - taxValue;
                // jangan sampai negatif
                if (finalValue < 0) finalValue = 0;
            }

            $(`.taxval-${index}`).val(formatNumber(taxValue));
            $(`.final-${index}`).val(formatNumber(finalValue));

            updateTotal();
        }

        function updateTotal() {
            let total = 0;
            let totalTax = 0;
            let grandTotal = 0;


            $('.item-row').each(function() {
                const index = $(this).data('index');
                const amount = parseNumber($(`.amount-${index}`).val());

                const $selectedTax = $(`.tax-${index} option:selected`);
                const taxVal = parseNumber($(`.taxval-${index}`).val());
                const taxType = $selectedTax.data('type') || 'input_tax';

                total += amount;

                if (taxType === 'input_tax') {
                    // contoh PPN → ditambahkan
                    totalTax += taxVal;
                    grandTotal += (amount + taxVal);
                } else if (taxType === 'withholding_tax') {
                    // contoh PPh → dikurangkan
                    totalTax -= taxVal;
                    grandTotal += (amount - taxVal);
                } else {
                    // fallback
                    grandTotal += amount;
                }
            });

            // Freight
            const freight = parseNumber(document.getElementById('freight')?.value);

            // Update fields
            $('#subtotal').val(formatNumber(total));
            $('#total-tax').val(formatNumber(totalTax));
            $('#grand-total').val(formatNumber(grandTotal + freight));
        }


        function addRow() {
            const newRow = generateRow(rowIndex);
            $('#item-table-body').append(newRow);
            attachSelect2(rowIndex);
            attachEvents(rowIndex);
            rowIndex++;
        }

        $('#add-row').on('click', function() {
            addRow();
        });
        $('#freight').on('input', function() {
            updateTotal();
        }).on('blur', function() {
            let val = parseNumber($(this).val());
            $(this).val(formatNumber(val));
        });


        $(document).on('click', '.remove-row', function() {
            const index = $(this).data('index');
            $(`tr[data-index="${index}"]`).remove();
            updateTotal();
        });

        $(document).ready(function() {
            addRow();
        });

        // Format angka tampilan
        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0, // ✅ tanpa angka di belakang koma
                maximumFractionDigits: 0
            }).format(Number(num) || 0);
        }

        // Parse dari "1.234,56" -> 1234.56
        function parseNumber(value) {
            if (value === null || typeof value === 'undefined') return 0;
            return parseFloat(value.toString().replace(/\./g, '').replace(',', '.')) || 0;
        }

        // Saat submit: kirim angka mentah (decimal) ke server
        $('form').on('submit', function() {
            // Jika punya grand total, ubah ke decimal 2 angka
            if ($('#grand-total').length) {
                const total = parseNumber($('#grand-total').val());
                $('#grand-total').val(total.toFixed(2));
            }

            $('.item-row').each(function() {
                const index = $(this).data('index');

                const price = parseNumber($(`.price-${index}`).val());
                const amount = parseNumber($(`.amount-${index}`).val());
                const base = parseNumber($(`.base-${index}`).val());
                const discount = parseNumber($(`.disc-${index}`).val());
                const taxVal = parseNumber($(`.taxval-${index}`).val());
                const finalVal = parseNumber($(`.final-${index}`).val());
                // NOTE: .tax-${index} adalah <select>, jangan diubah ke toFixed!
                // Biarkan value-nya tetap "0" / "11" / "12"

                $(`.price-${index}`).val(price.toFixed(2));
                $(`.amount-${index}`).val(amount.toFixed(2));
                $(`.base-${index}`).val(base.toFixed(2));
                $(`.disc-${index}`).val(discount.toFixed(2));
                $(`.taxval-${index}`).val(taxVal.toFixed(2));
                $(`.final-${index}`).val(finalVal.toFixed(2));
            });
        });
    </script>

@endsection

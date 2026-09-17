@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="w-full px-2">
            <div class="bg-white shadow rounded p-4">
                <div id="tabs" class="type-section">
                    <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                        <li><a href="#select_item" class="tab-link active">Process Payment Expense</a></li>
                        <li><a href="#journal_report" class="tab-link">Journal Report</a></li>
                    </ul>
                </div>

                <h2 class="text-lg font-bold mb-4">Payment Expense</h2>

                <form method="POST"
                    action="{{ isset($payment_expense) ? route('payment_expense.update', $payment_expense->id) : route('payment_expense.store') }}">
                    @csrf
                    @if (isset($payment_expense))
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

                    <!-- Tab Content -->
                    <div class="tab-content" id="select_item">

                        <!-- Header Form -->
                        <div class="grid grid-cols-3 gap-4 text-sm">
                            <div>
                                <label for="date" class="block font-medium mb-1">Date</label>
                                <input type="date" name="date" class="w-full border rounded px-2 py-1 text-sm"
                                    value="{{ old('date', isset($payment_expense) ? $payment_expense->date : date('Y-m-d')) }}"
                                    required>
                            </div>
                            <div>
                                <label class="block font-medium mb-1">From Account</label>
                                <select name="account_header_id" id="account_header_id"
                                    class="w-full border rounded px-2 py-1 text-sm" required>
                                    <option value="">-- Account --</option>
                                    @foreach ($account as $acc)
                                        <option value="{{ $acc->id }}"
                                            {{ old('account_header_id', $payment_expense->from_account_id ?? '') == $acc->id ? 'selected' : '' }}>
                                            {{ $acc->kode_akun }} {{ $acc->nama_akun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="source" class="block font-medium mb-1">Source</label>
                                <input type="text" name="source" placeholder="Masukkan Source"
                                    value="{{ old('source', $payment_expense->source ?? '') }}"
                                    class="w-full border rounded px-2 py-1 text-sm" required>
                            </div>
                        </div>

                        <!-- Detail Items -->
                        <h3 class="text-md font-semibold mt-6 mb-2">Expense Details</h3>
                        <table class="w-full border text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border px-2 py-1">Account</th>
                                    <th class="border px-2 py-1">Description</th>
                                    <th class="border px-2 py-1 text-right">Amount</th>
                                    <th class="border px-2 py-1">Tax</th>
                                    <th class="border px-2 py-1 text-right">Total</th>
                                    <th class="border px-2 py-1 text-center">#</th>
                                </tr>
                            </thead>
                            <tbody id="item-table-body">
                                @if (isset($payment_expense))
                                    @foreach ($payment_expense->details as $i => $detail)
                                        <tr class="item-row" data-index="{{ $i }}">
                                            <td>
                                                <select name="items[{{ $i }}][account_id]"
                                                    class="account-select w-full border rounded text-sm" required>
                                                    <option value="">-- Account --</option>
                                                    @foreach ($account_beban as $acc)
                                                        <option value="{{ $acc->id }}"
                                                            {{ $detail->account_id == $acc->id ? 'selected' : '' }}>
                                                            {{ $acc->kode_akun }} {{ $acc->nama_akun }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="items[{{ $i }}][deskripsi]"
                                                    value="{{ $detail->deskripsi }}"
                                                    class="w-full border rounded text-sm" />
                                            </td>
                                            <td>
                                                <input type="number" step="0.01"
                                                    name="items[{{ $i }}][amount]"
                                                    value="{{ $detail->amount }}"
                                                    class="amount-input w-full border rounded text-sm text-right" />
                                            </td>
                                            <td>
                                                <select name="items[{{ $i }}][tax_id]"
                                                    class="tax-select w-full border rounded">
                                                    <option value="">--Pilih--</option>
                                                    @foreach ($sales_taxes as $tax)
                                                        <option value="{{ $tax->id }}"
                                                            data-rate="{{ $tax->rate }}"
                                                            data-type="{{ $tax->type }}"
                                                            data-account="{{ $tax->salesAccount->nama_akun ?? $tax->purchaseAccount->nama_akun }}"
                                                            {{ $detail->sales_taxes_id == $tax->id ? 'selected' : '' }}>
                                                            {{ $tax->name }} ({{ $tax->rate }}%)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="total-cell text-right">0</td>
                                            <td class="text-center">
                                                <button type="button"
                                                    class="remove-row bg-red-500 text-white rounded text-xs px-2">Hapus</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                            <tfoot class="bg-gray-50 font-semibold">
                                <tr>
                                    <td colspan="4" class="text-right border px-2 py-1">Grand Total</td>
                                    <td class="border px-2 py-1 text-right" id="grandtotal">0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="mt-2">
                            <button type="button" id="add-row"
                                class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700">
                                + Tambah Baris
                            </button>
                        </div>

                        <!-- Notes -->
                        <div class="mt-4">
                            <label class="block font-medium mb-1">Comment</label>
                            <textarea name="notes" rows="2" class="w-full border rounded px-2 py-1 text-sm"
                                placeholder="Masukkan comment(jika ada)">{{ old('notes', $payment_expense->notes ?? '') }}</textarea>
                        </div>
                    </div>

                    <!-- Journal Report -->
                    <div id="journal_report" class="tab-content hidden mt-6">
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
                                    <td colspan="3" class="text-center py-2 text-gray-500">Tidak ada journal</td>
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
                    <div class="mt-4 flex space-x-2">
                        <button type="submit"
                            class="px-4 py-1 bg-blue-600 text-white text-sm font-semibold rounded hover:bg-blue-700">
                            {{ isset($payment_expense) ? 'Update' : 'Create' }}
                        </button>
                        <a href="{{ route('payment_expense.index') }}"
                            class="px-4 py-1 bg-gray-300 text-sm text-gray-700 rounded hover:bg-gray-400">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- JQUERY & SELECT2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $('.account-select, .tax-select, #account_header_id').select2({
            placeholder: "Pilih...",
            allowClear: true,
            width: 'resolve'
        });
    </script>
    <script>
        // Helper format angka
        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        }

        let rowIndex = 0;

        // Buat baris detail
        function generateRow(index) {
            return `
        <tr class="item-row" data-index="${index}">
            <td>
                <select name="items[${index}][account_id]" 
                        class="account-select w-full border rounded text-sm" required>
                    <option value="">-- Account --</option>
                    @foreach ($account_beban as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->kode_akun }} {{ $acc->nama_akun }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="items[${index}][deskripsi]" 
                       class="w-full border rounded text-sm" />
            </td>
            <td>
                <input type="number" step="0.01" 
                       name="items[${index}][amount]" 
                       class="amount-input w-full border rounded text-sm text-right" />
            </td>
            <td>
                <select name="items[${index}][tax_id]" class="tax-select w-full border rounded">
                    <option value="">--Pilih--</option>
                    @foreach ($sales_taxes as $tax)
                        <option value="{{ $tax->id }}"
                            data-rate="{{ $tax->rate }}"
                            data-type="{{ $tax->type }}"
                            data-account="{{ $tax->salesAccount->kode_akun ?? $tax->purchaseAccount->kode_akun }} - {{ $tax->salesAccount->nama_akun ?? $tax->purchaseAccount->nama_akun }}">
                            {{ $tax->name }} ({{ $tax->rate }}%)
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="total-cell text-right">0</td>
            <td class="text-center">
                <button type="button" 
                        class="remove-row bg-red-500 text-white rounded text-xs px-2">Hapus</button>
            </td>
        </tr>`;
        }

        // Hitung ulang total & grandtotal
        function recalcTotals() {
            let grandtotal = 0;

            $('#item-table-body tr').each(function() {
                const amount = parseFloat($(this).find('.amount-input').val()) || 0;
                const taxRate = parseFloat($(this).find('.tax-select option:selected').data('rate')) || 0;
                const total = amount + (amount * taxRate / 100);

                $(this).find('.total-cell').text(formatNumber(total));
                grandtotal += total;
            });

            $('#grandtotal').text(formatNumber(grandtotal));
        }

        // Preview journal
        function generateJournalPreview() {
            const journalBody = document.querySelector('.journal-body');
            journalBody.innerHTML = '';

            let rows = [];
            let totalDebit = 0,
                totalCredit = 0;

            const fromAccountName = document.querySelector('#account_header_id option:checked')?.textContent;

            $('#item-table-body tr').each(function() {
                const amount = parseFloat($(this).find('.amount-input').val()) || 0;
                const accountName = $(this).find('.account-select option:selected').text();
                const $taxOption = $(this).find('.tax-select option:selected');
                const taxRate = parseFloat($taxOption.data('rate')) || 0;
                const taxType = $taxOption.data('type');
                const taxAccount = $taxOption.data('account') || $taxOption.text();
                const taxAmount = amount * (taxRate / 100);

                if (amount > 0 && accountName) {
                    rows.push({
                        account: accountName,
                        debit: amount,
                        credit: 0
                    });
                    totalDebit += amount;
                }

                if ($taxOption.val()) {
                    if (taxType === 'input_tax') {
                        rows.push({
                            account: taxAccount,
                            debit: taxAmount,
                            credit: 0
                        });
                        totalDebit += taxAmount;
                    } else if (taxType === 'withholding_tax') {
                        rows.push({
                            account: taxAccount,
                            debit: 0,
                            credit: taxAmount
                        });
                        totalCredit += taxAmount;
                    }
                }
            });

            if (totalDebit > 0 && fromAccountName) {
                rows.push({
                    account: fromAccountName,
                    debit: 0,
                    credit: totalDebit
                });
                totalCredit += totalDebit;
            }

            if (rows.length === 0) {
                journalBody.innerHTML =
                    `<tr><td colspan="3" class="text-center py-2 text-gray-500">Tidak ada journal</td></tr>`;
            } else {
                rows.forEach(r => {
                    journalBody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td class="border px-2 py-1">${r.account}</td>
                        <td class="border px-2 py-1 text-right">${formatNumber(r.debit)}</td>
                        <td class="border px-2 py-1 text-right">${formatNumber(r.credit)}</td>
                    </tr>
                `);
                });
            }

            document.querySelector('.total-debit').textContent = formatNumber(totalDebit);
            document.querySelector('.total-credit').textContent = formatNumber(totalCredit);

            recalcTotals(); // update total per baris & grandtotal
        }

        $(document).ready(function() {
            // Tambah baris awal
            $('#item-table-body').append(generateRow(rowIndex));
            $('#item-table-body .account-select, #item-table-body .tax-select').select2({
                placeholder: "Pilih...",
                allowClear: true,
                width: 'resolve'
            });
            rowIndex++;

            // Tambah baris baru
            $('#add-row').on('click', function() {
                $('#item-table-body').append(generateRow(rowIndex));
                $('#item-table-body tr:last .account-select, #item-table-body tr:last .tax-select')
                    .select2({
                        placeholder: "Pilih...",
                        allowClear: true,
                        width: 'resolve'
                    });
                rowIndex++;
            });

            // Hapus baris
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                generateJournalPreview();
            });

            // Trigger recalculation & journal
            $(document).on('change keyup',
                '#account_header_id, .account-select, .amount-input, .tax-select',
                function() {
                    generateJournalPreview();
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


    <style>
        .select2-container {
            width: 90% !important;
        }

        .select2-selection {
            min-height: 34px;
        }
    </style>

@endsection

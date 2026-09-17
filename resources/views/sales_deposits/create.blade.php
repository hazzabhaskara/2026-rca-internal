@extends('layouts.app')
@section('content')
    <div class="py-10">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <div id="tabs" class="type-section">
                    <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                        <li><a href="#select_item" class="tab-link active">Proces Deposit</a></li>
                        <li><a href="#journal_report" class="tab-link">Journal report</a></li>
                    </ul>
                </div>

                <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                    Deposits Create
                </h4>
                <form method="POST"
                    action="{{ isset($sales_deposits) ? route('sales_deposits.update', $sales_deposits->id) : route('sales_deposits.store') }}">
                    @csrf
                    @if (isset($sales_deposits))
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
                    <div class="tab-content" id="select_item">

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            {{-- payment method --}}
                            <div class="mb-4">
                                <label for="nama_metode" class="block text-gray-700 font-medium mb-1">Payment Method
                                </label>
                                <select name="jenis_pembayaran_id"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                    <option value="">-- Payment Method--</option>
                                    @foreach ($jenis_pembayaran as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('jenis_pembayaran_id', $sales_deposits->jenis_pembayaran_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->nama_jenis }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jenis_pembayaran_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label for="nama_metode" class="block text-gray-700 font-medium mb-1">Account
                                </label>
                                <select name="account_id" id="account_header_id"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                    <option value="">-- Pilih--</option>
                                    @foreach ($account as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('account_id', $sales_deposits->account_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->kode_akun }}-{{ $level->nama_akun }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label for="nama_metode" class="block text-gray-700 font-medium mb-1">Deposit Account
                                </label>
                                <select name="account_deposit" id="account_deposit"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                    <option value="">-- Pilih--</option>
                                    @foreach ($deposit as $dep)
                                        <option value="{{ $dep->id }}"
                                            {{ old('account_deposit', $sales_deposits->account_deposit ?? '') == $dep->id ? 'selected' : '' }}>
                                            {{ $dep->kode_akun }}-{{ $dep->nama_akun }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_deposit')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- customers --}}
                            <div class="mb-4">
                                <label for="customers" class="block text-gray-700 font-medium mb-1">Customers
                                </label>
                                <select name="customers_id" id="customers_id"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                    <option value="">-- Customers--</option>
                                    @foreach ($customer as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('customer_id', $sales_deposits->customer_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->nama_customers }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- deposit number --}}
                            <div class="mb-4">
                                <label for="deposit_no" class="block text-gray-700 font-medium mb-1">Deposits
                                    Number</label>
                                <input type="text" id="deposit_no" name="deposit_no"
                                    value="{{ old('deposit_no', $sales_deposits->deposit_no ?? '') }}"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                                {{-- <label class="inline-flex items-center mt-2">
                                <input type="checkbox" id="auto_generate" name="auto_generate" value="1"
                                    class="form-checkbox text-blue-600" onchange="toggleAutoGenerate()">
                                <span class="ml-2 text-sm text-gray-700">Generate Invoice Number secara otomatis</span>
                            </label> --}}

                                @error('deposit_no')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- deposit date --}}
                            <div class="mb-4">
                                <label for="deposit_date" class="block text-gray-700 font-medium mb-1">Date
                                </label>
                                <input type="date" id="name" name="deposit_date" required
                                    value="{{ old('deposit_date', $sales_deposits->deposit_date ?? '') }}"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('deposit_date')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="deposit reference" class="block text-gray-700 font-medium mb-1">Deposit
                                    Reference No</label>
                                <input type="text" name="deposit_reference" placeholder="Masukkan No depesit reference"
                                    value="{{ old('deposit_reference', $sales_deposits->deposit_reference ?? '') }}"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring=blue-500">
                            </div>
                            <div>
                                <label for="Deposit Amount" class="block text-gray-700 font-medium mb-1">Deposit
                                    Amount</label>
                                <div class="flex">
                                    <span
                                        class="inline-flex items-center px-3 rounded-l-md bg-gray-100 border border-r-0 border-gray-300 text-gray-600 text-sm">Rp</span>
                                    <input type="text" name="deposit_amount" step="0.01" id="amount" required
                                        value="{{ old('deposit_amount', $sales_deposits->deposit_amount ?? '') }}"
                                        class="w-full border number-format rounded px-2 py-1 text-right font-semibold" />
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="comment" class="block text-gray-700 font-medium mb-1">Comment</label>
                            <textarea name="comment" class="w-full border rounded px-2 py-1" placeholder="Masukkan comment(opsional)"></textarea>
                        </div>

                        <!-- Order Items Table -->

                    </div>
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
                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="{{ route('sales_deposits.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition">
                            {{ isset($sales_deposits) ? 'Update' : 'Process' }}
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
            $('#account_header_id,#account_deposit').select2({
                placeholder: "-- Pilih --",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#customers_id').select2({
                placeholder: "-- Customers --",
                ajax: {
                    url: '{{ route('customers.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        }; // query keyword
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(customer) {
                                return {
                                    id: customer.id,
                                    text: customer.nama_customers
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
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.number-format');

            inputs.forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');
                    this.value = new Intl.NumberFormat('id-ID').format(value);
                });
            });
        });
    </script>
    <script>
        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            }).format(num);
        }

        function generateJournalPreview() {
            const journalBody = document.querySelector('.journal-body');
            journalBody.innerHTML = '';

            let rows = [];
            let totalDebit = 0,
                totalCredit = 0;

            const paidAccount = {
                kode: "{{ $paidAccount->akun->kode_akun ?? '' }}",
                name: "{{ $paidAccount->akun->nama_akun ?? 'prepaid order' }}"
            };

            const fromAccountName = document.querySelector('#account_header_id option:checked')?.textContent;
            const fromAccountDeposit = document.querySelector('#account_deposit option:checked')?.textContent;
            const amountInput = document.querySelector('#amount');
            const amount = parseFloat(amountInput.value.replace(/\D/g, '')) || 0;

            if (amount > 0 && fromAccountName) {
                // Credit akun prepayment (akun lawan)
                // Credit akun prepayment
                rows.push({
                    account: fromAccountName,
                    debit: amount,
                    credit: 0
                });
                totalDebit += amount;

                // Debit akun kas/bank
                rows.push({
                    account: fromAccountDeposit,
                    debit: 0,
                    credit: amount
                });
                totalCredit += amount;

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
        }

        // Trigger otomatis saat input berubah
        $(document).ready(function() {
            // Trigger realtime update
            $(document).on('change', '#account_header_id, #account_deposit', generateJournalPreview);
            $(document).on('input', '#amount', generateJournalPreview);

            // Render awal
            generateJournalPreview();
        });
    </script>

@endsection

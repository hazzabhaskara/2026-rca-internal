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
                        <li><a href="#select_item" class="tab-link active">Proces Deposit</a></li>
                        <li><a href="#journal_report" class="tab-link">Journal report</a></li>
                    </ul>
                </div>
                <form method="POST" action="{{ route('sales_deposits.update', $sales_deposits->id) }}">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
                        <div class="bg-red-100 p-4 text-sm text-red-700 rounded mb-4">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Deposits Edit
                    </h4>
                    <div class="tab-content" id="select_item">

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Payment Method</label>
                                <select name="jenis_pembayaran_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($jenis_pembayaran as $jenis)
                                        <option value="{{ $jenis->id }}"
                                            {{ $sales_deposits->jenis_pembayaran_id == $jenis->id ? 'selected' : '' }}>
                                            {{ $jenis->nama_jenis }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Account</label>
                                <select name="account_id" id="account_header_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($account as $jenis)
                                        <option value="{{ $jenis->id }}"
                                            {{ $sales_deposits->account_id == $jenis->id ? 'selected' : '' }}>
                                            {{ $jenis->kode_akun }}-{{ $jenis->nama_akun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Account Deposit</label>
                                <select name="account_deposit" id="account_deposit" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($deposit as $dep)
                                        <option value="{{ $dep->id }}"
                                            {{ $sales_deposits->account_deposit == $dep->id ? 'selected' : '' }}>
                                            {{ $dep->kode_akun }}-{{ $dep->nama_akun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Deposit No</label>
                                <input type="text" name="deposit_no"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    value="{{ $sales_deposits->deposit_no }}" required>
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Customer</label>
                                <select name="customers_id" id="customers_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach ($customer as $cust)
                                        <option value="{{ $cust->id }}"
                                            {{ $sales_deposits->customer_id == $cust->id ? 'selected' : '' }}>
                                            {{ $cust->nama_customers }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Deposit Date</label>
                                <input type="date" name="deposit_date"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    value="{{ $sales_deposits->deposit_date }}" required>
                            </div>

                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Deposit Amount</label>
                                <input type="text" name="deposit_amount" id="amount"
                                    class="w-full number-format border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    value="{{ old('deposit_amount', number_format($sales_deposits->deposit_amount, 0, ',', '.')) }}"
                                    required>
                            </div>

                        </div>

                        {{-- Info Tambahan --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">

                            <div>
                                <label class="font-medium text-gray-700 block mb-1">Deposit Reference</label>
                                <input type="text" name="deposit_reference"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    value="{{ $sales_deposits->deposit_reference }}" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="font-medium text-gray-700 block mb-1">Comment</label>
                                <textarea name="comment" rows="3"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $sales_deposits->comment }}</textarea>
                            </div>
                        </div>
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

                    {{-- Tombol --}}
                    <div class="mt-8 flex justify-end space-x-4">

                        <a href="{{ route('sales_deposits.index') }}"
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
    <!-- JQUERY DULU -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
                name: "{{ $paidAccount->akun->nama_akun ?? 'Kas/Bank' }}"
            };

            const fromAccountName = document.querySelector('#account_header_id option:checked')?.textContent;
            const fromAccountDeposit = document.querySelector('#account_deposit option:checked')?.textContent;
            const amountInput = document.querySelector('#amount');
            const rawValue = amountInput.value.replace(/\./g, '').replace(',', '.');
            const amount = parseFloat(rawValue) || 0;


            if (amount > 0 && fromAccountName) {
                rows.push({
                    account: fromAccountName,
                    debit: amount,
                    credit: 0
                });
                totalDebit += amount;

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

        document.addEventListener('DOMContentLoaded', function() {
            // Aktifkan select2
            $('.account-select, #account_header_id').select2({
                placeholder: "Pilih...",
                allowClear: true,
                width: 'resolve'
            });

            // Render awal pakai nilai dari DB
            generateJournalPreview();

            // Listener perubahan
            $(document).ready(function() {
                $('#account_header_id, #account_deposit, #amount').on('change input',
                    generateJournalPreview);

                // render awal saat form pertama kali dibuka
                generateJournalPreview();
            });
        });
    </script>

@endsection

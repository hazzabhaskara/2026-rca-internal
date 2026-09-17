@extends('layouts.app')
@section('content')
    <div class="py-10">
        <div class="py-8">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                @php
                    $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                @endphp
                <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                    <div id="tabs" class="type-section">
                        <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                            <li><a href="#select_item" class="tab-link active">Proces Prepayment</a></li>
                            <li><a href="#journal_report" class="tab-link">Journal report</a></li>
                        </ul>
                    </div>
                    <h2 class="font-bold text-lg mb-4">Prepayment Create</h2>
                    <form action="{{ route('prepayment.store') }}" method="POST">
                        @csrf

                        {{-- Error Validation --}}
                        @if ($errors->any())
                            <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                                {{ session('error') }}
                            </div>
                        @endif

                        {{-- Form Fields --}}
                        <div class="tab-content" id="select_item">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 text-base">

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Date</label>
                                    <input type="date" name="tanggal_prepayment"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2"
                                        value="{{ old('tanggal_prepayment') }}" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Reference</label>
                                    <input type="text" name="reference"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2"
                                        value="{{ old('reference') }}" required>
                                </div>
                                <div>
                                    <label for="account_id"
                                        class="block text-sm font-semibold text-gray-700 mb-1">Account</label>
                                    <select name="account_header_id" id="account_header_id"
                                        class="w-full account-select rounded-md border border-gray-300 px-3 py-2" required>
                                        <option value="">--Pilih---</option>
                                        @foreach ($account as $acc)
                                            <option value="{{ $acc->id }}">
                                                {{ $acc->kode_akun }}-{{ $acc->nama_akun }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="account_id"
                                        class="block text-sm font-semibold text-gray-700 mb-1">Prepayment Account</label>
                                    <select name="account_prepayment" id="account_prepayment"
                                        class="w-full account-select rounded-md border border-gray-300 px-3 py-2" required>
                                        <option value="">--Pilih---</option>
                                        @foreach ($prepaymentAccount as $acc)
                                            <option value="{{ $acc->id }}">
                                                {{ $acc->kode_akun }}-{{ $acc->nama_akun }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="vendor_id"
                                        class="block text-sm font-semibold text-gray-700 mb-1">Vendor</label>
                                    <select name="vendor_id" id="vendor_id"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2" required>
                                        <option value="">--Pilih---</option>
                                        @foreach ($vendor as $ver)
                                            <option value="{{ $ver->id }}">
                                                {{ $ver->nama_vendors }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="amount"
                                        class="block text-sm font-semibold text-gray-700 mb-1">Amount</label>
                                    <input type="text" name="amount" id="amount"
                                        class="number-format w-full rounded-md border border-gray-300 px-3 py-2"
                                        placeholder="Masukkan amount...">
                                </div>
                                <div>
                                    <label for="comment"
                                        class="block text-sm font-semibold text-gray-700 mb-1">Comment</label>
                                    <textarea name="comment" id="comment" class="w-full rounded-md border border-gray-300 px-3 py-2"
                                        placeholder="Masukkan comment(opsional)"></textarea>
                                </div>
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
                        {{-- Tombol Aksi --}}
                        <div class="mt-6 flex justify-end gap-4">
                            <a href="{{ route('prepayment.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded-md">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-semibold text-sm rounded-md hover:bg-green-700">
                                Process
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Optional: Select2 CDN -->
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
                $('#account_header_id,#account_prepayment').select2({
                    placeholder: "-- Pilih --",
                    allowClear: true,
                    width: '100%'
                });
            });
        </script>

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
                const fromAccountPrepayment = document.querySelector('#account_prepayment option:checked')?.textContent;
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
                        account: fromAccountPrepayment,
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

            $(document).ready(function() {
                // Trigger realtime update
                $(document).on('change', '#account_header_id, #account_prepayment', generateJournalPreview);
                $(document).on('input', '#amount', generateJournalPreview);

                // Render awal
                generateJournalPreview();
            });
        </script>


    @endsection

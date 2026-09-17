@extends('layouts.app')

@section('content')

    <div class="py-4">
        <div class="w-full px-2">

            <div class="bg-white shadow rounded p-4">
                <div id="tabs" class="type-section">
                    <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                        <li><a href="#select_item" class="tab-link active">Process Prepayment</a></li>
                        <li><a href="#journal_report" class="tab-link">Journal Report</a></li>
                    </ul>
                </div>

                <h2 class="text-lg font-bold mb-4">Edit Prepayment</h2>

                <form method="POST" action="{{ route('prepayment.update', $prepayment->id) }}">
                    @csrf
                    @method('PUT')

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
                                <input type="date" name="tanggal_prepayment"
                                    class="w-full border rounded px-2 py-1 text-sm"
                                    value="{{ old('tanggal_prepayment', $prepayment->tanggal_prepayment) }}" required>
                            </div>

                            <div>
                                <label for="Reference" class="block font-medium mb-1">Reference</label>
                                <input type="text" name="reference" placeholder="Masukkan Reference"
                                    class="w-full border rounded px-2 py-1 text-sm"
                                    value="{{ old('reference', $prepayment->reference) }}" required>
                            </div>
                            <div>
                                <label class="block font-medium mb-1">From Account</label>
                                <select name="account_header_id" id="account_header_id"
                                    class="w-full border rounded px-2 py-1 text-sm" required>
                                    <option value="">-- Account --</option>
                                    @foreach ($account as $acc)
                                        <option value="{{ $acc->id }}"
                                            {{ old('account_id', $prepayment->account_id) == $acc->id ? 'selected' : '' }}>
                                            {{ $acc->kode_akun }} - {{ $acc->nama_akun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>


                        <div class="grid grid-cols-3 gap-4 text-sm">
                            <div>
                                <label class="block font-medium mb-1">From Account Prepayment</label>
                                <select name="account_prepayment" id="account_prepayment"
                                    class="w-full border rounded px-2 py-1 text-sm" required>
                                    <option value="">-- Account --</option>
                                    @foreach ($prepaymentAccount as $acc)
                                        <option value="{{ $acc->id }}"
                                            {{ old('account_prepayment', $prepayment->account_prepayment) == $acc->id ? 'selected' : '' }}>
                                            {{ $acc->kode_akun }} - {{ $acc->nama_akun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium mb-1">Vendor</label>
                                <select name="vendor_id" id="vendor_id" class="w-full border rounded px-2 py-1 text-sm"
                                    required>
                                    <option value="">-- Vendor --</option>
                                    @foreach ($vendor as $vend)
                                        <option value="{{ $vend->id }}"
                                            {{ old('vendor_id', $prepayment->vendor_id) == $vend->id ? 'selected' : '' }}>
                                            {{ $vend->nama_vendors }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="amount" class="block font-medium mb-1">Amount</label>
                                <input type="text" name="amount" id="amount" placeholder="Masukkan amount"
                                    class="w-full number-format border rounded px-2 py-1 text-sm"
                                    value="{{ old('amount', number_format($prepayment->amount, 0, ',', '.')) }}" required>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mt-4">
                            <label class="block font-medium mb-1">Comment</label>
                            <textarea name="comment" rows="2" class="w-full border rounded px-2 py-1 text-sm"
                                placeholder="Masukkan comment(jika ada)">{{ old('comment', $prepayment->comment) }}</textarea>
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
                    <div class="mt-4 flex justify-end space-x-2">

                        <a href="{{ route('prepayment.index') }}"
                            class="px-4 py-1 bg-gray-300 text-sm text-gray-700 rounded hover:bg-gray-400">Cancel</a>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded hover:bg-green-700">
                            Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JQUERY & SELECT2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>



    <style>
        .select2-container {
            width: 90% !important;
        }

        .select2-selection {
            min-height: 34px;
        }
    </style>

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

            const fromAccountName = $('#account_header_id option:selected').text();
            const fromAccountPrepayment = $('#account_prepayment option:selected').text();
            const amount = parseFloat($('#amount').val().replace(/\D/g, '')) || 0;

            let rows = [];
            let totalDebit = 0,
                totalCredit = 0;

            if (amount > 0 && fromAccountName && fromAccountPrepayment) {
                rows.push({
                    account: fromAccountName,
                    debit: amount,
                    credit: 0
                });
                rows.push({
                    account: fromAccountPrepayment,
                    debit: 0,
                    credit: amount
                });
                totalDebit = totalCredit = amount;
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
            $('#account_header_id, #account_prepayment, #amount').on('change input', generateJournalPreview);

            // render awal saat form pertama kali dibuka
            generateJournalPreview();
        });
    </script>

@endsection

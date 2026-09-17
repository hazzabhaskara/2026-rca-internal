@extends('layouts.app')


@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form action="{{ route('payment.update', $data->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Payment Edit
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="font-medium text-gray-700 block mb-1">Payment Method</label>
                            <select id="jenis_pembayaran_id" name="jenis_pembayaran_id" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50">
                                <option value="">-- Pilih --</option>
                                @foreach ($jenis_pembayaran as $jenis)
                                    <option value="{{ $jenis->id }}"
                                        {{ $data->jenis_pembayaran_id == $jenis->id ? 'selected' : '' }}>
                                        {{ $jenis->nama_jenis }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="account-wrapper" class="hidden">
                            <label class="font-medium text-gray-700 block mb-1">Account</label>
                            <select id="account_id" name="payment_method_account_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50">
                                <option value="">-- Pilih Account --</option>
                            </select>
                        </div>

                        <div>
                            <label for="source" class="font-medium text-gray-700 block mb-1">Source</label>
                            <input type="text" name="source" class="w-full border border-gray-300 rounded-lg px-4 py-2"
                                value="{{ old('source', $data->source) }}">
                        </div>
                        <div>
                            <label class="font-medium text-gray-700 block mb-1">Vendor</label>
                            <select id="vendor_id" name="vendor_id" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50">
                                <option value="">-- Pilih --</option>
                                @foreach ($vendor as $ven)
                                    <option value="{{ $ven->id }}"
                                        {{ $data->vendor_id == $ven->id ? 'selected' : '' }}>
                                        {{ $ven->nama_vendors }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="payment_date" class="font-medium text-gray-700 block mb-1">Payment Date</label>
                            <input type="date" name="payment_date"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2"
                                value="{{ old('payment_date', $data->payment_date) }}">
                        </div>

                        <div>
                            <label for="comment" class="font-medium text-gray-700 block mb-1">Comment</label>
                            <textarea name="comment" class="w-full border border-gray-300 rounded-lg px-4 py-2">{{ $data->comment ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="overflow-x-auto border rounded-lg shadow-sm mt-6">
                        <table class="w-full border-collapse border text-sm whitespace-nowrap">
                            @php
                                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                            @endphp
                            <thead
                                class="bg-gradient-to-r bg-[{{ $themeColor }}]  to-blue-600 text-white text-sm font-semibold">
                                <th class="border px-4 py-2">Tanggal</th>
                                <th class="border px-4 py-2">Nomor</th>
                                <th class="border px-4 py-2">Original Amount</th>
                                <th class="border px-4 py-2">Amount Owing</th>
                                <th class="border px-4 py-2">Payment Amount</th>
                                <th class="border px-4 py-2">Prepayment</th>
                            </thead>

                            <tbody>
                                {{-- 🔹 Baris pembayaran langsung --}}
                                @foreach ($data->details as $detail)
                                    <tr>
                                        <td class="border px-4 py-2">
                                            <input type="date" name="date_invoice"
                                                class="w-full border border-gray-600 rounded bg-gray-50" readonly
                                                value="{{ $detail->invoice->date_invoice ?? '' }}">
                                        </td>
                                        <td class="border px-4 py-2">
                                            <input type="text" name="invoice_number"
                                                class="w-full border border-gray-600 rounded bg-gray-50" readonly
                                                value="{{ $detail->invoice->invoice_number ?? '' }}">
                                        </td>
                                        <td class="border px-4 py-2 text-right">
                                            <input type="text" name="original_amount"
                                                class="w-full border border-gray-600 rounded bg-gray-50 text-right" readonly
                                                value="{{ number_format($detail->original_amount ?? 0, 2, '.', ',') }}">
                                        </td>
                                        <td class="border px-4 py-2 text-right">
                                            <input type="text" name="amount_owing"
                                                class="w-full border border-gray-600 rounded bg-gray-50 text-right" readonly
                                                value="{{ number_format($detail->amount_owing ?? 0, 2, '.', ',') }}">
                                        </td>
                                        <td class="border px-4 py-2 text-right">
                                            <input type="text" name="payment_amount"
                                                class="w-full border border-gray-600 rounded text-right"
                                                value="{{ number_format($detail->payment_amount ?? 0, 2, '.', ',') }}">
                                        </td>
                                        <td class="border px-4 py-2 text-center text-gray-400">—</td>
                                    </tr>
                                @endforeach

                                {{-- 🟡 Baris prepayment allocations --}}
                                @foreach ($prepaymentAllocations as $alloc)
                                    <tr class="bg-yellow-50">
                                        <td class="border px-4 py-2">
                                            <input type="date" name="tanggal_prepayment"
                                                class="w-full border border-gray-600 rounded bg-gray-50" readonly
                                                value="{{ $alloc->tanggal }}">
                                        </td>
                                        <td class="border px-4 py-2">
                                            <input type="text" name="reference"
                                                class="w-full border border-gray-600 rounded bg-gray-50" readonly
                                                value="{{ $alloc->reference }}">
                                        </td>
                                        <td class="border px-4 py-2 text-right">
                                            <input type="text" name="original_amount_prepayment"
                                                class="w-full border border-gray-600 rounded bg-gray-50 text-right" readonly
                                                value="{{ number_format($alloc->original_amount ?? 0, 2, '.', ',') }}">
                                        </td>
                                        <td class="border px-4 py-2 text-center text-gray-400"><input type="text"
                                                class="w-full border border-gray-600 rounded text-right"
                                                value="{{ number_format($alloc->amount_owing ?? 0, 2, '.', ',') }}"></td>
                                        <td class="border px-4 py-2 text-center text-gray-400">—</td>
                                        <td class="border px-4 py-2 text-right">
                                            <input type="text" name="allocated_amount"
                                                class="w-full border border-gray-600 rounded text-right"
                                                value="{{ number_format($alloc->allocated_amount ?? 0, 2, '.', ',') }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>


                        </table>
                    </div>

                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="{{ route('payment.index') }}"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                            <i class="fas fa-arrow-left mr-1"></i> Cancel
                        </a>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                            <i class="fas fa-save mr-1"></i> Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const $pmSelect = $('#jenis_pembayaran_id');
            const $account = $('#account_id');
            const $wrapper = $('#account-wrapper');

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
                            "{{ old('payment_method_account_id', $data->payment_method_account_id ?? '') }}";
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
        });
    </script>
@endsection

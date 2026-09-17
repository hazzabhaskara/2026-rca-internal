<table class="w-full text-sm border">
    <thead class="bg-gray-100">
        <tr>
            <th>Invoice</th>
            <th>Tgl Invoice</th>
            <th>Original</th>
            <th>Owing</th>
            <th>Disc Avail</th>
            <th>Disc Taken</th>
            <th>Paid</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($invoices as $i => $inv)
            <tr class="border-t" data-index="{{ $i }}">
                <td>
                    <input type="hidden" name="details[{{ $i }}][sales_invoice_id]"
                        value="{{ $inv->id }}">
                    {{ $inv->invoice_number }}
                </td>
                <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d-m-Y') }}</td>

                {{-- Original --}}
                <td class="text-right">
                    <input type="hidden" name="details[{{ $i }}][original_amount]"
                        value="{{ $inv->original }}">
                    <span class="original-display">{{ number_format($inv->original, 2) }}</span>
                </td>

                {{-- Owing --}}
                <td class="text-right">
                    <input type="hidden" name="details[{{ $i }}][amount_owing]"
                        value="{{ $inv->sisa_tagihan }}">
                    <span class="owing-display">{{ number_format($inv->sisa_tagihan, 2) }}</span>
                </td>

                {{-- Discount Available (editable) --}}
                <td>
                    <input type="number" step="0.01" name="details[{{ $i }}][discount_available]"
                        class="border rounded w-full text-right disc-avail-input" value="{{ $inv->discount_invoice }}">
                </td>

                {{-- Discount Taken (editable) --}}
                <td>
                    <input type="number" step="0.01" name="details[{{ $i }}][discount_taken]"
                        class="border rounded w-full text-right disc-taken-input" value="0">
                </td>

                {{-- Paid (manual input) --}}
                <td>
                    <input type="number" step="0.01" name="details[{{ $i }}][amount_received]"
                        class="border rounded w-full text-right paid-input" value="0">
                </td>

            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-gray-500">Tidak ada invoice untuk customer ini</td>
            </tr>
        @endforelse
    </tbody>

    <tfoot class="bg-gray-200 font-semibold">
        <tr>
            <td colspan="2" class="text-right">Total :</td>
            <td class="text-right" id="total-original">0.00</td>
            <td class="text-right" id="total-owing">0.00</td>
            <td class="text-right" id="total-disc-avail">0.00</td>
            <td class="text-right" id="total-disc-taken">0.00</td>
            <td class="text-right" id="total-paid">0.00</td>
        </tr>
    </tfoot>
</table>

<script>
    function recalc() {
        let totalOriginal = 0;
        let totalOwing = 0;
        let totalDiscAvail = 0;
        let totalDiscTaken = 0;
        let totalPaid = 0;

        document.querySelectorAll("tbody tr[data-index]").forEach(row => {
            let original = parseFloat(row.querySelector('input[name*="[original_amount]"]').value) || 0;
            let owing = parseFloat(row.querySelector('input[name*="[amount_owing]"]').value) || 0;
            let discAvail = parseFloat(row.querySelector('input[name*="[discount_available]"]').value) || 0;
            let discTaken = parseFloat(row.querySelector('input[name*="[discount_taken]"]').value) || 0;
            let paid = parseFloat(row.querySelector('input[name*="[amount_received]"]').value) || 0;

            totalOriginal += original;
            totalOwing += owing;
            totalDiscAvail += discAvail;
            totalDiscTaken += discTaken;
            totalPaid += paid;
        });

        document.getElementById("total-original").innerText = totalOriginal.toFixed(2);
        document.getElementById("total-owing").innerText = totalOwing.toFixed(2);
        document.getElementById("total-disc-avail").innerText = totalDiscAvail.toFixed(2);
        document.getElementById("total-disc-taken").innerText = totalDiscTaken.toFixed(2);
        document.getElementById("total-paid").innerText = totalPaid.toFixed(2);
    }

    document.addEventListener("input", recalc);
    recalc();

    // Jalankan saat ada input perubahan
    document.addEventListener("input", recalc);
    recalc();
</script>

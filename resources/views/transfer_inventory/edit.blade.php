@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-4 p-4 rounded bg-red-100 border border-red-400 text-red-700">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="list-disc list-inside mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-md rounded-lg p-6">
                <form method="POST" action="{{ route('transfer_inventory.update', $transfer->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="tab-content">
                        <h2 class="text-lg font-semibold mb-4">Edit Transfer Inventory</h2>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="date" class="w-full border rounded px-4 py-2 bg-gray-50"
                                    value="{{ old('date', $transfer->date) }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">From Location</label>
                                <select name="from_location_id"
                                    class="w-full border rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($fromLocation as $g)
                                        <option value="{{ $g->id }}"
                                            {{ old('from_location_id', $transfer->from_location_id) == $g->id ? 'selected' : '' }}>
                                            {{ $g->kode_lokasi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">To Location</label>
                                <select name="to_location_id"
                                    class="w-full border rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($toLocation as $g)
                                        <option value="{{ $g->id }}"
                                            {{ old('to_location_id', $transfer->to_location_id) == $g->id ? 'selected' : '' }}>
                                            {{ $g->kode_lokasi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Source</label>
                                <input type="text" name="source" class="w-full border rounded px-4 py-2 bg-gray-50"
                                    value="{{ old('source', $transfer->source) }}">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="notes" class="w-full border rounded px-2 py-1" placeholder="Masukkan catatan bila ada">{{ old('notes', $transfer->notes) }}</textarea>
                            </div>
                        </div>

                        <!-- Detail Items -->
                        <div class="mt-6">
                            <table class="w-full border border-gray-300 text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border text-center px-2 py-1">Component</th>
                                        <th class="border text-right px-2 py-1">Qty</th>
                                        <th class="border text-center px-2 py-1">Unit</th>
                                        <th class="border text-right px-2 py-1">Unit Cost</th>
                                        <th class="border text-right px-2 py-1">Amount</th>
                                        <th class="border text-center px-2 py-1">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="assembly-body">
                                    @foreach ($transfer->details as $i => $detail)
                                        @php
                                            $unitCost = $detail->qty > 0 ? $detail->amount / $detail->qty : 0;
                                            $amount = $unitCost * $detail->qty;
                                        @endphp
                                        <tr data-index="{{ $i }}">
                                            <td class="border px-2 py-1">
                                                <select name="component_id[]"
                                                    class="component-select w-full border rounded px-2 py-1">
                                                    <option value="">-- Pilih Komponen --</option>
                                                    @foreach ($items as $it)
                                                        <option value="{{ $it->id }}"
                                                            {{ $detail->component_item_id == $it->id ? 'selected' : '' }}>
                                                            {{ $it->item_description }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td class="border px-2 py-1">
                                                <input type="number" name="qty[]" value="{{ $detail->qty }}"
                                                    class="qty-used w-full border rounded px-2 py-1 text-right">
                                            </td>

                                            <td class="border px-2 py-1">
                                                <input type="text" name="unit[]" value="{{ $detail->unit }}"
                                                    class="unit w-full border rounded px-2 py-1 text-center">
                                            </td>

                                            <td class="border px-2 py-1">
                                                <input type="number" step="0.01" name="unit_cost[]"
                                                    value="{{ number_format($unitCost, 2, '.', '') }}"
                                                    class="unit-cost w-full border rounded px-2 py-1 text-right">
                                            </td>

                                            <td class="border px-2 py-1">
                                                <input type="text"
                                                    class="total-cost-display w-full border rounded px-2 py-1 text-right"
                                                    value="{{ number_format($amount, 2) }}" readonly>
                                                <input type="hidden" name="amount[]" class="total-cost-hidden"
                                                    value="{{ $amount }}">
                                            </td>
                                            <td class="border px-2 py-1 text-center">
                                                <button type="button" onclick="removeComponentRow(this)"
                                                    class="text-red-500">🗑️</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="mt-6 flex space-x-4">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                            Update Transfer Inventory
                        </button>
                        <a href="{{ route('transfer_inventory.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // Formatter angka Rupiah / ID locale
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        // Fungsi hitung ulang 1 row
        function calculateRowTotal(row) {
            const qty = parseFloat(row.querySelector('.qty-used').value) || 0;
            const unitCost = parseFloat(row.querySelector('.unit-cost').value) || 0;
            const total = qty * unitCost;

            // Simpan hidden value (raw float)
            row.querySelector('.total-cost-hidden').value = total;

            // Tampilkan dengan format
            row.querySelector('.total-cost-display').value = formatter.format(total);

            updateAssemblyTotal();
        }

        // Hitung ulang semua row → total cost
        function updateAssemblyTotal() {
            let total = 0;
            document.querySelectorAll('.total-cost-hidden').forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            // Jika ada summary di bawah form
            const totalInput = document.querySelector('input[name="total_cost"]');
            const totalDisplay = document.querySelector('.grand-total-display');
            if (totalInput) totalInput.value = total;
            if (totalDisplay) totalDisplay.value = formatter.format(total);
        }

        // Listener perubahan qty / unit_cost
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('qty-used') || e.target.classList.contains('unit-cost')) {
                const row = e.target.closest('tr');
                calculateRowTotal(row);
            }
        });

        // Inisialisasi: format semua row ketika halaman edit dibuka
        document.querySelectorAll('.assembly-body tr').forEach(row => {
            calculateRowTotal(row);
        });
    </script>

@endsection

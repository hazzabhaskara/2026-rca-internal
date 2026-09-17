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
                <form method="POST" action="{{ route('transfer_inventory.store') }}">
                    @csrf
                    <!-- Tab Content -->
                    <div class="tab-content">
                        <h2 class="text-lg font-semibold mb-4">Process Transfer Inventory</h2>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="date" class="w-full border rounded px-4 py-2 bg-gray-50"
                                    value="{{ now()->toDateString() }}">
                            </div>

                            <div>
                                <label for="item" class="block text-sm font-medium text-gray-700">From Location
                                </label>
                                <select name="from_location_id"
                                    class="parent-item-select w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($fromLocation as $g)
                                        <option value="{{ $g->id }}">{{ $g->kode_lokasi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="item" class="block text-sm font-medium text-gray-700">To Location
                                </label>
                                <select name="to_location_id"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($toLocation as $g)
                                        <option value="{{ $g->id }}">{{ $g->kode_lokasi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Source</label>
                                <input type="source" name="source" class="w-full border rounded px-4 py-2 bg-gray-50">
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="notes" class="w-full border rounded px-2 py-1" placeholder="Masukkan catatan bila ada"></textarea>
                            </div>
                        </div>

                        <!-- Detail Komponen -->
                        <div class="mt-6">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-semibold">Items</h3>
                                <button type="button" onclick="addComponentRow()"
                                    class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                    + Add Items
                                </button>
                            </div>

                            <table class="w-full border border-gray-300 text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border text-center px-2 py-1">Component</th>
                                        <th class="border text-right px-2 py-1">Qty</th>
                                        <th class="border text-center px-2 py-1">Unit</th>
                                        <th class="border text-right px-2 py-1">Unit Cost</th>
                                        <th class="border text-right px-2 py-1">Amount</th>
                                        <th class="border text-right px-2 py-1">Available</th>
                                        <th class="border text-center px-2 py-1">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="assembly-body"></tbody>
                            </table>
                        </div>
                    </div>



                    <!-- Buttons -->
                    <div class="mt-6 flex space-x-4">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                            Create Transfer Inventory
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
        let componentIndex = 0;
        document.querySelector('select[name="from_location_id"]').addEventListener('change', function() {
            let locationId = this.value;
            if (!locationId) return;

            // perbarui semua dropdown komponen
            document.querySelectorAll('.component-select').forEach(select => {
                fetch(`/items/by-location/${locationId}`)
                    .then(res => res.json())
                    .then(items => {
                        select.innerHTML = '<option value="">-- Pilih Komponen --</option>';
                        items.forEach(it => {
                            select.innerHTML +=
                                `<option value="${it.id}">${it.item_description}</option>`;
                        });
                    });
            });
        });

        function addComponentRow() {
            const tbody = document.querySelector('.assembly-body');
            const row = document.createElement('tr');
            row.setAttribute('data-index', componentIndex);

            row.innerHTML = `
        <td class="border px-2 py-1">
            <select name="component_id[]" class="component-select w-full border rounded px-2 py-1">
                <option value="">-- Pilih Komponen --</option>
            </select>
        </td>
        <td class="border px-2 py-1">
            <input type="number" name="qty[]" class="qty-used w-full border rounded px-2 py-1 text-right" min="0" value="1">
        </td>
        <td class="border px-2 py-1">
            <input type="text" name="unit[]" class="unit w-full border rounded px-2 py-1 text-center" readonly>
        </td>
        <td class="border px-2 py-1">
            <input type="number" step="0.01" name="unit_cost[]" class="unit-cost w-full border rounded px-2 py-1 text-right" value="0">
        </td>
        <td class="border px-2 py-1">
            <input type="text" class="total-cost-display w-full border rounded px-2 py-1 text-right" value="0" readonly>
            <input type="hidden" name="amount[]" class="total-cost-hidden" value="0">
        </td>
        <td class="border px-2 py-1">
            <input type="text" name="available[]" class="available w-full border rounded px-2 py-1 text-right" value="0" readonly>
        </td>
        <td class="border px-2 py-1 text-center">
            <button type="button" onclick="removeComponentRow(this)" class="text-red-500">🗑️</button>
        </td>
     `;
            tbody.appendChild(row);

            // isi dropdown sesuai lokasi terpilih
            let locationId = document.querySelector('select[name="from_location_id"]').value;
            if (locationId) {
                fetch(`/items/by-location/${locationId}`)
                    .then(res => res.json())
                    .then(items => {
                        const select = row.querySelector('.component-select');
                        select.innerHTML = '<option value="">-- Pilih Komponen --</option>';
                        items.forEach(it => {
                            select.innerHTML += `<option value="${it.id}">${it.item_description}</option>`;
                        });

                        // listener pilih item → ambil detail
                        select.addEventListener('change', function() {
                            let itemId = this.value;
                            if (!itemId) return;
                            fetch(`/items/${itemId}/info?location_id=${locationId}`)
                                .then(res => res.json())
                                .then(data => {
                                    row.querySelector('.unit').value = data.unit ?? '-';
                                    row.querySelector('.available').value = data.current_stock ?? 0;
                                    row.querySelector('.unit-cost').value = data.unit_cost ?? 0;
                                    calculateRowTotal(row);
                                });
                        });
                    });
            }

            componentIndex++;
        }

        function removeComponentRow(btn) {
            btn.closest('tr').remove();
            updateAssemblyTotal();
        }

        // hitung ulang 1 row
        function calculateRowTotal(row) {
            const qty = parseFloat(row.querySelector('.qty-used').value) || 0;
            const unitCost = parseFloat(row.querySelector('.unit-cost').value) || 0;
            const total = qty * unitCost;

            row.querySelector('.total-cost-display').value = total.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            });
            row.querySelector('.total-cost-hidden').value = total;

            updateAssemblyTotal();
        }

        // hitung ulang semua row → total cost
        function updateAssemblyTotal() {
            let total = 0;
            document.querySelectorAll('.total-cost-hidden').forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            document.querySelector('input[name="total_cost"]').value = total;
            document.querySelector('.total-cost-display').value = total.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            });
        }

        // listener qty & cost → auto recalc
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('qty-used') || e.target.classList.contains('unit-cost')) {
                const row = e.target.closest('tr');
                calculateRowTotal(row);
            }
        });
    </script>
@endsection

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
    <div>
        <label for="item" class="block text-sm font-medium text-gray-700">Item</label>
        <select name="item_id"
            class="item-select
            w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">-- Item --</option>
            @foreach ($item as $g)
                <option value="{{ $g->id }}">{{ $g->item_description }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Description</label>
        <input type="text" class="item-description w-full border rounded px-4 py-2 bg-gray-50" readonly>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Unit</label>
        <input type="text" class="item-unit w-full border rounded px-4 py-2 bg-gray-50" readonly>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Current Stock</label>
        <input type="text" class="item-stock w-full border rounded px-4 py-2 bg-gray-50" readonly>
    </div>
    <div>
        <label for="" class="block text-sm font-medium text-gray-700">Qty To Build</label>
        <input type="number" class="qty-to-build w-full border rounded px-4 py-2" value="1" min="1">
    </div>
    <div>
        <label for="total_cost" class="block text-sm font-medium text-gray-700">Total Cost</label>
        <input type="hidden" name="total_cost" value="0">
        <input type="text" class="total-cost-display w-full border rounded px-4 py-2 bg-gray-50 text-right" readonly>
    </div>
</div>

<div class="mt-6 hidden bom-section">
    <h3 class="text-lg font-semibold mb-2">Bill Of Materials Formula</h3>
    <table class="w-full border border-gray-300 text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="border text-center px-2 py-1">Component</th>
                <th class="border text-center px-2 py-1">Unit</th>
                <th class="border text-right px-2 py-1">Required Per</th>
                <th class="border text-right px-2 py-1">Unit Cost</th>
                <th class="border text-right px-2 py-1">Total Cost</th>
                <th class="border text-right px-2 py-1">Available</th>
                <th class="border text-center px-2 py-1">Status</th>
                <th class="border text-right px-2 py-1">Aksi</th>
            </tr>
        </thead>
        <tbody class="bom-body"></tbody>
    </table>
</div>

<script>
    document.querySelectorAll('.item-select').forEach(select => {
        select.addEventListener('change', function() {
            const wrapper = this.closest('.grid').parentNode;
            let itemId = this.value;

            let descInput = wrapper.querySelector('.item-description');
            let unitInput = wrapper.querySelector('.item-unit');
            let stockInput = wrapper.querySelector('.item-stock');
            let bomSection = wrapper.querySelector('.bom-section');
            let tbody = wrapper.querySelector('.bom-body');

            if (!itemId) {
                descInput.value = '';
                unitInput.value = '';
                stockInput.value = '';
                bomSection.classList.add('hidden');
                return;
            }

            // Ambil info item
            fetch(`/items/${itemId}/info`)
                .then(res => res.json())
                .then(data => {
                    descInput.value = data.description;
                    unitInput.value = data.unit;
                    stockInput.value = data.current_stock;
                });

            // Ambil BOM detail
            fetch(`/items/${itemId}/bom`)
                .then(res => res.json())
                .then(data => {
                    tbody.innerHTML = '';

                    if (data.details.length > 0) {
                        data.details.forEach(d => {
                            tbody.innerHTML += `
                            <tr>
                                <td class="border px-2 py-1">
                                    <input type="hidden" name="component_id[]" value="${d.component_id}">
                                    <input type="text" value="${d.description}" 
                                        class="w-full border text-center rounded px-2 py-1 bg-gray-50" readonly>
                                </td>
                                <td class="border px-2 py-1">
                                    <input type="text" value="${d.unit}" name="unit[]" 
                                        class="w-full border text-center rounded px-2 py-1 bg-gray-50" readonly>
                                </td>
                                <td class="border px-2 py-1">
                                    <input type="hidden" name="base_qty_per[]" value="${d.quantity}">
                                    <input type="number" value="${d.quantity}" name="qty_per[]" 
                                        class="w-full border  rounded px-2 py-1 bg-gray-50 text-right" readonly>
                                </td>
                                <td class="border px-2 py-1">
                                    <input type="hidden" name="unit_cost[]" value="${d.unit_cost}">
                                    <input type="text" value="${Number(d.unit_cost).toLocaleString('id-ID',{ minimumFractionDigits: 2 })}" 
                                        class="w-full border rounded px-2 py-1 bg-gray-50 text-right" readonly>
                                </td>
                                <td class="border px-2 py-1">
                                    <input type="hidden" name="amount[]" value="${d.amount}">
                                    <input type="text" class="amount-display w-full border rounded px-2 py-1 bg-gray-50 text-right" 
                                        value="${Number(d.amount).toLocaleString('id-ID',{ minimumFractionDigits: 2 })}" readonly>
                                </td>
                                <td class="border px-2 py-1">
                                    <input type="hidden" name="available[]" value="${d.available}">
                                    <input type="text" class="available-display w-full border rounded px-2 py-1 bg-gray-50 text-right" 
                                        value="${d.available}" readonly>
                                </td>
                                <td class="border px-2 py-1">
                                    <input type="text" class="status-display w-full border rounded px-2 py-1 bg-gray-50 text-center" 
                                        value="-" readonly>
                                </td>
                                <td class="border px-2 py-1 text-center">
                                    <button type="button" onclick="hapusBaris(this)" class="text-red-500">🗑️</button>
                                </td>
                            </tr>`;
                        });

                        bomSection.classList.remove('hidden');
                    } else {
                        bomSection.classList.add('hidden');
                    }
                })
                .catch(err => console.error(err));
        });
    });

    // Listener Qty To Build
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-to-build')) {
            let qtyToBuild = parseFloat(e.target.value) || 1;
            let tbody = document.querySelector('.bom-body');
            let totalCost = 0;

            tbody.querySelectorAll('tr').forEach(row => {
                let baseQtyPer = parseFloat(row.querySelector('input[name="base_qty_per[]"]').value) ||
                    0;
                let unitCost = parseFloat(row.querySelector('input[name="unit_cost[]"]').value) || 0;
                let available = parseFloat(row.querySelector('input[name="available[]"]').value) || 0;

                // Hitung Required Qty (Qty Per baru)
                let newQtyPer = baseQtyPer * qtyToBuild;
                row.querySelector('input[name="qty_per[]"]').value = newQtyPer;

                // Hitung Amount baru
                let amount = newQtyPer * unitCost;
                row.querySelector('input[name="amount[]"]').value = amount;
                row.querySelector('.amount-display').value = amount.toLocaleString('id-ID', {
                    minimumFractionDigits: 2
                });

                // Cek status
                let statusInput = row.querySelector('.status-display');
                if (newQtyPer > available) {
                    statusInput.value = 'Tidak Sesuai';
                    statusInput.classList.remove('text-green-600');
                    statusInput.classList.add('text-red-600');
                } else {
                    statusInput.value = 'OK';
                    statusInput.classList.remove('text-red-600');
                    statusInput.classList.add('text-green-600');
                }

                totalCost += amount;
            });

            // Update total cost
            document.querySelector('input[name="total_cost"]').value = totalCost;
            document.querySelector('.total-cost-display').value = totalCost.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            });
        }
    });

    function hapusBaris(btn) {
        btn.closest('tr').remove();
    }
</script>

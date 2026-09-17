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
            <div id="tabs" class="type-section">
                <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                    <li><a href="#assembly_form" class="tab-link active">Process Assembly</a></li>
                    <li><a href="#journal_report" class="tab-link">Journal Preview</a></li>
                </ul>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6">
                <form method="POST" action="{{ route('item_assembly.store') }}">
                    @csrf



                    <!-- Tab Content -->
                    <div id="assembly_form" class="tab-content">
                        <h2 class="text-lg font-semibold mb-4">Process Assembly</h2>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="date" class="w-full border rounded px-4 py-2 bg-gray-50"
                                    value="{{ now()->toDateString() }}">
                            </div>


                            <div>
                                <label for="item" class="block text-sm font-medium text-gray-700">From Location
                                </label>
                                <select name="location_id" id="from_location_id"
                                    class="parent-item-select w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($fromLocation as $g)
                                        <option value="{{ $g->id }}">{{ $g->kode_lokasi }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="item" class="block text-sm font-medium text-gray-700">Item</label>
                                <select name="parent_item_id"
                                    class="item-select
                                    w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Item --</option>
                                    @foreach ($item as $g)
                                        <option value="{{ $g->id }}">{{ $g->item_description }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Qty To Build</label>
                                <input type="number" name="qty_built" class="qty-to-build w-full border rounded px-4 py-2"
                                    value="1" min="1">
                            </div>

                            <div>
                                <label for="total_cost" class="block text-sm font-medium text-gray-700">Total Cost</label>
                                <input type="hidden" name="total_cost" value="0">
                                <input type="text"
                                    class="total-cost-display w-full border rounded px-4 py-2 bg-gray-50 text-right"
                                    placeholder="Terisi otomatis" readonly>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="notes" class="w-full border rounded px-2 py-1" placeholder="Masukkan catatan bila ada"></textarea>
                            </div>
                        </div>

                        <!-- Detail Komponen -->
                        <!-- Detail Komponen -->
                        <div class="mt-6 assembly-section">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-semibold">Assembly Components</h3>
                                <button type="button" onclick="addComponentRow()"
                                    class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                    + Add Component
                                </button>
                            </div>

                            <table class="w-full border border-gray-300 text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border text-center px-2 py-1">Component</th>
                                        <th class="border text-center px-2 py-1">Unit</th>
                                        <th class="border text-right px-2 py-1">Qty Used</th>
                                        <th class="border text-right px-2 py-1">Unit Cost</th>
                                        <th class="border text-right px-2 py-1">Total Cost</th>
                                        <th class="border text-right px-2 py-1">Available</th>
                                        <th class="border text-center px-2 py-1">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="assembly-body"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Journal Preview -->
                    <div id="journal_report" class="tab-content hidden">
                        <h2 class="text-lg font-semibold mb-4">Journal Preview</h2>
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
                    <div class="mt-6 flex space-x-4">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                            Create Assembly
                        </button>
                        <a href="{{ route('item_assembly.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('select[name="location_id"]').addEventListener('change', function() {
            let locationId = this.value;
            let itemSelect = document.querySelector('select[name="parent_item_id"]');

            // Reset item select
            itemSelect.innerHTML = '<option value="">-- Item --</option>';

            if (locationId) {
                fetch(`/items/by-location/${locationId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(item => {
                            let option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = item.item_description;
                            itemSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    </script>

    <script>
        // Tab switching
        document.querySelectorAll('.tab-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.tab-link').forEach(el => el.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
                this.classList.add('active');
                document.querySelector(this.getAttribute('href')).classList.remove('hidden');
            });
        });

        // Nanti tambahkan JS fetch parent item info + components mirip BOM
    </script>
    <script>
        let componentIndex = 0;
        let currentParentAccount = null;
        let currentComponentAccounts = {};

        // Tambah baris komponen
        function addComponentRow() {
            const tbody = document.querySelector('.assembly-body');
            const row = document.createElement('tr');
            row.setAttribute('data-index', componentIndex);

            row.innerHTML = `
        <td class="border px-2 py-1">
            <select name="component_id[]" class="component-select w-full border rounded px-2 py-1">
                <option value="">-- Pilih Komponen --</option>
                @foreach ($item as $g)
                    <option value="{{ $g->id }}">{{ $g->item_description }}</option>
                @endforeach
            </select>
        </td>
        <td class="border px-2 py-1">
            <input type="text" name="unit[]" class="unit w-full border rounded px-2 py-1 text-center" readonly>
        </td>
        <td class="border px-2 py-1">
            <input type="number" name="qty_used[]" class="qty-used w-full border rounded px-2 py-1 text-right" min="0" value="1">
        </td>
        <td class="border px-2 py-1">
            <input type="number" step="0.01" name="unit_cost[]" class="unit-cost w-full border rounded px-2 py-1 text-right" value="0">
        </td>
        <td class="border px-2 py-1">
            <input type="text" class="total-cost-display w-full border rounded px-2 py-1 text-right" value="0" readonly>
            <input type="hidden" name="component_total_cost[]" class="total-cost-hidden" value="0">
        </td>
        <td class="border px-2 py-1">
            <input type="text" name="available[]" class="available w-full border rounded px-2 py-1 text-right" value="0" readonly>
        </td>
        <td class="border px-2 py-1 text-center">
            <button type="button" onclick="removeComponentRow(this)" class="text-red-500">🗑️</button>
        </td>
        `;

            tbody.appendChild(row);

            // Listener dropdown komponen
            const select = row.querySelector('.component-select');
            select.addEventListener('change', function() {
                let itemId = this.value;
                if (!itemId) return;

                const locationId = document.getElementById('from_location_id').value;
                fetch(`/items/${itemId}/info?location_id=${locationId}`)

                    .then(res => res.json())
                    .then(data => {
                        row.querySelector('.unit').value = data.unit ?? '-';
                        row.querySelector('.available').value = data.current_stock ?? 0;
                        row.querySelector('.unit-cost').value = data.unit_cost ?? 0;

                        calculateRowTotal(row);
                    });

                // Ambil akun komponen
                fetch(`/items/${itemId}/accounts`)
                    .then(res => res.json())
                    .then(data => {
                        currentComponentAccounts[itemId] = data.asset_account;
                        updateJournalPreview();
                    });
            });

            componentIndex++;
        }

        // Hapus baris
        function removeComponentRow(btn) {
            btn.closest('tr').remove();
            updateAssemblyTotal();
        }

        // Hitung ulang 1 baris
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

        // Hitung ulang semua total
        function updateAssemblyTotal() {
            let total = 0;
            document.querySelectorAll('.total-cost-hidden').forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            document.querySelector('input[name="total_cost"]').value = total;
            document.querySelector('.total-cost-display').value = total.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            });

            updateJournalPreview();
        }

        // Sync Qty Build ke Qty Used
        document.querySelector('.qty-to-build').addEventListener('input', function() {
            let qtyBuild = parseFloat(this.value) || 1;

            document.querySelectorAll('.assembly-body tr').forEach(row => {
                let qtyUsedInput = row.querySelector('.qty-used');
                let baseQty = 1; // default 1
                qtyUsedInput.value = qtyBuild * baseQty;

                calculateRowTotal(row);
            });
        });

        // Listener input qty/cost → hitung ulang
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('qty-used') || e.target.classList.contains('unit-cost')) {
                const row = e.target.closest('tr');
                calculateRowTotal(row);
            }
        });

        // Ambil akun parent item → debit finished goods
        document.querySelector('.item-select').addEventListener('change', function() {
            let itemId = this.value;
            if (!itemId) return;

            fetch(`/items/${itemId}/accounts`)
                .then(res => res.json())
                .then(data => {
                    currentParentAccount = data.asset_account;
                    updateJournalPreview();
                });
        });

        // Update Journal Preview
        function updateJournalPreview() {
            const tbody = document.querySelector('.journal-body');
            const totalDebitCell = document.querySelector('.total-debit');
            const totalCreditCell = document.querySelector('.total-credit');
            tbody.innerHTML = '';

            let totalDebit = 0;
            let totalCredit = 0;
            let totalCost = parseFloat(document.querySelector('input[name="total_cost"]').value) || 0;

            if (totalCost <= 0 || !currentParentAccount) {
                tbody.innerHTML = `<tr><td colspan="3" class="text-center py-2 text-gray-500">Tidak ada journal</td></tr>`;
                totalDebitCell.textContent = '0.00';
                totalCreditCell.textContent = '0.00';
                return;
            }

            // Debit Produk Jadi
            tbody.innerHTML += `
        <tr>
            <td class="border px-2 py-1">[${currentParentAccount.kode}] ${currentParentAccount.nama}</td>
            <td class="border px-2 py-1 text-right">${totalCost.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</td>
            <td class="border px-2 py-1 text-right">0.00</td>
        </tr>`;
            totalDebit += totalCost;

            // Credit tiap komponen
            document.querySelectorAll('.assembly-body tr').forEach(row => {
                let compId = row.querySelector('.component-select').value;
                let amount = parseFloat(row.querySelector('.total-cost-hidden').value) || 0;
                if (amount <= 0 || !compId) return;

                let acc = currentComponentAccounts[compId];
                if (!acc) return;

                tbody.innerHTML += `
            <tr>
                <td class="border px-2 py-1">[${acc.kode}] ${acc.nama}</td>
                <td class="border px-2 py-1 text-right">0.00</td>
                <td class="border px-2 py-1 text-right">${amount.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</td>
            </tr>`;
                totalCredit += amount;
            });

            // Update footer
            totalDebitCell.textContent = totalDebit.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            });
            totalCreditCell.textContent = totalCredit.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            });
        }
    </script>

    <script>
        let currentParentAccount = null;
        let currentComponentAccounts = {};

        document.querySelector('.parent-item-select').addEventListener('change', function() {
            let itemId = this.value;
            if (!itemId) return;

            fetch(`/items/${itemId}/accounts`)
                .then(res => res.json())
                .then(data => {
                    currentParentAccount = data.asset_account; // produk jadi masuk debit
                    updateJournalPreview();
                });
        });

        // Sinkronisasi Qty Build → Qty Used
        document.querySelector('.qty-to-build').addEventListener('input', function() {
            let qtyBuild = parseFloat(this.value) || 1;

            document.querySelectorAll('.assembly-body tr').forEach(row => {
                let qtyUsedInput = row.querySelector('.qty-used');
                let baseQty = 1; // default 1 (bisa diubah kalau ada base_qty per komponen)
                qtyUsedInput.value = qtyBuild * baseQty;

                calculateRowTotal(row);
            });
        });

        // Hitung ulang 1 row
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

        // Hitung ulang total cost
        function updateAssemblyTotal() {
            let total = 0;
            document.querySelectorAll('.total-cost-hidden').forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            document.querySelector('input[name="total_cost"]').value = total;
            document.querySelector('.total-cost-display').value = total.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            });

            updateJournalPreview();
        }

        // Update Journal Preview
        function updateJournalPreview() {
            const tbody = document.querySelector('.journal-body');
            const totalDebitCell = document.querySelector('.total-debit');
            const totalCreditCell = document.querySelector('.total-credit');
            tbody.innerHTML = '';

            let totalDebit = 0;
            let totalCredit = 0;
            let totalCost = parseFloat(document.querySelector('input[name="total_cost"]').value) || 0;

            if (totalCost <= 0 || !currentParentAccount) {
                tbody.innerHTML = `<tr><td colspan="3" class="text-center py-2 text-gray-500">Tidak ada journal</td></tr>`;
                totalDebitCell.textContent = '0.00';
                totalCreditCell.textContent = '0.00';
                return;
            }

            // Debit Produk Jadi
            tbody.innerHTML += `
        <tr>
            <td class="border px-2 py-1">[${currentParentAccount.kode}] ${currentParentAccount.nama}</td>
            <td class="border px-2 py-1 text-right">${totalCost.toLocaleString('id-ID', {minimumFractionDigits:2})}</td>
            <td class="border px-2 py-1 text-right">0.00</td>
        </tr>`;
            totalDebit += totalCost;

            // Credit Tiap Komponen
            document.querySelectorAll('.assembly-body tr').forEach(row => {
                let compId = row.querySelector('.component-select').value;
                let amount = parseFloat(row.querySelector('.total-cost-hidden').value) || 0;
                if (amount <= 0 || !compId) return;

                let acc = currentComponentAccounts[compId];
                if (!acc) return;

                tbody.innerHTML += `
            <tr>
                <td class="border px-2 py-1">[${acc.kode}] ${acc.nama}</td>
                <td class="border px-2 py-1 text-right">0.00</td>
                <td class="border px-2 py-1 text-right">${amount.toLocaleString('id-ID', {minimumFractionDigits:2})}</td>
            </tr>`;
                totalCredit += amount;
            });

            // Update footer
            totalDebitCell.textContent = totalDebit.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            });
            totalCreditCell.textContent = totalCredit.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            });
        }

        // Tambahkan autofill akun komponen
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('component-select')) {
                let itemId = e.target.value;
                if (!itemId) return;

                fetch(`/items/${itemId}/accounts`)
                    .then(res => res.json())
                    .then(data => {
                        currentComponentAccounts[itemId] = data.asset_account;
                        updateJournalPreview();
                    });
            }
        });
    </script>
@endsection

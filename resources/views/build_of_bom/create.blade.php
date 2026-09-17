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

            {{-- 🔹 Pesan Success (jika ada flash) --}}
            @if (session('success'))
                <div class="mb-4 p-4 rounded bg-green-100 border border-green-400 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            {{-- 🔹 Pesan Error umum (exception / gagal simpan) --}}
            @if (session('error'))
                <div class="mb-4 p-4 rounded bg-red-100 border border-red-400 text-red-700">
                    {{ session('error') }}
                </div>
            @endif
            <div id="tabs" class="type-section">
                <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                    <li><a href="#select_item" class="tab-link active">Process Build</a></li>
                    <li><a href="#journal_report" class="tab-link">Journal Report</a></li>
                </ul>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6">
                <form method="POST"
                    action="{{ isset($build_of_bom) ? route('build_of_bom.update', $build_of_bom->id) : route('build_of_bom.store') }}">
                    @csrf
                    @if (isset($build_of_bom))
                        @method('PUT')
                    @endif

                    <!-- Tab Content -->
                    <div id="select_item" class="tab-content">
                        <h2 class="text-lg font-semibold mb-4">Process Build</h2>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label for="" class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="date" class="w-full border rounded px-4 py-2 bg-gray-50">
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
                                <input type="text" class="item-description w-full border rounded px-4 py-2 bg-gray-50"
                                    placeholder="Terisi otomatis" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Unit</label>
                                <input type="text" class="item-unit w-full border rounded px-4 py-2 bg-gray-50"
                                    placeholder="Terisi otomatis" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Current Stock</label>
                                <input type="text" class="item-stock w-full border rounded px-4 py-2 bg-gray-50"
                                    placeholder="Terisi otomatis" readonly>
                            </div>
                            <div>
                                <label for="" class="block text-sm font-medium text-gray-700">Qty To Build</label>
                                <input type="number" name="qty_to_build"
                                    class="qty-to-build w-full border rounded px-4 py-2" value="1" min="1">
                            </div>
                            <div>
                                <label for="total_cost" class="block text-sm font-medium text-gray-700">Total Cost</label>
                                <input type="hidden" name="total_cost" value="0">
                                <input type="text"
                                    class="total-cost-display w-full border rounded px-4 py-2 bg-gray-50 text-right"
                                    placeholder="Terisi otomatis" readonly>
                            </div>
                            <div>
                                <label for="note" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="notes" id="note" class="w-full border rounded px-2 py-1" placeholder="Masukkan Note bila ada"></textarea>
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
                    </div>
                    <div id="journal_report" class="tab-content hidden">
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
                                    <td colspan="3" class="text-center py-2 text-gray-500">
                                        Tidak ada journal
                                    </td>
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
                            {{ isset($build_of_bom) ? 'Update' : 'Create' }} Build Of BOM
                        </button>
                        <a href="{{ route('build_of_bom.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.querySelector('select[name="from_location_id"]').addEventListener('change', function() {
            let locationId = this.value;
            let itemSelect = document.querySelector('select[name="item_id"]');

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
        let currentAccounts = {}; // akun produk utama
        let currentBomAccounts = {}; // akun tiap komponen BOM

        // Listener select item
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
                    currentAccounts = {};
                    currentBomAccounts = {};
                    updateJournalPreview(0);
                    return;
                }

                // 🔹 Ambil info item
                fetch(`/items/${itemId}/info`)
                    .then(res => res.json())
                    .then(data => {
                        descInput.value = data.description;
                        unitInput.value = data.unit;
                        stockInput.value = data.current_stock;
                    });

                // 🔹 Ambil akun item utama
                fetch(`/items/${itemId}/accounts`)
                    .then(res => res.json())
                    .then(data => {
                        currentAccounts = data; // simpan akun produk utama
                        updateJournalPreview(0);
                    });

                // 🔹 Ambil BOM detail + akun komponen
                const locationId = document.querySelector('[name="from_location_id"]').value;
                fetch(`/items/${itemId}/bom?location_id=${locationId}`)
                    .then(res => res.json())
                    .then(data => {
                        tbody.innerHTML = '';
                        currentBomAccounts = {}; // reset akun komponen

                        if (data.details.length > 0) {
                            data.details.forEach(d => {
                                // Simpan akun komponen (pastikan API kirim field account)
                                currentBomAccounts[d.component_id] = d.account ?? null;

                                tbody.innerHTML += `
                            <tr data-component="${d.component_id}">
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
                                        class="w-full border rounded px-2 py-1 bg-gray-50 text-right" readonly>
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

                    // Hitung Required Qty
                    let newQtyPer = baseQtyPer * qtyToBuild;
                    row.querySelector('input[name="qty_per[]"]').value = newQtyPer;

                    // Hitung Amount baru
                    let amount = newQtyPer * unitCost;
                    row.querySelector('input[name="amount[]"]').value = amount;
                    row.querySelector('.amount-display').value = amount.toLocaleString('id-ID', {
                        minimumFractionDigits: 2
                    });

                    // Cek status stok
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

                // 🔹 Update journal preview
                updateJournalPreview(totalCost);
            }
        });

        // Fungsi hapus baris
        function hapusBaris(btn) {
            btn.closest('tr').remove();
        }

        // Fungsi update preview journal
        function updateJournalPreview(totalCost) {
            const tbody = document.querySelector('.journal-body');
            const totalDebitCell = document.querySelector('.total-debit');
            const totalCreditCell = document.querySelector('.total-credit');

            tbody.innerHTML = '';
            let totalDebit = 0;
            let totalCredit = 0;

            if (totalCost <= 0 || !currentAccounts.asset_account) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center py-2 text-gray-500">
                        No journal to preview yet
                    </td>
                </tr>
                `;
                totalDebitCell.textContent = '0.00';
                totalCreditCell.textContent = '0.00';
                return;
            }

            // 🔹 Debit: Produk Utama
            tbody.innerHTML += `
                <tr>
                    <td class="border px-2 py-1">
                        [${currentAccounts.asset_account.kode}] ${currentAccounts.asset_account.nama}
                        <input type="hidden" name="journal[kode_akun][]" value="${currentAccounts.asset_account.kode}">
                        <input type="hidden" name="journal[debit][]" value="${totalCost}">
                        <input type="hidden" name="journal[credit][]" value="0">
                    </td>
                    <td class="border px-2 py-1 text-right">
                        ${totalCost.toLocaleString('id-ID',{minimumFractionDigits:2})}
                    </td>
                    <td class="border px-2 py-1 text-right">0.00</td>
                </tr>
            `;
            totalDebit += totalCost;

            // 🔹 Credit: Tiap Komponen BOM
            document.querySelectorAll('.bom-body tr').forEach(row => {
                let compId = row.dataset.component;
                let amount = parseFloat(row.querySelector('input[name="amount[]"]').value) || 0;

                let acc = currentBomAccounts[compId];
                if (!acc) return;

                tbody.innerHTML += `
                    <tr>
                        <td class="border px-2 py-1">
                            [${acc.kode}] ${acc.nama}
                            <input type="hidden" name="journal[kode_akun][]" value="${acc.kode}">
                            <input type="hidden" name="journal[debit][]" value="0">
                            <input type="hidden" name="journal[credit][]" value="${amount}">
                        </td>
                        <td class="border px-2 py-1 text-right">0.00</td>
                        <td class="border px-2 py-1 text-right">
                            ${amount.toLocaleString('id-ID',{minimumFractionDigits:2})}
                        </td>
                    </tr>
                `;
                totalCredit += amount;
            });

            // 🔹 Update footer total
            totalDebitCell.textContent = totalDebit.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            });
            totalCreditCell.textContent = totalCredit.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            });
        }
    </script>
@endsection

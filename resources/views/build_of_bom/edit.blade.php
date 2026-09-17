@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">

                {{-- Error --}}
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

                {{-- Success --}}
                @if (session('success'))
                    <div class="mb-4 p-4 rounded bg-green-100 border border-green-400 text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Tabs --}}
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8">
                        <button type="button" class="tab-btn text-blue-600 border-b-2 border-blue-600 px-1 py-2"
                            data-tab="edit">Edit BOM</button>
                        <button type="button" class="tab-btn text-gray-500 hover:text-blue-600 px-1 py-2"
                            data-tab="journal">Journal Preview</button>
                    </nav>
                </div>

                {{-- Edit BOM Tab --}}
                <div id="tab-edit" class="tab-content">
                    <form method="POST" action="{{ route('build_of_bom.update', $build->id) }}">
                        @csrf
                        @method('PUT')

                        <h2 class="text-lg font-semibold mb-4">Edit Build Of BOM</h2>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="date" value="{{ old('date', $build->date) }}"
                                    class="w-full border rounded px-4 py-2 bg-gray-50">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Location</label>
                                <select disabled class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50">
                                    @foreach ($fromLocation as $g)
                                        <option value="{{ $g->id }}"
                                            {{ $build->from_location_id == $g->id ? 'selected' : '' }}>
                                            {{ $g->kode_lokasi }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="from_location_id" value="{{ $build->from_location_id }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Item</label>
                                <select name="item_id"
                                    class="item-select w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50">
                                    @foreach ($item as $g)
                                        <option value="{{ $g->id }}"
                                            {{ $build->item_id == $g->id ? 'selected' : '' }}>
                                            {{ $g->item_description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Qty To Build</label>
                                <input type="number" name="qty_to_build" value="{{ $build->qty_to_build }}"
                                    class="qty-to-build w-full border rounded px-4 py-2">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Cost</label>
                                <input type="hidden" name="total_cost" value="{{ $build->total_cost }}">
                                <input type="text"
                                    class="total-cost-display w-full border rounded px-4 py-2 bg-gray-50 text-right"
                                    value="{{ number_format($build->total_cost, 2) }}" readonly>
                            </div>

                            <div class="col-span-4">
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="notes" class="w-full border rounded px-2 py-1">{{ $build->notes }}</textarea>
                            </div>
                        </div>

                        {{-- Detail Komponen --}}
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-2">Bill Of Materials Formula</h3>
                            <table class="w-full border border-gray-300 text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border px-2 py-1">Component</th>
                                        <th class="border px-2 py-1">Unit</th>
                                        <th class="border px-2 py-1 text-right">Required Per</th>
                                        <th class="border px-2 py-1 text-right">Unit Cost</th>
                                        <th class="border px-2 py-1 text-right">Qty Total</th>
                                        <th class="border px-2 py-1 text-right">Cost</th>
                                    </tr>
                                </thead>
                                <tbody class="bom-body">
                                    @foreach ($build->details as $d)
                                        @php
                                            $qty = $d->component
                                                ->quantities()
                                                ->where('location_id', $build->from_location_id)
                                                ->first();
                                            $compUnitCost =
                                                $qty && $qty->on_hand_qty > 0
                                                    ? $qty->on_hand_value / $qty->on_hand_qty
                                                    : 0;
                                        @endphp
                                        <tr>
                                            <td class="border px-2 py-1">
                                                {{ $d->component->item_description }}
                                                <input type="hidden" name="component_id[]"
                                                    value="{{ $d->component_item_id }}">
                                            </td>
                                            <td class="border px-2 py-1" name="unit[]" value="{{ $d->unit }}">
                                                {{ $d->unit }}</td>
                                            <td class="border px-2 py-1 text-right">
                                                <input type="number" name="base_qty_per[]" value="{{ $d->qty_per_unit }}"
                                                    class="w-full border rounded text-right bg-gray-50" readonly>
                                            </td>
                                            <td class="border px-2 py-1 text-right">
                                                <input type="text" value="{{ number_format($compUnitCost, 2) }}"
                                                    class="w-full border rounded text-right bg-gray-50" readonly>
                                                <input type="hidden" name="unit_cost[]" value="{{ $compUnitCost }}">
                                            </td>
                                            <td class="border px-2 py-1 text-right">
                                                <input type="number" name="qty_per[]" value="{{ $d->qty_total }}"
                                                    class="w-full border rounded text-right">
                                            </td>
                                            <td class="border px-2 py-1 text-right">
                                                <input type="number" name="amount[]" value="{{ $d->cost_component }}"
                                                    class="w-full border rounded text-right">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Tombol --}}
                        <div class="mt-6 flex space-x-4">
                            <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                                Update Build Of BOM
                            </button>
                            <a href="{{ route('build_of_bom.index') }}"
                                class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Journal Preview Tab --}}
                {{-- Journal Preview Tab --}}
                <div id="tab-journal" class="tab-content hidden">
                    <h3 class="text-lg font-semibold mb-4">Journal Preview</h3>
                    <table class="w-full border border-gray-300 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-2 py-1">Kode Akun</th>
                                <th class="border px-2 py-1">Nama Akun</th>
                                <th class="border px-2 py-1 text-right">Debit</th>
                                <th class="border px-2 py-1 text-right">Credit</th>
                                <th class="border px-2 py-1">Comment</th>
                            </tr>
                        </thead>
                        <tbody id="journal-body"></tbody>
                        <tfoot class="bg-gray-50 font-semibold">
                            <tr>
                                <td colspan="2" class="border px-2 py-1 text-right">Total</td>
                                <td id="total-debit" class="border px-2 py-1 text-right">0.00</td>
                                <td id="total-credit" class="border px-2 py-1 text-right">0.00</td>
                                <td class="border px-2 py-1"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qtyToBuildInput = document.querySelector('input[name="qty_to_build"]');
            const journalBody = document.getElementById('journal-body');
            const itemAccounts = @json($itemAccounts);

            function recalc() {
                let qtyToBuild = parseFloat(qtyToBuildInput.value) || 0;
                let total = 0;
                let totalDebit = 0;
                let totalCredit = 0;
                let componentRows = [];

                document.querySelectorAll('.bom-body tr').forEach(function(row) {
                    let compId = row.querySelector('input[name="component_id[]"]').value;
                    let compName = row.querySelector('td').innerText.trim();
                    let requiredPer = parseFloat(row.querySelector('input[name="base_qty_per[]"]').value) ||
                        0;
                    let unitCost = parseFloat(row.querySelector('input[name="unit_cost[]"]').value) || 0;
                    let qtyTotalEl = row.querySelector('input[name="qty_per[]"]');
                    let costEl = row.querySelector('input[name="amount[]"]');

                    let qtyTotal = requiredPer * qtyToBuild;
                    qtyTotalEl.value = qtyTotal;

                    let cost = unitCost * qtyTotal;
                    costEl.value = cost.toFixed(2);

                    total += cost;

                    let akun = itemAccounts[compId] || {
                        kode_akun: '-',
                        nama_akun: compName
                    };
                    componentRows.push({
                        kode_akun: akun.kode_akun,
                        nama_akun: akun.nama_akun,
                        debit: 0,
                        credit: cost
                    });
                });

                // Update total cost
                document.querySelector('input[name="total_cost"]').value = total;
                document.querySelector('.total-cost-display').value = total.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Update Journal Preview
                journalBody.innerHTML = '';

                // Produk jadi (Debit)
                let productId = document.querySelector('select[name="item_id"]').value;
                let productAcc = itemAccounts[productId] || {
                    kode_akun: '-',
                    nama_akun: 'Produk Jadi'
                };
                journalBody.innerHTML += `
        <tr>
            <td class="border px-2 py-1 text-center">${productAcc.kode_akun}</td>
            <td class="border px-2 py-1">${productAcc.nama_akun}</td>
            <td class="border px-2 py-1 text-right">${total.toFixed(2)}</td>
            <td class="border px-2 py-1 text-right">0.00</td>
            <td class="border px-2 py-1">Persediaan Produk Jadi</td>
        </tr>`;
                totalDebit += total;

                // Komponen (Credit)
                componentRows.forEach(r => {
                    journalBody.innerHTML += `
            <tr>
                <td class="border px-2 py-1 text-center">${r.kode_akun}</td>
                <td class="border px-2 py-1">${r.nama_akun}</td>
                <td class="border px-2 py-1 text-right">${r.debit.toFixed(2)}</td>
                <td class="border px-2 py-1 text-right">${r.credit.toFixed(2)}</td>
                <td class="border px-2 py-1">Pemakaian Komponen</td>
            </tr>`;
                    totalCredit += r.credit;
                });

                // Update Total Debit & Credit
                document.getElementById('total-debit').textContent = totalDebit.toFixed(2);
                document.getElementById('total-credit').textContent = totalCredit.toFixed(2);
            }


            // Tabs toggle
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove(
                        'text-blue-600', 'border-b-2', 'border-blue-600'));
                    this.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.add(
                        'hidden'));
                    document.getElementById('tab-' + this.dataset.tab).classList.remove('hidden');
                });
            });

            qtyToBuildInput.addEventListener('input', recalc);
            recalc();
        });
    </script>
@endsection

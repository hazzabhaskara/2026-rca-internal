@extends('layouts.app')
@section('content')
    @if (session('error'))
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    @if (session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="max-w-full border rounded text-sm mx-auto py-10 px-6">
        <h2 class="text-2xl font-semibold mb-6">Edit Inventory</h2>

        <form action="{{ route('inventory.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-6 rounded-lg shadow space-y-6">
                {{-- Header --}}
                <div>
                    <label class="font-semibold text-gray-700 block mb-2">Item</label>

                    <label for="item_number" class="mr-4">Number</label>
                    <input type="text" name="item_number" value="{{ old('item_number', $item->item_number) }}"
                        class="form-input w-1/8 border rounded px-2 py-1 text-sm">
                    @error('item_number')
                        <div class="text-red-500 text-xs">{{ $message }}</div>
                    @enderror

                    <label for="item_description" class="ml-4 mr-4">Item Name</label>
                    <input type="text" name="item_description"
                        value="{{ old('item_description', $item->item_description) }}"
                        class="form-input w-1/3 border rounded px-2 py-1 text-sm">
                    @error('item_description')
                        <div class="text-red-500 text-xs">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="font-semibold text-gray-700 block mb-2">Type</label>
                    @php $typeVal = old('type', $item->type); @endphp
                    <label class="mr-4">
                        <input type="radio" name="type" value="inventory"
                            {{ $typeVal === 'inventory' ? 'checked' : '' }}>
                        Inventory
                    </label>
                    <label>
                        <input type="radio" name="type" value="service" {{ $typeVal === 'service' ? 'checked' : '' }}>
                        Service
                    </label>
                    @error('type')
                        <div class="text-red-500 text-xs">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tabs header --}}
                <div id="inventory-tabs" class="type-section {{ $typeVal === 'inventory' ? '' : 'hidden' }}">
                    <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                        <li><a href="#quantities" class="tab-link">Quantities</a></li>
                        <li><a href="#units" class="tab-link">Units</a></li>
                        <li><a href="#pricing" class="tab-link">Pricing</a></li>
                        <li><a href="#vendors" class="tab-link">Vendors</a></li>
                        <li><a href="#linked" class="tab-link">Linked Account</a></li>
                        <li><a href="#build" class="tab-link">Build</a></li>
                        <li><a href="#taxes" class="tab-link">Taxes</a></li>
                        <li><a href="#description" class="tab-link">Description</a></li>
                        <li><a href="#picture" class="tab-link">Picture</a></li>
                    </ul>
                </div>
                <div id="service-tabs" class="type-section {{ $typeVal === 'service' ? '' : 'hidden' }}">
                    <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                        <li><a href="#unitsService" class="tab-link">Units</a></li>
                        <li><a href="#pricingService" class="tab-link">Pricing</a></li>
                        <li><a href="#vendorsService" class="tab-link">Vendors</a></li>
                        <li><a href="#linkedService" class="tab-link">Linked Account</a></li>
                        <li><a href="#taxesService" class="tab-link">Taxes</a></li>
                        <li><a href="#descriptionService" class="tab-link">Description</a></li>
                        <li><a href="#pictureService" class="tab-link">Picture</a></li>
                    </ul>
                </div>

                {{-- Quantities --}}
                <div id="quantities" class="tab-content">
                    <h3 class="font-semibold text-lg mb-2">Quantities per Location</h3>

                    @foreach ($quantities as $q)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border rounded p-4 mb-4">
                            <div>
                                <label class="block">Location</label>
                                <select name="quantities[{{ $loop->index }}][location_id]"
                                    class="w-full border rounded-lg px-4 py-2">
                                    <option value="">-- Location --</option>
                                    @foreach ($lokasiInventory as $g)
                                        <option value="{{ $g->id }}"
                                            {{ $q->location_id == $g->id ? 'selected' : '' }}>
                                            {{ $g->kode_lokasi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block">On Hand Quantity</label>
                                <input type="number" name="quantities[{{ $loop->index }}][on_hand_qty]" readonly
                                    value="{{ $q->on_hand_qty }}"
                                    class="form-input w-full border rounded px-2 py-1 text-sm" />
                            </div>

                            <div>
                                <label class="block">On Hand Value</label>
                                <input type="text" readonly value="{{ number_format($q->on_hand_value, 2, '.', ',') }}"
                                    class="form-input w-full border rounded px-2 py-1 text-sm" />

                                <input type="hidden" name="quantities[{{ $loop->index }}][on_hand_value]"
                                    value="{{ $q->on_hand_value }}">

                            </div>
                        </div>
                    @endforeach
                </div>


                {{-- Units (inventory) --}}
                @php $u = $item->units; @endphp
                <div id="units" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Units</h3>
                    <div class="mb-4">
                        <label class="block">Stocking Unit of Measure</label>
                        <input type="text" name="stocking_unit"
                            value="{{ old('stocking_unit', optional($u)->unit_of_measure) }}"
                            class="form-input w-full border rounded px-2 py-1 text-sm" />
                    </div>

                    <div class="mb-4">
                        <label>
                            <input type="checkbox" name="selling_same_as_stocking" value="1"
                                {{ old('selling_same_as_stocking', optional($u)->selling_same_as_stocking) ? 'checked' : '' }} />
                            Selling unit same as stocking unit
                        </label>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block">Selling Unit (if different)</label>
                            <input type="text" name="selling_unit"
                                value="{{ old('selling_unit', optional($u)->selling_unit) }}"
                                class="form-input w-full border rounded px-2 py-1 text-sm" />
                        </div>
                        <div>
                            <label class="block">Selling Relationship</label>
                            <input type="number" name="selling_relationship"
                                value="{{ old('selling_relationship', optional($u)->selling_relationship) }}"
                                class="form-input w-full border rounded px-2 py-1 text-sm" />
                        </div>
                    </div>

                    <div class="mb-4 mt-4">
                        <label>
                            <input type="checkbox" name="buying_same_as_stocking" value="1"
                                {{ old('buying_same_as_stocking', optional($u)->buying_same_as_stocking) ? 'checked' : '' }} />
                            Buying unit same as stocking unit
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block">Buying Unit (if different)</label>
                            <input type="text" name="buying_unit"
                                value="{{ old('buying_unit', optional($u)->buying_unit) }}"
                                class="form-input w-full border rounded px-2 py-1 text-sm" />
                        </div>
                        <div>
                            <label class="block">Buying Relationship</label>
                            <input type="number" name="buying_relationship"
                                value="{{ old('buying_relationship', optional($u)->buying_relationship) }}"
                                class="form-input w-full border rounded px-2 py-1 text-sm" />
                        </div>
                    </div>
                </div>

                {{-- Pricing (inventory) --}}
                <div id="pricing" class="tab-content hidden">
                    <table class="table-auto w-full border-collapse border border-gray-200 text-sm" id="pricingTable">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th>Price List</th>
                                <th>Price Per Selling Unit</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($priceListInventory as $index => $pl)
                                @php
                                    $pref = optional($pricingPrefill->get($pl->description))->price;
                                    $val = old("pricing.$index.price", $pref);
                                @endphp
                                <tr>
                                    <td class="px-4 py-2 border text-center">
                                        <input type="text" name="pricing[{{ $index }}][name]"
                                            class="form-input w-full border rounded px-2 py-1 text-sm"
                                            value="{{ old("pricing.$index.name", $pl->description) }}">
                                    </td>
                                    <td class="px-4 py-2 border text-right">
                                        <input type="text" name="pricing[{{ $index }}][price]"
                                            class="form-input w-full border rounded px-2 py-1 text-sm"
                                            value="{{ $val }}">
                                    </td>
                                    <td class="px-4 py-2 border text-center">
                                        <button type="button"
                                            class="remove-row bg-red-500 text-white px-2 py-1 rounded text-xs">Hapus</button>
                                    </td>
                                </tr>
                            @endforeach
                            {{-- Harga existing lain yang tidak ada di master (jaga-jaga) --}}
                            @foreach ($item->prices->whereNotIn('price_list_name', $priceListInventory->pluck('description')) as $extra)
                                @php $idx = $loop->index + $priceListInventory->count(); @endphp
                                <tr>
                                    <td class="px-4 py-2 border text-center">
                                        <input type="text" name="pricing[{{ $idx }}][name]"
                                            class="form-input w-full border rounded px-2 py-1 text-sm"
                                            value="{{ old("pricing.$idx.name", $extra->price_list_name) }}">
                                    </td>
                                    <td class="px-4 py-2 border text-right">
                                        <input type="text" name="pricing[{{ $idx }}][price]"
                                            class="form-input w-full border rounded px-2 py-1 text-sm"
                                            value="{{ old("pricing.$idx.price", $extra->price) }}">
                                    </td>
                                    <td class="px-4 py-2 border text-center">
                                        <button type="button"
                                            class="remove-row bg-red-500 text-white px-2 py-1 rounded text-xs">Hapus</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-2">
                        <button type="button" id="addRow" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                            + Tambah Data
                        </button>
                    </div>
                </div>

                {{-- Vendors (inventory & service) --}}
                {{-- Vendors Inventory --}}
                <div id="vendors" class="tab-content hidden">
                    <div>
                        <label class="block">Vendor</label>
                        @php
                            $currentVendorId = old('vendor_id_inventory', optional($item->vendors)->vendor_id);
                        @endphp

                        <select name="vendor_id_inventory" id="vendor_id_inventory"
                            class="w-full border rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Vendor --</option>
                            @foreach ($vendors as $g)
                                <option value="{{ $g->id }}"
                                    {{ (string) $currentVendorId === (string) $g->id ? 'selected' : '' }}>
                                    {{ $g->nama_vendors }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Vendors Service --}}
                <div id="vendorsService" class="tab-content hidden">
                    <div>
                        <label class="block">Vendor</label>
                        @php
                            $currentVendorId = old('vendor_id_service', optional($item->vendors)->vendor_id);
                        @endphp
                        <select name="vendor_id_service" id="vendor_id_service"
                            class="w-full border rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Vendor --</option>
                            @foreach ($vendors as $g)
                                <option value="{{ $g->id }}"
                                    {{ (string) $currentVendorId === (string) $g->id ? 'selected' : '' }}>
                                    {{ $g->nama_vendors }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>


                {{-- Linked COA (inventory) --}}
                @php $acc = $item->accounts; @endphp
                <div id="linked" class="tab-content {{ $item->type === 'inventory' ? '' : 'hidden' }}">
                    <h3 class="font-semibold text-lg mb-2">Account Linkings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Asset Account</label>
                            <select name="asset_account_id" class="form-select w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Pilih Akun Aset --</option>
                                @foreach ($accounts->where('tipe_akun', 'Aset') as $account)
                                    <option value="{{ $account->id }}"
                                        {{ (string) old('asset_account_id', optional($acc)->asset_account_id) === (string) $account->id ? 'selected' : '' }}>
                                        {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Revenue Account</label>
                            <select name="inventory_revenue_account_id"
                                class="form-select w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Pilih Akun Pendapatan --</option>
                                @foreach ($accounts->where('tipe_akun', 'Pendapatan') as $account)
                                    <option value="{{ $account->id }}"
                                        {{ (string) old('revenue_account_id', optional($acc)->revenue_account_id) === (string) $account->id ? 'selected' : '' }}>
                                        {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">COGS Account</label>
                            <select name="cogs_account_id" class="form-select w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Pilih Akun Beban Pokok Penjualan --</option>
                                @foreach ($accounts->where('tipe_akun', 'Beban') as $account)
                                    <option value="{{ $account->id }}"
                                        {{ (string) old('cogs_account_id', optional($acc)->cogs_account_id) === (string) $account->id ? 'selected' : '' }}>
                                        {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Variance Account</label>
                            <select name="variance_account_id"
                                class="form-select w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Pilih Akun Selisih Harga --</option>
                                @foreach ($accounts->where('tipe_akun', 'Beban') as $account)
                                    <option value="{{ $account->id }}"
                                        {{ (string) old('variance_account_id', optional($acc)->variance_account_id) === (string) $account->id ? 'selected' : '' }}>
                                        {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Build --}}
                <div id="build" class="tab-content hidden">
                    <table class="table-auto w-full border-collapse border border-gray-200 text-sm" id="buildTable">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th>Item</th>
                                <th>Unit</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $details = optional($item->builds->first())->details ?? collect();
                            @endphp

                            @forelse ($details as $i => $d)
                                <tr data-index="{{ $i }}">

                                    <td>
                                        <select name="build[{{ $i }}][item_id]"
                                            class="item-select form-select w-full border rounded px-2 py-1 text-sm">
                                            <option value="">-- Pilih Item --</option>
                                            @foreach ($items as $it)
                                                <option value="{{ $it->id }}"
                                                    data-unit="{{ $it->units->unit_of_measure ?? '' }}"
                                                    data-description="{{ $it->item_description }}"
                                                    {{ (string) old("build.$i.item_id", $d->item_id) === (string) $it->id ? 'selected' : '' }}>
                                                    {{ $it->item_description }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="build[{{ $i }}][unit]"
                                            value="{{ old("build.$i.unit", $d->unit) }}"
                                            class="unit-input form-input w-full border rounded px-2 py-1 text-sm">
                                    </td>

                                    <td>
                                        <input type="text" name="build[{{ $i }}][description]"
                                            value="{{ old("build.$i.description", $d->description) }}"
                                            class="desc-input form-input w-full border rounded px-2 py-1 text-sm" readonly>
                                    </td>
                                    <td>
                                        <input type="number" name="build[{{ $i }}][quantity]"
                                            value="{{ old("build.$i.quantity", $d->quantity) }}"
                                            class="qty-input form-input w-full border rounded px-2 py-1 text-sm"
                                            min="0" step="1">
                                    </td>
                                    <td class="px-4 py-2 border text-center">
                                        <button type="button"
                                            class="remove-row-build bg-red-500 text-white px-2 py-1 rounded text-xs">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                {{-- Row kosong awal --}}
                                <tr data-index="0">
                                    <td>
                                        <select name="build[0][item_id]"
                                            class="item-select form-select w-full border rounded px-2 py-1 text-sm">
                                            <option value="">-- Pilih Item --</option>
                                            @foreach ($items as $it)
                                                <option value="{{ $it->id }}"
                                                    data-unit="{{ $it->units->unit_of_measure ?? '' }}"
                                                    data-description="{{ $it->item_description }}">
                                                    {{ $it->item_description }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="build[0][unit]"
                                            class="unit-input form-input w-full border rounded px-2 py-1 text-sm">
                                    </td>
                                    <td>
                                        <input type="text" name="build[0][description]"
                                            class="desc-input form-input w-full border rounded px-2 py-1 text-sm" readonly>
                                    </td>
                                    <td>
                                        <input type="number" name="build[0][quantity]"
                                            class="qty-input form-input w-full border rounded px-2 py-1 text-sm"
                                            min="0" step="1">
                                    </td>
                                    <td class="px-4 py-2 border text-center">
                                        <button type="button"
                                            class="remove-row-build bg-red-500 text-white px-2 py-1 rounded text-xs">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-2">
                        <button type="button" id="addRowBuild" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                            + Tambah Data
                        </button>
                    </div>
                </div>

                {{-- Taxes (inventory) --}}
                <div id="taxes" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Tax Configuration</h3>
                    @error('taxes.*')
                        <div class="text-red-600 text-sm mb-2">{{ $message }}</div>
                    @enderror

                    <table class="table-auto w-full border-collapse border border-gray-200 text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-2">Tax</th>
                                <th class="px-4 py-2">Tax Exempt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($taxes as $t)
                                <tr class="border-b">
                                    <td class="px-4 py-2">
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" name="taxes[]" value="{{ $t->id }}"
                                                class="form-checkbox"
                                                {{ in_array($t->id, old('taxes', $selectedTaxIds ?? [])) ? 'checked' : '' }}>
                                            <span>{{ $t->name }} ({{ $t->rate }}%)</span>
                                        </label>
                                    </td>
                                    <td class="px-4 py-2">
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" name="tax_exempt[]" value="{{ $t->id }}"
                                                class="form-checkbox"
                                                {{ in_array($t->id, old('tax_exempt', $exemptTaxIds ?? [])) ? 'checked' : '' }}>
                                            <span>Exempt {{ $t->name }}</span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Description --}}
                <div id="description" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Description</h3>
                    <textarea name="long_description" class="form-textarea w-full border rounded px-2 py-1 text-sm" rows="5">{{ old('long_description', $item->description) }}</textarea>
                </div>

                {{-- Picture --}}
                <div id="picture" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Picture</h3>

                    @if ($item->picture_path)
                        <div class="mb-2">
                            <span class="text-xs text-gray-500">Current:</span><br>
                            <img src="{{ Storage::disk('public')->url($item->picture_path) }}"
                                class="max-h-24 rounded border">
                        </div>
                    @endif
                    <div class="mb-4">
                        <label class="block">Main Picture</label>
                        <input type="file" name="picture"
                            class="form-input w-full border rounded px-2 py-1 text-sm" />
                    </div>

                    @if ($item->thumbnail_path ?? $item->thubmnail_path)
                        <div class="mb-2">
                            <span class="text-xs text-gray-500">Current Thumbnail:</span><br>
                            <img src="{{ Storage::disk('public')->url($item->thumbnail_path ?? $item->thubmnail_path) }}"
                                class="max-h-16 rounded border">
                        </div>
                    @endif
                    <div class="mb-4">
                        <label class="block">Thumbnail</label>
                        <input type="file" name="thumbnail"
                            class="form-input w-full border rounded px-2 py-1 text-sm" />
                    </div>
                </div>

                {{-- Service tabs (Units, Pricing, Vendors, Linked, Taxes, Description, Picture) --}}
                <div id="unitsService" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Units</h3>
                    <div class="mb-4">
                        <label class="block">Unit of Measure</label>
                        <input type="text" name="unit_of_measure"
                            value="{{ old('unit_of_measure', optional($u)->unit_of_measure) }}"
                            class="form-input w-full border rounded px-2 py-1 text-sm" />
                    </div>
                </div>

                <div id="pricingService" class="tab-content hidden">
                    <table class="table-auto w-full border-collapse border border-gray-200 text-sm"
                        id="pricingTableService">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th>Price List</th>
                                <th>Price Per Selling Unit</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($priceListInventory as $index => $pl)
                                @php
                                    $pref = optional($pricingPrefill->get($pl->description))->price;
                                    $val = old("pricing.$index.price", $pref);
                                @endphp
                                <tr>
                                    <td class="px-4 py-2 border text-center">
                                        <input type="text" name="pricing[{{ $index }}][name]"
                                            class="form-input w-full border rounded px-2 py-1 text-sm"
                                            value="{{ old("pricing.$index.name", $pl->description) }}">
                                    </td>
                                    <td class="px-4 py-2 border text-right">
                                        <input type="text" name="pricing[{{ $index }}][price]"
                                            class="form-input w-full border rounded px-2 py-1 text-sm"
                                            value="{{ $val }}">
                                    </td>
                                    <td class="px-4 py-2 border text-center">
                                        <button type="button"
                                            class="remove-row bg-red-500 text-white px-2 py-1 rounded text-xs">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-2">
                        <button type="button" id="addRowService"
                            class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                            + Tambah Data
                        </button>
                    </div>
                </div>
                <div id="linkedService" class="tab-content {{ $item->type === 'service' ? '' : 'hidden' }}">
                    <h3 class="font-semibold text-lg mb-2">Account Linkings</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Revenue Account</label>
                        <select name="service_revenue_account_id"
                            class="form-select w-full border rounded px-2 py-1 text-sm">
                            <option value="">-- Pilih Account --</option>
                            @foreach ($accounts->where('tipe_akun', 'Pendapatan') as $account)
                                <option value="{{ $account->id }}"
                                    {{ (string) old('revenue_account_id', optional($acc)->revenue_account_id) === (string) $account->id ? 'selected' : '' }}>
                                    {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Receivable</label>
                        <select name="account_receivable_id" class="form-select w-full border rounded px-2 py-1 text-sm">
                            <option value="">-- Pilih Account --</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}"
                                    {{ (string) old('account_receivable_id', optional($acc)->account_receivable_id) === (string) $account->id ? 'selected' : '' }}>
                                    {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Expense Account</label>
                        <select name="expense_account_id" class="form-select w-full border rounded px-2 py-1 text-sm">
                            <option value="">-- Pilih Account --</option>
                            @foreach ($accounts->where('tipe_akun', 'Beban') as $account)
                                <option value="{{ $account->id }}"
                                    {{ (string) old('expense_account_id', optional($acc)->expense_account_id) === (string) $account->id ? 'selected' : '' }}>
                                    {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="taxesService" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Tax Configuration</h3>
                    <table class="table-auto w-full border-collapse border border-gray-200 text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-2">Tax</th>
                                <th class="px-4 py-2">Tax Exempt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($taxes as $t)
                                <tr class="border-b">
                                    <td class="px-4 py-2">
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" name="taxes_service[]" value="{{ $t->id }}"
                                                class="form-checkbox"
                                                {{ in_array($t->id, old('taxes', $selectedTaxIds ?? [])) ? 'checked' : '' }}>
                                            <span>{{ $t->name }} ({{ $t->rate }}%)</span>
                                        </label>
                                    </td>
                                    <td class="px-4 py-2">
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" name="tax_exempt_service[]"
                                                value="{{ $t->id }}" class="form-checkbox"
                                                {{ in_array($t->id, old('tax_exempt', $exemptTaxIds ?? [])) ? 'checked' : '' }}>
                                            <span>Exempt {{ $t->name }}</span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div id="descriptionService" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Description</h3>
                    <textarea name="long_description" class="form-textarea w-full border rounded px-2 py-1 text-sm" rows="5">{{ old('long_description', $item->description) }}</textarea>
                </div>

                <div id="pictureService" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Picture</h3>
                    @if ($item->picture_path)
                        <div class="mb-2">
                            <span class="text-xs text-gray-500">Current:</span><br>
                            <img src="{{ Storage::disk('public')->url($item->picture_path) }}"
                                class="max-h-24 rounded border">
                        </div>
                    @endif
                    <div class="mb-4">
                        <label class="block">Main Picture</label>
                        <input type="file" name="picture"
                            class="form-input w-full border rounded px-2 py-1 text-sm" />
                    </div>

                    @if ($item->thumbnail_path ?? $item->thubmnail_path)
                        <div class="mb-2">
                            <span class="text-xs text-gray-500">Current Thumbnail:</span><br>
                            <img src="{{ Storage::disk('public')->url($item->thumbnail_path ?? $item->thubmnail_path) }}"
                                class="max-h-16 rounded border">
                        </div>
                    @endif
                    <div class="mb-4">
                        <label class="block">Thumbnail</label>
                        <input type="file" name="thumbnail"
                            class="form-input w-full border rounded px-2 py-1 text-sm" />
                    </div>
                </div>

                {{-- Submit --}}
                <div class="mt-6 justify-end flex space-x-4">
                    <a href="{{ route('inventory.index') }}"
                        class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                        Process
                    </button>
                </div>
            </div>
        </form>
    </div>


    <!-- JQUERY DULU -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script>
        $(document).ready(function() {
            $('.form-select').select2({
                placeholder: "Cari account...",
                allowClear: true,
                width: '100%',
            });
        });
    </script>
    {{-- Build JS --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let index = document.querySelectorAll("#buildTable tbody tr").length;
            const tableBody = document.querySelector("#buildTable tbody");
            const addRowBtn = document.getElementById("addRowBuild");

            function rowTemplate(i) {
                return `
                <tr data-index="${i}">
                    <td>
                        <select name="build[${i}][item_id]" 
                            class="item-select form-select w-full border rounded px-2 py-1 text-sm">
                            <option value="">-- Pilih Item --</option>
                            @foreach ($items as $it)
                                <option value="{{ $it->id }}"
                                    data-unit="{{ $it->units->unit_of_measure ?? '' }}"
                                    data-description="{{ $it->item_description }}">
                                    {{ $it->item_description }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text" name="build[${i}][unit]" 
                            class="unit-input form-input w-full border rounded px-2 py-1 text-sm">
                    </td>
                    <td>
                        <input type="text" name="build[${i}][description]" 
                            class="desc-input form-input w-full border rounded px-2 py-1 text-sm" readonly>
                    </td>
                    <td>
                        <input type="number" name="build[${i}][quantity]" 
                            class="qty-input form-input w-full border rounded px-2 py-1 text-sm" min="0" step="1">
                    </td>
                    <td class="px-4 py-2 border text-center">
                        <button type="button" class="remove-row-build bg-red-500 text-white px-2 py-1 rounded text-xs">
                            Hapus
                        </button>
                    </td>
                </tr>`;
            }


            if (addRowBtn) {
                addRowBtn.addEventListener("click", function() {
                    tableBody.insertAdjacentHTML("beforeend", rowTemplate(index));
                    index++;
                });
            }

            tableBody.addEventListener("change", function(e) {
                if (e.target.classList.contains("item-select")) {
                    const select = e.target;
                    const selected = select.options[select.selectedIndex];
                    const unit = selected.getAttribute("data-unit") || "";
                    const desc = selected.getAttribute("data-description") || "";
                    const row = select.closest("tr");
                    row.querySelector(".unit-input").value = unit;
                    row.querySelector(".desc-input").value = desc;
                }
            });

            tableBody.addEventListener("click", function(e) {
                if (e.target.classList.contains("remove-row-build")) {
                    const row = e.target.closest("tr");
                    if (tableBody.querySelectorAll("tr").length === 1) {
                        // jika hanya tersisa 1 baris, kosongkan isinya saja agar selalu ada minimal 1 baris
                        row.querySelector(".item-select").value = "";
                        row.querySelector(".unit-input").value = "";
                        row.querySelector(".desc-input").value = "";
                        row.querySelector(".qty-input").value = "";
                    } else {
                        row.remove();
                    }
                }
            });
        });
    </script>

    <!-- Pricing (Inventory) -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tableBody = document.querySelector("#pricingTable tbody");
            const addRowBtn = document.getElementById("addRow");
            if (!tableBody || !addRowBtn) return;

            let index = tableBody.querySelectorAll("tr").length;

            function pricingRow(i) {
                return `
            <tr>
                <td class="px-4 py-2 border text-center">
                    <input type="text" name="pricing[${i}][name]" class="form-input w-full border rounded px-2 py-1 text-sm">
                </td>
                <td class="px-4 py-2 border text-right">
                    <input type="text" name="pricing[${i}][price]" class="form-input w-full border rounded px-2 py-1 text-sm">
                </td>
                <td class="px-4 py-2 border text-center">
                    <button type="button" class="remove-row bg-red-500 text-white px-2 py-1 rounded text-xs">Hapus</button>
                </td>
            </tr>`;
            }

            addRowBtn.addEventListener("click", function() {
                tableBody.insertAdjacentHTML("beforeend", pricingRow(index));
                index++;
            });

            tableBody.addEventListener("click", function(e) {
                if (e.target.classList.contains("remove-row")) {
                    const row = e.target.closest("tr");
                    row.remove();
                }
            });
        });
    </script>

    <!-- Pricing (Service) -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tableBody = document.querySelector("#pricingTableService tbody");
            const addRowBtn = document.getElementById("addRowService");
            if (!tableBody || !addRowBtn) return;

            let index = tableBody.querySelectorAll("tr").length;

            function pricingRow(i) {
                return `
            <tr>
                <td class="px-4 py-2 border text-center">
                    <input type="text" name="pricing[${i}][name]" class="form-input w-full border rounded px-2 py-1 text-sm">
                </td>
                <td class="px-4 py-2 border text-right">
                    <input type="text" name="pricing[${i}][price]" class="form-input w-full border rounded px-2 py-1 text-sm">
                </td>
                <td class="px-4 py-2 border text-center">
                    <button type="button" class="remove-row bg-red-500 text-white px-2 py-1 rounded text-xs">Hapus</button>
                </td>
            </tr>`;
            }

            addRowBtn.addEventListener("click", function() {
                tableBody.insertAdjacentHTML("beforeend", pricingRow(index));
                index++;
            });

            tableBody.addEventListener("click", function(e) {
                if (e.target.classList.contains("remove-row")) {
                    const row = e.target.closest("tr");
                    row.remove();
                }
            });
        });
    </script>

    <!-- Tab switching + toggle Inventory/Service -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const typeRadios = document.querySelectorAll('input[name="type"]');
            const inventoryTabs = document.getElementById('inventory-tabs');
            const serviceTabs = document.getElementById('service-tabs');

            function toggleTypeSections(val) {
                if (val === 'inventory') {
                    inventoryTabs.classList.remove('hidden');
                    serviceTabs.classList.add('hidden');
                    // show default inventory tab
                    showTab('#quantities');
                } else {
                    inventoryTabs.classList.add('hidden');
                    serviceTabs.classList.remove('hidden');
                    // show default service tab
                    showTab('#unitsService');
                }
            }

            typeRadios.forEach(r => {
                r.addEventListener('change', function() {
                    toggleTypeSections(this.value);
                });
            });

            // Generic tab show function (works for both groups as we use ids)
            function showTab(id) {
                document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
                const el = document.querySelector(id);
                if (el) el.classList.remove('hidden');
            }

            // Inventory tab links
            document.querySelectorAll('#inventory-tabs .tab-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    showTab(this.getAttribute('href'));
                });
            });
            // Service tab links (gunakan class .tab-link juga supaya seragam)
            document.querySelectorAll('#service-tabs .tab-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    showTab(this.getAttribute('href'));
                });
            });

            // Set initial state sesuai checked radio saat load
            const checked = document.querySelector('input[name="type"]:checked');
            toggleTypeSections(checked ? checked.value : 'inventory');
        });
    </script>

@endsection

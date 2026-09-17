@extends('layouts.app')
@section('content')
    @if (session('error'))
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="max-w-full border rounded text-sm mx-auto py-10 px-6">
        <h2 class="text-2xl font-semibold mb-6">Inventory</h2>

        <form action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data">
            @csrf


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
                <div>
                    <label for="" class="font-semibold text-gray-700 block mb-2">Item</label>
                    <label for="item_number" class="mr-4">Number</label>
                    <input type="text" name="item_number" class="form-input w-1/8 border rounded px-2 py-1 text-sm">
                    <label for="item_description" class="mr-4">Item Name</label>
                    <input type="text" name="item_description" class="form-input w-1/3 border rounded px-2 py-1 text-sm">
                </div>
                <div class="mb-4">
                    <label class="font-semibold text-gray-700 block mb-2">Type</label>
                    <label class="mr-4">
                        <input type="radio" name="type" value="inventory" checked>
                        Inventory
                    </label>
                    <label>
                        <input type="radio" name="type" value="service">
                        Service
                    </label>
                </div>


                {{-- Tab Navigation --}}
                <div id="inventory-tabs" class="type-section">
                    <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                        <li><a href="#quantities" class="tab-link">Quantities</a></li>
                        <li><a href="#units" class="tab-link">Units</a></li>
                        <li><a href="#pricing" class="tab-link">Pricing</a></li>
                        <li><a href="#vendors" class="tab-link">Vendors</a></li>
                        <li><a href="#linked" class="tab-link">Linked Account</a></li>
                        <li><a href="#build" class="tab-link">Build</a></li>
                        {{-- <li><a href="#statistics" class="tab-link">Statistics</a></li> --}}
                        <li><a href="#taxes" class="tab-link">Taxes</a></li>
                        <li><a href="#description" class="tab-link">Description</a></li>
                        <li><a href="#picture" class="tab-link">Picture</a></li>
                    </ul>
                </div>
                <div id="service-tabs" class="type-section hidden">
                    <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                        <li><a href="#unitsService" class="tab-link">Units</a></li>
                        <li><a href="#pricingService" class="tab-link">Pricing</a></li>
                        <li><a href="#vendorsService" class="tab-link">Vendors</a></li>
                        <li><a href="#linkedService" class="tab-link">Linked Account</a></li>
                        {{-- <li><a href="#statisticsService" class="tab-link">Statistics</a></li> --}}
                        <li><a href="#taxesService" class="tab-link">Taxes</a></li>
                        <li><a href="#descriptionService" class="tab-link">Description</a></li>
                        <li><a href="#pictureService" class="tab-link">Picture</a></li>
                    </ul>
                </div>

                {{-- Quantities Tab --}}
                <div id="quantities" class="tab-content">
                    <h3 class="font-semibold text-lg mb-2">Quantities per Location</h3>

                    <div id="quantities-wrapper">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border rounded p-4 mb-4">
                            <div>
                                <label class="block">Location</label>
                                <select name="quantities[0][location_id]" class="w-full border rounded-lg px-4 py-2">
                                    <option value="">-- Location --</option>
                                    @foreach ($lokasiInventory as $g)
                                        <option value="{{ $g->id }}">{{ $g->kode_lokasi }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block">On Hand Quantity</label>
                                <input type="number" name="quantities[0][on_hand_qty]" value="0" readonly
                                    class="form-input w-full border rounded px-2 py-1 text-sm bg-gray-200" />
                            </div>

                            <div>
                                <label class="block">On Hand Value</label>
                                <input type="text" id="on_hand_display"
                                    class="form-input w-full border rounded px-2 py-1 text-sm bg-gray-200" value="0.00"
                                    readonly />
                                <input type="hidden" id="on_hand_value" name="quantities[0][on_hand_value]" />
                            </div>
                        </div>
                    </div>

                    {{-- tombol tambah baris lokasi baru --}}
                    <button type="button" onclick="addLocationRow()" class="mt-2 bg-blue-500 text-white px-3 py-1 rounded">
                        + Add Location
                    </button>
                </div>


                {{-- Units Tab --}}
                <div id="units" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Units</h3>

                    {{-- Stocking --}}
                    <div class="mb-4">
                        <label class="block">Stocking Unit of Measure</label>
                        <input type="text" id="stocking_unit" name="stocking_unit"
                            class="form-input w-full border rounded px-2 py-1 text-sm" />
                    </div>

                    {{-- Selling --}}
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="selling_same" name="selling_same_as_stocking" value="1"
                                checked>
                            <span class="ml-2">Selling unit same as stocking unit</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block">Selling Unit (if different)</label>
                            <input type="text" id="selling_unit" name="selling_unit"
                                class="form-input w-full border rounded px-2 py-1 text-sm" readonly />
                        </div>
                        <div>
                            <label class="block">Selling Relationship</label>
                            <input type="number" name="selling_relationship"
                                class="form-input w-full border rounded px-2 py-1 text-sm" />
                        </div>
                    </div>

                    {{-- Buying --}}
                    <div class="mb-4 mt-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="buying_same" name="buying_same_as_stocking" value="1"
                                checked>
                            <span class="ml-2">Buying unit same as stocking unit</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block">Buying Unit (if different)</label>
                            <input type="text" id="buying_unit" name="buying_unit"
                                class="form-input w-full border rounded px-2 py-1 text-sm" readonly />
                        </div>
                        <div>
                            <label class="block">Buying Relationship</label>
                            <input type="number" name="buying_relationship"
                                class="form-input w-full border rounded px-2 py-1 text-sm" />
                        </div>
                    </div>
                </div>


                {{-- Pricing Tab --}}
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
                            @foreach ($priceListInventory as $index => $item)
                                <tr>
                                    <td class="px-4 py-2 border text-center">
                                        <input type="text" name="pricing[{{ $index }}][name]"
                                            class="form-input w-full border rounded px-2 py-1 text-sm"
                                            value="{{ $item->description }}">
                                    </td>
                                    <td class="px-4 py-2 border text-right">
                                        <input type="text" name="pricing[{{ $index }}][price]"
                                            class="form-input w-full border rounded px-2 py-1 text-sm">
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
                        <button type="button" id="addRow" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">+
                            Tambah Data</button>
                    </div>
                </div>

                {{-- Vendors Tab --}}
                <div id="vendors" class="tab-content hidden">
                    <div>
                        <label class="block">Vendor</label>
                        <select name="vendor_id_inventory" id="vendor_inventory_id"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Vendor --</option>
                            @foreach ($vendors as $g)
                                <option value="{{ $g->id }}">
                                    {{ $g->nama_vendors }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="vendorsService" class="tab-content hidden">
                    <div>
                        <label class="block">Vendor</label>
                        <select name="vendor_id_service" id="vendor_service_id"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Vendor --</option>
                            @foreach ($vendors as $g)
                                <option value="{{ $g->id }}">
                                    {{ $g->nama_vendors }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Description Tab --}}
                <div id="description" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Description</h3>
                    <textarea name="long_description" class="form-textarea w-full border rounded px-2 py-1 text-sm" rows="5"></textarea>
                </div>

                {{-- Picture Tab --}}
                <div id="picture" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Picture</h3>
                    <div class="mb-4">
                        <label class="block">Main Picture</label>
                        <input type="file" name="picture"
                            class="form-input w-full border rounded px-2 py-1 text-sm" />
                    </div>
                    <div class="mb-4">
                        <label class="block">Thumbnail</label>
                        <input type="file" name="thumbnail"
                            class="form-input w-full border rounded px-2 py-1 text-sm" />
                    </div>
                </div>

                {{-- Tab Linked (Chart of Accounts) --}}
                <div id="linked" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Account Linkings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Asset Account --}}
                        <div>
                            <label for="asset_account_id" class="block text-sm font-medium text-gray-700">Asset
                                Account</label>
                            <select name="asset_account_id" class="form-select w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Pilih Akun Aset --</option>
                                @foreach ($accounts->where('tipe_akun', 'Aset') as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Revenue Account --}}
                        <div>
                            <label for="inventory_revenue_account_id"
                                class="block text-sm font-medium text-gray-700">Revenue
                                Account</label>
                            <select name="inventory_revenue_account_id"
                                class="form-select w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Pilih Akun Pendapatan --</option>
                                @foreach ($accounts->where('tipe_akun', 'Pendapatan') as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- COGS Account --}}
                        <div>
                            <label for="cogs_account_id" class="block text-sm font-medium text-gray-700">COGS
                                Account</label>
                            <select name="cogs_account_id" class="form-select w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Pilih Akun Beban Pokok Penjualan --</option>
                                @foreach ($accounts->where('tipe_akun', 'Beban') as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Variance Account --}}
                        <div>
                            <label for="variance_account_id" class="block text-sm font-medium text-gray-700">Variance
                                Account</label>
                            <select name="variance_account_id"
                                class="form-select w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Pilih Akun Selisih Harga --</option>
                                @foreach ($accounts->where('tipe_akun', 'Beban') as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

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
                            {{-- Row awal (index 0) --}}
                            <tr data-index="0">
                                <td>
                                    <select name="build[0][item_id]"
                                        class="item-select form-select w-full border rounded px-2 py-1 text-sm">
                                        <option value="">-- Pilih Item --</option>
                                        @foreach ($items as $item)
                                            <option value="{{ $item->id }}" data-unit="{{ $item->unit }}"
                                                data-description="{{ $item->item_description }}">
                                                {{ $item->item_description }}
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
                        </tbody>
                    </table>

                    <div class="mt-2">
                        <button type="button" id="addRowBuild" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                            + Tambah Data
                        </button>
                    </div>
                </div>

                {{-- Taxes Tab --}}
                <div id="taxes" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Tax Configuration</h3>

                    {{-- Notifikasi error khusus taxes (opsional) --}}
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
                            @foreach ($taxes as $tax)
                                <tr class="border-b">
                                    <td class="px-4 py-2">
                                        <label class="flex items-center space-x-2">
                                            {{-- Pilih pajaknya --}}
                                            <input type="checkbox" name="taxes[]" value="{{ $tax->id }}"
                                                class="form-checkbox"
                                                {{ in_array($tax->id, old('taxes', [])) ? 'checked' : '' }}>
                                            <span>{{ $tax->name }} ({{ $tax->rate }}%)</span>
                                        </label>
                                    </td>
                                    <td class="px-4 py-2">
                                        <label class="flex items-center space-x-2">
                                            {{-- Tandai exempt utk pajak tersebut --}}
                                            <input type="checkbox" name="tax_exempt[]" value="{{ $tax->id }}"
                                                class="form-checkbox"
                                                {{ in_array($tax->id, old('tax_exempt', [])) ? 'checked' : '' }}>
                                            <span>Exempt {{ $tax->name }}</span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- service area --}}

                {{-- Units Tab --}}
                <div id="unitsService" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Units</h3>
                    <div class="mb-4">
                        <label class="block">Unit of Measure</label>
                        <input type="text" name="unit_of_measure"
                            class="form-input w-full border rounded px-2 py-1 text-sm" />
                    </div>
                </div>

                {{-- Pricing Tab --}}
                <div id="pricingService" class="tab-content hidden">
                    <table class="table-auto w-full border-collapse border border-gray-200 text-sm" id="pricingTable">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th>Price List</th>
                                <th>Price Per Selling Unit</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($priceListInventory as $index => $item)
                                <tr>
                                    <td class="px-4 py-2 border text-center">
                                        <input type="text" name="pricing[{{ $index }}][name]"
                                            class="form-input w-full border rounded px-2 py-1 text-sm"
                                            value="{{ $item->description }}">
                                    </td>
                                    <td class="px-4 py-2 border text-right">
                                        <input type="text" name="pricing[{{ $index }}][price]"
                                            class="form-input w-full border rounded px-2 py-1 text-sm">
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
                        <button type="button" id="addRow" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">+
                            Tambah Data</button>
                    </div>
                </div>

                {{-- Tab Linked (Chart of Accounts) --}}
                <div id="linkedService" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Account Linkings</h3>


                    {{-- Revenue Account --}}
                    <div>
                        <label for="service_revenue_account_id" class="block text-sm font-medium text-gray-700">Revenue
                        </label>
                        <select name="service_revenue_account_id"
                            class="form-select w-1/2 border rounded px-2 py-1 text-sm">
                            <option value="">-- Pilih Account --</option>
                            @foreach ($accounts->where('tipe_akun', 'Pendapatan') as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Account Receivable Account --}}
                    <div>
                        <label for="account_receivable_id" class="block text-sm font-medium text-gray-700">Account
                            Receivable
                        </label>
                        <select name="account_receivable_id" class="form-select w-1/2 border rounded px-2 py-1 text-sm">
                            <option value="">-- Pilih Account --</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- expense Account --}}
                    <div>
                        <label for="expense_account_id" class="block text-sm font-medium text-gray-700">Expense
                        </label>
                        <select name="expense_account_id" class="form-select w-1/2 border rounded px-2 py-1 text-sm">
                            <option value="">-- Pilih Account --</option>
                            @foreach ($accounts->where('tipe_akun', 'Beban') as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Taxes Tab --}}
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
                            @foreach ($taxes as $tax)
                                <tr class="border-b">
                                    <td class="px-4 py-2">
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" name="taxes_service[]" value="{{ $tax->id }}"
                                                class="form-checkbox">
                                            <span>{{ $tax->name }} ({{ $tax->rate }}%)</span>
                                        </label>
                                    </td>
                                    <td class="px-4 py-2">
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" name="tax_exempt_service[]"
                                                value="{{ $tax->id }}" class="form-checkbox">
                                            <span>Exempt {{ $tax->name }}</span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Description Tab --}}
                <div id="descriptionService" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Description</h3>
                    <textarea name="long_description" class="form-textarea w-full border rounded px-2 py-1 text-sm" rows="5"></textarea>
                </div>

                {{-- Picture Tab --}}
                <div id="pictureService" class="tab-content hidden">
                    <h3 class="font-semibold text-lg mb-2">Picture</h3>
                    <div class="mb-4">
                        <label class="block">Main Picture</label>
                        <input type="file" name="picture"
                            class="form-input w-full border rounded px-2 py-1 text-sm" />
                    </div>
                    <div class="mb-4">
                        <label class="block">Thumbnail</label>
                        <input type="file" name="thumbnail"
                            class="form-input w-full border rounded px-2 py-1 text-sm" />
                    </div>
                </div>

                {{-- end service --}}


                {{-- Tombol Submit --}}
                <div class="mt-6 justify-end flex space-x-4">

                    <a href="{{ route('inventory.index') }}"
                        class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
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
    <script>
        $(document).ready(function() {
            $('#vendor_inventory_id,#vendor_service_id').select2({
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
        document.getElementById('on_hand_display').addEventListener('input', function(e) {
            let raw = e.target.value.replace(/,/g, '');
            let num = parseFloat(raw);
            if (!isNaN(num)) {
                document.getElementById('on_hand_value').value = num.toFixed(2);
                e.target.value = num.toLocaleString('en-US', {
                    minimumFractionDigits: 2
                });
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Hitung index awal dari jumlah baris existing
            let index = document.querySelectorAll("#buildTable tbody tr").length;
            const tableBody = document.querySelector("#buildTable tbody");
            const addRowBtn = document.getElementById("addRowBuild");

            // Template baris baru (pakai fungsi agar gampang inject index dinamis)
            function rowTemplate(i) {
                return `
            <tr data-index="${i}">
                <td>
                    <select name="build[${i}][item_id]" 
                            class="item-select form-select w-full border rounded px-2 py-1 text-sm">
                        <option value="">-- Pilih Item --</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}"
                                    data-unit="{{ $item->unit }}"
                                    data-description="{{ $item->item_description }}">
                                {{ $item->item_description }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" 
                           name="build[${i}][unit]"
                           class="unit-input form-input w-full border rounded px-2 py-1 text-sm" 
                           >
                </td>
                <td>
                    <input type="text" 
                           name="build[${i}][description]"
                           class="desc-input form-input w-full border rounded px-2 py-1 text-sm" 
                           readonly>
                </td>
                <td>
                    <input type="number" 
                           name="build[${i}][quantity]"
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
        `;
            }

            // Tambah baris
            addRowBtn.addEventListener("click", function() {
                tableBody.insertAdjacentHTML("beforeend", rowTemplate(index));
                index++;
            });

            // Event delegation: autofill Unit & Description saat pilih item
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

            // Hapus baris
            tableBody.addEventListener("click", function(e) {
                if (e.target.classList.contains("remove-row-build")) {
                    const row = e.target.closest("tr");
                    // Jika hanya 1 baris, kosongkan saja (opsional), atau langsung hapus
                    if (tableBody.querySelectorAll("tr").length === 1) {
                        // kosongkan input-input jika ingin minimal satu baris tetap ada
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
    {{-- Script Tab Navigation --}}

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let index = {{ count($priceListInventory) }};
            const tableBody = document.querySelector("#pricingTable tbody");
            const addRowBtn = document.getElementById("addRow");

            // Tambah baris
            addRowBtn.addEventListener("click", function() {
                let newRow = document.createElement("tr");
                newRow.innerHTML = `
                <td class="px-4 py-2 border text-center">
                    <input type="text" name="pricing[${index}][name]" 
                        class="form-input w-full border rounded px-2 py-1 text-sm">
                </td>
                <td class="px-4 py-2 border text-right">
                    <input type="text" name="pricing[${index}][price]" 
                        class="form-input w-full border rounded px-2 py-1 text-sm">
                </td>
                <td class="px-4 py-2 border text-center">
                    <button type="button" class="remove-row bg-red-500 text-white px-2 py-1 rounded text-xs">Hapus</button>
                </td>
            `;
                tableBody.appendChild(newRow);
                index++;
            });

            // Hapus baris
            tableBody.addEventListener("click", function(e) {
                if (e.target.classList.contains("remove-row")) {
                    e.target.closest("tr").remove();
                }
            });
        });
    </script>
    <script>
        const stocking = document.getElementById('stocking_unit');
        const sellingSame = document.getElementById('selling_same');
        const sellingUnit = document.getElementById('selling_unit');
        const buyingSame = document.getElementById('buying_same');
        const buyingUnit = document.getElementById('buying_unit');

        function toggleSelling() {
            if (sellingSame.checked) {
                sellingUnit.value = stocking.value;
                sellingUnit.readOnly = true;
            } else {
                sellingUnit.readOnly = false;
                sellingUnit.value = '';
            }
        }

        function toggleBuying() {
            if (buyingSame.checked) {
                buyingUnit.value = stocking.value;
                buyingUnit.readOnly = true;
            } else {
                buyingUnit.readOnly = false;
                buyingUnit.value = '';
            }
        }

        // event listener
        sellingSame.addEventListener('change', toggleSelling);
        buyingSame.addEventListener('change', toggleBuying);
        stocking.addEventListener('input', () => {
            if (sellingSame.checked) sellingUnit.value = stocking.value;
            if (buyingSame.checked) buyingUnit.value = stocking.value;
        });

        // init on page load
        toggleSelling();
        toggleBuying();
    </script>
    <script>
        const typeRadios = document.querySelectorAll('input[name="type"]');
        const inventoryTabs = document.getElementById('inventory-tabs');
        const serviceTabs = document.getElementById('service-tabs');

        typeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'inventory') {
                    inventoryTabs.classList.remove('hidden');
                    serviceTabs.classList.add('hidden');
                } else {
                    inventoryTabs.classList.add('hidden');
                    serviceTabs.classList.remove('hidden');
                }
            });
        });

        // Tab switching untuk Inventory
        const invLinks = document.querySelectorAll('.tab-link');
        const invTabs = document.querySelectorAll('.tab-content');
        invLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                invTabs.forEach(tab => tab.classList.add('hidden'));
                const id = this.getAttribute('href');
                document.querySelector(id).classList.remove('hidden');
            });
        });

        // Tab switching untuk Service
        const srvLinks = document.querySelectorAll('.tab-link-service');
        const srvTabs = document.querySelectorAll('.tab-content-service');
        srvLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                srvTabs.forEach(tab => tab.classList.add('hidden'));
                const id = this.getAttribute('href');
                document.querySelector(id).classList.remove('hidden');
            });
        });
    </script>
@endsection

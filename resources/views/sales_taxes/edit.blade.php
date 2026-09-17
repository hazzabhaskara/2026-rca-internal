@extends('layouts.app')

@section('content')

    <div class="max-w-full mx-auto bg-white p-8 mt-6">
        @php
            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
        @endphp
        <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
            <h2 class="text-lg font-bold mb-4">Taxes Edit</h2>

            <form method="POST"
                action="{{ isset($sales_taxes) ? route('sales_taxes.update', $sales_taxes->id) : route('sales_taxes.store') }}">
                @csrf
                @if (isset($sales_taxes))
                    @method('PUT')
                @endif

                @if ($errors->any())
                    <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-4 gap-4 text-sm">

                    <div class="mb-5">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Jenis Pajak</label>
                        <input type="text" id="name" name="name"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            value="{{ old('name', $sales_taxes->name) }}">
                    </div>
                    <div class="mb-5">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Tax Code</label>
                        <input type="text" id="name" name="tax_code"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            value="{{ old('tax_code', $sales_taxes->tax_code) }}">
                    </div>

                    <div class="mb-5">
                        <label for="rate" class="block text-sm font-medium text-gray-700 mb-1">Rate(%)</label>
                        <input type="text" id="rate" name="rate"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh : 10" value="{{ old('rate', $sales_taxes->rate ?? '') }}">
                    </div>

                    <div class="mb-5">
                        <label for="purchase_account_id" class="block text-sm font-medium text-gray-700 mb-1">Purchases
                            Transaction</label>
                        <select name="purchase_account_id" id="purchase_account_id"
                            class="w-full account-select border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Account --</option>
                            @foreach ($account as $g)
                                <option value="{{ $g->id }}"
                                    {{ isset($sales_taxes) && $sales_taxes->purchase_account_id == $g->id ? 'selected' : '' }}>
                                    {{ $g->kode_akun }}-{{ $g->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-5">
                        <label for="sales_account_id" class="block text-sm font-medium text-gray-700 mb-1">Sales
                            Transaction</label>
                        <select name="sales_account_id" id="sales_account_id"
                            class="w-full account-select border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Account --</option>
                            @foreach ($account as $g)
                                <option value="{{ $g->id }}"
                                    {{ isset($sales_taxes) && $sales_taxes->sales_account_id == $g->id ? 'selected' : '' }}>
                                    {{ $g->kode_akun }}-{{ $g->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                            Kriteria
                        </label>
                        <select name="type" id="type" class="w-full border rounded px-3 py-2">
                            <option value="input_tax"
                                {{ old('type', $sales_taxes->type ?? '') == 'input_tax' ? 'selected' : '' }}>
                                Input Tax (Ditambahkan, contoh: PPN)
                            </option>
                            <option value="withholding_tax"
                                {{ old('type', $sales_taxes->type ?? '') == 'withholding_tax' ? 'selected' : '' }}>
                                Withholding Tax (Potongan, contoh: PPh)
                            </option>
                        </select>

                    </div>
                    <div>
                        <label for="active" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select name="kategori" id="kategori"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Exclude"
                                {{ old('kategori', $sales_taxes->kategori ?? 1) == 'Exclude' ? 'selected' : '' }}>
                                Exclude
                            </option>
                            <option value="Include"
                                {{ old('kategori', $sales_taxes->kategori ?? 1) == 'Include' ? 'selected' : '' }}>
                                Include
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="active" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="active" id="active"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="1" {{ old('active', $sales_taxes->active ?? 1) == 1 ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="0" {{ old('active', $sales_taxes->active ?? 1) == 0 ? 'selected' : '' }}>
                                Non-Active
                            </option>
                        </select>
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="flex justify-end">
                    <a href="{{ route('sales_taxes.index') }}"
                        class="mr-3 inline-block px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-block px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                        {{ isset($sales_taxes) ? 'Process' : ' Process' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

{{-- Select2 JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.account-select').select2({
            placeholder: "Cari akun...",
            allowClear: true
        });
    });
</script>

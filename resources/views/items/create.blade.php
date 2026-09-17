@extends('layouts.app')
@section('content')
    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="item_number" class="block font-medium text-sm text-gray-700 mb-1">Item
                                Number</label>
                            <input type="text" name="item_number" id="item_number" required
                                value="{{ old('item_number') }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="item_name" class="block font-medium text-sm text-gray-700 mb-1">Item
                                Name</label>
                            <input type="text" name="item_name" id="item_name" required value="{{ old('item_name') }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="unit" class="block font-medium text-sm text-gray-700 mb-1">Unit</label>
                            <input type="text" name="unit" id="unit" required value="{{ old('unit') }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="base_price" class="block font-medium text-sm text-gray-700 mb-1">Base
                                Price</label>
                            <input type="number" step="0.01" name="base_price" id="base_price" required
                                value="{{ old('base_price') }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="tax_rate" class="block font-medium text-sm text-gray-700 mb-1">Tax Rate
                                (%)</label>
                            <input type="number" step="0.01" name="tax_rate" id="tax_rate"
                                value="{{ old('tax_rate') }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="purchase_price" class="block font-medium text-sm text-gray-700 mb-1">Purchase
                                Price</label>
                            <input type="number" step="0.01" name="purchase_price" id="purchase_price"
                                value="{{ old('purchase_price') }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="stock_quantity" class="block font-medium text-sm text-gray-700 mb-1">Stock
                                Quantity</label>
                            <input type="number" step="0.01" name="stock_quantity" id="stock_quantity"
                                value="{{ old('stock_quantity') }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="brand" class="block font-medium text-sm text-gray-700 mb-1">Brand</label>
                            <input type="text" name="brand" id="brand" value="{{ old('brand') }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="account_id" class="block font-medium text-sm text-gray-700 mb-1">Account</label>
                            <select name="account_id" id="account_id" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Akun --</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}"
                                        {{ old('account_id') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->kode_akun ?? '' }} - {{ $acc->nama_akun ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="category_id" class="block font-medium text-sm text-gray-700 mb-1">Category</label>
                            <select name="category_id" id="category_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nama_kategori ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="item_description"
                                class="block font-medium text-sm text-gray-700 mb-1">Description</label>
                            <textarea name="item_description" id="item_description" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('item_description') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="is_active" class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" class="mr-2" value="1"
                                    {{ old('is_active', true) ? 'checked' : '' }}>
                                Aktif
                            </label>
                        </div>

                        <div class="mb-4">
                            <label class="font-weight-bold">Logo</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" name="image">

                            <!-- error message untuk title -->
                            @error('image')
                                <div class="alert alert-danger mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('items.index') }}"
                            class="px-4 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">Batal</a>
                        <button type="submit"
                            class="ml-3 px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

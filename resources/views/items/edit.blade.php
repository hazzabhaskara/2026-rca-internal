@extends('layouts.app')
@section('content')
    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form method="POST" action="{{ route('items.update', $item->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="item_number" class="block font-medium text-sm text-gray-700 mb-1">Item
                                Number</label>
                            <input type="text" name="item_number" id="item_number" required
                                value="{{ old('item_number', $item->item_number) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="item_name" class="block font-medium text-sm text-gray-700 mb-1">Item
                                Name</label>
                            <input type="text" name="item_name" id="item_name" required
                                value="{{ old('item_name', $item->item_name) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="unit" class="block font-medium text-sm text-gray-700 mb-1">Unit</label>
                            <input type="text" name="unit" id="unit" required
                                value="{{ old('unit', $item->unit) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="base_price" class="block font-medium text-sm text-gray-700 mb-1">Base
                                Price</label>
                            <input type="number" step="0.01" name="base_price" id="base_price" required
                                value="{{ old('base_price', $item->base_price) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="tax_rate" class="block font-medium text-sm text-gray-700 mb-1">Tax Rate
                                (%)</label>
                            <input type="number" step="0.01" name="tax_rate" id="tax_rate"
                                value="{{ old('tax_rate', $item->tax_rate) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="purchase_price" class="block font-medium text-sm text-gray-700 mb-1">Purchase
                                Price</label>
                            <input type="number" step="0.01" name="purchase_price" id="purchase_price"
                                value="{{ old('purchase_price', $item->purchase_price) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="stock_quantity" class="block font-medium text-sm text-gray-700 mb-1">Stock
                                Quantity</label>
                            <input type="number" step="0.01" name="stock_quantity" id="stock_quantity"
                                value="{{ old('stock_quantity', $item->stock_quantity) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="brand" class="block font-medium text-sm text-gray-700 mb-1">Brand</label>
                            <input type="text" name="brand" id="brand" value="{{ old('brand', $item->brand) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="account_id" class="block font-medium text-sm text-gray-700 mb-1">Account</label>
                            <select name="account_id" id="account_id" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Akun --</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}"
                                        {{ old('account_id', $item->account_id) == $acc->id ? 'selected' : '' }}>
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
                                        {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nama_kategori ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="item_description"
                                class="block font-medium text-sm text-gray-700 mb-1">Description</label>
                            <textarea name="item_description" id="item_description" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('item_description', $item->item_description) }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="is_active" class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" class="mr-2" value="1"
                                    {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                                Aktif
                            </label>
                        </div>

                        <div class="mb-4 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gambar (opsional)</label>
                            <input type="file" name="image"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0 file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            @if ($item->image)
                                <img src="{{ asset('storage/items/' . $item->image) }}"
                                    class="mt-2 w-24 h-24 object-cover rounded border" />
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('items.index') }}"
                            class="px-4 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">Batal</a>
                        <button type="submit"
                            class="ml-3 px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

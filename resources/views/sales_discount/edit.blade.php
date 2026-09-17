@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-8 shadow-xl rounded-xl">
                <form method="POST" action="{{ route('sales_discount.update', $salesDiscount->id) }}">
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="font-medium text-gray-700 block mb-1">Nama Discount</label>
                            <input type="text" name="nama_diskon"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="{{ $salesDiscount->nama_diskon }}" required>
                        </div>
                        <div>
                            <label class="font-medium text-gray-700 block mb-1">Jenis Discount</label>
                            <input type="text" name="jenis_diskon"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="{{ $salesDiscount->jenis_diskon }}" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="font-medium text-gray-700 block mb-1">Deskripsi</label>
                            <textarea name="deskripsi" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $salesDiscount->deskripsi }}</textarea>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="aktif" id="aktif" class="form-checkbox text-blue-600 rounded"
                                {{ $salesDiscount->aktif ? 'checked' : '' }}>
                            <label for="aktif" class="ml-2 text-sm">Aktif</label>
                        </div>
                    </div>

                    {{-- TABEL ITEM --}}
                    <div class="mt-8">
                        <h3 class="font-semibold text-lg mb-2">🛒 Order Items</h3>
                        <div class="overflow-auto">
                            <table class="w-full border text-sm text-left shadow-md">
                                <thead class="bg-blue-100 text-gray-700">
                                    <tr>
                                        @if ($salesDiscount->jenis_diskon === 'early_payment')
                                            <th class="border px-3 py-2">Hari Ke</th>
                                        @endif
                                        <th class="border px-3 py-2">Tipe Nilai</th>
                                        <th class="border px-3 py-2">Nilai Discount</th>
                                        @if ($salesDiscount->jenis_diskon === 'berlapis' || $salesDiscount->jenis_diskon === 'early_payment')
                                            <th class="border px-3 py-2">Urutan</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="item-table-body">
                                    @foreach ($salesDiscount->details as $i => $item)
                                        <tr>
                                            {{-- Hanya muncul untuk early_payment --}}
                                            @if ($salesDiscount->jenis_diskon === 'early_payment')
                                                <td class="border px-3 py-2">
                                                    <input type="number" name="details[{{ $i }}][hari_ke]"
                                                        value="{{ $item->hari_ke }}"
                                                        class="form-input w-full border rounded px-2 py-1 text-sm">
                                                </td>
                                            @endif

                                            <td class="border px-3 py-2">
                                                <select name="details[{{ $i }}][tipe_nilai]"
                                                    class="form-select w-full border rounded px-2 py-1 text-sm">
                                                    <option value="persen"
                                                        {{ $item->tipe_nilai === 'persen' ? 'selected' : '' }}>Persen
                                                    </option>
                                                    <option value="nominal"
                                                        {{ $item->tipe_nilai === 'nominal' ? 'selected' : '' }}>Nominal
                                                    </option>
                                                </select>
                                            </td>

                                            <td class="border px-3 py-2">
                                                <input type="number" step="0.01"
                                                    name="details[{{ $i }}][nilai_diskon]"
                                                    value="{{ $item->nilai_diskon }}"
                                                    class="form-input w-full border rounded px-2 py-1 text-sm">
                                            </td>

                                            {{-- Untuk berlapis dan early_payment ada urutan --}}
                                            @if ($salesDiscount->jenis_diskon === 'berlapis' || $salesDiscount->jenis_diskon === 'early_payment')
                                                <td class="border px-3 py-2">
                                                    <input type="number" name="details[{{ $i }}][urutan]"
                                                        value="{{ $item->urutan }}"
                                                        class="form-input w-full border rounded px-2 py-1 text-sm">
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>


                            </table>

                        </div>
                    </div>
                    {{-- Tombol --}}
                    <div class="mt-8 flex justify-start space-x-4">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                            💾 Update
                        </button>
                        <a href="{{ route('sales_discount.index') }}"
                            class="px-6 py-2 bg-gray-300 rounded-lg shadow hover:bg-gray-400 transition">
                            ❌ Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection

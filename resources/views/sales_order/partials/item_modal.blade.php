{{-- sales_order/partials/item_modal.blade.php --}}
<div id="item-modal" class="fixed inset-0 z-50 bg-black bg-opacity-50 hidden">
    <div class="bg-white w-full max-w-4xl mx-auto mt-20 p-6 rounded shadow-md relative">
        <h3 class="text-lg font-semibold mb-4">Pilih Item</h3>

        <input type="text" id="item-search" placeholder="Cari item..." class="w-full border px-3 py-2 rounded mb-3">

        <table class="w-full text-sm" id="item-table">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="px-2 py-1">Item Number</th>
                    <th class="px-2 py-1">Name</th>
                    <th class="px-2 py-1">Unit</th>
                    <th class="px-2 py-1">Base Price</th>
                    <th class="px-2 py-1">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr class="hover:bg-gray-50 border-b">
                        <td class="px-2 py-1">{{ $item->item_number }}</td>
                        <td class="px-2 py-1">{{ $item->item_name }}</td>
                        <td class="px-2 py-1">{{ $item->unit }}</td>
                        <td class="px-2 py-1">Rp {{ number_format($item->base_price, 0, ',', '.') }}</td>
                        <td class="px-2 py-1">
                            <button type="button" class="select-item bg-green-500 text-white px-2 py-1 rounded text-xs"
                                data-id="{{ $item->id }}"
                                data-name="{{ $item->item_number }} - {{ $item->item_name }}"
                                data-unit="{{ $item->unit }}" data-description="{{ $item->item_description }}"
                                data-price="{{ $item->base_price }}">
                                Pilih
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" id="close-item-modal"
            class="absolute top-2 right-2 text-gray-600 hover:text-black">✕</button>
    </div>
</div>

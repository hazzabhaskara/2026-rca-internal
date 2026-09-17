<tr class="item-row border-b hover:bg-gray-50" data-row="{{ $index }}">

    <td>
        <div class="flex items-center space-x-2">
            <input type="hidden" name="items[{{ $index }}][item_id]" class="item-id" />
            <input type="text" class="item-name bg-gray-100 text-sm w-full rounded border px-2 py-1" readonly
                placeholder="Pilih item..." />
            <button type="button" class="open-item-modal bg-blue-500 text-white px-2 py-1 rounded text-xs"
                data-row="{{ $index }}">
                Cari Item
            </button>

        </div>
    </td>

    <td class="px-2 py-1">
        <input type="number" name="items[{{ $index }}][quantity_ordered]" step="1" min="0"
            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
            value="{{ old("items.$index.quantity_ordered", 0) }}" required>
    </td>
    <td class="px-2 py-1">
        <input type="number" name="items[{{ $index }}][back_order]" step="1" min="0"
            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
            value="{{ old("items.$index.back_order", 0) }}">
    </td>
    <td class="px-2 py-1">
        <input type="text" name="items[{{ $index }}][unit]"
            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
            value="{{ old("items.$index.unit") }}">
    </td>
    <td class="px-2 py-1">
        <input type="text" name="items[{{ $index }}][description]"
            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
            value="{{ old("items.$index.description") }}">
    </td>
    <td class="px-2 py-1">
        <input type="number" name="items[{{ $index }}][base_price]" step="0.01" min="0"
            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
            value="{{ old("items.$index.base_price", 0) }}">
    </td>
    <td class="px-2 py-1">
        <input type="number" name="items[{{ $index }}][discount]" step="0.01" min="0"
            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
            value="{{ old("items.$index.discount", 0) }}">
    </td>
    <td class="px-2 py-1">
        <input type="number" name="items[{{ $index }}][price]" step="0.01" min="0"
            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
            value="{{ old("items.$index.price", 0) }}">
    </td>
    <td class="px-2 py-1">
        <input type="number" name="items[{{ $index }}][amount]" step="0.01" min="0"
            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
            value="{{ old("items.$index.amount", 0) }}">
    </td>
    <td class="px-2 py-1">
        <input type="number" name="items[{{ $index }}][tax]" step="0.01" min="0"
            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
            value="{{ old("items.$index.tax", 0) }}">
    </td>
    <td class="px-2 py-1">
        <select name="items[{{ $index }}][account_id]"
            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
            required>
            <option value="">-- Pilih Akun --</option>
            @foreach ($accounts as $acc)
                <option value="{{ $acc->id }}"
                    {{ old("items.$index.account_id") == $acc->id ? 'selected' : '' }}>
                    {{ $acc->kode_akun }} - {{ $acc->nama_akun }}
                </option>
            @endforeach
        </select>
    </td>
    <td class="px-2 py-1 text-center">
        <button type="button"
            class="remove-item-row bg-red-500 hover:bg-red-600 text-white text-xs px-2 py-1 rounded shadow-sm transition duration-150">
            ✕
        </button>
    </td>
</tr>

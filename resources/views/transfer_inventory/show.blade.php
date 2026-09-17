@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Tansfer Inventory Detail</h2>

                <!-- Header -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p><span class="font-semibold">Date:</span> {{ $data->date }}</p>
                        <p><span class="font-semibold">From Location:</span>
                            {{ $data->fromInventory->kode_lokasi ?? '-' }}
                        </p>
                        <p><span class="font-semibold">To Location:</span>
                            {{ $data->toInventory->kode_lokasi ?? '-' }}
                        </p>
                        <p><span class="font-semibold">Source:</span> {{ $data->source }}</p>
                    </div>
                    <div>
                        <p><span class="font-semibold">Notes:</span>
                            {{ $data->notes ?? '-' }}
                        </p>
                    </div>
                </div>

                <!-- Details -->
                <h3 class="text-lg font-semibold mb-2">Item Components</h3>
                <table class="w-full border border-gray-300 text-sm mb-6">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-2 py-1 text-left">Item</th>
                            <th class="border px-2 py-1 text-center">Unit</th>
                            <th class="border px-2 py-1 text-right">Qty</th>
                            <th class="border px-2 py-1 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data->details as $d)
                            <tr>
                                <td class="border px-2 py-1">
                                    {{ $d->component->item_description ?? '-' }}
                                </td>
                                <td class="border px-2 py-1 text-center">{{ $d->unit }}</td>
                                <td class="border px-2 py-1 text-right">{{ $d->qty }}</td>
                                <td class="border px-2 py-1 text-right">
                                    {{ number_format($d->amount, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-gray-500 py-2">Tidak ada komponen</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-8 flex flex-col sm:flex-row justify-between items-center gap-3">
                    <a href="{{ route('transfer_inventory.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>

                    <div class="flex gap-3">
                        <a href="{{ route('transfer_inventory.edit', $data->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 transition">
                            <i class="fas fa-edit mr-2"></i> Edit
                        </a>

                        {{-- <form id="delete-form-{{ $journal->id }}" action="{{ route('journal_entry.destroy', $journal->id) }}"
                    method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form> --}}

                        {{-- <button type="button" onclick="confirmDelete({{ $journal->id }})"
                    class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition-colors"
                    title="Delete">
                    <i class="fas fa-trash"></i>
                </button> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

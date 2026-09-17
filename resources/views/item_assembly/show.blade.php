@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Assembly Detail</h2>

                <!-- Header -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p><span class="font-semibold">Date:</span> {{ $assembly->date }}</p>
                        <p><span class="font-semibold">Item:</span>
                            {{ $assembly->parentItem->item_description ?? '-' }}
                        </p>
                        <p><span class="font-semibold">Qty Built:</span> {{ $assembly->qty_built }}</p>
                    </div>
                    <div>
                        <p><span class="font-semibold">Total Cost:</span>
                            {{ number_format($assembly->total_cost, 2, ',', '.') }}
                        </p>
                        <p><span class="font-semibold">Notes:</span>
                            {{ $assembly->notes ?? '-' }}
                        </p>
                    </div>
                </div>

                <!-- Details -->
                <h3 class="text-lg font-semibold mb-2">Assembly Components</h3>
                <table class="w-full border border-gray-300 text-sm mb-6">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-2 py-1 text-left">Component</th>
                            <th class="border px-2 py-1 text-center">Unit</th>
                            <th class="border px-2 py-1 text-right">Qty Used</th>
                            <th class="border px-2 py-1 text-right">Unit Cost</th>
                            <th class="border px-2 py-1 text-right">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total = 0;
                        @endphp
                        @forelse($assembly->details as $d)
                            @php
                                $total += $d['total_cost'];
                            @endphp
                            <tr>
                                <td class="border px-2 py-1">
                                    {{ $d->component->item_description ?? '-' }}
                                </td>
                                <td class="border px-2 py-1 text-center">{{ $d->unit }}</td>
                                <td class="border px-2 py-1 text-right">{{ $d->qty_used }}</td>
                                <td class="border px-2 py-1 text-right">
                                    {{ number_format($d->unit_cost, 2, ',', '.') }}
                                </td>
                                <td class="border px-2 py-1 text-right">
                                    {{ number_format($d->total_cost, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-gray-500 py-2">Tidak ada komponen</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"></td>
                            <td class="text-center text-gray-500 font-bold">Total</td>
                            <td class="text-right text-gray-500 font-bold">{{ number_format($total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <a href="{{ route('item_assembly.index') }}"
                    class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                    Back
                </a>
            </div>
        </div>
    </div>
@endsection

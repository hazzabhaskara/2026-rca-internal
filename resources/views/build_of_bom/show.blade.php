@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Build Of Bom Detail</h2>

                <!-- Header -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p><span class="font-semibold">Date:</span> {{ $data->date }}</p>
                        <p><span class="font-semibold">Item:</span>
                            {{ $data->item->item_description ?? '-' }}
                        </p>
                        <p><span class="font-semibold">Qty Built:</span> {{ $data->qty_to_build }}</p>
                    </div>
                    <div>
                        <p><span class="font-semibold">Total Cost:</span>
                            {{ number_format($data->total_cost, 2, ',', '.') }}
                        </p>
                        <p><span class="font-semibold">Notes:</span>
                            {{ $data->notes ?? '-' }}
                        </p>
                    </div>
                </div>

                <!-- Details -->
                <h3 class="text-lg font-semibold mb-2">data Components</h3>
                <table class="w-full border border-gray-300 text-sm mb-6">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-2 py-1 text-left">Component</th>
                            <th class="border px-2 py-1 text-center">Unit</th>
                            <th class="border px-2 py-1 text-right">Required Per</th>
                            <th class="border px-2 py-1 text-right">Qty Total</th>
                            <th class="border px-2 py-1 text-right">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data->details as $d)
                            <tr>
                                <td class="border px-2 py-1">
                                    {{ $d->component->item_description ?? '-' }}
                                </td>
                                <td class="border px-2 py-1 text-center">{{ $d->unit }}</td>
                                <td class="border px-2 py-1 text-right">{{ $d->qty_per_unit }}</td>
                                <td class="border px-2 py-1 text-right">{{ $d->qty_total }}</td>
                                <td class="border px-2 py-1 text-right">
                                    {{ number_format($d->cost_component, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-gray-500 py-2">Tidak ada komponen</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <a href="{{ route('build_of_bom.index') }}"
                    class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                    Back
                </a>
            </div>
        </div>
    </div>
@endsection

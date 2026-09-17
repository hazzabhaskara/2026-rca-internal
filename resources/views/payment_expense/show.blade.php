@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow w-full">
                <div class=" bg-white p-6 rounded shadow">
                    <h2 class="text-2xl font-semibold mb-6">Payment Expense Details</h2>

                    <!-- Tabs -->


                    <!-- Tab Detail -->
                    <div>
                        <!-- Informasi Utama Payment Expense -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">

                            <div>
                                <strong>Date:</strong>
                                <p>{{ \Carbon\Carbon::parse($data->date)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <strong>Source:</strong>
                                <p>{{ $data->source }}</p>
                            </div>
                            <div>
                                <strong>Vendor:</strong>
                                <p>{{ $data->Vendor->nama_vendors ?? '-' }}</p>
                            </div>
                            <div>
                                <strong>Account:</strong>
                                <p>{{ $data->Account->nama_akun ?? '-' }}</p>
                            </div>
                            <div class="md:col-span-3">
                                <strong>Keterangan:</strong>
                                <p>{{ $data->notes ?? 'Tidak ada' }}</p>
                            </div>
                        </div>

                        <!-- Detail Items -->
                        <h3 class="text-xl font-semibold mb-2">Detail Payment Expense</h3>
                        <div class="overflow-auto">
                            <table class="table-auto w-full border-collapse border border-gray-200 text-sm">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th class="border px-3 py-2">Account</th>
                                        <th class="border px-3 py-2 text-center">Description</th>
                                        <th class="border px-3 py-2 text-center">Amount</th>
                                        <th class="border px-3 py-2 text-center">Tax</th>
                                        <th class="border px-3 py-2 text-center">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $total = 0;
                                        $grandtotal = 0;
                                    @endphp
                                    @php
                                        $grandtotal = 0;
                                    @endphp

                                    @foreach ($data->details as $item)
                                        @php
                                            $lineTotal =
                                                $item->amount + ($item->amount * ($item->salesTaxes->rate ?? 0)) / 100;
                                            $grandtotal += $lineTotal;
                                        @endphp
                                        <tr>
                                            <td class="border px-3 py-2">{{ $item->Account->nama_akun ?? '-' }}</td>
                                            <td class="border px-3 py-2 text-center">{{ $item->deskripsi }}</td>
                                            <td class="border px-3 py-2 text-center">{{ number_format($item->amount) }}</td>
                                            <td class="border px-3 py-2 text-center">{{ $item->salesTaxes->rate ?? 0 }}%
                                            </td>
                                            <td class="border px-3 py-2 text-center">{{ number_format($lineTotal) }}</td>
                                        </tr>
                                    @endforeach

                                    <tr class="font-bold bg-gray-50">
                                        <td colspan="4" class="border px-3 py-2 text-right">Grand Total</td>
                                        <td class="border px-3 py-2 text-center">{{ number_format($grandtotal) }}</td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>


                    <!-- Tombol Kembali -->
                    <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                        <a href="{{ route('payment_expense.edit', $data->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a>
                        <a href="{{ route('payment_expense.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

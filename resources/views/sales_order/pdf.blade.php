<div class="container mx-auto py-6">
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-semibold mb-6">Sales Order Details</h2>
        <!-- Tab Detail -->
        <div>
            <!-- Informasi Utama Sales Order -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <strong>Order Number:</strong>
                    <p>{{ $salesOrder->order_number }}</p>
                </div>
                <div>
                    <strong>Date Order:</strong>
                    <p>{{ \Carbon\Carbon::parse($salesOrder->date_order)->format('d M Y') }}</p>
                </div>
                <div>
                    <strong>Shipping Date:</strong>
                    <p>{{ \Carbon\Carbon::parse($salesOrder->shipping_date)->format('d M Y') }}</p>
                </div>
                <div>
                    <strong>Customer:</strong>
                    <p>{{ $salesOrder->customer->nama_customers ?? '-' }}</p>
                </div>
                <div>
                    <strong>Sales Person:</strong>
                    <p>{{ $salesOrder->salesPerson->nama_karyawan ?? '-' }}</p>
                </div>
                <div>
                    <strong>Payment Method:</strong>
                    <p>{{ $salesOrder->jenisPembayaran->nama_jenis ?? '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <strong>Shipping Address:</strong>
                    <p>{{ $salesOrder->shipping_address }}</p>
                </div>

                <div>
                    <strong>Early Payment Terms:</strong>
                    <p>{{ $salesOrder->early_payment_terms }}</p>
                </div>
                <div class="md:col-span-2">
                    <strong>Messages:</strong>
                    <p>{{ $salesOrder->messages }}</p>
                </div>
            </div>

            <!-- Detail Items -->
            <h3 class="text-xl font-semibold mb-2">Order Items</h3>
            <div class="overflow-auto">
                <table class="table-auto w-full border-collapse border border-gray-200 text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="border px-3 py-2">Item</th>
                            <th class="border px-3 py-2">Qty</th>
                            <th class="border px-3 py-2">Order</th>
                            <th class="border px-3 py-2">Back Order</th>
                            <th class="border px-3 py-2">Unit</th>
                            <th class="border px-3 py-2">Description</th>
                            <th class="border px-3 py-2">Base Price</th>
                            <th class="border px-3 py-2">Discount</th>
                            <th class="border px-3 py-2">Price</th>
                            <th class="border px-3 py-2">Amount</th>
                            <th class="border px-3 py-2">Tax</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($salesOrder->details as $item)
                            <tr>
                                <td class="border px-3 py-2">{{ $item->item->item_name ?? '-' }}</td>
                                <td class="border px-3 py-2 text-center">{{ $item->quantity }}</td>
                                <td class="border px-3 py-2 text-center">{{ $item->order }}</td>
                                <td class="border px-3 py-2 text-center">{{ $item->back_order }}</td>
                                <td class="border px-3 py-2 text-center">{{ $item->unit }}</td>
                                <td class="border px-3 py-2">{{ $item->item_description }}</td>
                                <td class="border px-3 py-2 text-right">{{ number_format($item->base_price) }}</td>
                                <td class="border px-3 py-2 text-right">{{ number_format($item->discount) }}</td>
                                <td class="border px-3 py-2 text-right">{{ number_format($item->price) }}</td>
                                <td class="border px-3 py-2 text-right">{{ number_format($item->amount) }}</td>
                                <td class="border px-3 py-2 text-right">{{ number_format($item->tax) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php
                            $subtotal = $salesOrder->details->sum('amount');
                            $totalTax = $salesOrder->details->sum('tax');
                            $freight = $salesOrder->freight ?? 0; // kalau ada kolom freight di salesOrder
                            $grandTotal = $subtotal + $totalTax + $freight;
                        @endphp
                        <tr>
                            <td colspan="9" class="border px-3 py-2 text-right font-bold">Subtotal</td>
                            <td class="border px-3 py-2 text-right font-bold">{{ number_format($subtotal) }}</td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td colspan="9" class="border px-3 py-2 text-right font-bold">Total Pajak</td>
                            <td class="border px-3 py-2 text-right font-bold">{{ number_format($totalTax) }}</td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td colspan="9" class="border px-3 py-2 text-right font-bold">Freight</td>
                            <td class="border px-3 py-2 text-right font-bold">{{ number_format($freight) }}</td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td colspan="9" class="border px-3 py-2 text-right font-bold">Grand Total</td>
                            <td class="border px-3 py-2 text-right font-bold">{{ number_format($grandTotal) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>

                </table>
            </div>
        </div>
    </div>
</div>

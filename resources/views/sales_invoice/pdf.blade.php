<div class="py-10">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8">

        <div class="container mx-auto py-6">
            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-2xl font-semibold mb-6">Sales Invoice Details</h2>


                <!-- Informasi Utama Sales Order -->
                <div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <strong>Invoice Number:</strong>
                            <p>{{ $salesInvoice->invoice_number }}</p>
                        </div>
                        <div>
                            <strong>Invoice Date:</strong>
                            <p>{{ \Carbon\Carbon::parse($salesInvoice->invoice_date)->format('d M Y') }}</p>
                        </div>
                        <div>
                            <strong>Shipping Date:</strong>
                            <p>{{ \Carbon\Carbon::parse($salesInvoice->shipping_date)->format('d M Y') }}</p>
                        </div>
                        <div>
                            <strong>Customer:</strong>
                            <p>{{ $salesInvoice->customer->nama_customers ?? '-' }}</p>
                        </div>
                        <div>
                            <strong>Sales Person:</strong>
                            <p>{{ $salesInvoice->salesPerson->nama_karyawan ?? '-' }}</p>
                        </div>
                        <div>
                            <strong>Payment Method:</strong>
                            <p>{{ $salesInvoice->jenisPembayaran->nama_jenis ?? '-' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <strong>Shipping Address:</strong>
                            <p>{{ $salesInvoice->shipping_address }}</p>
                        </div>
                        <div>
                            <strong>Early Payment Terms:</strong>
                            <p>{{ $salesInvoice->early_payment_terms }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <strong>Messages:</strong>
                            <p>{{ $salesInvoice->messages }}</p>
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
                                    <th class="border px-3 py-2">Project</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($salesInvoice->details as $item)
                                    <tr>
                                        <td class="border px-3 py-2">{{ $item->item->item_name ?? '-' }}</td>
                                        <td class="border px-3 py-2 text-center">{{ $item->quantity }}</td>
                                        <td class="border px-3 py-2 text-center">{{ $item->order_quantity }}</td>
                                        <td class="border px-3 py-2 text-center">{{ $item->back_order }}</td>
                                        <td class="border px-3 py-2 text-center">{{ $item->unit }}</td>
                                        <td class="border px-3 py-2">{{ $item->description }}</td>
                                        <td class="border px-3 py-2 text-right">
                                            {{ number_format($item->base_price) }}
                                        </td>
                                        <td class="border px-3 py-2 text-right">{{ number_format($item->discount) }}
                                        </td>
                                        <td class="border px-3 py-2 text-right">{{ number_format($item->price) }}
                                        </td>
                                        <td class="border px-3 py-2 text-right">{{ number_format($item->amount) }}
                                        </td>
                                        <td class="border px-3 py-2 text-right">{{ number_format($item->tax) }}
                                        </td>
                                        <td class="border px-3 py-2">{{ $item->project->nama_project ?? 'Tidak Ada' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                @php
                                    $subtotal = $salesInvoice->details->sum('amount');
                                    $totalTax = $salesInvoice->details->sum('tax');
                                    $freight = $salesInvoice->freight ?? 0;
                                    $grandTotal = $subtotal + $totalTax + $freight;
                                @endphp
                                <tr>
                                    <td colspan="9" class="border px-3 py-2 text-right font-bold">Subtotal</td>
                                    <td class="border px-3 py-2 text-right font-bold">{{ number_format($subtotal) }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="border px-3 py-2 text-right font-bold">Total Pajak</td>
                                    <td class="border px-3 py-2 text-right font-bold">{{ number_format($totalTax) }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="border px-3 py-2 text-right font-bold">Freight</td>
                                    <td class="border px-3 py-2 text-right font-bold">{{ number_format($freight) }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="border px-3 py-2 text-right font-bold">Grand Total</td>
                                    <td class="border px-3 py-2 text-right font-bold">{{ number_format($grandTotal) }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

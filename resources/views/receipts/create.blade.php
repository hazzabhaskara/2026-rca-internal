@extends('layouts.app')
@section('content')
    <div class="py-10">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">

                @if (session('error'))
                    <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('receipts.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label>No. Receipt</label>
                            <input type="text" name="receipt_number"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div>
                            <label>Tanggal</label>
                            <input type="date" name="date"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div>
                            <label>Customer</label>
                            <select id="customer" name="customer_id"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="">-- Pilih Customer --</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->nama_customers ?? $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Deposit To</label>
                            <select name="deposit_to_id"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="">-- Pilih Akun --</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->nama_akun }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label>Comment</label>
                            <textarea name="comment"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                rows="2"></textarea>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold mb-2">💳 Invoice yang Dibayar</h3>
                    <div id="invoice-table">
                        <p class="text-gray-500">Silakan pilih customer untuk melihat invoice.</p>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            💾 Simpan Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('customer').addEventListener('change', function() {
            let customerId = this.value;
            let container = document.getElementById('invoice-table');

            if (!customerId) {
                container.innerHTML = '<p class="text-gray-500">Silakan pilih customer untuk melihat invoice.</p>';
                return;
            }

            fetch(`/get-invoices/${customerId}`)
                .then(res => res.text())
                .then(html => {
                    container.innerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    container.innerHTML = '<p class="text-red-500">Gagal memuat data invoice.</p>';
                });
        });
    </script>
@endsection

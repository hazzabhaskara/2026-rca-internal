@extends('layouts.app')
@section('content')
    <div class="max-w-full mx-auto py-10 sm:px-6 lg:px-8">

        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Detail Sales Deposit</h2>

            {{-- Informasi Utama --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                <div><strong>Deposit Number:</strong> {{ $deposit->deposit_no }}</div>
                <div><strong>Deposit Date:</strong> {{ $deposit->deposit_date }}</div>
                <div><strong>Customer:</strong> {{ $deposit->customer->nama_customers ?? '-' }}</div>
                <div><strong>Payment Method:</strong> {{ $deposit->paymentMethod->nama_jenis ?? '-' }}</div>
                <div><strong>Deposit To Account:</strong> {{ $deposit->account->nama_akun ?? '-' }}</div>
                <div><strong>Deposit Amount:</strong> Rp {{ number_format($deposit->deposit_amount, 2, ',', '.') }}
                </div>
                <div><strong>Deposit Reference:</strong> {{ $deposit->deposit_reference ?? '-' }}</div>
                <div><strong>Comment:</strong> {{ $deposit->comment ?? '-' }}</div>
            </div>
            {{-- Tombol kembali --}}
            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('sales_deposits.index') }}"
                    class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    Back
                </a>
                <a href="{{ route('sales_deposits.edit', $deposit->id) }}"
                    class="inline-block px-4 py-2 bg-yellow-600 text-white rounded transition">Edit</a>
            </div>
        </div>
    </div>
@endsection

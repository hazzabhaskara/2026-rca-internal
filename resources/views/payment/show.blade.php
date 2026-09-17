@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">

                <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                    Payment Detail
                </h4>
                <!-- Informasi Klasifikasi Akun -->
                <div class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">

                        <div>
                            <span class="font-medium">Payment Date:</span>
                            <span class="ml-2">{{ $data->payment_date }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Payment Method:</span>
                            <span class="ml-2">{{ $data->jenis_pembayaran->nama_jenis }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Payment Method Account:</span>
                            <span class="ml-2">{{ $data->PaymentMethodAccount->chartOfAccount->nama_akun }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Invoice Number:</span>
                            <span class="ml-2">{{ $invoice_number }}</span>
                        </div>

                        <div>
                            <span class="font-medium">Source:</span>
                            <span class="ml-2">{{ $data->source }}</span>
                        </div>

                        <div>
                            <span class="font-medium">Vendor:</span>
                            <span class="ml-2">{{ $data->vendor->nama_vendors ?? '-' }}</span>
                        </div>
                        {{-- <div>
                            <span class="font-medium">Payment Amount</span>
                            <span class="ml-2">{{ number_format($data->details->sum('payment_amount')) }}</span>
                        </div> --}}
                        <div>
                            <span class="font-medium">Comment:</span>
                            <span class="ml-2">{{ $data->comment }}</span>
                        </div>

                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('payment.edit', $data->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('payment.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>
    </div>
@endsection

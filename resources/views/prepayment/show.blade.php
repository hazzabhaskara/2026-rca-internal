@extends('layouts.app')
@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <!-- Informasi Klasifikasi Akun -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Informasi Prepayment</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">

                        <div>
                            <span class="font-medium">Date:</span>
                            <span class="ml-2">{{ $data->tanggal_prepayment }}</span>
                        </div>

                        <div class="md:col-span-2">
                            <span class="font-medium">Reference:</span>
                            <span class="ml-2">{{ $data->reference }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="font-medium">Account:</span>
                            <span class="ml-2">{{ $data->account->nama_akun ?? '-' }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="font-medium">Vendor:</span>
                            <span class="ml-2">{{ $data->vendor->nama_vendors }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="font-medium">Amount:</span>
                            <span class="ml-2">{{ number_format($data->amount) }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="font-medium">Comment:</span>
                            <span class="ml-2">{{ $data->comment ?? 'Tidak Ada' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('prepayment.edit', $data->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('prepayment.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition">
                    kembali</a>
            </div>
        </div>
    </div>
@endsection

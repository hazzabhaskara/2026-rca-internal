@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <!-- Informasi Payment Method -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Informasi Payment Method</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">

                        <div>
                            <span class="font-medium">Kode Jenis:</span>
                            <span class="ml-2">{{ $paymentMethod->kode_jenis }}</span>
                        </div>

                        <div>
                            <span class="font-medium">Status:</span>
                            <span class="ml-2">
                                @if ($paymentMethod->status)
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Aktif</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Non Aktif</span>
                                @endif
                            </span>
                        </div>

                        <div class="md:col-span-2">
                            <span class="font-medium">Nama Jenis:</span>
                            <span class="ml-2">{{ $paymentMethod->nama_jenis ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Detail Account -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Akun Terkait</h3>
                    <table class="w-full border-collapse border text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="border px-3 py-2 text-left w-[40%]">Account</th>
                                <th class="border px-3 py-2 text-left w-[50%]">Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($paymentMethod->details as $detail)
                                <tr>
                                    <td class="border px-3 py-2">
                                        {{ $detail->chartOfAccount->kode_akun ?? '-' }} -
                                        {{ $detail->chartOfAccount->nama_akun ?? '-' }}
                                    </td>
                                    <td class="border px-3 py-2">
                                        {{ $detail->deskripsi ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="border px-3 py-2 text-center text-gray-500">
                                        Tidak ada akun terkait
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('PaymentMethod.edit', $paymentMethod->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('PaymentMethod.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

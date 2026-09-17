@extends('layouts.app')
@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-2xl font-semibold text-gray-800">Informasi Customers</h2>
            </div>
            <div class="px-6 py-4">
                <table class="w-full text-sm text-left text-gray-700">
                    <tbody>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Kode Customers</th>
                            <td class="py-2">{{ $customer->kd_customers }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Nama customers</th>
                            <td class="py-2">{{ $customer->nama_customers }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Contact Person</th>
                            <td class="py-2">{{ $customer->contact_person }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Alamat</th>
                            <td class="py-2">{{ $customer->alamat }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Telepon</th>
                            <td class="py-2">{{ $customer->telepon }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Email</th>
                            <td class="py-2">{{ $customer->email }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Limit Kredit</th>
                            <td class="py-2">{{ $customer->limit_kredit }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Payment Terms</th>
                            <td class="py-2">{{ $customer->payment_terms }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('customers.edit', $customer->kd_customers) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('customers.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

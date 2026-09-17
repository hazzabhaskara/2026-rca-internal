@extends('layouts.app')
@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form action="{{ route('customers.update', $customers->kd_customers) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="kd_customer" class="block font-medium">Kode customers</label>
                            <input type="text" name="kd_customer" id="kd_customer"
                                value="{{ old('kd_customers', $customers->kd_customers) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('kd_customer') border-red-500 @enderror">
                            @error('kd_customer')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nama_customers" class="block font-medium">Nama Customers</label>
                            <input type="text" name="nama_customers" id="nama_customers"
                                value="{{ old('nama_customers', $customers->nama_customers) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('nama_customers') border-red-500 @enderror"
                                required>
                            @error('nama_customers')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="contact_person" class="block font-medium">Contact Person</label>
                            <input type="text" name="contact_person" id="contact_person"
                                value="{{ old('contact_person', $customers->contact_person) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('contact_person') border-red-500 @enderror"
                                required>
                            @error('contact_person')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="alamat" class="block font-medium">Alamat customers</label>
                            <input type="text" name="alamat" id="alamat"
                                value="{{ old('alamat', $customers->alamat) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('alamat') border-red-500 @enderror"
                                required>
                            @error('alamat')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="telepon" class="block font-medium">Telepon customers</label>
                            <input type="text" name="telepon" id="telepon"
                                value="{{ old('telepon', $customers->telepon) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('telepon') border-red-500 @enderror">
                            @error('telepon')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block font-medium">Email customers</label>
                            <input type="text" name="email" id="email"
                                value="{{ old('email', $customers->email) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="limit_kredit" class="block font-medium">Limit Kredit</label>
                            <input type="text" name="limit_kredit" id="limit_kredit"
                                value="{{ old('limit_kredit', $customers->limit_kredit) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('limit_kredit') border-red-500 @enderror">
                            @error('limit_kredit')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="payment_terms" class="block font-medium">Payment Terms</label>
                            <input type="text" name="payment_terms" id="payment_terms"
                                value="{{ old('payment_terms', $customers->payment_terms) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('payment_terms') border-red-500 @enderror">
                            @error('payment_terms')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('customers.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                            <i class="fas fa-save mr-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

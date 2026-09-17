@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form action="{{ route('vendors.update', $vendors->kd_vendor) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="kd_vendor" class="block font-medium">Kode vendors</label>
                            <input type="text" name="kd_vendor" id="kd_vendor"
                                value="{{ old('kd_vendors', $vendors->kd_vendor) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('kd_vendor') border-red-500 @enderror">
                            @error('kd_vendor')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nama_vendors" class="block font-medium">Nama vendors</label>
                            <input type="text" name="nama_vendors" id="nama_vendors"
                                value="{{ old('nama_vendors', $vendors->nama_vendors) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('nama_vendors') border-red-500 @enderror"
                                required>
                            @error('nama_vendors')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="contact_person" class="block font-medium">Contact Person</label>
                            <input type="text" name="contact_person" id="contact_person"
                                value="{{ old('contact_person', $vendors->contact_person) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('contact_person') border-red-500 @enderror"
                                required>
                            @error('contact_person')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="alamat" class="block font-medium">Alamat vendors</label>
                            <input type="text" name="alamat" id="alamat"
                                value="{{ old('alamat', $vendors->alamat) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('alamat') border-red-500 @enderror"
                                required>
                            @error('alamat')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="telepon" class="block font-medium">Telepon vendors</label>
                            <input type="text" name="telepon" id="telepon"
                                value="{{ old('telepon', $vendors->telepon) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('telepon') border-red-500 @enderror">
                            @error('telepon')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block font-medium">Email vendors</label>
                            <input type="text" name="email" id="email" value="{{ old('email', $vendors->email) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="payment_terms" class="block font-medium">Payment Terms</label>
                            <input type="text" name="payment_terms" id="payment_terms"
                                value="{{ old('payment_terms', $vendors->payment_terms) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('payment_terms') border-red-500 @enderror">
                            @error('payment_terms')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('vendors.index') }}"
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

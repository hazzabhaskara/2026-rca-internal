@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form method="POST"
                    action="{{ isset($vendors) ? route('vendors.update', $vendors->id) : route('vendors.store') }}">
                    @csrf
                    @if (isset($vendors))
                        @method('PUT')
                    @endif



                    <h2 class="font-bold text-2xl mb-3">Vendor Create</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- kode vendors -->
                        <div class="mb-4" id="manual_kd">
                            <label for="kd_vendor" class="block text-gray-700 font-medium mb-1">Kode vendors</label>
                            <input type="text" id="kd_vendor" name="kd_vendor"
                                value="{{ old('kd_vendor', $vendors->kd_vendor ?? '') }}"
                                placeholder="Masukkan kode vendor atau generate otomatis"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="auto_generate" class="form-checkbox text-blue-600"
                                    onchange="toggleAutoGenerate()">
                                <span class="ml-2 text-sm text-gray-700">Generate kode vendors secara otomatis</span>
                            </label>
                            @error('kd_vendor')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-gray-700 font-medium mb-1">Nama vendors</label>
                            <input type="text" id="name" name="nama_vendors" placeholder="Masukkan nama vendor"
                                required value="{{ old('nama_vendors', $vendors->nama_vendors ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('nama_vendors')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>



                    <h4 class="font-bold text-lg">Informasi Kontak</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <!-- contact person -->
                        <div class="mb-4" id="manual_kd">
                            <label for="contact_person" class="block text-gray-700 font-medium mb-1">Contact
                                Person</label>
                            <input type="text" id="contact_person" name="contact_person"
                                placeholder="Masukkan contact person"
                                value="{{ old('contact_person', $vendors->contact_person ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('contact_person')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>



                        <!-- Phone -->
                        <div class="mb-4">
                            <label for="phone" class="block text-gray-700 font-medium mb-1">No.Telepon</label>
                            <input type="text" id="phone" name="telepon" placeholder="Masukkan No.Telepon"
                                value="{{ old('telepon', $vendors->telepon ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('telepon')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                            <input type="email" id="email" name="email" placeholder="Masukkan Email"
                                value="{{ old('email', $vendors->email ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('email')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    <!-- payment terms -->

                    <div class="mb-4">
                        <label for="payment_terms" class="block text-gray-700 font-medium mb-1">Payment
                            Terms</label>
                        <input type="payment_terms" id="payment_terms" name="payment_terms"
                            placeholder="Masukkan Payment Terms"
                            value="{{ old('payment_terms', $vendors->payment_terms ?? '') }}"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('payment_terms')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- alamat -->
                    <div class="mb-4 md:col-span-3">
                        <label for="alamat" class="block text-gray-700 font-medium mb-1">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3" placeholder="Masukkan Alamat"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('alamat', $vendors->alamat ?? '') }}</textarea>
                        @error('alamat')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
            </div>

            <!-- Buttons -->
            <div class="mt-6 flex space-x-4">
                <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                    {{ isset($vendors) ? 'Update' : 'Create' }} vendors
                </button>
                <a href="{{ route('vendors.index') }}"
                    class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                    Cancel
                </a>
            </div>
            </form>
        </div>
    </div>
    </div>
    <script>
        function toggleAutoGenerate() {
            const checkbox = document.getElementById('auto_generate');
            const manualField = document.getElementById('manual_kd');

            if (checkbox.checked) {
                manualField.style.display = 'none';
            } else {
                manualField.style.display = 'block';
            }
        }

        // Inisialisasi saat halaman pertama kali dimuat
        window.onload = toggleAutoGenerate;
    </script>
@endsection

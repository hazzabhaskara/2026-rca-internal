@extends('layouts.app')
@section('content')

    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form method="POST"
                    action="{{ isset($customers) ? route('customers.update', $customers->id) : route('customers.store') }}">
                    @csrf
                    @if (isset($customers))
                        @method('PUT')
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <h2 class="font-bold text-lg mb-1">Customer Create</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                        <!-- kode customers -->
                        <div class="mb-4" id="manual_kd">
                            <label for="kd_customers" class="block text-gray-700 font-medium mb-1">Kode
                                Customers</label>
                            <input type="text" id="kd_customers" name="kd_customers"
                                value="{{ old('kd_customers', $customers->kd_customers ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Masukkan Kode Customers atau generate otomatis">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="auto_generate" class="form-checkbox text-blue-600"
                                    onchange="toggleAutoGenerate()">
                                <span class="ml-2 text-sm text-gray-700">Generate kode customers secara otomatis</span>
                            </label>
                            @error('kd_customers')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-gray-700 font-medium mb-1">Nama Customers</label>
                            <input type="text" id="name" name="nama_customers" required
                                value="{{ old('nama_customers', $customers->nama_customers ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Masukkan Nama Customers">
                            @error('nama_customers')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <h4 class="font-bold text-sm mb-2">Informasi Kontak</h4>
                    <hr>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- contact person -->
                        <div class="mb-4" id="manual_kd">
                            <label for="contact_person" class="block text-gray-700 font-medium mb-1">Contact
                                Person</label>
                            <input type="text" id="contact_person" name="contact_person"
                                value="{{ old('contact_person', $customers->contact_person ?? '') }}"
                                placeholder="Masukkan Contact person"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('contact_person')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Phone -->
                        <div class="mb-4">
                            <label for="phone" class="block text-gray-700 font-medium mb-1">No.Telepon</label>
                            <input type="text" id="phone" name="telepon"
                                value="{{ old('telepon', $customers->telepon ?? '') }}" placeholder="Masukkan No.Telepon"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('telepon')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                            <input type="email" id="email" name="email" placeholder="Masukkan Email"
                                value="{{ old('email', $customers->email ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('email')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>



                    <hr>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                        <!-- limit_kredit -->
                        <div class="mb-4">
                            <label for="limit_kredit" class="block text-gray-700 font-medium mb-1">Limit Kredit</label>
                            <input type="text" id="limit_kredit" name="limit_kredit" placeholder="Masukkan Limit kredit"
                                value="{{ old('limit_kredit', $customers->limit_kredit ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('limit_kredit')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- mata uang -->

                        <div class="mb-4">
                            <label for="payment_terms" class="block text-gray-700 font-medium mb-1">Payment
                                Terms</label>
                            <input type="payment_terms" id="payment_terms" name="payment_terms"
                                placeholder="Masukkan Payment Terms"
                                value="{{ old('payment_terms', $customers->payment_terms ?? '') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('payment_terms')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- alamat -->
                    <div class="mb-4 md:col-span-2">
                        <label for="alamat" class="block text-gray-700 font-medium mb-1">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3" placeholder="Masukkan Alamat"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('alamat', $customers->alamat ?? '') }}</textarea>
                        @error('alamat')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Buttons -->
                    <div class="mt-6 flex space-x-4">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                            {{ isset($customers) ? 'Update' : 'Create' }} Customers
                        </button>
                        <a href="{{ route('customers.index') }}"
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

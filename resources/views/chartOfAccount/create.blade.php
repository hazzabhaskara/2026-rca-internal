@extends('layouts.app')


@section('content')
    <div class="py-10 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <h2 class="mb-6 font-bold text-lg">Account Create</h2>
                <form method="POST"
                    action="{{ isset($chartOfAccounts) ? route('chartOfAccount.update', $chartOfAccounts->id) : route('chartOfAccount.store') }}">
                    @csrf
                    @if (isset($chartOfAccounts))
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


                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Akun</label>
                            <input type="text" name="kode_akun" id="kode_akun"
                                placeholder="Masukkan kode akun sesuai digit"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('kode_akun') border-red-500 @enderror"
                                value="{{ old('kode_akun', $chartOfAccounts->kode_akun ?? '') }}" required>
                            <small id="info_digit" class="text-sm text-gray-500 mt-1 block"></small>
                            @error('kode_akun')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Akun</label>
                            <input type="text" name="nama_akun" placeholder="Masukkan nama akun"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="{{ old('nama_akun', $chartOfAccounts->nama_akun ?? '') }}" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Akun</label>
                            <select name="tipe_akun"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                id="tipe_akun" ...>
                                @foreach ($tipe_akun as $tipe)
                                    @php
                                        $digit =
                                            optional($numberings->firstWhere('nama_grup', $tipe))->jumlah_digit ?? '';
                                    @endphp
                                    <option value="{{ $tipe }}" data-digit="{{ $digit }}"
                                        {{ old('tipe_akun', $chartOfAccounts->tipe_akun ?? '') == $tipe ? 'selected' : '' }}>
                                        {{ $tipe }}
                                    </option>
                                @endforeach
                            </select>

                        </div>


                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Level Akun</label>
                            <select name="level_akun"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                @foreach (['HEADER', 'GROUP ACCOUNT', 'ACCOUNT', 'SUB ACCOUNT', 'X'] as $level)
                                    <option value="{{ $level }}"
                                        {{ (old('level_akun') ?? '') == $level ? 'selected' : '' }}>
                                        {{ ucfirst($level) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Klasifikasi Akun</label>
                            <select name="klasifikasi_id" id="klasifikasi_akun_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Klasifikasi --</option>
                                @foreach ($parent_akun as $klasifikasi)
                                    <option value="{{ $klasifikasi->id }}"
                                        {{ old('klasifikasi_id', $chartOfAccounts->klasifikasi_id ?? '') == $klasifikasi->id ? 'selected' : '' }}>
                                        {{ $klasifikasi->kode_klasifikasi }} - {{ $klasifikasi->nama_klasifikasi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        @can('fiscal.access')
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fiscal Akun</label>
                                <select name="fiscal_account_id" id="fiscal_account_id"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Pilih Fiscal --</option>
                                    @foreach ($fiscalAccount as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('fiscal_account_id', $chartOfAccounts->fiscal_account_id ?? '') == $item->id ? 'selected' : '' }}>
                                            {{ $item->kode_akun }} - {{ $item->nama_akun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endcan


                        <!-- Checkboxes -->
                        <div class="col-span-1 md:col-span-2 mt-4 space-y-2">
                            <div class="flex items-center">
                                <input id="omit" name="omit_zero_balance" type="checkbox"
                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                    {{ old('omit_zero_balance', $chartOfAccounts->omit_zero_balance ?? false) ? 'checked' : '' }}>
                                <label for="omit" class="ml-2 block text-sm text-gray-700">
                                    Omit from Financial Statements if Balance is Zero
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input id="project_allocation" name="allow_project_allocation" type="checkbox"
                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                    {{ old('allow_project_allocation', $chartOfAccounts->allow_project_allocation ?? false) ? 'checked' : '' }}>
                                <label for="project_allocation" class="ml-2 block text-sm text-gray-700">
                                    Allow Project Allocation
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input id="aktif" name="aktif" type="checkbox"
                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                    {{ old('aktif', $chartOfAccounts->aktif ?? true) ? 'checked' : '' }}>
                                <label for="aktif" class="ml-2 block text-sm text-gray-700">
                                    Inactive Account
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="is_income_tax" class="block text-gray-700 font-medium mb-1">Pajak</label>
                            <select name="is_income_tax" id="is_income_tax" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih --</option>
                                <option value="1"
                                    {{ old('is_income_tax', $data->is_income_tax ?? '') == '1' ? 'selected' : '' }}>
                                    Ya</option>
                                <option value="0"
                                    {{ old('is_income_tax', $data->is_income_tax ?? '') == '0' ? 'selected' : '' }}>
                                    Tidak
                                </option>
                            </select>
                            @error('is_income_tax')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Catatan -->
                        <div class="col-span-1 md:col-span-2 mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan / Deskripsi</label>
                            <textarea name="catatan" rows="3" placeholder="Masukkan catatan"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('catatan', $chartOfAccounts->catatan ?? '') }}</textarea>
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Khusus Pajak</label>
                            <textarea name="catatan_pajak" rows="2" placeholder="Masukkan catatan khusus pajak"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('catatan_pajak', $chartOfAccounts->catatan_pajak ?? '') }}</textarea>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="mt-6 flex justify-end gap-4">
                        <a href="{{ route('chartOfAccount.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded-md">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md">
                            {{ isset($chartOfAccounts) ? 'Update' : 'Process' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Optional: Select2 CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tipeSelect = document.getElementById('tipe_akun');
            const kodeInput = document.getElementById('kode_akun');
            const infoDigit = document.getElementById('info_digit');

            function updateValidasi() {
                const selected = tipeSelect.options[tipeSelect.selectedIndex];
                const digit = selected.dataset.digit;

                if (digit) {
                    // Aktifkan input dan atur atribut validasi
                    kodeInput.disabled = false;
                    kodeInput.setAttribute('maxlength', digit);
                    kodeInput.setAttribute('minlength', digit);
                    kodeInput.dataset.jumlahDigit = digit;

                    // Tampilkan info jumlah digit ke user
                    infoDigit.innerText = 'Jumlah digit yang dibutuhkan: ' + digit;
                } else {
                    // Nonaktifkan input kalau tipe belum dipilih
                    kodeInput.disabled = true;
                    kodeInput.value = '';
                    kodeInput.removeAttribute('maxlength');
                    kodeInput.removeAttribute('minlength');
                    kodeInput.removeAttribute('data-jumlah-digit');

                    // Bersihkan info
                    infoDigit.innerText = '';
                }
            }

            kodeInput.addEventListener('input', function() {
                const digit = this.dataset.jumlahDigit;
                if (digit && this.value.length != digit) {
                    this.setCustomValidity('Kode akun harus terdiri dari ' + digit + ' digit');
                } else {
                    this.setCustomValidity('');
                }
            });

            tipeSelect.addEventListener('change', updateValidasi);

            updateValidasi(); // Inisialisasi saat pertama kali load halaman
        });
    </script>


    <script>
        $(document).ready(function() {
            $('#klasifikasi_akun_id,#fiscal_account_id').select2({
                placeholder: "-- Pilih --",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

@endsection

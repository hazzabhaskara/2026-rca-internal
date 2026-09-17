@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <h2 class="mb-6 font-bold text-lg">Account Edit</h2>

                <form method="POST" action="{{ route('chartOfAccount.update', $chartOfAccounts->id) }}">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
                        <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Akun</label>
                            <input type="text" name="kode_akun"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="{{ old('kode_akun', $chartOfAccounts->kode_akun) }}" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Akun</label>
                            <input type="text" name="nama_akun"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="{{ old('nama_akun', $chartOfAccounts->nama_akun) }}" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Akun</label>
                            <select name="tipe_akun"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                @foreach ($tipe_akun as $tipe)
                                    <option value="{{ $tipe }}"
                                        {{ old('tipe_akun', $chartOfAccounts->tipe_akun) == $tipe ? 'selected' : '' }}>
                                        {{ $tipe }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Level Akun</label>
                            <select name="level_akun"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                @foreach (['HEADER', 'GROUP ACCOUNT', 'ACCOUNT', 'SUB ACCOUNT', 'X'] as $level)
                                    <option value="{{ $level }}"
                                        {{ old('level_akun', $chartOfAccounts->level_akun) == $level ? 'selected' : '' }}>
                                        {{ ucfirst($level) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Klasifikasi Akun</label>
                            <select name="klasifikasi_id" id="klasifikasi_akun_id"
                                class="w-full select-klasifikasi border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                @foreach ($klasifikasi as $g)
                                    <option value="{{ $g->id }}"
                                        {{ isset($chartOfAccounts) && $chartOfAccounts->klasifikasi_id == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama_klasifikasi }}
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
                                    {{ old('omit_zero_balance', $chartOfAccounts->omit_zero_balance) ? 'checked' : '' }}>
                                <label for="omit" class="ml-2 block text-sm text-gray-700">
                                    Omit from Financial Statements if Balance is Zero
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input id="project_allocation" name="allow_project_allocation" type="checkbox"
                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                    {{ old('allow_project_allocation', $chartOfAccounts->allow_project_allocation) ? 'checked' : '' }}>
                                <label for="project_allocation" class="ml-2 block text-sm text-gray-700">
                                    Allow Project Allocation
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input id="aktif" name="aktif" type="checkbox"
                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                    {{ old('aktif', $chartOfAccounts->aktif) ? 'checked' : '' }}>
                                <label for="aktif" class="ml-2 block text-sm text-gray-700">
                                    Inactive Account
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="is_income_tax" class="block text-gray-700 font-medium mb-1">Akun Pajak
                                Penghasilan</label>
                            <select name="is_income_tax" id="is_income_tax" required
                                class="w-1/3 border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih --</option>
                                <option value="1"
                                    {{ old('is_income_tax', $chartOfAccounts->is_income_tax ?? '') == '1' ? 'selected' : '' }}>
                                    Ya</option>
                                <option value="0"
                                    {{ old('is_income_tax', $chartOfAccounts->is_income_tax ?? '') == '0' ? 'selected' : '' }}>
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
                            <textarea name="catatan" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('catatan', $chartOfAccounts->catatan) }}</textarea>
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Khusus Pajak</label>
                            <textarea name="catatan_pajak" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('catatan_pajak', $chartOfAccounts->catatan_pajak) }}</textarea>
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
                            Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

{{-- Select2 JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#klasifikasi_akun_id,#fiscal_account_id').select2({
            placeholder: "-- Pilih --",
            allowClear: true,
            width: '100%'
        });
    });
</script>

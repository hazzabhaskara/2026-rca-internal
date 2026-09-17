<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Edit Linked Accounts - Sales
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded px-6 py-4">
                <form method="POST" action="{{ route('linked-accounts.sales.store') }}">
                    @csrf

                    @php
                    $linkedFields = [
                    'principal_bank_account' => ['label' => 'Principal Bank Account', 'filter' => 'cash'],
                    'account_receivable' => ['label' => 'Account Receivable', 'kelompok' => 1],
                    'default_revenue' => ['label' => 'Default Revenue', 'kelompok' => 4],
                    'freight_revenue' => ['label' => 'Freight Revenue', 'kelompok' => 4],
                    'early_payment_sales_discount' => ['label' => 'Early Payment Sales Discount', 'kelompok' => [4,5]],
                    'deposits_and_prepaid_orders' => ['label' => 'Deposits & Prepaid Orders', 'kelompok' => 2],
                    ];
                    @endphp

                    @foreach($linkedFields as $kode => $info)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $info['label'] }}</label>
                        <select name="linked[{{ $kode }}]" class="w-full border rounded-md px-3 py-2">
                            <option value="">-- Pilih Akun --</option>
                            @foreach($accounts as $akun)
                            @php
                            $isMatch = true;
                            if(isset($info['filter']) && strtolower($akun->tipe_akun) !== strtolower($info['filter'])) $isMatch = false;
                            if(isset($info['kelompok'])) {
                            $kelompok = $info['kelompok'];
                            $isMatch = is_array($kelompok)
                            ? in_array($akun->level_akun, $kelompok)
                            : $akun->level_akun == $kelompok;
                            }
                            @endphp
                            @if($isMatch)
                            <option value="{{ $akun->id }}" {{ (isset($selected[$kode]) && $selected[$kode] == $akun->id) ? 'selected' : '' }}>
                                {{ $akun->kode_akun }} - {{ $akun->nama_akun }}
                            </option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    @endforeach

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
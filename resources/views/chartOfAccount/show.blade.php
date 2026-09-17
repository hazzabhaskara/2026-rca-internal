@extends('layouts.app')

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @php
            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
        @endphp
        <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
            <div class="px-6 py-4 border-b">
                <h2 class="text-2xl font-semibold text-gray-800">Informasi Account</h2>
            </div>
            <div class="px-6 py-4">
                <table class="w-full text-sm text-left text-gray-700">
                    <tbody>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Kode Akun</th>
                            <td class="py-2">{{ $chartOfAccounts->kode_akun }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Nama Akun</th>
                            <td class="py-2">{{ $chartOfAccounts->nama_akun }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Tipe Akun</th>
                            <td class="py-2">{{ $chartOfAccounts->tipe_akun }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Level Akun</th>
                            <td class="py-2">{{ $chartOfAccounts->level_akun }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Status Akun</th>
                            <td class="py-2"> {{ $chartOfAccounts->aktif ? 'true' : 'false' }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Omit Zero Balance</th>
                            <td class="py-2"> {{ $chartOfAccounts->omit_zero_balance ? 'true' : 'false' }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Allow Project Allocation</th>
                            <td class="py-2"> {{ $chartOfAccounts->allow_project_allocation ? 'true' : 'false' }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Deskripsi Akun</th>
                            <td class="py-2">{{ $chartOfAccounts->catatan }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Catatan Pajak</th>
                            <td class="py-2">{{ $chartOfAccounts->catatan_pajak }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">
                <a href="{{ route('chartOfAccount.edit', $chartOfAccounts->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>

                <a href="{{ route('chartOfAccount.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

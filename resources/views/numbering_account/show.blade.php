@extends('layouts.app')

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div
                class="sticky top-0 z-20 px-6 py-5 border-b border-gray-100 
                bg-gradient-to-r from-indigo-500 to-blue-600 
                flex justify-between items-center">
                <h2 class="text-xl font-bold text-white flex items-center">Informasi Numbering Account</h2>
            </div>
            <div class="px-6 py-4">
                <table class="w-full text-sm text-left text-gray-700">
                    <tbody>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Nama Grup</th>
                            <td class="py-2">{{ $numberingAccount->nama_grup }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 font-medium">Jumlah Digit</th>
                            <td class="py-2">{{ $numberingAccount->jumlah_digit }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Nomor Akun Awal</th>
                            <td class="py-2">{{ $numberingAccount->nomor_akun_awal }}</td>
                        </tr>
                        <tr class="border-t">
                            <th class="py-2 w-1/3 font-medium">Nomor Akun Akhir</th>
                            <td class="py-2">{{ $numberingAccount->nomor_akun_akhir }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-between">

                <a href="{{ route('numbering_account.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

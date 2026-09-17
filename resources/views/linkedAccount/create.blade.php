@extends('layouts.app')


@section('content')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Linked Account - Setup
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
                <form action="{{ route('linkedAccount.store') }}" method="POST">
                    @csrf

                    @if ($errors->any())
                        <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <input type="hidden" name="kode" value="RETAINED EARNINGS">

                    <div class="mb-4">
                        <label for="akun_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Retained Earnings <span class="text-red-500">*</span>
                        </label>
                        <select name="akun_id" id="akun_id" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Akun --</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->kode_akun }} -
                                    {{ $account->nama_akun }}</option>
                            @endforeach
                        </select>
                        @error('akun_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')
@section('content')
    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                @if (count($missingCodes) === 0)
                    <div class="text-center text-gray-500">
                        <i class="fas fa-check-circle text-green-500 text-3xl mb-3"></i>
                        <p class="text-lg font-medium">Semua Linked Account Asset sudah terisi ✅</p>
                        <a href="{{ route('linkedAccountAsset.index') }}"
                            class="mt-4 inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                            Kembali ke Daftar
                        </a>
                    </div>
                @else
                    <form action="{{ route('linkedAccountAsset.store') }}" method="POST">
                        @csrf

                        <h2 class="font-bold text-lg"> Linked Account Asset Create</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach ($missingCodes as $kode)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ $kode }} <span class="text-red-500">*</span>
                                    </label>
                                    <select name="accounts[{{ $kode }}]"
                                        class="select2-account w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="">-- Pilih Akun --</option>

                                        {{-- Pilih daftar akun sesuai kategori kode --}}
                                        @php
                                            $options = collect();
                                            if ($kode === 'Expenses') {
                                                $options = $akun5; // Kewajiban atau Aset, sesuaikan kebutuhan
                                            } elseif ($kode === 'Accumulated Depreciation/Amortisation') {
                                                $options = $akun1; // Kewajiban atau Aset, sesuaikan kebutuhan
                                            }
                                        @endphp

                                        @foreach ($options as $account)
                                            <option value="{{ $account->id }}">
                                                {{ $account->kode_akun }} - {{ $account->nama_akun }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex justify-end mt-5">
                            <a href="{{ route('linkedAccountAsset.index') }}"
                                class="px-6 py-2 mr-3 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition">
                                Process
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- Select2 JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script>
        $(document).ready(function() {
            $('.select2-account').select2({
                width: '100%',
                placeholder: '-- Pilih Akun --',
                allowClear: true
            });
        });
    </script>

@endsection

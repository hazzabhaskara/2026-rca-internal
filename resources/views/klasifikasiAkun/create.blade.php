@extends('layouts.app')

@section('content')
    <div class="py-10 px-4 sm:px-6 lg:px-8 max-w-full mx-auto">
        @php
            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
        @endphp
        <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800">Create Account Classification</h2>
            <form action="{{ route('klasifikasiAkun.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-3 gap-2 text-xs">
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Kode Classification</label>
                        <input type="text" name="kode_klasifikasi"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Masukkan Code Classification" required>
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Nama Classification</label>
                        <input type="text" name="nama_klasifikasi" placeholder="Masukkan Name Classification"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Grup Numbering Akun</label>
                        <select name="numbering_account_id"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                            <option value="">-- Pilih Grup --</option>
                            @foreach ($numberingAccounts as $group)
                                <option value="{{ $group->id }}">
                                    {{ $group->nama_grup }} ({{ $group->nomor_akun_awal }} - {{ $group->nomor_akun_akhir }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" placeholder="Masukkan Deskripsi(opsional)"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Aktif?</label>
                        <select name="aktif"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                            <option value="1" selected>Ya</option>
                            <option value="0">Tidak</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-4">
                    <a href="{{ route('klasifikasiAkun.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded-md">
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

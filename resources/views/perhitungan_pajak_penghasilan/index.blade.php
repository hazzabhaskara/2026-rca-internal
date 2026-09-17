@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200" x-data="{ tab: 'komponen' }">
                @php
                    $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                @endphp

                <!-- Header -->
                <div class="sticky top-0 z-20 px-6 py-5 border-b border-gray-100 flex justify-between items-center"
                    style="background: {{ $themeColor }};">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-list mr-3 text-white text-xl"></i>
                        Perhitungan Pajak Penghasilan
                    </h3>
                </div>

                <!-- Tabs -->
                <div class="border-b mb-4 flex space-x-4 px-6 bg-gray-50">
                    <button @click="tab = 'komponen'"
                        :class="tab === 'komponen' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-600'"
                        class="px-4 py-2 focus:outline-none">
                        Komponen Dasar
                    </button>

                    <button @click="tab = 'kredit_pajak'"
                        :class="tab === 'kredit_pajak' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                            'text-gray-600'"
                        class="px-4 py-2 focus:outline-none">
                        Kredit Pajak
                    </button>

                    <button @click="tab = 'pengaturan_tarif'"
                        :class="tab === 'pengaturan_tarif' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                            'text-gray-600'"
                        class="px-4 py-2 focus:outline-none">
                        Pengaturan Tarif
                    </button>
                </div>

                <!-- Panel Komponen -->
                <div class="max-w-full mx-auto bg-white shadow-md rounded-xl p-6 mb-4 mt-4" x-show="tab === 'komponen'">
                    <h2 class="text-xl font-bold mb-6 text-gray-800">
                        Komponen dari Rekonsiliasi 1A
                    </h2>
                    <div class="space-y-2 text-gray-700">
                        <div class="flex justify-between">
                            <span>Laba Komersial:</span>
                            <span class="font-semibold">Rp
                                {{ number_format($summary['laba_komersial'], 2, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span>− PPh Final:</span>
                            <span class="font-semibold text-red-600">−Rp
                                {{ number_format($summary['pph_final'], 2, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span>− TMOP:</span>
                            <span class="font-semibold text-red-600">−Rp
                                {{ number_format($summary['tmop'], 2, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span>+ Koreksi (+):</span>
                            <span class="font-semibold text-green-600">+Rp
                                {{ number_format($summary['koreksi_plus'], 2, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span>− Koreksi (−):</span>
                            <span class="font-semibold text-red-600">−Rp
                                {{ number_format($summary['koreksi_minus'], 2, ',', '.') }}</span>
                        </div>

                        <hr class="my-4">

                        <div class="flex justify-between text-lg font-bold">
                            <span>PKP (Penghasilan Kena Pajak):</span>
                            <span class="text-blue-700">Rp {{ number_format($summary['pkp'], 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Panel Kredit Pajak -->
                <div class="max-w-full mx-auto bg-white shadow-md rounded-xl p-6 mb-4 mt-4" x-show="tab === 'kredit_pajak'">
                    <h2 class="text-xl font-bold mb-6 text-gray-800">Kredit Pajak</h2>

                    <form
                        action="{{ isset($kredit) ? route('perhitungan_pajak.updateKreditPajak', $kredit->id) : route('perhitungan_pajak.saveKreditPajak') }}"
                        method="POST" class="space-y-6">
                        @csrf
                        @if (isset($kredit))
                            @method('PUT')
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-700">
                            <!-- Kolom Kiri: PPh 22,23,24 -->
                            <div class="space-y-3">
                                <h3 class="text-lg font-semibold text-gray-800 border-b pb-1">PPh 22, 23, 24</h3>

                                <div class="flex justify-between">
                                    <span>PPh Pasal 22 (Impor/Pembelian)</span>
                                    <input type="text" name="pph_22" value="{{ old('pph_22', $kredit->pph_22 ?? '') }}"
                                        class="w-40 text-right border border-gray-300 rounded-lg px-3 py-1 bg-gray-50 focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="flex justify-between">
                                    <span>PPh Pasal 23 (Jasa/Sewa)</span>
                                    <input type="text" name="pph_23" value="{{ old('pph_23', $kredit->pph_23 ?? '') }}"
                                        class="w-40 text-right border border-gray-300 rounded-lg px-3 py-1 bg-gray-50 focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="flex justify-between">
                                    <span>PPh Pasal 24 (Luar Negeri)</span>
                                    <input type="text" name="pph_24" value="{{ old('pph_24', $kredit->pph_24 ?? '') }}"
                                        class="w-40 text-right border border-gray-300 rounded-lg px-3 py-1 bg-gray-50 focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <!-- Kolom Kanan: PPh 25 -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 border-b pb-1 mb-3">PPh 25 (Angsuran Bulanan)
                                </h3>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    @php
                                        $bulan = [
                                            'Januari',
                                            'Februari',
                                            'Maret',
                                            'April',
                                            'Mei',
                                            'Juni',
                                            'Juli',
                                            'Agustus',
                                            'September',
                                            'Oktober',
                                            'November',
                                            'Desember',
                                        ];
                                    @endphp
                                    @foreach ($bulan as $b)
                                        @php
                                            $nilai = isset($kredit)
                                                ? optional($kredit->pph25->where('bulan', $b)->first())->nilai
                                                : null;
                                        @endphp
                                        <div class="space-y-1">
                                            <label class="block text-sm text-gray-600">{{ $b }}</label>
                                            <input type="text" name="pph_25_{{ strtolower($b) }}"
                                                value="{{ old('pph_25_' . strtolower($b), $nilai) }}"
                                                class="w-full text-right border border-gray-300 rounded-lg px-3 py-1 bg-gray-50 focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <hr class="my-6">

                        <!-- Tombol -->
                        <div class="mt-4 flex justify-end">
                            <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                                {{ isset($kredit) ? 'Update Kredit Pajak' : 'Simpan Kredit Pajak' }}
                            </button>
                        </div>
                    </form>
                </div>
                <div class="max-w-full mx-auto bg-white shadow-md rounded-xl p-6 mb-4 mt-4"
                    x-show="tab === 'pengaturan_tarif'">
                    <h2 class="text-xl font-bold mb-6 text-gray-800">
                        Pengaturan Tarif Pajak
                    </h2>
                    <div class="space-y-2 text-gray-700">
                        <div class="flex justify-between">
                            <span>PKP (post kompensasi):</span>
                            <span class="font-semibold">Rp {{ number_format($summary['pkp'], 2, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span>Tarif PPh (22%):</span>
                            <span class="font-semibold">Rp {{ number_format($pphTerutang, 2, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span>(-) Kredit PPh 22:</span>
                            <span class="font-semibold">Rp {{ number_format($kredit->pph_22 ?? 0, 2, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span>(-) Kredit PPh 23:</span>
                            <span class="font-semibold">Rp {{ number_format($kredit->pph_23 ?? 0, 2, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span>(-) Kredit PPh 24:</span>
                            <span class="font-semibold">Rp {{ number_format($kredit->pph_24 ?? 0, 2, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span>(-) Kredit PPh 25:</span>
                            <span class="font-semibold">Rp {{ number_format($pph25Total, 2, ',', '.') }}</span>
                        </div>

                        <hr class="my-4">

                        <div class="flex justify-between text-lg font-bold">
                            <span>PPh 29:</span>
                            <span class="text-red-600">Rp {{ number_format($pph29, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp

            @if (session('success'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200 flex items-center">
                    <i class="fas fa-check-circle mr-2 text-green-600"></i>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center"
                    style="background: {{ $themeColor }};">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-sliders-h mr-3 text-white text-xl"></i>
                        Fiscal Settings (Opsi Fisrec)
                    </h3>
                    <span class="text-xs text-white bg-white/20 px-3 py-1 rounded-full font-semibold">
                        Setup General Journal Integrasi
                    </span>
                </div>

                <form action="{{ route('fiscal_setting.update') }}" method="POST" class="p-6 md:p-8 space-y-6">
                    @csrf

                    <div>
                        <h4 class="text-lg font-bold text-gray-900">Format Fiscorr & Penyesuaian Fiskal di General Journal</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Atur keberadaan kolom Fiscorr dan Penyesuaian Fiskal pada form Create & Edit General Journal. Jika dinonaktifkan, proses fiskal difokuskan melalui menu Fisrec tersendiri.
                        </p>
                    </div>

                    <!-- 3 Options Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Option 1: Ditiadakan (Default) -->
                        <label class="relative flex flex-col border-2 rounded-xl p-5 cursor-pointer transition hover:shadow-md {{ $fiscalJournalMode === 'disabled' ? 'border-emerald-600 bg-emerald-50/40 ring-2 ring-emerald-500/20' : 'border-gray-200 bg-white' }}">
                            <div class="flex items-center justify-between mb-3">
                                <span class="font-bold text-gray-900 flex items-center gap-2">
                                    <input type="radio" name="fiscal_journal_mode" value="disabled" class="text-emerald-600 focus:ring-emerald-500" {{ $fiscalJournalMode === 'disabled' ? 'checked' : '' }}>
                                    Ditiadakan Sama Sekali
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-600 text-white font-bold">DEFAULT</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                Format Fiscorr & Penyesuaian Fiskal <strong>ditiadakan total</strong> dari form jurnal umum. Transaksi jurnal tidak menyimpan tag fiskal (pajak 0 & penyesuaian null).
                            </p>
                            <div class="mt-4 pt-3 border-t border-gray-100 text-xs font-semibold text-emerald-800">
                                Sangat direkomendasikan untuk input operasional harian.
                            </div>
                        </label>

                        <!-- Option 2: Sembunyikan Saja (Hide Only) -->
                        <label class="relative flex flex-col border-2 rounded-xl p-5 cursor-pointer transition hover:shadow-md {{ $fiscalJournalMode === 'hide' ? 'border-blue-600 bg-blue-50/40 ring-2 ring-blue-500/20' : 'border-gray-200 bg-white' }}">
                            <div class="flex items-center justify-between mb-3">
                                <span class="font-bold text-gray-900 flex items-center gap-2">
                                    <input type="radio" name="fiscal_journal_mode" value="hide" class="text-blue-600 focus:ring-blue-500" {{ $fiscalJournalMode === 'hide' ? 'checked' : '' }}>
                                    Sembunyikan Saja (Hide Only)
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-semibold border">PILIHAN</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                Kolom Fiscorr & Penyesuaian Fiskal disembunyikan agar form ringkas, namun jika jurnal lama sudah memiliki data fiskal historis, <strong>data tidak dihapus</strong> saat diedit.
                            </p>
                            <div class="mt-4 pt-3 border-t border-gray-100 text-xs font-semibold text-blue-800">
                                Menjaga riwayat data fiskal lama tetap utuh.
                            </div>
                        </label>

                        <!-- Option 3: Aktif Penuh -->
                        <label class="relative flex flex-col border-2 rounded-xl p-5 cursor-pointer transition hover:shadow-md {{ $fiscalJournalMode === 'enabled' ? 'border-indigo-600 bg-indigo-50/40 ring-2 ring-indigo-500/20' : 'border-gray-200 bg-white' }}">
                            <div class="flex items-center justify-between mb-3">
                                <span class="font-bold text-gray-900 flex items-center gap-2">
                                    <input type="radio" name="fiscal_journal_mode" value="enabled" class="text-indigo-600 focus:ring-indigo-500" {{ $fiscalJournalMode === 'enabled' ? 'checked' : '' }}>
                                    Diadakan / Aktif Penuh
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-semibold border">PILIHAN</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                Kolom Fiscorr & Penyesuaian Fiskal <strong>ditampilkan penuh</strong> di form General Journal. Pengguna dapat menandai pajak dan kode penyesuaian fiskal langsung per baris.
                            </p>
                            <div class="mt-4 pt-3 border-t border-gray-100 text-xs font-semibold text-indigo-800">
                                Mode input manual lengkap perpajakan.
                            </div>
                        </label>
                    </div>

                    <!-- Footer Info & Button -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-4 border-t border-gray-200">
                        <div class="text-xs text-gray-500 flex items-center gap-1.5">
                            <i class="fas fa-info-circle text-blue-500"></i>
                            Perubahan akan langsung berlaku pada form General Journal Create dan Edit tanpa restart server.
                        </div>
                        <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-lg shadow transition inline-flex items-center gap-2">
                            <i class="fas fa-save"></i>
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

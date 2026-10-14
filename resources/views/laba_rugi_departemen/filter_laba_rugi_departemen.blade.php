@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-2xl mx-auto px-4">

        {{-- Notifications --}}
        @if (session('success'))
            <div class="mb-4 flex items-start gap-3 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg p-4 shadow-sm">
                <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-semibold text-emerald-800">Berhasil!</p>
                    <p class="text-emerald-700 text-sm">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 flex items-start gap-3 bg-red-50 border-l-4 border-red-500 rounded-lg p-4 shadow-sm">
                <svg class="w-5 h-5 text-red-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Card Filter --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Card Header --}}
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 px-6 py-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-white font-bold text-lg leading-tight">Laba Rugi Departemen</h2>
                        <span class="px-2 py-0.5 text-[10px] font-semibold tracking-wider uppercase bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 rounded-full">PSAK & IFRS</span>
                    </div>
                    <p class="text-indigo-200/80 text-xs mt-0.5">Multi-Step Performance, Eliminasi Antar-Unit & Rekonsiliasi Neraca</p>
                </div>
            </div>

            {{-- Form --}}
            <div class="p-6">
                <form method="GET" action="{{ route('laba_rugi_departemen.report') }}" class="space-y-6">

                    {{-- Rentang Tanggal --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Periode Transaksi Laba Rugi
                            <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="start_date" class="block text-xs font-medium text-slate-500 mb-1">Tanggal Mulai</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <input type="date" id="start_date" name="start_date"
                                        value="{{ request('start_date', now()->startOfYear()->format('Y-m-d')) }}"
                                        class="w-full pl-10 pr-3 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition"
                                        required>
                                </div>
                            </div>
                            <div>
                                <label for="end_date" class="block text-xs font-medium text-slate-500 mb-1">Tanggal Akhir (Cut-Off)</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <input type="date" id="end_date" name="end_date"
                                        value="{{ request('end_date', now()->format('Y-m-d')) }}"
                                        class="w-full pl-10 pr-3 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition"
                                        required>
                                </div>
                            </div>
                        </div>

                        {{-- Quick Period Presets --}}
                        <div class="flex flex-wrap items-center gap-1.5 mt-2.5">
                            <span class="text-[11px] font-medium text-slate-400 mr-1">Preset Cepat:</span>
                            <button type="button" onclick="setPreset('this_month')" class="px-2 py-1 text-[11px] font-medium bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition border border-slate-200/60">Bulan Ini</button>
                            <button type="button" onclick="setPreset('ytd')" class="px-2 py-1 text-[11px] font-medium bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition border border-slate-200/60">YTD (Tahun Ini)</button>
                            <button type="button" onclick="setPreset('full_2025')" class="px-2 py-1 text-[11px] font-medium bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition border border-slate-200/60">Tahun 2025 Penuh</button>
                            <button type="button" onclick="setPreset('q1')" class="px-2 py-1 text-[11px] font-medium bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition border border-slate-200/60">Q1</button>
                            <button type="button" onclick="setPreset('q2')" class="px-2 py-1 text-[11px] font-medium bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition border border-slate-200/60">Q2</button>
                            <button type="button" onclick="setPreset('q3')" class="px-2 py-1 text-[11px] font-medium bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition border border-slate-200/60">Q3</button>
                            <button type="button" onclick="setPreset('q4')" class="px-2 py-1 text-[11px] font-medium bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition border border-slate-200/60">Q4</button>
                        </div>
                    </div>

                    {{-- Mode Switcher --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Mode Tampilan Laporan
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/30 cursor-pointer transition">
                                <input type="radio" name="mode" value="matrix" id="mode_matrix"
                                    class="text-indigo-600 focus:ring-indigo-300"
                                    {{ request('mode', 'matrix') === 'matrix' ? 'checked' : '' }}
                                    onchange="toggleMode(this.value)">
                                <div>
                                    <span class="text-sm font-semibold text-slate-800 block">Matriks Komparatif</span>
                                    <span class="text-xs text-slate-500">9 Cabang, Eliminasi & Konsolidasi</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/30 cursor-pointer transition">
                                <input type="radio" name="mode" value="single" id="mode_single"
                                    class="text-indigo-600 focus:ring-indigo-300"
                                    {{ request('mode') === 'single' ? 'checked' : '' }}
                                    onchange="toggleMode(this.value)">
                                <div>
                                    <span class="text-sm font-semibold text-slate-800 block">Single Departemen</span>
                                    <span class="text-xs text-slate-500">Analisis Mendalam 1 Cabang Tertentu</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Dropdown Departemen (Hanya muncul jika mode Single) --}}
                    <div id="dept_select_container" class="{{ request('mode') === 'single' ? '' : 'hidden' }} transition-all">
                        <label for="departemen_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Pilih Departemen / Cabang
                            <span class="text-rose-500">*</span>
                        </label>
                        <select id="departemen_id" name="departemen_id"
                            class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 bg-white transition">
                            <option value="konsolidasi" {{ request('departemen_id') === 'konsolidasi' ? 'selected' : '' }}>
                                ★ TOTAL KONSOLIDASI (Seluruh Perusahaan)
                            </option>
                            <option value="unassigned" {{ request('departemen_id') === 'unassigned' ? 'selected' : '' }}>
                                [NON-DEPT] Non-Departemen / Unassigned
                            </option>
                            <optgroup label="Daftar Cabang Aktif">
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ (string)request('departemen_id') === (string)$dept->id ? 'selected' : '' }}>
                                        {{ $dept->kode }} - {{ $dept->deskripsi }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    {{-- Opsi Tampilan --}}
                    <div class="border-t border-slate-100 pt-4 space-y-2.5">
                        <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Opsi Tampilan Tabel</span>
                        
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="hide_account_with_zero" value="hide_account_with_zero"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-300"
                                {{ request('hide_account_with_zero') ? 'checked' : '' }}>
                            <span class="text-sm text-slate-700">Sembunyikan akun yang bersaldo Rp 0,00 di seluruh kolom</span>
                        </label>

                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="show_account_number" value="show_account_number"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-300"
                                {{ request('show_account_number', '1') ? 'checked' : '' }}>
                            <span class="text-sm text-slate-700">Tampilkan nomor / kode akun CoA di samping nama akun</span>
                        </label>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full bg-gradient-to-r from-indigo-600 to-slate-900 hover:from-indigo-700 hover:to-black text-white font-semibold py-3 px-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Tampilkan Laporan Laba Rugi
                        </button>
                    </div>

                </form>
            </div>
        </div>

        {{-- Footer Info --}}
        <div class="mt-4 text-center">
            <p class="text-xs text-slate-400">
                Laporan mengintegrasikan PSAK Multi-Step, eliminasi reciprocal internal, serta rekonsiliasi matematis Rp 0,00 dengan Neraca Departemen.
            </p>
        </div>

    </div>
</div>

<script>
function toggleMode(val) {
    const container = document.getElementById('dept_select_container');
    if (val === 'single') {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}

function setPreset(type) {
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    const now = new Date();
    const curYear = now.getFullYear();

    if (type === 'this_month') {
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const lastDay = new Date(y, now.getMonth() + 1, 0).getDate();
        startInput.value = `${y}-${m}-01`;
        endInput.value = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;
    } else if (type === 'ytd') {
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        startInput.value = `${y}-01-01`;
        endInput.value = `${y}-${m}-${d}`;
    } else if (type === 'full_2025') {
        startInput.value = '2025-01-01';
        endInput.value = '2025-12-31';
    } else if (type === 'q1') {
        const y = curYear;
        startInput.value = `${y}-01-01`;
        endInput.value = `${y}-03-31`;
    } else if (type === 'q2') {
        const y = curYear;
        startInput.value = `${y}-04-01`;
        endInput.value = `${y}-06-30`;
    } else if (type === 'q3') {
        const y = curYear;
        startInput.value = `${y}-07-01`;
        endInput.value = `${y}-09-30`;
    } else if (type === 'q4') {
        const y = curYear;
        startInput.value = `${y}-10-01`;
        endInput.value = `${y}-12-31`;
    }
}
</script>
@endsection

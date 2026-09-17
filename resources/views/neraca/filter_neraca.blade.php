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

        @if (session('not_found'))
            <div class="mb-4 flex items-start gap-3 bg-red-50 border-l-4 border-red-500 rounded-lg p-4 shadow-sm">
                <svg class="w-5 h-5 text-red-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-red-700">{{ session('not_found') }}</p>
            </div>
        @endif

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Card Header --}}
            <div class="bg-gradient-to-r from-slate-800 to-slate-700 px-6 py-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-white font-bold text-base leading-tight">Balance Sheet</h2>
                    <p class="text-slate-300 text-xs">Laporan Neraca Keuangan</p>
                </div>
            </div>

            {{-- Form --}}
            <div class="p-6">
                <form method="GET" action="{{ route('neraca.neraca_report') }}" class="space-y-5">

                    {{-- Tanggal --}}
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Tanggal Laporan
                            <span class="text-red-500 ml-0.5">*</span>
                        </label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <input type="date" id="end_date" name="end_date"
                                value="{{ request('end_date', now()->format('Y-m-d')) }}"
                                max="{{ now()->endOfYear()->format('Y-m-d') }}"
                                class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400"
                                required>
                        </div>
                    </div>

                    {{-- Options --}}
                    <div class="space-y-3">
                        <p class="text-sm font-medium text-slate-700">Opsi Tampilan</p>

                        <label class="flex items-start gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors">
                            <input type="checkbox" name="show_account_number" value="show_account_number"
                                class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-300"
                                {{ request('show_account_number') == 'show_account_number' ? 'checked' : '' }}>
                            <div>
                                <span class="text-sm font-medium text-slate-700 block">Tampilkan Nomor Akun</span>
                                <span class="text-xs text-slate-400">Menampilkan kode akun di samping nama akun</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors">
                            <input type="checkbox" name="hide_account_with_zero" value="hide_account_with_zero"
                                class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-300"
                                {{ request('hide_account_with_zero') == 'hide_account_with_zero' ? 'checked' : '' }}>
                            <div>
                                <span class="text-sm font-medium text-slate-700 block">Sembunyikan Akun Saldo Nol</span>
                                <span class="text-xs text-slate-400">Menyembunyikan akun yang tidak memiliki transaksi</span>
                            </div>
                        </label>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2 pt-2">
                        <button type="submit"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white font-semibold text-sm rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Lihat Laporan
                        </button>
                        <a href="{{ route('neraca.filter_neraca') }}"
                            class="px-4 py-2.5 border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const endDateInput = document.getElementById('end_date');
        const maxDate = new Date("{{ now()->endOfYear()->format('Y-m-d') }}");

        endDateInput.addEventListener('change', function () {
            const selectedDate = new Date(this.value);
            if (selectedDate > maxDate) {
                alert('Tanggal tidak boleh melebihi akhir tahun berjalan!');
                this.value = maxDate.toISOString().split('T')[0];
            }
        });
    });
</script>
@endsection

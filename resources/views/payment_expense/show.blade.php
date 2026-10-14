@extends('layouts.app')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-full mx-auto space-y-6">

    <!-- ========================================================================= -->
    <!-- SAGE 50 TOP ACTION RIBBON TOOLBAR                                         -->
    <!-- ========================================================================= -->
    <div class="bg-slate-900 rounded-2xl shadow-xl border border-slate-800 overflow-hidden text-white">
        <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 via-teal-400 to-indigo-500"></div>

        <div class="p-4 sm:p-5 flex flex-wrap items-center justify-between gap-4">
            <!-- Left Info -->
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center font-bold text-lg shadow-inner">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base sm:text-lg font-bold tracking-tight text-white">
                            {{ $data->voucher_number ?: 'EXP#' . $data->id }}
                        </h2>
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            POSTED TO GENERAL JOURNAL
                        </span>
                    </div>
                    <p class="text-xs text-slate-300">
                        Sumber Akun: <strong class="text-emerald-400 font-mono">{{ $data->Account ? ('[' . $data->Account->kode_akun . '] ' . $data->Account->nama_akun) : '-' }}</strong> &bull; Divisi {{ $data->departemen->deskripsi ?? 'Pusat' }} &bull; Tanggal {{ \Carbon\Carbon::parse($data->date)->format('d F Y') }}
                    </p>
                </div>
            </div>

            <!-- Sage 50 Action Ribbon Buttons -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Cetak Bon / Print Voucher -->
                <a href="{{ route('payment_expense.print_voucher', $data->id) }}" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-md shadow-emerald-500/20 active:scale-95 transition-all">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Bon (Voucher)</span>
                </a>

                @if ($data->attachment)
                    <a href="{{ $data->attachment_url }}" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-300 border border-slate-700 text-xs font-semibold transition-all">
                        <i class="fa-solid fa-paperclip"></i>
                        <span>Buka Dokumen Bon</span>
                    </a>
                @endif

                @can('payment_expense.update')
                    <a href="{{ route('payment_expense.edit', $data->id) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-300 border border-slate-700 text-xs font-semibold transition-all">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Edit</span>
                    </a>
                @endcan

                <a href="{{ route('payment_expense.index') }}"
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700 transition-all">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SAGE 50 VOUCHER CARD MAIN BODY                                            -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden space-y-6 p-6">

        <!-- HEADER SLIP INFORMATION -->
        <div class="bg-slate-50/80 rounded-2xl p-5 border border-slate-200/80 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 text-xs">
            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Nomor Voucher</span>
                <span class="text-sm font-mono font-bold text-slate-900">{{ $data->voucher_number ?: 'EXP#' . $data->id }}</span>
            </div>

            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Tanggal Transaksi</span>
                <span class="text-sm font-medium text-slate-800">{{ \Carbon\Carbon::parse($data->date)->format('d/m/Y') }}</span>
            </div>

            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Divisi / Departemen</span>
                @if ($data->departemen)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        <i class="fa-solid fa-building text-[10px]"></i>
                        <span>{{ $data->departemen->deskripsi }}</span>
                    </span>
                @else
                    <span class="text-gray-500 italic">Pusat / General</span>
                @endif
            </div>

            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Kategori Dana Kas</span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class="fa-solid fa-wallet text-[10px]"></i>
                    <span>{{ $data->category_source ?: $data->source }}</span>
                </span>
            </div>

            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Akun Kas / Bank (Credit)</span>
                <span class="text-xs font-bold text-slate-800">
                    [{{ $data->Account->kode_akun ?? '-' }}] {{ $data->Account->nama_akun ?? '-' }}
                </span>
            </div>

            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Penerima Dana / Vendor</span>
                <span class="text-xs font-medium text-slate-800">{{ $data->Vendor->nama_vendors ?? 'Non-Vendor / Internal' }}</span>
            </div>

            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Diajukan / Diinput Oleh</span>
                <span class="text-xs font-medium text-slate-800">{{ $data->user->name ?? 'Sistem' }}</span>
            </div>

            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Referensi / Source</span>
                <span class="text-xs font-medium text-slate-800">{{ $data->source ?: '-' }}</span>
            </div>
        </div>

        <!-- SAGE 50 PROMINENT AMOUNT PAID BANNER -->
        <div class="rounded-2xl p-4 bg-gradient-to-r from-slate-900 to-slate-800 text-white border border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Total Pengeluaran Kas (Voucher Amount)</span>
                <div class="text-xs text-slate-300 italic font-mono mt-0.5">
                    "{{ $data->terbilang }}"
                </div>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold font-mono text-emerald-300">
                    Rp {{ number_format($data->grand_total, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- SAGE 50 EXPENSE DISTRIBUTION TABLE -->
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-emerald-600"></i>
                <span>Distribusi Rincian Beban Biaya (Expense Distribution)</span>
            </h3>

            <div class="border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[10px] border-b border-gray-200">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4 w-36">Kode Akun</th>
                            <th class="py-3 px-4 w-4/12">Nama Akun Beban</th>
                            <th class="py-3 px-4">Deskripsi / Keterangan Belanja</th>
                            <th class="py-3 px-4 text-right w-36">Jumlah (Rp)</th>
                            <th class="py-3 px-4 text-center w-24">Pajak</th>
                            <th class="py-3 px-4 text-right w-36">Total Baris</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($data->details as $item)
                            @php
                                $taxRate = $item->salesTaxes->rate ?? 0;
                                $lineTotal = $item->amount + ($item->amount * ($taxRate / 100));
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 px-4 text-center text-gray-400 font-mono">{{ $loop->iteration }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $item->Account->kode_akun ?? '-' }}</td>
                                <td class="py-3 px-4 font-medium text-slate-800">{{ $item->Account->nama_akun ?? '-' }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $item->deskripsi ?: '-' }}</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">
                                    Rp {{ number_format($item->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if ($item->salesTaxes)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                            {{ $item->salesTaxes->name }} ({{ $taxRate }}%)
                                        </span>
                                    @else
                                        <span class="text-gray-300 font-bold">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($lineTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t border-gray-200 text-slate-800">
                        <tr>
                            <td colspan="4" class="py-2.5 px-4 text-right uppercase text-[11px] text-gray-500">Subtotal Beban:</td>
                            <td class="py-2.5 px-4 text-right font-mono">Rp {{ number_format($data->subtotal, 0, ',', '.') }}</td>
                            <td class="py-2.5 px-4 text-center text-[11px] text-gray-500">Pajak: Rp {{ number_format($data->total_tax, 0, ',', '.') }}</td>
                            <td class="py-2.5 px-4 text-right font-mono text-emerald-700 text-sm">
                                Rp {{ number_format($data->grand_total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- MEMO & DOKUMEN PENDUKUNG (ATTACHMENT CARD) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
            
            <!-- Memo -->
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 space-y-1.5">
                <span class="block text-xs font-bold uppercase tracking-wider text-slate-700">Catatan / Memo Pengeluaran</span>
                <p class="text-xs text-gray-700 whitespace-pre-line leading-relaxed">
                    {{ $data->notes ?: 'Tidak ada catatan tambahan.' }}
                </p>
            </div>

            <!-- Dokumen Lampiran Bon / Struk -->
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-700">Dokumen Bukti (Bon / Struk / Nota)</span>
                    @if ($data->attachment)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Terlampir</span>
                    @endif
                </div>

                @if ($data->attachment)
                    @php
                        $ext = strtolower(pathinfo($data->attachment, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                    @endphp

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid {{ $isImage ? 'fa-image' : 'fa-file-pdf' }}"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-gray-800 truncate">{{ basename($data->attachment) }}</div>
                            <span class="text-[10px] text-gray-400 font-mono uppercase">{{ $ext }} Document</span>
                        </div>
                        <a href="{{ $data->attachment_url }}" target="_blank"
                            class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition-all">
                            <i class="fa-solid fa-arrow-up-right-from-square mr-1 text-[10px]"></i> Buka File
                        </a>
                    </div>

                    @if ($isImage)
                        <div class="mt-2 border rounded-xl overflow-hidden bg-white max-h-64 flex items-center justify-center p-2">
                            <img src="{{ $data->attachment_url }}" alt="Bukti Bon" class="max-h-60 rounded object-contain">
                        </div>
                    @endif
                @else
                    <div class="text-center py-4 text-gray-400 text-xs">
                        <i class="fa-solid fa-file-circle-xmark text-2xl mb-1.5 block"></i>
                        Tidak ada dokumen bon fisik yang diunggah untuk transaksi ini.
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection

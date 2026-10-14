<div class="p-4 bg-white text-slate-800">
    {{-- Header detail summary --}}
    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-4">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 pb-2 mb-3">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Detail Transaksi</span>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>#{{ $detail->id }}</span>
                    <span class="text-xs px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 font-semibold">
                        {{ $detail->journalEntry ? $detail->journalEntry->source : 'Tanpa Voucher' }}
                    </span>
                </h3>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-500">Tanggal:</span>
                <span class="text-xs font-semibold text-slate-700 block">
                    {{ $detail->journalEntry ? \Carbon\Carbon::parse($detail->journalEntry->tanggal)->format('d/m/Y') : '-' }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
            <div>
                <span class="text-slate-500 block">Akun COA:</span>
                <span class="font-semibold text-slate-900">
                    {{ $detail->kode_akun }} - {{ $detail->chartOfAccount ? $detail->chartOfAccount->nama_akun : '' }}
                </span>
            </div>
            <div>
                <span class="text-slate-500 block">Departemen Saat Ini:</span>
                <span class="font-semibold text-slate-900">
                    @if($detail->departemenAkun && $detail->departemenAkun->departemen)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                            {{ $detail->departemenAkun->departemen->kode }} - {{ $detail->departemenAkun->departemen->deskripsi }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-600">
                            Tanpa Departemen (NULL)
                        </span>
                    @endif
                </span>
            </div>
            <div>
                <span class="text-slate-500 block">Nominal Debit / Credit:</span>
                <span class="font-mono font-semibold text-slate-900">
                    Rp {{ number_format($detail->debits, 0, ',', '.') }} / Rp {{ number_format($detail->credits, 0, ',', '.') }}
                </span>
            </div>
            <div>
                <span class="text-slate-500 block">Keterangan Baris:</span>
                <span class="text-slate-700 italic">
                    {{ $detail->comment ?: ($detail->journalEntry ? $detail->journalEntry->comment : '-') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Timeline Title --}}
    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-3 flex items-center gap-2">
        <i class="fas fa-history text-indigo-600"></i> Riwayat Perubahan Alokasi (Audit Trail)
    </h4>

    {{-- Timeline list --}}
    @if($logs->isEmpty())
        <div class="text-center py-8 text-slate-500 bg-slate-50 rounded-lg border border-dashed border-slate-200">
            <i class="fas fa-file-invoice text-3xl text-slate-300 mb-2"></i>
            <p class="text-xs">Belum ada riwayat penyesuaian untuk baris transaksi ini.</p>
            <p class="text-[11px] text-slate-400 mt-1">Transaksi ini masih memiliki alokasi orisinil tanpa adjustment tercatat.</p>
        </div>
    @else
        <div class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
            @foreach($logs as $log)
                <div class="relative">
                    {{-- Dot icon --}}
                    @php
                        $dotColor = 'bg-blue-500';
                        $actionLabel = 'Penyesuaian';
                        if ($log->action_type === 'unassign') {
                            $dotColor = 'bg-rose-500';
                            $actionLabel = 'Pelepasan (Unassign)';
                        } elseif ($log->action_type === 'initial_assign') {
                            $dotColor = 'bg-emerald-500';
                            $actionLabel = 'Penugasan Awal';
                        } else {
                            $actionLabel = 'Pengalihan (Reassign)';
                        }
                    @endphp
                    <span class="absolute -left-6 top-1.5 w-3 h-3 rounded-full {{ $dotColor }} ring-4 ring-white"></span>

                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs hover:bg-slate-100/60 transition-colors">
                        <div class="flex flex-wrap items-center justify-between gap-1 mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-800">{{ $actionLabel }}</span>
                                @if($log->is_override_closed)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                        <i class="fas fa-unlock-alt mr-0.5"></i> Override Periode Ditutup
                                    </span>
                                @endif
                            </div>
                            <span class="text-slate-400 text-[11px]">
                                {{ $log->created_at ? $log->created_at->format('d M Y, H:i:s') : '-' }}
                            </span>
                        </div>

                        {{-- Department transition --}}
                        <div class="flex items-center gap-2 my-2 py-1 px-2 bg-white rounded border border-slate-200 text-[11px]">
                            <span class="text-slate-600">
                                @if($log->oldDepartemen)
                                    <span class="font-semibold text-slate-800">{{ $log->oldDepartemen->kode }}</span> - {{ $log->oldDepartemen->deskripsi }}
                                @else
                                    <span class="italic text-slate-400">Tanpa Departemen (NULL)</span>
                                @endif
                            </span>
                            <i class="fas fa-arrow-right text-slate-400"></i>
                            <span class="text-slate-900 font-semibold">
                                @if($log->newDepartemen)
                                    <span class="text-indigo-600 font-bold">{{ $log->newDepartemen->kode }}</span> - {{ $log->newDepartemen->deskripsi }}
                                @else
                                    <span class="italic text-rose-600 font-medium">Dilepas ke NULL</span>
                                @endif
                            </span>
                        </div>

                        {{-- Reason --}}
                        <div class="text-slate-700 bg-amber-50/50 border-l-2 border-amber-400 px-2 py-1 my-1.5 rounded-r">
                            <span class="text-slate-500 font-semibold text-[10px] block">ALASAN:</span>
                            <span class="text-slate-800">{{ $log->reason }}</span>
                        </div>

                        {{-- Footer meta --}}
                        <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-100 mt-2">
                            <span><i class="fas fa-user mr-1"></i> Oleh: <strong>{{ $log->user ? $log->user->name : 'Sistem' }}</strong></span>
                            @if($log->ip_address)
                                <span class="font-mono text-[10px]">{{ $log->ip_address }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- resources/views/assigned_watchdog/audit_logs.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    .audit-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
        position: relative;
        overflow: hidden;
    }
    .audit-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
        pointer-events: none;
    }
    .table-audit th {
        font-size: 0.73rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        background-color: #1e293b;
        color: #f8fafc;
        border-bottom: 2px solid #334155;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-800 pb-24">

    {{-- Header Banner --}}
    <div class="audit-header text-white px-6 py-8 mb-6 shadow-lg">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">
                        <i class="fas fa-shield-alt mr-1"></i> AUDIT &amp; GOVERNANCE
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-500/30 text-blue-200 border border-blue-400/30">
                        <i class="fas fa-history mr-1"></i> AUDIT TRAIL LOGS
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white flex items-center gap-2">
                    <span>Audit Trail Penyesuaian Departemen</span>
                </h1>
                <p class="text-slate-300 text-sm mt-1">
                    Log terpusat aktivitas penyesuaian alokasi departemen, reassign, dan pelepasan akun (unassign).
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('assigned_watchdog.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold shadow-md transition-colors duration-150">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Control Center
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Filter Bar --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-6">
            <form method="GET" action="{{ route('assigned_watchdog.audit_logs') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                {{-- Date Start --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                           class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- Date End --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                           class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- Department --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Departemen</label>
                    <select name="departemen_id" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('departemen_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->kode }} - {{ $dept->deskripsi }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Type --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Jenis Aktivitas</label>
                    <select name="action_type" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Aktivitas</option>
                        <option value="reassign" {{ request('action_type') == 'reassign' ? 'selected' : '' }}>Reassign (Pengalihan)</option>
                        <option value="unassign" {{ request('action_type') == 'unassign' ? 'selected' : '' }}>Unassign (Pelepasan)</option>
                        <option value="initial_assign" {{ request('action_type') == 'initial_assign' ? 'selected' : '' }}>Initial Assign</option>
                    </select>
                </div>

                {{-- User --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Pengguna (User)</label>
                    <select name="user_id" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Pengguna</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Actions --}}
                <div class="sm:col-span-2 md:col-span-5 flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('assigned_watchdog.audit_logs') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                        Reset Filter
                    </a>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors">
                        <i class="fas fa-filter mr-1.5"></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600">
                    Menampilkan {{ $logs->total() }} Log Aktivitas
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse table-audit text-xs">
                    <thead>
                        <tr>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">User</th>
                            <th class="py-3 px-4">Aktivitas</th>
                            <th class="py-3 px-4">Voucher / Detail</th>
                            <th class="py-3 px-4">Akun COA</th>
                            <th class="py-3 px-4">Perubahan Departemen</th>
                            <th class="py-3 px-4">Alasan (Reason)</th>
                            <th class="py-3 px-4 text-center">Status Periode</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 whitespace-nowrap text-slate-600 font-mono text-[11px]">
                                    {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap font-medium text-slate-800">
                                    <div class="flex items-center gap-1.5">
                                        <i class="fas fa-user-circle text-slate-400"></i>
                                        <span>{{ $log->user ? $log->user->name : 'Sistem' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($log->action_type === 'unassign')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            <i class="fas fa-unlink mr-1"></i> Unassign
                                        </span>
                                    @elseif($log->action_type === 'initial_assign')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <i class="fas fa-plus mr-1"></i> Initial
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            <i class="fas fa-exchange-alt mr-1"></i> Reassign
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800">{{ $log->voucher ?: '-' }}</span>
                                    <span class="text-slate-400 text-[10px] block font-mono">ID Detail #{{ $log->journal_entry_detail_id }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-mono text-slate-700 font-semibold">{{ $log->kode_akun }}</span>
                                    @if($log->chartOfAccount)
                                        <span class="text-slate-500 text-[11px] block truncate max-w-[180px]">{{ $log->chartOfAccount->nama_akun }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-slate-600">
                                            {{ $log->oldDepartemen ? $log->oldDepartemen->kode : 'NULL' }}
                                        </span>
                                        <i class="fas fa-arrow-right text-slate-400 text-[10px]"></i>
                                        <span class="font-bold {{ $log->newDepartemen ? 'text-indigo-600' : 'text-rose-600' }}">
                                            {{ $log->newDepartemen ? $log->newDepartemen->kode : 'NULL' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-slate-700 block max-w-xs">{{ $log->reason }}</span>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if($log->is_override_closed)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300" title="Override dilakukan pada periode yang sudah ditutup">
                                            <i class="fas fa-unlock mr-1"></i> Override Closed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            Normal (Open)
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-500">
                                    <i class="fas fa-inbox text-4xl text-slate-300 mb-2"></i>
                                    <p class="text-sm font-semibold">Tidak ada data log yang sesuai dengan filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

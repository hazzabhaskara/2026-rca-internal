@extends('layouts.app')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-full mx-auto space-y-6">

    <!-- ========================================================================= -->
    <!-- SAGE 50 HEADER & ACTION RIBBON                                            -->
    <!-- ========================================================================= -->
    <div class="bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-slate-800 text-white">
        <!-- Top Accent Strip -->
        <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 via-teal-400 to-indigo-500"></div>

        <div class="p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white shadow-lg shadow-emerald-900/30">
                    <i class="fa-solid fa-money-bill-transfer text-xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-xl font-bold tracking-tight text-white">Payment Expenses (Pengeluaran Kas)</h1>
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-widest bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            Sage 50 Payments Register
                        </span>
                    </div>
                    <p class="text-xs text-slate-300 mt-1">
                        Pencatatan pengeluaran kas & bank divisi dengan cetak Bon / Voucher Pengeluaran resmi dan upload dokumen bukti fisik.
                    </p>
                </div>
            </div>

            <!-- Sage Action Buttons -->
            <div class="flex items-center gap-2 flex-wrap">
                @can('payment_expense.create')
                    <a href="{{ route('payment_expense.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-xs shadow-md shadow-emerald-500/20 active:scale-95 transition-all">
                        <i class="fa-solid fa-plus"></i>
                        <span>+ Input Pengeluaran Baru</span>
                    </a>
                @endcan

                <button type="button" onclick="document.getElementById('filter-panel').classList.toggle('hidden')"
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-xs transition-all">
                    <i class="fa-solid fa-filter text-emerald-400"></i>
                    <span>Filter & Pencarian</span>
                </button>
            </div>
        </div>

        <!-- Mode Access Banner -->
        <div class="px-5 sm:px-6 py-2.5 bg-slate-800/80 border-t border-slate-800 flex items-center justify-between text-xs text-slate-300">
            <div class="flex items-center gap-2">
                @if ($isAllAccess)
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-emerald-300 font-medium">Mode Administrator / Finance:</span>
                    <span>Menampilkan seluruh pengeluaran kas dari semua divisi dan pengguna.</span>
                @else
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                    <span class="text-blue-300 font-medium">Mode Divisi Pribadi:</span>
                    <span>Hanya menampilkan pengeluaran kas yang Anda ajukan/input sendiri.</span>
                @endif
            </div>
            <div class="font-mono text-[11px] text-slate-400 hidden sm:block">
                Total: {{ $data->total() }} Transaksi Terdaftar
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SAGE 50 FILTER PANEL                                                      -->
    <!-- ========================================================================= -->
    <div id="filter-panel" class="{{ request()->anyFilled(['start_date', 'end_date', 'filter_departemen_id', 'filter_category', 'filter_user_id', 'search']) ? '' : 'hidden' }} bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('payment_expense.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3.5">
            <!-- Tanggal Mulai -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <!-- Tanggal Akhir -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <!-- Departemen -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">Divisi / Departemen</label>
                <select name="filter_departemen_id" class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Divisi</option>
                    @foreach ($departements as $dept)
                        <option value="{{ $dept->id }}" {{ request('filter_departemen_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->deskripsi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Kategori Source -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">Kategori Dana</label>
                <select name="filter_category" class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Kategori</option>
                    @foreach ($categorySources as $cat)
                        <option value="{{ $cat }}" {{ request('filter_category') == $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Pemohon (khusus Admin / Finance) -->
            @if ($isAllAccess)
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">User Pemohon</label>
                    <select name="filter_user_id" class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500">
                        <option value="">Semua Pengguna</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ request('filter_user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Cari Kata Kunci -->
            <div class="{{ $isAllAccess ? 'col-span-1' : 'col-span-2' }}">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">Cari Voucher / Ket.</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="No. Voucher / Keterangan..."
                        class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
                </div>
            </div>

            <!-- Tombol Filter -->
            <div class="sm:col-span-2 md:col-span-4 lg:col-span-6 flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                <a href="{{ route('payment_expense.index') }}"
                    class="px-3.5 py-1.5 text-xs rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 font-semibold transition-all">
                    Reset Filter
                </a>
                <button type="submit"
                    class="px-4 py-1.5 text-xs rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800 shadow-sm transition-all">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- SAGE 50 TRANSACTION REGISTER (TABEL DAFTAR PENGELUARAN)                   -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[10px] border-b border-gray-200">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4 w-36">No. Voucher (Bon)</th>
                        <th class="py-3.5 px-3 w-28">Tanggal</th>
                        <th class="py-3.5 px-4 w-36">Divisi / Dept</th>
                        <th class="py-3.5 px-4 w-36">Kategori Dana</th>
                        <th class="py-3.5 px-4">Dari Kas / Bank</th>
                        <th class="py-3.5 px-4">Keterangan / Memo</th>
                        <th class="py-3.5 px-4 text-right w-36">Total Pengeluaran</th>
                        <th class="py-3.5 px-3 text-center w-20">Dokumen</th>
                        <th class="py-3.5 px-4 w-28">Pemohon</th>
                        <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($data as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Nomor Urut -->
                            <td class="py-3.5 px-4 text-center text-gray-400 font-mono font-medium">
                                {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                            </td>

                            <!-- No. Voucher / Bon -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-bold text-slate-900 flex items-center gap-1.5">
                                    <span class="text-emerald-600">●</span>
                                    <span>{{ $item->voucher_number ?: 'EXP#' . $item->id }}</span>
                                </div>
                                <span class="text-[10px] text-gray-400 font-mono">ID #{{ $item->id }}</span>
                            </td>

                            <!-- Tanggal -->
                            <td class="py-3.5 px-3 font-mono font-medium text-gray-600">
                                {{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}
                            </td>

                            <!-- Divisi / Departemen -->
                            <td class="py-3.5 px-4">
                                @if ($item->departemen)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="fa-solid fa-building text-[9px]"></i>
                                        <span>{{ $item->departemen->deskripsi }}</span>
                                    </span>
                                @else
                                    <span class="text-gray-400 italic">Pusat / General</span>
                                @endif
                            </td>

                            <!-- Kategori Sumber Dana -->
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fa-solid fa-wallet text-[9px]"></i>
                                    <span>{{ $item->category_source ?: $item->source }}</span>
                                </span>
                            </td>

                            <!-- Dari Kas/Bank -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800">
                                    {{ $item->Account->nama_akun ?? '-' }}
                                </div>
                                <span class="text-[10px] font-mono text-gray-400">
                                    {{ $item->Account->kode_akun ?? '' }}
                                </span>
                            </td>

                            <!-- Keterangan / Memo -->
                            <td class="py-3.5 px-4 max-w-xs truncate" title="{{ $item->notes }}">
                                <span class="text-gray-800">{{ $item->notes ?: '-' }}</span>
                                @if ($item->Vendor)
                                    <div class="text-[10px] text-gray-400 mt-0.5">
                                        Vendor: {{ $item->Vendor->nama_vendors }}
                                    </div>
                                @endif
                            </td>

                            <!-- Total Pengeluaran -->
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($item->grand_total, 0, ',', '.') }}
                            </td>

                            <!-- Lampiran Dokumen -->
                            <td class="py-3.5 px-3 text-center">
                                @if ($item->attachment)
                                    <a href="{{ $item->attachment_url }}" target="_blank"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 transition-colors"
                                        title="Buka Dokumen Bukti (Bon / Struk / Nota)">
                                        <i class="fa-solid fa-paperclip text-xs"></i>
                                    </a>
                                @else
                                    <span class="text-gray-300 font-bold" title="Tidak ada lampiran dokumen">—</span>
                                @endif
                            </td>

                            <!-- User Pemohon -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-5 h-5 rounded-full bg-slate-100 border border-gray-200 text-slate-700 font-bold text-[9px] flex items-center justify-center">
                                        {{ strtoupper(substr($item->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-slate-700 text-xs">{{ $item->user->name ?? 'Sistem' }}</span>
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center justify-center gap-1">
                                    <!-- Cetak Bon / Voucher (Sage 50 Style) -->
                                    <a href="{{ route('payment_expense.print_voucher', $item->id) }}" target="_blank"
                                        class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-all"
                                        title="Cetak Bon / Bukti Kas Keluar">
                                        <i class="fa-solid fa-print text-xs"></i>
                                    </a>

                                    <!-- Detail -->
                                    @can('payment_expense.view')
                                        <a href="{{ route('payment_expense.show', $item->id) }}"
                                            class="w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 flex items-center justify-center transition-all"
                                            title="Lihat Detail Voucher">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
                                    @endcan

                                    <!-- Edit -->
                                    @can('payment_expense.update')
                                        <a href="{{ route('payment_expense.edit', $item->id) }}"
                                            class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-600 flex items-center justify-center transition-all"
                                            title="Edit Pengeluaran">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </a>
                                    @endcan

                                    <!-- Delete -->
                                    @can('payment_expense.delete')
                                        <button type="button" onclick="confirmDeleteExpense({{ $item->id }}, '{{ $item->voucher_number ?: 'EXP#' . $item->id }}')"
                                            class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition-all"
                                            title="Hapus Pengeluaran">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                        <form id="delete-expense-form-{{ $item->id }}"
                                            action="{{ route('payment_expense.destroy', $item->id) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-14 px-4 text-center text-gray-400">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mb-3">
                                        <i class="fa-solid fa-receipt"></i>
                                    </div>
                                    <p class="font-bold text-gray-700 text-sm">Belum Ada Transaksi Pengeluaran Kas</p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        Klik tombol "Input Pengeluaran Baru" di atas untuk mencatat pengeluaran kas divisi dan mencetak bon resmi.
                                    </p>
                                    @can('payment_expense.create')
                                        <a href="{{ route('payment_expense.create') }}"
                                            class="mt-4 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition-all">
                                            <i class="fa-solid fa-plus mr-1"></i> Mulai Input Pengeluaran
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($data->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $data->links() }}
            </div>
        @endif

    </div>

</div>

<!-- Delete Confirmation Alert -->
<script>
function confirmDeleteExpense(id, voucherNo) {
    Swal.fire({
        title: 'Hapus Pengeluaran ' + voucherNo + '?',
        text: 'Jurnal akuntansi terkait dan rincian pengeluaran akan otomatis dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-expense-form-' + id).submit();
        }
    });
}
</script>
@endsection

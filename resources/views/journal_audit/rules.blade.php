{{-- resources/views/journal_audit/rules.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
    .rules-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-800 pb-24">
    {{-- Header Banner --}}
    <div class="rules-header text-white px-6 py-8 mb-6 shadow-lg">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <a href="{{ route('journal_audit.index') }}" class="text-indigo-300 hover:text-white text-xs font-semibold">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Control Center Audit
                    </a>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white flex items-center gap-2">
                    <span>Kamus Indikator Akun Jurnal (COA Rules)</span>
                </h1>
                <p class="text-slate-300 text-sm mt-1">
                    Kelola aturan kata kunci, pola narasi transaksi, dan ambang batas (threshold) untuk deteksi otomatis.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="openAddRuleModal()"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold shadow-md transition">
                    <i class="fas fa-plus mr-1.5"></i> Tambah Aturan Baru
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Alerts --}}
        @if(session('success'))
            <div class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center">
                <i class="fas fa-check-circle text-emerald-500 mr-2"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Rules Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Daftar Aturan Kamus ({{ $rules->count() }})</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-800 text-white text-[11px] uppercase tracking-wider">
                            <th class="py-3 px-3 w-12 text-center">No</th>
                            <th class="py-3 px-3">Kategori</th>
                            <th class="py-3 px-4">Nama Indikator</th>
                            <th class="py-3 px-4">Pola Kata Kunci (Keywords)</th>
                            <th class="py-3 px-3">Target Akun (COA)</th>
                            <th class="py-3 px-3 text-center">Kepastian</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-center w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($rules as $idx => $r)
                            @php
                                $badgeColor = $r->tingkat_kepastian === 'PASTI' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800';
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-3 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                                <td class="py-3 px-3 font-semibold text-slate-700">
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $r->kategori }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    {{ $r->nama_indikator }}
                                    @if($r->penjelasan_aturan)
                                        <div class="text-[11px] text-slate-400 font-normal mt-0.5">{{ $r->penjelasan_aturan }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-indigo-700 max-w-xs break-words">
                                    {{ $r->keyword_pattern }}
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <div class="font-mono font-bold text-indigo-900">{{ $r->target_kode_akun }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $r->target_nama_akun }}</div>
                                    @if($r->alternate_kode_akuns)
                                        <div class="text-[10px] text-slate-400 mt-0.5">Alt: {{ implode(', ', $r->alternate_kode_akuns) }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $badgeColor }}">
                                        {{ $r->tingkat_kepastian }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    @if($r->is_active)
                                        <span class="text-emerald-600 font-bold text-[11px]"><i class="fas fa-check-circle mr-1"></i> Aktif</span>
                                    @else
                                        <span class="text-slate-400 font-semibold text-[11px]">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <form action="{{ route('journal_audit.rules.destroy', $r->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus aturan ini dari kamus?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 transition" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH ATURAN --}}
<div id="modalAddRule" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full border border-slate-200 overflow-hidden">
        <div class="bg-indigo-900 px-6 py-4 text-white flex justify-between items-center">
            <h3 class="font-bold text-base flex items-center gap-2">
                <i class="fas fa-plus-circle text-indigo-400"></i> Tambah Aturan Kamus Baru
            </h3>
            <button onclick="closeAddRuleModal()" class="text-slate-300 hover:text-white">&times;</button>
        </div>
        <form action="{{ route('journal_audit.rules.store') }}" method="POST" class="p-6 space-y-3.5 text-xs">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kategori</label>
                    <input type="text" name="kategori" required placeholder="Contoh: UTILITAS, ASET"
                        class="w-full rounded-lg border-slate-300 py-1.5 px-2.5 uppercase">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tingkat Kepastian</label>
                    <select name="tingkat_kepastian" class="w-full rounded-lg border-slate-300 py-1.5 px-2.5">
                        <option value="PASTI">PASTI (Salah jika melanggar)</option>
                        <option value="AMBIGU_REVIEW">AMBIGU_REVIEW (Zona Pertimbangan)</option>
                        <option value="KONDISIONAL">KONDISIONAL</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Indikator</label>
                <input type="text" name="nama_indikator" required placeholder="Contoh: Biaya Pembelian Perlengkapan Kebersihan"
                    class="w-full rounded-lg border-slate-300 py-1.5 px-2.5">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Kata Kunci Pattern (Pisahkan koma)</label>
                <textarea name="keyword_pattern" required rows="2" placeholder="Contoh: sabun,sapu,pel,pembersih lantai"
                    class="w-full rounded-lg border-slate-300 py-1.5 px-2.5 font-mono"></textarea>
                <div class="text-[10px] text-slate-400 mt-0.5">Sistem akan memeriksa jika narasi komentar mengandung salah satu kata ini.</div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Match Type</label>
                    <select name="match_type" class="w-full rounded-lg border-slate-300 py-1.5 px-2.5">
                        <option value="contains">Contains (Sebagian)</option>
                        <option value="exact">Exact (Persis)</option>
                        <option value="regex">Regex</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Arah Mutasi</label>
                    <select name="arah_mutasi" class="w-full rounded-lg border-slate-300 py-1.5 px-2.5">
                        <option value="debit">Debit Saja</option>
                        <option value="kredit">Kredit Saja</option>
                        <option value="any">Semua (Any)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Prefix Sumber</label>
                    <input type="text" name="sumber_dana_prefix" placeholder="Semua"
                        class="w-full rounded-lg border-slate-300 py-1.5 px-2.5 uppercase">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Target Akun COA yang Seharusnya</label>
                <select name="target_kode_akun" required class="w-full rounded-lg border-slate-300 py-1.5 px-2.5">
                    @foreach($coas as $c)
                        <option value="{{ $c->kode_akun }}">{{ $c->kode_akun }} - {{ $c->nama_akun }} ({{ $c->tipe_akun }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Penjelasan Alasan Akuntansi</label>
                <input type="text" name="penjelasan_aturan" placeholder="Alasan kenapa narasi ini harus masuk ke akun target..."
                    class="w-full rounded-lg border-slate-300 py-1.5 px-2.5">
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeAddRuleModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">Batal</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg shadow transition">
                    Simpan Aturan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAddRuleModal() {
        document.getElementById('modalAddRule').classList.remove('hidden');
    }
    function closeAddRuleModal() {
        document.getElementById('modalAddRule').classList.add('hidden');
    }
</script>
@endpush
@endsection

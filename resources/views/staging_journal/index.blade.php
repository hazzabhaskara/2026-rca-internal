@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Staging Journal Ingestion (Inbox Error)</h4>
                    <div>
                        <form action="{{ route('staging_journal.reprocess') }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Proses All-or-Nothing: jika ada 1 error, SEMUA data ditolak. Lanjutkan?')">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                ⚙️ Proses Ulang Data Pending
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">

                    {{-- ══ FLASH MESSAGES ════════════════════════════════════════════════ --}}

                    @if(session('info'))
                        <div class="alert alert-info">ℹ️ {{ session('info') }}</div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">✅ {{ session('success') }}</div>
                    @endif

                    @if(session('danger'))
                        <div class="alert alert-danger">❌ {{ session('danger') }}</div>
                    @endif

                    {{-- ══ ERROR SUMMARY (All-or-Nothing Rejection Report) ══════════════ --}}

                    @if(session('validation_errors'))
                        @php $summary = session('error_summary'); @endphp
                        <div class="alert alert-danger" style="border-left: 5px solid #dc3545;">
                            <h5 class="alert-heading">
                                🚫 Import Ditolak — All-or-Nothing
                            </h5>
                            <p class="mb-1">{{ session('error_message_text') }}</p>

                            @if($summary)
                            <div class="row text-center my-3">
                                <div class="col-3">
                                    <div class="p-2 bg-white rounded border">
                                        <div class="h4 mb-0 text-dark">{{ number_format($summary['total_rows']) }}</div>
                                        <small class="text-muted">Total Baris</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="p-2 bg-white rounded border">
                                        <div class="h4 mb-0 text-dark">{{ $summary['total_vouchers'] }}</div>
                                        <small class="text-muted">Total Voucher</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="p-2 bg-white rounded border">
                                        <div class="h4 mb-0 text-danger">{{ $summary['error_vouchers'] }}</div>
                                        <small class="text-muted">Voucher Error</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="p-2 bg-white rounded border">
                                        <div class="h4 mb-0 text-success">{{ $summary['ok_vouchers'] }}</div>
                                        <small class="text-muted">Voucher OK</small>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <details open>
                                <summary style="cursor:pointer; font-weight:600;">
                                    📋 Detail Error per Voucher ({{ count(session('validation_errors')) }} voucher bermasalah)
                                </summary>
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered table-striped mb-0">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Source</th>
                                                <th>Tanggal</th>
                                                <th>Jml Baris</th>
                                                <th>Penyebab Error</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach(session('validation_errors') as $i => $err)
                                            <tr class="table-danger">
                                                <td>{{ $i + 1 }}</td>
                                                <td><code>{{ $err['source'] }}</code></td>
                                                <td>{{ $err['tanggal'] }}</td>
                                                <td class="text-center">{{ $err['rows'] }}</td>
                                                <td><small>{{ $err['message'] }}</small></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <p class="mt-2 mb-0 text-muted">
                                    <small>
                                        💡 <strong>Langkah selanjutnya:</strong>
                                        Perbaiki data di tab "Failed (Error)" menggunakan dropdown di bawah,
                                        atau perbaiki file Excel dan upload ulang.
                                        Setelah diperbaiki, klik <em>"Proses Ulang Data Pending"</em>.
                                    </small>
                                </p>
                            </details>
                        </div>
                    @endif

                    {{-- ══ STATUS TABS ════════════════════════════════════════════════════ --}}

                    <div class="mb-3">
                        <a href="{{ route('staging_journal.index', ['status' => 'failed']) }}"
                           class="btn btn-sm {{ $status == 'failed' ? 'btn-danger' : 'btn-outline-danger' }}">
                            ❌ Failed (Error)
                        </a>
                        <a href="{{ route('staging_journal.index', ['status' => 'pending']) }}"
                           class="btn btn-sm {{ $status == 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                            ⏳ Pending
                        </a>
                        <a href="{{ route('staging_journal.index', ['status' => 'success']) }}"
                           class="btn btn-sm {{ $status == 'success' ? 'btn-success' : 'btn-outline-success' }}">
                            ✅ Success
                        </a>
                    </div>

                    {{-- ══ PANDUAN EDIT (untuk tab failed & pending) ══════════════════════ --}}

                    @if(in_array($status, ['failed', 'pending']) && $entries->count() > 0)
                    <div class="alert alert-info py-2 mb-3">
                        <small>
                            💡 <strong>Cara perbaiki:</strong>
                            Ubah <strong>Kode Akun</strong> dan/atau <strong>Departemen</strong> di kolom dropdown.
                            Setelah diubah, baris akan otomatis kembali ke status <strong>Pending</strong>.
                            Setelah semua diperbaiki, klik <strong>"Proses Ulang Data Pending"</strong>.
                        </small>
                    </div>
                    @endif

                    {{-- ══ DATA TABLE ═════════════════════════════════════════════════════ --}}

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Source</th>
                                    <th>Tanggal</th>
                                    <th>Debit</th>
                                    <th>Kredit</th>
                                    <th>Kode Akun</th>
                                    <th>Departemen</th>
                                    <th>Error Message</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entries as $entry)
                                <tr>
                                    <td><code style="font-size:0.8em">{{ $entry->source }}</code></td>
                                    <td>{{ $entry->tanggal }}</td>
                                    <td class="text-right">
                                        {{ $entry->debit > 0 ? number_format((float)str_replace(',', '', $entry->debit), 0, '.', '.') : '-' }}
                                    </td>
                                    <td class="text-right">
                                        {{ $entry->kredit > 0 ? number_format((float)str_replace(',', '', $entry->kredit), 0, '.', '.') : '-' }}
                                    </td>

                                    {{-- ── Kolom Kode Akun ─────────────────────────────── --}}
                                    <td>
                                        @if(in_array($status, ['failed', 'pending']))
                                            <select class="form-control form-control-sm coa-select"
                                                    id="coa-{{ $entry->id }}"
                                                    name="kode_akun_{{ $entry->id }}"
                                                    data-id="{{ $entry->id }}"
                                                    style="min-width:220px;">
                                                <option value="">-- Pilih Akun --</option>
                                                @foreach($coas as $coa)
                                                    <option value="{{ $coa->kode_akun }}"
                                                        {{ $entry->kode_akun == $coa->kode_akun ? 'selected' : '' }}>
                                                        {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <small>{{ $entry->kode_akun }}</small>
                                        @endif
                                    </td>

                                    {{-- ── Kolom Departemen ────────────────────────────── --}}
                                    <td>
                                        @if(in_array($status, ['failed', 'pending']))
                                            <select class="form-control form-control-sm dept-select"
                                                    id="dept-{{ $entry->id }}"
                                                    name="departemen_{{ $entry->id }}"
                                                    data-id="{{ $entry->id }}"
                                                    style="min-width:160px;">
                                                <option value="">-- Opsional --</option>
                                                @foreach($departements as $dept)
                                                    <option value="{{ $dept->kode }}"
                                                        {{ $entry->departemen == $dept->kode ? 'selected' : '' }}>
                                                        {{ $dept->kode }} - {{ $dept->deskripsi }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <small>{{ $entry->departemen ?? '-' }}</small>
                                        @endif
                                    </td>

                                    {{-- ── Error Message ───────────────────────────────── --}}
                                    <td class="text-danger" style="max-width:300px;">
                                        <small>{{ $entry->error_message }}</small>
                                    </td>

                                    {{-- ── Status Badge ────────────────────────────────── --}}
                                    <td class="text-center">
                                        <span class="badge badge-{{ $entry->status == 'success' ? 'success' : ($entry->status == 'failed' ? 'danger' : 'warning') }}">
                                            {{ strtoupper($entry->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Tidak ada data untuk status <strong>{{ $status }}</strong>.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $entries->appends(request()->query())->links() }}

                </div>{{-- card-body --}}
            </div>{{-- card --}}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {

    // ── Fungsi update ke server ─────────────────────────────────────────────
    function updateEntry(id, data) {
        $.ajax({
            url: '/staging_journal/' + id + '/update',
            type: 'POST',
            data: Object.assign({ _token: '{{ csrf_token() }}' }, data),
            success: function (res) {
                if (res.success) {
                    // Ganti badge status di baris yang sama
                    var $badge = $('#coa-' + id).closest('tr').find('.badge');
                    $badge.removeClass('badge-danger badge-warning badge-success')
                          .addClass('badge-warning')
                          .text('PENDING');
                    // Hapus error message di baris yang sama
                    $('#coa-' + id).closest('tr').find('.text-danger small').text('');
                }
            },
            error: function () {
                alert('Gagal menyimpan perubahan. Silakan coba lagi.');
            }
        });
    }

    // ── Listener: dropdown Kode Akun berubah ───────────────────────────────
    $('.coa-select').on('change', function () {
        var id       = $(this).data('id');
        var kodeAkun = $(this).val();
        if (!kodeAkun) return;
        updateEntry(id, { kode_akun: kodeAkun });
    });

    // ── Listener: dropdown Departemen berubah ──────────────────────────────
    $('.dept-select').on('change', function () {
        var id   = $(this).data('id');
        var dept = $(this).val(); // bisa kosong string (opsional)
        updateEntry(id, { departemen: dept });
    });

});
</script>
@endsection

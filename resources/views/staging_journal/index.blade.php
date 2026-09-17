@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Staging Journal Ingestion (Inbox Error)</h4>
                    <div>
                        <form action="{{ route('staging_journal.reprocess') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">Proses Ulang Data Pending</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="mb-3">
                        <a href="{{ route('staging_journal.index', ['status' => 'failed']) }}" class="btn btn-sm {{ $status == 'failed' ? 'btn-danger' : 'btn-outline-danger' }}">Failed (Error)</a>
                        <a href="{{ route('staging_journal.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status == 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">Pending</a>
                        <a href="{{ route('staging_journal.index', ['status' => 'success']) }}" class="btn btn-sm {{ $status == 'success' ? 'btn-success' : 'btn-outline-success' }}">Success</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Source</th>
                                    <th>Tanggal</th>
                                    <th>Debit</th>
                                    <th>Kredit</th>
                                    <th>Kode Akun (Bisa diubah)</th>
                                    <th>Error Message</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entries as $entry)
                                <tr>
                                    <td>{{ $entry->source }}</td>
                                    <td>{{ $entry->tanggal }}</td>
                                    <td>{{ number_format((float) str_replace(',', '', $entry->debit), 2) }}</td>
                                    <td>{{ number_format((float) str_replace(',', '', $entry->kredit), 2) }}</td>
                                    <td class="{{ $status == 'failed' ? 'bg-light-danger' : '' }}">
                                        @if($status == 'failed' || $status == 'pending')
                                        <select class="form-control select2 coa-select" data-id="{{ $entry->id }}">
                                            <option value="">-- Pilih Akun --</option>
                                            @foreach($coas as $coa)
                                                <option value="{{ $coa->kode_akun }}" {{ $entry->kode_akun == $coa->kode_akun ? 'selected' : '' }}>
                                                    {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @else
                                        {{ $entry->kode_akun }} - {{ $entry->nama_akun }}
                                        @endif
                                    </td>
                                    <td class="text-danger"><small>{{ $entry->error_message }}</small></td>
                                    <td>
                                        <span class="badge badge-{{ $entry->status == 'success' ? 'success' : ($entry->status == 'failed' ? 'danger' : 'warning') }}">
                                            {{ strtoupper($entry->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data untuk status ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $entries->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.coa-select').on('change', function() {
            let id = $(this).data('id');
            let kode_akun = $(this).val();

            $.ajax({
                url: `/staging_journal/${id}/update`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    kode_akun: kode_akun
                },
                success: function(res) {
                    if(res.success) {
                        alert(res.message); // Atau ganti dengan toastr/sweetalert
                        location.reload();
                    }
                }
            });
        });
    });
</script>
@endsection

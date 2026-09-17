@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">

                {{-- Tampilkan error validasi --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('taxes.update', $tax->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                        {{-- Pilih Bulan --}}
                        <div class="mb-3">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select name="bulan" id="bulan" class="form-control select-bulan" required>
                                <option value="">-- Pilih Bulan --</option>
                                @foreach ([
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ] as $num => $nama)
                                    <option value="{{ $num }}" {{ $tax->bulan == $num ? 'selected' : '' }}>
                                        {{ $nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Pilih Tahun --}}
                        <div class="mb-3">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select name="tahun" id="tahun" class="form-control select-tahun" required>
                                <option value="">-- Pilih Tahun --</option>
                                @for ($year = date('Y'); $year >= 2000; $year--)
                                    <option value="{{ $year }}" {{ $tax->tahun == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        {{-- Jenis Pajak --}}
                        <div>
                            <label for="active" class="block text-sm font-medium text-gray-700 mb-1">Jenis pajak</label>
                            <select name="jenis_pajak" id="jenis_pajak"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="PPN"
                                    {{ old('jenis_pajak', $tax->jenis_pajak ?? '') == 'PPN' ? 'selected' : '' }}>
                                    PPN
                                </option>
                                <option value="PPH 21"
                                    {{ old('jenis_pajak', $tax->jenis_pajak ?? '') == 'PPH 21' ? 'selected' : '' }}>
                                    PPH 21
                                </option>
                                <option value="PPH 22"
                                    {{ old('jenis_pajak', $tax->jenis_pajak ?? '') == 'PPH 22' ? 'selected' : '' }}>
                                    PPH 22
                                </option>
                                <option value="PPH 23"
                                    {{ old('jenis_pajak', $tax->jenis_pajak ?? '') == 'PPH 23' ? 'selected' : '' }}>
                                    PPH 23
                                </option>
                                <option value="PPH FINAL"
                                    {{ old('jenis_pajak', $tax->jenis_pajak ?? '') == 'PPH FINAL' ? 'selected' : '' }}>
                                    PPH FINAL
                                </option>
                            </select>
                        </div>

                        {{-- Jenis Dokumen --}}
                        <div class="mb-3">
                            <label for="jenis_dokumen" class="form-label">Jenis Dokumen</label>
                            <input type="text" name="jenis_dokumen" id="jenis_dokumen" class="form-control"
                                value="{{ old('jenis_dokumen', $tax->jenis_dokumen) }}" required>
                        </div>

                        {{-- Upload File --}}
                        <div class="mb-3">
                            <label for="file_path" class="form-label">Upload File (kosongkan jika tidak diganti)</label>
                            <input type="file" name="file_path" id="file_path" class="form-control">

                            @if ($tax->file_path)
                                <small>File lama: <a href="{{ asset('storage/' . $tax->file_path) }}" target="_blank">Lihat
                                        File</a></small>
                            @endif
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('taxes.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
@endsection

{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

{{-- Select2 JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select-bulan').select2({
            placeholder: "Cari bulan...",
            allowClear: true,
            width: '100%',
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('.select-tahun').select2({
            placeholder: "Cari tahun...",
            allowClear: true,
            width: '100%',
        });
    });
</script>

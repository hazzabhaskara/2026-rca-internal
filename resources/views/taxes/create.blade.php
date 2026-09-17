@extends('layouts.app')

@section('content')
    <div class="py-10 max-w-full mx-auto px-6">
        <h2 class="font-bold text-2xl">Tambah Data Pajak</h2>

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

        <form action="{{ route('taxes.store') }}" method="POST" enctype="multipart/form-data"
            class="bg-white p-6 rounded-xl shadow-sm space-y-6">
            @csrf

            {{-- Pilih Bulan --}}

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

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
                            <option value="{{ $num }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Pilih Tahun --}}
                <div class="mb-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <select name="tahun" id="tahun" class="select-tahun form-control" required>
                        <option value="">-- Pilih Tahun --</option>
                        @for ($year = date('Y'); $year >= 2000; $year--)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>


                <div class="mb-3">
                    <label for="jenis_pajak" class="form-label">Jenis Pajak</label>
                    <select name="jenis_pajak" id="jenis_pajak" class="form-control" required>
                        <option value="">--Pilih--</option>
                        <option value="PPN">PPN</option>
                        <option value="PPH 21">PPH 21</option>
                        <option value="PPH 22">PPH 22</option>
                        <option value="PPH 23">PPH 23</option>
                        <option value="PPH FINAL">PPH FINAL</option>
                    </select>
                </div>

                {{-- Jenis Dokumen --}}
                <div class="mb-3">
                    <label for="jenis_dokumen" class="form-label">Jenis Dokumen</label>
                    <input type="text" name="jenis_dokumen" id="jenis_dokumen" class="form-control"
                        placeholder="contoh (jasa)" required>
                </div>

                {{-- Upload File --}}
                <div class="mb-3">
                    <label for="file_path" class="form-label">Upload File</label>
                    <input type="file" name="file_path" id="file_path" class="form-control" required>
                </div>

            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('taxes.index') }}" class="btn btn-secondary">Batal</a>
        </form>
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

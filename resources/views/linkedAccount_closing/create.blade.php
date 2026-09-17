@extends('layouts.app')
@section('content')
    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
                <form action="{{ route('linkedAccount_closing.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">

                        <div>
                            <label for="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
                            <select name="unit_kerja_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500""
                                id="unit_kerja">
                                <option>--Pilih---</option>
                                @foreach ($unitKerja as $unit)
                                    <option value="{{ $unit->id }}">
                                        {{ $unit->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Piutang Qris</label>
                            <input type="hidden" name="kode[]" value="Piutang Qris">
                            <select name="akun_id[]"
                                class="select2-account w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="">--Pilih--</option>

                                @foreach ($akun as $acc)
                                    @if ($acc->departemenAkun->count() > 0)
                                        @foreach ($acc->departemenAkun as $depAkun)
                                            <option value="{{ $acc->id }}|{{ $depAkun->departemen->id }}">
                                                {{ $acc->kode_akun }} - {{ $acc->nama_akun }} -
                                                {{ $depAkun->departemen->deskripsi ?? '-' }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="{{ $acc->id }}|0">
                                            {{ $acc->kode_akun }} - {{ $acc->nama_akun }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>

                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kas Omset</label>
                            <input type="hidden" name="kode[]" value="Kas Omset">
                            <select name="akun_id[]"
                                class="select2-account w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="">--Pilih--</option>

                                @foreach ($akun as $acc)
                                    @if ($acc->departemenAkun->count() > 0)
                                        @foreach ($acc->departemenAkun as $depAkun)
                                            <option value="{{ $acc->id }}|{{ $depAkun->departemen->id }}">
                                                {{ $acc->kode_akun }} - {{ $acc->nama_akun }} -
                                                {{ $depAkun->departemen->deskripsi ?? '-' }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="{{ $acc->id }}|0">
                                            {{ $acc->kode_akun }} - {{ $acc->nama_akun }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>

                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Titipan Omset
                                Merchandise</label>
                            <input type="hidden" name="kode[]" value="Titipan Omset Merchandise">
                            <select name="akun_id[]"
                                class="select2-account w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="">--Pilih--</option>

                                @foreach ($akun as $acc)
                                    @if ($acc->departemenAkun->count() > 0)
                                        @foreach ($acc->departemenAkun as $depAkun)
                                            <option value="{{ $acc->id }}|{{ $depAkun->departemen->id }}">
                                                {{ $acc->kode_akun }} - {{ $acc->nama_akun }} -
                                                {{ $depAkun->departemen->deskripsi ?? '-' }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="{{ $acc->id }}|0">
                                            {{ $acc->kode_akun }} - {{ $acc->nama_akun }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>

                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Beban Bagi Hasil(Sewa)
                            </label>
                            <input type="hidden" name="kode[]" value="Beban Bagi Hasil(Sewa)">
                            <select name="akun_id[]"
                                class="select2-account w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="">--Pilih--</option>

                                @foreach ($akun as $acc)
                                    @if ($acc->departemenAkun->count() > 0)
                                        @foreach ($acc->departemenAkun as $depAkun)
                                            <option value="{{ $acc->id }}|{{ $depAkun->departemen->id }}">
                                                {{ $acc->kode_akun }} - {{ $acc->nama_akun }} -
                                                {{ $depAkun->departemen->deskripsi ?? '-' }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="{{ $acc->id }}|0">
                                            {{ $acc->kode_akun }} - {{ $acc->nama_akun }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>

                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pendapatan Non Operasional
                                Lainnya
                            </label>
                            <input type="hidden" name="kode[]" value="Pendapatan Non Operasional">
                            <select name="akun_id[]"
                                class="select2-account w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="">--Pilih--</option>

                                @foreach ($akun as $acc)
                                    @if ($acc->departemenAkun->count() > 0)
                                        @foreach ($acc->departemenAkun as $depAkun)
                                            <option value="{{ $acc->id }}|{{ $depAkun->departemen->id }}">
                                                {{ $acc->kode_akun }} - {{ $acc->nama_akun }} -
                                                {{ $depAkun->departemen->deskripsi ?? '-' }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="{{ $acc->id }}|0">
                                            {{ $acc->kode_akun }} - {{ $acc->nama_akun }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>


                        </div>
                    </div>
                    <div class="flex justify-end mt-6">
                        <a href="{{ route('linkedAccount_closing.index') }}"
                            class="px-6 py-2 mr-3 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Kembali
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- Select2 JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2-account').select2({
                width: '100%',
                placeholder: '-- Pilih Akun --',
                allowClear: true
            });
        });
    </script>
@endsection

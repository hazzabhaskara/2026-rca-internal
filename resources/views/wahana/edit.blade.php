@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form action="{{ route('wahana.update', $wahana->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
                        <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Wahana Edit
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                        <div>
                            <label for="kode_wahana" class="block font-medium">Kode wahana</label>
                            <input type="text" name="kode_wahana" id="kode_wahana"
                                value="{{ old('kd_wahana', $wahana->kode_wahana) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('kode_wahana') border-red-500 @enderror">
                            @error('kode_wahana')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nama_wahana" class="block font-medium">Nama wahana</label>
                            <input type="text" name="nama_wahana" id="nama_wahana"
                                value="{{ old('nama_wahana', $wahana->nama_wahana) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('nama_wahana') border-red-500 @enderror"
                                required>
                            @error('nama_wahana')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="unit_kerja_id" class="block text-sm font-medium text-gray-700">Unit Kerja
                            </label>
                            <select name="unit_kerja_id" id="unit_kerja_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih unit kerja --</option>
                                @foreach ($unit as $g)
                                    <option value="{{ $g->id }}"
                                        {{ isset($wahana) && $wahana->unit_kerja_id == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="kategori" class="block font-medium">Kategori wahana</label>
                            <input type="text" name="kategori" id="kategori"
                                value="{{ old('kategori', $wahana->kategori) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('kategori') border-red-500 @enderror">
                            @error('kategori')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="kapasitas" class="block font-medium">kapasitas wahana</label>
                            <input type="text" name="kapasitas" id="kapasitas"
                                value="{{ old('kapasitas', $wahana->kapasitas) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('kapasitas') border-red-500 @enderror">
                            @error('kapasitas')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="urutan" class="block font-medium">Urutan</label>
                            <input type="text" name="urutan" id="urutan"
                                value="{{ old('urutan', $wahana->urutan) }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500 
                                          @error('urutan') border-red-500 @enderror"
                                required>
                            @error('urutan')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Tipe -->
                        <div class="mb-4">
                            <label for="status" class="block text-gray-700 font-medium">Status</label>
                            <select name="status" id="status" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih --</option>
                                <option value="Aktif" {{ old('status', $wahana->status) == 'Aktif' ? 'selected' : '' }}>
                                    Aktif</option>
                                <option value="Non Aktif"
                                    {{ old('status', $wahana->status) == 'Non Aktif' ? 'selected' : '' }}>
                                    Non Aktif</option>
                            </select>
                        </div>
                    </div>
                    {{-- Akun Terkait --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-gray-800">Wahana Item</h3>
                            <button type="button" id="add-row" class="btn btn-secondary">+ Tambah Item</button>
                        </div>

                        <table class="w-full border-collapse border text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="border px-2 py-1 text-left w-[10%]">Kode Item</th>
                                    <th class="border px-2 py-1 text-left w-[25%]">Nama Item</th>
                                    <th class="border px-2 py-1 text-left w-[10%]">Harga</th>
                                    <th class="border px-2 py-1 text-left w-[10%]">Status</th>
                                    <th class="border px-2 py-1 text-left w-[10%]">Dasar Perhitungan <br>Titipan</th>
                                    <th class="border px-2 py-1 text-left w-[25%]">Account</th>
                                    <th class="border px-2 py-1 text-left w-[5%]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="rows-body">
                                @forelse ($wahana->wahanaItem as $d)
                                    <tr class="border-b row-item">
                                        <td class="border px-2 py-1">
                                            <input type="text" name="kode_item[]" value="{{ $d->kode_item ?? '' }}"
                                                class="w-full border rounded px-2 py-1">
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="text" name="nama_item[]" value="{{ $d->nama_item ?? '' }}"
                                                class="w-full border rounded px-2 py-1">
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="text" name="harga[]"
                                                value="{{ number_format($d->harga) ?? '' }}"
                                                class="w-full border rounded px-2 py-1">
                                        </td>
                                        <td class="border px-2 py-1">
                                            <select name="status_item[]" id="status" required
                                                class="w-full border border-gray-300 rounded-lg px-2 py-1  focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">-- Pilih --</option>
                                                <option value="1"
                                                    {{ old('status', $d->status) == '1' ? 'selected' : '' }}>
                                                    Aktif</option>
                                                <option value="0"
                                                    {{ old('status', $d->status) == '0' ? 'selected' : '' }}>
                                                    Non Aktif</option>
                                            </select>
                                        </td>
                                        <td class="border px-2 py-1">
                                            <select name="dasar_perhitungan_titipan[]" required
                                                class="dasar-select w-full border border-gray-300 rounded-lg px-2 py-1  focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">-- Pilih --</option>
                                                <option value="1"
                                                    {{ old('dasar_perhitungan_titipan', $d->dasar_perhitungan_titipan) == '1' ? 'selected' : '' }}>
                                                    Ya</option>
                                                <option value="0"
                                                    {{ old('dasar_perhitungan_titipan', $d->dasar_perhitungan_titipan) == '0' ? 'selected' : '' }}>
                                                    Tidak</option>
                                            </select>
                                            <div class="harga-container mt-2" style="display:none;">
                                                <input type="text" name="harga_perhitungan_titipan[]"
                                                    placeholder="Masukkan harga titipan omset"
                                                    oninput="formatRibuan(this)"
                                                    value="{{ number_format($d->harga_perhitungan_titipan) ?? '' }}"
                                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        </td>
                                        <td class="border px-2 py-1">
                                            <select style="width: 100%" name="account_id[]"
                                                class="select2 w-full border rounded">
                                                <option value="">-- Pilih Akun --</option>
                                                @php
                                                    $selectedValue = $d->account_id . '|' . ($d->departemen_id ?? 0);
                                                @endphp

                                                @foreach ($account as $acc)
                                                    @if ($acc->departemenAkun->count() > 0)
                                                        @foreach ($acc->departemenAkun as $depAkun)
                                                            @php $optionValue = $acc->id . '|' . $depAkun->departemen->id; @endphp
                                                            <option value="{{ $optionValue }}"
                                                                {{ $optionValue == $selectedValue ? 'selected' : '' }}>
                                                                {{ $acc->kode_akun }} - {{ $acc->nama_akun }} -
                                                                {{ $depAkun->departemen->deskripsi ?? '-' }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        @php $optionValue = $acc->id . '|0'; @endphp
                                                        <option value="{{ $optionValue }}"
                                                            {{ $optionValue == $selectedValue ? 'selected' : '' }}>
                                                            {{ $acc->kode_akun }} - {{ $acc->nama_akun }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="border px-2 py-1">
                                            <button type="button" class="btn btn-danger remove-row">Hapus</button>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- fallback minimal 1 baris --}}
                                    <tr class="border-b row-item">
                                        <td class="border px-2 py-1">
                                            <input type="text" name="kode_item[]"
                                                class="w-full border rounded px-2 py-1">
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="text" name="nama_item[]"
                                                class="w-full border rounded px-2 py-1">
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="text" name="harga[]" oninput="formatRibuan(this)"
                                                class="w-full border rounded px-2 py-1">
                                        </td>
                                        <td class="border px-2 py-1">
                                            <select name="status_item[]" class="w-full border rounded px-2 py-1">
                                                <option value="1">Aktif</option>
                                                <option value="0">Non Aktif</option>
                                            </select>
                                        </td>
                                        <td style="padding: 12px; border: 1px solid #ddd;">
                                            <select name="dasar_perhitungan_titipan[]"
                                                class="dasar-select w-full border border-gray-300 rounded-lg px-4 py-2">
                                                <option>--Pilih--</option>
                                                <option value="1">Ya</option>
                                                <option value="0">Tidak</option>
                                            </select>
                                            <div class="harga-container mt-2" style="display:none;">
                                                <input type="text" name="harga_perhitungan_titipan[]"
                                                    placeholder="Masukkan harga titipan omset"
                                                    oninput="formatRibuan(this)"
                                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        </td>
                                        <td class="border px-2 py-1">
                                            <select name="account_id[]" style="width: 100%;"
                                                class="select2 w-full border rounded">
                                                <option value="">-- Pilih Akun --</option>
                                                @foreach ($account as $acc)
                                                    @if ($acc->departemenAkun->count() > 0)
                                                        @foreach ($acc->departemenAkun as $depAkun)
                                                            <option
                                                                value="{{ $acc->id }}|{{ $depAkun->departemen->id }}">
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
                                        </td>
                                        <td class="border px-2 py-1">
                                            <button type="button" class="btn btn-danger remove-row">Hapus</button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <a href="{{ route('wahana.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                            Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


    {{-- jQuery & Select2 JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.dasar-select').forEach(function(select) {
                const hargaContainer = select.parentElement.querySelector('.harga-container');
                hargaContainer.style.display = (select.value === '1') ? 'block' : 'none';
            });
        });

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('dasar-select')) {
                const hargaContainer = e.target.parentElement.querySelector('.harga-container');
                hargaContainer.style.display = (e.target.value === '1') ? 'block' : 'none';
            }
        });

        function initSelect2(scope) {
            $(scope).find('.select2').select2();
        }

        $(document).ready(function() {
            initSelect2(document);

            const rowTemplate = `
    <tr class="border-b row-item">
        <td class="border px-2 py-1">
            <input type="text" name="kode_item[]" class="w-full border rounded px-2 py-1">
        </td>
        <td class="border px-2 py-1">
            <input type="text" name="nama_item[]" class="w-full border rounded px-2 py-1">
        </td>
        <td class="border px-2 py-1">
            <input type="text" name="harga[]" oninput="formatRibuan(this)" class="w-full border rounded px-2 py-1">
        </td>
        <td class="border px-2 py-1">
            <select name="status_item[]" class="w-full border rounded px-2 py-1">
                <option value="1">Aktif</option>
                <option value="0">Non Aktif</option>
            </select>
        </td>
        <td style="padding: 12px; border: 1px solid #ddd;">
            <select name="dasar_perhitungan_titipan[]"
                class="dasar-select w-full border border-gray-300 rounded-lg px-4 py-2">
                <option>--Pilih--</option>
                <option value="1">Ya</option>
                <option value="0">Tidak</option>
            </select>
            <div class="harga-container mt-2" style="display:none;">
                <input type="text" name="harga_perhitungan_titipan[]" placeholder="Masukkan harga titipan omset"
                    oninput="formatRibuan(this)"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
        </td>
        <td class="border px-2 py-1">
            <select style="width:100%;" name="account_id[]" class="select2 w-full border rounded">
                <option value="">-- Pilih Akun --</option>
                @foreach ($account as $acc)
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
        </td>
        <td class="border px-2 py-1">
            <button type="button" class="btn btn-danger remove-row">Hapus</button>
        </td>
    </tr>`;

            $('#add-row').on('click', function() {
                const $row = $(rowTemplate);
                $('#rows-body').append($row);
                initSelect2($row);
            });

            $(document).on('click', '.remove-row', function() {
                const total = $('#rows-body .row-item').length;
                if (total > 1) {
                    $(this).closest('tr').remove();
                } else {
                    // jika tinggal 1, kosongkan saja
                    const $tr = $(this).closest('tr');
                    $tr.find('input[name="detail_id[]"]').val('');
                    $tr.find('select[name="account_id[]"]').val('').trigger('change');
                    $tr.find('input[name="deskripsi[]"]').val('');
                }
            });
        });
    </script>
    <script>
        function formatRibuan(input) {
            let value = input.value;
            // Hilangkan semua karakter selain angka
            value = value.replace(/[^\d]/g, "");
            // Format angka jadi ribuan
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            input.value = value;
        }
    </script>
@endsection

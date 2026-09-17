@extends('layouts.app')

@section('content')
    <div class="py-10 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">

        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form method="POST"
                    action="{{ isset($wahana) ? route('wahana.update', $wahana->id) : route('wahana.store') }}">
                    @csrf
                    @if (isset($wahana))
                        @method('PUT')
                    @endif

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
                        Wahana Create
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Kode Wahana</label>
                            <input type="text" name="kode_wahana" value="{{ old('kode_wahana') }}"
                                placeholder="Masukkan kode wahana"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Nama Wahana</label>
                            <input type="text" name="nama_wahana" value="{{ old('nama_wahana') }}"
                                placeholder="Masukkan nama wahana"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Unit Kerja</label>
                            <select name="unit_kerja_id" id="unit_kerja_id" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih --</option>
                                @foreach ($unit_kerja as $g)
                                    <option value="{{ $g->id }}"
                                        {{ isset($wahana) && $wahana->unit_kerja_id == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="block font-medium text-gray-700 mb-1">Kategori</label>
                            <input type="text" name="kategori" placeholder="Masukkan kategori wahana"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Status</label>
                            <select name="status"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="Aktif" {{ old('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="Non Aktif" {{ old('status') == 'Non Aktif' ? 'selected' : '' }}>Non Aktif
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="block font-medium text-gray-700 mb-1">Kapasitas</label>
                            <input type="number" name="kapasitas" placeholder="Masukkan kapasitas setiap wahana"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="block font-medium text-gray-700 mb-1">Urutan</label>
                            <input type="number" name="urutan" placeholder="Masukkan Urutan dalam tampilan"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div
                        style="border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-top:20px">
                        <table style="width: 100%; border-collapse: collapse;" id="tabel-wahana">
                            <thead>
                                <tr style="background-color: #f5f5f5; font-weight: bold;">
                                    <th style="padding: 12px; border: 1px solid #ddd; width: 50px;">No.</th>
                                    <th style="padding: 12px; border: 1px solid #ddd;">Kode Item</th>
                                    <th style="padding: 12px; border: 1px solid #ddd;">Nama Item</th>
                                    <th style="padding: 12px; border: 1px solid #ddd;">Harga</th>
                                    <th style="padding: 12px; border: 1px solid #ddd;">Status</th>
                                    <th style="padding: 12px; border:1px solid #ddd;">Dasar Perhitungan<br> Pajak</th>
                                    <th style="padding:12px; border:1px solid #ddd;">Account</th>
                                    <th style="padding: 12px; border: 1px solid #ddd; width: 70px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-wahana">
                                <tr>
                                    <td style="padding: 12px; border: 1px solid #ddd;">1</td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <input type="text" name="kode_item[]"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <input type="text" name="nama_item[]"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>

                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <input type="text" name="harga[]" oninput="formatRibuan(this)"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select
                                            name="status_item[]"class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                                                oninput="formatRibuan(this)" placeholder="Masukkan harga titipan omset"
                                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd">
                                        <select name="account_id[]" id="account_id" required
                                            class="form-select w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Pilih --</option>
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
                                    <td style="text-align: center; border: 1px solid #ddd;">
                                        <button type="button" onclick="hapusBaris(this)"
                                            style="color: red; border: none; background: none; font-size: 18px;">🗑️</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Tombol Tambah Baris -->
                        <div style="margin: 20px;">
                            <button type="button" onclick="tambahBaris()" class="btn elevation-2"
                                style="background-color: green; color: white; padding: 10px 16px; border: none; border-radius: 6px; font-size: 14px;">+
                                Tambah Baris</button>
                        </div>

                    </div>

                    <!-- Buttons -->
                    <div class="mt-6 justify-end flex space-x-4">
                        <a href="{{ route('wahana.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition">
                            {{ isset($wahana) ? 'Update' : 'Process' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('dasar-select')) {
                const hargaContainer = e.target.parentElement.querySelector('.harga-container');
                hargaContainer.style.display = (e.target.value === '1') ? 'block' : 'none';
            }
        });

        function tambahBaris() {
            let tbody = document.getElementById('tbody-wahana');
            let rowCount = tbody.rows.length;
            let row = tbody.insertRow();

            row.innerHTML = `
        <td style="padding: 12px; border: 1px solid #ddd;"></td>
        <td style="padding: 12px; border: 1px solid #ddd;">
            <input type="text" name="kode_item[]"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </td>
        <td style="padding: 12px; border: 1px solid #ddd;">
            <input type="text" name="nama_item[]"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </td>
        <td style="padding: 12px; border: 1px solid #ddd;">
            <input type="text" name="harga[]" oninput="formatRibuan(this)"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </td>
        <td style="padding: 12px; border: 1px solid #ddd;">
            <select name="status_item[]" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                                                placeholder="Masukkan harga titipan omset" oninput="formatRibuan(this)"
                                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </td>
        <td style="padding: 12px; border: 1px solid #ddd;">
            <select name="account_id[]" required
                class="form-select w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih --</option>
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
        <td style="text-align: center; border: 1px solid #ddd;">
            <button type="button" onclick="hapusBaris(this)" style="color: red; border: none; background: none;">🗑️</button>
        </td>
    `;

            perbaruiNomor();

            $(row).find('.form-select').select2({
                placeholder: "Cari account...",
                allowClear: true,
                width: '100%'
            });
        }


        function hapusBaris(button) {
            let row = button.closest('tr');
            let tbody = row.parentNode;
            tbody.removeChild(row);
            perbaruiNomor();
        }

        function perbaruiNomor() {
            let tbody = document.getElementById('tbody-wahana');
            let rows = tbody.querySelectorAll('tr');
            rows.forEach((tr, index) => {
                tr.cells[0].innerText = index + 1;
            });
        }
    </script>
    <script>
        $(document).ready(function() {
            $('.form-select').select2({
                placeholder: "Cari account...",
                allowClear: true,
                width: '100%',
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

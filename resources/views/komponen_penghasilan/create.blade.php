@extends('layouts.app')

@section('content')

    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form method="POST" enctype="multipart/form-data"
                    action="{{ isset($data) ? route('komponen_penghasilan.update', $data->id) : route('komponen_penghasilan.store') }}">

                    @csrf
                    @if (isset($data))
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
                        Income Components By Level Create
                    </h4>


                    <div style="border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <table style="width: 100%; border-collapse: collapse;" id="tabel-komponen_penghasilan">
                            @php
                                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                            @endphp
                            <thead
                                class="bg-gradient-to-r bg-[{{ $themeColor }}]  to-blue-600 text-white text-sm font-semibold">
                                <tr style="font-weight: bold;">
                                    <th style="padding: 12px; border: 1px solid; width: 50px;">No.</th>
                                    <th style="padding: 12px; border: 1px solid;">Level Kepegawaian</th>
                                    <th style="padding: 12px; border: 1px solid;">Nama Komponen</th>
                                    <th style="padding: 12px; border: 1px solid;">Tipe</th>
                                    <th style="padding: 12px; border: 1px solid;">Sifat</th>
                                    <th style="padding: 12px; border: 1px solid;">Periode Perhitungan</th>
                                    <th style="padding: 12px; border: 1px solid;">Status Komponen</th>
                                    <th style="padding: 12px; border: 1px solid;">Berdasarkan Kehadiran</th>
                                    <th style="padding: 12px; border: 1px solid;">Deskripsi</th>
                                    <th style="padding: 12px; border: 1px solid; width: 70px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-komponen_penghasilan">
                                <tr>
                                    <td style="padding: 12px; border: 1px solid #ddd;">1</td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select name="level_karyawan_id[]"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required>
                                            <option value="">-- Pilih Level Kepegawaian --</option>
                                            @foreach ($levels as $level)
                                                <option value="{{ $level->id }}"
                                                    {{ old('level_karyawan_id', $data->level_karyawan_id ?? '') == $level->id ? 'selected' : '' }}>
                                                    {{ $level->nama_level }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <input type="text" name="nama_komponen[]" placeholder="Masukkan nama komponen"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select name="tipe[]" id="tipe" required
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Pilih --</option>
                                            <option value="Penambah Gaji Bruto"
                                                {{ old('tipe', $data->tipe ?? '') == 'Penambah Gaji Bruto' ? 'selected' : '' }}>
                                                Penambah Gaji Bruto</option>
                                            <option value="Penambah Penghasilan Bruto"
                                                {{ old('tipe', $data->tipe ?? '') == 'Penambah Penghasilan Bruto' ? 'selected' : '' }}>
                                                Penambah Penghasilan Bruto</option>
                                            <option value="Pengurang Penghasilan Bruto"
                                                {{ old('tipe', $data->tipe ?? '') == 'Pengurang Penghasilan Bruto' ? 'selected' : '' }}>
                                                Pengurang Penghasilan Bruto</option>
                                        </select>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select name="sifat[]" id="sifat" required
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Pilih --</option>
                                            <option value="Tunai"
                                                {{ old('sifat', $data->sifat ?? '') == 'Tunai' ? 'selected' : '' }}>
                                                Tunai</option>
                                            <option value="Non Tunai"
                                                {{ old('sifat', $data->sifat ?? '') == 'Non Tunai' ? 'selected' : '' }}>
                                                Non Tunai
                                            </option>
                                        </select>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select name="periode_perhitungan[]" id="periode_perhitungan" required
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Pilih--</option>
                                            <option value="Jam"
                                                {{ old('periode_perhitungan', $data->periode_perhitungan ?? '') == 'Jam' ? 'selected' : '' }}>
                                                Jam</option>
                                            <option value="Harian"
                                                {{ old('periode_perhitungan', $data->periode_perhitungan ?? '') == 'Harian' ? 'selected' : '' }}>
                                                Harian</option>
                                            <option value="Mingguan"
                                                {{ old('periode_perhitungan', $data->periode_perhitungan ?? '') == 'Mingguan' ? 'selected' : '' }}>
                                                Mingguan</option>
                                            <option value="Bulanan"
                                                {{ old('periode_perhitungan', $data->periode_perhitungan ?? '') == 'Bulanan' ? 'selected' : '' }}>
                                                Bulanan</option>
                                        </select>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select name="status_komponen[]" id="status_komponen" required
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Pilih --</option>
                                            <option value="Aktif"
                                                {{ old('status_komponen', $data->status_komponen ?? '') == 'Aktif' ? 'selected' : '' }}>
                                                Aktif</option>
                                            <option value="Non Aktif"
                                                {{ old('status_komponen', $data->status_komponen ?? '') == 'Non Aktif' ? 'selected' : '' }}>
                                                Non Aktif</option>
                                        </select>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <input type="checkbox" name="is_kehadiran[]" value="1"
                                            class="form-check-input ml-3" id="isKehadiran">
                                        <br>
                                        <small class="form-text text-muted">
                                            Centang jika komponen ini digunakan untuk perhitungan otomatis berdasarkan
                                            jumlah hadir
                                            karyawan.
                                        </small>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <input type="text" name="deskripsi[]" placeholder="Masukkan deskripsi(Opsional)"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="{{ route('komponen_penghasilan.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition">
                            {{ isset($data) ? 'Update' : 'Process' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function toggleAutoGenerate() {
            const checkbox = document.getElementById('auto_generate');
            const manualField = document.getElementById('manual_kd');

            if (checkbox.checked) {
                manualField.style.display = 'none';
            } else {
                manualField.style.display = 'block';
            }
        }

        // Inisialisasi saat halaman pertama kali dimuat
        window.onload = toggleAutoGenerate;
    </script>
    <script>
        function tambahBaris() {
            let tbody = document.getElementById('tbody-komponen_penghasilan');
            let rowCount = tbody.rows.length;
            let row = tbody.insertRow();

            row.innerHTML = `
        <td style="padding: 12px; border: 1px solid #ddd;"></td>
                                           <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select name="level_karyawan_id[]"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required>
                                            <option value="">-- Pilih Level Kepegawaian --</option>
                                            @foreach ($levels as $level)
                                                <option value="{{ $level->id }}"
                                                    {{ old('level_karyawan_id', $data->level_karyawan_id ?? '') == $level->id ? 'selected' : '' }}>
                                                    {{ $level->nama_level }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <input type="text" name="nama_komponen[]" placeholder="Masukkan nama komponen"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select name="tipe[]" id="tipe" required
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Pilih --</option>
                                            <option value="Penambah Gaji Bruto"
                                                {{ old('tipe', $data->tipe ?? '') == 'Penambah Gaji Bruto' ? 'selected' : '' }}>
                                                Penambah Gaji Bruto</option>
                                            <option value="Penambah Penghasilan Bruto"
                                                {{ old('tipe', $data->tipe ?? '') == 'Penambah Penghasilan Bruto' ? 'selected' : '' }}>
                                                Penambah Penghasilan Bruto</option>
                                            <option value="Pengurang Penghasilan Bruto"
                                                {{ old('tipe', $data->tipe ?? '') == 'Pengurang Penghasilan Bruto' ? 'selected' : '' }}>
                                                Pengurang Penghasilan Bruto</option>
                                        </select>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select name="sifat[]" id="sifat" required
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Pilih --</option>
                                            <option value="Tunai"
                                                {{ old('sifat', $data->sifat ?? '') == 'Tunai' ? 'selected' : '' }}>
                                                Tunai</option>
                                            <option value="Non Tunai"
                                                {{ old('sifat', $data->sifat ?? '') == 'Non Tunai' ? 'selected' : '' }}>
                                                Non Tunai
                                            </option>
                                        </select>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select name="periode_perhitungan[]" id="periode_perhitungan" required
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Pilih--</option>
                                            <option value="Jam"
                                                {{ old('periode_perhitungan', $data->periode_perhitungan ?? '') == 'Jam' ? 'selected' : '' }}>
                                                Jam</option>
                                            <option value="Harian"
                                                {{ old('periode_perhitungan', $data->periode_perhitungan ?? '') == 'Harian' ? 'selected' : '' }}>
                                                Harian</option>
                                            <option value="Mingguan"
                                                {{ old('periode_perhitungan', $data->periode_perhitungan ?? '') == 'Mingguan' ? 'selected' : '' }}>
                                                Mingguan</option>
                                            <option value="Bulanan"
                                                {{ old('periode_perhitungan', $data->periode_perhitungan ?? '') == 'Bulanan' ? 'selected' : '' }}>
                                                Bulanan</option>
                                        </select>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select name="status_komponen[]" id="status_komponen" required
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Pilih --</option>
                                            <option value="Aktif"
                                                {{ old('status_komponen', $data->status_komponen ?? '') == 'Aktif' ? 'selected' : '' }}>
                                                Aktif</option>
                                            <option value="Non Aktif"
                                                {{ old('status_komponen', $data->status_komponen ?? '') == 'Non Aktif' ? 'selected' : '' }}>
                                                Non Aktif</option>
                                        </select>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <input type="checkbox" name="is_kehadiran[]" value="1"
                                            class="form-check-input ml-3" id="isKehadiran">
                                        <br>
                                        <small class="form-text text-muted">
                                            Centang jika komponen ini digunakan untuk perhitungan otomatis berdasarkan
                                            jumlah hadir
                                            karyawan.
                                        </small>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <input type="text" name="deskripsi[]" placeholder="Masukkan deskripsi(Opsional)"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
        <td style="text-align: center; border: 1px solid #ddd;">
            <button type="button" onclick="hapusBaris(this)" style="color: red; border: none; background: none;">🗑️</button>
        </td>
     `;

            perbaruiNomor();
        }

        function hapusBaris(button) {
            let row = button.closest('tr');
            let tbody = row.parentNode;
            tbody.removeChild(row);
            perbaruiNomor();
        }

        function perbaruiNomor() {
            let tbody = document.getElementById('tbody-komponen_penghasilan');
            let rows = tbody.querySelectorAll('tr');
            rows.forEach((tr, index) => {
                tr.cells[0].innerText = index + 1;
            });
        }
    </script>
@endsection

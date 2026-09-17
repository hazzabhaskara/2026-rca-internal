@extends('layouts.app')

@section('content')
    <div class="py-10 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">

        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form method="POST"
                    action="{{ isset($jam_kerja) ? route('jam_kerja.update', $jam_kerja->id) : route('jam_kerja.store') }}">
                    @csrf
                    @if (isset($jam_kerja))
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
                        Jam Kerja Create
                    </h4>

                    <div style="border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <table style="width: 100%; border-collapse: collapse;" id="tabel-jam_kerja">
                            <thead>
                                <tr style="background-color: #f5f5f5; font-weight: bold;">
                                    <th style="padding: 12px; border: 1px solid #ddd; width: 50px;">No.</th>
                                    <th style="padding: 12px; border: 1px solid #ddd;">Unit Kerja</th>
                                    <th style="padding: 12px; border: 1px solid #ddd;">Jam Mulai</th>
                                    <th style="padding: 12px; border: 1px solid #ddd;">Jam Selesai</th>
                                    <th style="padding: 12px; border: 1px solid #ddd; width: 70px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-jam_kerja">
                                <tr>
                                    <td style="padding: 12px; border: 1px solid #ddd;">1</td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select name="unit_kerja_id[]" id="unit_kerja_id"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($unit_kerja as $g)
                                                <option value="{{ $g->id }}"
                                                    {{ isset($jam_kerja) && $jam_kerja->unit_kerja_id == $g->id ? 'selected' : '' }}>
                                                    {{ $g->nama_unit }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <input type="time" name="jam_mulai[]" placeholder="Masukkan kode"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <input type="time" name="jam_selesai[]" placeholder="Masukkan nama"
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
                    <div class="mt-6 justify-end flex space-x-4">
                        <a href="{{ route('jam_kerja.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition">
                            {{ isset($jam_kerja) ? 'Update' : 'Process' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // ambil nilai default dari Blade (di-render server-side)

        function tambahBaris() {
            let tbody = document.getElementById('tbody-jam_kerja');
            let rowCount = tbody.rows.length;
            let row = tbody.insertRow();

            // gunakan backtick agar HTML multi-line lebih mudah
            row.innerHTML = `
            <td style="padding: 12px; border: 1px solid #ddd;"></td>

                 <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select name="unit_kerja_id[]" id="unit_kerja_id"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($unit_kerja as $g)
                                                <option value="{{ $g->id }}"
                                                    {{ isset($jam_kerja) && $jam_kerja->unit_kerja_id == $g->id ? 'selected' : '' }}>
                                                    {{ $g->nama_unit }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <input type="time" name="jam_mulai[]" placeholder="Masukkan kode"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <input type="time" name="jam_selesai[]" placeholder="Masukkan nama"
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
            let tbody = document.getElementById('tbody-jam_kerja');
            let rows = tbody.querySelectorAll('tr');
            rows.forEach((tr, index) => {
                tr.cells[0].innerText = index + 1;
            });
        }
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

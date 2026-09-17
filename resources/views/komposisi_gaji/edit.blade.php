@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                    Salary Components Edit
                </h4>

                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('komposisi_gaji.update', $komposisi->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Karyawan</label>
                        <select name="kode_karyawan" class="w-full rounded-md border-gray-300 shadow-sm" disabled>
                            @foreach ($karyawan as $emp)
                                <option value="{{ $emp->id }}"
                                    {{ $emp->id == $komposisi->kode_karyawan ? 'selected' : '' }}>
                                    {{ $emp->nama_karyawan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table id="komponenTable" class="min-w-full border border-gray-200 table-auto">
                            @php
                                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                            @endphp
                            <thead
                                class="bg-gradient-to-r bg-[{{ $themeColor }}]  to-blue-600 text-white text-sm font-semibold">
                                <tr>
                                    <th class="px-4 py-2 border">Nama Komponen</th>
                                    <th class="px-4 py-2 border text-center">Jumlah Hari</th>
                                    <th class="px-4 py-2 border text-right">Nilai</th>
                                    <th class="px-4 py-2 border text-right">Potongan</th>
                                    <th class="px-4 py-2 border text-right">Total Nilai</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-komposisi_gaji">
                                @foreach ($details as $index => $detail)
                                    <tr>
                                        <td class="px-4 py-2 border">
                                            {{ $detail->komponen->nama_komponen }}
                                            <input type="hidden" name="komponen[{{ $index }}][id_detail]"
                                                value="{{ $detail->id }}">
                                            <input type="hidden" name="komponen[{{ $index }}][kode_komponen]"
                                                value="{{ $detail->kode_komponen }}">
                                        </td>
                                        <td class="px-4 py-2 border">
                                            <input type="number" name="komponen[{{ $index }}][jumlah_hari]"
                                                class="w-full border p-1 rounded text-center jumlah_hari"
                                                value="{{ $detail->jumlah_hari }}">
                                        </td>
                                        <td class="px-4 py-2 border">
                                            <input type="number" name="komponen[{{ $index }}][nilai]"
                                                class="w-full border p-1 rounded text-right nilai"
                                                value="{{ $detail->nilai }}">
                                        </td>

                                        <td class="px-4 py-2 border">
                                            <input type="number" name="komponen[{{ $index }}][potongan]"
                                                class="w-full border p-1 rounded text-right potongan"
                                                value="{{ $detail->potongan }}">
                                        </td>
                                        <td class="px-4 py-2 border text-right">
                                            <input type="text"
                                                class="total border rounded w-full p-1 bg-gray-100 text-right" readonly
                                                value="0">
                                            <input type="hidden" name="komponen[{{ $index }}][total]"
                                                class="total_raw" value="0">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-200 font-semibold">
                                    <td colspan="4" class="px-4 py-2 border text-right">Total Keseluruhan</td>
                                    <td class="px-4 py-2 border text-right" id="grandTotal">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="tambah_baris">
                        <button type="button" class="px-4 py-2 bg-green-600 rounded text-white mt-3"
                            onclick="tambahBaris()">Tambah Baris</button>

                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <a href="{{ route('komposisi_gaji.index') }}"
                            class="ml-2 px-6 py-2 bg-gray-100 text-gray-600 hover:underline">Cancel</a>
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // 🔹 Format angka dengan 2 desimal
        function formatNumber(num) {
            return Number(num).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // 🔹 Menghitung ulang total untuk setiap baris
        function calculateTotals() {
            let grandTotal = 0;
            const rows = document.querySelectorAll('#tbody-komposisi_gaji tr');
            console.log('Jumlah baris:', rows.length);

            rows.forEach(row => {
                const nilai = parseFloat(row.querySelector('.nilai')?.value || 0);
                const jumlahHari = parseFloat(row.querySelector('.jumlah_hari')?.value || 0);
                const potongan = parseFloat(row.querySelector('.potongan')?.value || 0);

                const total = (nilai * jumlahHari) - (potongan * jumlahHari);
                row.querySelector('.total').value = formatNumber(total);
                grandTotal += total;
            });

            // console.log('Grand total:', grandTotal);
            document.getElementById('grandTotal').textContent = formatNumber(grandTotal);
        }


        // 🔹 Tambah baris baru dinamis
        function tambahBaris() {
            const tbody = document.getElementById('tbody-komposisi_gaji');
            const rowCount = tbody.rows.length;
            const row = tbody.insertRow();

            row.innerHTML = `
            <td class="px-4 py-2 border">
                <select name="baru[${rowCount}][kode_komponen]"
                        class="w-full border border-gray-300 rounded px-2 py-1">
                    <option value="">-- Pilih --</option>
                    @foreach ($komponenBaru as $g)
                        <option value="{{ $g->id }}">{{ $g->nama_komponen }}</option>
                    @endforeach
                </select>
            </td>
            <td class="px-4 py-2 border">
                <input type="number" name="baru[${rowCount}][jumlah_hari]"
                       class="w-full border rounded p-1 text-center jumlah_hari" value="0">
            </td>
            <td class="px-4 py-2 border">
                <input type="number" name="baru[${rowCount}][nilai]"
                       class="w-full border rounded p-1 text-right nilai" value="0">
            </td>
            <td class="px-4 py-2 border">
                <input type="number" name="baru[${rowCount}][potongan]"
                       class="w-full border rounded p-1 text-right potongan" value="0">
            </td>
            <td class="px-4 py-2 border">
                <input type="text"
                                                class="total border rounded w-full p-1 bg-gray-100 text-right" readonly
                                                value="0">
            </td>
        `;

            // 🔹 Setelah baris baru ditambahkan, aktifkan event agar ikut hitung otomatis
            row.querySelectorAll('.nilai, .jumlah_hari, .potongan').forEach(input => {
                input.addEventListener('input', calculateTotals);
            });
        }

        // 🔹 Saat halaman selesai dimuat, hitung total awal dan aktifkan event handler
        document.addEventListener('DOMContentLoaded', function() {
            calculateTotals();

            document.querySelectorAll('.nilai, .jumlah_hari, .potongan').forEach(input => {
                input.addEventListener('input', calculateTotals);
            });
        });
    </script>
@endsection

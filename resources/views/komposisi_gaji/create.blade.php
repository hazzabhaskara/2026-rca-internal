@extends('layouts.app')

@section('content')

    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                    Salary Components Create
                </h4>
                <form method="POST" enctype="multipart/form-data"
                    action="{{ isset($data) ? route('komponen_penghasilan.update', $data->id) : route('komposisi_gaji.store') }}">

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

                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Karyawan</label>
                            <select id="kode_karyawan_id" name="kode_karyawan"
                                class="w-full rounded-md bg-blue-200 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($karyawan as $level)
                                    <option value="{{ $level->id }}"
                                        {{ old('kode_karyawan', $data->kode_karyawan ?? '') == $level->id ? 'selected' : '' }}>
                                        {{ $level->nama_panggilan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Tabel Komponen Penghasilan Berdasarkan Level -->
                    <div id="komponenContainer" class="mt-8 hidden">
                        <h3 class="text-lg font-semibold mb-4">Komponen Penghasilan</h3>
                        <div id="komponenTable"></div>
                    </div>


                    <!-- Buttons -->
                    <div class="mt-6 justify-end  flex space-x-4">
                        <a href="{{ route('komposisi_gaji.index') }}"
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

@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const karyawanSelect = document.getElementById('kode_karyawan_id');
        const komponenContainer = document.getElementById('komponenContainer');
        const komponenTable = document.getElementById('komponenTable');

        // Fungsi format angka Indonesia
        function formatNumber(number) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(number);
        }

        // Fungsi konversi string format ID ke angka float
        function parseFormattedNumber(str) {
            return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
        }

        karyawanSelect.addEventListener('change', function() {
            const karyawanId = this.value;
            komponenTable.innerHTML = '';
            komponenContainer.classList.add('hidden');

            if (karyawanId) {
                fetch(`/get-komponen-by-karyawan/${karyawanId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            let tableHTML = `
                                <table class="min-w-full table-auto border border-gray-200">
                                    <thead>
                                        <tr class="bg-gray-100 text-left">
                                            <th class="px-4 py-2 border">Nama Komponen</th>
                                            <th class="px-4 py-2 border">Tipe</th>
                                            <th class="px-4 py-2 border">Periode</th>
                                            <th class="px-4 py-2 border">Jumlah Hari</th>
                                            <th class="px-4 py-2 border">Nilai</th>
                                            <th class="px-4 py-2 border">Potongan</th>
                                            <th class="px-4 py-2 border">Total Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            `;

                            data.forEach((item, index) => {
                                tableHTML += `
                                    <tr>
                                        <td class="px-2 py-1 border">${item.nama_komponen}</td>
                                        <input type="hidden" name="komponen[${index}][kode_komponen]" value="${item.id}">
                                        <td class="px-2 py-1 border">${item.tipe}</td>
                                        <td class="px-2 py-1 border">${item.periode_perhitungan}</td>
                                          <td class="px-2 py-1 border">
                                            <input type="number" name="komponen[${index}][jumlah_hari]" class="jumlah-hari border rounded w-full p-1" data-index="${index}">
                                        </td>
                                        <td class="px-2 py-1 border">
                                            <input type="text" name="komponen[${index}][nilai]" class="nilai border text-right rounded w-full p-1" data-index="${index}">
                                        </td>
                                        <td class="px-2 py-1 border">
                                            <input type="text" name="komponen[${index}][potongan]" class="potongan border text-right rounded w-full p-1" data-index="${index}">
                                        </td>
                                        <td class="px-2 py-1 border">
                                            <input type="text" name="komponen[${index}][total]" class="total border text-right rounded w-full p-1 bg-gray-100" data-index="${index}" readonly>
                                        </td>
                                    </tr>
                                `;
                            });

                            tableHTML += `
                                            </tbody>
                                            <tfoot>
                                            <tr class="bg-gray-200 font-semibold">
                                                <td colspan="6" class="px-4 py-2 border text-right">Total Keseluruhan:</td>
                                                <td class="px-4 py-2 border text-right" id="grandTotal">0,00</td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                        `;
                            komponenTable.innerHTML = tableHTML;
                            komponenContainer.classList.remove('hidden');

                            // Fungsi perhitungan ulang total
                            function calculate(index) {
                                const nilai = parseFormattedNumber(document.querySelector(
                                    `input[name="komponen[${index}][nilai]"]`).value);
                                const jumlahHari = parseFloat(document.querySelector(
                                    `input[name="komponen[${index}][jumlah_hari]"]`).value) || 0;
                                const potongan = parseFormattedNumber(document.querySelector(
                                    `input[name="komponen[${index}][potongan]"]`).value);

                                const totalNilai = nilai * jumlahHari;
                                const totalPotongan = potongan * jumlahHari;
                                const totalKeseluruhan = totalNilai - totalPotongan;

                                document.querySelector(`input[name="komponen[${index}][total]"]`)
                                    .value = formatNumber(totalKeseluruhan);
                            }

                            function updateGrandTotal() {
                                let sum = 0;
                                komponenTable.querySelectorAll('.total').forEach(input => {
                                    sum += parseFormattedNumber(input.value);
                                });
                                document.getElementById('grandTotal').textContent = formatNumber(
                                    sum);
                            }


                            // Event handler untuk semua input
                            komponenTable.querySelectorAll('.nilai, .jumlah-hari, .potongan')
                                .forEach(input => {
                                    input.addEventListener('input', function() {
                                        const index = this.dataset.index;
                                        calculate(index);
                                        updateGrandTotal();
                                    });
                                });

                            // Format nilai dan potongan saat blur
                            komponenTable.querySelectorAll('.nilai, .potongan').forEach(input => {
                                input.addEventListener('blur', function() {
                                    const raw = parseFormattedNumber(this.value);
                                    this.value = formatNumber(raw);
                                });
                            });
                        }
                    });
            }
        });
    });
</script>

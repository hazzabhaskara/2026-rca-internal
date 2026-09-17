@extends('layouts.app')
@section('content')

    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6 space-y-6">
                <h2 class="font-bold text-lg mb-4">Salary Calculation Non Staff</h2>
                <form method="POST" enctype="multipart/form-data"
                    action="{{ isset($data) ? route('pembayaran_gaji_nonstaff.update', $data->id) : route('pembayaran_gaji_nonstaff.store') }}">
                    @csrf
                    @if (isset($data))
                        @method('PUT')
                    @endif

                    {{-- Error Validation --}}
                    @if ($errors->any())
                        <div class="bg-red-100 text-red-700 p-4 rounded-md">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="p-4 bg-red-100 text-red-700 rounded-md">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Karyawan --}}
                    <div>
                        <label
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">Karyawan</label>
                        <select id="kode_karyawan_id" name="kode_karyawan"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-3"
                            required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach ($karyawan as $level)
                                <option value="{{ $level->id }}"
                                    {{ old('kode_karyawan', $data->kode_karyawan ?? '') == $level->id ? 'selected' : '' }}>
                                    {{ $level->nama_karyawan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">Periode
                                Awal</label>
                            <input type="date" name="periode_awal"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="{{ old('periode_awal', $data->periode_awal ?? '') }}">
                        </div>
                        <div>
                            <label
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">Periode
                                Akhir</label>
                            <input type="date" name="periode_akhir"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="{{ old('periode_akhir', $data->periode_akhir ?? '') }}">
                        </div>
                        <div>
                            <label
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">Tanggal
                                Pembayaran</label>
                            <input type="date" name="tanggal_pembayaran"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="{{ old('tanggal_pembayaran', $data->tanggal_pembayaran ?? '') }}">
                        </div>
                    </div>

                    {{-- Komponen --}}
                    <div id="komponenContainer" class="mt-8 hidden">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Komponen Penghasilan</h3>
                        <div id="komponenTable"></div>
                        <div class="mt-4 text-right font-semibold">
                            Total Keseluruhan: <span id="grandTotal">0</span>
                        </div>
                    </div>

                    {{-- Tombol --}}
                    <div class="flex justify-between pt-6">
                        <button type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 transition">
                            {{ isset($data) ? 'Update' : 'Simpan' }}
                        </button>
                        <a href="{{ route('pembayaran_gaji_nonstaff.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const karyawanSelect = document.getElementById('kode_karyawan_id');
        const periodeAwalInput = document.querySelector('input[name="periode_awal"]');
        const periodeAkhirInput = document.querySelector('input[name="periode_akhir"]');
        const komponenContainer = document.getElementById('komponenContainer');
        const komponenTable = document.getElementById('komponenTable');
        const grandTotalSpan = document.getElementById('grandTotal');

        function formatNumber(value) {
            return parseFloat(value).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Hitung total keseluruhan
        function updateGrandTotal() {
            let total = 0;
            document.querySelectorAll('input.total-hidden').forEach(input => {
                const val = parseFloat(input.value) || 0;
                total += val;
            });
            grandTotalSpan.textContent = formatNumber(total);
        }

        function loadKomponen() {
            const karyawanId = karyawanSelect.value;
            const periodeAwal = periodeAwalInput.value;
            const periodeAkhir = periodeAkhirInput.value;

            komponenTable.innerHTML = '';
            komponenContainer.classList.add('hidden');

            if (!karyawanId || !periodeAwal || !periodeAkhir) return;

            fetch(
                    `/get-pembayaran-gaji-by-karyawan/${karyawanId}?periode_awal=${periodeAwal}&periode_akhir=${periodeAkhir}`
                )
                .then(response => response.json())
                .then(data => {
                    if (!Array.isArray(data) || data.length === 0) {
                        komponenTable.innerHTML =
                            '<p class="text-red-500 mt-4">Tidak ada komposisi gaji ditemukan untuk karyawan ini.</p>';
                        return;
                    }

                    let tableHTML = `
                    <table class="min-w-full table-auto border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100 text-left">
                                <th class="px-4 py-2 border">Nama Komponen</th>
                                <th class="px-4 py-2 border">Tipe</th>
                                <th class="px-4 py-2 border">Periode</th>
                                <th class="px-4 py-2 border text-right">Nilai</th>
                                <th class="px-4 py-2 border text-center">Jumlah</th>
                                <th class="px-4 py-2 border text-right">Potongan</th>
                                <th class="px-4 py-2 border">Catatan</th>
                                <th class="px-4 py-2 border text-right">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                    data.forEach((item, index) => {
                        // Tentukan label Jumlah
                        let jumlahLabel = 'Hari';
                        if (item.nama_komponen.toLowerCase() === 'lembur') jumlahLabel = 'Jam';
                        if (item.nama_komponen.toLowerCase() === 'transportasi') jumlahLabel =
                            'Unit';

                        // Lock nilai transportasi jika target tidak tercapai
                        const isTransportasiLocked = (item.nama_komponen.toLowerCase() ===
                            'transportasi' && item.nilai == 0);

                        tableHTML += `
                        <tr>
                            <td class="px-4 py-2 border">
                                ${item.nama_komponen}
                                <input type="hidden" name="komponen[${index}][kode_komponen]" value="${item.id}">
                            </td>
                            <td class="px-4 py-2 border">${item.tipe || '-'}</td>
                            <td class="px-4 py-2 border">${item.periode_perhitungan || '-'}</td>
                            <td class="px-4 py-2 border">
                                <input type="number" step="any" name="komponen[${index}][nilai]" 
                                       class="nilai border rounded w-full p-1 text-right" 
                                       data-index="${index}" value="${item.nilai || 0}"
                                       ${isTransportasiLocked ? 'readonly style="background:#f3f4f6; color:#999;"' : ''}>
                            </td>
                            <td class="px-4 py-2 border">
                                <input type="number" step="any" name="komponen[${index}][jumlah_hari]" 
                                       class="jumlah-hari border rounded w-full p-1 text-center" 
                                       data-index="${index}" value="${item.jumlah_hari || 0}">
                                <span class="text-xs text-gray-500">${jumlahLabel}</span>
                            </td>
                            <td class="px-4 py-2 border">
                                <input type="number" step="any" name="komponen[${index}][potongan]" 
                                       class="potongan border rounded w-full p-1 text-right" 
                                       data-index="${index}" value="${item.potongan || 0}">
                            </td>
                            <td class="px-4 py-2 border">
                                ${item.catatan || '-'}
                                <input type="hidden" name="komponen[${index}][catatan]" value="${item.catatan || ''}">
                            </td>
                            <td class="px-4 py-2 border">
                                <input type="text" class="total-display border rounded w-full p-1 bg-gray-100 text-right" 
                                       data-index="${index}" value="0" readonly>
                                <input type="hidden" name="komponen[${index}][total]" 
                                       class="total-hidden" data-index="${index}" value="0">
                            </td>
                        </tr>
                    `;
                    });

                    tableHTML += '</tbody></table>';
                    komponenTable.innerHTML = tableHTML;
                    komponenContainer.classList.remove('hidden');

                    // perhitungan ulang total
                    function updateTotal(index) {
                        const nilai = parseFloat(document.querySelector(
                            `input[name="komponen[${index}][nilai]"]`).value) || 0;
                        const jumlah = parseFloat(document.querySelector(
                            `input[name="komponen[${index}][jumlah_hari]"]`).value) || 0;
                        const potongan = parseFloat(document.querySelector(
                            `input[name="komponen[${index}][potongan]"]`).value) || 0;

                        const totalNilai = nilai * jumlah;
                        const totalPotongan = potongan * jumlah;
                        const total = totalNilai - totalPotongan;

                        const displayInput = document.querySelector(
                            `input.total-display[data-index="${index}"]`);
                        if (displayInput) displayInput.value = formatNumber(total);

                        const hiddenInput = document.querySelector(
                            `input.total-hidden[data-index="${index}"]`);
                        if (hiddenInput) hiddenInput.value = total;

                        updateGrandTotal();
                    }

                    // pasang event listener
                    data.forEach((_, index) => {
                        ['nilai', 'jumlah_hari', 'potongan'].forEach(field => {
                            const input = document.querySelector(
                                `input[name="komponen[${index}][${field}]"]`);
                            if (input) {
                                input.addEventListener('input', () => updateTotal(index));
                                updateTotal(index);
                            }
                        });
                    });
                })
                .catch(err => {
                    console.error('Error saat mengambil data:', err);
                    komponenTable.innerHTML =
                        '<p class="text-red-500 mt-4">Terjadi kesalahan saat memuat data komponen.</p>';
                });
        }

        karyawanSelect.addEventListener('change', loadKomponen);
        periodeAwalInput.addEventListener('change', loadKomponen);
        periodeAkhirInput.addEventListener('change', loadKomponen);
    });
</script>

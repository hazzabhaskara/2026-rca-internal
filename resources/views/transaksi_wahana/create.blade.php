@extends('layouts.app')

@section('content')

    <div class="py-10 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">

        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form method="POST"
                    action="{{ isset($transaksi_wahana) ? route('transaksi_wahana.update', $transaksi_wahana->id) : route('transaksi_wahana.store') }}">
                    @csrf
                    @if (isset($transaksi_wahana))
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
                        Transaksi Create
                    </h4>
                    <div class="grid grid-cols-3 gap-4 text-sm">

                        <div class="mb-5">
                            <label for="unit_kerja_id" class="block text-sm font-medium text-gray-700 mb-1">Unit
                                Kerja</label>
                            <select name="unit_kerja_id" id="unit_kerja_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Unit Kerja --</option>
                                @foreach ($unit as $g)
                                    <option value="{{ $g->id }}"
                                        {{ isset($target_unit) && $target_unit->unit_kerja_id == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-5">
                            <label for="wahana_id" class="block text-sm font-medium text-gray-700 mb-1">Wahana</label>
                            <select name="wahana_id" id="wahana_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Wahana --</option>
                            </select>
                        </div>

                        <div class="mb-5">
                            <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                            </label>
                            <input type="date" name="tanggal"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div class="mb-2">
                            <label for="jenis_hari_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis
                                Hari</label>
                            <select name="jenis_hari_id" id="jenis_hari_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Jenis Hari --</option>
                            </select>
                        </div>
                        <div class="mb-5">

                            <label for="realisasi" class="block text-sm font-medium text-gray-700 mb-1">Pendapatan
                                (Rp)</label>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 rounded-l-md bg-gray-100 border border-r-0 border-gray-300 text-gray-600 text-sm">Rp</span>
                                <input type="text" id="realisasi" name="realisasi"
                                    value="{{ isset($transaksi_wahana) && is_numeric($transaksi_wahana->realisasi) ? number_format($transaksi_wahana->realisasi, 0, ',', '.') : '' }}"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Contoh: 4.500.000">
                            </div>

                        </div>
                        <div class="mb-5">
                            <label for="jumlah_pengunjung" class="block text-sm font-medium text-gray-700 mb-1">Jumlah
                                Pengunjung
                            </label>
                            <input type="text" name="jumlah_pengunjung"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                    </div>

                    {{-- Tombol --}}
                    <div class="flex justify-end">
                        <a href="{{ route('transaksi_wahana.index') }}"
                            class="mr-3 inline-block px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-block px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                            {{ isset($transaksi_wahana) ? 'Process' : 'Process' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function formatRupiah(inputId) {
            const input = document.getElementById(inputId);
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = new Intl.NumberFormat('id-ID').format(value);
                e.target.value = value;
            });

            const form = input.closest('form');
            form.addEventListener('submit', function() {
                input.value = input.value.replace(/\./g, '');
            });
        }

        formatRupiah('realisasi');
        formatRupiah('max_penghasilan');
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const unitSelect = document.getElementById('unit_kerja_id');
        const wahanaSelect = document.getElementById('wahana_id');
        const jenisSelect = document.getElementById('jenis_hari_id');


        unitSelect.addEventListener('change', function() {
            const unitId = this.value;

            // Kosongkan dropdown jenis hari
            jenisSelect.innerHTML = '<option value="">-- Pilih Jenis Hari --</option>';

            if (!unitId) return;

            fetch(`/get-jenis-hari/${unitId}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.nama;
                        jenisSelect.appendChild(opt);
                    });
                })
                .catch(err => console.error('Gagal load jenis hari:', err));
        });
        if (unitSelect && wahanaSelect) {
            unitSelect.addEventListener('change', function() {
                let unitId = this.value;
                wahanaSelect.innerHTML = '<option value="">Loading...</option>';

                if (unitId) {
                    fetch('/wahana-by-unit/' + unitId)
                        .then(response => response.json())
                        .then(data => {
                            console.log("Respon:", data); // debug isi respon
                            let options =
                                '<option value="">-- Pilih Wahana  --</option>';
                            if (data.length === 0) {
                                options =
                                    '<option value="">Tidak ada wahana untuk unit ini</option>';
                            } else {
                                data.forEach(function(item) {
                                    options +=
                                        `<option value="${item.id}">${item.nama_wahana}</option>`;
                                });
                            }
                            wahanaSelect.innerHTML = options;
                        })
                        .catch(err => console.error("Fetch error:", err));
                } else {
                    wahanaSelect.innerHTML =
                        '<option value="">-- Pilih Unit kerja Dulu --</option>';
                }
            });
        }
    });
</script>

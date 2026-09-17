@extends('layouts.app')

@section('content')

    <div class="max-w-full mx-auto bg-white shadow-md rounded-xl p-8 mt-6">
        @php
            $themeColor = \App\Setting::get('theme_color', '#4F46E5');
        @endphp
        <form method="POST"
            action="{{ isset($target_unit) ? route('target_unit.update', $target_unit->id) : route('target_unit.store') }}"
            class="bg-white shadow-lg
            rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
            @csrf
            @if (isset($target_unit))
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
                Target Unit Create
            </h4>
            <div class="grid grid-cols-4 gap-4 text-sm">


                <div class="mb-5">
                    <label for="unit_kerja_id" class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
                    <select name="unit_kerja_id" id="unit_kerja_id"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                    <label for="level_karyawan_id" class="block text-sm font-medium text-gray-700 mb-1">Level
                        Karyawan</label>
                    <select name="level_karyawan_id" id="level_karyawan_id"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Level Karyawan --</option>
                        @foreach ($levelKaryawan as $g)
                            <option value="{{ $g->id }}">{{ $g->nama_level }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-5">
                    <label for="komponen_penghasilan_id" class="block text-sm font-medium text-gray-700 mb-1">Komponen
                        Penghasilan</label>
                    <select name="komponen_penghasilan_id" id="komponen_penghasilan_id"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Level Karyawan Dulu --</option>
                    </select>
                </div>


                <div class="mb-5">
                    <label for="target_bulanan" class="block text-sm font-medium text-gray-700 mb-1">Target Bulanan
                        (Rp)</label>
                    <input type="text" id="target_bulanan" name="target_bulanan"
                        value="{{ isset($target_unit) && is_numeric($target_unit->target_bulanan) ? number_format($target_unit->target_bulanan, 0, ',', '.') : '' }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Contoh: 4.500.000">
                </div>
                <div class="mb-5">
                    <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">Bulan
                    </label>
                    <input type="text" name="bulan"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                </div>
                <div class="mb-5">
                    <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun
                    </label>
                    <input type="text" name="tahun"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                </div>
                <div class="mb-5">
                    <label for="besaran_nominal" class="block text-sm font-medium text-gray-700 mb-1">Besaran Nominal
                        (Rp)</label>
                    <input type="text" id="besaran_nominal" name="besaran_nominal"
                        value="{{ isset($target_unit) && is_numeric($target_unit->besaran_nominal) ? number_format($target_unit->besaran_nominal, 0, ',', '.') : '' }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Contoh: 4.500.000">
                </div>
            </div>


            {{-- Tombol --}}
            <div class="flex justify-end">
                <a href="{{ route('target_unit.index') }}"
                    class="mr-3 inline-block px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-block px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                    {{ isset($target_unit) ? '💾 Update' : ' Process' }}
                </button>
            </div>
        </form>
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

        formatRupiah('target_bulanan');
        formatRupiah('besaran_nominal');
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const levelSelect = document.getElementById('level_karyawan_id');
        const komponenSelect = document.getElementById('komponen_penghasilan_id');

        if (levelSelect && komponenSelect) {
            levelSelect.addEventListener('change', function() {
                let levelId = this.value;
                komponenSelect.innerHTML = '<option value="">Loading...</option>';

                if (levelId) {
                    fetch('/komponen-by-level/' + levelId)
                        .then(response => response.json())
                        .then(data => {
                            console.log("Respon:", data); // debug isi respon
                            let options =
                                '<option value="">-- Pilih Komponen Penghasilan --</option>';
                            if (data.length === 0) {
                                options =
                                    '<option value="">Tidak ada komponen untuk level ini</option>';
                            } else {
                                data.forEach(function(item) {
                                    options +=
                                        `<option value="${item.id}">${item.nama_komponen}</option>`;
                                });
                            }
                            komponenSelect.innerHTML = options;
                        })
                        .catch(err => console.error("Fetch error:", err));
                } else {
                    komponenSelect.innerHTML =
                        '<option value="">-- Pilih Level Karyawan Dulu --</option>';
                }
            });
        }
    });
</script>

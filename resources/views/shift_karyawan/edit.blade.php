@extends('layouts.app')

@section('content')
    <div class="py-10 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">

        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form method="POST"
                    action="{{ isset($shift_karyawan) ? route('shift_karyawan.update', $shift_karyawan->id) : route('shift_karyawan.store') }}">
                    @csrf
                    @if (isset($shift_karyawan))
                        @method('PUT')
                    @endif

                    {{-- Error --}}
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
                        Scheduling Personnel Edit
                    </h4>
                    {{-- PTKP --}}
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div class="mb-2">
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Karyawan</label>
                            <select name="employee_id" id="employee_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih jenis hari --</option>
                                @foreach ($karyawan as $g)
                                    <option value="{{ $g->id }}"
                                        {{ isset($shift_karyawan) && $shift_karyawan->employee_id == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama_karyawan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label for="unit_kerja_id" class="block text-sm font-medium text-gray-700 mb-1">Unit
                                Kerja</label>
                            <select name="unit_kerja_id" id="unit_kerja_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Unit Kerja --</option>
                                @foreach ($unitKerja as $g)
                                    <option value="{{ $g->id }}"
                                        {{ isset($shift_karyawan) && $shift_karyawan->unit_kerja_id == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label for="wahana_id" class="block text-sm font-medium text-gray-700 mb-1">Wahana</label>
                            <select name="wahana_id" id="wahana_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Wahana --</option>
                            </select>
                        </div>

                        {{-- bulan --}}
                        <div class="mb-6">
                            <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal

                            </label>
                            <input type="date" id="tanggal" name="tanggal" value="{{ $shift_karyawan->tanggal ?? '' }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="1.25">
                        </div>
                        <div class="mb-2">
                            <label for="jenis_hari_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis
                                Hari</label>
                            <select name="jenis_hari_id" id="jenis_hari_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih jenis hari --</option>
                                @foreach ($jenisHari as $g)
                                    <option value="{{ $g->id }}"
                                        {{ isset($shift_karyawan) && $shift_karyawan->jenis_hari_id == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>



                        <div class="mb-6">
                            <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">
                                Jam Mulai

                            </label>
                            <input type="time" id="jam_mulai" name="jam_mulai"
                                value="{{ $shift_karyawan->jam_mulai ? \Carbon\Carbon::parse($shift_karyawan->jam_mulai)->format('H:i') : '' }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="mb-6">
                            <label for="jam_selesai" class="block text-sm font-medium text-gray-700 mb-1">
                                Jam Selesai

                            </label>
                            <input type="time" id="jam_selesai" name="jam_selesai"
                                value="{{ $shift_karyawan->jam_selesai ? \Carbon\Carbon::parse($shift_karyawan->jam_selesai)->format('H:i') : '' }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="mb-2">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih--</option>
                                <option value="Penetapan"
                                    {{ old('status', $shift_karyawan->status ?? '') == 'Penetapan' ? 'selected' : '' }}>
                                    Penetapan</option>
                                <option value="Perubahan"
                                    {{ old('status', $shift_karyawan->status ?? '') == 'Perubahan' ? 'selected' : '' }}>
                                    Perubahan</option>
                                <option value="Tambahan"
                                    {{ old('status', $shift_karyawan->status ?? '') == 'Tambahan' ? 'selected' : '' }}>
                                    Tambahan</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label for="posisi" class="block text-sm font-medium text-gray-700 mb-1">Posisi</label>
                            <select name="posisi" id="posisi" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih--</option>
                                <option value="petugas_1"
                                    {{ old('posisi', $shift_karyawan->posisi ?? '') == 'petugas_1' ? 'selected' : '' }}>
                                    Petugas 1</option>
                                <option value="petugas_2"
                                    {{ old('posisi', $shift_karyawan->posisi ?? '') == 'petugas_2' ? 'selected' : '' }}>
                                    Petugas 2</option>
                                <option value="petugas_3"
                                    {{ old('posisi', $shift_karyawan->posisi ?? '') == 'petugas_3' ? 'selected' : '' }}>
                                    Petugas 3</option>
                                <option value="petugas_4"
                                    {{ old('posisi', $shift_karyawan->posisi ?? '') == 'petugas_4' ? 'selected' : '' }}>
                                    Petugas 4</option>
                                <option value="pengganti"
                                    {{ old('posisi', $shift_karyawan->posisi ?? '') == 'pengganti' ? 'selected' : '' }}>
                                    Pengganti</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" value="{{ $shift_karyawan->keterangan }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $shift_karyawan->keterangan }}</textarea>
                    </div>
                    {{-- Tombol --}}
                    <div class="flex justify-end">
                        <a href="{{ route('shift_karyawan.index') }}"
                            class="mr-3 inline-block px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-block px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                            {{ isset($shift_karyawan) ? 'Process' : 'Process' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

<!-- JQUERY DULU -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        function initSelect2(selector, url, mapper, placeholder) {
            $(selector).select2({
                placeholder: placeholder,
                ajax: {
                    url: url,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(mapper)
                        };
                    },
                    cache: true
                },
                allowClear: true,
                width: '100%'
            });
        }

        // ✅ Customers
        initSelect2(
            '#customer_id',
            '{{ route('customers.search') }}',
            function(customer) {
                return {
                    id: customer.id,
                    text: customer.nama_customers
                };
            },
            "-- Customers --"
        );

        // ✅ Employees
        initSelect2(
            '#employee_id',
            '{{ route('employee.search') }}',
            function(employee) {
                return {
                    id: employee.id,
                    text: employee.nama_karyawan
                };
            },
            "-- Employees --"
        );
    });
</script>
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

        formatRupiah('target_harian');
        formatRupiah('max_penghasilan');
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const unitSelect = document.getElementById('unit_kerja_id');
        const wahanaSelect = document.getElementById('wahana_id');

        function loadKomponen(unitId, selectedId = null) {
            if (!unitId) {
                wahanaSelect.innerHTML = '<option value="">-- Pilih Unit kerja dulu --</option>';
                return;
            }

            wahanaSelect.innerHTML = '<option value="">Loading...</option>';

            fetch('/wahana-by-unit/' + unitId)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">-- Pilih unit kerja  --</option>';
                    if (data.length === 0) {
                        options = '<option value="">Tidak ada unit kerja untuk unit ini</option>';
                    } else {
                        data.forEach(function(item) {
                            options +=
                                `<option value="${item.id}" ${selectedId == item.id ? 'selected' : ''}>${item.nama_wahana}</option>`;
                        });
                    }
                    wahanaSelect.innerHTML = options;
                })
                .catch(err => console.error("Fetch error:", err));
        }

        if (unitSelect) {
            // Event ketika user ubah level
            unitSelect.addEventListener('change', function() {
                loadKomponen(this.value);
            });

            // Jika halaman edit, otomatis load komponen sesuai level yang tersimpan
            @if (isset($shift_karyawan) && $shift_karyawan->unit_kerja_id)
                loadKomponen("{{ $shift_karyawan->unit_kerja_id }}",
                    "{{ $shift_karyawan->wahana_id }}");
            @endif
        }
    });
</script>

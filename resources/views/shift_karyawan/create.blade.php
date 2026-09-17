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
                        Scheduling Personnel Create
                    </h4>
                    <div class="grid grid-cols-4 gap-4 text-sm">

                        <div class="mb-2">
                            <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                            </label>
                            <input type="date" name="tanggal" id="tanggal"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>

                        <div class="mb-2">
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                            <select name="employee_id" id="employee_id"
                                class="select2 w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Employee --</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label for="unit_kerja_id" class="block text-sm font-medium text-gray-700 mb-1">Unit
                                Kerja</label>
                            <select name="unit_kerja_id" id="unit_kerja_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Unit Kerja --</option>
                                @foreach ($unitKerja as $g)
                                    <option value="{{ $g->id }}"
                                        {{ isset($target_unit) && $target_unit->unit_kerja_id == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label for="wahana_id" class="block text-sm font-medium text-gray-700 mb-1">Wahana</label>
                            <select name="wahana_id" id="wahana_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Wahana --</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label for="jenis_hari_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis
                                Hari</label>
                            <select name="jenis_hari_id" id="jenis_hari_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Jenis Hari --</option>
                            </select>
                        </div>



                        <div class="mb-2">
                            <label for="jam_mulai" class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai
                            </label>
                            <input type="time" name="jam_mulai"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div class="mb-2">
                            <label for="jam_selesai" class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai
                            </label>
                            <input type="time" name="jam_selesai"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div class="mb-2">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
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

                            <select name="crew_id" id="crew_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Crew --</option>
                                @foreach ($crew as $g)
                                    <option value="{{ $g->id }}"
                                        {{ isset($shift_karyawan) && $shift_karyawan->crew_id == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <textarea name="keterangan" id="keterangan"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2  focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    {{-- Tombol --}}
                    <div class="flex justify-end">
                        <a href="{{ route('shift_karyawan.index') }}"
                            class="mr-3 inline-block px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-block px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                            {{ isset($shift_karyawan) ? '💾 Update' : 'Process' }}
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
    document.addEventListener('DOMContentLoaded', function() {
        const unitSelect = document.getElementById('unit_kerja_id');
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
    });
</script>
<script>
    $(document).ready(function() {

        // 🔒 Awal: disable dropdown sebelum tanggal dipilih
        $('#employee_id').prop('disabled', true);

        // 🧭 Inisialisasi Select2 dengan AJAX search
        $('#employee_id').select2({
            placeholder: '-- Pilih Employee --',
            allowClear: true,
            width: '100%',
            ajax: {
                url: "{{ route('karyawan.available') }}", // route ke controller
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        tanggal: $('#tanggal').val(), // ambil tanggal yang dipilih
                        search: params.term // keyword dari pencarian user
                    };
                },
                processResults: function(data) {
                    // ubah data JSON dari controller jadi format yang dipahami Select2
                    return {
                        results: data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.nama_panggilan
                            };
                        })
                    };
                },
                cache: true
            },
            language: {
                inputTooShort: function() {
                    return 'Ketik minimal 1 huruf untuk mencari karyawan';
                },
                searching: function() {
                    return 'Mencari...';
                },
                noResults: function() {
                    return 'Tidak ada karyawan tersedia';
                }
            }
        });

        // 📅 Ketika tanggal berubah
        $('#tanggal').on('change', function() {
            const tanggal = $(this).val();

            if (tanggal) {
                // Aktifkan dropdown
                $('#employee_id').prop('disabled', false);

                // Reset isi dropdown tiap kali tanggal berubah
                $('#employee_id').empty().append('<option value="">-- Pilih Employee --</option>');

                // Reset tampilan Select2
                $('#employee_id').val(null).trigger('change');
            } else {
                // Jika tanggal dikosongkan, disable lagi dropdown-nya
                $('#employee_id').prop('disabled', true);
                $('#employee_id').val(null).trigger('change');
            }
        });

    });
</script>

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
        // initSelect2(
        //     '#employee_id',
        //     '{{ route('employee.search') }}',
        //     function(employee) {
        //         return {
        //             id: employee.id,
        //             text: employee.nama_karyawan
        //         };
        //     },
        //     "-- Employees --"
        // );
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

        formatRupiah('shift_karyawan');
        formatRupiah('max_penghasilan');
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const unitSelect = document.getElementById('unit_kerja_id');
        const wahanaSelect = document.getElementById('wahana_id');

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

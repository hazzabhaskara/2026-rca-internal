@extends('layouts.app')

@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
                $themeColorSecondary = \App\Setting::get('theme_secondary_color', '#4F46E5');
            @endphp

            @if ($errors->any())
                <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <form method="POST"
                    action="{{ isset($pengajuan) ? route('pengajuan.update', $pengajuan->id) : route('pengajuan.store') }}">
                    @csrf
                    @if (isset($pengajuan))
                        @method('PUT')
                    @endif
                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Create Approval Step
                    </h4>
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <label for="" class="block font-medium mb-1">No Pengajuan</label>
                            <input type="text" class="w-full border bg-gray-100 rounded px-2 py-1 text-sm"
                                placeholder="Generate otomatis sistem" readonly>
                        </div>
                        <div>
                            <label for="tgl_pengajuan" class="block font-medium mb-1">Tanggal Pengajuan</label>
                            <input type="date" name="tgl_pengajuan" readonly
                                value="{{ old('date_order', $pengajuan->tgl_pengajuan ?? now()->toDateString()) }}"
                                class="w-full border rounded px-2 py-1 text-sm">
                        </div>
                        <div>
                            <label for="rekening" class="block font-medium mb-1">Rekening</label>
                            <select name="no_rek_id" id="rekening" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">--Pilih</option>
                                @foreach ($rekening as $rek)
                                    <option value="{{ $rek->id }}"
                                        {{ old('no_rek_id', $pengajuan->no_rek_id ?? '') == $rek->id ? 'selected' : '' }}>
                                        {{ $rek->atas_nama }} - {{ $rek->nama_bank }} - {{ $rek->no_rek }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="keterangan" class="block font-medium mb-1">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="w-full border rounded px-2 py-1 text-sm"
                                placeholder="Masukkan keterangan pengajuan(opsional)"></textarea>
                        </div>
                    </div>
                    <div class="overflow-x-auto border rounded-lg shadow-sm mt-6">
                        <table class="w-full border-collapse border text-sm whitespace-nowrap">
                            <thead
                                class="bg-gradient-to-r bg-[{{ $themeColor }}]  to-blue-600 text-white text-sm font-semibold">
                                <tr>
                                    <th style="padding: 12px; border: 1px solid #ddd; width: 50px;">No.</th>
                                    <th style="padding: 12px; border: 1px solid #ddd;">Account</th>
                                    <th style="padding: 5px; border: 1px solid #ddd;">Qty</th>
                                    <th style="padding: 5px; border: 1px solid #ddd;">Harga</th>
                                    <th style="padding: 12px; border: 1px solid #ddd;">Keterangan</th>
                                    <th style="padding: 12px; border: 1px solid #ddd; width: 70px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-approval">
                                <tr>
                                    <td style="padding: 12px; border: 1px solid #ddd;">1</td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select name="account_id[]" id="account_id"
                                            class="w-full form-select border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Pilih Account --</option>
                                            @foreach ($account as $g)
                                                <option value="{{ $g->id }}"
                                                    {{ isset($pengajuan) && $pengajuan->account_id == $g->id ? 'selected' : '' }}>
                                                    {{ $g->kode_akun }}-{{ $g->nama_akun }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid hsl(0, 0%, 87%);">
                                        <input type="text" name="qty[]" placeholder="Masukkan qty"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid hsl(0, 0%, 87%);">
                                        <input type="text" name="harga[]" placeholder="Masukkan harga"
                                            class="w-full number-format border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid hsl(0, 0%, 87%);">
                                        <input type="text" name="uraian[]" placeholder="Masukkan uraian"
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
                                style="background-color:{{ $themeColorSecondary }}; color: white; padding: 10px 16px; border: none; border-radius: 6px; font-size: 14px;">+
                                Tambah Baris</button>
                        </div>

                    </div>

                    <!-- Buttons -->
                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="{{ route('pengajuan.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition">
                            {{ isset($pengajuan) ? 'Process' : 'Process' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- JQUERY DULU -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.number-format');

            inputs.forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');
                    this.value = new Intl.NumberFormat('id-ID').format(value);
                });
            });
        });
    </script>
    <script>
        function tambahBaris() {
            let tbody = document.getElementById('tbody-approval');
            let rowCount = tbody.rows.length;
            let row = tbody.insertRow();

            row.innerHTML = `
        <td style="padding: 12px; border: 1px solid #ddd;"></td>
        <td style="padding: 12px; border: 1px solid #ddd;">
        <select name="account_id[]" id="account_id"
                                            class="w-full form-select border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                          <option value="">-- Pilih Account --</option>
                                            @foreach ($account as $g)
                                                <option value="{{ $g->id }}"
                                                    {{ isset($pengajuan) && $pengajuan->account_id == $g->id ? 'selected' : '' }}>
                                                    {{ $g->kode_akun }}-{{ $g->nama_akun }}
                                                </option>
                                            @endforeach
                                        </select>
        </td>
           <td style="padding: 12px; border: 1px solid hsl(0, 0%, 87%);">
                                        <input type="text" name="qty[]" placeholder="Masukkan qty"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid hsl(0, 0%, 87%);">
                                        <input type="text" name="harga[]" placeholder="Masukkan harga"
                                            class="w-full number-format border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid hsl(0, 0%, 87%);">
                                        <input type="text" name="uraian[]" placeholder="Masukkan uraian"
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
            let tbody = document.getElementById('tbody-approval');
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
        $(document).ready(function() {
            $('#rekening').select2({
                placeholder: "-- Rekening --",
                ajax: {
                    url: '{{ route('rekening.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(rekening) {
                                return {
                                    id: rekening.id,
                                    text: `${rekening.atas_nama} - ${rekening.nama_bank} (${rekening.no_rek})`
                                };
                            })
                        };
                    },
                    cache: true
                },
                allowClear: true,
                width: '100%'
            });
        });
    </script>

@endsection

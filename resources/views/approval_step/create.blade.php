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
                    action="{{ isset($approval_step) ? route('approval_step.update', $approval_step->id) : route('approval_step.store') }}">
                    @csrf
                    @if (isset($approval_step))
                        @method('PUT')
                    @endif
                    <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                        Create Approval Step
                    </h4>
                    <div class="overflow-x-auto border rounded-lg shadow-sm mt-6">
                        <table class="w-full border-collapse border text-sm whitespace-nowrap">
                            <thead
                                class="bg-gradient-to-r bg-[{{ $themeColor }}]  to-blue-600 text-white text-sm font-semibold">
                                <tr>
                                    <th style="padding: 12px; border: 1px solid #ddd; width: 50px;">No.</th>
                                    <th style="padding: 12px; border: 1px solid #ddd;">Jabatan</th>
                                    <th style="padding: 12px; border: 1px solid #ddd;">Urutan</th>
                                    <th style="padding: 12px; border: 1px solid #ddd; width: 70px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-approval">
                                <tr>
                                    <td style="padding: 12px; border: 1px solid #ddd;">1</td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <select name="jabatan_id[]" id="unit_kerja_id"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Pilih Jabatan --</option>
                                            @foreach ($jabatan as $g)
                                                <option value="{{ $g->id }}"
                                                    {{ isset($approval_step) && $approval_step->jabatan_id == $g->id ? 'selected' : '' }}>
                                                    {{ $g->nama_jabatan }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="padding: 12px; border: 1px solid hsl(0, 0%, 87%);">
                                        <input type="text" name="step_order[]"
                                            placeholder="Masukkan urutan dalam approval"
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
                        <a href="{{ route('approval_step.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-400 transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition">
                            {{ isset($approval_step) ? 'Process' : 'Process' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function tambahBaris() {
            let tbody = document.getElementById('tbody-approval');
            let rowCount = tbody.rows.length;
            let row = tbody.insertRow();

            row.innerHTML = `
        <td style="padding: 12px; border: 1px solid #ddd;"></td>
        <td style="padding: 12px; border: 1px solid #ddd;">
        <select name="jabatan_id[]" id="jabatan_id"
                                            class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">-- Pilih Jabatan --</option>
                                            @foreach ($jabatan as $g)
                                                <option value="{{ $g->id }}"
                                                    {{ isset($approval_step) && $approval_step->jabatan_id == $g->id ? 'selected' : '' }}>
                                                    {{ $g->nama_jabatan }}
                                                </option>
                                            @endforeach
                                        </select>
        </td>
        <td style="padding: 12px; border: 1px solid #ddd;">
            <input type="number" name="step_order[]" placeholder="Masukkan urutan dalam approval" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
@endsection

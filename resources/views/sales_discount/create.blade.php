@extends('layouts.app')

@section('content')

    <div class="max-w-full mx-auto py-10 px-6">
        <h2 class="text-2xl font-semibold mb-6">Sales Discount </h2>

        <form method="POST" action="{{ route('sales_discount.store') }}">
            @csrf

            @if ($errors->any())
                <div class="mb-4 text-red-600 bg-red-100 p-4 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-6 rounded-lg shadow space-y-6">
                <!-- Nama Diskon -->
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Diskon</label>
                    <input type="text" name="nama_diskon" class="form-input w-full border rounded px-2 py-1 text-sm"
                        required>
                </div>

                <!-- Jenis Diskon -->
                <div>
                    <label class="block text-sm font-semibold mb-1">Jenis Diskon</label>
                    <select name="jenis_diskon" id="jenis_diskon"
                        class="form-select w-full border rounded px-2 py-1 text-sm" required
                        onchange="toggleDiskonFields()">
                        <option value="">-- Pilih Jenis Diskon --</option>
                        <option value="normal">Normal</option>
                        <option value="early_payment">Early Payment</option>
                        <option value="berlapis">Berlapis</option>
                    </select>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block text-sm font-semibold mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="form-textarea w-full border rounded px-2 py-1 text-sm"></textarea>
                </div>

                <!-- Aktif -->
                <div class="flex items-center">
                    <input type="checkbox" name="aktif" id="aktif" class="form-checkbox text-blue-600 rounded">
                    <label for="aktif" class="ml-2 text-sm">Aktif</label>
                </div>

                <!-- Diskon Normal -->
                <div id="normal-value" class="hidden space-y-4 border-t pt-6">
                    <h3 class="text-lg font-bold text-gray-700">Nilai Diskon</h3>

                    <div>
                        <label class="text-sm font-medium">Tipe Nilai Diskon</label>
                        <select name="details[0][tipe_nilai]" class="form-select w-full border rounded px-2 py-1 text-sm">
                            <option value="persen">Persen</option>
                            <option value="nominal">Nominal</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Nilai Diskon</label>
                        <input type="number" step="0.01" name="details[0][nilai_diskon]"
                            class="form-input w-full border rounded px-2 py-1 text-sm">
                    </div>
                </div>

                <!-- Diskon Early Payment & Berlapis -->
                <div id="detail-section" class="hidden border-t pt-6">
                    <h3 class="text-lg font-bold mb-4 text-gray-700">Detail Diskon</h3>

                    <div id="detail-container" class="space-y-4"></div>

                    <div class="mt-3">
                        <button type="button" onclick="addDetailRow()" id="add-detail-btn"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-1 rounded-md hidden">
                            Tambah Baris Diskon
                        </button>
                    </div>
                </div>

                <!-- Tombol Simpan -->
                <div class="flex justify-end pt-4">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-md">
                        Simpan Diskon
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let index = 0;

        function toggleDiskonFields() {
            const jenis = document.getElementById('jenis_diskon').value;
            const normalValue = document.getElementById('normal-value');
            const detailSection = document.getElementById('detail-section');
            const detailContainer = document.getElementById('detail-container');
            const addButton = document.getElementById('add-detail-btn');

            // Reset
            detailContainer.innerHTML = '';
            index = 0;
            normalValue.classList.add('hidden');
            detailSection.classList.add('hidden');
            addButton.classList.add('hidden');

            if (jenis === 'normal') {
                normalValue.classList.remove('hidden');
            } else if (jenis === 'early_payment' || jenis === 'berlapis') {
                detailSection.classList.remove('hidden');
                addButton.classList.remove('hidden');
                addDetailRow(); // tambahkan satu baris awal
            }
        }

        function addDetailRow() {
            const jenis = document.getElementById('jenis_diskon').value;
            const container = document.getElementById('detail-container');
            const row = document.createElement('div');
            row.className = "grid grid-cols-6 gap-4 items-end bg-gray-50 p-4 rounded-md detail-row";

            let html = '';

            if (jenis === 'early_payment') {
                html += `
                    <div>
                        <label class="text-sm font-medium">Hari Ke</label>
                        <input type="number" name="details[${index}][hari_ke]" class="form-input w-full border rounded px-2 py-1 text-sm" required>
                    </div>
                `;
            } else {
                html += `<div></div>`;
            }

            html += `
                <div>
                    <label class="text-sm font-medium">Tipe</label>
                    <select name="details[${index}][tipe_nilai]" class="form-select w-full border rounded px-2 py-1 text-sm" required>
                        <option value="persen">Persen</option>
                        <option value="nominal">Nominal</option>
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="text-sm font-medium">Nilai Diskon</label>
                    <input type="number" step="0.01" name="details[${index}][nilai_diskon]" class="form-input w-full border rounded px-2 py-1 text-sm" required>
                </div>
            `;

            if (jenis === 'berlapis') {
                html += `
                    <div>
                        <label class="text-sm font-medium">Urutan</label>
                        <input type="number" name="details[${index}][urutan]" class="form-input w-full border rounded px-2 py-1 text-sm" required>
                    </div>
                `;
            } else {
                html += `<div></div>`;
            }

            html += `
                <div>
                    <button type="button" onclick="this.closest('.detail-row').remove()" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md">
                        Hapus
                    </button>
                </div>
            `;

            row.innerHTML = html;
            container.appendChild(row);
            index++;
        }
    </script>
@endsection

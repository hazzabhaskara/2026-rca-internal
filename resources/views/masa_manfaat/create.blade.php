@extends('layouts.app')


@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form method="POST" action="{{ route('masa_manfaat.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Jenis -->
                        <div class="mb-4">
                            <label for="jenis" class="block text-gray-700 font-medium mb-1">Jenis Aset</label>
                            <select name="jenis" id="jenis" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih --</option>
                                <option value="Tangible Asset">Tangible Asset</option>
                                <option value="Intangible Asset">Intangible Asset</option>
                            </select>
                            @error('jenis')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kelompok Harta -->
                        <div class="mb-4">
                            <label for="kelompok_harta" class="block text-gray-700 font-medium mb-1">Kelompok
                                Harta</label>
                            <select name="kelompok_harta" id="kelompok_harta" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih --</option>
                                <option value="Bangunan">Bangunan</option>
                                <option value="Bukan Bangunan">Bukan Bangunan</option>
                                <option value="Harta Tak Terwujud">Harta Tak Terwujud</option>
                            </select>
                            @error('kelompok_harta')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Tabel Golongan -->
                    <div id="tabel-golongan-wrapper" class="mt-6 hidden">
                        <h3 class="text-lg font-semibold mb-2">Detail Golongan</h3>
                        <table class="w-full border text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border p-2">Golongan</th>
                                    <th class="border p-2">Masa Manfaat (Tahun)</th>
                                    <th class="border p-2">Tarif Penyusutan (%)</th>
                                </tr>
                            </thead>
                            <tbody id="tabel-golongan-body"></tbody>
                        </table>
                    </div>

                    <!-- Submit -->
                    <div class="mt-6">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script>
        const dataGolongan = {
            'Bangunan': [{
                    nama: 'Permanen',
                    masa: 20,
                    tarif: 5.00
                },
                {
                    nama: 'Tidak Permanen',
                    masa: 10,
                    tarif: 10.00
                }
            ],
            'Bukan Bangunan': [{
                    nama: 'Kelompok 1',
                    masa: 4,
                    tarif: 25.00
                },
                {
                    nama: 'Kelompok 2',
                    masa: 8,
                    tarif: 12.50
                },
                {
                    nama: 'Kelompok 3',
                    masa: 16,
                    tarif: 6.25
                },
                {
                    nama: 'Kelompok 4',
                    masa: 20,
                    tarif: 5.00
                }
            ],
            'Harta Tak Terwujud': [{
                    nama: 'Kelompok 1',
                    masa: 4,
                    tarif: 25.00
                },
                {
                    nama: 'Kelompok 2',
                    masa: 8,
                    tarif: 12.50
                },
                {
                    nama: 'Kelompok 3',
                    masa: 16,
                    tarif: 6.25
                },
                {
                    nama: 'Kelompok 4',
                    masa: 20,
                    tarif: 5.00
                }
            ]
        };

        const kelompokOptions = {
            'Tangible Asset': ['Bangunan', 'Bukan Bangunan'],
            'Intangible Asset': ['Harta Tak Terwujud']
        };

        document.addEventListener('DOMContentLoaded', function() {
            const jenisSelect = document.getElementById('jenis');
            const kelompokSelect = document.getElementById('kelompok_harta');
            const wrapper = document.getElementById('tabel-golongan-wrapper');
            const tbody = document.getElementById('tabel-golongan-body');

            function populateKelompokOptions() {
                const jenis = jenisSelect.value;
                kelompokSelect.innerHTML = '<option value="">-- Pilih --</option>';
                if (kelompokOptions[jenis]) {
                    kelompokOptions[jenis].forEach(option => {
                        const selected = option === kelompokSelect.dataset.selected ? 'selected' : '';
                        kelompokSelect.innerHTML +=
                            `<option value="${option}" ${selected}>${option}</option>`;
                    });
                }
            }

            function renderGolongan() {
                const tipe = kelompokSelect.value;
                tbody.innerHTML = '';
                if (dataGolongan[tipe]) {
                    wrapper.classList.remove('hidden');
                    dataGolongan[tipe].forEach((item, index) => {
                        const row = `
                        <tr>
                            <td class="border px-2 py-1">
                                <input type="text" name="golongan[${index}][nama]" value="${item.nama}" class="w-full border px-2 py-1">
                            </td>
                            <td class="border px-2 py-1 text-center">
                                <input type="number" name="golongan[${index}][masa]" value="${item.masa}" class="w-full border px-2 py-1">
                            </td>
                            <td class="border px-2 py-1 text-center">
                                <input type="number" step="0.01" name="golongan[${index}][tarif]" value="${item.tarif.toFixed(2)}" class="w-full border px-2 py-1">
                            </td>
                        </tr>
                    `;
                        tbody.innerHTML += row;
                    });
                } else {
                    wrapper.classList.add('hidden');
                }
            }

            jenisSelect.addEventListener('change', () => {
                populateKelompokOptions();
                wrapper.classList.add('hidden');
                tbody.innerHTML = '';
            });

            kelompokSelect.addEventListener('change', renderGolongan);

            // Init if editing
            if (jenisSelect.value) {
                populateKelompokOptions();
                if (kelompokSelect.value) {
                    renderGolongan();
                }
            }
        });
    </script>
@endsection

@extends('layouts.app')

@section('content')

    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div id="tabs" class="type-section">
                <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                    <li><a href="#select_item" class="tab-link active">Proces Closing Harian</a></li>
                    <li><a href="#journal_report" class="tab-link">Journal report</a></li>
                </ul>
            </div>
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp

            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <h4 class="font-semibold text-lg text-gray-800 mt-8 mb-4 border-l-4 border-blue-500 pl-2">
                    Closing Harian Create
                </h4>
                <form method="POST" enctype="multipart/form-data"
                    action="{{ isset($data) ? route('closing_harian.update', $data->id) : route('closing_harian.store') }}">

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


                    <div id="select_item" class="tab-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                                <input type="date" name="tanggal" class="w-full px-4 py-2 border">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
                                <select id="unit_kerja_id" name="unit_kerja_id"
                                    class="w-full rounded-md bg-blue-200 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                    <option value="">-- Pilih Unit Kerja --</option>
                                    @foreach ($unitKerja as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('unit_kerja_id', $data->unit_kerja_id ?? '') == $level->id ? 'selected' : '' }}>
                                            {{ $level->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="jumlah_pengunjung" class="block text-sm font-medium text-gray-700 mb-1">Jumlah
                                    Pengunjung</label>
                                <input type="number" name="jumlah_pengunjung" class="w-full px-4 py-2 border"
                                    placeholder="Masukkan Jumlah Pengunjung">
                            </div>
                        </div>
                        <!-- Tabel Komponen Penghasilan Berdasarkan Level -->
                        <div id="komponenContainer" class="mt-8 hidden">
                            <h3 class="text-lg font-semibold mb-4">Closing Harian</h3>
                            <div id="komponenTable"></div>
                        </div>
                    </div>


                    <div id="journal_report" class="tab-content hidden">
                        <h2 class="text-lg font-semibold mb-4">Journal Report</h2>
                        <table class="w-full border border-gray-300 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border text-left px-2 py-1">Account</th>
                                    <th class="border text-right px-2 py-1">Debit</th>
                                    <th class="border text-right px-2 py-1">Credit</th>
                                    <th class="border text-left px-2 py-1">Comment</th>
                                </tr>
                            </thead>
                            <tbody class="journal-body">
                                <tr>
                                    <td colspan="3" class="border text-center py-2 text-gray-500">
                                        Tidak ada journal
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50 font-semibold">
                                <tr>
                                    <td class="border px-2 py-1 text-right">Total</td>
                                    <td class="border px-2 py-1 text-right total-debit">0.00</td>
                                    <td class="border px-2 py-1 text-right total-credit">0.00</td>
                                    <td class="border px-2 py-1 text-right total-credit"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- Buttons -->
                    <div class="mt-6 justify-end  flex space-x-4">
                        <a href="{{ route('closing_harian.index') }}"
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

    <style>
        table,
        th,
        td {
            border: 2px solid black !important;
            border-collapse: collapse;
        }
    </style>

    <script>
        // Tab switching
        document.querySelectorAll('.tab-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                // Reset semua tab
                document.querySelectorAll('.tab-link').forEach(el => el.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));

                // Aktifkan tab yang diklik
                this.classList.add('active');
                const target = document.querySelector(this.getAttribute('href'));
                target.classList.remove('hidden');
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let unitName = '';
            const unitSelect = document.getElementById('unit_kerja_id');
            const komponenContainer = document.getElementById('komponenContainer');
            const komponenTable = document.getElementById('komponenTable');

            unitSelect.addEventListener('change', function() {
                const unitId = this.value;
                unitName = unitSelect.options[unitSelect.selectedIndex].text;

                komponenTable.innerHTML = '';
                komponenContainer.classList.add('hidden');

                if (!unitId) return;

                fetch(`/get-linked-account-by-unit/${unitId}`)
                    .then(res => res.json())
                    .then(data => {
                        // console.log('🔍 Data linked accounts dari server:',
                        //     data); // <– tampilkan seluruh hasil JSON

                        linkedAccountsMap = {};
                        data.linkedAccounts.forEach(acc => {
                            linkedAccountsMap[acc.kode] = {
                                id: acc.id,
                                kode: acc.akun.split(' - ')[0] || '-',
                                name: acc.akun.split(' - ')[1] || '-',
                                departemen: acc.departemen
                            };
                            generateJournalPreviewFormat2();
                        });

                        // console.log('🧭 Hasil mapping ke linkedAccountsMap:',
                        //     linkedAccountsMap); // <– tampilkan hasil akhir mapping
                    })
                    .catch(error => {
                        // console.error('❌ Error saat fetch linked account:', error);
                    });


                fetch(`/get-wahana-by-unit/${unitId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data || !data.wahana || data.wahana.length === 0) return;

                        const format = parseInt(data.format_closing);
                        let html = '';

                        if (format === 1 || format === 3) {
                            html = generateFormat1(data.wahana, unitName);
                        } else if (format === 2 || format === 4) {
                            html = generateFormat2(data.wahana, unitName);
                        } else {
                            html =
                                `<p class="text-red-600 font-semibold mt-2">Format closing tidak di ada.</p>`;
                        }

                        komponenTable.innerHTML = html;
                        komponenContainer.classList.remove('hidden');

                        // ⬇️ Tambahan baru — pasang listener setelah tabel dirender
                        if (format === 1 || format === 3) {
                            attachListeners(data.wahana);
                        } else if (format === 2 || format === 4)
                            attachListeners2(data.wahana);
                    });
            });

            // ============================================================
            // FORMAT 1: Sederhana (dibersihkan, tanpa event listener)
            // ============================================================
            function generateFormat1(wahanaList, unitName = '') {
                if (!Array.isArray(wahanaList) || wahanaList.length === 0) {
                    return `
                <div class="p-4 text-center text-gray-600 bg-gray-100 rounded-lg border border-gray-300">
                    <p>Tidak ada data wahana untuk unit <strong>${unitName || '-'}</strong>.</p>
                </div>`;
                }

                const getItems = w => w.wahanaItem || w.wahana_item || [];

                const fmt = num => {
                    if (num === null || num === '' || isNaN(num)) return '';
                    return new Intl.NumberFormat('id-ID').format(num);
                };

                const allItemNames = [...new Set(
                    wahanaList.flatMap(w => getItems(w).map(i => i.nama_item))
                )];



                if (allItemNames.length === 0) {
                    return `
                <div class="p-4 text-center text-gray-600 bg-gray-100 rounded-lg border border-gray-300">
                    <p>Tidak ada item aktif pada wahana di unit <strong>${unitName || '-'}</strong>.</p>
                </div>`;
                }

                let html = `
                    <div class="overflow-x-auto">
                  <table class="table-fixed border-collapse w-full text-center text-sm">
                <thead class="bg-blue-300 text-black">
                  <tr>
        <th rowspan="2" class="px-2 py-1 w-[120px] bg-blue-200">Wahana</th>

        <!-- kolom item -->
        <!-- total lebar tiap 3 kolom item ≈ 200px -->
        ${allItemNames.map(item => `
                                      <th colspan="3" class="px-2 py-1 w-[200px]">${item}</th>
                                    `).join('')}

        <th rowspan="2" class="px-2 py-1 text-xs w-[120px] bg-green-200">TOTAL OMSET</th>
        <th colspan="2" class="px-2 py-1 text-xs bg-yellow-200 w-[180px]">PAYMENT TYPE<br>DARI PENGUNJUNG</th>
        <th colspan="2" class="px-2 py-1 text-xs bg-gray-200 w-[180px]">PEMBAGIAN<br>SHARING OMSET</th>
        <th rowspan="2" class="px-2 py-1 text-xs w-[150px] bg-pink-200">LEBIH (KURANG)<br>DANA CASH<br>SETOR TUNAI KE<br>MERCHANDISE</th>
      </tr>
      <tr>
        ${allItemNames.map(() => `
                                      <th class="px-2 py-1 text-xs w-[40px]">QTY</th>
                                      <th class="px-2 py-1 text-xs w-[70px]">HARGA</th>
                                      <th class="px-2 py-1 text-xs w-[80px]">JUMLAH</th>
                                    `).join('')}

        <th class="px-2 py-1 text-xs w-[100px]">QRIS</th>
        <th class="px-2 py-1 text-xs w-[100px]">CASH</th>
        <th class="px-2 py-1 text-xs w-[100px]">MERCH</th>
        <th class="px-2 py-1 text-xs w-[100px]">RCA</th>
      </tr></thead><tbody>`;

                wahanaList.forEach((wahana, idx) => {
                    const items = getItems(wahana);
                    html +=
                        `<tr><td class=" font-semibold bg-blue-50">${wahana.nama_wahana || '-'}</td>`;

                    allItemNames.forEach((itemName, iidx) => {
                        const item = items.find(i => i.nama_item === itemName);
                        const harga = item?.harga ?? '';
                        const accId = item?.account?.id ?? '';
                        const accKode = item?.account?.kode_akun ?? '';
                        const accNama = item?.account?.nama_akun ?? '';
                        const departemenNama = item?.departemen?.deskripsi ?? '';
                        const itemId = item?.id ?? '';



                        html += `
                                <td class="">
                    <input type="hidden" name="details[${idx}][items][${iidx}][item_id]" value="${itemId}">
                    <input type="number" name="details[${idx}][items][${iidx}][qty]" class="qty-${idx}-${iidx} w-full text-center" 
                            min="0"
                             />
                    </td>

                <td class="">
                    <input type="text" name="details[${idx}][items][${iidx}][harga]" class="harga-${idx}-${iidx} w-full text-right" value="${fmt(harga)}" />
                </td>
                <td class="">
                    <input type="text" name="details[${idx}][items][${iidx}][jumlah]" class="jumlah-${idx}-${iidx} w-full text-right bg-pink-100 font-bold"
                    data-account-id="${accId}" 
                            data-account-kode="${accKode}" 
                            data-item-name="${itemName}"
                            data-account-nama="${accNama}"
                            data-departemen-nama="${departemenNama}"
                             readonly />
                </td>`;
                    });

                    html += `
            <td class=" bg-green-100">
                <input type="text" name="details[${idx}][omset_total]" class="total-${idx} w-full bg-green-200 text-right font-bold" readonly />
            </td>
            <td class=" bg-yellow-100">
                <input type="text" name="details[${idx}][qris]" class="qris-${idx} w-full bg-amber-400 text-right" />
            </td>
            <td class=" bg-yellow-100">
                <input type="text" name="details[${idx}][cash]" class="cash-${idx} w-full bg-amber-200 text-right" readonly />
            </td>
            <td class=" bg-gray-100">
                <input type="text" name="details[${idx}][merch]" class="merch-${idx} w-full text-right" readonly />
            </td>
            <td class=" bg-gray-100">
                <input type="text" name="details[${idx}][rca]" class="rca-${idx} w-full text-right" readonly />
            </td>
            <td class=" bg-pink-100">
                <input type="text" name="details[${idx}][lebih_kurang]" class="lebih-${idx} w-full text-right" readonly />
            </td>
        </tr>`;
                });

                html += `</tbody>
                <tfoot class="bg-blue-100 font-bold">
        <tr>
            <td class=" text-right p-2">TOTAL</td>`;
                allItemNames.forEach(() => {
                    html += `<td colspan="3"></td>`;
                });
                html += `
        <td><input type="text" id="grand-total" class="w-full text-right  font-bold" readonly /></td>
        <td><input type="text" id="grand-qris" class="w-full text-right  font-bold" readonly /></td>
        <td><input type="text" id="grand-cash" class="w-full text-right  font-bold" readonly /></td>
        <td><input type="text" id="grand-merch" class="w-full text-right  font-bold" readonly /></td>
        <td><input type="text" id="grand-rca" class="w-full text-right  font-bold" readonly /></td>
        <td><input type="text" id="grand-lebih" class="w-full text-right  font-bold" readonly /></td>
        </tr>
     </tfoot></table></div>`;
                return html;
                generateJournalPreview();
            }

            // ============================================================
            // FUNGSI TAMBAHAN: attachListeners untuk hitung otomatis
            // ============================================================
            function attachListeners(wahanaList) {
                const getItems = w => w.wahanaItem || w.wahana_item || [];
                const fmt = num => new Intl.NumberFormat('id-ID').format(num);
                const toNum = val => parseFloat((val || '').toString().replace(/\./g, '').replace(',', '.')) || 0;

                const allItemNames = [...new Set(
                    wahanaList.flatMap(w => getItems(w).map(i => i.nama_item))
                )];

                // Fungsi untuk menghitung total keseluruhan di tfoot
                const updateGrandTotal = () => {
                    let gTotal = 0,
                        gQris = 0,
                        gCash = 0,
                        gMerch = 0,
                        gRca = 0,
                        gLebih = 0;
                    wahanaList.forEach((_, idx) => {
                        gTotal += toNum(document.querySelector(`.total-${idx}`)?.value);
                        gQris += toNum(document.querySelector(`.qris-${idx}`)?.value);
                        gCash += toNum(document.querySelector(`.cash-${idx}`)?.value);
                        gMerch += toNum(document.querySelector(`.merch-${idx}`)?.value);
                        gRca += toNum(document.querySelector(`.rca-${idx}`)?.value);
                        gLebih += toNum(document.querySelector(`.lebih-${idx}`)?.value);
                    });
                    document.getElementById('grand-total').value = fmt(gTotal);
                    document.getElementById('grand-qris').value = fmt(gQris);
                    document.getElementById('grand-cash').value = fmt(gCash);
                    document.getElementById('grand-merch').value = fmt(gMerch);
                    document.getElementById('grand-rca').value = fmt(gRca);
                    document.getElementById('grand-lebih').value = fmt(gLebih);
                };

                wahanaList.forEach((wahana, idx) => {
                    const qrisField = document.querySelector(`.qris-${idx}`);
                    const cashField = document.querySelector(`.cash-${idx}`);
                    const merchField = document.querySelector(`.merch-${idx}`);
                    const rcaField = document.querySelector(`.rca-${idx}`);
                    const lebihField = document.querySelector(`.lebih-${idx}`);
                    const totalField = document.querySelector(`.total-${idx}`);

                    const updateTotal = () => {
                        let total = 0;
                        allItemNames.forEach((__, ii) => {
                            const jml = document.querySelector(`.jumlah-${idx}-${ii}`);
                            total += toNum(jml?.value);
                        });

                        if (totalField) totalField.value = fmt(total);

                        const qris = toNum(qrisField?.value);
                        const cash = Math.max(0, total - qris);
                        if (cashField) cashField.value = fmt(cash);

                        const merch = total * 0.45;
                        if (merchField) merchField.value = fmt(merch);

                        const rca = total * 0.55;
                        if (rcaField) rcaField.value = fmt(rca);

                        const lebihKurang = cash - merch;
                        if (lebihField) lebihField.value = fmt(lebihKurang);

                        // update tfoot total
                        updateGrandTotal();
                        generateJournalPreview();

                    };

                    allItemNames.forEach((_, iidx) => {
                        const qtyInput = document.querySelector(`.qty-${idx}-${iidx}`);
                        const hargaInput = document.querySelector(`.harga-${idx}-${iidx}`);
                        const jumlahInput = document.querySelector(`.jumlah-${idx}-${iidx}`);
                        if (!qtyInput || !hargaInput) return;

                        const recalc = () => {
                            const qty = toNum(qtyInput.value);
                            const harga = toNum(hargaInput.value);
                            const jumlah = qty * harga;
                            jumlahInput.value = fmt(jumlah);
                            updateTotal();
                        };

                        qtyInput.addEventListener('input', recalc);
                        hargaInput.addEventListener('input', recalc);
                        hargaInput.addEventListener('blur', () => {
                            hargaInput.value = fmt(toNum(hargaInput.value));
                        });
                    });

                    if (qrisField) {
                        qrisField.addEventListener('focus', () => {
                            qrisField.value = toNum(qrisField.value);
                        });
                        qrisField.addEventListener('input', updateTotal);
                        qrisField.addEventListener('blur', () => {
                            qrisField.value = fmt(toNum(qrisField.value));
                            updateTotal();
                        });
                    }

                    updateTotal();
                    generateJournalPreview();

                });
            }

            // ============================================================
            // FORMAT 2 (original dari kamu, tidak diubah)
            // ============================================================
            function generateFormat2(wahanaList, unitName = '') {
                if (!Array.isArray(wahanaList) || wahanaList.length === 0) {
                    return `
                <div class="p-4 text-center text-gray-600 bg-gray-100 rounded-lg border border-gray-300">
                    <p>Tidak ada data wahana untuk unit <strong>${unitName || '-'}</strong>.</p>
                </div>`;
                }

                const getItems = w => w.wahanaItem || w.wahana_item || [];

                const fmt = num => {
                    if (num === null || num === '' || isNaN(num)) return '';
                    return new Intl.NumberFormat('id-ID').format(num);
                };

                const allItemNames = [...new Set(
                    wahanaList.flatMap(w => getItems(w).map(i => i.nama_item))
                )];

                if (allItemNames.length === 0) {
                    return `
                <div class="p-4 text-center text-gray-600 bg-gray-100 rounded-lg border border-gray-300">
                    <p>Tidak ada item aktif pada wahana di unit <strong>${unitName || '-'}</strong>.</p>
                </div>`;
                }

                let html = `
                <div class="overflow-x-auto">
                  <table class="table-fixed border-collapse w-full text-center text-sm">
    <thead class="bg-blue-300 text-black">
      <tr>
        <th rowspan="2" class="px-2 py-1 w-[120px] bg-blue-200">Wahana</th>

        <!-- kolom item -->
        <!-- total lebar tiap 3 kolom item ≈ 200px -->
        ${allItemNames.map(item => `
                                                      <th colspan="3" class="px-2 py-1 w-[200px]">${item}</th>
                                                    `).join('')}

        <th rowspan="2" class="px-2 py-1 text-xs w-[120px] bg-green-200">TOTAL OMSET</th>
        <th colspan="2" class="px-2 py-1 text-xs bg-yellow-200 w-[180px]">PAYMENT TYPE<br>DARI PENGUNJUNG</th>
        <th colspan="2" class="px-2 py-1 text-xs bg-gray-200 w-[180px]">PEMBAGIAN<br>SHARING OMSET</th>
        <th rowspan="2" class="px-2 py-1 text-xs w-[120px] bg-gray-200">TITIPAN<br>OMSET</th>
        <th rowspan="2" class="px-2 py-1 text-xs w-[150px] bg-pink-200">LEBIH (KURANG)<br>DANA CASH<br>SETOR TUNAI KE<br>MERCHANDISE</th>
      </tr>
      <tr>
        ${allItemNames.map(() => `
                                                      <th class="px-2 py-1 text-xs w-[40px]">QTY</th>
                                                      <th class="px-2 py-1 text-xs w-[70px]">HARGA</th>
                                                      <th class="px-2 py-1 text-xs w-[80px]">JUMLAH</th>
                                                    `).join('')}

        <th class="px-2 py-1 text-xs w-[100px]">QRIS</th>
        <th class="px-2 py-1 text-xs w-[100px]">CASH</th>
        <th class="px-2 py-1 text-xs w-[100px]">MERCH</th>
        <th class="px-2 py-1 text-xs w-[100px]">RCA</th>
      </tr>
    </thead>
    <tbody>`;

                wahanaList.forEach((wahana, idx) => {
                    const items = getItems(wahana);
                    html +=
                        `<tr><td class=" font-semibold text-left bg-blue-50">${wahana.nama_wahana || '-'}</td>`;

                    allItemNames.forEach((itemName, iidx) => {
                        const item = items.find(i => i.nama_item === itemName);
                        const harga = item?.harga ?? '';
                        const hargaTitipan = item?.harga_perhitungan_titipan ?? '';
                        const accId = item?.account?.id ?? '';
                        const accKode = item?.account?.kode_akun ?? '';
                        const accNama = item?.account?.nama_akun ?? '';
                        const itemId = item?.id ?? '';
                        const departemenNama = item?.departemen?.deskripsi ?? '';

                        html += `
                <td class="">
                     <input type="hidden" name="details[${idx}][items][${iidx}][item_id]" value="${itemId}">
                    <input type="number" name="details[${idx}][items][${iidx}][qty]" class="qty-${idx}-${iidx} w-full text-center" min="0"           
                    />
                </td>
                <td class="">
                    <input type="text" name="details[${idx}][items][${iidx}][harga]" class="harga-${idx}-${iidx} w-full text-right" value="${fmt(harga)}" />
                    <input type="hidden"  class="hargaTitipan-${idx}-${iidx} w-full text-right" value="${fmt(hargaTitipan)}" />
                </td>
                <td class="">
                    <input type="text" name="details[${idx}][items][${iidx}][jumlah]" class="jumlah-${idx}-${iidx} w-full text-right bg-pink-100 font-bold"
                      data-account-id="${accId}" 
                    data-account-kode="${accKode}" 
                     data-item-name="${itemName}" 
                    data-account-nama="${accNama}"
                    data-departemen-nama="${departemenNama}" readonly />
                </td>`;
                    });

                    html += `
         <td class=" bg-green-100">
                <input type="text"name="details[${idx}][omset_total]" class="total-${idx} w-full px-2 py-1 bg-green-200 text-right font-bold" readonly />
            </td>
            <td class=" bg-yellow-100">
                <input type="text" name="details[${idx}][qris]" class="qris-${idx} w-full px-2 py-1 bg-amber-400 text-right" />
            </td>
            <td class=" bg-yellow-100">
                <input type="text" name="details[${idx}][cash]" class="cash-${idx} w-full px-2 py-1 bg-amber-200 text-right" readonly />
            </td>
            <td class=" bg-gray-100">
                <input type="text" name="details[${idx}][merch]" class="merch-${idx} w-full px-2 py-1 text-right" readonly />
            </td>
            <td class=" bg-gray-100">
                <input type="text" name="details[${idx}][rca]" class="rca-${idx} w-full px-2 py-1 text-right" readonly />
            </td>
            <td class=" bg-pink-100">
                <input type="text" name="details[${idx}][titipan]" class="titipan-${idx} w-full px-2 py-1 text-right" />
            </td>
            <td class=" bg-pink-100">
                <input type="text" name="details[${idx}][lebih_kurang]" class="lebih-${idx} w-full px-2 py-1 text-right" readonly />
            </td>
        </tr>`;
                });
                html += `</tbody>
                <tfoot class="bg-blue-100 font-bold">

  <!-- SUBTOTAL (agregat semua wahana) -->
  <tr class="bg-gray-200 font-semibold">
    <td class="text-left bg-gray-300">SUBTOTAL</td>
    ${allItemNames.map((_, iidx) => `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <td><input type="text" class="subtotal-qty-${iidx} w-full text-center font-bold bg-gray-200" readonly /></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <td><input type="text" class="subtotal-harga-${iidx} w-full text-right  font-bold bg-gray-200" readonly /></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <td><input type="text" class="subtotal-jumlah-${iidx} w-full text-right font-bold bg-gray-200" readonly /></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `).join('')}
    <td><input type="text" id="subtotal-total"    class="w-full text-right bg-gray-200 font-bold" readonly /></td>
    <td><input type="text" id="subtotal-qris"     class="w-full text-right bg-gray-200 font-bold" readonly /></td>
    <td><input type="text" id="subtotal-cash"     class="w-full text-right bg-gray-200 font-bold" readonly /></td>
    <td><input type="text" id="subtotal-merch"    class="w-full text-right bg-gray-200 font-bold" readonly /></td>
    <td><input type="text" id="subtotal-rca"      class="w-full text-right bg-gray-200 font-bold" readonly /></td>
    <td><input type="text" id="subtotal-titipan"  class="w-full text-right bg-gray-200 font-bold" readonly /></td>
    <td><input type="text" id="subtotal-lebih"    class="w-full text-right bg-gray-200 font-bold" readonly /></td>
  </tr>

  <!-- DIKURANGI (placeholder baris kebijakan pengurang lain jika ada) -->
  <tr class="bg-gray-100 font-semibold">
    <td class="text-left">DIKURANGI:</td>
    ${allItemNames.map(() => `
                                                                                                                                                                                                    <td class="bg-gray-100"></td>
                                                                                                                                                                                                    <td class="bg-gray-100"></td>
                                                                                                                                                                                                    <td class="bg-gray-100"></td>
                                                                                                                                                                                                 `).join('')}
    <td class="bg-gray-100"></td> <!-- TOTAL OMSET -->
    <td class="bg-gray-100"></td> <!-- QRIS -->
    <td class="bg-gray-100"></td> <!-- CASH -->
    <td class="bg-gray-100"></td> <!-- MERCH -->
    <td class="bg-gray-100"></td> <!-- RCA -->
    <td class="bg-gray-100"></td> <!-- TITIPAN -->
    <td class="bg-gray-100"></td> <!-- LEBIH -->
  </tr>

  <!-- MDR 0,7% (dihitung dari TITIPAN agregat) -->
  <tr class="bg-gray-100 font-semibold">
    <td class="text-left">MDR 0,7%</td>
    ${allItemNames.map(() => ` 
                                                                                                                                                                                                    <td class="bg-gray-100"></td>
                                                                                                                                                                                                    <td class="bg-gray-100"></td>
                                                                                                                                                                                                    <td class="bg-gray-100"></td>
                                                                                                                                                                                               `).join('')}
    <td class="bg-gray-100"></td> <!-- TOTAL OMSET -->
    <td class="bg-gray-100"></td> <!-- QRIS -->
    <td class="bg-gray-100"></td> <!-- CASH -->
    <td class="bg-gray-100"></td> <!-- MERCH -->
    <td class="bg-gray-100"></td> <!-- RCA -->
    <td class="bg-gray-100"><input type="text" id="mdr-grand" class="w-full text-right bg-gray-100 font-bold" readonly /></td> <!-- TITIPAN -->
    <td class="bg-gray-100"><input type="text" id="mdr-grandLebih" class="w-full text-right bg-gray-100 font-bold" readonly /></td> <!-- TITIPAN -->
  </tr>

  <!-- SUBTOTAL SETELAH MDR (titipan - mdr) -->
  <tr class="bg-gray-100 font-bold">
    <td class="text-left">SUB TOTAL MDR</td>
    ${allItemNames.map(() => `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <td class="bg-gray-100"></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <td class="bg-gray-100"></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <td class="bg-gray-100"></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `).join('')}
    <td class="bg-gray-100"></td> <!-- TOTAL OMSET -->
    <td class="bg-gray-100"></td> <!-- QRIS -->
    <td class="bg-gray-100"></td> <!-- CASH -->
    <td class="bg-gray-100"></td> <!-- MERCH -->
    <td class="bg-gray-100"></td> <!-- RCA -->
    <td class="bg-gray-100"><input type="text" id="subtotal-mdr" class="w-full text-right bg-gray-100 font-bold" readonly /></td> <!-- TITIPAN -->
    <td class="bg-gray-100"></td> <!-- LEBIH -->
  </tr>

  <!-- GRAND TOTAL (sudah ada sebelumnya, dipertahankan) -->
  <tr>
    <td class="text-left font-bold">TOTAL</td>
    ${allItemNames.map(() => `<td colspan="3"></td>`).join('')}
    <td><input type="text" id="grand-total"class="w-full text-right font-bold" readonly /></td>
    <td><input type="text" id="grand-qris" class="w-full text-right font-bold" readonly /></td>
    <td><input type="text" id="grand-cash"class="w-full text-right font-bold" readonly /></td>
    <td><input type="text" id="grand-merch"class="w-full text-right font-bold" readonly /></td>
    <td><input type="text" id="grand-rca" class="w-full text-right font-bold" readonly /></td>
    <td><input type="text" id="grand-titipan"class="w-full text-right font-bold" readonly /></td>
    <td><input type="text" id="grand-lebih"  class="w-full text-right font-bold" readonly /></td>
  </tr>

</tfoot></table></div>`;
                return html;

                generateJournalPreviewFormat2();
            }


            function attachListeners2(wahanaList) {
                const getItems = w => w.wahanaItem || w.wahana_item || [];
                const fmt = num => new Intl.NumberFormat('id-ID').format(num);
                const toNum = val => parseFloat((val || '').toString().replace(/\./g, '').replace(',', '.')) || 0;

                const allItemNames = [...new Set(
                    wahanaList.flatMap(w => getItems(w).map(i => i.nama_item))
                )];
                const updateMdrGrand = () => {
                    let sTitipan = 0; // deklarasi yang benar
                    wahanaList.forEach((_, idx) => {
                        sTitipan += toNum(document.querySelector(`.titipan-${idx}`)?.value);
                    });
                    const totalMdr = sTitipan * 0.007; // 0.7%
                    const mdrField = document.getElementById('mdr-grand');
                    if (mdrField) mdrField.value = fmt(totalMdr);

                    const totalMdrLebih = sTitipan * 0.007;
                    const mdrLebihField = document.getElementById('mdr-grandLebih');
                    if (mdrLebihField) mdrLebihField.value = fmt(
                        totalMdrLebih);

                };

                const updateSubtotal = () => {
                    allItemNames.forEach((__, iidx) => {
                        let totalQty = 0,
                            totalHarga = 0,
                            totalJumlah = 0,
                            sTotal = 0,
                            sQris = 0,
                            sCash = 0,
                            sMerch = 0,
                            sRca = 0,
                            sLebih = 0;
                        sTitipan = 0;

                        wahanaList.forEach((_, idx) => {
                            const qty = toNum(document.querySelector(`.qty-${idx}-${iidx}`)
                                ?.value);
                            const harga = toNum(document.querySelector(`.harga-${idx}-${iidx}`)
                                ?.value);
                            const jumlah = toNum(document.querySelector(
                                `.jumlah-${idx}-${iidx}`)?.value);
                            sTotal += toNum(document.querySelector(`.total-${idx}`)?.value);
                            sQris += toNum(document.querySelector(`.qris-${idx}`)?.value);
                            sCash += toNum(document.querySelector(`.cash-${idx}`)?.value);
                            sMerch += toNum(document.querySelector(`.merch-${idx}`)?.value);
                            sRca += toNum(document.querySelector(`.rca-${idx}`)?.value);
                            sLebih += toNum(document.querySelector(`.lebih-${idx}`)?.value);
                            sTitipan += toNum(document.querySelector(`.titipan-${idx}`)?.value);

                            totalQty += qty;
                            totalHarga += harga;
                            totalJumlah += jumlah;

                        });
                        document.querySelector(`.subtotal-qty-${iidx}`).value = fmt(totalQty);
                        document.querySelector(`.subtotal-harga-${iidx}`).value = fmt(totalHarga);
                        document.querySelector(`.subtotal-jumlah-${iidx}`).value = fmt(totalJumlah);
                        document.getElementById('subtotal-total').value = fmt(sTotal);
                        document.getElementById('subtotal-qris').value = fmt(sQris);
                        document.getElementById('subtotal-cash').value = fmt(sCash);
                        document.getElementById('subtotal-merch').value = fmt(sMerch);
                        document.getElementById('subtotal-rca').value = fmt(sRca);
                        document.getElementById('subtotal-titipan').value = fmt(sTitipan);
                        document.getElementById('subtotal-lebih').value = fmt(sLebih);

                        updateMdrGrand();
                        generateJournalPreviewFormat2();

                    });
                };


                // Fungsi untuk menghitung total keseluruhan di tfoot
                const updateGrandTotal = () => {
                    let gTotal = 0,
                        gQris = 0,
                        gCash = 0,
                        gMerch = 0,
                        gRca = 0,
                        gLebih = 0;
                    gTitipan = 0;
                    wahanaList.forEach((_, idx) => {
                        gTotal += toNum(document.querySelector(`.total-${idx}`)?.value);
                        gQris += toNum(document.querySelector(`.qris-${idx}`)?.value);
                        gCash += toNum(document.querySelector(`.cash-${idx}`)?.value);
                        gMerch += toNum(document.querySelector(`.merch-${idx}`)?.value);
                        gRca += toNum(document.querySelector(`.rca-${idx}`)?.value);
                        gLebih += toNum(document.querySelector(`.lebih-${idx}`)?.value);
                        gTitipan += toNum(document.querySelector(`.titipan-${idx}`)?.value);
                    });
                    const subTitipan = toNum(document.getElementById('subtotal-titipan')?.value);
                    const subLebih = toNum(document.getElementById('subtotal-lebih')?.value);
                    const mdrGrand = toNum(document.getElementById('mdr-grand')?.value);

                    const grandTitipan = subTitipan - mdrGrand;
                    const grandLebih = subLebih + mdrGrand;

                    document.getElementById('grand-total').value = fmt(gTotal);
                    document.getElementById('grand-qris').value = fmt(gQris);
                    document.getElementById('grand-cash').value = fmt(gCash);
                    document.getElementById('grand-merch').value = fmt(gMerch);
                    document.getElementById('grand-rca').value = fmt(gRca);
                    document.getElementById('grand-lebih').value = fmt(grandLebih);
                    document.getElementById('grand-titipan').value = fmt(grandTitipan);


                    generateJournalPreviewFormat2();

                };

                wahanaList.forEach((wahana, idx) => {
                    const qrisField = document.querySelector(`.qris-${idx}`);
                    const titipanField = document.querySelector(`.titipan-${idx}`);
                    const cashField = document.querySelector(`.cash-${idx}`);
                    const merchField = document.querySelector(`.merch-${idx}`);
                    const rcaField = document.querySelector(`.rca-${idx}`);
                    const lebihField = document.querySelector(`.lebih-${idx}`);
                    const totalField = document.querySelector(`.total-${idx}`);

                    const updateTotal = () => {
                        let totalOmset = 0;
                        let totalTitipan = 0;

                        // 🔁 loop semua item di wahana ini
                        allItemNames.forEach((__, ii) => {
                            const qtyEl = document.querySelector(`.qty-${idx}-${ii}`);
                            const hargaEl = document.querySelector(`.harga-${idx}-${ii}`);
                            const hargaTitipanEl = document.querySelector(
                                `.hargaTitipan-${idx}-${ii}`);

                            const qty = toNum(qtyEl?.value);
                            const harga = toNum(hargaEl?.value);

                            // omset biasa
                            totalOmset += qty * harga;

                            // titipan per item (hanya kalau ada harga titipan)
                            const hargaTitipan = toNum(hargaTitipanEl?.value);
                            if (hargaTitipan > 0 && qty > 0) {
                                totalTitipan += qty * hargaTitipan;
                            }

                            // kalau kamu mau, di sini juga bisa update jumlah per item:
                            const jumlahInput = document.querySelector(`.jumlah-${idx}-${ii}`);
                            if (jumlahInput) {
                                jumlahInput.value = fmt(qty * harga);
                            }
                        });

                        // set total omset
                        if (totalField) totalField.value = fmt(totalOmset);

                        // QRIS
                        const qris = toNum(qrisField?.value);

                        // cash = omset - qris (nggak boleh minus)
                        const cash = Math.max(0, totalOmset - qris);
                        if (cashField) cashField.value = fmt(cash);

                        // merch & rca pakai rumus kamu
                        const merch = totalOmset * 0.45;
                        if (merchField) merchField.value = fmt(merch);

                        const rca = totalOmset * 0.55;
                        if (rcaField) rcaField.value = fmt(rca);

                        // ✅ titipan otomatis dari item
                        if (titipanField) {
                            titipanField.value = totalTitipan > 0 ? fmt(totalTitipan) : '';
                        }

                        // lebih / kurang
                        const lebihKurang = cash - merch - totalTitipan;
                        if (lebihField) lebihField.value = fmt(lebihKurang);

                        // fungsi global kamu
                        updateSubtotal();
                        updateGrandTotal();
                        generateJournalPreviewFormat2();
                    };

                    // 🔁 event per item
                    allItemNames.forEach((_, iidx) => {
                        const qtyInput = document.querySelector(`.qty-${idx}-${iidx}`);
                        const hargaInput = document.querySelector(`.harga-${idx}-${iidx}`);

                        if (!qtyInput || !hargaInput) return;

                        const recalc = () => {
                            // cuma ubah qty/harga ⇒ semua dihitung ulang
                            updateTotal();
                        };

                        qtyInput.addEventListener('input', recalc);
                        hargaInput.addEventListener('input', recalc);
                        hargaInput.addEventListener('blur', () => {
                            hargaInput.value = fmt(toNum(hargaInput.value));
                        });
                    });

                    // 🔁 kalau QRIS diubah, ya hitung ulang
                    if (qrisField) {
                        qrisField.addEventListener('focus', () => {
                            qrisField.value = toNum(qrisField.value);
                        });
                        qrisField.addEventListener('input', updateTotal);
                        qrisField.addEventListener('blur', () => {
                            qrisField.value = fmt(toNum(qrisField.value));
                            updateTotal();
                        });
                    }

                    // inisialisasi awal
                    updateTotal();
                    generateJournalPreviewFormat2();
                });

            }
            let linkedAccountsMap = {};

            function generateJournalPreview() {
                const journalBody = document.querySelector('.journal-body');
                journalBody.innerHTML = '';

                // --- helper lokal (selaras dengan attachListeners)
                const fmt = num => new Intl.NumberFormat('id-ID').format(num);
                const toNum = val => parseFloat((val || '').toString().replace(/\./g, '').replace(',', '.')) || 0;

                let journalRows = [];
                let totalDebit = 0;
                let totalCredit = 0;


                const piutangQrisAccount = linkedAccountsMap['Piutang Qris'] || {};
                const kasOmsetAccount = linkedAccountsMap['Kas Omset'] || {};
                const titipanOmset = linkedAccountsMap['Titipan Omset Merchandise'] || {};
                const bebanSewa = linkedAccountsMap['Beban Bagi Hasil(Sewa)'] || {};
                const pendapatanOpAccount = linkedAccountsMap['Pendapatan Non Operasional'] || {};

                // --- Debit Piutang QRIS
                const piutang = toNum($('#grand-qris').val());
                if (piutang > 0) {
                    journalRows.push({
                        accountCode: piutangQrisAccount.kode,
                        account: piutangQrisAccount.name,
                        departemen: piutangQrisAccount.departemen,
                        debit: piutang,
                        credit: 0,
                        comment: `Penjualan ${unitName || ' - '} Dibayarkan via QRIS`

                    });
                    totalDebit += piutang;
                }

                // --- Debit Kas Omset
                const kas = toNum($('#grand-cash').val());
                if (kas > 0) {
                    journalRows.push({
                        accountCode: kasOmsetAccount.kode,
                        account: kasOmsetAccount.name,
                        departemen: kasOmsetAccount.departemen,
                        debit: kas,
                        credit: 0,
                        comment: `Penjualan ${unitName || ' - '} Diterima Cash`

                    });
                    totalDebit += kas;
                }

                // --- Kredit per item (penjualan tiap wahana)
                document.querySelectorAll('[class^="jumlah-"]').forEach(input => {
                    const val = toNum(input.value);
                    if (val > 0) {
                        const accKode = input.dataset.accountKode || '';
                        const accNama = input.dataset.accountNama || '';
                        const itemName = input.dataset.itemName || '';
                        const departemenNama = input.dataset.departemenNama || '';

                        journalRows.push({
                            accountCode: accKode,
                            account: accNama,
                            departemen: departemenNama,
                            debit: 0,
                            credit: val,
                            comment: `Penjualan ${itemName}-${unitName}`

                        });
                        totalCredit += val;
                    }
                });

                // debit beban bagi hasil sewa
                const beban = toNum($('#grand-merch').val());
                if (beban > 0) {
                    journalRows.push({
                        accountCode: bebanSewa.kode,
                        account: bebanSewa.name,
                        departemen: bebanSewa.departemen,
                        debit: beban,
                        credit: 0,
                        comment: `Setor Bagi Hasil Omset ${unitName || ' - '} ke Ancol `

                    });
                    totalDebit += beban;

                    journalRows.push({
                        accountCode: kasOmsetAccount.kode,
                        account: kasOmsetAccount.name,
                        departemen: kasOmsetAccount.departemen,
                        debit: 0,
                        credit: beban,
                        comment: `Setor Bagi Hasil Omset ${unitName || ' - '} ke Ancol `

                    });
                    totalCredit += beban;
                }

                // --- Render ke tabel jurnal
                journalRows.forEach(row => {
                    const displayAccount = row.accountCode ?
                        `${row.accountCode} - ${row.account}${row.departemen ? ' (' + row.departemen + ')' : ''}` :
                        `${row.account}${row.departemen ? ' (' + row.departemen + ')' : ''}`;
                    journalBody.insertAdjacentHTML('beforeend', `
            <tr>
                <td class="border px-2 py-1">${displayAccount}</td>
                <td class="border px-2 py-1 text-right">${fmt(row.debit)}</td>
                <td class="border px-2 py-1 text-right">${fmt(row.credit)}</td>
                <td class="border px-2 py-1 text-left">${row.comment}</td>
            </tr>
        `);
                });

                // --- Update total debit/credit bawah tabel
                document.querySelector('.total-debit').textContent = fmt(totalDebit);
                document.querySelector('.total-credit').textContent = fmt(totalCredit);
            }

            function generateJournalPreviewFormat2() {
                const journalBody = document.querySelector('.journal-body');
                journalBody.innerHTML = '';

                // --- helper lokal (selaras dengan attachListeners)
                const fmt = num => new Intl.NumberFormat('id-ID').format(num);
                const toNum = val => parseFloat((val || '').toString().replace(/\./g, '').replace(',', '.')) || 0;

                let journalRows = [];
                let totalDebit = 0;
                let totalCredit = 0;

                const piutangQrisAccount = linkedAccountsMap['Piutang Qris'] || {};
                const kasOmsetAccount = linkedAccountsMap['Kas Omset'] || {};
                const titipanOmset = linkedAccountsMap['Titipan Omset Merchandise'] || {};
                const bebanSewa = linkedAccountsMap['Beban Bagi Hasil(Sewa)'] || {};
                const pendapatanOpAccount = linkedAccountsMap['Pendapatan Non Operasional'] || {};

                // --- Debit Piutang QRIS
                const piutang = toNum($('#grand-qris').val());
                if (piutang > 0) {
                    journalRows.push({
                        accountCode: piutangQrisAccount.kode,
                        account: piutangQrisAccount.name,
                        departemen: piutangQrisAccount.departemen,
                        debit: piutang,
                        credit: 0,
                        comment: `Penjualan ${unitName || ' - '} Dibayarkan via QRIS`
                    });
                    totalDebit += piutang;
                }

                // --- Debit Kas Omset
                const kas = toNum($('#grand-cash').val());
                if (kas > 0) {
                    journalRows.push({
                        accountCode: kasOmsetAccount.kode,
                        account: kasOmsetAccount.name,
                        departemen: kasOmsetAccount.departemen,
                        debit: kas,
                        credit: 0,
                        comment: `Penjualan ${unitName || ' - '} Diterima Cash`

                    });
                    totalDebit += kas;
                }
                // credit titipan omset
                const titipanOmsetCredit = toNum($('#subtotal-titipan').val());
                if (titipanOmsetCredit > 0) {
                    journalRows.push({
                        accountCode: titipanOmset.kode,
                        account: titipanOmset.name,
                        departemen: titipanOmset.departemen,
                        debit: 0,
                        credit: titipanOmsetCredit,
                        comment: `Titipan Penjualan Merchandise ${unitName || ' - '} `

                    });
                    totalCredit += titipanOmsetCredit;

                }

                // --- Kredit per item (penjualan tiap wahana)
                document.querySelectorAll('[class^="jumlah-"]').forEach(input => {
                    const val = toNum(input.value);
                    if (val > 0) {
                        const accKode = input.dataset.accountKode || '';
                        const accNama = input.dataset.accountNama || '';
                        const itemName = input.dataset.itemName || '';
                        const departemenNama = input.dataset.departemenNama || '';

                        journalRows.push({
                            accountCode: accKode,
                            account: accNama,
                            departemen: departemenNama,
                            debit: 0,
                            credit: val,
                            comment: `Penjualan ${itemName}-${unitName}`

                        });
                        totalCredit += val;
                    }
                });

                // debit beban bagi hasil sewa
                const beban = toNum($('#grand-merch').val());
                if (beban > 0) {
                    journalRows.push({
                        accountCode: bebanSewa.kode,
                        account: bebanSewa.name,
                        departemen: bebanSewa.departemen,
                        debit: beban,
                        credit: 0,
                        comment: `Setor Bagi Hasil Omset ${unitName || ' - '} ke Ancol `

                    });
                    totalDebit += beban;
                }
                // setor titipan omset debit
                const SetortitipanOmsetAccount = toNum($('#subtotal-titipan').val());
                if (SetortitipanOmsetAccount > 0) {
                    journalRows.push({
                        accountCode: titipanOmset.kode,
                        account: titipanOmset.name,
                        departemen: titipanOmset.departemen,
                        debit: SetortitipanOmsetAccount,
                        credit: 0,
                        comment: `Setor Titipan Penjualan Merchandise ${unitName || ' - '} `

                    });
                    totalDebit += SetortitipanOmsetAccount;

                }


                const pendapatanOp = toNum($('#mdr-grand').val());
                if (pendapatanOp > 0) {
                    journalRows.push({
                        accountCode: pendapatanOpAccount.kode,
                        account: pendapatanOpAccount.name,
                        departemen: pendapatanOpAccount.departemen,
                        debit: 0,
                        credit: pendapatanOp,
                        comment: `MDR 0,7 % Setor Titipan Penjualan Merchandise ${unitName || ' - '} `

                    });
                    totalCredit += pendapatanOp;
                }

                const grandTitipan = toNum($('#grand-titipan').val());
                if (grandTitipan > 0) {
                    journalRows.push({
                        accountCode: kasOmsetAccount.kode,
                        account: kasOmsetAccount.name,
                        departemen: kasOmsetAccount.departemen,
                        debit: 0,
                        credit: grandTitipan,
                        comment: `Setor Titipan Penjualan Merchandise ${unitName || ' - '} Dikurangi MDR `

                    });
                    totalCredit += grandTitipan;
                }

                const setorBagiHasil = toNum($('#grand-merch').val());
                if (setorBagiHasil > 0) {
                    journalRows.push({
                        accountCode: kasOmsetAccount.kode,
                        account: kasOmsetAccount.name,
                        departemen: kasOmsetAccount.departemen,
                        debit: 0,
                        credit: setorBagiHasil,
                        comment: `Setor Bagi Hasil Omset ${unitName || ' - '} Ke Ancol `

                    });
                    totalCredit += setorBagiHasil;
                }



                // --- Render ke tabel jurnal
                journalRows.forEach(row => {
                    const displayAccount = row.accountCode ?
                        `${row.accountCode} - ${row.account}${row.departemen ? ' (' + row.departemen + ')' : ''}` :
                        `${row.account}${row.departemen ? ' (' + row.departemen + ')' : ''}`;
                    journalBody.insertAdjacentHTML('beforeend', `
            <tr>
                <td class="border px-2 py-1">${displayAccount}</td>
                <td class="border px-2 py-1 text-right">${fmt(row.debit)}</td>
                <td class="border px-2 py-1 text-right">${fmt(row.credit)}</td>
                <td class="border px-2 py-1 text-left">${row.comment}</td>
            </tr>
        `);
                });

                // --- Update total debit/credit bawah tabel
                document.querySelector('.total-debit').textContent = fmt(totalDebit);
                document.querySelector('.total-credit').textContent = fmt(totalCredit);
            }

            function formatNumber(value) {
                return parseFloat(value).toLocaleString('en-US');
            }

            function cleanNumber(value) {
                if (!value) return 0;
                return value.replace(/,/g, '').replace(/[^0-9.-]/g, '');
            }

        });
    </script>



@endsection

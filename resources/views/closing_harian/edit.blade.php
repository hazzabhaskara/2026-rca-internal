@extends('layouts.app')


@section('content')
    <div class="py-10">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @php
                $themeColor = \App\Setting::get('theme_color', '#4F46E5');
            @endphp
            <div class="bg-white shadow-lg rounded-xl p-6 border-t-4" style="border-color:{{ $themeColor }}">
                <div id="tabs" class="type-section">
                    <ul class="flex border-b mb-4 space-x-4 text-sm font-medium text-gray-600" role="tablist">
                        <li><a href="#select_item" class="tab-link active">Proces closing harian</a></li>
                        <li><a href="#journal_report" class="tab-link">Journal report</a></li>
                    </ul>
                </div>
                <div id="select_item" class="tab-content">

                    <form action="{{ route('closing_harian.update', $data->id) }}" method="POST">
                        @csrf
                        @method('PUT')


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
                            Closing harian Edit
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal</label>
                                <input type="date" value="{{ old('tanggal', $data->tanggal) }}" name="tanggal"
                                    class="w-full border border-gray rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="jumlah_pengunjung" class="block text-sm font-medium text-gray-700">Jumlah
                                    Pengunjung</label>
                                <input type="number" value="{{ old('jumlah_pengunjung', $data->jumlah_pengunjung) }}"
                                    name="jumlah_pengunjung"
                                    class="w-full border border-gray rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="unit_kerja_id" class="block text-sm font-medium text-gray-700">
                                    Unit Kerja
                                </label>

                                <select id="unit_kerja_id"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed"
                                    disabled>
                                    <option value="">-- Pilih --</option>
                                    @foreach ($unitKerja as $g)
                                        <option value="{{ $g->id }}"
                                            {{ isset($data) && $data->unit_kerja_id == $g->id ? 'selected' : '' }}>
                                            {{ $g->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>

                                {{-- Hidden field agar value tetap dikirim --}}
                                <input type="hidden" name="unit_kerja_id" value="{{ $data->unit_kerja_id }}">
                            </div>

                        </div>

                        <div class="mt-6">
                            @php
                                $items = $data->details->pluck('wahanaItem.nama_item')->unique();
                            @endphp
                            <div style="overflow-x: auto;">
                                <table class="table-fixed border-collapse w-full text-center text-sm" id="tabelOmset">
                                    <thead class="bg-blue-300 text-black">
                                        <tr>
                                            <th rowspan="2" class="px-2 py-1 text-xs bg-green-200 w-[120px]">Wahana</th>
                                            @foreach ($items as $item)
                                                <th colspan="3" class="px-2 py-1 text-xs bg-blue-100 w-[200px]">
                                                    {{ $item }}
                                                </th>
                                            @endforeach

                                            <th rowspan="2" class="px-2 py-1 text-xs bg-green-200 w-[100px]">TOTAL OMSET
                                            </th>
                                            <th colspan="2" class="px-2 py-1 text-xs bg-yellow-200 w-[150px]">PAYMENT
                                                TYPE <br>DARI
                                                PENGUNJUNG</th>
                                            <th colspan="2" class="px-2 py-1 text-xs bg-gray-200 w-[150px]">PEMBAGIAN
                                                <br>SHARING
                                                OMSET
                                            </th>
                                            @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                                                <th rowpsan="2" class="px-2 py-1 text-xs bg-gray-200 w-[120px]">TITIPAN
                                                    <br>OMSET
                                                </th>
                                            @endif
                                            <th rowspan="2" class="px-2 py-1 text-xs bg-pink-200 w-[150px]">LEBIH
                                                (KURANG) <br>DANA
                                                CASH
                                                <br>SETOR TUNAI KE<br>MERCHANDISE
                                            </th>
                                        </tr>
                                        <tr>
                                            @foreach ($items as $item)
                                                <th class="px-2 py-1 text-xs w-[40px]">QTY</th>
                                                <th class="px-2 py-1 text-xs w-[100px]">HARGA</th>
                                                <th class="px-2 py-1 text-xs w-[120px]">JUMLAH</th>
                                            @endforeach
                                            <th class="px-2 py-1 text-xs w-[100px]">QRIS</th>
                                            <th class="px-2 py-1 text-xs w-[100px]">CASH</th>
                                            <th class="px-2 py-1 text-xs w-[100px]">MERCH</th>
                                            <th class="px-2 py-1 text-xs w-[100px]">RCA</th>
                                            @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                                                <th class="px-2 py-1 w-[100px]">{{ $data->unitKerja->nama_unit }}<br>(SETOR
                                                    TUNAI)</th>
                                            @endif
                                        </tr>
                                    </thead>

                                    <tbody id="item-table-body">
                                        @foreach ($data->details->groupBy('wahanaItem.wahana_id') as $wahanaId => $group)
                                            <tr>
                                                <td class="text-left font-semibold px-2 py-1">
                                                    {{ $group->first()->wahanaItem->wahana->nama_wahana ?? '-' }}
                                                </td>

                                                @foreach ($items as $itemIndex => $itemName)
                                                    @php
                                                        $detail = $group->firstWhere('wahanaItem.nama_item', $itemName);
                                                    @endphp

                                                    <td class="text-center px-2 py-1">
                                                        <input type="text"
                                                            name="details[{{ $wahanaId }}][items][{{ $itemIndex }}][qty]"
                                                            class="qty w-full px-2 py-1 text-center"
                                                            value="{{ number_format($detail->qty ?? 0, 0, ',', '.') }}"
                                                            required>
                                                    </td>
                                                    <td class="text-right px-2 py-1">
                                                        <input type="text"
                                                            name="details[{{ $wahanaId }}][items][{{ $itemIndex }}][harga]"
                                                            class="harga w-full px-2 py-1 text-right"
                                                            value="{{ number_format($detail->harga ?? 0, 0, ',', '.') }}"
                                                            readonly>
                                                        <input type="hidden"
                                                            class="hargaTitipan w-full px-2 py-1 text-right"
                                                            value="{{ number_format($detail->wahanaItem->harga_perhitungan_titipan ?? 0, 0, ',', '.') }}"
                                                            readonly>
                                                    </td>
                                                    <td class="text-right px-2 py-1">
                                                        <input type="text"
                                                            name="details[{{ $wahanaId }}][items][{{ $itemIndex }}][jumlah]"
                                                            class="jumlah jumlah-{{ Str::slug($detail->wahanaItem->nama_item) }} w-full px-2 py-1 text-right"
                                                            data-account-id="{{ $detail->wahanaItem->account_id ?? '' }}"
                                                            data-account-kode="{{ $detail->wahanaItem->account->kode_akun ?? '-' }}"
                                                            data-item-name="{{ $detail->wahanaItem->nama_item ?? '-' }}"
                                                            data-account-nama="{{ $detail->wahanaItem->account->nama_akun ?? '-' }}"
                                                            data-departemen-nama="{{ $detail->wahanaItem->departemen->deskripsi ?? '-' }}"
                                                            value="{{ number_format($detail->jumlah ?? 0, 0, ',', '.') }}"
                                                            readonly>
                                                        <input type="hidden"
                                                            name="details[{{ $wahanaId }}][items][{{ $itemIndex }}][item_id]"
                                                            value="{{ $detail->wahanaItem->id }}">
                                                    </td>
                                                @endforeach

                                                <td class="text-right px-2 py-1">
                                                    <input type="text" name="details[{{ $wahanaId }}][omset_total]"
                                                        class="omset_total w-full px-2 py-1 text-right"
                                                        value="{{ number_format($detail->omset_total ?? 0, 0, ',', '.') }}"
                                                        readonly>
                                                </td>

                                                <td class="text-right px-2 py-1">
                                                    <input type="text" name="details[{{ $wahanaId }}][qris]"
                                                        class="qris w-full px-2 py-1 text-right"
                                                        value="{{ number_format($detail->qris ?? 0, 0, ',', '.') }}">
                                                </td>
                                                <td class="text-right px-2 py-1">
                                                    <input type="text" name="details[{{ $wahanaId }}][cash]"
                                                        class="cash w-full px-2 py-1 text-right"
                                                        value="{{ number_format($detail->cash ?? 0, 0, ',', '.') }}"
                                                        readonly>
                                                </td>
                                                <td class="text-right px-2 py-1">
                                                    <input type="text" name="details[{{ $wahanaId }}][merch]"
                                                        class="merch w-full px-2 py-1 text-right"
                                                        value="{{ number_format($detail->merch ?? 0, 0, ',', '.') }}"
                                                        readonly>
                                                </td>
                                                <td class="text-right px-2 py-1">
                                                    <input type="text" name="details[{{ $wahanaId }}][rca]"
                                                        class="rca w-full px-2 py-1 text-right"
                                                        value="{{ number_format($detail->rca ?? 0, 0, ',', '.') }}"
                                                        readonly>
                                                </td>

                                                @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                                                    <td class="text-right px-2 py-1">
                                                        <input type="text"
                                                            name="details[{{ $wahanaId }}][titipan]"
                                                            class="titipan w-full px-2 py-1 text-right"
                                                            value="{{ number_format($detail->titipan ?? 0, 0, ',', '.') }}">
                                                    </td>
                                                @endif

                                                <td class="text-right px-2 py-1">
                                                    <input type="text"
                                                        name="details[{{ $wahanaId }}][lebih_kurang]"
                                                        class="lebih_kurang w-full px-2 py-1 text-right"
                                                        value="{{ number_format($detail->lebih_kurang ?? 0, 0, ',', '.') }}"
                                                        readonly>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-100 font-bold">
                                        {{-- SUBTOTAL PER ITEM --}}
                                        @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                                            <tr class="bg-blue-50">
                                                <td class="text-left px-2 py-1">
                                                    SUBTOTAL
                                                </td>
                                                <td colspan="{{ $items->count() * 3 }}"
                                                    class="text-left px-2 py-1 bg-blue-50"></td>

                                                {{-- Total omset semua item --}}
                                                @php
                                                    $totalOmset = 0;
                                                    $subtotalCash = 0;
                                                    $subtotalQris = 0;
                                                    $subtotalMerch = 0;
                                                    $subtotalRca = 0;
                                                    $subtotalTitipan = 0;
                                                    $subtotalLebihKurang = 0;

                                                    $omset = $detail->omset_total;
                                                    $totalOmset += $omset;
                                                    $subtotalCash += $detail->cash;
                                                    $subtotalQris += $detail->qris;
                                                    $subtotalMerch += $detail->merch;
                                                    $subtotalRca += $detail->rca;
                                                    $subtotalTitipan += $detail->titipan;
                                                    $subtotalLebihKurang += $detail->lebih_kurang;

                                                @endphp
                                                <td class="subtotal-omset text-right px-2 py-1 bg-green-100">
                                                    {{ number_format($totalOmset, 0, ',', '.') }}
                                                </td>
                                                <td class="subtotal-qris text-right px-2 py-1 bg-green-100">
                                                    {{ number_format($subtotalQris, 0, ',', '.') }}
                                                </td>
                                                <td class="subtotal-cash text-right px-2 py-1 bg-green-100">
                                                    {{ number_format($subtotalCash, 0, ',', '.') }}
                                                </td>
                                                <td class="subtotal-merch text-right px-2 py-1 bg-green-100">
                                                    {{ number_format($subtotalMerch, 0, ',', '.') }}
                                                </td>
                                                <td class="subtotal-rca text-right px-2 py-1 bg-green-100">
                                                    {{ number_format($subtotalRca, 0, ',', '.') }}
                                                </td>
                                                <td class="subtotal-titipan text-right px-2 py-1 bg-green-100">
                                                    {{ number_format($subtotalTitipan, 0, ',', '.') }}
                                                </td>
                                                <td class="subtotal-lebi_kurang text-right px-2 py-1 bg-green-100">
                                                    {{ number_format($subtotalLebihKurang, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- MDR dan Subtotal setelah MDR --}}
                                        @php
                                            $totalTitipan = 0;
                                            $totalTitipan += $detail->titipan;
                                            $mdrAmount = $totalTitipan * 0.007;
                                            $subtotalAfterMdr = $totalTitipan - $mdrAmount;
                                        @endphp
                                        @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                                            <tr class="bg-gray-50">
                                                <td class="text-left px-2 py-1">MDR 0,7%</td>
                                                @foreach ($items as $itemName)
                                                    <td colspan="3" class="px-2 py-1"></td>
                                                @endforeach
                                                <td colspan="5" class="px-2 py-1"></td>
                                                <td class="mdr-amount text-right px-2 py-1 text-red-500">
                                                    -{{ number_format($mdrAmount, 0, ',', '.') }}
                                                </td>
                                                <td class="mdrLebih-amount text-right px-2 py-1 text-red-500">
                                                    -{{ number_format($mdrAmount, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endif


                                        {{-- GRAND TOTAL --}}
                                        @php
                                            $totalOmset = 0;
                                            $subtotalCash = 0;
                                            $subtotalQris = 0;
                                            $subtotalMerch = 0;
                                            $subtotalRca = 0;
                                            $subtotalTitipan = 0;
                                            $subtotalLebihKurang = 0;

                                            $grandtotalOmset = 0;
                                            $grandCash = 0;
                                            $grandQris = 0;
                                            $grandMerch = 0;
                                            $grandRca = 0;
                                            $grandLebihKurang = 0;

                                            $omset = $detail->omset_total;
                                            $totalOmset += $omset;
                                            $subtotalCash += $detail->cash;
                                            $subtotalQris += $detail->qris;
                                            $subtotalMerch += $detail->merch;
                                            $subtotalRca += $detail->rca;
                                            $subtotalTitipan += $detail->titipan;
                                            $subtotalLebihKurang += $detail->lebih_kurang;

                                            $grandtotalOmset += $totalOmset;
                                            $grandCash += $subtotalCash;
                                            $grandQris += $subtotalQris;
                                            $grandMerch += $subtotalMerch;
                                            $grandRca += $subtotalRca;
                                            $grandLebihKurang += $subtotalLebihKurang;

                                            $mdrAmount = $subtotalTitipan * 0.007;
                                            $grandTitipan = $subtotalTitipan - $mdrAmount;
                                        @endphp
                                        <tr class="bg-blue-200">
                                            <td class="text-left px-2 py-1 font-semibold">GRAND TOTAL</td>
                                            @foreach ($items as $itemName)
                                                <td colspan="3" class="px-2 py-1"></td>
                                            @endforeach
                                            <td class="grand-total text-right px-2 py-1 bg-gray-300">
                                                {{ number_format($grandtotalOmset, 0, ',', '.') }}
                                            </td>
                                            <td class="grand-qris text-right px-2 py-1 bg-gray-300">
                                                {{ number_format($grandQris, 0, ',', '.') }}
                                            </td>
                                            <td class="grand-cash text-right px-2 py-1 bg-gray-300">
                                                {{ number_format($grandCash, 0, ',', '.') }}
                                            </td>
                                            <td class="grand-merch text-right px-2 py-1 bg-gray-300">
                                                {{ number_format($grandMerch, 0, ',', '.') }}
                                            </td>
                                            <td class="grand-rca text-right px-2 py-1 bg-gray-300">
                                                {{ number_format($grandRca, 0, ',', '.') }}
                                            </td>
                                            @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                                                <td class="grand-titipan text-right px-2 py-1 bg-gray-300">
                                                    {{ number_format($grandTitipan, 0, ',', '.') }}
                                                </td>
                                            @endif
                                            <td class="grand-lebi_kurang text-right px-2 py-1 bg-gray-300">
                                                {{ number_format($grandLebihKurang, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
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
                                <td colspan="3" class="text-center py-2 text-gray-500">
                                    Tidak ada journal
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50 font-semibold">
                            <tr>
                                <td class="border px-2 py-1 text-right">Total</td>
                                <td class="border px-2 py-1 text-right total-debit">0.00</td>
                                <td class="border px-2 py-1 text-right total-credit">0.00</td>
                                <td class="border"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-6 flex justify-end space-x-4">
                    <a href="{{ route('closing_harian.index') }}"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                        <i class="fas fa-arrow-left mr-1"></i> Cancel
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                        <i class="fas fa-save mr-1"></i> Process
                    </button>
                </div>
                </form>
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
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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

                // Global supaya bisa diakses dari generateJournalPreview
                window.linkedAccountsMap = {};
                window.unitConfig = {}; // ✅ tambahan: konfigurasi unit (gunakan titipan/mdr)

                const unitSelect = document.getElementById('unit_kerja_id');
                const selectedUnitId = unitSelect?.value;

                // =====================================================
                // FETCH DATA AKUN BERDASARKAN UNIT (untuk halaman edit)
                // =====================================================
                if (selectedUnitId) {
                    unitSelect.removeAttribute('disabled'); // buka sementara
                    fetch(`/get-linked-account-by-unit/${selectedUnitId}`)
                        .then(res => res.json())
                        .then(data => {
                            // console.log('🔍 Data linked accounts dari server:', data);

                            window.linkedAccountsMap = {};
                            data.linkedAccounts.forEach(acc => {
                                // console.log('💡 acc raw data:', acc);

                                const akunRaw = acc.akun || '';
                                const splitAkun = akunRaw.split(' - ');
                                // console.log('Split result:', splitAkun);

                                window.linkedAccountsMap[acc.kode] = {
                                    id: acc.id,
                                    kode: splitAkun[0] || '-',
                                    name: splitAkun[1] || '-',
                                    departemen: acc.departemen
                                };
                            });

                            // ✅ tambahan: simpan konfigurasi unit (jika disediakan oleh API)
                            window.unitConfig = data.unitConfig || {
                                gunakan_titipan: true,
                                gunakan_mdr: true
                            };

                            // console.log('🧭 Hasil mapping ke linkedAccountsMap:', window.linkedAccountsMap);
                            generateJournalPreview();
                        })
                        .catch(err => console.error('❌ Error saat fetch linked account:', err))
                        .finally(() => unitSelect.setAttribute('disabled', true)); // kunci lagi
                }

                const formatNumber = (num) => num.toLocaleString('id-ID');

                const parseNumber = (str) => parseFloat((str || '0').replace(/\./g, '').replace(',', '.')) || 0;

                // =====================================================
                // HITUNG PER BARIS
                // =====================================================
                const updateRow = (row) => {
                    const qtyInputs = row.querySelectorAll('.qty');
                    const hargaInputs = row.querySelectorAll('.harga');
                    const hargaTitipanInputs = row.querySelectorAll('.hargaTitipan');
                    const jumlahInputs = row.querySelectorAll('.jumlah');
                    const qrisInput = row.querySelector('.qris');
                    const cashInput = row.querySelector('.cash');
                    const merchInput = row.querySelector('.merch');
                    const rcaInput = row.querySelector('.rca');
                    const titipanInput = row.querySelector('.titipan');
                    const lebihKurangInput = row.querySelector('.lebih_kurang');
                    const omsetTotalInput = row.querySelector('.omset_total');

                    let totalOmset = 0;
                    let totalTitipan = 0;

                    qtyInputs.forEach((qtyInput, i) => {
                        const qty = parseNumber(qtyInput.value);
                        const harga = parseNumber(hargaInputs[i].value);
                        const hargaTitipan = parseNumber(hargaTitipanInputs[i]?.value || 0);
                        const titipanItem = qty * (hargaTitipan > 0 ? hargaTitipan : 0);

                        const jumlah = qty * harga;
                        jumlahInputs[i].value = formatNumber(jumlah);

                        totalOmset += jumlah;
                        totalTitipan += titipanItem; // tambahkan ke total titipan
                    });

                    if (omsetTotalInput) omsetTotalInput.value = formatNumber(totalOmset);

                    const qris = parseNumber(qrisInput?.value);
                    const cash = Math.max(0, totalOmset - qris);
                    if (cashInput) cashInput.value = formatNumber(cash);

                    const merch = totalOmset * 0.45;
                    const rca = totalOmset * 0.55;
                    if (merchInput) merchInput.value = formatNumber(merch);
                    if (rcaInput) rcaInput.value = formatNumber(rca);

                    // ✅ Titipan otomatis (bukan dari input manual)
                    if (titipanInput) titipanInput.value = formatNumber(totalTitipan);

                    const lebihKurang = cash - merch - totalTitipan;
                    if (lebihKurangInput) lebihKurangInput.value = formatNumber(lebihKurang);

                    return {
                        totalOmset,
                        totalTitipan,
                        qris,
                        cash,
                        merch,
                        rca,
                        lebihKurang
                    };
                };


                // =====================================================
                // HITUNG TOTAL
                // =====================================================
                const updateTotals = () => {
                    const rows = document.querySelectorAll('#tabelOmset tbody tr');

                    let sumOmset = 0,
                        sumQris = 0,
                        sumCash = 0,
                        sumMerch = 0,
                        sumRca = 0,
                        sumTitipan = 0,
                        sumLebihKurang = 0;

                    // Helper aman
                    const safe = (n) => (isNaN(n) || n === undefined ? 0 : n);

                    rows.forEach((row) => {
                        // 🔍 Skip baris yang bukan item (tidak punya .qty dan bukan .omset_total)
                        const isDataRow = row.querySelector('.qty') || row.querySelector('.omset_total');
                        if (!isDataRow) return;

                        const r = updateRow(row);
                        sumOmset += safe(r.totalOmset);
                        sumQris += safe(r.qris);
                        sumCash += safe(r.cash);
                        sumMerch += safe(r.merch);
                        sumRca += safe(r.rca);
                        sumTitipan += safe(r.totalTitipan);
                        sumLebihKurang += safe(r.lebihKurang);
                    });

                    // 🧮 Hitung MDR (0,7% dari total titipan)
                    const mdr = safe(sumTitipan) * 0.007;
                    const grandTitipan = safe(sumTitipan) - mdr;
                    const grandLebihKurang = safe(sumLebihKurang) + mdr;

                    // Helper untuk update teks
                    const setText = (selector, value) => {
                        const el = document.querySelector(selector);
                        if (el) el.textContent = value;
                    };

                    // --- Update Subtotal
                    setText('.subtotal-omset', formatNumber(sumOmset));
                    setText('.subtotal-qris', formatNumber(sumQris));
                    setText('.subtotal-cash', formatNumber(sumCash));
                    setText('.subtotal-merch', formatNumber(sumMerch));
                    setText('.subtotal-rca', formatNumber(sumRca));
                    setText('.subtotal-titipan', formatNumber(sumTitipan));
                    setText('.subtotal-lebi_kurang', formatNumber(sumLebihKurang));

                    // --- Update Grand Total
                    setText('.grand-total', formatNumber(sumOmset));
                    setText('.grand-qris', formatNumber(sumQris));
                    setText('.grand-cash', formatNumber(sumCash));
                    setText('.grand-merch', formatNumber(sumMerch));
                    setText('.grand-rca', formatNumber(sumRca));
                    setText('.grand-titipan', formatNumber(grandTitipan));
                    setText('.grand-lebi_kurang', formatNumber(grandLebihKurang));

                    // --- Update MDR row (kalau ada)
                    setText('.mdr-amount', '-' + formatNumber(mdr));
                    setText('.mdrLebih-amount', '-' + formatNumber(mdr));
                };


                // =====================================================
                // GENERATE JOURNAL PREVIEW (versi sama seperti CREATE)
                // =====================================================
                function generateJournalPreview() {
                    const journalBody = document.querySelector('.journal-body');
                    journalBody.innerHTML = '';

                    // Format angka aman
                    const fmt = num => new Intl.NumberFormat('id-ID').format(num);
                    const toNum = val => {
                        if (val == null || val === undefined) return 0;
                        const num = parseFloat((val || '').toString().replace(/\./g, '').replace(',', '.'));
                        return isNaN(num) ? 0 : num;
                    };

                    // Pastikan total sudah dihitung dulu
                    updateTotals();

                    let journalRows = [];
                    let totalDebit = 0;
                    let totalCredit = 0;

                    const unitName = $('#unit_kerja_id option:selected').text() || '-';
                    const map = window.linkedAccountsMap;
                    const config = window.unitConfig || {};

                    if (!map || Object.keys(map).length === 0) {
                        console.warn('⚠️ linkedAccountsMap belum diinisialisasi, journal tidak dibuat.');
                        return;
                    }

                    // Fungsi aman untuk push jurnal
                    const safePush = (accountObj, debit, credit, comment) => {
                        if (!accountObj || !accountObj.kode || !accountObj.name || accountObj.kode === '-') {
                            console.warn(`⚠️ Akun tidak ditemukan untuk jurnal: ${comment}`);
                            return;
                        }
                        journalRows.push({
                            accountCode: accountObj.kode,
                            account: accountObj.name,
                            departemen: accountObj.departemen || '-',
                            debit,
                            credit,
                            comment
                        });
                    };

                    // Ambil akun-akun terkait
                    const piutangQrisAccount = map['Piutang Qris'] || {};
                    const kasOmsetAccount = map['Kas Omset'] || {};
                    const titipanOmset = map['Titipan Omset Merchandise'] || {};
                    const bebanSewa = map['Beban Bagi Hasil(Sewa)'] || {};
                    const pendapatanOpAccount = map['Pendapatan Non Operasional'] || {};

                    // Fallback nilai jika elemen DOM tidak ada
                    const valOrZero = (selector) => {
                        const el = document.querySelector(selector);
                        return el ? toNum(el.textContent) : 0;
                    };

                    const piutang = valOrZero('.grand-qris');
                    const kas = valOrZero('.grand-cash');
                    const beban = valOrZero('.grand-merch');
                    const titipan = valOrZero('.subtotal-titipan');
                    const grandTitipan = valOrZero('.grand-titipan');
                    const pendapatanOp = Math.abs(valOrZero('.mdr-amount'));

                    // console.log('🧮 [DEBUG generateJournalPreview]', {
                    //     piutang,
                    //     kas,
                    //     beban,
                    //     titipan,
                    //     grandTitipan,
                    //     pendapatanOp,
                    //     config
                    // });

                    // --- Debit Piutang QRIS
                    if (piutang > 0) {
                        safePush(piutangQrisAccount, piutang, 0, `Penjualan ${unitName} Dibayarkan via QRIS`);
                        totalDebit += piutang;
                    }

                    // --- Debit Kas Omset
                    if (kas > 0) {
                        safePush(kasOmsetAccount, kas, 0, `Penjualan ${unitName} Diterima Cash`);
                        totalDebit += kas;
                    }

                    // --- Kredit Titipan (hanya jika digunakan)
                    if (config.gunakan_titipan && titipan > 0) {
                        safePush(titipanOmset, 0, titipan, `Penjualan ${unitName} Diterima Cash`);
                        totalCredit += titipan;
                    }

                    // --- Kredit per item (penjualan tiap wahana)
                    document.querySelectorAll('input[class*="jumlah-"]').forEach(input => {
                        const val = toNum(input.value);
                        const accKode = input.dataset.accountKode || '';
                        const accNama = input.dataset.accountNama || '';
                        const itemName = input.dataset.itemName || '';
                        const departemenNama = input.dataset.departemenNama || '';

                        if (val > 0 && accKode !== '-' && accNama !== '-') {
                            journalRows.push({
                                accountCode: accKode,
                                account: accNama,
                                departemen: departemenNama || '-',
                                debit: 0,
                                credit: val,
                                comment: `Penjualan ${itemName || '-'} - ${unitName || '-'}`
                            });
                            totalCredit += val;
                        }
                    });

                    // --- Debit Beban Bagi Hasil
                    if (beban > 0) {
                        safePush(bebanSewa, beban, 0, `Setor Bagi Hasil Omset ${unitName} ke Ancol`);
                        totalDebit += beban;
                    }

                    // --- Debit setoran titipan (hanya jika digunakan)
                    const SetortitipanOmsetAccount = titipan;
                    if (config.gunakan_titipan && SetortitipanOmsetAccount > 0) {
                        safePush(titipanOmset, SetortitipanOmsetAccount, 0,
                            `Setor Titipan Penjualan Merchandise ${unitName}`);
                        totalDebit += SetortitipanOmsetAccount;
                    }

                    // --- Kredit MDR (hanya jika digunakan)
                    if (config.gunakan_mdr && pendapatanOp > 0) {
                        safePush(pendapatanOpAccount, 0, pendapatanOp,
                            `MDR 0,7% Setor Titipan Penjualan Merchandise ${unitName}`);
                        totalCredit += pendapatanOp;
                    }

                    // --- Kredit Grand Titipan (hanya jika digunakan)
                    if (config.gunakan_titipan && grandTitipan > 0) {
                        safePush(kasOmsetAccount, 0, grandTitipan,
                            `Setor Titipan Penjualan Merchandise ${unitName} Dikurangi MDR`);
                        totalCredit += grandTitipan;
                    }

                    // --- Kredit Setor Bagi Hasil Omset
                    const setorBagi = valOrZero('.grand-merch');
                    if (setorBagi > 0) {
                        safePush(kasOmsetAccount, 0, setorBagi, `Setor Bagi Hasil Omset ${unitName} ke Ancol`);
                        totalCredit += setorBagi;
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

                    document.querySelector('.total-debit').textContent = fmt(totalDebit);
                    document.querySelector('.total-credit').textContent = fmt(totalCredit);
                }


                // =====================================================
                // EVENT LISTENER
                // =====================================================
                document.querySelectorAll('#tabelOmset .qty, #tabelOmset .qris, #tabelOmset .titipan')
                    .forEach(input => input.addEventListener('input', () => {
                        updateTotals();
                        generateJournalPreview();
                    }));

                updateTotals();
            });
        </script>


    @endsection

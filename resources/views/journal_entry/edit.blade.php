@extends('layouts.app')
@section('content')
    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="font-bold text-lg mb-2">General Journal Edit</h2>

                <form id="journal-entry-form" action="{{ route('journal_entry.update', $journalEntry->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    {{-- State filter dari halaman list --}}
                    <input type="hidden" name="back_url" value="{{ request('back_url', route('journal_entry.index')) }}">

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

                    {{-- Header --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 text-base">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Source</label>
                            <input type="text" name="source" class="w-full rounded-md border px-3 py-2"
                                value="{{ old('source', $journalEntry->source) }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Date</label>
                            <input type="date" name="tanggal" class="w-full rounded-md border px-3 py-2"
                                value="{{ old('tanggal', $journalEntry->tanggal) }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Comment</label>
                            <input type="text" name="comment" class="w-full rounded-md border px-3 py-2"
                                value="{{ old('comment', $journalEntry->comment) }}">
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto overflow-y-auto max-h-[450px] mb-6 border rounded">
                        <table class="min-w-full table-auto border-collapse text-base text-left bg-white">
                            <thead class="bg-gray-100 text-gray-700 font-semibold sticky top-0 z-10">
                                <tr>
                                <tr>
                                    <th class="border px-4 py-3 text-center w-[18%]">Accounts</th>
                                    <th class="border px-4 py-3 text-center w-[10%]">Debits</th>
                                    <th class="border px-4 py-3 text-center w-[10%]">Credits</th>
                                    <th class="border px-4 py-3 text-center w-[20%]">Comment</th>
                                    @can('specpose.access')
                                        <th class="border px-4 py-3 text-center w-[15%]">Specpose</th>
                                    @endcan

                                    @can('fiscal.access')
                                        <th class="border px-4 py-3 text-center w-[7%]">Fiscorr</th>
                                        <th class="border px-4 py-3 text-center w-[15%]">Penyesuaian Fiskal</th>
                                    @endcan
                                    <th class="border px-4 py-3 text-center w-[5%]">Aksi</th>
                                </tr>

                                </tr>
                            </thead>
                            <tbody id="item-table-body" class="bg-white">
                                @php
                                    $totalDebit = 0;
                                    $totalKredit = 0;
                                @endphp

                                @foreach ($journalEntry->details as $i => $detail)
                                    @php
                                        $totalDebit += $detail->debits;
                                        $totalKredit += $detail->credits;
                                    @endphp

                                    <tr class="item-row" data-index="{{ $i }}"
                                        data-tipe-akun="{{ $detail->chartOfAccount->tipe_akun ?? '' }}">
                                        @if ($detail->status == 0)
                                            <td class="border px-2 py-1">
                                                @php
                                                    $selectValue = $detail->departemen_akun_id ? ('d-' . $detail->departemen_akun_id) : ('c-' . ($detail->chartOfAccount->id ?? $detail->kode_akun));
                                                    $displayText = $detail->kode_akun . ' - ' . ($detail->chartOfAccount->nama_akun ?? '-');
                                                    if ($detail->departemenAkun && $detail->departemenAkun->departemen) {
                                                        $displayText .= ' - ' . $detail->departemenAkun->departemen->deskripsi;
                                                    }
                                                @endphp
                                                {{-- Select2 purely for UI display --}}
                                                <select class="item-select w-full border rounded"
                                                    data-index="{{ $i }}">
                                                    <option value="{{ $selectValue }}" selected>{{ $displayText }}</option>
                                                </select>

                                                {{-- Hidden inputs for actual server submission --}}
                                                <input type="hidden" name="items[{{ $i }}][kode_akun]"
                                                    class="kode-akun" value="{{ $detail->kode_akun }}">
                                                <input type="hidden" name="items[{{ $i }}][departemen_akun_id]"
                                                    class="departemen-akun" value="{{ $detail->departemen_akun_id }}">
                                            </td>

                                            <td class="border px-2 py-1">
                                                <input type="text" name="items[{{ $i }}][debits]"
                                                    class="money-input debit-input w-full border rounded px-2 py-1 text-right"
                                                    value="{{ number_format($detail->debits, 2, ',', '.') }}" />
                                            </td>
                                            <td class="border px-2 py-1">
                                                <input type="text" name="items[{{ $i }}][credits]"
                                                    class="money-input credit-input w-full border rounded px-2 py-1 text-right"
                                                    value="{{ number_format($detail->credits, 2, ',', '.') }}" />
                                            </td>
                                            <td class="border px-2 py-1">
                                                <input type="text" name="items[{{ $i }}][comment]"
                                                    class="w-full border rounded px-2 py-1"
                                                    value="{{ $detail->comment }}" />
                                            </td>
                                        @else
                                            <td class="border px-2 py-1">
                                                @php
                                                    $selectValue = $detail->departemen_akun_id ? ('d-' . $detail->departemen_akun_id) : ('c-' . ($detail->chartOfAccount->id ?? $detail->kode_akun));
                                                    $displayText = $detail->kode_akun . ' - ' . ($detail->chartOfAccount->nama_akun ?? '-');
                                                    if ($detail->departemenAkun && $detail->departemenAkun->departemen) {
                                                        $displayText .= ' - ' . $detail->departemenAkun->departemen->deskripsi;
                                                    }
                                                @endphp
                                                {{-- Select2 purely for UI display --}}
                                                <select class="item-select w-full border rounded" disabled
                                                    data-index="{{ $i }}">
                                                    <option value="{{ $selectValue }}" selected>{{ $displayText }}</option>
                                                </select>

                                                {{-- Hidden inputs for actual server submission --}}
                                                <input type="hidden" name="items[{{ $i }}][kode_akun]"
                                                    disabled class="kode-akun" value="{{ $detail->kode_akun }}">
                                                <input type="hidden" name="items[{{ $i }}][departemen_akun_id]"
                                                    disabled class="departemen-akun"
                                                    value="{{ $detail->departemen_akun_id }}">
                                            </td>

                                            <td class="border px-2 py-1">
                                                <input type="text" name="items[{{ $i }}][debits]" disabled
                                                    class="money-input debit-input w-full border rounded px-2 py-1 text-right"
                                                    value="{{ number_format($detail->debits, 2, ',', '.') }}" />
                                            </td>
                                            <td class="border px-2 py-1">
                                                <input type="text" name="items[{{ $i }}][credits]" disabled
                                                    class="money-input credit-input w-full border rounded px-2 py-1 text-right"
                                                    value="{{ number_format($detail->credits, 2, ',', '.') }}" />
                                            </td>
                                            <td class="border px-2 py-1">
                                                <input type="text" name="items[{{ $i }}][comment]" disabled
                                                    class="w-full border rounded px-2 py-1"
                                                    value="{{ $detail->comment }}" />
                                            </td>
                                        @endif

                                        @can('specpose.access')
                                            <td class="border px-2 py-1">
                                                <select class="w-full border rounded"
                                                    name="items[{{ $i }}][project_id]">
                                                    <option value="">-- Pilih Specpose --</option>
                                                    @foreach ($projects as $prj)
                                                        <option value="{{ $prj->id }}"
                                                            {{ $detail->project_id == $prj->id ? 'selected' : '' }}>
                                                            {{ $prj->nama_project }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        @endcan

                                        @can('fiscal.access')
                                            <td class="border px-2 py-1 text-center">
                                                <input type="hidden" name="items[{{ $i }}][pajak]" value="0">
                                                <input type="checkbox" name="items[{{ $i }}][pajak]" value="1"
                                                    class="pajak-checkbox"
                                                    {{ old("items.$i.pajak", $detail->pajak ?? 0) ? 'checked' : '' }}>
                                            </td>
                                            <td class="border px-2 py-1 penyesuaian-col text-center">
                                                @if ($detail->penyesuaian_fiskal || $detail->kode_fiscal)
                                                    <div class="flex flex-col space-y-1">
                                                        <select name="items[{{ $i }}][penyesuaian_fiskal]"
                                                            class="w-full border rounded px-2 py-1">
                                                            <option value="">-- Pilih --</option>
                                                            <option value="non_tax"
                                                                {{ $detail->penyesuaian_fiskal == 'non_tax' ? 'selected' : '' }}>
                                                                Non Tax Object</option>
                                                            <option value="pph_final"
                                                                {{ $detail->penyesuaian_fiskal == 'pph_final' ? 'selected' : '' }}>
                                                                PPH Final</option>
                                                            <option value="koreksi_plus"
                                                                {{ $detail->penyesuaian_fiskal == 'koreksi_plus' ? 'selected' : '' }}>
                                                                Koreksi Positif</option>
                                                            <option value="koreksi_minus"
                                                                {{ $detail->penyesuaian_fiskal == 'koreksi_minus' ? 'selected' : '' }}>
                                                                Koreksi Negatif</option>
                                                        </select>
                                                        <input type="text" name="items[{{ $i }}][kode_fiscal]"
                                                            value="{{ $detail->kode_fiscal }}" placeholder="Kode Fiscal"
                                                            class="w-full border rounded px-2 py-1" />
                                                    </div>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endcan


                                        @if ($detail->status == 0)
                                            <td class="border px-2 py-1 text-center">
                                                <button type="button"
                                                    class="add-row px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600">+</button>

                                                <button type="button"
                                                    class="remove-row px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">X</button>
                                            </td>
                                        @else
                                            <td class="border px-2 py-1 text-center">
                                                <button type="button" disabled
                                                    class="add-row px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600">+</button>

                                                <button type="button" disabled
                                                    class="remove-row px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">X</button>
                                            </td>
                                        @endif

                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot class="bg-gray-100 font-bold text-gray-800">
                                <tr>
                                    <td class="border px-4 py-2 text-right">TOTAL</td>
                                    <td class="border px-4 py-2 text-right" id="total-debit">
                                        {{ number_format($totalDebit, 0, ',', '.') }}
                                    </td>
                                    <td class="border px-4 py-2 text-right" id="total-credit">
                                        {{ number_format($totalKredit, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Tombol Add Row --}}


                    {{-- Actions --}}
                    <div class="mt-6 flex justify-end gap-4">
                        <a href="{{ request('back_url', route('journal_entry.index')) }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded-md">
                            Cancel
                        </a>

                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let rowIndex = $('#item-table-body tr').length || 0;

        function parseNumber(value) {
            if (!value) return 0;
            return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
        }

        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        }

        // Format angka dua desimal dan update total
        function formatNumberInput(input) {
            let value = input.value;

            // Jika pengguna baru mengetik koma, jangan langsung diformat
            if (value.endsWith(',')) {
                return;
            }

            // Hapus semua karakter kecuali angka dan koma
            value = value.replace(/[^\d,]/g, '');

            if (value === '') {
                input.value = '';
                return;
            }

            // Pisahkan bagian ribuan dan desimal
            let parts = value.split(',');
            let integerPart = parts[0];
            let decimalPart = parts[1] ? parts[1].slice(0, 2) : '';

            // Tambahkan titik ribuan
            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            // Gabungkan kembali
            input.value = decimalPart ? `${integerPart},${decimalPart}` : integerPart;
        }

        // Event input
        $(document).on('input', '.money-input', function() {
            formatNumberInput(this);
            updateTotals();
        });

        // Saat keluar dari input → paksa 2 desimal
        $(document).on('blur', '.money-input', function() {
            let val = this.value.replace(/\./g, '').replace(',', '.');
            if (val === '') return;
            this.value = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(parseFloat(val));
        });

        function updateTotals() {
            let totalDebit = 0,
                totalCredit = 0;

            document.querySelectorAll('.debit-input').forEach(input => totalDebit += parseNumber(input.value));
            document.querySelectorAll('.credit-input').forEach(input => totalCredit += parseNumber(input.value));

            document.getElementById('total-debit').innerText = formatNumber(totalDebit);
            document.getElementById('total-credit').innerText = formatNumber(totalCredit);
        }

        function generateRow(index) {
            return `
        <tr class="item-row" data-index="${index}" data-tipe-akun="">
            <td class="border px-2 py-1">
                <select class="item-select w-full border rounded" data-index="${index}"></select>
                <input type="hidden" name="items[${index}][kode_akun]" class="kode-akun">
                <input type="hidden" name="items[${index}][departemen_akun_id]" class="departemen-akun">
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[${index}][debits]" class="money-input debit-input w-full border rounded px-2 py-1 text-right" value="0"/>
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[${index}][credits]" class="money-input credit-input w-full border rounded px-2 py-1 text-right" value="0"/>
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[${index}][comment]" class="w-full border rounded px-2 py-1"/>
            </td>
            <td class="border px-2 py-1">
                <select name="items[${index}][project_id]" class="w-full border rounded px-2 py-1">
                    <option value="">-- Pilih Specpose --</option>
                    @foreach ($projects as $prj)
                        <option value="{{ $prj->id }}">{{ $prj->nama_project }}</option>
                    @endforeach
                </select>
            </td>
            <td class="border px-2 py-1 text-center">
                <input type="hidden" name="items[${index}][pajak]" value="0">
                <input type="checkbox" class="pajak-checkbox" name="items[${index}][pajak]" value="1">
            </td>
            <td class="border px-2 py-1 penyesuaian-col text-center">-</td>
            <td class="border px-2 py-1 text-center">
                <button type="button" class="add-row px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600">+</button>
                <button type="button" class="remove-row px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">X</button>
            </td>
        </tr>`;
        }

        function reindexRows() {
            $('#item-table-body tr').each(function(i, row) {
                $(row).attr('data-index', i);
                $(row).find('input, select').each(function() {
                    const name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, `[${i}]`));
                    }
                });
            });
        }

        function attachSelect2($select) {
            $select.select2({
                placeholder: 'Cari Account...',
                width: '100%',
                ajax: {
                    url: '/search-account',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        q: params.term
                    }),
                    processResults: data => {
                        let results = [];
                        data.forEach(item => {
                            // 1. Akun tanpa departemen (Non-Departemen / Unassigned)
                            results.push({
                                id: `c-${item.id}`,
                                text: `${item.kode_akun} - ${item.nama_akun}`,
                                kode_akun: item.kode_akun,
                                departemen_akun_id: null,
                                tipe_akun: item.tipe_akun
                            });
                            // 2. Akun per departemen (masing-masing id d-{dept.id} unik)
                            if (item.daftar_departemen && item.daftar_departemen.length > 0) {
                                item.daftar_departemen.forEach(dept => {
                                    results.push({
                                        id: `d-${dept.id}`,
                                        text: `${item.kode_akun} - ${item.nama_akun} - ${dept.deskripsi}`,
                                        kode_akun: item.kode_akun,
                                        departemen_akun_id: dept.id,
                                        tipe_akun: item.tipe_akun
                                    });
                                });
                            }
                        });
                        return {
                            results
                        };
                    },
                    cache: true
                }
            }).on('select2:select', function(e) {
                const data = e.params.data;
                const row = $(this).closest('tr');
                row.find('.kode-akun').val(data.kode_akun);
                row.find('.departemen-akun').val(data.departemen_akun_id || '');
                row.attr('data-tipe-akun', data.tipe_akun || '');
            });
        }

        // Pajak → kolom penyesuaian fiskal
        $(document).on('change', '.pajak-checkbox', function() {
            const row = $(this).closest('tr');
            const tipeAkun = row.attr('data-tipe-akun');
            const col = row.find('.penyesuaian-col');
            const idx = row.attr('data-index');

            if (this.checked) {
                let html = `
                <div class="flex flex-col space-y-1">
                    <select name="items[${idx}][penyesuaian_fiskal]" class="w-full border rounded px-2 py-1">
                        ${tipeAkun === 'Pendapatan'
                            ? `<option value="non_tax">Non Tax Object</option><option value="pph_final">PPH Final</option>`
                            : tipeAkun === 'Beban'
                                ? `<option value="koreksi_plus">Koreksi Positif</option><option value="koreksi_minus">Koreksi Negatif</option>`
                                : `<option value="">-</option>`}
                    </select>
                    <input type="text" name="items[${idx}][kode_fiscal]" placeholder="Kode Fiscal" class="w-full border rounded px-2 py-1"/>
                </div>`;
                col.html(html);
            } else {
                col.text('-');
            }
        });

        // Tambah row ke bawah tabel
        $('#add-row').click(function() {
            $('#item-table-body').append(generateRow(rowIndex));
            attachSelect2($('#item-table-body tr:last .item-select'));
            rowIndex++;
            reindexRows();
            updateTotals();
        });

        // Tambah row di bawah baris tertentu
        $(document).on('click', '.add-row', function() {
            const currentRow = $(this).closest('tr');
            const newRow = $(generateRow(rowIndex));
            currentRow.after(newRow);
            attachSelect2(newRow.find('.item-select'));
            rowIndex++;
            reindexRows();
            updateTotals();
        });

        // Hapus row
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            reindexRows();
            updateTotals();
        });

        // ✅ Init & event submit aman (urutan paling bawah)
        $(document).ready(function() {
            $('.item-select').each(function() {
                attachSelect2($(this));
            });
            updateTotals();

            // ✅ Normalisasi sebelum submit
            $('#journal-entry-form').on('submit', function() {
                document.querySelectorAll('.money-input').forEach(input => {
                    // dari "1.000,50" → "1000.50"
                    let val = input.value
                        .replace(/\./g, '') // buang titik ribuan
                        .replace(',', '.'); // koma → titik desimal
                    input.value = val; // biarkan string numerik
                });
            });
        });
    </script>
@endpush

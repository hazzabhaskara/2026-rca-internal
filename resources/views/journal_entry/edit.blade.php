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
                    @php
                        $rawBackUrl = old('back_url', request('back_url'));
                        if (empty($rawBackUrl) && request()->headers->has('referer')) {
                            $referer = request()->headers->get('referer');
                            if (is_string($referer) && !empty($referer) && !\Illuminate\Support\Str::contains($referer, '/edit')) {
                                $rawBackUrl = $referer;
                            }
                        }
                        if (is_string($rawBackUrl)) {
                            while (strpos($rawBackUrl, '&amp;') !== false) {
                                $rawBackUrl = html_entity_decode($rawBackUrl, ENT_QUOTES, 'UTF-8');
                            }
                            if (\Illuminate\Support\Str::startsWith($rawBackUrl, '/') && !\Illuminate\Support\Str::startsWith($rawBackUrl, '//') && !\Illuminate\Support\Str::startsWith($rawBackUrl, '/\\')) {
                                $rawBackUrl = url($rawBackUrl);
                            }
                        }
                        $appUrl = url('/');
                        $parsedHost = $rawBackUrl && is_string($rawBackUrl) ? parse_url($rawBackUrl, PHP_URL_HOST) : null;
                        $currentHost = request()->getHost();
                        $configAppUrl = config('app.url');
                        $configHost = $configAppUrl ? parse_url($configAppUrl, PHP_URL_HOST) : null;
                        $isSameHost = $parsedHost && (
                            strtolower($parsedHost) === strtolower($currentHost) ||
                            ($configHost && strtolower($parsedHost) === strtolower($configHost))
                        );

                        $isValidBackUrl = $rawBackUrl && is_string($rawBackUrl) && (
                            (\Illuminate\Support\Str::startsWith($rawBackUrl, $appUrl) && (
                                $rawBackUrl === $appUrl ||
                                \Illuminate\Support\Str::startsWith($rawBackUrl, $appUrl . '/') ||
                                \Illuminate\Support\Str::startsWith($rawBackUrl, $appUrl . '?')
                            )) || $isSameHost
                        );
                        $safeBackUrl = $isValidBackUrl ? $rawBackUrl : route('journal_entry.index');
                    @endphp
                    <input type="hidden" name="back_url" value="{{ $safeBackUrl }}" id="back_url">
                    <input type="hidden" name="from_new_tab" id="from_new_tab" value="{{ old('from_new_tab', request('from_new_tab', 0)) }}">

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
                                    <th class="border px-4 py-3 text-center w-[18%]">Accounts</th>
                                    <th class="border px-4 py-3 text-center w-[10%]">Debits</th>
                                    <th class="border px-4 py-3 text-center w-[10%]">Credits</th>
                                    <th class="border px-4 py-3 text-center w-[20%]">Comment</th>
                                    @can('specpose.access')
                                        <th class="border px-4 py-3 text-center w-[15%]">Specpose</th>
                                    @endcan

                                    @can('fiscal.access')
                                        @if (($fiscalJournalMode ?? 'disabled') === 'enabled')
                                            <th class="border px-4 py-3 text-center w-[7%]">Fiscorr</th>
                                            <th class="border px-4 py-3 text-center w-[15%]">Penyesuaian Fiskal</th>
                                        @endif
                                    @endcan
                                    <th class="border px-4 py-3 text-center w-[8%]">Aksi</th>
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
                                            @if (($fiscalJournalMode ?? 'disabled') === 'enabled')
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
                                            @elseif (($fiscalJournalMode ?? 'disabled') === 'hide')
                                                <input type="hidden" name="items[{{ $i }}][pajak]" value="{{ $detail->pajak ?? 0 }}">
                                                <input type="hidden" name="items[{{ $i }}][penyesuaian_fiskal]" value="{{ $detail->penyesuaian_fiskal }}">
                                                <input type="hidden" name="items[{{ $i }}][kode_fiscal]" value="{{ $detail->kode_fiscal }}">
                                            @endif
                                        @endcan

                                        <td class="border px-2 py-1 text-center whitespace-nowrap space-x-1">
                                            <button type="button" title="Tambah Baris"
                                                class="add-row px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs font-bold">+</button>
                                            <button type="button" title="Auto-Balance (Seimbangkan Baris)"
                                                class="auto-balance px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs font-bold" data-index="{{ $i }}">⚡</button>
                                            <button type="button" title="Hapus Baris"
                                                class="remove-row px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs font-bold">X</button>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot class="bg-gray-100 font-bold text-gray-800">
                                <tr>
                                    <td class="border px-4 py-2 text-right">TOTAL</td>
                                    <td class="border px-4 py-2 text-right font-mono" id="total-debit">
                                        {{ number_format($totalDebit, 2, ',', '.') }}
                                    </td>
                                    <td class="border px-4 py-2 text-right font-mono" id="total-credit">
                                        {{ number_format($totalKredit, 2, ',', '.') }}
                                    </td>
                                    @php
                                        $footerColspan = 2; // Comment + Aksi
                                        if (auth()->user()->can('specpose.access')) $footerColspan++;
                                        if (auth()->user()->can('fiscal.access') && ($fiscalJournalMode ?? 'disabled') === 'enabled') $footerColspan += 2;
                                    @endphp
                                    <td colspan="{{ $footerColspan }}" class="border px-4 py-2 text-xs font-normal text-gray-500">
                                        <div class="flex items-center justify-between">
                                            <span id="balance-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"></span>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Tombol Add Row --}}


                    {{-- Actions --}}
                    <div class="mt-6 flex flex-wrap justify-between items-center gap-4">
                        <div>
                            @can('journal_entry.delete')
                                <button type="button" onclick="confirmDeleteCurrentVoucher()"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-md shadow-sm transition">
                                    <i class="fas fa-trash-alt mr-2"></i> Hapus Voucher Ini
                                </button>
                            @endcan
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ $safeBackUrl }}" id="btn-cancel" onclick="handleCancel(event)"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded-md transition">
                                Cancel
                            </a>

                            <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded-md hover:bg-green-700 font-semibold shadow transition">
                                Process
                            </button>
                        </div>
                    </div>
                </form>

                @can('journal_entry.delete')
                    <form id="delete-voucher-form" action="{{ route('journal_entry.destroy', $journalEntry->id) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="back_url" value="{{ $safeBackUrl }}">
                    </form>
                @endcan
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

            // Real-Time Balance Difference Indicator & Submit Guard
            const diff = Math.abs(totalDebit - totalCredit);
            const isBalanced = (diff < 0.01) && (totalDebit > 0 || totalCredit > 0);
            const badge = $('#balance-badge');
            const submitBtn = $('#journal-entry-form button[type="submit"]');

            if (badge.length) {
                if (isBalanced) {
                    badge.removeClass('bg-red-100 text-red-800 border-red-300')
                         .addClass('bg-emerald-100 text-emerald-800 border border-emerald-300')
                         .html('<i class="fas fa-check-circle mr-1 text-emerald-600"></i> Balance (Selisih: Rp 0,00)');
                    submitBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                } else {
                    const diffText = formatNumber(diff);
                    badge.removeClass('bg-emerald-100 text-emerald-800 border-emerald-300')
                         .addClass('bg-red-100 text-red-800 border border-red-300')
                         .html('<i class="fas fa-exclamation-triangle mr-1 text-red-600"></i> Belum Balance (Selisih: Rp ' + diffText + ')');
                }
            }
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
                <input type="text" name="items[${index}][debits]" class="money-input debit-input w-full border rounded px-2 py-1 text-right font-mono" value="0,00"/>
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[${index}][credits]" class="money-input credit-input w-full border rounded px-2 py-1 text-right font-mono" value="0,00"/>
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[${index}][comment]" class="w-full border rounded px-2 py-1"/>
            </td>
            @can('specpose.access')
            <td class="border px-2 py-1">
                <select name="items[${index}][project_id]" class="w-full border rounded px-2 py-1">
                    <option value="">-- Pilih Specpose --</option>
                    @foreach ($projects as $prj)
                        <option value="{{ $prj->id }}">{{ $prj->nama_project }}</option>
                    @endforeach
                </select>
            </td>
            @endcan
            @can('fiscal.access')
            @if (($fiscalJournalMode ?? 'disabled') === 'enabled')
            <td class="border px-2 py-1 text-center">
                <input type="hidden" name="items[${index}][pajak]" value="0">
                <input type="checkbox" class="pajak-checkbox" name="items[${index}][pajak]" value="1">
            </td>
            <td class="border px-2 py-1 penyesuaian-col text-center">-</td>
            @elseif (($fiscalJournalMode ?? 'disabled') === 'hide')
            <input type="hidden" name="items[${index}][pajak]" value="0">
            <input type="hidden" name="items[${index}][penyesuaian_fiskal]" value="">
            <input type="hidden" name="items[${index}][kode_fiscal]" value="">
            @endif
            @endcan
            <td class="border px-2 py-1 text-center whitespace-nowrap space-x-1">
                <button type="button" title="Tambah Baris" class="add-row px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs font-bold">+</button>
                <button type="button" title="Auto-Balance (Seimbangkan Baris)" class="auto-balance px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs font-bold" data-index="${index}">⚡</button>
                <button type="button" title="Hapus Baris" class="remove-row px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs font-bold">X</button>
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

        // Auto-Balance (Seimbangkan Baris)
        $(document).on('click', '.auto-balance', function() {
            const row = $(this).closest('tr');
            let totalDebit = 0, totalCredit = 0;
            document.querySelectorAll('.debit-input').forEach(input => totalDebit += parseNumber(input.value));
            document.querySelectorAll('.credit-input').forEach(input => totalCredit += parseNumber(input.value));

            const curDebit = parseNumber(row.find('.debit-input').val());
            const curCredit = parseNumber(row.find('.credit-input').val());

            const otherDebit = totalDebit - curDebit;
            const otherCredit = totalCredit - curCredit;

            if (otherDebit > otherCredit) {
                row.find('.credit-input').val(formatNumber(otherDebit - otherCredit));
                row.find('.debit-input').val('0,00');
            } else if (otherCredit > otherDebit) {
                row.find('.debit-input').val(formatNumber(otherCredit - otherDebit));
                row.find('.credit-input').val('0,00');
            }
            updateTotals();
        });

        function confirmDeleteCurrentVoucher() {
            const voucherSource = @json($journalEntry->source);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Voucher Ini?',
                    html: `Apakah Anda yakin ingin menghapus voucher <strong>${voucherSource}</strong>?<br><br><span class="text-xs text-gray-500">Semua baris debit dan kredit pada voucher ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus Voucher',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-voucher-form').submit();
                    }
                });
            } else {
                if (confirm(`Apakah Anda yakin ingin menghapus voucher ${voucherSource} secara permanen? Semua baris debit dan kredit akan dihapus.`)) {
                    document.getElementById('delete-voucher-form').submit();
                }
            }
        }

        function handleCancel(e) {
            const cancelBtn = document.getElementById('btn-cancel');
            const targetUrl = cancelBtn ? cancelBtn.href : '{{ $safeBackUrl }}';
            if (window.opener && !window.opener.closed) {
                e.preventDefault();
                try {
                    window.close();
                } catch(err) {}
                setTimeout(function() {
                    if (!window.closed) {
                        window.location.href = targetUrl;
                    }
                }, 300);
            }
        }

        // ✅ Init & event submit aman (urutan paling bawah)
        $(document).ready(function() {
            // Deteksi jika halaman ini dibuka di tab baru dari tab lain (window.opener)
            const fromNewTabInput = document.getElementById('from_new_tab');
            if (fromNewTabInput) {
                if (window.opener && !window.opener.closed) {
                    fromNewTabInput.value = '1';
                }
            }

            $('.item-select').each(function() {
                attachSelect2($(this));
            });
            updateTotals();

            // ✅ Guard submit & cegah double-click
            let isSubmitting = false;
            $('#journal-entry-form').on('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                isSubmitting = true;
                $(this).find('button[type="submit"]').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');

                // Pastikan nilai back_url tidak mengandung entitas html &amp;
                const backUrlInput = document.getElementById('back_url');
                if (backUrlInput && backUrlInput.value) {
                    let val = backUrlInput.value;
                    while (val.indexOf('&amp;') !== -1) {
                        val = val.replace(/&amp;/g, '&');
                    }
                    backUrlInput.value = val;
                }
            });
        });
    </script>
@endpush

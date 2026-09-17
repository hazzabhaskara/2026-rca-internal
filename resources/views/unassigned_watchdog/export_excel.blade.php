<table>
    <thead>
        <tr>
            <th colspan="11" style="font-size: 14pt; font-weight: bold; text-align: center;">
                PT REMBULAN CITRA ABADI
            </th>
        </tr>
        <tr>
            <th colspan="11" style="font-size: 12pt; font-weight: bold; text-align: center;">
                LAPORAN AUDIT TRANSAKSI TANPA DEPARTEMEN (UNASSIGNED WATCHDOG)
            </th>
        </tr>
        <tr>
            <th colspan="11" style="font-size: 9pt; text-align: center; color: #555555;">
                Dicetak pada: {{ $exportDate ?? date('d/m/Y H:i:s') }}
                @if(!empty($startDate) || !empty($endDate))
                    | Periode: {{ $startDate ?? 'Awal' }} s/d {{ $endDate ?? 'Akhir' }}
                @endif
                @if(!empty($kategori) && $kategori !== 'all')
                    | Kategori: {{ strtoupper($kategori) }}
                @endif
                @if(!empty($tipeAkun) && $tipeAkun !== 'all')
                    | Tipe: {{ $tipeAkun }}
                @endif
                @if(!empty($sourcePrefix) && $sourcePrefix !== 'all')
                    | Prefix: {{ $sourcePrefix }}
                @endif
            </th>
        </tr>
        <tr>
            <th colspan="11"></th>
        </tr>
        {{-- KPI Summary Row --}}
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <th colspan="3" style="border: 1px solid #cbd5e1; text-align: left;">
                Total Transaksi: {{ number_format($kpis['total'] ?? count($details), 0, ',', '.') }}
            </th>
            <th colspan="3" style="border: 1px solid #cbd5e1; text-align: left;">
                Akun Riil: {{ number_format($kpis['real'] ?? 0, 0, ',', '.') }}
            </th>
            <th colspan="3" style="border: 1px solid #cbd5e1; text-align: left;">
                Akun Nominal: {{ number_format($kpis['nominal'] ?? 0, 0, ',', '.') }}
            </th>
            <th colspan="2" style="border: 1px solid #cbd5e1; text-align: right;">
                Baris Terfilter: {{ count($details) }}
            </th>
        </tr>
        <tr>
            <th colspan="11"></th>
        </tr>
        <tr style="background-color: #1e293b; color: #ffffff; font-weight: bold; text-align: center;">
            <th style="border: 1px solid #000000; width: 6; text-align: center;">NO</th>
            <th style="border: 1px solid #000000; width: 14; text-align: center;">TANGGAL</th>
            <th style="border: 1px solid #000000; width: 22; text-align: left;">NO. VOUCHER / SOURCE</th>
            <th style="border: 1px solid #000000; width: 16; text-align: center;">KODE AKUN</th>
            <th style="border: 1px solid #000000; width: 35; text-align: left;">NAMA AKUN</th>
            <th style="border: 1px solid #000000; width: 14; text-align: center;">KATEGORI</th>
            <th style="border: 1px solid #000000; width: 16; text-align: center;">TIPE AKUN</th>
            <th style="border: 1px solid #000000; width: 35; text-align: left;">KETERANGAN JURNAL</th>
            <th style="border: 1px solid #000000; width: 35; text-align: left;">KETERANGAN DETAIL</th>
            <th style="border: 1px solid #000000; width: 20; text-align: right;">DEBIT (RP)</th>
            <th style="border: 1px solid #000000; width: 20; text-align: right;">KREDIT (RP)</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($details as $index => $row)
            @php
                $tipe = $row->chartOfAccount->tipe_akun ?? '-';
                $isReal = in_array($tipe, ['Aset', 'Kewajiban', 'Ekuitas']);
                $kategoriLabel = $isReal ? 'Riil / Neraca' : 'Nominal / Laba Rugi';
            @endphp
            <tr>
                <td style="border: 1px solid #cccccc; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #cccccc; text-align: center;">
                    {{ $row->journalEntry->tanggal ? \Carbon\Carbon::parse($row->journalEntry->tanggal)->format('d/m/Y') : '-' }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: left;">
                    {{ $row->journalEntry->source ?? '-' }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: center; mso-number-format:'\@';">
                    {{ $row->kode_akun }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: left;">
                    {{ $row->chartOfAccount->nama_akun ?? '-' }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: center;">
                    {{ $kategoriLabel }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: center;">
                    {{ $tipe }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: left;">
                    {{ $row->journalEntry->comment ?? '-' }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: left;">
                    {{ $row->comment ?? '-' }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: right;">
                    {{ (float) $row->debits }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: right;">
                    {{ (float) $row->credits }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="11" style="border: 1px solid #cccccc; text-align: center; padding: 15px;">
                    Tidak ada transaksi tanpa departemen yang sesuai dengan filter.
                </td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr style="background-color: #1e293b; color: #ffffff; font-weight: bold;">
            <td colspan="9" style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                TOTAL MUTASI (RP)
            </td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                {{ (float) ($totalDebit ?? 0) }}
            </td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                {{ (float) ($totalCredit ?? 0) }}
            </td>
        </tr>
    </tfoot>
</table>

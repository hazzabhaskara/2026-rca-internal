@php
    $totalColspan = ($is_consolidated ? 7 : 6) + (!empty($show_lawan) ? 1 : 0);
    $labelColspan = ($is_consolidated ? 4 : 3) + (!empty($show_lawan) ? 1 : 0);
@endphp
<table>
    <thead>
        <tr>
            <th colspan="{{ $totalColspan }}" style="font-size: 14pt; font-weight: bold; text-align: center;">PT REMBULAN CITRA ABADI</th>
        </tr>
        <tr>
            <th colspan="{{ $totalColspan }}" style="font-size: 12pt; font-weight: bold; text-align: center;">LAPORAN BUKU BESAR (GENERAL LEDGER)</th>
        </tr>
        <tr>
            <th colspan="{{ $totalColspan }}" style="text-align: center;">
                Periode: {{ \Carbon\Carbon::parse($start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalColspan }}" style="text-align: center;">
                Departemen: {{ $departemen_name ?? 'Semua Departemen (Konsolidasi)' }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ $totalColspan }}"></th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandDebit = 0;
            $grandKredit = 0;
        @endphp

        @forelse ($groupedByAccount as $kodeAkunKey => $akunRows)
            @php
                $akun = $akunRows->account ?? optional($akunRows->first())->chartOfAccount ?? (isset($accounts) ? $accounts->firstWhere('kode_akun', $kodeAkunKey) : null);
                $kodeAkun = $akun->kode_akun ?? $kodeAkunKey;
                $namaAkun = $akun->nama_akun ?? '-';
                $tipeAkun = strtolower($akun->tipe_akun ?? '');
                $saldoAwal = $saldoAwalPerAkun[$kodeAkun] ?? 0;
                $saldoBerjalan = $saldoAwal;
                $totDebitAkun = $akunRows->sum('debits');
                $totKreditAkun = $akunRows->sum('credits');
                $grandDebit += $totDebitAkun;
                $grandKredit += $totKreditAkun;
            @endphp

            <!-- Account Header Row -->
            <tr style="background-color: #D9E1F2;">
                <td colspan="{{ $totalColspan }}" style="font-weight: bold;">
                    Akun: {{ $kodeAkun }} - {{ $namaAkun }} ({{ ucfirst($tipeAkun) }})
                </td>
            </tr>

            <!-- Table Columns Header -->
            <tr style="background-color: #F2F2F2; font-weight: bold;">
                <th style="border: 1px solid #000; text-align: center;">Tanggal</th>
                <th style="border: 1px solid #000; text-align: center;">No. Voucher</th>
                @if ($is_consolidated)
                    <th style="border: 1px solid #000; text-align: center;">Departemen</th>
                @endif
                @if (!empty($show_lawan))
                    <th style="border: 1px solid #000; text-align: left;">Lawan Transaksi</th>
                @endif
                <th style="border: 1px solid #000;">Keterangan</th>
                <th style="border: 1px solid #000; text-align: right;">Debet</th>
                <th style="border: 1px solid #000; text-align: right;">Kredit</th>
                <th style="border: 1px solid #000; text-align: right;">Saldo Berjalan</th>
            </tr>

            <!-- Opening Balance Row -->
            <tr style="background-color: #FAFAFA;">
                <td style="border: 1px solid #CCCCCC; text-align: center;">{{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }}</td>
                <td style="border: 1px solid #CCCCCC; text-align: center;">-</td>
                @if ($is_consolidated)
                    <td style="border: 1px solid #CCCCCC; text-align: center;">-</td>
                @endif
                @if (!empty($show_lawan))
                    <td style="border: 1px solid #CCCCCC; text-align: center;">-</td>
                @endif
                <td style="border: 1px solid #CCCCCC; font-style: italic;">Saldo Awal</td>
                <td style="border: 1px solid #CCCCCC; text-align: right;">0.00</td>
                <td style="border: 1px solid #CCCCCC; text-align: right;">0.00</td>
                <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold;">{{ number_format($saldoBerjalan, 2, '.', '') }}</td>
            </tr>

            <!-- Transactions -->
            @foreach ($akunRows as $row)
                @php
                    $d = (float) $row->debits;
                    $c = (float) $row->credits;
                    if (in_array($tipeAkun, ['aset', 'beban'])) {
                        $saldoBerjalan += $d - $c;
                    } else {
                        $saldoBerjalan += $c - $d;
                    }
                    $dept = $row->departemenAkun ? $row->departemenAkun->departemen : null;
                    $commentText = ($showComment ?? 'line_comment') === 'transaction_comment'
                        ? (optional($row->journalEntry)->comment ?? '-')
                        : ($row->comment ?? '-');
                @endphp
                <tr>
                    <td style="border: 1px solid #E0E0E0; text-align: center;">{{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
                    <td style="border: 1px solid #E0E0E0; text-align: center;">{{ $row->source ?? optional($row->journalEntry)->source ?? '-' }}</td>
                    @if ($is_consolidated)
                        <td style="border: 1px solid #E0E0E0; text-align: center;">{{ $dept ? ($dept->kode . ' - ' . $dept->deskripsi) : 'Non-Dept' }}</td>
                    @endif
                    @if (!empty($show_lawan))
                        <td style="border: 1px solid #E0E0E0;">{{ $row->contra_account_name ?? '-' }}</td>
                    @endif
                    <td style="border: 1px solid #E0E0E0;">{{ $commentText }}</td>
                    <td style="border: 1px solid #E0E0E0; text-align: right;">{{ number_format($d, 2, '.', '') }}</td>
                    <td style="border: 1px solid #E0E0E0; text-align: right;">{{ number_format($c, 2, '.', '') }}</td>
                    <td style="border: 1px solid #E0E0E0; text-align: right;">{{ number_format($saldoBerjalan, 2, '.', '') }}</td>
                </tr>
            @endforeach

            <!-- Account Total Row -->
            <tr style="background-color: #EAECEE; font-weight: bold;">
                <td colspan="{{ $labelColspan }}" style="border: 1px solid #000; text-align: right;">
                    Total Mutasi {{ $kodeAkun }} - {{ $namaAkun }}:
                </td>
                <td style="border: 1px solid #000; text-align: right;">{{ number_format($totDebitAkun, 2, '.', '') }}</td>
                <td style="border: 1px solid #000; text-align: right;">{{ number_format($totKreditAkun, 2, '.', '') }}</td>
                <td style="border: 1px solid #000; text-align: right;">{{ number_format($saldoBerjalan, 2, '.', '') }}</td>
            </tr>

            <!-- Blank separator row -->
            <tr>
                <td colspan="{{ $totalColspan }}"></td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $totalColspan }}" style="text-align: center; padding: 12px; font-style: italic;">
                    Tidak ada data transaksi atau akun yang ditemukan pada periode ini.
                </td>
            </tr>
        @endforelse

        <!-- Grand Total Row -->
        <tr style="background-color: #B4C6E7; font-weight: bold;">
            <td colspan="{{ $labelColspan }}" style="border: 2px solid #000; text-align: right;">GRAND TOTAL KESELURUHAN:</td>
            <td style="border: 2px solid #000; text-align: right;">{{ number_format($grandDebit, 2, '.', '') }}</td>
            <td style="border: 2px solid #000; text-align: right;">{{ number_format($grandKredit, 2, '.', '') }}</td>
            <td style="border: 2px solid #000; text-align: right;">-</td>
        </tr>
    </tbody>
</table>

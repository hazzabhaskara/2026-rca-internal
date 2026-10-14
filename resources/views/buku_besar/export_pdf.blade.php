<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Buku Besar - {{ $departemen_name ?? 'Konsolidasi' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm 10mm 10mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5px;
            color: #2d3748;
            margin: 0;
            padding: 0;
        }
        .header-container {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #4a5568;
            padding-bottom: 6px;
        }
        .company-title {
            font-size: 13px;
            font-weight: bold;
            color: #1a202c;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-title {
            font-size: 11px;
            font-weight: bold;
            color: #2b6cb0;
            margin: 2px 0;
        }
        .report-subtitle {
            font-size: 8.5px;
            color: #4a5568;
            margin: 1px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        thead {
            display: table-header-group;
        }
        tr {
            page-break-inside: avoid;
        }
        th {
            background-color: #edf2f7;
            color: #2d3748;
            font-weight: bold;
            padding: 4px 5px;
            border: 1px solid #cbd5e0;
            font-size: 8px;
        }
        td {
            padding: 3px 5px;
            border: 1px solid #e2e8f0;
            font-size: 8px;
        }
        .account-card-header {
            background-color: #ebf8ff;
            border: 1px solid #bee3f8;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 9px;
            color: #2b6cb0;
            margin-top: 10px;
            margin-bottom: 2px;
            page-break-inside: avoid;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bg-saldo-awal { background-color: #f7fafc; }
        .bg-total { background-color: #edf2f7; font-weight: bold; }
        .bg-grand { background-color: #e2e8f0; font-weight: bold; }
        .badge-dept {
            color: #3730a3;
            font-weight: bold;
        }
        .badge-nodept {
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <div class="company-title">PT REMBULAN CITRA ABADI</div>
        <div class="report-title">LAPORAN BUKU BESAR (GENERAL LEDGER)</div>
        <div class="report-subtitle">
            Periode: {{ \Carbon\Carbon::parse($start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}
            &nbsp;|&nbsp;
            Departemen: {{ $departemen_name ?? 'Semua Departemen (Konsolidasi)' }}
        </div>
    </div>

    @php
        $isConsolidated = empty($selected_departemen_id) || $selected_departemen_id === 'all';
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

        <div class="account-card-header">
            Akun: {{ $kodeAkun }} - {{ $namaAkun }} 
            <span style="font-size: 7.5px; font-weight: normal; color: #4a5568;">({{ ucfirst($tipeAkun) }})</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 65px;" class="text-center">Tanggal</th>
                    <th style="width: 85px;" class="text-center">No. Voucher</th>
                    @if ($isConsolidated)
                        <th style="width: 70px;" class="text-center">Departemen</th>
                    @endif
                    @if (!empty($show_lawan))
                        <th style="width: 95px;">Lawan Transaksi</th>
                    @endif
                    <th>Keterangan</th>
                    <th style="width: 80px;" class="text-right">Debet</th>
                    <th style="width: 80px;" class="text-right">Kredit</th>
                    <th style="width: 85px;" class="text-right">Saldo Berjalan</th>
                </tr>
            </thead>
            <tbody>
                <!-- Saldo Awal -->
                <tr class="bg-saldo-awal">
                    <td colspan="{{ ($isConsolidated ? 6 : 5) + (!empty($show_lawan) ? 1 : 0) }}" class="text-center" style="font-style: italic;">
                        Saldo Awal per {{ \Carbon\Carbon::parse($start_date)->format('d M Y') }}
                    </td>
                    <td class="text-right" style="font-weight: bold;">
                        {{ number_format($saldoBerjalan, 2, ',', '.') }}
                    </td>
                </tr>

                <!-- Transactions -->
                @forelse ($akunRows as $row)
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
                        <td class="text-center">{{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
                        <td class="text-center">{{ $row->source ?? optional($row->journalEntry)->source ?? '-' }}</td>
                        @if ($isConsolidated)
                            <td class="text-center">
                                @if ($dept)
                                    <span class="badge-dept">{{ $dept->kode }}</span>
                                @else
                                    <span class="badge-nodept">Non-Dept</span>
                                @endif
                            </td>
                        @endif
                        @if (!empty($show_lawan))
                            <td style="font-size: 7.5px;">{{ $row->contra_account_name ?? '-' }}</td>
                        @endif
                        <td>{{ $commentText }}</td>
                        <td class="text-right">{{ number_format($d, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($c, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($saldoBerjalan, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($isConsolidated ? 7 : 6) + (!empty($show_lawan) ? 1 : 0) }}" class="text-center" style="color: #718096; font-style: italic; padding: 6px;">
                            Tidak ada mutasi transaksi pada periode ini.
                        </td>
                    </tr>
                @endforelse

                <!-- Subtotal Akun -->
                <tr class="bg-total">
                    <td colspan="{{ ($isConsolidated ? 4 : 3) + (!empty($show_lawan) ? 1 : 0) }}" class="text-right">
                        Total Mutasi:
                    </td>
                    <td class="text-right">{{ number_format($totDebitAkun, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totKreditAkun, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($saldoBerjalan, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @empty
        <div style="text-align: center; padding: 25px; color: #718096; font-style: italic; border: 1px dashed #cbd5e0; margin: 20px 0;">
            Tidak ada data transaksi atau akun yang ditemukan pada kriteria filter yang dipilih.
        </div>
    @endforelse

    <!-- Grand Total Table -->
    <table style="margin-top: 15px; border: 2px solid #4a5568;">
        <tr class="bg-grand">
            <td colspan="{{ ($isConsolidated ? 4 : 3) + (!empty($show_lawan) ? 1 : 0) }}" class="text-right" style="font-size: 9px; padding: 6px;">
                GRAND TOTAL KESELURUHAN ({{ count($groupedByAccount) }} Akun):
            </td>
            <td style="width: 80px; font-size: 9px; padding: 6px;" class="text-right">
                {{ number_format($grandDebit, 2, ',', '.') }}
            </td>
            <td style="width: 80px; font-size: 9px; padding: 6px;" class="text-right">
                {{ number_format($grandKredit, 2, ',', '.') }}
            </td>
            <td style="width: 85px;" class="text-center">-</td>
        </tr>
    </table>
</body>
</html>

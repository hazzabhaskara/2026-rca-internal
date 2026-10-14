<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi Departemen - Matriks</title>
    <style>
        @page {
            margin: 8mm 8mm 8mm 8mm;
            size: A4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 6.5pt;
            color: #1e293b;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }
        .header-title {
            text-align: center;
            margin-bottom: 6px;
        }
        .header-title h2 {
            margin: 0;
            font-size: 11pt;
            color: #0f172a;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-title h3 {
            margin: 2px 0;
            font-size: 9pt;
            color: #1e1b4b;
            font-weight: bold;
        }
        .header-title p {
            margin: 1px 0 0 0;
            font-size: 7pt;
            color: #64748b;
        }

        table.matrix-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.matrix-table th,
        table.matrix-table td {
            border: 0.5px solid #cbd5e1;
            padding: 2.5px 3px;
            overflow: hidden;
            word-wrap: break-word;
        }
        table.matrix-table thead th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 6pt;
            text-align: right;
            vertical-align: middle;
        }
        table.matrix-table thead th.col-nama {
            text-align: left;
        }
        table.matrix-table thead th.col-total {
            background-color: #1e1b4b;
            color: #e0e7ff;
        }
        table.matrix-table thead th.col-elim {
            background-color: #451a03;
            color: #fef3c7;
        }

        .tr-section {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            font-size: 6.5pt;
        }
        .tr-section td {
            border: 0.5px solid #0f172a;
        }

        .tr-group {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #1e293b;
            font-size: 6.5pt;
        }

        .tr-subtotal {
            background-color: #e0e7ff;
            color: #1e1b4b;
            font-weight: bold;
            font-size: 6.5pt;
        }
        .tr-subtotal td {
            border: 1px solid #c7d2fe;
        }

        .tr-bottomline {
            background-color: #065f46;
            color: #ffffff;
            font-weight: bold;
            font-size: 7.5pt;
        }
        .tr-bottomline td {
            border: 1.5px solid #064e3b;
        }

        .tr-reconcile {
            background-color: #0f172a;
            color: #94a3b8;
            font-size: 6pt;
        }

        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'DejaVu Sans Mono', monospace; font-size: 6pt; }
    </style>
</head>
<body>

    <div class="header-title">
        <h2>{{ $siteTitle ?? 'PT REMBULAN CITRA ABADI' }}</h2>
        <h3>LAPORAN LABA RUGI DEPARTEMEN (MATRIKS KOMPARATIF)</h3>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
    </div>

    <table class="matrix-table">
        <thead>
            <tr>
                <th class="col-nama" style="width: 20%;">KETERANGAN / NAMA AKUN</th>
                <th class="col-total" style="width: 8.5%;">TOTAL KONSOLIDASI</th>
                @foreach ($departments as $dept)
                    <th style="width: 7.1%;">
                        {{ $dept->kode }}<br>{{ $dept->deskripsi }}
                    </th>
                @endforeach
                <th class="col-elim" style="width: 7.5%;">ELIMINASI</th>
                <th style="width: 6.5%;">NON-DEPT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sections as $secKey => $sec)
                @if (!empty($sec['row_type']))
                    @php
                        $isNetProfit = ($sec['row_type'] === 'subtotal_net_profit');
                        $trClass = $isNetProfit ? 'tr-bottomline' : 'tr-subtotal';
                        $secTotals = $sec['totals'] ?? ($sec['balances'] ?? []);
                    @endphp
                    <tr class="{{ $trClass }}">
                        <td class="text-left" style="padding-left: 4px;">
                            {{ strtoupper($sec['title']) }}
                            @if (!empty($sec['marginPercent'])) ({{ $sec['marginPercent']['total'] ?? 0 }}% GM) @endif
                            @if (!empty($sec['opexRatioPercent'])) ({{ $sec['opexRatioPercent']['total'] ?? 0 }}% Rasio) @endif
                        </td>
                        <td class="text-right font-mono">{{ number_format($secTotals['total'] ?? 0, 0, ',', '.') }}</td>
                        @foreach ($departments as $dept)
                            <td class="text-right font-mono">{{ number_format($secTotals[$dept->id] ?? 0, 0, ',', '.') }}</td>
                        @endforeach
                        <td class="text-right font-mono">{{ number_format($secTotals['eliminasi'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ number_format($secTotals['unassigned'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @else
                    @php $secTotals = $sec['totals'] ?? ($sec['balances'] ?? []); @endphp
                    <tr class="tr-section">
                        <td class="text-left" style="padding-left: 4px;">{{ strtoupper($sec['title']) }}</td>
                        <td class="text-right font-mono">{{ number_format($secTotals['total'] ?? 0, 0, ',', '.') }}</td>
                        @foreach ($departments as $dept)
                            <td class="text-right font-mono">{{ number_format($secTotals[$dept->id] ?? 0, 0, ',', '.') }}</td>
                        @endforeach
                        <td class="text-right font-mono">{{ number_format($secTotals['eliminasi'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ number_format($secTotals['unassigned'] ?? 0, 0, ',', '.') }}</td>
                    </tr>

                    @foreach ($sec['rows'] ?? ($sec['children'] ?? []) as $acc)
                        @php
                            $isZero = true;
                            $accTotals = $acc['balances'] ?? ($acc['totals'] ?? []);
                            foreach ($allColKeys as $ck) {
                                if (abs($accTotals[$ck] ?? 0) >= 0.01) { $isZero = false; break; }
                            }
                        @endphp
                        @if (!$hideAccountWithZero || !$isZero)
                            <tr>
                                <td class="text-left" style="padding-left: 8px;">
                                    @if($showAccountNumber){{ $acc['kode_akun'] ?? $acc['kode'] }} - @endif{{ $acc['nama_akun'] ?? $acc['nama'] }}
                                </td>
                                <td class="text-right font-mono">{{ number_format($accTotals['total'] ?? 0, 0, ',', '.') }}</td>
                                @foreach ($departments as $dept)
                                    <td class="text-right font-mono">{{ number_format($accTotals[$dept->id] ?? 0, 0, ',', '.') }}</td>
                                @endforeach
                                <td class="text-right font-mono">{{ number_format($accTotals['eliminasi'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-right font-mono">{{ number_format($accTotals['unassigned'] ?? 0, 0, ',', '.') }}</td>
                            </tr>

                            @if (!empty($acc['sub_accounts']) || !empty($acc['children']))
                                @foreach ($acc['sub_accounts'] ?? $acc['children'] as $sub)
                                    @php
                                        $isSubZero = true;
                                        $subTotals = $sub['balances'] ?? ($sub['totals'] ?? []);
                                        foreach ($allColKeys as $ck) {
                                            if (abs($subTotals[$ck] ?? 0) >= 0.01) { $isSubZero = false; break; }
                                        }
                                    @endphp
                                    @if (!$hideAccountWithZero || !$isSubZero)
                                        <tr style="color: #64748b;">
                                            <td class="text-left" style="padding-left: 14px;">
                                                @if($showAccountNumber){{ $sub['kode_akun'] ?? $sub['kode'] }} - @endif{{ $sub['nama_akun'] ?? $sub['nama'] }}
                                            </td>
                                            <td class="text-right font-mono">{{ number_format($subTotals['total'] ?? 0, 0, ',', '.') }}</td>
                                            @foreach ($departments as $dept)
                                                <td class="text-right font-mono">{{ number_format($subTotals[$dept->id] ?? 0, 0, ',', '.') }}</td>
                                            @endforeach
                                            <td class="text-right font-mono">{{ number_format($subTotals['eliminasi'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-right font-mono">{{ number_format($subTotals['unassigned'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Rekonsiliasi Neraca --}}
            <tr class="tr-reconcile">
                <td class="text-left" style="padding-left: 4px;">Laba Tahun Berjalan di Ekuitas Neraca</td>
                <td class="text-right font-mono">{{ number_format($reconciliation['neracaPerDept']['total'] ?? 0, 0, ',', '.') }}</td>
                @foreach ($departments as $dept)
                    <td class="text-right font-mono">{{ number_format($reconciliation['neracaPerDept'][$dept->id] ?? 0, 0, ',', '.') }}</td>
                @endforeach
                <td class="text-right font-mono">0</td>
                <td class="text-right font-mono">{{ number_format($reconciliation['neracaPerDept']['unassigned'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>

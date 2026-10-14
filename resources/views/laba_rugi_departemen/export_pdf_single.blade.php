<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi Departemen - Single</title>
    <style>
        @page {
            margin: 12mm 12mm 12mm 12mm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header-title {
            text-align: center;
            margin-bottom: 12px;
        }
        .header-title h2 {
            margin: 0;
            font-size: 13pt;
            color: #0f172a;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-title h3 {
            margin: 3px 0;
            font-size: 10.5pt;
            color: #1e1b4b;
            font-weight: bold;
        }
        .header-title p {
            margin: 2px 0 0 0;
            font-size: 8pt;
            color: #64748b;
        }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.report-table th,
        table.report-table td {
            border: 0.5px solid #cbd5e1;
            padding: 4px 6px;
        }
        table.report-table thead th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 7.5pt;
            vertical-align: middle;
        }

        .tr-section {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            font-size: 8pt;
        }
        .tr-section td {
            border: 0.5px solid #0f172a;
        }

        .tr-subtotal {
            background-color: #e0e7ff;
            color: #1e1b4b;
            font-weight: bold;
            font-size: 8pt;
        }
        .tr-subtotal td {
            border: 1px solid #c7d2fe;
        }

        .tr-bottomline {
            background-color: #065f46;
            color: #ffffff;
            font-weight: bold;
            font-size: 9pt;
        }
        .tr-bottomline td {
            border: 1.5px solid #064e3b;
        }

        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'DejaVu Sans Mono', monospace; font-size: 7.5pt; }
    </style>
</head>
<body>

    <div class="header-title">
        <h2>{{ $siteTitle ?? 'PT REMBULAN CITRA ABADI' }}</h2>
        <h3>LAPORAN LABA RUGI DEPARTEMEN</h3>
        <p><strong>{{ $singleDept->kode ?? '' }} - {{ $singleDept->deskripsi ?? 'Departemen' }}</strong></p>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 18%;" class="text-left">KODE AKUN</th>
                <th style="width: 50%;" class="text-left">NAMA AKUN</th>
                <th style="width: 18%;" class="text-right">SALDO NOMINAL</th>
                <th style="width: 14%;" class="text-right">% PENDAPATAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sections as $secKey => $sec)
                @if (!empty($sec['row_type']))
                    @php
                        $isNetProfit = ($sec['row_type'] === 'subtotal_net_profit');
                        $trClass = $isNetProfit ? 'tr-bottomline' : 'tr-subtotal';
                        $val = $sec['totals'][$selectedDeptId] ?? ($sec['balances'][$selectedDeptId] ?? ($sec['single_balance'] ?? 0));
                        $pct = ($kpi['totalRevenue'] ?? 0) > 0 ? round(($val / $kpi['totalRevenue']) * 100, 2) : 0;
                    @endphp
                    <tr class="{{ $trClass }}">
                        <td colspan="2" class="text-left">{{ strtoupper($sec['title']) }}</td>
                        <td class="text-right font-mono">{{ number_format($val, 0, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ $pct }}%</td>
                    </tr>
                @else
                    @php
                        $secVal = $sec['totals'][$selectedDeptId] ?? ($sec['balances'][$selectedDeptId] ?? ($sec['single_balance'] ?? 0));
                        $secPct = ($kpi['totalRevenue'] ?? 0) > 0 ? round(($secVal / $kpi['totalRevenue']) * 100, 2) : 0;
                    @endphp
                    <tr class="tr-section">
                        <td colspan="2" class="text-left">{{ strtoupper($sec['title']) }}</td>
                        <td class="text-right font-mono">{{ number_format($secVal, 0, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ $secPct }}%</td>
                    </tr>

                    @foreach ($sec['rows'] ?? ($sec['children'] ?? []) as $acc)
                        @php
                            $val = $acc['balances'][$selectedDeptId] ?? ($acc['totals'][$selectedDeptId] ?? ($acc['single_balance'] ?? 0));
                            $accPct = ($kpi['totalRevenue'] ?? 0) > 0 ? round(($val / $kpi['totalRevenue']) * 100, 2) : 0;
                        @endphp
                        @if (!$hideAccountWithZero || abs($val) >= 0.01)
                            <tr>
                                <td class="text-left font-mono" style="padding-left: 8px;">{{ $acc['kode_akun'] ?? $acc['kode'] }}</td>
                                <td class="text-left" style="padding-left: 8px;">{{ $acc['nama_akun'] ?? $acc['nama'] }}</td>
                                <td class="text-right font-mono">{{ number_format($val, 0, ',', '.') }}</td>
                                <td class="text-right font-mono">{{ $accPct }}%</td>
                            </tr>

                            @if (!empty($acc['sub_accounts']) || !empty($acc['children']))
                                @foreach ($acc['sub_accounts'] ?? $acc['children'] as $sub)
                                    @php
                                        $subVal = $sub['balances'][$selectedDeptId] ?? ($sub['totals'][$selectedDeptId] ?? ($sub['single_balance'] ?? 0));
                                        $subPct = ($kpi['totalRevenue'] ?? 0) > 0 ? round(($subVal / $kpi['totalRevenue']) * 100, 2) : 0;
                                    @endphp
                                    @if (!$hideAccountWithZero || abs($subVal) >= 0.01)
                                        <tr style="color: #64748b;">
                                            <td class="text-left font-mono" style="padding-left: 14px;">{{ $sub['kode_akun'] ?? $sub['kode'] }}</td>
                                            <td class="text-left" style="padding-left: 14px;">{{ $sub['nama_akun'] ?? $sub['nama'] }}</td>
                                            <td class="text-right font-mono">{{ number_format($subVal, 0, ',', '.') }}</td>
                                            <td class="text-right font-mono">{{ $subPct }}%</td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif
                        @endif
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>

</body>
</html>

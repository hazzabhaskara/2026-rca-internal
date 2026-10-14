<table>
    <thead>
        <tr>
            <th colspan="4" style="font-size: 14pt; font-weight: bold; text-align: center;">
                {{ $siteTitle ?? 'PT REMBULAN CITRA ABADI' }}
            </th>
        </tr>
        <tr>
            <th colspan="4" style="font-size: 12pt; font-weight: bold; text-align: center;">
                LAPORAN LABA RUGI DEPARTEMEN (SINGLE DEPARTEMEN)
            </th>
        </tr>
        <tr>
            <th colspan="4" style="font-size: 11pt; font-weight: bold; text-align: center;">
                {{ $singleDept->kode ?? '' }} - {{ $singleDept->deskripsi ?? 'Departemen' }}
            </th>
        </tr>
        <tr>
            <th colspan="4" style="font-size: 10pt; text-align: center;">
                Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </th>
        </tr>
        <tr><th colspan="4"></th></tr>
        <tr style="background-color: #1e293b; color: #ffffff; font-weight: bold;">
            <th style="border: 1px solid #000000; width: 18; text-align: left;">KODE AKUN</th>
            <th style="border: 1px solid #000000; width: 45; text-align: left;">NAMA AKUN</th>
            <th style="border: 1px solid #000000; width: 22; text-align: right;">SALDO NOMINAL</th>
            <th style="border: 1px solid #000000; width: 18; text-align: right;">% PENDAPATAN</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sections as $secKey => $sec)
            @if (!empty($sec['row_type']))
                @php
                    $isNetProfit = ($sec['row_type'] === 'subtotal_net_profit');
                    $bgColor = $isNetProfit ? '#065f46' : '#e0e7ff';
                    $textColor = $isNetProfit ? '#ffffff' : '#000000';
                    $val = $sec['totals'][$selectedDeptId] ?? ($sec['balances'][$selectedDeptId] ?? ($sec['single_balance'] ?? 0));
                    $pct = ($kpi['totalRevenue'] ?? 0) > 0 ? round(($val / $kpi['totalRevenue']) * 100, 2) : 0;
                @endphp
                <tr style="background-color: {{ $bgColor }}; color: {{ $textColor }}; font-weight: bold;">
                    <td colspan="2" style="border: 1px solid #000000; font-weight: bold;">{{ strtoupper($sec['title']) }}</td>
                    <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $val }}</td>
                    <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $pct }}%</td>
                </tr>
            @else
                @php
                    $secVal = $sec['totals'][$selectedDeptId] ?? ($sec['balances'][$selectedDeptId] ?? ($sec['single_balance'] ?? 0));
                    $secPct = ($kpi['totalRevenue'] ?? 0) > 0 ? round(($secVal / $kpi['totalRevenue']) * 100, 2) : 0;
                @endphp
                <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
                    <td colspan="2" style="border: 1px solid #000000;">{{ strtoupper($sec['title']) }}</td>
                    <td style="border: 1px solid #000000; text-align: right;">{{ $secVal }}</td>
                    <td style="border: 1px solid #000000; text-align: right;">{{ $secPct }}%</td>
                </tr>

                @foreach ($sec['rows'] ?? ($sec['children'] ?? []) as $acc)
                    @php
                        $val = $acc['balances'][$selectedDeptId] ?? ($acc['totals'][$selectedDeptId] ?? ($acc['single_balance'] ?? 0));
                        $accPct = ($kpi['totalRevenue'] ?? 0) > 0 ? round(($val / $kpi['totalRevenue']) * 100, 2) : 0;
                    @endphp
                    @if (!$hideAccountWithZero || abs($val) >= 0.01)
                        <tr>
                            <td style="border: 1px solid #000000;">&nbsp;&nbsp;{{ $acc['kode_akun'] ?? $acc['kode'] }}</td>
                            <td style="border: 1px solid #000000;">&nbsp;&nbsp;{{ $acc['nama_akun'] ?? $acc['nama'] }}</td>
                            <td style="border: 1px solid #000000; text-align: right;">{{ $val }}</td>
                            <td style="border: 1px solid #000000; text-align: right;">{{ $accPct }}%</td>
                        </tr>

                        @if (!empty($acc['sub_accounts']) || !empty($acc['children']))
                            @foreach ($acc['sub_accounts'] ?? $acc['children'] as $sub)
                                @php
                                    $subVal = $sub['balances'][$selectedDeptId] ?? ($sub['totals'][$selectedDeptId] ?? ($sub['single_balance'] ?? 0));
                                    $subPct = ($kpi['totalRevenue'] ?? 0) > 0 ? round(($subVal / $kpi['totalRevenue']) * 100, 2) : 0;
                                @endphp
                                @if (!$hideAccountWithZero || abs($subVal) >= 0.01)
                                    <tr style="color: #64748b;">
                                        <td style="border: 1px solid #000000;">&nbsp;&nbsp;&nbsp;&nbsp;{{ $sub['kode_akun'] ?? $sub['kode'] }}</td>
                                        <td style="border: 1px solid #000000;">&nbsp;&nbsp;&nbsp;&nbsp;{{ $sub['nama_akun'] ?? $sub['nama'] }}</td>
                                        <td style="border: 1px solid #000000; text-align: right;">{{ $subVal }}</td>
                                        <td style="border: 1px solid #000000; text-align: right;">{{ $subPct }}%</td>
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

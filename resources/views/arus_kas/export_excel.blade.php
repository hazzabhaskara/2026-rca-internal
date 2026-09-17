<table border="1" cellspacing="0" cellpadding="5">
    <tr>
        <th colspan="8" style="font-size: 16px; font-weight: bold; text-align: center;">
            LAPORAN CASH FLOW
        </th>
    </tr>
    <tr>
        <th colspan="8" style="text-align: center;">
            Periode: {{ \Carbon\Carbon::parse($tanggalAwal)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d M Y') }}
        </th>
    </tr>
    <tr><td colspan="8"></td></tr>

    {{-- ========================================= --}}
    {{-- MODE 1: DETAIL PER SOURCE --}}
    {{-- ========================================= --}}
    @if($displayMode == 'source')
        @php
            $grouped = collect($rows)->groupBy('source');
            $grandCashIn = 0;
            $grandCashOut = 0;
        @endphp

        <tr style="background-color: #f0f0f0; font-weight: bold;">
            <th>Tanggal</th>
            <th>Source</th>
            <th>Akun Kas/Bank</th>
            <th>Lawan Akun</th>
            <th>Keterangan</th>
            <th style="text-align: right;">Cash In</th>
            <th style="text-align: right;">Cash Out</th>
            <th></th>
        </tr>

        @foreach ($grouped as $source => $items)
            <tr style="background-color: #e0e0e0; font-weight: bold;">
                <td colspan="8">Source: {{ $source }}</td>
            </tr>

            @php
                $subtotalIn = 0;
                $subtotalOut = 0;
            @endphp

            @foreach ($items as $r)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($r['tanggal'])->format('d/m/Y') }}</td>
                    <td>{{ $r['source'] }}</td>
                    <td>{{ $r['akun_kas'] }}</td>
                    <td>{{ $r['lawan_akun'] }}</td>
                    <td>{{ $r['keterangan'] }}</td>
                    <td style="text-align: right;">{{ number_format($r['cash_in'], 2) }}</td>
                    <td style="text-align: right;">{{ number_format($r['cash_out'], 2) }}</td>
                    <td></td>
                </tr>
                @php
                    $subtotalIn += $r['cash_in'];
                    $subtotalOut += $r['cash_out'];
                @endphp
            @endforeach

            <tr style="background-color: #f5f5f5; font-weight: bold;">
                <td colspan="5" style="text-align: right;">Subtotal ({{ $source }})</td>
                <td style="text-align: right;">{{ number_format($subtotalIn, 2) }}</td>
                <td style="text-align: right;">{{ number_format($subtotalOut, 2) }}</td>
                <td></td>
            </tr>

            @php
                $grandCashIn += $subtotalIn;
                $grandCashOut += $subtotalOut;
            @endphp
        @endforeach

        <tr style="background-color: #d0d0d0; font-weight: bold;">
            <td colspan="5" style="text-align: right;">TOTAL KESELURUHAN</td>
            <td style="text-align: right;">{{ number_format($grandCashIn, 2) }}</td>
            <td style="text-align: right;">{{ number_format($grandCashOut, 2) }}</td>
            <td></td>
        </tr>

    {{-- ========================================= --}}
    {{-- MODE 2: PER ACCOUNT KAS/BANK --}}
    {{-- ========================================= --}}
    @elseif($displayMode == 'account')
        @php
            $grouped = collect($rows)->groupBy('akun_kas');
            $grandCashIn = 0;
            $grandCashOut = 0;
        @endphp

        <tr style="background-color: #f0f0f0; font-weight: bold;">
            <th>Tanggal</th>
            <th>Source</th>
            <th>Lawan Akun</th>
            <th>Line Comment</th>
            <th style="text-align: right;">Cash In</th>
            <th style="text-align: right;">Cash Out</th>
            <th style="text-align: right;">Net</th>
            <th></th>
        </tr>

        @foreach ($grouped as $akunKas => $items)
            @php
                $subtotalIn = $items->sum('cash_in');
                $subtotalOut = $items->sum('cash_out');
                $netCashFlow = $subtotalIn - $subtotalOut;
                $grandCashIn += $subtotalIn;
                $grandCashOut += $subtotalOut;
            @endphp

            <tr style="background-color: #cce5ff; font-weight: bold;">
                <td colspan="4">{{ $akunKas }}</td>
                <td style="text-align: right;">{{ number_format($subtotalIn, 2) }}</td>
                <td style="text-align: right;">{{ number_format($subtotalOut, 2) }}</td>
                <td style="text-align: right; {{ $netCashFlow >= 0 ? 'color: green;' : 'color: red;' }}">{{ number_format($netCashFlow, 2) }}</td>
                <td></td>
            </tr>

            @foreach ($items as $r)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($r['tanggal'])->format('d/m/Y') }}</td>
                    <td>{{ $r['source'] }}</td>
                    <td>{{ $r['lawan_akun'] }}</td>
                    <td>{{ $r['line_comment'] }}</td>
                    <td style="text-align: right;">{{ number_format($r['cash_in'], 2) }}</td>
                    <td style="text-align: right;">{{ number_format($r['cash_out'], 2) }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        @endforeach

        <tr style="background-color: #d0d0d0; font-weight: bold;">
            <td colspan="4" style="text-align: right;">TOTAL KESELURUHAN</td>
            <td style="text-align: right;">{{ number_format($grandCashIn, 2) }}</td>
            <td style="text-align: right;">{{ number_format($grandCashOut, 2) }}</td>
            <td style="text-align: right; {{ ($grandCashIn - $grandCashOut) >= 0 ? 'color: green;' : 'color: red;' }}">{{ number_format($grandCashIn - $grandCashOut, 2) }}</td>
            <td></td>
        </tr>

    {{-- ========================================= --}}
    {{-- MODE 3: UNIVERSAL --}}
    {{-- ========================================= --}}
    @else
        @php
            $grandCashIn = collect($rows)->sum('cash_in');
            $grandCashOut = collect($rows)->sum('cash_out');
        @endphp

        <tr style="background-color: #f0f0f0; font-weight: bold;">
            <th>Tanggal</th>
            <th>Source</th>
            <th>Akun Kas/Bank</th>
            <th>Lawan Akun</th>
            <th>Line Comment</th>
            <th>Keterangan</th>
            <th style="text-align: right;">Cash In</th>
            <th style="text-align: right;">Cash Out</th>
        </tr>

        @foreach ($rows as $r)
            <tr>
                <td>{{ \Carbon\Carbon::parse($r['tanggal'])->format('d/m/Y') }}</td>
                <td>{{ $r['source'] }}</td>
                <td>{{ $r['akun_kas'] }}</td>
                <td>{{ $r['lawan_akun'] }}</td>
                <td>{{ $r['line_comment'] }}</td>
                <td>{{ $r['keterangan'] }}</td>
                <td style="text-align: right;">{{ number_format($r['cash_in'], 2) }}</td>
                <td style="text-align: right;">{{ number_format($r['cash_out'], 2) }}</td>
            </tr>
        @endforeach

        <tr style="background-color: #d0d0d0; font-weight: bold;">
            <td colspan="6" style="text-align: right;">TOTAL KESELURUHAN</td>
            <td style="text-align: right;">{{ number_format($grandCashIn, 2) }}</td>
            <td style="text-align: right;">{{ number_format($grandCashOut, 2) }}</td>
        </tr>
        <tr style="background-color: #e0e0e0; font-weight: bold;">
            <td colspan="6" style="text-align: right;">NET CASH FLOW</td>
            <td colspan="2" style="text-align: right; {{ ($grandCashIn - $grandCashOut) >= 0 ? 'color: green;' : 'color: red;' }}">
                {{ number_format($grandCashIn - $grandCashOut, 2) }}
            </td>
        </tr>
    @endif
</table>

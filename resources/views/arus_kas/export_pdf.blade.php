<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Cash Flow</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; }
        h1 { font-size: 14px; text-align: center; margin-bottom: 5px; }
        .periode { text-align: center; margin-bottom: 10px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 4px; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bg-header { background-color: #cce5ff; font-weight: bold; }
        .bg-subtotal { background-color: #f5f5f5; font-weight: bold; }
        .bg-total { background-color: #d0d0d0; font-weight: bold; }
        .text-green { color: green; }
        .text-red { color: red; }
    </style>
</head>
<body>
    <h1>LAPORAN CASH FLOW</h1>
    <div class="periode">
        Periode: {{ \Carbon\Carbon::parse($tanggalAwal)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d M Y') }}
    </div>

    {{-- ========================================= --}}
    {{-- MODE 1: DETAIL PER SOURCE --}}
    {{-- ========================================= --}}
    @if($displayMode == 'source')
        @php
            $grouped = collect($rows)->groupBy('source');
            $grandCashIn = 0;
            $grandCashOut = 0;
        @endphp

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Source</th>
                    <th>Akun Kas/Bank</th>
                    <th>Lawan Akun</th>
                    <th>Keterangan</th>
                    <th class="text-right">Cash In</th>
                    <th class="text-right">Cash Out</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($grouped as $source => $items)
                    <tr class="bg-header">
                        <td colspan="7" class="text-left">Source: {{ $source }}</td>
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
                            <td class="text-right">{{ number_format($r['cash_in'], 2) }}</td>
                            <td class="text-right">{{ number_format($r['cash_out'], 2) }}</td>
                        </tr>
                        @php
                            $subtotalIn += $r['cash_in'];
                            $subtotalOut += $r['cash_out'];
                        @endphp
                    @endforeach

                    <tr class="bg-subtotal">
                        <td colspan="5" class="text-right">Subtotal ({{ $source }})</td>
                        <td class="text-right">{{ number_format($subtotalIn, 2) }}</td>
                        <td class="text-right">{{ number_format($subtotalOut, 2) }}</td>
                    </tr>

                    @php
                        $grandCashIn += $subtotalIn;
                        $grandCashOut += $subtotalOut;
                    @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-total">
                    <td colspan="5" class="text-right">TOTAL KESELURUHAN</td>
                    <td class="text-right">{{ number_format($grandCashIn, 2) }}</td>
                    <td class="text-right">{{ number_format($grandCashOut, 2) }}</td>
                </tr>
            </tfoot>
        </table>

    {{-- ========================================= --}}
    {{-- MODE 2: PER ACCOUNT KAS/BANK --}}
    {{-- ========================================= --}}
    @elseif($displayMode == 'account')
        @php
            $grouped = collect($rows)->groupBy('akun_kas');
            $grandCashIn = 0;
            $grandCashOut = 0;
        @endphp

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Source</th>
                    <th>Lawan Akun</th>
                    <th>Line Comment</th>
                    <th class="text-right">Cash In</th>
                    <th class="text-right">Cash Out</th>
                    <th class="text-right">Net</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($grouped as $akunKas => $items)
                    @php
                        $subtotalIn = $items->sum('cash_in');
                        $subtotalOut = $items->sum('cash_out');
                        $netCashFlow = $subtotalIn - $subtotalOut;
                        $grandCashIn += $subtotalIn;
                        $grandCashOut += $subtotalOut;
                    @endphp

                    <tr class="bg-header">
                        <td colspan="4" class="text-left">{{ $akunKas }}</td>
                        <td class="text-right">{{ number_format($subtotalIn, 2) }}</td>
                        <td class="text-right">{{ number_format($subtotalOut, 2) }}</td>
                        <td class="text-right {{ $netCashFlow >= 0 ? 'text-green' : 'text-red' }}">{{ number_format($netCashFlow, 2) }}</td>
                    </tr>

                    @foreach ($items as $r)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($r['tanggal'])->format('d/m/Y') }}</td>
                            <td>{{ $r['source'] }}</td>
                            <td>{{ $r['lawan_akun'] }}</td>
                            <td>{{ $r['line_comment'] }}</td>
                            <td class="text-right">{{ number_format($r['cash_in'], 2) }}</td>
                            <td class="text-right">{{ number_format($r['cash_out'], 2) }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-total">
                    <td colspan="4" class="text-right">TOTAL KESELURUHAN</td>
                    <td class="text-right">{{ number_format($grandCashIn, 2) }}</td>
                    <td class="text-right">{{ number_format($grandCashOut, 2) }}</td>
                    <td class="text-right {{ ($grandCashIn - $grandCashOut) >= 0 ? 'text-green' : 'text-red' }}">{{ number_format($grandCashIn - $grandCashOut, 2) }}</td>
                </tr>
            </tfoot>
        </table>

    {{-- ========================================= --}}
    {{-- MODE 3: UNIVERSAL --}}
    {{-- ========================================= --}}
    @else
        @php
            $grandCashIn = collect($rows)->sum('cash_in');
            $grandCashOut = collect($rows)->sum('cash_out');
        @endphp

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Source</th>
                    <th>Akun Kas/Bank</th>
                    <th>Lawan Akun</th>
                    <th>Line Comment</th>
                    <th>Keterangan</th>
                    <th class="text-right">Cash In</th>
                    <th class="text-right">Cash Out</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $r)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($r['tanggal'])->format('d/m/Y') }}</td>
                        <td>{{ $r['source'] }}</td>
                        <td>{{ $r['akun_kas'] }}</td>
                        <td>{{ $r['lawan_akun'] }}</td>
                        <td>{{ $r['line_comment'] }}</td>
                        <td>{{ $r['keterangan'] }}</td>
                        <td class="text-right">{{ number_format($r['cash_in'], 2) }}</td>
                        <td class="text-right">{{ number_format($r['cash_out'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-total">
                    <td colspan="6" class="text-right">TOTAL KESELURUHAN</td>
                    <td class="text-right">{{ number_format($grandCashIn, 2) }}</td>
                    <td class="text-right">{{ number_format($grandCashOut, 2) }}</td>
                </tr>
                <tr class="bg-subtotal">
                    <td colspan="6" class="text-right">NET CASH FLOW</td>
                    <td colspan="2" class="text-right {{ ($grandCashIn - $grandCashOut) >= 0 ? 'text-green' : 'text-red' }}">
                        {{ number_format($grandCashIn - $grandCashOut, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif

</body>
</html>

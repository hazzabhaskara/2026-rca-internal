<table border="1" cellspacing="0" cellpadding="5">
    <tr>
        <th colspan="8" style="font-size: 16px; font-weight: bold; text-align: center;">
            LAPORAN ARUS KAS (CASH FLOW)
        </th>
    </tr>
    <tr>
        <th colspan="8" style="text-align: center;">
            Periode: {{ \Carbon\Carbon::parse($tanggalAwal)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d M Y') }}
        </th>
    </tr>
    <tr>
        <th colspan="8" style="text-align: center; font-style: italic;">
            Mode: {{ strtoupper($displayMode) }}
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
        <tr style="background-color: #e0e0e0; font-weight: bold;">
            <td colspan="5" style="text-align: right;">KENAIKAN / (PENURUNAN) BERSIH KAS</td>
            <td colspan="2" style="text-align: right; {{ ($grandCashIn - $grandCashOut) >= 0 ? 'color: green;' : 'color: red;' }}">
                {{ number_format($grandCashIn - $grandCashOut, 2) }}
            </td>
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
    {{-- MODE 4: DIRECT METHOD (METODE LANGSUNG) --}}
    {{-- ========================================= --}}
    @elseif($displayMode == 'direct')
        <tr style="background-color: #f0f0f0; font-weight: bold;">
            <th colspan="4">Aktivitas / Klasifikasi Akun</th>
            <th style="text-align: right;">Penerimaan (In)</th>
            <th style="text-align: right;">Pengeluaran (Out)</th>
            <th colspan="2" style="text-align: right;">Arus Kas Bersih</th>
        </tr>

        @foreach ($aktivitasData as $aktKey => $akt)
            <tr style="background-color: #dbeafe; font-weight: bold;">
                <td colspan="8">{{ $akt['label'] }}</td>
            </tr>

            @forelse ($akt['items'] as $itemKey => $item)
                <tr>
                    <td colspan="4">{{ $item['label'] }} ({{ $item['count'] ?? 0 }} transaksi)</td>
                    <td style="text-align: right;">{{ number_format($item['cash_in'], 2) }}</td>
                    <td style="text-align: right;">{{ number_format($item['cash_out'], 2) }}</td>
                    <td colspan="2" style="text-align: right; {{ $item['net_cash_flow'] >= 0 ? 'color: green;' : 'color: red;' }}">
                        {{ number_format($item['net_cash_flow'], 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="font-style: italic; color: #777;">(Tidak ada transaksi untuk aktivitas ini)</td>
                </tr>
            @endforelse

            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="4" style="text-align: right;">Arus Kas Bersih dari {{ $akt['label'] }}</td>
                <td style="text-align: right;">{{ number_format($akt['cash_in'], 2) }}</td>
                <td style="text-align: right;">{{ number_format($akt['cash_out'], 2) }}</td>
                <td colspan="2" style="text-align: right; {{ $akt['net_cash_flow'] >= 0 ? 'color: green;' : 'color: red;' }}">
                    {{ number_format($akt['net_cash_flow'], 2) }}
                </td>
            </tr>
            <tr><td colspan="8"></td></tr>
        @endforeach

        <tr style="background-color: #bbf7d0; font-weight: bold; font-size: 13px;">
            <td colspan="6" style="text-align: right;">KENAIKAN / (PENURUNAN) BERSIH KAS &amp; BANK</td>
            <td colspan="2" style="text-align: right; {{ $netCashFlow >= 0 ? 'color: green;' : 'color: red;' }}">
                {{ number_format($netCashFlow, 2) }}
            </td>
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

    {{-- ======================================================== --}}
    {{-- REKONSILIASI SALDO KAS & BANK AKHIR PERIODE (PSAK STANDAR) --}}
    {{-- ======================================================== --}}
    <tr><td colspan="8"></td></tr>
    <tr style="background-color: #f8fafc; font-weight: bold;">
        <th colspan="8" style="text-align: left; font-size: 13px;">REKONSILIASI SALDO KAS &amp; BANK</th>
    </tr>
    <tr>
        <td colspan="6">Saldo Kas &amp; Bank pada Awal Periode (per {{ \Carbon\Carbon::parse($tanggalAwal)->format('d M Y') }})</td>
        <td colspan="2" style="text-align: right; font-weight: bold;">{{ number_format($saldoAwal, 2) }}</td>
    </tr>
    <tr>
        <td colspan="6">Kenaikan / (Penurunan) Bersih Kas &amp; Bank Periode Berjalan</td>
        <td colspan="2" style="text-align: right; font-weight: bold; {{ $netCashFlow >= 0 ? 'color: green;' : 'color: red;' }}">
            {{ number_format($netCashFlow, 2) }}
        </td>
    </tr>
    <tr style="background-color: #dbeafe; font-weight: bold;">
        <td colspan="6">Saldo Kas &amp; Bank pada Akhir Periode (per {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d M Y') }})</td>
        <td colspan="2" style="text-align: right; font-weight: bold; color: #1e40af;">{{ number_format($saldoAkhir, 2) }}</td>
    </tr>
</table>

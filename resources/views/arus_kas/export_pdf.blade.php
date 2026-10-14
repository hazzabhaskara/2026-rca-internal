<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Cash Flow</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 8.5px; color: #1e293b; }
        h1 { font-size: 14px; text-align: center; margin-bottom: 3px; font-weight: bold; }
        .periode { text-align: center; margin-bottom: 8px; font-size: 9.5px; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #cbd5e1; padding: 3px 5px; }
        th { background-color: #f1f5f9; font-weight: bold; text-align: left; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .bg-header { background-color: #dbeafe; font-weight: bold; }
        .bg-subtotal { background-color: #f8fafc; font-weight: bold; }
        .bg-total { background-color: #e2e8f0; font-weight: bold; }
        .bg-direct-act { background-color: #eff6ff; font-weight: bold; color: #1e40af; }
        .bg-grand { background-color: #dcfce7; font-weight: bold; color: #166534; }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .font-mono { font-family: monospace; }
        .reconcile-box { margin-top: 15px; width: 55%; margin-left: auto; border: 1px solid #94a3b8; }
        .reconcile-box th, .reconcile-box td { border: 1px solid #cbd5e1; padding: 4px 8px; }
    </style>
</head>
<body>
    <h1>LAPORAN ARUS KAS (CASH FLOW)</h1>
    <div class="periode">
        Periode: {{ \Carbon\Carbon::parse($tanggalAwal)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d M Y') }} | Mode: {{ strtoupper($displayMode) }}
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
                            <td class="text-right font-mono">{{ number_format($r['cash_in'], 2) }}</td>
                            <td class="text-right font-mono">{{ number_format($r['cash_out'], 2) }}</td>
                        </tr>
                        @php
                            $subtotalIn += $r['cash_in'];
                            $subtotalOut += $r['cash_out'];
                        @endphp
                    @endforeach

                    <tr class="bg-subtotal">
                        <td colspan="5" class="text-right">Subtotal ({{ $source }})</td>
                        <td class="text-right font-mono">{{ number_format($subtotalIn, 2) }}</td>
                        <td class="text-right font-mono">{{ number_format($subtotalOut, 2) }}</td>
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
                    <td class="text-right font-mono">{{ number_format($grandCashIn, 2) }}</td>
                    <td class="text-right font-mono">{{ number_format($grandCashOut, 2) }}</td>
                </tr>
                <tr class="bg-subtotal">
                    <td colspan="5" class="text-right font-bold">KENAIKAN / (PENURUNAN) BERSIH KAS</td>
                    <td colspan="2" class="text-right font-mono {{ ($grandCashIn - $grandCashOut) >= 0 ? 'text-green' : 'text-red' }}">
                        {{ number_format($grandCashIn - $grandCashOut, 2) }}
                    </td>
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
                        <td class="text-right font-mono">{{ number_format($subtotalIn, 2) }}</td>
                        <td class="text-right font-mono">{{ number_format($subtotalOut, 2) }}</td>
                        <td class="text-right font-mono {{ $netCashFlow >= 0 ? 'text-green' : 'text-red' }}">{{ number_format($netCashFlow, 2) }}</td>
                    </tr>

                    @foreach ($items as $r)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($r['tanggal'])->format('d/m/Y') }}</td>
                            <td>{{ $r['source'] }}</td>
                            <td>{{ $r['lawan_akun'] }}</td>
                            <td>{{ $r['line_comment'] }}</td>
                            <td class="text-right font-mono">{{ number_format($r['cash_in'], 2) }}</td>
                            <td class="text-right font-mono">{{ number_format($r['cash_out'], 2) }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-total">
                    <td colspan="4" class="text-right">TOTAL KESELURUHAN</td>
                    <td class="text-right font-mono">{{ number_format($grandCashIn, 2) }}</td>
                    <td class="text-right font-mono">{{ number_format($grandCashOut, 2) }}</td>
                    <td class="text-right font-mono {{ ($grandCashIn - $grandCashOut) >= 0 ? 'text-green' : 'text-red' }}">
                        {{ number_format($grandCashIn - $grandCashOut, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

    {{-- ========================================= --}}
    {{-- MODE 4: DIRECT METHOD (METODE LANGSUNG) --}}
    {{-- ========================================= --}}
    @elseif($displayMode == 'direct')
        <table>
            <thead>
                <tr>
                    <th colspan="3">Aktivitas / Klasifikasi Akun</th>
                    <th class="text-right" style="width: 20%;">Penerimaan (In)</th>
                    <th class="text-right" style="width: 20%;">Pengeluaran (Out)</th>
                    <th class="text-right" style="width: 20%;">Arus Kas Bersih</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($aktivitasData as $aktKey => $akt)
                    <tr class="bg-direct-act">
                        <td colspan="6">{{ $akt['label'] }}</td>
                    </tr>

                    @forelse ($akt['items'] as $itemKey => $item)
                        <tr>
                            <td colspan="3" style="padding-left: 15px;">{{ $item['label'] }} ({{ $item['count'] ?? 0 }} transaksi)</td>
                            <td class="text-right font-mono">{{ number_format($item['cash_in'], 2) }}</td>
                            <td class="text-right font-mono">{{ number_format($item['cash_out'], 2) }}</td>
                            <td class="text-right font-mono {{ $item['net_cash_flow'] >= 0 ? 'text-green' : 'text-red' }}">
                                {{ number_format($item['net_cash_flow'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding-left: 15px; font-style: italic; color: #64748b;">(Tidak ada transaksi untuk aktivitas ini)</td>
                        </tr>
                    @endforelse

                    <tr class="bg-subtotal">
                        <td colspan="3" class="text-right font-bold">Arus Kas Bersih dari {{ $akt['label'] }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($akt['cash_in'], 2) }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($akt['cash_out'], 2) }}</td>
                        <td class="text-right font-mono font-bold {{ $akt['net_cash_flow'] >= 0 ? 'text-green' : 'text-red' }}">
                            {{ number_format($akt['net_cash_flow'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-grand">
                    <td colspan="5" class="text-right font-bold" style="font-size: 9.5px;">KENAIKAN / (PENURUNAN) BERSIH KAS &amp; BANK</td>
                    <td class="text-right font-mono font-bold {{ $netCashFlow >= 0 ? 'text-green' : 'text-red' }}" style="font-size: 9.5px;">
                        {{ number_format($netCashFlow, 2) }}
                    </td>
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
                        <td class="text-right font-mono">{{ number_format($r['cash_in'], 2) }}</td>
                        <td class="text-right font-mono">{{ number_format($r['cash_out'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-total">
                    <td colspan="6" class="text-right">TOTAL KESELURUHAN</td>
                    <td class="text-right font-mono">{{ number_format($grandCashIn, 2) }}</td>
                    <td class="text-right font-mono">{{ number_format($grandCashOut, 2) }}</td>
                </tr>
                <tr class="bg-subtotal">
                    <td colspan="6" class="text-right font-bold">NET CASH FLOW</td>
                    <td colspan="2" class="text-right font-mono {{ ($grandCashIn - $grandCashOut) >= 0 ? 'text-green' : 'text-red' }}">
                        {{ number_format($grandCashIn - $grandCashOut, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ======================================================== --}}
    {{-- REKONSILIASI SALDO KAS & BANK AKHIR PERIODE (PSAK STANDAR) --}}
    {{-- ======================================================== --}}
    <table class="reconcile-box">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th colspan="2" class="text-left font-bold" style="font-size: 9px;">REKONSILIASI SALDO KAS &amp; BANK</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Saldo Kas &amp; Bank pada Awal Periode</td>
                <td class="text-right font-mono font-bold">{{ number_format($saldoAwal, 2) }}</td>
            </tr>
            <tr>
                <td>Kenaikan / (Penurunan) Bersih Kas &amp; Bank</td>
                <td class="text-right font-mono font-bold {{ $netCashFlow >= 0 ? 'text-green' : 'text-red' }}">
                    {{ number_format($netCashFlow, 2) }}
                </td>
            </tr>
            <tr style="background-color: #eff6ff;">
                <td class="font-bold" style="color: #1e40af;">Saldo Kas &amp; Bank pada Akhir Periode</td>
                <td class="text-right font-mono font-bold" style="color: #1e40af;">{{ number_format($saldoAkhir, 2) }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>

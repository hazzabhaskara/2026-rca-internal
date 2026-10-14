<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Neraca Departemen - {{ $tanggalAkhir }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 7mm 5mm 7mm 5mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 6.8pt;
            color: #0f172a;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }
        .header-title {
            text-align: center;
            margin-bottom: 8px;
        }
        .header-title h2 {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header-title h3 {
            margin: 1px 0;
            font-size: 9.5pt;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
        }
        .header-title p {
            margin: 1px 0 0 0;
            font-size: 7.5pt;
            color: #64748b;
        }

        table.matrix-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Mandated for fixed column percentage calculation */
        }
        table.matrix-table th, table.matrix-table td {
            border: 1px solid #cbd5e1;
            padding: 2.5px 2px;
            overflow: hidden;
            word-wrap: break-word;
        }
        table.matrix-table thead {
            display: table-header-group;
        }
        table.matrix-table tr {
            page-break-inside: avoid;
        }

        .bg-head-main { background-color: #1e293b; color: #ffffff; font-weight: bold; }
        .bg-aset { background-color: #2563eb; color: #ffffff; font-weight: bold; }
        .bg-kewajiban { background-color: #d97706; color: #ffffff; font-weight: bold; }
        .bg-ekuitas { background-color: #7c3aed; color: #ffffff; font-weight: bold; }
        .bg-total-pasiva { background-color: #0f172a; color: #ffffff; font-weight: bold; }
        .bg-balance { background-color: #dcfce7; color: #166534; font-weight: bold; }

        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }

        .num-neg { color: #dc2626; }
        .footer-note {
            position: fixed;
            bottom: -4mm;
            left: 0;
            right: 0;
            text-align: right;
            font-size: 6pt;
            color: #94a3b8;
        }
        .pagenum:before { content: counter(page); }
    </style>
</head>
<body>
    <div class="footer-note">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }} &bull; Halaman <span class="pagenum"></span>
    </div>

    <div class="header-title">
        <h2>{{ $siteTitle ?? 'PT REMBULAN CITRA ABADI' }}</h2>
        <h3>LAPORAN NERACA DEPARTEMEN (MATRIKS KOMPARATIF)</h3>
        <p>Per {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y') }}</p>
    </div>

    @php
        $deptCount = count($departments);
        $keteranganWidth = 22.0;
        $totalWidth = 8.5;
        $eliminasiWidth = 7.5;
        $unassignedWidth = 7.2;
        $remainingWidth = 100.0 - ($keteranganWidth + $totalWidth + $eliminasiWidth + $unassignedWidth);
        $deptWidth = $deptCount > 0 ? round($remainingWidth / $deptCount, 2) : 0;
    @endphp

    <table class="matrix-table">
        <thead>
            <tr class="bg-head-main">
                <th style="width: {{ $keteranganWidth }}%; text-align: left; padding-left: 4px;">KETERANGAN / AKUN</th>
                <th style="width: {{ $totalWidth }}%; text-align: right; background-color: #312e81; color: #e0e7ff;">
                    TOTAL<br><span style="font-size: 5.5pt; font-weight: normal;">Konsolidasi</span>
                </th>
                @foreach ($departments as $dept)
                    <th style="width: {{ $deptWidth }}%; text-align: right;">
                        {{ $dept->kode }}<br>
                        <span style="font-size: 5.5pt; font-weight: normal;">{{ substr($dept->deskripsi, 0, 10) }}</span>
                    </th>
                @endforeach
                <th style="width: {{ $eliminasiWidth }}%; text-align: right; background-color: #451a03; color: #fde68a;">
                    ELIMINASI<br><span style="font-size: 5.5pt; font-weight: normal;">R/K Unit</span>
                </th>
                <th style="width: {{ $unassignedWidth }}%; text-align: right; background-color: #334155;">
                    NON-DEPT<br><span style="font-size: 5.5pt; font-weight: normal;">Unassigned</span>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach (['Aset', 'Kewajiban', 'Ekuitas'] as $tipe)
                @php
                    $tipeClass = 'bg-' . strtolower($tipe);
                    $tipeLabels = [
                        'Aset' => 'AKTIVA / ASET',
                        'Kewajiban' => 'KEWAJIBAN / LIABILITAS',
                        'Ekuitas' => 'EKUITAS &amp; DANA ANTAR UNIT'
                    ];
                    $accountsList = $neraca[$tipe] ?? [];
                @endphp

                @foreach ($accountsList as $item)
                    @php
                        $lvl = $item['level_akun'];
                        $isZero = $hideAccountWithZero && collect($item['balances'])->every(fn($v) => abs($v) < 0.001);
                    @endphp

                    @if ($lvl === 'HEADER')
                        <tr class="{{ $tipeClass }}">
                            <td style="text-align: left; padding-left: 4px;">
                                {{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}
                            </td>
                            @php $hTot = $item['balances']['total'] ?? ($grandTotals[$tipe]['total'] ?? 0); @endphp
                            <td class="text-right">
                                {{ $hTot < 0 ? '(' . number_format(abs($hTot), 2, ',', '.') . ')' : number_format($hTot, 2, ',', '.') }}
                            </td>
                            @foreach ($departments as $dept)
                                @php $hDept = $item['balances'][$dept->id] ?? ($grandTotals[$tipe][$dept->id] ?? 0); @endphp
                                <td class="text-right">
                                    {{ $hDept < 0 ? '(' . number_format(abs($hDept), 2, ',', '.') . ')' : number_format($hDept, 2, ',', '.') }}
                                </td>
                            @endforeach
                            @php $hElim = $item['balances']['eliminasi'] ?? ($grandTotals[$tipe]['eliminasi'] ?? 0); @endphp
                            <td class="text-right" style="color: #fef08a;">
                                {{ $hElim < 0 ? '(' . number_format(abs($hElim), 2, ',', '.') . ')' : number_format($hElim, 2, ',', '.') }}
                            </td>
                            @php $hUn = $item['balances']['unassigned'] ?? ($grandTotals[$tipe]['unassigned'] ?? 0); @endphp
                            <td class="text-right">
                                {{ $hUn < 0 ? '(' . number_format(abs($hUn), 2, ',', '.') . ')' : number_format($hUn, 2, ',', '.') }}
                            </td>
                        </tr>
                    @elseif ($lvl === 'GROUP ACCOUNT')
                        <tr style="background-color: #f8fafc; font-weight: bold;">
                            <td style="padding-left: 8px; color: #475569;">
                                {{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}
                            </td>
                            @php $gTot = $item['balances']['total'] ?? 0; @endphp
                            <td class="text-right font-bold {{ $gTot < 0 ? 'num-neg' : '' }}">
                                {{ $gTot < 0 ? '(' . number_format(abs($gTot), 2, ',', '.') . ')' : number_format($gTot, 2, ',', '.') }}
                            </td>
                            @foreach ($departments as $dept)
                                @php $gDept = $item['balances'][$dept->id] ?? 0; @endphp
                                <td class="text-right font-bold {{ $gDept < 0 ? 'num-neg' : '' }}">
                                    {{ $gDept < 0 ? '(' . number_format(abs($gDept), 2, ',', '.') . ')' : number_format($gDept, 2, ',', '.') }}
                                </td>
                            @endforeach
                            @php $gElim = $item['balances']['eliminasi'] ?? 0; @endphp
                            <td class="text-right font-bold {{ $gElim < 0 ? 'num-neg' : '' }}">
                                {{ $gElim < 0 ? '(' . number_format(abs($gElim), 2, ',', '.') . ')' : number_format($gElim, 2, ',', '.') }}
                            </td>
                            @php $gUn = $item['balances']['unassigned'] ?? 0; @endphp
                            <td class="text-right font-bold {{ $gUn < 0 ? 'num-neg' : '' }}">
                                {{ $gUn < 0 ? '(' . number_format(abs($gUn), 2, ',', '.') . ')' : number_format($gUn, 2, ',', '.') }}
                            </td>
                        </tr>
                    @elseif ($lvl === 'ACCOUNT')
                        @if ($isZero) @continue @endif
                        <tr style="background-color: #ffffff;">
                            <td style="padding-left: 12px; font-weight: bold; color: #1e293b;">
                                {{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}
                            </td>
                            @php $vTot = $item['balances']['total'] ?? 0; @endphp
                            <td class="text-right font-bold {{ $vTot < 0 ? 'num-neg' : '' }}" style="background-color: #f8fafc;">
                                {{ abs($vTot) > 0.001 ? ($vTot < 0 ? '(' . number_format(abs($vTot), 2, ',', '.') . ')' : number_format($vTot, 2, ',', '.')) : '0,00' }}
                            </td>
                            @foreach ($departments as $dept)
                                @php $v = $item['balances'][$dept->id] ?? 0; @endphp
                                <td class="text-right {{ $v < 0 ? 'num-neg' : '' }}">
                                    {{ abs($v) > 0.001 ? ($v < 0 ? '(' . number_format(abs($v), 2, ',', '.') . ')' : number_format($v, 2, ',', '.')) : '0,00' }}
                                </td>
                            @endforeach
                            <td class="text-right" style="color: #94a3b8;">0,00</td>
                            @php $vUn = $item['balances']['unassigned'] ?? 0; @endphp
                            <td class="text-right {{ $vUn < 0 ? 'num-neg' : '' }}">
                                {{ abs($vUn) > 0.001 ? ($vUn < 0 ? '(' . number_format(abs($vUn), 2, ',', '.') . ')' : number_format($vUn, 2, ',', '.')) : '0,00' }}
                            </td>
                        </tr>
                    @elseif ($lvl === 'SUB ACCOUNT')
                        @if ($isZero) @continue @endif
                        <tr style="background-color: #fafafa;">
                            <td style="padding-left: 18px; color: #475569; font-size: 6.3pt;">
                                {{ $showAccountNumber ? $item['kode_akun'] . ' — ' . $item['nama_akun'] : $item['nama_akun'] }}
                            </td>
                            @php $vTot = $item['balances']['total'] ?? 0; @endphp
                            <td class="text-right {{ $vTot < 0 ? 'num-neg' : '' }}" style="font-size: 6.3pt;">
                                {{ abs($vTot) > 0.001 ? ($vTot < 0 ? '(' . number_format(abs($vTot), 2, ',', '.') . ')' : number_format($vTot, 2, ',', '.')) : '0,00' }}
                            </td>
                            @foreach ($departments as $dept)
                                @php $v = $item['balances'][$dept->id] ?? 0; @endphp
                                <td class="text-right {{ $v < 0 ? 'num-neg' : '' }}" style="font-size: 6.3pt;">
                                    {{ abs($v) > 0.001 ? ($v < 0 ? '(' . number_format(abs($v), 2, ',', '.') . ')' : number_format($v, 2, ',', '.')) : '0,00' }}
                                </td>
                            @endforeach
                            <td class="text-right" style="color: #cbd5e1; font-size: 6.3pt;">0,00</td>
                            @php $vUn = $item['balances']['unassigned'] ?? 0; @endphp
                            <td class="text-right {{ $vUn < 0 ? 'num-neg' : '' }}" style="font-size: 6.3pt;">
                                {{ abs($vUn) > 0.001 ? ($vUn < 0 ? '(' . number_format(abs($vUn), 2, ',', '.') . ')' : number_format($vUn, 2, ',', '.')) : '0,00' }}
                            </td>
                        </tr>
                    @elseif ($lvl === 'X')
                        <tr style="background-color: #faf5ff; font-weight: bold; color: #581c87;">
                            <td style="padding-left: 12px;">{{ $item['nama_akun'] }}</td>
                            @php $vTot = $item['balances']['total'] ?? 0; @endphp
                            <td class="text-right {{ $vTot < 0 ? 'num-neg' : '' }}">
                                {{ $vTot < 0 ? '(' . number_format(abs($vTot), 2, ',', '.') . ')' : number_format($vTot, 2, ',', '.') }}
                            </td>
                            @foreach ($departments as $dept)
                                @php $v = $item['balances'][$dept->id] ?? 0; @endphp
                                <td class="text-right {{ $v < 0 ? 'num-neg' : '' }}">
                                    {{ $v < 0 ? '(' . number_format(abs($v), 2, ',', '.') . ')' : number_format($v, 2, ',', '.') }}
                                </td>
                            @endforeach
                            <td class="text-right">0,00</td>
                            @php $vUn = $item['balances']['unassigned'] ?? 0; @endphp
                            <td class="text-right {{ $vUn < 0 ? 'num-neg' : '' }}">
                                {{ $vUn < 0 ? '(' . number_format(abs($vUn), 2, ',', '.') . ')' : number_format($vUn, 2, ',', '.') }}
                            </td>
                        </tr>
                    @elseif ($lvl === 'RK')
                        <tr style="background-color: #eef2ff; font-weight: bold; color: #312e81;">
                            <td style="padding-left: 12px;">{{ $item['nama_akun'] }}</td>
                            <td class="text-right">0,00</td>
                            @foreach ($departments as $dept)
                                @php $v = $item['balances'][$dept->id] ?? 0; @endphp
                                <td class="text-right {{ $v < 0 ? 'num-neg' : '' }}">
                                    {{ $v < 0 ? '(' . number_format(abs($v), 2, ',', '.') . ')' : number_format($v, 2, ',', '.') }}
                                </td>
                            @endforeach
                            @php $vElim = $item['balances']['eliminasi'] ?? 0; @endphp
                            <td class="text-right {{ $vElim < 0 ? 'num-neg' : '' }}" style="background-color: #fef3c7; color: #78350f;">
                                {{ $vElim < 0 ? '(' . number_format(abs($vElim), 2, ',', '.') . ')' : number_format($vElim, 2, ',', '.') }}
                            </td>
                            @php $vUn = $item['balances']['unassigned'] ?? 0; @endphp
                            <td class="text-right {{ $vUn < 0 ? 'num-neg' : '' }}">
                                {{ $vUn < 0 ? '(' . number_format(abs($vUn), 2, ',', '.') . ')' : number_format($vUn, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                @endforeach

                {{-- Subtotal Tipe --}}
                <tr style="background-color: #e2e8f0; font-weight: bold;">
                    <td style="padding-left: 4px;">TOTAL {{ $tipeLabels[$tipe] }}</td>
                    <td class="text-right">
                        {{ number_format($grandTotals[$tipe]['total'] ?? 0, 2, ',', '.') }}
                    </td>
                    @foreach ($departments as $dept)
                        <td class="text-right">
                            {{ number_format($grandTotals[$tipe][$dept->id] ?? 0, 2, ',', '.') }}
                        </td>
                    @endforeach
                    <td class="text-right" style="color: #b45309;">
                        {{ number_format($grandTotals[$tipe]['eliminasi'] ?? 0, 2, ',', '.') }}
                    </td>
                    <td class="text-right">
                        {{ number_format($grandTotals[$tipe]['unassigned'] ?? 0, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach

            {{-- Total Pasiva --}}
            <tr class="bg-total-pasiva">
                <td style="padding-left: 4px;">TOTAL PASIVA (KEWAJIBAN &amp; EKUITAS)</td>
                <td class="text-right" style="color: #6ee7b7;">
                    {{ number_format($grandTotals['Pasiva']['total'] ?? 0, 2, ',', '.') }}
                </td>
                @foreach ($departments as $dept)
                    <td class="text-right" style="color: #6ee7b7;">
                        {{ number_format($grandTotals['Pasiva'][$dept->id] ?? 0, 2, ',', '.') }}
                    </td>
                @endforeach
                <td class="text-right" style="color: #fde68a;">
                    {{ number_format($grandTotals['Pasiva']['eliminasi'] ?? 0, 2, ',', '.') }}
                </td>
                <td class="text-right" style="color: #6ee7b7;">
                    {{ number_format($grandTotals['Pasiva']['unassigned'] ?? 0, 2, ',', '.') }}
                </td>
            </tr>

            {{-- Status Balance --}}
            <tr class="bg-balance">
                <td style="padding-left: 4px;">STATUS BALANCE (AKTIVA - PASIVA)</td>
                @php $diffTot = ($grandTotals['Aset']['total'] ?? 0) - ($grandTotals['Pasiva']['total'] ?? 0); @endphp
                <td class="text-right">{{ abs($diffTot) < 0.01 ? '0,00' : number_format($diffTot, 2, ',', '.') }}</td>
                @foreach ($departments as $dept)
                    @php $diff = ($grandTotals['Aset'][$dept->id] ?? 0) - ($grandTotals['Pasiva'][$dept->id] ?? 0); @endphp
                    <td class="text-right">{{ abs($diff) < 0.01 ? '0,00' : number_format($diff, 2, ',', '.') }}</td>
                @endforeach
                <td class="text-right">0,00</td>
                @php $diffUn = ($grandTotals['Aset']['unassigned'] ?? 0) - ($grandTotals['Pasiva']['unassigned'] ?? 0); @endphp
                <td class="text-right">{{ abs($diffUn) < 0.01 ? '0,00' : number_format($diffUn, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>

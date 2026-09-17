<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Neraca - {{ $tanggalAkhir }}</title>
    <style>
        @page {
            margin: 15mm 12mm 15mm 12mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #000;
            line-height: 1.3;
        }

        h2 {
            margin: 0 0 5px 0;
            text-align: center;
            font-size: 14px;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 12px;
            font-size: 10px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 4px 6px;
            vertical-align: top;
        }

        th {
            text-align: left;
            background: #334155;
            color: white;
            font-weight: bold;
            font-size: 8px;
        }

        th.right {
            text-align: right;
        }

        /* Section Headers */
        .section-aset { background: #2563eb; color: white; font-weight: bold; }
        .section-kewajiban { background: #d97706; color: white; font-weight: bold; }
        .section-ekuitas { background: #7c3aed; color: white; font-weight: bold; }

        /* Group Account */
        .row-group-aset { background: #dbeafe; font-weight: bold; }
        .row-group-kewajiban { background: #fef3c7; font-weight: bold; }
        .row-group-ekuitas { background: #ede9fe; font-weight: bold; }

        /* Account */
        .row-account { background: #fff; }
        .row-account td:first-child { padding-left: 15px; }

        /* Sub Account */
        .row-subaccount { background: #f9fafb; color: #4b5563; font-size: 8px; }
        .row-subaccount td:first-child { padding-left: 30px; }

        /* Totals */
        .total-aset { background: #2563eb; color: white; font-weight: bold; }
        .total-kewajiban { background: #d97706; color: white; font-weight: bold; }
        .total-ekuitas { background: #7c3aed; color: white; font-weight: bold; }

        .row-final { background: #1e40af; color: white; font-weight: bold; font-size: 10px; }

        .text-right { text-align: right; }
        .spacer { height: 8px; background: transparent; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        .pagenum:before { content: counter(page); }
    </style>
</head>

<body>
    <h2>LAPORAN NERACA</h2>
    <div class="subtitle">
        Per {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50%;">KETERANGAN</th>
                <th class="right" style="width: 16%;">SUB ACCOUNT</th>
                <th class="right" style="width: 17%;">ACCOUNT</th>
                <th class="right" style="width: 17%;">GROUP ACCOUNT</th>
            </tr>
        </thead>
        <tbody>
            @php
                $tipeConfig = [
                    'Aset' => ['section' => 'section-aset', 'group' => 'row-group-aset', 'total' => 'total-aset'],
                    'Kewajiban' => ['section' => 'section-kewajiban', 'group' => 'row-group-kewajiban', 'total' => 'total-kewajiban'],
                    'Ekuitas' => ['section' => 'section-ekuitas', 'group' => 'row-group-ekuitas', 'total' => 'total-ekuitas'],
                ];
                $norm = fn($v) => strtoupper(trim((string) $v));
            @endphp

            @foreach (['Aset', 'Kewajiban', 'Ekuitas'] as $tipe)
                @if (!empty($neraca[$tipe]))
                    @php
                        $config = $tipeConfig[$tipe];
                        $currentGroupName = null;
                        $currentGroupTotal = 0;
                    @endphp

                    {{-- HEADER TIPE --}}
                    <tr class="{{ $config['section'] }}">
                        <td colspan="4">{{ strtoupper($tipe) }}</td>
                    </tr>

                    @foreach ($neraca[$tipe] as $akun)
                        @if ($akun['level_akun'] === 'HEADER')
                            @if ($currentGroupName && $currentGroupTotal != 0)
                                <tr class="{{ $config['group'] }}">
                                    <td>Subtotal {{ $currentGroupName }}</td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-right">{{ number_format($currentGroupTotal, 2, ',', '.') }}</td>
                                </tr>
                                @php $currentGroupName = null; $currentGroupTotal = 0; @endphp
                            @endif
                            <tr style="background: #f3f4f6;">
                                <td colspan="4" style="font-weight: bold;">{{ $akun['nama_akun'] }}</td>
                            </tr>

                        @elseif ($akun['level_akun'] === 'GROUP ACCOUNT')
                            @if ($currentGroupName && $currentGroupTotal != 0)
                                <tr class="{{ $config['group'] }}">
                                    <td>Subtotal {{ $currentGroupName }}</td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-right">{{ number_format($currentGroupTotal, 2, ',', '.') }}</td>
                                </tr>
                            @endif
                            @php
                                $currentGroupName = $akun['nama_akun'];
                                $currentGroupTotal = 0;
                            @endphp
                            <tr class="{{ $config['group'] }}">
                                <td>{{ $akun['nama_akun'] }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>

                        @elseif ($akun['level_akun'] === 'ACCOUNT')
                            @php
                                $parentSaldo = $akun['saldo'] ?? 0;
                                $parentCode = (string) $akun['kode_akun'];
                                $parentPrefix = rtrim($parentCode, '0');
                                $childAccounts = collect($neraca[$tipe])->filter(
                                    fn($sub) => $norm($sub['level_akun']) === 'SUB ACCOUNT' &&
                                        \Illuminate\Support\Str::startsWith((string) $sub['kode_akun'], $parentPrefix)
                                );
                                $hasChild = $childAccounts->isNotEmpty();
                                $currentGroupTotal += $parentSaldo;
                            @endphp

                            <tr class="row-account">
                                <td>{{ $akun['kode_akun'] }} - {{ $akun['nama_akun'] }}</td>
                                <td></td>
                                <td class="text-right">{{ number_format($parentSaldo, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>

                            @if ($hasChild)
                                @foreach ($childAccounts as $sub)
                                    <tr class="row-subaccount">
                                        <td>{{ $sub['kode_akun'] }} - {{ $sub['nama_akun'] }}</td>
                                        <td class="text-right">{{ number_format($sub['saldo'] ?? 0, 2, ',', '.') }}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                @endforeach
                            @endif

                        @elseif ($akun['level_akun'] === 'SUB ACCOUNT')
                            @continue

                        @else
                            {{-- Pos lain (X = Laba Tahun Berjalan) --}}
                            @php $currentGroupTotal += $akun['saldo'] ?? 0; @endphp
                            <tr class="row-account">
                                <td style="font-style: italic;">{{ $akun['nama_akun'] }}</td>
                                <td></td>
                                <td class="text-right">{{ number_format($akun['saldo'] ?? 0, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        @endif
                    @endforeach

                    {{-- Subtotal terakhir --}}
                    @if ($currentGroupName && $currentGroupTotal != 0)
                        <tr class="{{ $config['group'] }}">
                            <td>Subtotal {{ $currentGroupName }}</td>
                            <td></td>
                            <td></td>
                            <td class="text-right">{{ number_format($currentGroupTotal, 2, ',', '.') }}</td>
                        </tr>
                    @endif

                    {{-- TOTAL per tipe --}}
                    <tr class="{{ $config['total'] }}">
                        <td>TOTAL {{ strtoupper($tipe) }}</td>
                        <td></td>
                        <td></td>
                        <td class="text-right">
                            @if ($tipe === 'Aset')
                                {{ number_format($grandTotalAset, 2, ',', '.') }}
                            @elseif ($tipe === 'Kewajiban')
                                {{ number_format($grandTotalKewajiban, 2, ',', '.') }}
                            @else
                                {{ number_format($grandTotalEkuitas, 2, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                    <tr class="spacer"><td colspan="4"></td></tr>
                @endif
            @endforeach

            {{-- TOTAL KEWAJIBAN DAN EKUITAS --}}
            <tr class="row-final">
                <td>TOTAL KEWAJIBAN DAN EKUITAS</td>
                <td></td>
                <td></td>
                <td class="text-right">{{ number_format($grandTotalKewajiban + $grandTotalEkuitas, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dicetak: {{ now()->format('d/m/Y H:i') }} — Halaman <span class="pagenum"></span>
    </div>
</body>

</html>

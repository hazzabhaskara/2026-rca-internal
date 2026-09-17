<table>
    <thead>
        <tr>
            <th colspan="4" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN NERACA
            </th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center; font-size: 12px;">
                Per {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d M Y') }}
            </th>
        </tr>
        <tr><td colspan="4"></td></tr>
        <tr style="background-color: #334155; color: white; font-weight: bold;">
            <th style="text-align: left; padding: 8px;">KETERANGAN</th>
            <th style="text-align: right; padding: 8px;">SUB ACCOUNT</th>
            <th style="text-align: right; padding: 8px;">ACCOUNT</th>
            <th style="text-align: right; padding: 8px;">GROUP ACCOUNT</th>
        </tr>
    </thead>
    <tbody>
        @php
            $tipeConfig = [
                'Aset' => ['headerBg' => '#2563eb', 'groupBg' => '#dbeafe'],
                'Kewajiban' => ['headerBg' => '#d97706', 'groupBg' => '#fef3c7'],
                'Ekuitas' => ['headerBg' => '#7c3aed', 'groupBg' => '#ede9fe'],
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
                <tr style="background-color: {{ $config['headerBg'] }}; color: white;">
                    <td colspan="4" style="font-weight: bold; padding: 6px;">{{ strtoupper($tipe) }}</td>
                </tr>

                @foreach ($neraca[$tipe] as $akun)
                    {{-- HEADER --}}
                    @if ($akun['level_akun'] === 'HEADER')
                        @if ($currentGroupName && $currentGroupTotal != 0)
                            <tr style="background-color: {{ $config['groupBg'] }};">
                                <td style="font-weight: bold; padding: 4px;">Subtotal {{ $currentGroupName }}</td>
                                <td></td>
                                <td></td>
                                <td style="text-align: right; font-weight: bold; padding: 4px;">{{ number_format($currentGroupTotal, 2, ',', '.') }}</td>
                            </tr>
                            @php $currentGroupName = null; $currentGroupTotal = 0; @endphp
                        @endif
                        <tr style="background-color: #f3f4f6;">
                            <td colspan="4" style="font-weight: bold; padding: 6px;">{{ $akun['nama_akun'] }}</td>
                        </tr>

                    {{-- GROUP ACCOUNT --}}
                    @elseif ($akun['level_akun'] === 'GROUP ACCOUNT')
                        @if ($currentGroupName && $currentGroupTotal != 0)
                            <tr style="background-color: {{ $config['groupBg'] }};">
                                <td style="font-weight: bold; padding: 4px;">Subtotal {{ $currentGroupName }}</td>
                                <td></td>
                                <td></td>
                                <td style="text-align: right; font-weight: bold; padding: 4px;">{{ number_format($currentGroupTotal, 2, ',', '.') }}</td>
                            </tr>
                        @endif
                        @php
                            $currentGroupName = $akun['nama_akun'];
                            $currentGroupTotal = 0;
                        @endphp
                        <tr style="background-color: {{ $config['groupBg'] }};">
                            <td style="font-weight: bold; padding: 6px;">{{ $akun['nama_akun'] }}</td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right; font-weight: bold; padding: 6px;"></td>
                        </tr>

                    {{-- ACCOUNT --}}
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

                        {{-- Account Row --}}
                        <tr>
                            <td style="padding-left: 20px; padding: 4px 4px 4px 20px;">{{ $akun['kode_akun'] }} - {{ $akun['nama_akun'] }}</td>
                            <td></td>
                            <td style="text-align: right; padding: 4px;">{{ number_format($parentSaldo, 2, ',', '.') }}</td>
                            <td></td>
                        </tr>

                        {{-- Sub Accounts --}}
                        @if ($hasChild)
                            @foreach ($childAccounts as $sub)
                                <tr style="background-color: #f9fafb;">
                                    <td style="padding-left: 40px; padding: 4px 4px 4px 40px; color: #6b7280;">{{ $sub['kode_akun'] }} - {{ $sub['nama_akun'] }}</td>
                                    <td style="text-align: right; padding: 4px; color: #6b7280;">{{ number_format($sub['saldo'] ?? 0, 2, ',', '.') }}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endforeach
                        @endif

                    {{-- SUB ACCOUNT - skip in main loop --}}
                    @elseif ($akun['level_akun'] === 'SUB ACCOUNT')
                        @continue

                    {{-- Pos lain (X = Laba Tahun Berjalan) --}}
                    @else
                        @php $currentGroupTotal += $akun['saldo'] ?? 0; @endphp
                        <tr>
                            <td style="padding-left: 20px; padding: 4px 4px 4px 20px; font-style: italic;">{{ $akun['nama_akun'] }}</td>
                            <td></td>
                            <td style="text-align: right; padding: 4px;">{{ number_format($akun['saldo'] ?? 0, 2, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    @endif
                @endforeach

                {{-- Subtotal group terakhir --}}
                @if ($currentGroupName && $currentGroupTotal != 0)
                    <tr style="background-color: {{ $config['groupBg'] }};">
                        <td style="font-weight: bold; padding: 4px;">Subtotal {{ $currentGroupName }}</td>
                        <td></td>
                        <td></td>
                        <td style="text-align: right; font-weight: bold; padding: 4px;">{{ number_format($currentGroupTotal, 2, ',', '.') }}</td>
                    </tr>
                @endif

                {{-- TOTAL per tipe --}}
                <tr style="background-color: {{ $config['headerBg'] }}; color: white;">
                    <td style="font-weight: bold; padding: 6px;">TOTAL {{ strtoupper($tipe) }}</td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right; font-weight: bold; padding: 6px;">
                        @if ($tipe === 'Aset')
                            {{ number_format($grandTotalAset, 2, ',', '.') }}
                        @elseif ($tipe === 'Kewajiban')
                            {{ number_format($grandTotalKewajiban, 2, ',', '.') }}
                        @else
                            {{ number_format($grandTotalEkuitas, 2, ',', '.') }}
                        @endif
                    </td>
                </tr>
                <tr><td colspan="4"></td></tr>
            @endif
        @endforeach

        {{-- TOTAL KEWAJIBAN DAN EKUITAS --}}
        <tr style="background-color: #1e40af; color: white;">
            <td style="font-weight: bold; padding: 8px; font-size: 12px;">TOTAL KEWAJIBAN DAN EKUITAS</td>
            <td></td>
            <td></td>
            <td style="text-align: right; font-weight: bold; padding: 8px; font-size: 12px;">{{ number_format($grandTotalKewajiban + $grandTotalEkuitas, 2, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

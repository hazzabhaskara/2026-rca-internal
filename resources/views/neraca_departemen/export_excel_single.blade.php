@php
    $singleId = $singleData['targetCol'] ?? (($selectedDeptId === 'konsolidasi') ? 'total' : $selectedDeptId);
@endphp
<table>
    <thead>
        <tr>
            <th colspan="4" style="font-size: 14pt; font-weight: bold; text-align: center;">
                {{ $siteTitle ?? 'PT REMBULAN CITRA ABADI' }}
            </th>
        </tr>
        <tr>
            <th colspan="4" style="font-size: 12pt; font-weight: bold; text-align: center;">
                LAPORAN NERACA - {{ strtoupper($singleDept->deskripsi ?? 'DEPARTEMEN') }}
            </th>
        </tr>
        <tr>
            <th colspan="4" style="font-size: 10pt; text-align: center;">
                Per {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y') }}
            </th>
        </tr>
        <tr><th colspan="4"></th></tr>
        <tr style="background-color: #1e293b; color: #ffffff; font-weight: bold;">
            <th style="border: 1px solid #000000; width: 45; text-align: left;">KETERANGAN</th>
            <th style="border: 1px solid #000000; width: 22; text-align: right;">SUB ACCOUNT</th>
            <th style="border: 1px solid #000000; width: 22; text-align: right;">ACCOUNT</th>
            <th style="border: 1px solid #000000; width: 22; text-align: right;">GROUP ACCOUNT</th>
        </tr>
    </thead>
    <tbody>
        @foreach (['Aset', 'Kewajiban', 'Ekuitas'] as $tipe)
            @php
                $tipeNames = [
                    'Aset' => 'AKTIVA / ASET',
                    'Kewajiban' => 'KEWAJIBAN / LIABILITAS',
                    'Ekuitas' => 'EKUITAS &amp; DANA ANTAR UNIT'
                ];
                $accountsList = $neraca[$tipe] ?? [];
            @endphp
            @foreach ($accountsList as $item)
                @php
                    $lvl = $item['level_akun'];
                    $vSelf = $item['balances_self'][$singleId] ?? 0;
                    $vAgg = $item['balances'][$singleId] ?? 0;
                    $isZero = $hideAccountWithZero && (abs($vAgg) < 0.001);
                @endphp

                @if ($lvl === 'HEADER')
                    @php
                        $hVal = $vAgg != 0 ? $vAgg : ($grandTotals[$tipe][$singleId] ?? 0);
                    @endphp
                    <tr style="background-color: #1e3a8a; color: #ffffff; font-weight: bold;">
                        <td colspan="3" style="border: 1px solid #000000; font-weight: bold;">
                            {{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}
                        </td>
                        <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                            {{ $hVal }}
                        </td>
                    </tr>
                @elseif ($lvl === 'GROUP ACCOUNT')
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="3" style="border: 1px solid #cccccc; font-weight: bold; padding-left: 10px;">
                            {{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}
                        </td>
                        <td style="border: 1px solid #cccccc; text-align: right; font-weight: bold;">
                            {{ $vAgg }}
                        </td>
                    </tr>
                @elseif ($lvl === 'ACCOUNT')
                    @if ($isZero) @continue @endif
                    <tr style="background-color: #ffffff;">
                        <td style="border: 1px solid #e2e8f0; font-weight: 600; padding-left: 20px;">
                            {{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}
                        </td>
                        <td style="border: 1px solid #e2e8f0;"></td>
                        <td style="border: 1px solid #e2e8f0; text-align: right; font-weight: 600;">
                            {{ $vAgg }}
                        </td>
                        <td style="border: 1px solid #e2e8f0;"></td>
                    </tr>
                @elseif ($lvl === 'SUB ACCOUNT')
                    @if ($isZero) @continue @endif
                    <tr style="background-color: #fafafa;">
                        <td style="border: 1px solid #f1f5f9; padding-left: 30px; font-style: italic;">
                            {{ $showAccountNumber ? $item['kode_akun'] . ' — ' . $item['nama_akun'] : $item['nama_akun'] }}
                        </td>
                        <td style="border: 1px solid #f1f5f9; text-align: right;">
                            {{ $vSelf }}
                        </td>
                        <td style="border: 1px solid #f1f5f9;"></td>
                        <td style="border: 1px solid #f1f5f9;"></td>
                    </tr>
                @elseif ($lvl === 'X')
                    <tr style="background-color: #faf5ff; font-weight: bold;">
                        <td style="border: 1px solid #d8b4fe; font-weight: bold; padding-left: 20px;">
                            {{ $item['nama_akun'] }}
                        </td>
                        <td style="border: 1px solid #d8b4fe;"></td>
                        <td style="border: 1px solid #d8b4fe; text-align: right; font-weight: bold;">
                            {{ $vAgg }}
                        </td>
                        <td style="border: 1px solid #d8b4fe;"></td>
                    </tr>
                @elseif ($lvl === 'RK')
                    <tr style="background-color: #eef2ff; font-weight: bold;">
                        <td style="border: 1px solid #c7d2fe; font-weight: bold; padding-left: 20px;">
                            {{ $item['nama_akun'] }} (Penyeimbang Cabang)
                        </td>
                        <td style="border: 1px solid #c7d2fe;"></td>
                        <td style="border: 1px solid #c7d2fe; text-align: right; font-weight: bold;">
                            {{ $vAgg }}
                        </td>
                        <td style="border: 1px solid #c7d2fe;"></td>
                    </tr>
                @endif
            @endforeach

            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td colspan="3" style="border: 1px solid #000000; font-weight: bold;">TOTAL {{ $tipeNames[$tipe] }}</td>
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    {{ $grandTotals[$tipe][$singleId] ?? 0 }}
                </td>
            </tr>
        @endforeach

        <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
            <td colspan="3" style="border: 1px solid #000000; font-weight: bold;">TOTAL PASIVA (KEWAJIBAN &amp; EKUITAS)</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                {{ $grandTotals['Pasiva'][$singleId] ?? 0 }}
            </td>
        </tr>

        <tr style="background-color: #dcfce7; font-weight: bold;">
            <td colspan="3" style="border: 1px solid #86efac; font-weight: bold;">STATUS BALANCE (AKTIVA - PASIVA)</td>
            @php $diffSingle = ($grandTotals['Aset'][$singleId] ?? 0) - ($grandTotals['Pasiva'][$singleId] ?? 0); @endphp
            <td style="border: 1px solid #86efac; text-align: right; font-weight: bold;">{{ abs($diffSingle) < 0.01 ? 0 : round($diffSingle, 2) }}</td>
        </tr>
    </tbody>
</table>

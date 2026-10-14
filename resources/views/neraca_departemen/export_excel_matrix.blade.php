<table>
    <thead>
        <tr>
            <th colspan="{{ count($departments) + 4 }}" style="font-size: 14pt; font-weight: bold; text-align: center;">
                {{ $siteTitle ?? 'PT REMBULAN CITRA ABADI' }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ count($departments) + 4 }}" style="font-size: 12pt; font-weight: bold; text-align: center;">
                LAPORAN NERACA DEPARTEMEN (MATRIKS KOMPARATIF)
            </th>
        </tr>
        <tr>
            <th colspan="{{ count($departments) + 4 }}" style="font-size: 10pt; text-align: center;">
                Per {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y') }}
            </th>
        </tr>
        <tr><th colspan="{{ count($departments) + 4 }}"></th></tr>
        <tr style="background-color: #1e293b; color: #ffffff; font-weight: bold;">
            <th style="border: 1px solid #000000; width: 35; text-align: left;">KETERANGAN / NAMA AKUN</th>
            <th style="border: 1px solid #000000; width: 22; text-align: right;">TOTAL KONSOLIDASI</th>
            @foreach ($departments as $dept)
                <th style="border: 1px solid #000000; width: 18; text-align: right;">
                    {{ $dept->kode }} - {{ $dept->deskripsi }}
                </th>
            @endforeach
            <th style="border: 1px solid #000000; width: 18; text-align: right;">ELIMINASI R/K</th>
            <th style="border: 1px solid #000000; width: 18; text-align: right;">NON-DEPARTEMEN</th>
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
                    $isZero = $hideAccountWithZero && collect($item['balances'])->every(fn($v) => abs($v) < 0.001);
                @endphp

                @if ($lvl === 'HEADER')
                    <tr style="background-color: #1e3a8a; color: #ffffff; font-weight: bold;">
                        <td style="border: 1px solid #000000; font-weight: bold;">
                            {{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}
                        </td>
                        <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                            {{ $item['balances']['total'] ?? ($grandTotals[$tipe]['total'] ?? 0) }}
                        </td>
                        @foreach ($departments as $dept)
                            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                                {{ $item['balances'][$dept->id] ?? ($grandTotals[$tipe][$dept->id] ?? 0) }}
                            </td>
                        @endforeach
                        <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                            {{ $item['balances']['eliminasi'] ?? ($grandTotals[$tipe]['eliminasi'] ?? 0) }}
                        </td>
                        <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                            {{ $item['balances']['unassigned'] ?? ($grandTotals[$tipe]['unassigned'] ?? 0) }}
                        </td>
                    </tr>
                @elseif ($lvl === 'GROUP ACCOUNT')
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td style="border: 1px solid #cccccc; font-weight: bold; padding-left: 10px;">
                            {{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}
                        </td>
                        <td style="border: 1px solid #cccccc; text-align: right; font-weight: bold;">
                            {{ $item['balances']['total'] ?? 0 }}
                        </td>
                        @foreach ($departments as $dept)
                            <td style="border: 1px solid #cccccc; text-align: right; font-weight: bold;">
                                {{ $item['balances'][$dept->id] ?? 0 }}
                            </td>
                        @endforeach
                        <td style="border: 1px solid #cccccc; text-align: right; font-weight: bold;">
                            {{ $item['balances']['eliminasi'] ?? 0 }}
                        </td>
                        <td style="border: 1px solid #cccccc; text-align: right; font-weight: bold;">
                            {{ $item['balances']['unassigned'] ?? 0 }}
                        </td>
                    </tr>
                @elseif ($lvl === 'ACCOUNT')
                    @if ($isZero) @continue @endif
                    <tr style="background-color: #ffffff;">
                        <td style="border: 1px solid #e2e8f0; font-weight: 600; padding-left: 20px;">
                            {{ $showAccountNumber ? $item['kode_akun'] . ' - ' . $item['nama_akun'] : $item['nama_akun'] }}
                        </td>
                        <td style="border: 1px solid #e2e8f0; text-align: right; font-weight: bold;">
                            {{ $item['balances']['total'] ?? 0 }}
                        </td>
                        @foreach ($departments as $dept)
                            <td style="border: 1px solid #e2e8f0; text-align: right;">
                                {{ $item['balances'][$dept->id] ?? 0 }}
                            </td>
                        @endforeach
                        <td style="border: 1px solid #e2e8f0; text-align: right;">0</td>
                        <td style="border: 1px solid #e2e8f0; text-align: right;">
                            {{ $item['balances']['unassigned'] ?? 0 }}
                        </td>
                    </tr>
                @elseif ($lvl === 'SUB ACCOUNT')
                    @if ($isZero) @continue @endif
                    <tr style="background-color: #fafafa;">
                        <td style="border: 1px solid #f1f5f9; padding-left: 30px; font-style: italic;">
                            {{ $showAccountNumber ? $item['kode_akun'] . ' — ' . $item['nama_akun'] : $item['nama_akun'] }}
                        </td>
                        <td style="border: 1px solid #f1f5f9; text-align: right;">
                            {{ $item['balances']['total'] ?? 0 }}
                        </td>
                        @foreach ($departments as $dept)
                            <td style="border: 1px solid #f1f5f9; text-align: right;">
                                {{ $item['balances'][$dept->id] ?? 0 }}
                            </td>
                        @endforeach
                        <td style="border: 1px solid #f1f5f9; text-align: right;">0</td>
                        <td style="border: 1px solid #f1f5f9; text-align: right;">
                            {{ $item['balances']['unassigned'] ?? 0 }}
                        </td>
                    </tr>
                @elseif ($lvl === 'X')
                    <tr style="background-color: #faf5ff; font-weight: bold;">
                        <td style="border: 1px solid #d8b4fe; font-weight: bold; padding-left: 20px;">
                            {{ $item['nama_akun'] }}
                        </td>
                        <td style="border: 1px solid #d8b4fe; text-align: right; font-weight: bold;">
                            {{ $item['balances']['total'] ?? 0 }}
                        </td>
                        @foreach ($departments as $dept)
                            <td style="border: 1px solid #d8b4fe; text-align: right; font-weight: bold;">
                                {{ $item['balances'][$dept->id] ?? 0 }}
                            </td>
                        @endforeach
                        <td style="border: 1px solid #d8b4fe; text-align: right; font-weight: bold;">0</td>
                        <td style="border: 1px solid #d8b4fe; text-align: right; font-weight: bold;">
                            {{ $item['balances']['unassigned'] ?? 0 }}
                        </td>
                    </tr>
                @elseif ($lvl === 'RK')
                    <tr style="background-color: #eef2ff; font-weight: bold;">
                        <td style="border: 1px solid #c7d2fe; font-weight: bold; padding-left: 20px;">
                            {{ $item['nama_akun'] }}
                        </td>
                        <td style="border: 1px solid #c7d2fe; text-align: right; font-weight: bold;">0</td>
                        @foreach ($departments as $dept)
                            <td style="border: 1px solid #c7d2fe; text-align: right; font-weight: bold;">
                                {{ $item['balances'][$dept->id] ?? 0 }}
                            </td>
                        @endforeach
                        <td style="border: 1px solid #c7d2fe; text-align: right; font-weight: bold;">
                            {{ $item['balances']['eliminasi'] ?? 0 }}
                        </td>
                        <td style="border: 1px solid #c7d2fe; text-align: right; font-weight: bold;">
                            {{ $item['balances']['unassigned'] ?? 0 }}
                        </td>
                    </tr>
                @endif
            @endforeach

            {{-- Total per Tipe --}}
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td style="border: 1px solid #000000; font-weight: bold;">TOTAL {{ $tipeNames[$tipe] }}</td>
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    {{ $grandTotals[$tipe]['total'] ?? 0 }}
                </td>
                @foreach ($departments as $dept)
                    <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                        {{ $grandTotals[$tipe][$dept->id] ?? 0 }}
                    </td>
                @endforeach
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    {{ $grandTotals[$tipe]['eliminasi'] ?? 0 }}
                </td>
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    {{ $grandTotals[$tipe]['unassigned'] ?? 0 }}
                </td>
            </tr>
        @endforeach

        {{-- Grand Total Pasiva --}}
        <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
            <td style="border: 1px solid #000000; font-weight: bold;">TOTAL PASIVA (KEWAJIBAN &amp; EKUITAS)</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                {{ $grandTotals['Pasiva']['total'] ?? 0 }}
            </td>
            @foreach ($departments as $dept)
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                    {{ $grandTotals['Pasiva'][$dept->id] ?? 0 }}
                </td>
            @endforeach
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                {{ $grandTotals['Pasiva']['eliminasi'] ?? 0 }}
            </td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                {{ $grandTotals['Pasiva']['unassigned'] ?? 0 }}
            </td>
        </tr>

        {{-- Status Balance --}}
        <tr style="background-color: #dcfce7; font-weight: bold;">
            <td style="border: 1px solid #86efac; font-weight: bold;">STATUS BALANCE (AKTIVA - PASIVA)</td>
            @php $diffTot = ($grandTotals['Aset']['total'] ?? 0) - ($grandTotals['Pasiva']['total'] ?? 0); @endphp
            <td style="border: 1px solid #86efac; text-align: right; font-weight: bold;">{{ abs($diffTot) < 0.01 ? 0 : round($diffTot, 2) }}</td>
            @foreach ($departments as $dept)
                @php $diff = ($grandTotals['Aset'][$dept->id] ?? 0) - ($grandTotals['Pasiva'][$dept->id] ?? 0); @endphp
                <td style="border: 1px solid #86efac; text-align: right; font-weight: bold;">{{ abs($diff) < 0.01 ? 0 : round($diff, 2) }}</td>
            @endforeach
            <td style="border: 1px solid #86efac; text-align: right; font-weight: bold;">0</td>
            @php $diffUn = ($grandTotals['Aset']['unassigned'] ?? 0) - ($grandTotals['Pasiva']['unassigned'] ?? 0); @endphp
            <td style="border: 1px solid #86efac; text-align: right; font-weight: bold;">{{ abs($diffUn) < 0.01 ? 0 : round($diffUn, 2) }}</td>
        </tr>
    </tbody>
</table>

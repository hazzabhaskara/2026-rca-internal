<table>
    <thead>
        <tr>
            <th colspan="{{ count($departments) + 4 }}" style="font-size: 14pt; font-weight: bold; text-align: center;">
                {{ $siteTitle ?? 'PT REMBULAN CITRA ABADI' }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ count($departments) + 4 }}" style="font-size: 12pt; font-weight: bold; text-align: center;">
                LAPORAN LABA RUGI DEPARTEMEN (MATRIKS KOMPARATIF)
            </th>
        </tr>
        <tr>
            <th colspan="{{ count($departments) + 4 }}" style="font-size: 10pt; text-align: center;">
                Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </th>
        </tr>
        <tr><th colspan="{{ count($departments) + 4 }}"></th></tr>
        <tr style="background-color: #1e293b; color: #ffffff; font-weight: bold;">
            <th style="border: 1px solid #000000; width: 38; text-align: left;">KETERANGAN / NAMA AKUN</th>
            <th style="border: 1px solid #000000; width: 22; text-align: right;">TOTAL KONSOLIDASI</th>
            @foreach ($departments as $dept)
                <th style="border: 1px solid #000000; width: 18; text-align: right;">
                    {{ $dept->kode }} - {{ $dept->deskripsi }}
                </th>
            @endforeach
            <th style="border: 1px solid #000000; width: 18; text-align: right;">ELIMINASI</th>
            <th style="border: 1px solid #000000; width: 18; text-align: right;">NON-DEPARTEMEN</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sections as $secKey => $sec)
            @if (!empty($sec['row_type']))
                @php
                    $isNetProfit = ($sec['row_type'] === 'subtotal_net_profit');
                    $bgColor = $isNetProfit ? '#065f46' : '#e0e7ff';
                    $textColor = $isNetProfit ? '#ffffff' : '#000000';
                    $secTotals = $sec['totals'] ?? ($sec['balances'] ?? []);
                @endphp
                <tr style="background-color: {{ $bgColor }}; color: {{ $textColor }}; font-weight: bold;">
                    <td style="border: 1px solid #000000; font-weight: bold;">
                        {{ strtoupper($sec['title']) }}
                        @if (!empty($sec['marginPercent'])) ({{ $sec['marginPercent']['total'] ?? 0 }}% GM) @endif
                        @if (!empty($sec['opexRatioPercent'])) ({{ $sec['opexRatioPercent']['total'] ?? 0 }}% Rasio) @endif
                    </td>
                    <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                        {{ $secTotals['total'] ?? 0 }}
                    </td>
                    @foreach ($departments as $dept)
                        <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                            {{ $secTotals[$dept->id] ?? 0 }}
                        </td>
                    @endforeach
                    <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                        {{ $secTotals['eliminasi'] ?? 0 }}
                    </td>
                    <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                        {{ $secTotals['unassigned'] ?? 0 }}
                    </td>
                </tr>
            @else
                @php $secTotals = $sec['totals'] ?? ($sec['balances'] ?? []); @endphp
                {{-- Header Seksi --}}
                <tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">
                    <td style="border: 1px solid #000000; font-weight: bold;">{{ strtoupper($sec['title']) }}</td>
                    <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                        {{ $secTotals['total'] ?? 0 }}
                    </td>
                    @foreach ($departments as $dept)
                        <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                            {{ $secTotals[$dept->id] ?? 0 }}
                        </td>
                    @endforeach
                    <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                        {{ $secTotals['eliminasi'] ?? 0 }}
                    </td>
                    <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                        {{ $secTotals['unassigned'] ?? 0 }}
                    </td>
                </tr>

                @foreach ($sec['rows'] ?? ($sec['children'] ?? []) as $acc)
                    @php
                        $isZero = true;
                        $accTotals = $acc['balances'] ?? ($acc['totals'] ?? []);
                        foreach ($allColKeys as $ck) {
                            if (abs($accTotals[$ck] ?? 0) >= 0.01) { $isZero = false; break; }
                        }
                    @endphp
                    @if (!$hideAccountWithZero || !$isZero)
                        <tr>
                            <td style="border: 1px solid #000000;">
                                &nbsp;&nbsp;{{ $acc['kode_akun'] ?? $acc['kode'] }} - {{ $acc['nama_akun'] ?? $acc['nama'] }}
                            </td>
                            <td style="border: 1px solid #000000; text-align: right;">
                                {{ $accTotals['total'] ?? 0 }}
                            </td>
                            @foreach ($departments as $dept)
                                <td style="border: 1px solid #000000; text-align: right;">
                                    {{ $accTotals[$dept->id] ?? 0 }}
                                </td>
                            @endforeach
                            <td style="border: 1px solid #000000; text-align: right;">
                                {{ $accTotals['eliminasi'] ?? 0 }}
                            </td>
                            <td style="border: 1px solid #000000; text-align: right;">
                                {{ $accTotals['unassigned'] ?? 0 }}
                            </td>
                        </tr>

                        @if (!empty($acc['sub_accounts']) || !empty($acc['children']))
                            @foreach ($acc['sub_accounts'] ?? $acc['children'] as $sub)
                                @php
                                    $isSubZero = true;
                                    $subTotals = $sub['balances'] ?? ($sub['totals'] ?? []);
                                    foreach ($allColKeys as $ck) {
                                        if (abs($subTotals[$ck] ?? 0) >= 0.01) { $isSubZero = false; break; }
                                    }
                                @endphp
                                @if (!$hideAccountWithZero || !$isSubZero)
                                    <tr style="color: #64748b;">
                                        <td style="border: 1px solid #000000;">
                                            &nbsp;&nbsp;&nbsp;&nbsp;{{ $sub['kode_akun'] ?? $sub['kode'] }} - {{ $sub['nama_akun'] ?? $sub['nama'] }}
                                        </td>
                                        <td style="border: 1px solid #000000; text-align: right;">
                                            {{ $subTotals['total'] ?? 0 }}
                                        </td>
                                        @foreach ($departments as $dept)
                                            <td style="border: 1px solid #000000; text-align: right;">
                                                {{ $subTotals[$dept->id] ?? 0 }}
                                            </td>
                                        @endforeach
                                        <td style="border: 1px solid #000000; text-align: right;">
                                            {{ $subTotals['eliminasi'] ?? 0 }}
                                        </td>
                                        <td style="border: 1px solid #000000; text-align: right;">
                                            {{ $subTotals['unassigned'] ?? 0 }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endif
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Baris Rekonsiliasi Neraca --}}
        <tr style="background-color: #f8fafc; font-style: italic; border-top: 2px solid #000000;">
            <td style="border: 1px solid #000000;">Neraca: Laba Tahun Berjalan di Ekuitas</td>
            <td style="border: 1px solid #000000; text-align: right;">
                {{ $reconciliation['neracaPerDept']['total'] ?? 0 }}
            </td>
            @foreach ($departments as $dept)
                <td style="border: 1px solid #000000; text-align: right;">
                    {{ $reconciliation['neracaPerDept'][$dept->id] ?? 0 }}
                </td>
            @endforeach
            <td style="border: 1px solid #000000; text-align: right;">0</td>
            <td style="border: 1px solid #000000; text-align: right;">
                {{ $reconciliation['neracaPerDept']['unassigned'] ?? 0 }}
            </td>
        </tr>
    </tbody>
</table>

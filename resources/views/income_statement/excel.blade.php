<table>
    <thead>
        <tr>
            <th colspan="4" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN LABA RUGI
            </th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center; font-size: 12px;">
                Periode {{ \Carbon\Carbon::parse($start_date)->translatedFormat('d M Y') }}
                s/d {{ \Carbon\Carbon::parse($end_date)->translatedFormat('d M Y') }}
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
        {{-- =======================
             Bagian PENDAPATAN
        ======================== --}}
        @if (!empty($groupsPendapatan))
            <tr style="background-color: #059669; color: white;">
                <td colspan="4" style="font-weight: bold; padding: 6px;">PENDAPATAN</td>
            </tr>
            @foreach ($groupsPendapatan as $group)
                {{-- GROUP ACCOUNT --}}
                <tr style="background-color: #d1fae5;">
                    <td style="font-weight: bold; padding: 6px;">{{ $group['group'] }}</td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right; font-weight: bold; padding: 6px;">{{ number_format($group['saldo_group'], 2, ',', '.') }}</td>
                </tr>
                @foreach ($group['accounts'] as $account)
                    @php
                        $hasSubAccounts = !empty($account['sub_accounts']);
                    @endphp
                    
                    {{-- ACCOUNT --}}
                    <tr>
                        <td style="padding-left: 20px; padding: 4px 4px 4px 20px;">{{ $account['nama_akun'] }}</td>
                        <td></td>
                        <td style="text-align: right; padding: 4px;">{{ number_format($account['saldo_account'], 2, ',', '.') }}</td>
                        <td></td>
                    </tr>
                    
                    @if ($hasSubAccounts)
                        @foreach ($account['sub_accounts'] as $sub)
                            {{-- SUB ACCOUNT --}}
                            <tr style="background-color: #f9fafb;">
                                <td style="padding-left: 40px; padding: 3px 3px 3px 40px; color: #4b5563;">{{ $sub['kode_akun'] }} - {{ $sub['nama_akun'] }}</td>
                                <td style="text-align: right; padding: 3px; color: #4b5563;">{{ number_format($sub['saldo'], 2, ',', '.') }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            @endforeach
            {{-- Total Pendapatan --}}
            <tr style="background-color: #047857; color: white;">
                <td style="font-weight: bold; padding: 8px;">TOTAL PENDAPATAN</td>
                <td></td>
                <td></td>
                <td style="text-align: right; font-weight: bold; padding: 8px;">{{ number_format($totalPendapatan, 2, ',', '.') }}</td>
            </tr>
        @endif

        {{-- Spacer --}}
        <tr><td colspan="4" style="height: 15px;"></td></tr>

        {{-- ===================
             Bagian BEBAN
        ==================== --}}
        @if (!empty($groupsBeban))
            <tr style="background-color: #dc2626; color: white;">
                <td colspan="4" style="font-weight: bold; padding: 6px;">BEBAN</td>
            </tr>
            @foreach ($groupsBeban as $group)
                {{-- GROUP ACCOUNT --}}
                <tr style="background-color: #fee2e2;">
                    <td style="font-weight: bold; padding: 6px;">{{ $group['group'] }}</td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right; font-weight: bold; padding: 6px;">{{ number_format($group['saldo_group'], 2, ',', '.') }}</td>
                </tr>
                @foreach ($group['accounts'] as $account)
                    @php
                        $hasSubAccounts = !empty($account['sub_accounts']);
                    @endphp
                    
                    {{-- ACCOUNT --}}
                    <tr>
                        <td style="padding-left: 20px; padding: 4px 4px 4px 20px;">{{ $account['nama_akun'] }}</td>
                        <td></td>
                        <td style="text-align: right; padding: 4px;">{{ number_format($account['saldo_account'], 2, ',', '.') }}</td>
                        <td></td>
                    </tr>
                    
                    @if ($hasSubAccounts)
                        @foreach ($account['sub_accounts'] as $sub)
                            {{-- SUB ACCOUNT --}}
                            <tr style="background-color: #f9fafb;">
                                <td style="padding-left: 40px; padding: 3px 3px 3px 40px; color: #4b5563;">{{ $sub['kode_akun'] }} - {{ $sub['nama_akun'] }}</td>
                                <td style="text-align: right; padding: 3px; color: #4b5563;">{{ number_format($sub['saldo'], 2, ',', '.') }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            @endforeach
            {{-- Total Beban --}}
            <tr style="background-color: #b91c1c; color: white;">
                <td style="font-weight: bold; padding: 8px;">TOTAL BEBAN</td>
                <td></td>
                <td></td>
                <td style="text-align: right; font-weight: bold; padding: 8px;">{{ number_format($totalBeban, 2, ',', '.') }}</td>
            </tr>
        @endif

        {{-- Spacer --}}
        <tr><td colspan="4" style="height: 15px;"></td></tr>

        {{-- ==========================
             RINGKASAN AKHIR
        =========================== --}}
        <tr style="background-color: #e2e8f0;">
            <td style="font-weight: bold; padding: 8px;">LABA SEBELUM PAJAK PENGHASILAN</td>
            <td></td>
            <td></td>
            <td style="text-align: right; font-weight: bold; padding: 8px;">{{ number_format($labaSebelumPajak, 2, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #fef3c7;">
            <td style="font-weight: bold; padding: 6px;">BEBAN PAJAK PENGHASILAN</td>
            <td></td>
            <td></td>
            <td style="text-align: right; font-weight: bold; padding: 6px;">{{ number_format($bebanPajak, 2, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #1e40af; color: white;">
            <td style="font-weight: bold; padding: 10px; font-size: 14px;">LABA BERSIH SETELAH PAJAK PENGHASILAN</td>
            <td></td>
            <td></td>
            <td style="text-align: right; font-weight: bold; padding: 10px; font-size: 14px;">{{ number_format($labaSetelahPajak, 2, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

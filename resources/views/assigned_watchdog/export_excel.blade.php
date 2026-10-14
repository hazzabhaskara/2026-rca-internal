<table>
    <thead>
        <tr>
            <th colspan="10" style="font-size: 14pt; font-weight: bold; text-align: center;">
                PT REMBULAN CITRA ABADI
            </th>
        </tr>
        <tr>
            <th colspan="10" style="font-size: 12pt; font-weight: bold; text-align: center;">
                LAPORAN AUDIT TRANSAKSI DENGAN DEPARTEMEN (ASSIGNED WATCHDOG)
            </th>
        </tr>
        <tr>
            <th colspan="10" style="font-size: 9pt; text-align: center; color: #555555;">
                Dicetak pada: {{ $exportDate ?? date('d/m/Y H:i:s') }}
                @if(!empty($departemenNama))
                    | Departemen: {{ $departemenNama }}
                @endif
                @if(!empty($startDate) || !empty($endDate))
                    | Periode: {{ $startDate ?? 'Awal' }} s/d {{ $endDate ?? 'Akhir' }}
                @endif
                @if(!empty($kategori) && $kategori !== 'all')
                    | Kategori: {{ strtoupper($kategori) }}
                @endif
                @if(!empty($tipeAkun) && $tipeAkun !== 'all')
                    | Tipe: {{ $tipeAkun }}
                @endif
                @if(!empty($sourcePrefix) && $sourcePrefix !== 'all')
                    | Prefix: {{ $sourcePrefix }}
                @endif
                @if(!empty($anomaliesOnly))
                    | Filter: Anomali Saja
                @endif
                @if(!empty($search))
                    | Cari: "{{ $search }}"
                @endif
            </th>
        </tr>
        <tr>
            <th colspan="10"></th>
        </tr>
        {{-- KPI Summary Row --}}
        <tr style="background-color: #f1f5f9; font-weight: bold;">
            <th colspan="2" style="border: 1px solid #cbd5e1; text-align: left;">
                Total Assigned: {{ number_format($kpis['total_assigned'] ?? ($kpis['total'] ?? count($details)), 0, ',', '.') }}
            </th>
            <th colspan="2" style="border: 1px solid #cbd5e1; text-align: left;">
                Total Anomali: {{ number_format($kpis['total_anomalies'] ?? 0, 0, ',', '.') }}
            </th>
            <th colspan="2" style="border: 1px solid #cbd5e1; text-align: left;">
                Total Debit: {{ number_format($totalDebit ?? 0, 2, ',', '.') }}
            </th>
            <th colspan="2" style="border: 1px solid #cbd5e1; text-align: left;">
                Total Kredit: {{ number_format($totalCredit ?? 0, 2, ',', '.') }}
            </th>
            <th colspan="2" style="border: 1px solid #cbd5e1; text-align: right;">
                Baris Terfilter: {{ count($details) }}
            </th>
        </tr>
        <tr>
            <th colspan="10"></th>
        </tr>
        <tr style="background-color: #1e293b; color: #ffffff; font-weight: bold; text-align: center;">
            <th style="border: 1px solid #000000; width: 6; text-align: center;">NO</th>
            <th style="border: 1px solid #000000; width: 14; text-align: center;">TANGGAL</th>
            <th style="border: 1px solid #000000; width: 22; text-align: left;">NO. VOUCHER</th>
            <th style="border: 1px solid #000000; width: 16; text-align: center;">KODE AKUN</th>
            <th style="border: 1px solid #000000; width: 35; text-align: left;">NAMA AKUN</th>
            <th style="border: 1px solid #000000; width: 20; text-align: left;">DEPARTEMEN</th>
            <th style="border: 1px solid #000000; width: 35; text-align: left;">STATUS ANOMALI / REASON</th>
            <th style="border: 1px solid #000000; width: 20; text-align: right;">DEBET (RP)</th>
            <th style="border: 1px solid #000000; width: 20; text-align: right;">KREDIT (RP)</th>
            <th style="border: 1px solid #000000; width: 35; text-align: left;">KETERANGAN</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($details as $index => $row)
            @php
                $anomalyText = 'Normal';
                $isAnomaly = !empty($row->anomaly) && !empty($row->anomaly['is_anomaly']);
                if ($isAnomaly) {
                    if (!empty($row->anomaly['reasons'])) {
                        $anomalyText = implode('; ', $row->anomaly['reasons']);
                    } elseif (!empty($row->anomaly['rules'])) {
                        $anomalyText = implode(', ', $row->anomaly['rules']);
                    } else {
                        $anomalyText = 'Anomali';
                    }
                }
                $deptNama = $row->departemenAkun && $row->departemenAkun->departemen 
                    ? ($row->departemenAkun->departemen->deskripsi ?? $row->departemenAkun->departemen->nama) 
                    : '-';
                $comment = !empty($row->comment) ? $row->comment : ($row->journalEntry->comment ?? '-');
            @endphp
            <tr>
                <td style="border: 1px solid #cccccc; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #cccccc; text-align: center;">
                    {{ $row->journalEntry && $row->journalEntry->tanggal ? \Carbon\Carbon::parse($row->journalEntry->tanggal)->format('d/m/Y') : '-' }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: left;">
                    {{ $row->journalEntry->source ?? '-' }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: center; mso-number-format:'\@';">
                    {{ $row->kode_akun }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: left;">
                    {{ $row->chartOfAccount->nama_akun ?? '-' }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: left;">
                    {{ $deptNama }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: left; {{ $isAnomaly ? 'color: #b91c1c; font-weight: bold;' : 'color: #15803d;' }}">
                    {{ $anomalyText }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: right;">
                    {{ (float) $row->debits }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: right;">
                    {{ (float) $row->credits }}
                </td>
                <td style="border: 1px solid #cccccc; text-align: left;">
                    {{ $comment }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" style="border: 1px solid #cccccc; text-align: center; padding: 15px;">
                    Tidak ada transaksi dengan departemen yang sesuai dengan filter.
                </td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr style="background-color: #1e293b; color: #ffffff; font-weight: bold;">
            <td colspan="7" style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                TOTAL MUTASI (RP)
            </td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                {{ (float) ($totalDebit ?? 0) }}
            </td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
                {{ (float) ($totalCredit ?? 0) }}
            </td>
            <td style="border: 1px solid #000000;"></td>
        </tr>
    </tfoot>
</table>

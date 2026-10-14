<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Audit Transaksi Assigned Watchdog - {{ $exportDate ?? '' }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 8mm 6mm 8mm 6mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 7.2pt;
            color: #0f172a;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 5px;
        }
        .header h2 {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .header h3 {
            margin: 2px 0;
            font-size: 9.5pt;
            font-weight: bold;
            color: #1e293b;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 7pt;
            color: #475569;
        }

        /* KPI summary box */
        .kpi-container {
            width: 100%;
            margin-bottom: 8px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            padding: 4px 6px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
        }
        .kpi-table td {
            font-size: 7pt;
            padding: 2px 4px;
        }
        .kpi-label {
            color: #475569;
            font-weight: normal;
        }
        .kpi-value {
            font-weight: bold;
            color: #0f172a;
        }

        /* Main Data Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.data-table thead {
            display: table-header-group;
        }
        table.data-table tr {
            page-break-inside: avoid;
        }
        table.data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 6.8pt;
            text-transform: uppercase;
            padding: 4px 3px;
            border: 1px solid #334155;
            text-align: center;
        }
        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 3px 3px;
            font-size: 6.8pt;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
        }
        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* Badges */
        .badge-normal {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 6pt;
            font-weight: bold;
            background-color: #dcfce7;
            color: #166534;
            border: 0.5px solid #86efac;
        }
        .badge-prefix {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 6pt;
            font-weight: bold;
            background-color: #fee2e2;
            color: #991b1b;
            border: 0.5px solid #fca5a5;
            margin-bottom: 1px;
        }
        .badge-outlier {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 6pt;
            font-weight: bold;
            background-color: #fef3c7;
            color: #92400e;
            border: 0.5px solid #fcd34d;
            margin-bottom: 1px;
        }
        .badge-unit {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 6pt;
            font-weight: bold;
            background-color: #f3e8ff;
            color: #6b21a8;
            border: 0.5px solid #d8b4fe;
            margin-bottom: 1px;
        }
        .badge-anomaly {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 6pt;
            font-weight: bold;
            background-color: #fee2e2;
            color: #991b1b;
            border: 0.5px solid #fca5a5;
            margin-bottom: 1px;
        }

        /* Footer */
        .footer {
            margin-top: 6px;
            font-size: 6.5pt;
            color: #64748b;
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            text-align: left;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
        }
    </style>
</head>
<body>

    {{-- Report Header --}}
    <div class="header">
        <h2>PT REMBULAN CITRA ABADI</h2>
        <h3>LAPORAN AUDIT TRANSAKSI DENGAN DEPARTEMEN (ASSIGNED WATCHDOG)</h3>
        <p>
            Dicetak: {{ $exportDate ?? date('d/m/Y H:i:s') }}
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
        </p>
    </div>

    {{-- KPI Summary Bar --}}
    <div class="kpi-container">
        <table class="kpi-table">
            <tr>
                <td style="width: 25%;">
                    <span class="kpi-label">Total Assigned:</span>
                    <span class="kpi-value">{{ number_format($kpis['total_assigned'] ?? ($kpis['total'] ?? count($details)), 0, ',', '.') }} baris</span>
                </td>
                <td style="width: 25%;">
                    <span class="kpi-label">Total Anomali:</span>
                    <span class="kpi-value" style="color: #b91c1c;">{{ number_format($kpis['total_anomalies'] ?? 0, 0, ',', '.') }} baris</span>
                </td>
                <td style="width: 25%;">
                    <span class="kpi-label">Total Debit:</span>
                    <span class="kpi-value">Rp {{ number_format($totalDebit ?? 0, 2, ',', '.') }}</span>
                </td>
                <td style="width: 25%; text-align: right;">
                    <span class="kpi-label">Total Kredit:</span>
                    <span class="kpi-value">Rp {{ number_format($totalCredit ?? 0, 2, ',', '.') }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Main Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">NO</th>
                <th style="width: 7%;">TANGGAL</th>
                <th style="width: 13%;">NO. VOUCHER</th>
                <th style="width: 8%;">KODE AKUN</th>
                <th style="width: 15%;">NAMA AKUN</th>
                <th style="width: 11%;">DEPARTEMEN</th>
                <th style="width: 15%;">STATUS / ANOMALI</th>
                <th style="width: 12%;">KETERANGAN</th>
                <th style="width: 8%;">DEBET (RP)</th>
                <th style="width: 8%;">KREDIT (RP)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($details as $index => $row)
                @php
                    $deptNama = $row->departemenAkun && $row->departemenAkun->departemen 
                        ? ($row->departemenAkun->departemen->deskripsi ?? $row->departemenAkun->departemen->nama) 
                        : '-';
                    $comment = !empty($row->comment) ? $row->comment : ($row->journalEntry->comment ?? '-');
                    $isAnomaly = !empty($row->anomaly) && !empty($row->anomaly['is_anomaly']);
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">
                        {{ $row->journalEntry && $row->journalEntry->tanggal ? \Carbon\Carbon::parse($row->journalEntry->tanggal)->format('d/m/Y') : '-' }}
                    </td>
                    <td class="text-left font-bold">
                        {{ $row->journalEntry->source ?? '-' }}
                    </td>
                    <td class="text-center font-bold">
                        {{ $row->kode_akun }}
                    </td>
                    <td class="text-left">
                        {{ $row->chartOfAccount->nama_akun ?? '-' }}
                    </td>
                    <td class="text-left">
                        {{ $deptNama }}
                    </td>
                    <td class="text-left">
                        @if (!$isAnomaly)
                            <span class="badge-normal">Normal</span>
                        @else
                            @foreach ($row->anomaly['rules'] as $rIdx => $rule)
                                @php
                                    $reason = $row->anomaly['reasons'][$rIdx] ?? '';
                                @endphp
                                @if ($rule === \App\Services\AnomalyDetectionEngine::RULE_PREFIX_MISMATCH)
                                    <span class="badge-prefix">Prefix Mismatch</span>
                                @elseif ($rule === \App\Services\AnomalyDetectionEngine::RULE_VOUCHER_INCONSISTENCY)
                                    <span class="badge-outlier">Inkonsistensi</span>
                                @elseif ($rule === \App\Services\AnomalyDetectionEngine::RULE_UNIT_ACCOUNT_MISMATCH)
                                    <span class="badge-unit">Akun Unit</span>
                                @else
                                    <span class="badge-anomaly">Anomali</span>
                                @endif
                                @if (!empty($reason))
                                    <div style="font-size: 5.5pt; color: #475569; line-height: 1;">{{ $reason }}</div>
                                @endif
                            @endforeach
                        @endif
                    </td>
                    <td class="text-left">
                        {{ $comment }}
                    </td>
                    <td class="text-right">
                        {{ $row->debits > 0 ? number_format($row->debits, 2, ',', '.') : '-' }}
                    </td>
                    <td class="text-right">
                        {{ $row->credits > 0 ? number_format($row->credits, 2, ',', '.') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 15px;">
                        Tidak ada transaksi dengan departemen yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #1e293b; color: #ffffff; font-weight: bold;">
                <td colspan="8" class="text-right font-bold" style="padding: 4px;">TOTAL MUTASI:</td>
                <td class="text-right font-bold" style="padding: 4px;">
                    {{ number_format($totalDebit ?? 0, 2, ',', '.') }}
                </td>
                <td class="text-right font-bold" style="padding: 4px;">
                    {{ number_format($totalCredit ?? 0, 2, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Report Footer --}}
    <div class="footer">
        <div class="footer-left">
            PT Rembulan Citra Abadi &bull; Assigned Watchdog Audit System &bull; Laporan dicetak otomatis
        </div>
        <div class="footer-right">
            Total Baris: {{ count($details) }} transaksi
        </div>
    </div>

</body>
</html>

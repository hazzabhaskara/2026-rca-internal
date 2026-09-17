<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Rekonsiliasi Closing vs Kas & Bank</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm 10mm 12mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            color: #1e293b;
            line-height: 1.3;
        }

        .header-container {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .company-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-title {
            font-size: 10.5pt;
            font-weight: bold;
            color: #3b82f6;
            margin-top: 2px;
        }

        .doc-meta {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 3px;
        }

        /* KPI summary boxes */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .kpi-card {
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            padding: 5px 8px;
            text-align: center;
        }

        .kpi-label {
            font-size: 6.5pt;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }

        .kpi-value {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }

        /* Main table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }

        table.data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            padding: 5px 4px;
            border: 1px solid #334155;
            text-align: center;
            font-size: 7pt;
        }

        table.data-table td {
            padding: 4px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 6.5pt;
            font-weight: bold;
            border-radius: 3px;
            text-align: center;
        }

        .badge-match {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .badge-pending {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fcd34d;
        }

        .badge-selisih {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        /* Sign-off section */
        .sign-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .sign-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 7.5pt;
        }

        .sign-space {
            height: 45px;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <table class="header-container">
        <tr>
            <td style="vertical-align: top;">
                <div class="company-title">{{ $siteTitle ?? 'PT REMBULAN CITRA ABADI' }}</div>
                <div class="doc-title">LAPORAN REKONSILIASI CLOSING HARIAN VS KAS & BANK (INFLOW AUDIT)</div>
                <div class="doc-meta">
                    <strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
                    &nbsp;|&nbsp;
                    <strong>Departemen:</strong> {{ $activeDepartment ? ($activeDepartment->kode . ' - ' . $activeDepartment->deskripsi) : 'Semua Departemen (Konsolidasi)' }}
                    &nbsp;|&nbsp;
                    <strong>Status Filter:</strong> {{ $statusFilter ?? 'ALL' }}
                </div>
            </td>
            <td style="text-align: right; vertical-align: top; font-size: 7pt; color: #64748b;">
                Tanggal Cetak: {{ date('d/m/Y H:i:s') }}<br>
                Dokumen: Internal Audit Report
            </td>
        </tr>
    </table>

    {{-- KPI Cards Summary --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Total Omset Kasir</div>
                <div class="kpi-value">Rp {{ number_format($kpi['total_omset'], 2, ',', '.') }}</div>
                <div style="font-size: 6.5pt; color: #64748b; margin-top: 1px;">
                    Cash: {{ number_format($kpi['omset_cash'], 0, ',', '.') }} | QRIS: {{ number_format($kpi['omset_qris'], 0, ',', '.') }}
                </div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Total Kas & Bank Masuk</div>
                <div class="kpi-value">Rp {{ number_format($kpi['total_kas_masuk'], 2, ',', '.') }}</div>
                <div style="font-size: 6.5pt; color: #64748b; margin-top: 1px;">
                    Cash: {{ number_format($kpi['kas_cash'], 0, ',', '.') }} | QRIS: {{ number_format($kpi['kas_qris'], 0, ',', '.') }}
                </div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Net Selisih (Inflow Variance)</div>
                <div class="kpi-value" style="color: {{ $kpi['net_selisih'] == 0 ? '#15803d' : ($kpi['net_selisih'] < 0 ? '#b91c1c' : '#2563eb') }};">
                    Rp {{ number_format($kpi['net_selisih'], 2, ',', '.') }}
                </div>
                <div style="font-size: 6.5pt; color: #64748b; margin-top: 1px;">
                    {{ $kpi['net_selisih'] == 0 ? 'Tepat Seimbang' : ($kpi['net_selisih'] < 0 ? 'Under-Deposit / Potongan' : 'Over-Deposit / Pending Closing') }}
                </div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Status Distribusi ({{ $kpi['count_total'] }} Records)</div>
                <div class="kpi-value" style="font-size: 7.5pt; margin-top: 4px;">
                    <span style="color: #15803d;">MATCH: {{ $kpi['count_match'] }}</span> &nbsp;|&nbsp;
                    <span style="color: #b45309;">PENDING: {{ $kpi['count_pending'] }}</span> &nbsp;|&nbsp;
                    <span style="color: #b91c1c;">SELISIH: {{ $kpi['count_selisih'] }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- Main Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 24px;">No</th>
                <th style="width: 62px;">Tanggal</th>
                <th style="width: 90px;">Departemen</th>
                <th style="width: 65px;">Omset Cash</th>
                <th style="width: 65px;">Omset QRIS</th>
                <th style="width: 75px;">Total Omset</th>
                <th style="width: 65px;">Kas Masuk Cash</th>
                <th style="width: 65px;">Kas Masuk QRIS</th>
                <th style="width: 75px;">Total Kas Masuk</th>
                <th style="width: 70px;">Selisih</th>
                <th style="width: 48px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                    <td><strong>{{ $row['dept_kode'] }}</strong> - {{ $row['dept_nama'] }}</td>
                    <td class="text-right">{{ number_format($row['omset_cash'], 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['omset_qris'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($row['total_omset'], 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['kas_masuk_cash'], 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['kas_masuk_qris'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($row['total_kas_masuk'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: {{ $row['selisih'] == 0 ? '#15803d' : ($row['selisih'] < 0 ? '#b91c1c' : '#2563eb') }};">
                        {{ number_format($row['selisih'], 2, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if ($row['status'] === 'MATCH')
                            <span class="badge badge-match">MATCH</span>
                        @elseif ($row['status'] === 'PENDING')
                            <span class="badge badge-pending">PENDING</span>
                        @else
                            <span class="badge badge-selisih">SELISIH</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="font-style: italic; color: #64748b; padding: 12px;">
                        Tidak ada data rekonsiliasi untuk filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #0f172a;">
                <td colspan="3" class="text-center">TOTAL REKONSILIASI</td>
                <td class="text-right">{{ number_format($kpi['omset_cash'], 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($kpi['omset_qris'], 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($kpi['total_omset'], 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($kpi['kas_cash'], 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($kpi['kas_qris'], 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($kpi['total_kas_masuk'], 2, ',', '.') }}</td>
                <td class="text-right" style="color: {{ $kpi['net_selisih'] == 0 ? '#15803d' : ($kpi['net_selisih'] < 0 ? '#b91c1c' : '#2563eb') }};">
                    {{ number_format($kpi['net_selisih'], 2, ',', '.') }}
                </td>
                <td class="text-center" style="font-size: 6.5pt;">
                    M:{{ $kpi['count_match'] }} P:{{ $kpi['count_pending'] }} S:{{ $kpi['count_selisih'] }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Sign-off block --}}
    <table class="sign-table">
        <tr>
            <td>
                <div>Disiapkan Oleh,</div>
                <div class="sign-space"></div>
                <div style="font-weight: bold;">( Kasir / Admin Operasional )</div>
            </td>
            <td>
                <div>Diperiksa Oleh,</div>
                <div class="sign-space"></div>
                <div style="font-weight: bold;">( Internal Auditor )</div>
            </td>
            <td>
                <div>Disetujui Oleh,</div>
                <div class="sign-space"></div>
                <div style="font-weight: bold;">( Finance Manager / Direksi )</div>
            </td>
        </tr>
    </table>
</body>

</html>

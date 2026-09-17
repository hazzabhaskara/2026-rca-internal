<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Audit Transaksi Tanpa Departemen - {{ $exportDate ?? '' }}</title>
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
        .badge-real {
            color: #1d4ed8;
            font-weight: bold;
        }
        .badge-nominal {
            color: #7e22ce;
            font-weight: bold;
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
        <h3>LAPORAN AUDIT TRANSAKSI TANPA DEPARTEMEN (UNASSIGNED WATCHDOG)</h3>
        <p>
            Dicetak: {{ $exportDate ?? date('d/m/Y H:i:s') }}
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
        </p>
    </div>

    {{-- KPI Summary Bar --}}
    <div class="kpi-container">
        <table class="kpi-table">
            <tr>
                <td style="width: 25%;">
                    <span class="kpi-label">Total Unassigned:</span>
                    <span class="kpi-value">{{ number_format($kpis['total'] ?? count($details), 0, ',', '.') }} baris</span>
                </td>
                <td style="width: 25%;">
                    <span class="kpi-label">Akun Riil (Neraca):</span>
                    <span class="kpi-value">{{ number_format($kpis['real'] ?? 0, 0, ',', '.') }}</span>
                </td>
                <td style="width: 25%;">
                    <span class="kpi-label">Akun Nominal (Laba Rugi):</span>
                    <span class="kpi-value">{{ number_format($kpis['nominal'] ?? 0, 0, ',', '.') }}</span>
                </td>
                <td style="width: 25%; text-align: right;">
                    <span class="kpi-label">Total Mutasi:</span>
                    <span class="kpi-value">Rp {{ number_format($totalDebit ?? 0, 0, ',', '.') }}</span>
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
                <th style="width: 14%;">NO. VOUCHER / SOURCE</th>
                <th style="width: 8%;">KODE AKUN</th>
                <th style="width: 18%;">NAMA AKUN</th>
                <th style="width: 7%;">KATEGORI</th>
                <th style="width: 7%;">TIPE</th>
                <th style="width: 16%;">KETERANGAN</th>
                <th style="width: 10%;">DEBIT (RP)</th>
                <th style="width: 10%;">KREDIT (RP)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($details as $index => $row)
                @php
                    $tipe = $row->chartOfAccount->tipe_akun ?? '-';
                    $isReal = in_array($tipe, ['Aset', 'Kewajiban', 'Ekuitas']);
                    $comment = !empty($row->comment) ? $row->comment : ($row->journalEntry->comment ?? '-');
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">
                        {{ $row->journalEntry->tanggal ? \Carbon\Carbon::parse($row->journalEntry->tanggal)->format('d/m/Y') : '-' }}
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
                    <td class="text-center {{ $isReal ? 'badge-real' : 'badge-nominal' }}">
                        {{ $isReal ? 'Riil' : 'Nominal' }}
                    </td>
                    <td class="text-center">
                        {{ $tipe }}
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
                        Tidak ada transaksi tanpa departemen yang ditemukan.
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
            PT Rembulan Citra Abadi &bull; Unassigned Watchdog Audit System &bull; Halaman dicetak otomatis
        </div>
        <div class="footer-right">
            Total Baris: {{ count($details) }} transaksi
        </div>
    </div>

</body>
</html>

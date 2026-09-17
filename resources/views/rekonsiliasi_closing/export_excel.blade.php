<table>
    <thead>
        <tr>
            <th colspan="12" style="font-size: 14pt; font-weight: bold; text-align: center;">
                PT REMBULAN CITRA ABADI
            </th>
        </tr>
        <tr>
            <th colspan="12" style="font-size: 12pt; font-weight: bold; text-align: center;">
                LAPORAN REKONSILIASI CLOSING HARIAN VS KAS &amp; BANK (INFLOW AUDIT)
            </th>
        </tr>
        <tr>
            <th colspan="12" style="text-align: center;">
                Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="12" style="text-align: center;">
                Departemen: {{ $activeDepartment ? ($activeDepartment->kode . ' - ' . $activeDepartment->deskripsi) : 'Semua Departemen (Konsolidasi)' }} | Status Filter: {{ $statusFilter ?? 'ALL' }}
            </th>
        </tr>
        <tr>
            <th colspan="12" style="font-style: italic; text-align: center;">
                Waktu Cetak: {{ date('d/m/Y H:i:s') }}
            </th>
        </tr>
        <tr>
            <th colspan="12"></th>
        </tr>

        {{-- Ringkasan KPI --}}
        <tr>
            <th colspan="4" style="font-weight: bold; background-color: #E2E8F0;">METRIK AUDIT</th>
            <th colspan="8" style="font-weight: bold; background-color: #E2E8F0;">NILAI / JUMLAH</th>
        </tr>
        <tr>
            <td colspan="4">Total Omset Kasir</td>
            <td colspan="8" align="right" style="font-weight: bold;">{{ number_format($kpi['total_omset'], 2, ',', '.') }} (Cash: {{ number_format($kpi['omset_cash'], 2, ',', '.') }} | QRIS: {{ number_format($kpi['omset_qris'], 2, ',', '.') }})</td>
        </tr>
        <tr>
            <td colspan="4">Total Kas &amp; Bank Masuk</td>
            <td colspan="8" align="right" style="font-weight: bold;">{{ number_format($kpi['total_kas_masuk'], 2, ',', '.') }} (Cash: {{ number_format($kpi['kas_cash'], 2, ',', '.') }} | QRIS: {{ number_format($kpi['kas_qris'], 2, ',', '.') }})</td>
        </tr>
        <tr>
            <td colspan="4">Net Selisih (Kas Masuk - Omset)</td>
            <td colspan="8" align="right" style="font-weight: bold;">{{ number_format($kpi['net_selisih'], 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="4">Distribusi Status</td>
            <td colspan="8" style="font-weight: bold;">MATCH: {{ $kpi['count_match'] }} | PENDING: {{ $kpi['count_pending'] }} | SELISIH: {{ $kpi['count_selisih'] }} | Total: {{ $kpi['count_total'] }}</td>
        </tr>
        <tr>
            <th colspan="12"></th>
        </tr>

        {{-- Header Kolom --}}
        <tr style="background-color: #1E293B; color: #FFFFFF; font-weight: bold; text-align: center;">
            <th style="background-color: #1E293B; color: #FFFFFF; border: 1px solid #334155;">No</th>
            <th style="background-color: #1E293B; color: #FFFFFF; border: 1px solid #334155;">Tanggal</th>
            <th style="background-color: #1E293B; color: #FFFFFF; border: 1px solid #334155;">Kode Unit</th>
            <th style="background-color: #1E293B; color: #FFFFFF; border: 1px solid #334155;">Nama Unit / Wahana</th>
            <th style="background-color: #1E293B; color: #FFFFFF; border: 1px solid #334155;">Omset Cash (Rp)</th>
            <th style="background-color: #1E293B; color: #FFFFFF; border: 1px solid #334155;">Omset QRIS (Rp)</th>
            <th style="background-color: #1E293B; color: #FFFFFF; border: 1px solid #334155;">Total Omset (Rp)</th>
            <th style="background-color: #1E293B; color: #FFFFFF; border: 1px solid #334155;">Kas Masuk Cash (Rp)</th>
            <th style="background-color: #1E293B; color: #FFFFFF; border: 1px solid #334155;">Kas Masuk QRIS (Rp)</th>
            <th style="background-color: #1E293B; color: #FFFFFF; border: 1px solid #334155;">Total Kas Masuk (Rp)</th>
            <th style="background-color: #1E293B; color: #FFFFFF; border: 1px solid #334155;">Selisih (Rp)</th>
            <th style="background-color: #1E293B; color: #FFFFFF; border: 1px solid #334155;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($items as $index => $row)
            <tr>
                <td align="center">{{ $index + 1 }}</td>
                <td align="center">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                <td align="center">{{ $row['dept_kode'] }}</td>
                <td>{{ $row['dept_nama'] }}</td>
                <td align="right">{{ number_format($row['omset_cash'], 2, ',', '.') }}</td>
                <td align="right">{{ number_format($row['omset_qris'], 2, ',', '.') }}</td>
                <td align="right" style="font-weight: bold;">{{ number_format($row['total_omset'], 2, ',', '.') }}</td>
                <td align="right">{{ number_format($row['kas_masuk_cash'], 2, ',', '.') }}</td>
                <td align="right">{{ number_format($row['kas_masuk_qris'], 2, ',', '.') }}</td>
                <td align="right" style="font-weight: bold;">{{ number_format($row['total_kas_masuk'], 2, ',', '.') }}</td>
                <td align="right" style="font-weight: bold; color: {{ $row['selisih'] == 0 ? '#16A34A' : ($row['selisih'] < 0 ? '#DC2626' : '#2563EB') }};">
                    {{ number_format($row['selisih'], 2, ',', '.') }}
                </td>
                <td align="center" style="font-weight: bold; color: {{ $row['status'] === 'MATCH' ? '#16A34A' : ($row['status'] === 'PENDING' ? '#D97706' : '#DC2626') }};">
                    {{ $row['status'] }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12" align="center" style="font-style: italic; color: #64748B;">
                    Tidak ada data rekonsiliasi untuk filter yang dipilih.
                </td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr style="background-color: #F1F5F9; font-weight: bold;">
            <td colspan="4" align="center">TOTAL</td>
            <td align="right">{{ number_format($kpi['omset_cash'], 2, ',', '.') }}</td>
            <td align="right">{{ number_format($kpi['omset_qris'], 2, ',', '.') }}</td>
            <td align="right">{{ number_format($kpi['total_omset'], 2, ',', '.') }}</td>
            <td align="right">{{ number_format($kpi['kas_cash'], 2, ',', '.') }}</td>
            <td align="right">{{ number_format($kpi['kas_qris'], 2, ',', '.') }}</td>
            <td align="right">{{ number_format($kpi['total_kas_masuk'], 2, ',', '.') }}</td>
            <td align="right" style="color: {{ $kpi['net_selisih'] == 0 ? '#16A34A' : ($kpi['net_selisih'] < 0 ? '#DC2626' : '#2563EB') }};">
                {{ number_format($kpi['net_selisih'], 2, ',', '.') }}
            </td>
            <td align="center">
                M: {{ $kpi['count_match'] }} | P: {{ $kpi['count_pending'] }} | S: {{ $kpi['count_selisih'] }}
            </td>
        </tr>
    </tfoot>
</table>

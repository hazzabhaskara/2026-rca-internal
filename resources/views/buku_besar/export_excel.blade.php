<table>
    <thead>
        <tr>
            <th colspan="5">Buku Besar</th>
        </tr>
        <tr>
            <th colspan="5">
                Periode: {{ \Carbon\Carbon::parse($start_date)->format('d M Y') }}
                - {{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="5">
                Departemen: {{ $departemen_name ?? 'Semua Departemen (Konsolidasi)' }}
            </th>
        </tr>
        <tr>
            <th>Tanggal</th>
            <th>Kode Akun</th>
            <th>Nama Akun</th>
            <th>Debet</th>
            <th>Kredit</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
                <td>{{ $row->kode_akun }}</td>
                <td>{{ $row->chartOfAccount->nama_akun ?? '-' }}</td>
                <td align="right">{{ number_format($row->debits, 2, ',', '.') }}</td>
                <td align="right">{{ number_format($row->credits, 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Buku Besar</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }

        h2,
        h4 {
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    <h2 style="text-align: center;">Buku Besar</h2>
    <h4 style="text-align: center;">
        Periode: {{ \Carbon\Carbon::parse($start_date)->format('d M Y') }}
        - {{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}
    </h4>
    <h4 style="text-align: center; margin-top: 4px; color: #555;">
        Departemen: {{ $departemen_name ?? 'Semua Departemen (Konsolidasi)' }}
    </h4>

    <table>
        <thead>
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
                    <td style="text-align: right;">{{ number_format($row->debits, 2, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($row->credits, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

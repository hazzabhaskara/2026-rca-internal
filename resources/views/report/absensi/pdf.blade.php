<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rekap Absensi</title>
    <style>
        body {
            font-family: sans-serif;
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
            padding: 6px;
        }

        th {
            background: #f0f0f0;
        }
    </style>
</head>

<body>
    <h3>Rekap Absensi</h3>
    <p>Periode: {{ $startDate }} s/d {{ $endDate }} ({{ ucfirst($filterType) }})</p>
    <p>Unit: {{ $unitName }}</p>

    <table>
        <thead>
            <tr>
                <th>Pegawai</th>
                <th>Level</th>
                <th>Unit</th>
                <th>Total Hari Kerja</th>
                <th>Total Jam Lembur</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rekap as $r)
                <tr>
                    <td>{{ $r['pegawai'] }}</td>
                    <td>{{ $r['level'] }}</td>
                    <td>{{ $r['unit'] }}</td>
                    <td>{{ $r['total_hari'] }}</td>
                    <td>{{ $r['total_lembur'] }} Jam</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

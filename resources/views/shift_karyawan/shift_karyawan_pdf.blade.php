<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Shift Karyawan Wahana</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            background: #ffeb3b;
            padding: 6px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 6px;
            text-align: center;
        }

        th {
            background-color: #f4b084;
        }

        .off {
            background-color: #fff2cc;
            text-align: center;
            padding: 6px;
            font-weight: bold;
        }

        .tanggal-section {
            page-break-after: always;
        }
    </style>
</head>

<body>

    @foreach ($dataPerTanggal as $tanggal => $items)
        <div class="tanggal-section">
            <h2>SHIFT KARYAWAN WAHANA - {{ strtoupper(\Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y')) }}
            </h2>

            <table>
                <thead>
                    <tr>
                        <th>Wahana</th>
                        @foreach ($crewShift as $shift)
                            <th>{{ $shift->nama }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items->groupBy(fn($s) => optional($s->wahana)->nama_wahana ?? 'Tanpa Wahana') as $wahana => $dataWahana)
                        <tr>
                            <td>{{ $wahana }}</td>
                            @foreach ($dataWahana->sortBy('crew_id') as $shift)
                                <td>{{ $shift->karyawan->nama_karyawan ?? '-' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @php
                $offEmployees = $offPerTanggal[$tanggal]->pluck('employee.nama_karyawan')->toArray() ?? [];
            @endphp

            <div class="off">
                OFF: {{ count($offEmployees) ? implode(', ', $offEmployees) : '-' }}
            </div>
        </div>
    @endforeach

</body>

</html>

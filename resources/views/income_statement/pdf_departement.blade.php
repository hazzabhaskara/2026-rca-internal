<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi per Departemen</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        h2 {
            margin: 0;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 10px;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 5px;
        }

        th {
            background: #f0f0f0;
            text-align: center;
        }

        td.text-right {
            text-align: right;
        }

        td.text-left {
            text-align: left;
        }

        .section-title {
            font-weight: bold;
            background: #ddd;
            text-align: left;
        }

        .total-row {
            font-weight: bold;
            background: #eee;
        }

        .final {
            font-weight: bold;
            font-size: 12px;
            background: #ddd;
        }

        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
        }

        .pagenum:before {
            content: counter(page);
        }
    </style>
</head>

<body>
    <h2>LAPORAN LABA RUGI PER DEPARTEMEN</h2>
    <div class="subtitle">
        Periode: {{ \Carbon\Carbon::parse($start_date)->translatedFormat('d M Y') }}
        s/d {{ \Carbon\Carbon::parse($end_date)->translatedFormat('d M Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                @foreach ($departemens as $dept)
                    <th>{{ $dept->deskripsi }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            {{-- =======================
                 Pendapatan
            ======================== --}}
            <tr>
                <td colspan="{{ 3 + count($departemens) }}" class="section-title">PENDAPATAN</td>
            </tr>
            @foreach ($incomeStatement as $row)
                @if (strtolower($row['tipe_akun']) === 'pendapatan')
                    <tr>
                        <td class="text-left">{{ $row['kode_akun'] }}</td>
                        <td class="text-left">{{ $row['nama_akun'] }}</td>
                        @foreach ($departemens as $dept)
                            <td class="text-right">
                                {{ number_format($row['per_departemen'][$dept->deskripsi] ?? 0, 2, ',', '.') }}
                            </td>
                        @endforeach
                        <td class="text-right">{{ number_format($row['saldo'], 2, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="total-row">
                <td colspan="{{ 2 + count($departemens) }}">TOTAL PENDAPATAN</td>
                <td class="text-right">{{ number_format($totalPendapatan, 2, ',', '.') }}</td>
            </tr>

            {{-- =======================
                 Beban
            ======================== --}}
            <tr>
                <td colspan="{{ 3 + count($departemens) }}" class="section-title">BEBAN</td>
            </tr>
            @foreach ($incomeStatement as $row)
                @if (strtolower($row['tipe_akun']) === 'beban')
                    <tr>
                        <td class="text-left">{{ $row['kode_akun'] }}</td>
                        <td class="text-left">{{ $row['nama_akun'] }}</td>
                        @foreach ($departemens as $dept)
                            <td class="text-right">
                                {{ number_format($row['per_departemen'][$dept->deskripsi] ?? 0, 2, ',', '.') }}
                            </td>
                        @endforeach
                        <td class="text-right">{{ number_format($row['saldo'], 2, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="total-row">
                <td colspan="{{ 2 + count($departemens) }}">TOTAL BEBAN</td>
                <td class="text-right">{{ number_format($totalBeban, 2, ',', '.') }}</td>
            </tr>

            {{-- =======================
                 Laba Bersih
            ======================== --}}
            <tr class="final">
                <td colspan="{{ 2 + count($departemens) }}">LABA BERSIH</td>
                <td class="text-right">{{ number_format($labaBersih, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dicetak: {{ now()->format('d/m/Y H:i') }} — Halaman <span class="pagenum"></span>
    </div>
</body>

</html>

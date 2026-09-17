<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Slip Gaji</title>
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
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <h3>Slip Gaji</h3>
    <p><strong>Nama:</strong> {{ $pembayaran->employee->nama_karyawan }}</p>
    <p><strong>Periode:</strong> {{ $pembayaran->periode_awal }} s/d {{ $pembayaran->periode_akhir }}</p>
    <p><strong>Tanggal Pembayaran:</strong> {{ $pembayaran->tanggal_pembayaran }}</p>

    <table>
        <thead>
            <tr>
                <th>Komponen</th>
                <th class="text-right">Nilai</th>
                <th class="text-right">Potongan</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach ($pembayaran->details as $detail)
                @php
                    $subtotal = ($detail->nilai - $detail->potongan) * $detail->jumlah_hari;
                    $grandTotal += $subtotal;
                @endphp
                <tr>
                    <td>{{ $detail->komponen->nama_komponen }}</td>
                    <td class="text-right">{{ number_format($detail->nilai, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->potongan, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="3">Total Gaji</th>
                <th class="text-right">{{ number_format($grandTotal, 0, ',', '.') }}</th>
            </tr>
        </tbody>
    </table>
</body>

</html>

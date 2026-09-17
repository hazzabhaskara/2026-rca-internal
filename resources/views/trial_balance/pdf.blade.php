{{-- resources/views/neraca/export_pdf.blade.php --}}
<html>

<head>
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .pagenum:before {
            content: counter(page);
        }
    </style>
</head>

<body>
    <div style="text-align: center; margin-bottom: 20px;">
        <h2>Trial Balance</h2>
        <p>Periode sampai {{ \Carbon\Carbon::parse($end_date)->translatedFormat('d F Y') }}</p>
    </div>

    <table width="100%" border="1" cellspacing="0" cellpadding="5">
        <thead>
            <tr>
                <th>Account</th>
                <th>Debet</th>
                <th>Kredit</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDebet = 0;
                $totalKredit = 0;
            @endphp
            @foreach ($trialBalances as $row)
                @php
                    $debet = $row['saldo_debit'];
                    $kredit = $row['saldo_kredit'];
                    $totalDebet += $debet;
                    $totalKredit += $kredit;
                @endphp
                <tr>
                    <td>{{ $row['kode_akun'] }} - {{ $row['nama_akun'] }}</td>
                    <td align="right">{{ number_format($debet, 2) }}</td>
                    <td align="right">{{ number_format($kredit, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-semibold bg-gray-200">
                <td colspan="1" class="border px-3 py-2 text-right">Total</td>
                <td align="right" class="border px-3 py-2">{{ number_format($totalDebet, 2) }}</td>
                <td align="right" class="border px-3 py-2">{{ number_format($totalKredit, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Footer nomor halaman --}}
    {{-- <div style="position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px;">
        Halaman <span class="pagenum"></span>
    </div> --}}
</body>

</html>

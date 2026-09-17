<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Closing Harian Print</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 20px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px auto;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            border: 1px solid black;
            padding: 6px 8px;
            text-align: center;
        }

        thead th {
            background-color: #f5f5f5;
        }

        tbody td {
            text-align: right;
        }

        tbody td:first-child {
            text-align: left;
        }

        tfoot td {
            font-weight: bold;
        }

        tfoot .subtotal td,
        tfoot tr.subtotal td {
            background-color: rgb(144, 142, 142);
            color: #000;
            text-align: right;
        }

        tfoot .total td,
        tfoot tr.total td {
            background-color: rgb(144, 142, 142);
            color: #000;
            text-align: right;
        }

        .mdr td {
            text-align: left;
        }

        /* Tabel tanda tangan */
        .signature-table {
            width: 100%;
            margin: 40px auto;
            border-collapse: collapse;
            text-align: center;
        }

        .signature-table th,
        .signature-table td {
            border: 1px solid black;
            padding: 8px;
        }

        .signature-table td {
            vertical-align: bottom;
            height: 100px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            width: 80%;
            margin: 0 auto;
            height: 30px;
        }

        .signature-label {
            margin-top: 5px;
        }

        thead .header-kolektor tr {
            background-color: yellow;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 10mm;
            }
        }
    </style>
</head>

<body>

    @if (!isset($isPdf))
        <div class="no-print" style="text-align:right; margin-bottom:10px;">
            <button onclick="window.print()"
                style="background:#007bff; color:white; border:none; padding:6px 12px; border-radius:4px;">
                Print
            </button>
            <button onclick="window.close()"
                style="background:#6c757d; color:white; border:none; padding:6px 12px; border-radius:4px;">
                Close
            </button>
        </div>
    @endif
    <div class="tabel-header">
        <table>
            <thead>
                <tr>
                    <th colspan="2" rowspan="2">{{ $data->unitKerja->nama_unit }}</th>
                </tr>
                <tr>
                    @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                        <th colspan="2">TOTAL OMSET DITERIMA</th>
                    @else
                        <th>TOTAL OMSET DITERIMA</th>
                    @endif
                    <th>(.............)</th>
                </tr>
                <tr class="header-kolektor">
                    <th colspan="2"></th>
                    @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                        <th colspan="2">NILAI</th>
                    @else
                        <th>NILAI</th>
                    @endif
                    <th>KOLEKTOR</th>
                </tr>
                <tr>
                    <th>WAHANA</th>
                    <th>TOTAL OMSET</th>
                    <th>MERCHANDISE</th>
                    @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                        <th>{{ $data->unitKerja->nama_unit }}</th>
                    @endif
                    <th>RCA</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $items = $data->details->pluck('wahanaItem.nama_item')->unique();
                @endphp

                @foreach ($data->details->groupBy('wahanaItem.wahana_id') as $wahanaId => $group)
                    @foreach ($items as $itemName)
                        @php
                            $detail = $group->firstWhere('wahanaItem.nama_item', $itemName);
                        @endphp
                    @endforeach
                    <tr>
                        <td>{{ $group->first()->wahanaItem->wahana->nama_wahana ?? '-' }}</td>
                        <td>{{ number_format($detail->omset_total) }}</td>
                        <td>{{ number_format($detail->merch) }}</td>
                        @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                            <td>{{ number_format($detail->titipan) }}</td>
                        @endif
                        <td>{{ number_format($detail->rca) }}</td>
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                @php
                    $subtotalOmset = 0;
                    $subtotalCash = 0;
                    $subtotalQris = 0;
                    $subtotalMerch = 0;
                    $subtotalRca = 0;
                    $subtotalTitipan = 0;
                    $subtotalLebihKurang = 0;

                    foreach ($data->details->groupBy('wahanaItem.wahana_id') as $group) {
                        $detail = $group->first();
                        $subtotalOmset += $detail->omset_total ?? 0;
                        $subtotalCash += $detail->cash ?? 0;
                        $subtotalQris += $detail->qris ?? 0;
                        $subtotalMerch += $detail->merch ?? 0;
                        $subtotalRca += $detail->rca ?? 0;
                        $subtotalTitipan += $detail->titipan ?? 0;
                        $subtotalLebihKurang += $detail->lebih_kurang ?? 0;
                    }

                    $totalTitipan = $subtotalTitipan;
                    $mdrAmount = $subtotalTitipan * 0.007;
                    $subtotalAfterMdr = $totalTitipan - $mdrAmount;
                @endphp

                @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                    <tr class="subtotal">
                        <td style="text-align: left;">SUBTOTAL</td>
                        <td>{{ number_format($subtotalOmset) }}</td>
                        <td>{{ number_format($subtotalMerch) }}</td>
                        <td>{{ number_format($subtotalTitipan) }}</td>
                        <td>{{ number_format($subtotalRca) }}</td>
                    </tr>

                    <tr>
                        <td style="text-align: left">DIKURANGI:</td>
                        <td colspan="4"></td>
                    </tr>

                    <tr class="mdr">
                        <td>MDR 0,7%</td>
                        <td></td>
                        <td style="text-align: left;">Rp</td>
                        <td style="text-align: right;">{{ number_format($mdrAmount) }}</td>
                        <td>Rp</td>
                    </tr>

                    <tr class="mdr">
                        <td>SUB TOTAL MDR 0,7%</td>
                        <td></td>
                        <td style="text-align: left;">Rp</td>
                        <td style="text-align: right;">{{ number_format($mdrAmount) }}</td>
                        <td>Rp</td>
                    </tr>
                @endif

                <tr class="total">
                    <td style="text-align: left;">TOTAL</td>
                    <td>{{ number_format($subtotalOmset) }}</td>
                    <td>{{ number_format($subtotalMerch) }}</td>
                    @if ($data->unitKerja->format_closing === 2 || $data->unitKerja->format_closing === 4)
                        <td>{{ number_format($subtotalAfterMdr) }}</td>
                    @endif
                    <td>{{ number_format($subtotalRca) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Tabel tanda tangan -->
        <table class="signature-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 150px;">TANGGAL</th>
                    <th rowspan="2" style="width: 150px;">{{ $data->tanggal }}</th>
                    <th colspan="2">MENGETAHUI</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight: bold;">TOTAL OMSET</td>
                    <td>{{ number_format($subtotalOmset) }}</td>

                    <td>
                        <div class="signature-line"></div>
                        <div class="signature-label">MERCHANDISE</div>
                    </td>

                    <td>
                        <div class="signature-line"></div>
                        <div class="signature-label">RCA</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</body>

</html>

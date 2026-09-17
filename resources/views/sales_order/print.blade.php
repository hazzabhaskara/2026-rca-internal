<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Sales Order - {{ $salesOrder->order_number }}</title>

    <!-- Font modern -->
    <style>
        body {
            color: #333;
            margin: 40px;
            background: #fff;
        }

        /* Header Perusahaan */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .company-info .logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }

        .company-info h1 {
            font-size: 20px;
            margin: 0;
            color: #007bff;
        }

        .company-info p {
            margin: 2px 0;
            font-size: 12px;
        }

        .doc-info {
            text-align: right;
        }

        .doc-info h2 {
            margin: 0;
            font-size: 22px;
            color: #007bff;
        }

        .doc-info p {
            margin: 2px 0;
            font-size: 13px;
        }

        /* Info Vendor & Shipping */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .info-box {
            width: 48%;
            background: #f9fafc;
            padding: 10px 15px;
            border-radius: 5px;
        }

        .info-box h3 {
            margin-top: 0;
            margin-bottom: 5px;
            color: #007bff;
            font-size: 15px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 3px;
        }

        .info-box p {
            margin: 3px 0;
            font-size: 13px;
        }

        /* Tabel Item */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        th {
            background: #007bff;
            color: #fff;
            font-size: 13px;
        }

        td,
        th {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
            font-size: 13px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #eef4ff;
        }

        .text-right {
            text-align: right;
        }

        /* Summary Section */
        .summary td {
            border: none;
            padding: 5px 8px;
        }

        .summary tr:last-child td {
            background: #007bff;
            color: #fff;
            font-weight: bold;
        }

        /* Tanda Tangan */
        .signature {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }

        .signature div {
            text-align: center;
            width: 45%;
        }

        .signature p {
            margin: 5px 0;
            font-size: 13px;
        }

        /* Note & Footer */
        .note {
            margin-top: 30px;
            font-style: italic;
            color: #666;
            font-size: 13px;
        }

        .footer {
            text-align: center;
            margin-top: 60px;
            font-size: 11px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        /* Cetak */
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
    <!-- Tombol non-print -->
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

    @php
        $title = \App\Setting::get('site_title', '-');
    @endphp
    <!-- Header -->
    <div class="header">
        <div class="company-info">
            @if (isset($isPdf) && $isPdf)
                {{-- Mode PDF --}}
                <img src="{{ public_path('storage/' . \App\Setting::get('logo', 'logo.jpg')) }}" alt="Logo"
                    class="logo">
            @else
                {{-- Mode browser (print langsung) --}}
                <img src="{{ asset('storage/' . \App\Setting::get('logo', 'logo.jpg')) }}" alt="Logo"
                    class="logo">
            @endif

            <div>
                <h1>{{ $title }}</h1>
                <p>{{ $companyProfile->jalan ?? '-' }}</p>
                <p>Telp: {{ $companyProfile->phone_number ?? '-' }} | Email: {{ $companyProfile->email ?? '-' }}</p>
            </div>
        </div>
        <div class="doc-info">
            <h2>Sales ORDER</h2>
            <p><strong>No:</strong> {{ $salesOrder->order_number }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($salesOrder->date_order)->format('d M Y') }}</p>
        </div>
    </div>

    <!-- Vendor & Shipping -->
    <div class="info-section">
        <div class="info-box">
            <h3>Customer Information</h3>
            <p><strong>{{ $salesOrder->customer->nama_customers ?? '-' }}</strong></p>
            <p>{{ $salesOrder->customer->alamat ?? '-' }}</p>
            <p>{{ $salesOrder->customer->telepon ?? '-' }}</p>
        </div>
        <div class="info-box">
            <h3>Shipping Information</h3>
            <p><strong>Shipping Date:</strong>
                {{ \Carbon\Carbon::parse($salesOrder->shipping_date)->format('d M Y') }}
            </p>
            <p><strong>Shipping Address:</strong> {{ $salesOrder->shipping_address ?? '-' }}</p>
        </div>
    </div>

    <!-- Tabel Detail -->
    <table class="table-auto w-full border-collapse border border-gray-200 text-sm">
        <thead class="bg-gray-100 text-gray-700">
            <tr>
                <th class="border px-3 py-2">Item</th>
                <th class="border px-3 py-2">Order</th>
                <th class="border px-3 py-2">Unit</th>
                <th class="border px-3 py-2">Description</th>
                <th class="border px-3 py-2">Base Price</th>
                <th class="border px-3 py-2">Discount</th>
                <th class="border px-3 py-2">Price</th>
                <th class="border px-3 py-2">Tax</th>
                <th class="border px-3 py-2">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($salesOrder->details as $item)
                <tr>
                    <td class="border px-3 py-2">{{ $item->item->item_description ?? '-' }}</td>
                    <td class="border px-3 py-2 text-center">{{ $item->order }}</td>
                    <td class="border px-3 py-2 text-center">{{ $item->unit }}</td>
                    <td class="border px-3 py-2">{{ $item->item_description }}</td>
                    <td class="border px-3 py-2 text-right">{{ number_format($item->base_price) }}
                    </td>
                    <td class="border px-3 py-2 text-right">{{ number_format($item->discount) }}
                    </td>
                    <td class="border px-3 py-2 text-right">{{ number_format($item->price) }}</td>
                    <td class="border px-3 py-2 text-right">{{ number_format($item->tax) }}</td>
                    <td class="border px-3 py-2 text-right">{{ number_format($item->amount) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                // Inisialisasi
                $subtotal = 0;
                $totalInputTax = 0; // PPN (+)
                $totalWithholding = 0; // PPh (+ di sini, tapi akan dikurangkan saat net)
            @endphp

            @foreach ($salesOrder->details as $item)
                @php
                    // Jumlahkan subtotal (sebelum pajak)
                    $subtotal += (float) ($item->amount ?? 0);

                    // Ambil tipe pajak dari relasi yang BENAR (ganti 'sales_taxes' sesuai nama relasi di model)
                    $taxType = optional($item->sales_taxes)->type; // 'input_tax' | 'withholding_tax' | null
                    $taxAmt = (float) ($item->tax ?? 0); // pastikan selalu disimpan positif di DB

                    if ($taxType === 'input_tax') {
                        $totalInputTax += $taxAmt; // PPN ditambah
                    } elseif ($taxType === 'withholding_tax') {
                        $totalWithholding += $taxAmt; // PPh diakumulasi terpisah (positif di sini)
                    }
                @endphp
            @endforeach

            @php
                $freight = (float) ($salesOrder->freight ?? 0);
                $totalTaxNet = $totalInputTax - $totalWithholding; // PPN (+) dikurangi PPh (–)
                $grandTotal = $subtotal + $totalTaxNet + $freight;
            @endphp@php
                // Inisialisasi
                $subtotal = 0;
                $totalInputTax = 0; // PPN (+)
                $totalWithholding = 0; // PPh (+ di sini, tapi akan dikurangkan saat net)
            @endphp

            @foreach ($salesOrder->details as $item)
                @php
                    // Jumlahkan subtotal (sebelum pajak)
                    $subtotal += (float) ($item->amount ?? 0);

                    // Ambil tipe pajak dari relasi yang BENAR (ganti 'sales_taxes' sesuai nama relasi di model)
                    $taxType = optional($item->sales_taxes)->type; // 'input_tax' | 'withholding_tax' | null
                    $taxAmt = (float) ($item->tax ?? 0); // pastikan selalu disimpan positif di DB

                    if ($taxType === 'input_tax') {
                        $totalInputTax += $taxAmt; // PPN ditambah
                    } elseif ($taxType === 'withholding_tax') {
                        $totalWithholding += $taxAmt; // PPh diakumulasi terpisah (positif di sini)
                    }
                @endphp
            @endforeach

            @php
                $freight = (float) ($salesOrder->freight ?? 0);
                $totalTaxNet = $totalInputTax - $totalWithholding; // PPN (+) dikurangi PPh (–)
                $grandTotal = $subtotal + $totalTaxNet + $freight;
            @endphp

            <tr>
                <td colspan="8" class="border px-3 py-2 text-right font-bold">Subtotal</td>
                <td class="border px-3 py-2 text-right font-bold">{{ number_format($subtotal) }}
                </td>
            </tr>
            <tr>
                <td colspan="8" class="border px-3 py-2 text-right font-bold">Total Pajak</td>
                <td class="border px-3 py-2 text-right font-bold">
                    {{ number_format($totalTaxNet) }}
                </td>
            </tr>
            <tr>
                <td colspan="8" class="border px-3 py-2 text-right font-bold">Freight</td>
                <td class="border px-3 py-2 text-right font-bold">{{ number_format($freight) }}
                </td>
            </tr>
            <tr>
                <td colspan="8" class="border px-3 py-2 text-right font-bold">Grand Total</td>
                <td class="border px-3 py-2 text-right font-bold">{{ number_format($grandTotal) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Note & Signature -->
    <p class="note"><strong>Note:</strong> {{ $salesOrder->messages ?? '-' }}</p>

    {{-- <div class="signature">
        <div>
            <p><strong>Prepared By:</strong></p>
            <br><br>
            <p>______________________</p>
            <p>{{ auth()->user()->name ?? 'Admin' }}</p>
        </div>
        <div>
            <p><strong>Approved By:</strong></p>
            <br><br>
            <p>______________________</p>
            <p>Manager</p>
        </div>
    </div> --}}
    {{-- 
    <div class="footer">
        This Sales Order was generated by {{ config('app.name') }} ERP System on {{ now()->format('d M Y H:i') }}
    </div> --}}

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>

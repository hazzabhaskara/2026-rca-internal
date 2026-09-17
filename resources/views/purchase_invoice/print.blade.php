<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Purchase Invoice - {{ $purchaseInvoice->invoice_number }}</title>

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
            <h2>PURCHASE INVOICE</h2>
            <p><strong>No:</strong> {{ $purchaseInvoice->invoice_number }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($purchaseInvoice->date_invoice)->format('d M Y') }}</p>
        </div>
    </div>

    <!-- Vendor & Shipping -->
    <div class="info-section">
        <div class="info-box">
            <h3>Vendor Information</h3>
            <p><strong>{{ $purchaseInvoice->vendor->nama_vendors ?? '-' }}</strong></p>
            <p>{{ $purchaseInvoice->vendor->alamat ?? '-' }}</p>
            <p>{{ $purchaseInvoice->vendor->telepon ?? '-' }}</p>
        </div>
        <div class="info-box">
            <h3>Shipping Information</h3>
            <p><strong>Shipping Date:</strong>
                {{ \Carbon\Carbon::parse($purchaseInvoice->shipping_date)->format('d M Y') }}
            </p>
            <p><strong>Shipping Address:</strong> {{ $purchaseInvoice->shipping_address ?? '-' }}</p>
        </div>
    </div>

    <!-- Tabel Detail -->
    <table>
        <tr>
            <th>Item</th>
            <th>Deskripsi</th>
            <th>Qty</th>
            <th>Order</th>
            <th>Back Order</th>
            <th>Unit</th>
            <th>Harga</th>
            <th>Discount</th>
            <th>Tax</th>
            <th>Total</th>
        </tr>

        @php
            $total_tax = 0;
            $amount = 0;
            $subtotal = 0;
        $total = 0; @endphp
        @foreach ($purchaseInvoice->details as $item)
            @php
                $amount = ($item->price - $item->discount) * $item->quantity;
                $total_tax += $item->tax_amount;
                $subtotal += $amount;
                $total = $subtotal + $total_tax + $purchaseInvoice->freight;
            @endphp
            <tr>
                <td>{{ $item->item->item_number }}</td>
                <td>{{ $item->item_description }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->order }}</td>
                <td>{{ $item->order }}</td>
                <td>{{ $item->back_order }}</td>
                <td class="text-right">{{ number_format($item->price, 2) }}</td>
                <td class="text-right">{{ number_format($item->discount, 2) }}</td>
                <td class="text-right">{{ number_format($item->tax_amount, 2) }}</td>
                <td class="text-right">{{ number_format($item->amount, 2) }}</td>
            </tr>
        @endforeach
        <tr class="summary">
            <td colspan="8"></td>
            <td><strong>Subtotal</strong></td>
            <td class="text-right">{{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr class="summary">
            <td colspan="8"></td>
            <td><strong>Tax</strong></td>
            <td class="text-right">{{ number_format($total_tax, 2) }}</td>
        </tr>
        <tr class="summary">
            <td colspan="8"></td>
            <td><strong>Freight</strong></td>
            <td class="text-right">{{ number_format($purchaseInvoice->freight, 2) }}</td>
        </tr>
        <tr class="summary">
            <td colspan="8"></td>
            <td><strong>Grand Total</strong></td>
            <td class="text-right">{{ number_format($total, 2) }}</td>
        </tr>
    </table>

    <!-- Note & Signature -->
    <p class="note"><strong>Note:</strong> {{ $purchaseInvoice->messages ?? '-' }}</p>

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
        This Purchase Order was generated by {{ config('app.name') }} ERP System on {{ now()->format('d M Y H:i') }}
    </div> --}}

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>

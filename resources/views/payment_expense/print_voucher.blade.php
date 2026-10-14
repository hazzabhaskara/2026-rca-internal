@php
    $accCode = optional($data->Account)->kode_akun ?? '';
    $accName = strtoupper(optional($data->Account)->nama_akun ?? '');
    $isBank = (substr($accCode, 0, 4) === '1102') || (strpos($accName, 'BANK') !== false) || (strpos($accName, 'BCA') !== false) || (strpos($accName, 'BNI') !== false) || (strpos($accName, 'BRI') !== false) || (strpos($accName, 'MANDIRI') !== false);
    $docTitle = $isBank ? 'Bukti Bank Keluar (BBK)' : 'Bukti Kas Keluar (BKK)';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $docTitle }} - {{ $data->voucher_number ?: 'EXP#' . $data->id }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
        }

        body {
            background-color: #f1f5f9;
            padding: 24px;
            font-size: 12px;
        }

        .no-print-bar {
            max-width: 800px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-print {
            background-color: #059669;
            color: #ffffff;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-print:hover {
            background-color: #047857;
        }

        .btn-back {
            background-color: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .btn-back:hover {
            background-color: #f8fafc;
        }

        .voucher-card {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 32px;
        }

        /* HEADER VOUCHER */
        .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 16px;
            margin-bottom: 16px;
        }

        .company-info h1 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .company-info p {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }

        .voucher-title-box {
            text-align: right;
        }

        .voucher-title-box h2 {
            font-size: 16px;
            font-weight: 800;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .voucher-badge {
            display: inline-block;
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 6px;
            margin-top: 4px;
        }

        /* METADATA MATRIX */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .meta-item {
            display: flex;
            margin-bottom: 4px;
        }

        .meta-label {
            width: 130px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            font-size: 10px;
        }

        .meta-value {
            flex: 1;
            font-weight: 700;
            color: #0f172a;
        }

        /* TABLE GRID SAGE 50 */
        .expense-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 11px;
        }

        .expense-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            color: #334155;
            text-align: left;
        }

        .expense-table td {
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            color: #1e293b;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .font-bold { font-weight: 700; }

        .expense-table tfoot td {
            background-color: #f8fafc;
            font-weight: 700;
            border: 1px solid #cbd5e1;
        }

        /* TERBILANG BOX */
        .terbilang-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .terbilang-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 2px;
        }

        .terbilang-text {
            font-weight: 700;
            font-style: italic;
            color: #065f46;
        }

        /* NOTES */
        .notes-section {
            margin-bottom: 24px;
            font-size: 11px;
        }

        .notes-section strong {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
        }

        .notes-content {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            color: #334155;
            min-height: 40px;
        }

        /* SIGNATURE BOXES (4-KOLOM SAGE 50) */
        .signature-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 16px;
        }

        .sig-box {
            text-align: center;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 6px;
            background-color: #fafafa;
        }

        .sig-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 45px;
        }

        .sig-name {
            font-size: 10px;
            font-weight: 700;
            border-top: 1px dashed #94a3b8;
            padding-top: 4px;
            margin: 0 4px;
        }

        .sig-sub {
            font-size: 8px;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* ATTACHMENT FOOTNOTE */
        .doc-badge {
            margin-top: 16px;
            font-size: 9px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }

            .no-print-bar {
                display: none !important;
            }

            .voucher-card {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }

            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>

    <!-- Tombol Aksi di Layar (Tersembunyi saat Print) -->
    <div class="no-print-bar">
        <button type="button" onclick="window.print()" class="btn btn-print">
            <i class="fa-solid fa-print"></i> Cetak Bon / Print Voucher
        </button>

        <a href="{{ route('payment_expense.show', $data->id) }}" class="btn btn-back">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Rincian
        </a>
    </div>

    <!-- SAGE 50 PAYMENT VOUCHER SHEET -->
    <div class="voucher-card">

        <!-- HEADER PERUSAHAAN & JUDUL -->
        <div class="voucher-header">
            <div class="company-info">
                <h1>PT REMBULAN CITRA ABADI</h1>
                <p>Accounting & Finance Disbursement Division</p>
                <p>Sistem Operasional Keuangan Terpadu</p>
            </div>
            <div class="voucher-title-box">
                <h2>{{ $docTitle }}</h2>
                <div class="voucher-badge">
                    {{ $data->voucher_number ?: 'EXP#' . $data->id }}
                </div>
            </div>
        </div>

        <!-- MATRIX INFORMASI VOUCHER -->
        <div class="meta-grid">
            <div>
                <div class="meta-item">
                    <span class="meta-label">Tanggal Transaksi:</span>
                    <span class="meta-value">{{ \Carbon\Carbon::parse($data->date)->format('d F Y') }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Divisi / Dept:</span>
                    <span class="meta-value">{{ $data->departemen->deskripsi ?? 'PUSAT BANDUNG' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Sumber Kas:</span>
                    <span class="meta-value">{{ $data->category_source ?: $data->source }}</span>
                </div>
            </div>

            <div>
                <div class="meta-item">
                    <span class="meta-label">Dibayar Dari:</span>
                    <span class="meta-value font-mono">
                        [{{ $data->Account->kode_akun ?? '-' }}] {{ $data->Account->nama_akun ?? '-' }}
                    </span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Kepada / Vendor:</span>
                    <span class="meta-value">{{ $data->Vendor->nama_vendors ?? 'Non-Vendor / Internal' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Pemohon (User):</span>
                    <span class="meta-value">{{ $data->user->name ?? 'Kasir / Staff' }}</span>
                </div>
            </div>
        </div>

        <!-- TABEL RINCIAN PENGELUARAN BEBAN -->
        <table class="expense-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 30px;">No</th>
                    <th style="width: 100px;">Kode Akun</th>
                    <th style="width: 220px;">Akun Beban (GL Account)</th>
                    <th>Uraian / Rincian Pengeluaran</th>
                    <th class="text-right" style="width: 110px;">Nominal (Rp)</th>
                    <th class="text-center" style="width: 60px;">Pajak</th>
                    <th class="text-right" style="width: 110px;">Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data->details as $item)
                    @php
                        $taxRate = $item->salesTaxes->rate ?? 0;
                        $lineTotal = $item->amount + ($item->amount * ($taxRate / 100));
                    @endphp
                    <tr>
                        <td class="text-center font-mono">{{ $loop->iteration }}</td>
                        <td class="font-mono font-bold">{{ $item->Account->kode_akun ?? '-' }}</td>
                        <td>{{ $item->Account->nama_akun ?? '-' }}</td>
                        <td>{{ $item->deskripsi ?: '-' }}</td>
                        <td class="text-right font-mono">{{ number_format($item->amount, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $taxRate > 0 ? $taxRate . '%' : '-' }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format($lineTotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">SUBTOTAL BEBAN:</td>
                    <td class="text-right font-mono">{{ number_format($data->subtotal, 0, ',', '.') }}</td>
                    <td class="text-center font-mono">{{ number_format($data->total_tax, 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-bold" style="font-size: 13px; color: #065f46;">
                        Rp {{ number_format($data->grand_total, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- TERBILANG RUPIAH -->
        <div class="terbilang-box">
            <div class="terbilang-label">Terbilang (Amount in Words):</div>
            <div class="terbilang-text"># {{ $data->terbilang }} #</div>
        </div>

        <!-- CATATAN / MEMO -->
        <div class="notes-section">
            <strong>Catatan / Memo:</strong>
            <div class="notes-content">
                {{ $data->notes ?: 'Tidak ada catatan tambahan.' }}
            </div>
        </div>

        <!-- 4 KOTAK TANDA TANGAN RESMI (STANDAR AUDIT SAGE 50) -->
        <div class="signature-grid">
            <div class="sig-box">
                <div class="sig-title">Dibuat Oleh (Pemohon)</div>
                <div class="sig-name">{{ $data->user->name ?? 'Staf Divisi' }}</div>
                <div class="sig-sub">Staff / Kasir Divisi</div>
            </div>

            <div class="sig-box">
                <div class="sig-title">Disetujui Oleh</div>
                <div class="sig-name">( .................................... )</div>
                <div class="sig-sub">Kepala Divisi / SPV</div>
            </div>

            <div class="sig-box">
                <div class="sig-title">Diperiksa / Verifikasi</div>
                <div class="sig-name">( .................................... )</div>
                <div class="sig-sub">Accounting / Finance</div>
            </div>

            <div class="sig-box">
                <div class="sig-title">Diterima Oleh</div>
                <div class="sig-name">{{ $data->Vendor->nama_vendors ?? '( .................................... )' }}</div>
                <div class="sig-sub">Penerima Dana</div>
            </div>
        </div>

        <!-- STATUS LAMPIRAN DOKUMEN -->
        <div class="doc-badge">
            <i class="fa-solid fa-paperclip"></i>
            <span>
                Status Dokumen Fisik: 
                <strong>{{ $data->attachment ? 'Lampiran Bon/Struk Tersedia (' . basename($data->attachment) . ')' : 'Tidak Ada Dokumen Fisik' }}</strong>
            </span>
            &bull;
            <span>Dicetak pada: {{ date('d/m/Y H:i') }}</span>
        </div>

    </div>

</body>
</html>

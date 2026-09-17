<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Aktivitas Karyawan</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        .header {
            background-color: #4F46E5;
            color: #fff;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 5px solid #312E81;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .header p {
            margin: 8px 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .info-section {
            margin: 0 20px 20px;
            padding: 15px;
            background-color: #f3f4f6;
            border-radius: 6px;
            border-left: 5px solid #4F46E5;
            line-height: 1.6;
        }
        .info-section p {
            margin: 3px 0;
            font-size: 13px;
        }
        table {
            width: calc(100% - 40px);
            border-collapse: collapse;
            margin: 0 20px 20px;
            background-color: #fff;
        }
        table, th, td {
            border: 1px solid #e5e7eb;
        }
        th {
            background-color: #4F46E5;
            color: #ffffff;
            text-align: left;
            padding: 12px;
            font-size: 12px;
            text-transform: uppercase;
        }
        td {
            padding: 10px 12px;
            vertical-align: top;
            font-size: 12px;
        }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .activity-text {
            color: #3730a3;
            font-weight: 600;
        }
        .footer {
            position: fixed;
            bottom: -20px;
            left: 20px;
            right: 20px;
            height: 30px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Aktivitas Karyawan</h1>
        <p>Log Sistem Internal &bull; Rembulan Citra Abadi</p>
    </div>

    <div class="info-section">
        <table style="width: 100%; border: none; margin: 0; background: transparent;">
            <tr style="background: transparent;">
                <td style="border: none; padding: 0;">
                    <p><strong>Dicetak pada:</strong> {{ now()->format('d M Y, H:i:s') }}</p>
                    <p><strong>Oleh:</strong> {{ auth()->user()->name ?? 'System' }}</p>
                </td>
                <td style="border: none; padding: 0; text-align: right;">
                    @if(request('start_date') || request('end_date'))
                    <p><strong>Periode:</strong> {{ request('start_date') ? date('d M Y', strtotime(request('start_date'))) : 'Awal' }} s/d {{ request('end_date') ? date('d M Y', strtotime(request('end_date'))) : 'Sekarang' }}</p>
                    @else
                    <p><strong>Periode:</strong> Keseluruhan Data</p>
                    @endif
                    <p><strong>Total Record:</strong> {{ count($data) }} entri</p>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%" style="text-align: center;">#</th>
                <th width="18%">User</th>
                <th width="22%">Activity</th>
                <th width="15%">IP Address</th>
                <th width="25%">User Agent</th>
                <th width="15%">Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td><strong>{{ optional($item->user)->name ?? '-' }}</strong></td>
                    <td class="activity-text">{{ $item->activity ?? '-' }}</td>
                    <td>{{ $item->ip_address ?? '-' }}</td>
                    <td style="font-size: 10px; color: #6b7280; word-break: break-all;">
                        {{ \Illuminate\Support\Str::limit($item->user_agent ?? '-', 60) }}
                    </td>
                    <td>{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: #6b7280;">Belum ada aktivitas yang tercatat pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated automatically by Ujitest ERP System &copy; {{ date('Y') }} Rembulan Citra Abadi. All rights reserved.
    </div>

</body>
</html>

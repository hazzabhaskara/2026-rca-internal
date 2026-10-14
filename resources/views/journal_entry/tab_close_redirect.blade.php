<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menyimpan Perubahan...</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            color: #334155;
        }
        .card {
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 420px;
            width: 90%;
        }
        .icon {
            font-size: 2.5rem;
            color: #10b981;
            margin-bottom: 0.75rem;
        }
        .title {
            margin: 0 0 0.5rem;
            font-size: 1.125rem;
            font-weight: 600;
        }
        .desc {
            margin: 0 0 1rem;
            font-size: 0.875rem;
            color: #64748b;
        }
        .btn {
            display: inline-block;
            padding: 0.5rem 1.25rem;
            background-color: #2563eb;
            color: white;
            border-radius: 0.375rem;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">✓</div>
        <h3 class="title">Journal Entry Berhasil Diperbarui</h3>
        <p class="desc">Mengalihkan kembali ke tab Buku Besar...</p>
        <a id="fallbackBtn" href="{{ $backUrl }}" class="btn">Kembali ke Buku Besar</a>
    </div>

    <script>
        (function() {
            const backUrl = {!! json_encode($backUrl) !!};
            const message = {!! json_encode($message ?? 'Journal entry berhasil diperbarui.') !!};
            try {
                // Notifikasi tab Buku Besar melalui localStorage (bekerja antar-tab)
                localStorage.setItem('rca_journal_updated', JSON.stringify({
                    time: Date.now(),
                    message: message,
                    url: backUrl
                }));

                if (window.opener && !window.opener.closed) {
                    try {
                        window.opener.location.href = backUrl;
                    } catch (e) {
                        console.warn('Opener navigation failed:', e);
                    }
                    window.close();
                } else {
                    window.location.href = backUrl;
                }
            } catch (e) {
                window.location.href = backUrl;
            }

            // Fallback timeout jika window.close() tidak diizinkan oleh browser
            setTimeout(function() {
                try { window.close(); } catch(e) {}
                window.location.href = backUrl;
            }, 600);
        })();
    </script>
</body>
</html>

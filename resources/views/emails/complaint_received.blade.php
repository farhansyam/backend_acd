<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Komplain Baru</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #D32F2F; padding: 32px 24px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 24px; }
        .header p { color: rgba(255,255,255,0.8); margin: 8px 0 0; }
        .body { padding: 32px 24px; }
        .greeting { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 16px; }
        .text { color: #555; line-height: 1.6; margin-bottom: 16px; }
        .card { background: #f8f9fa; border-radius: 8px; padding: 16px; margin: 20px 0; }
        .card-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #eee; }
        .card-row:last-child { border-bottom: none; }
        .card-label { color: #888; font-size: 14px; }
        .card-value { color: #333; font-size: 14px; font-weight: bold; }
        .complaint-box { background: #ffebee; border-left: 4px solid #D32F2F; padding: 16px; border-radius: 4px; margin: 16px 0; }
        .complaint-title { font-weight: bold; color: #D32F2F; margin-bottom: 8px; }
        .complaint-desc { color: #555; font-size: 14px; line-height: 1.6; }
        .footer { background: #f8f9fa; padding: 20px 24px; text-align: center; color: #888; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Komplain Baru</h1>
            <p>Segera tangani komplain dari customer</p>
        </div>
        <div class="body">
            <div class="greeting">Halo, Tim BP!</div>
            <p class="text">Ada komplain baru masuk untuk area kamu. Segera tinjau dan tangani melalui panel BP Dikari.</p>

            <div class="card">
                <div class="card-row">
                    <span class="card-label">Order</span>
                    <span class="card-value">#{{ $complaint->order_id }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Customer</span>
                    <span class="card-value">{{ $complaint->user->name }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Teknisi</span>
                    <span class="card-value">{{ $complaint->technician->user->name ?? '-' }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Diajukan</span>
                    <span class="card-value">{{ $complaint->created_at->format('d M Y H:i') }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Masa Garansi Hingga</span>
                    <span class="card-value">{{ $complaint->warranty_expires_at?->format('d M Y H:i') }}</span>
                </div>
            </div>

            <div class="complaint-box">
                <div class="complaint-title">{{ $complaint->title }}</div>
                <div class="complaint-desc">{{ $complaint->description }}</div>
            </div>

            <p class="text">Login ke panel BP Dikari untuk menangani komplain ini.</p>
        </div>
        <div class="footer">
            © {{ date('Y') }} Dikari · Layanan Service AC Terpercaya<br>
            Email ini dikirim otomatis, mohon tidak membalas.
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pekerjaan Selesai</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #388E3C; padding: 32px 24px; text-align: center; }
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
        .btn { display: block; background: #388E3C; color: #fff; text-decoration: none; padding: 14px 24px; border-radius: 8px; text-align: center; font-weight: bold; margin: 24px 0; }
        .warning { background: #fff3e0; border-left: 4px solid #FF9800; padding: 12px 16px; border-radius: 4px; color: #E65100; font-size: 14px; margin: 16px 0; }
        .footer { background: #f8f9fa; padding: 20px 24px; text-align: center; color: #888; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Pekerjaan Selesai!</h1>
            <p>Teknisi telah menyelesaikan pekerjaan</p>
        </div>
        <div class="body">
            <div class="greeting">Halo, {{ $order->user->name }}!</div>
            <p class="text">Teknisi kami telah menyelesaikan pekerjaan untuk pesanan <strong>#{{ $order->id }}</strong>. Silakan konfirmasi melalui aplikasi Dikari.</p>

            <div class="card">
                <div class="card-row">
                    <span class="card-label">Order</span>
                    <span class="card-value">#{{ $order->id }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Teknisi</span>
                    <span class="card-value">{{ $order->technician?->user?->name ?? '-' }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Total</span>
                    <span class="card-value">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="warning">
                ⏰ Jika tidak dikonfirmasi dalam <strong>30 menit</strong>, pesanan akan otomatis terkonfirmasi dan masa garansi 7 hari akan aktif.
            </div>

            <p class="text">Buka aplikasi Dikari untuk mengkonfirmasi dan memberikan ulasan.</p>
        </div>
        <div class="footer">
            © {{ date('Y') }} Dikari · Layanan Service AC Terpercaya<br>
            Email ini dikirim otomatis, mohon tidak membalas.
        </div>
    </div>
</body>
</html>
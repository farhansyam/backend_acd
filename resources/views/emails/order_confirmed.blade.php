<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Dikonfirmasi</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1976D2; padding: 32px 24px; text-align: center; }
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
        .badge { display: inline-block; background: #e3f2fd; color: #1976D2; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: bold; margin-bottom: 20px; }
        .footer { background: #f8f9fa; padding: 20px 24px; text-align: center; color: #888; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Pesanan Dikonfirmasi</h1>
            <p>Teknisi kami akan segera menuju lokasi kamu</p>
        </div>
        <div class="body">
            <div class="greeting">Halo, {{ $order->user->name }}!</div>
            <p class="text">Pesanan kamu telah dikonfirmasi oleh mitra kami. Teknisi akan datang sesuai jadwal yang telah ditentukan.</p>

            <div class="badge">Order #{{ $order->id }}</div>

            <div class="card">
                <div class="card-row">
                    <span class="card-label">Jadwal</span>
                    <span class="card-value">{{ $order->scheduled_date?->format('d M Y') }} · {{ $order->scheduled_time }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Lokasi</span>
                    <span class="card-value">{{ $order->address?->city_name }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Total</span>
                    <span class="card-value">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="card-row">
                    <span class="card-label">Status Pembayaran</span>
                    <span class="card-value">{{ $order->payment_status === 'paid' ? '✓ Lunas' : 'Belum Bayar' }}</span>
                </div>
            </div>

            <p class="text">Pastikan kamu berada di lokasi saat jadwal tiba. Jika ada pertanyaan, hubungi kami melalui aplikasi Dikari.</p>
        </div>
        <div class="footer">
            © {{ date('Y') }} Dikari · Layanan Service AC Terpercaya<br>
            Email ini dikirim otomatis, mohon tidak membalas.
        </div>
    </div>
</body>
</html>
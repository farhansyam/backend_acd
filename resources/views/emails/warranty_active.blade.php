<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Masa Garansi Aktif</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #00796B; padding: 32px 24px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 24px; }
        .header p { color: rgba(255,255,255,0.8); margin: 8px 0 0; }
        .body { padding: 32px 24px; }
        .greeting { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 16px; }
        .text { color: #555; line-height: 1.6; margin-bottom: 16px; }
        .card { background: #e0f2f1; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
        .card h2 { color: #00796B; margin: 0 0 8px; font-size: 36px; }
        .card p { color: #00796B; margin: 0; font-size: 14px; }
        .info { background: #f8f9fa; border-radius: 8px; padding: 16px; margin: 16px 0; }
        .info-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #eee; }
        .info-row:last-child { border-bottom: none; }
        .info-icon { font-size: 20px; }
        .info-text { color: #555; font-size: 14px; }
        .footer { background: #f8f9fa; padding: 20px 24px; text-align: center; color: #888; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ Masa Garansi Aktif</h1>
            <p>Pesanan #{{ $order->id }} kamu dilindungi garansi</p>
        </div>
        <div class="body">
            <div class="greeting">Halo, {{ $order->user->name }}!</div>
            <p class="text">Pesanan kamu telah selesai dan masa garansi <strong>7 hari</strong> kini aktif. Jika ada masalah dengan hasil pengerjaan, kamu bisa ajukan komplain melalui aplikasi Dikari.</p>

            <div class="card">
                <h2>7 Hari</h2>
                <p>Masa Garansi Aktif</p>
            </div>

            <div class="info">
                <div class="info-row">
                    <span class="info-icon">📅</span>
                    <span class="info-text">Garansi berlaku hingga <strong>{{ $warrantyExpires }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-icon">⚠️</span>
                    <span class="info-text">Ajukan komplain melalui aplikasi Dikari selama masa garansi</span>
                </div>
                <div class="info-row">
                    <span class="info-icon">⭐</span>
                    <span class="info-text">Jangan lupa berikan ulasan untuk teknisi kami</span>
                </div>
            </div>

            <p class="text">Terima kasih sudah mempercayakan perawatan AC kamu kepada Dikari!</p>
        </div>
        <div class="footer">
            © {{ date('Y') }} Dikari · Layanan Service AC Terpercaya<br>
            Email ini dikirim otomatis, mohon tidak membalas.
        </div>
    </div>
</body>
</html>
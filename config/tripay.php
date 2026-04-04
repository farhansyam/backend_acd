<?php

// config/tripay.php
// Isi nilai-nilai ini di .env

return [
    'api_key'       => env('TRIPAY_API_KEY', ''),
    'private_key'   => env('TRIPAY_PRIVATE_KEY', ''),
    'merchant_code' => env('TRIPAY_MERCHANT_CODE', ''),
    'sandbox'       => env('TRIPAY_SANDBOX', true),  // true = sandbox, false = production
    'return_url'    => env('TRIPAY_RETURN_URL', 'https://noegenetic-jiggly-lulu.ngrok-free.dev/api/payment/callback'),
];

// ─── Tambahkan ini ke .env ────────────────────────────────────────
// TRIPAY_API_KEY=your_api_key
// TRIPAY_PRIVATE_KEY=your_private_key
// TRIPAY_MERCHANT_CODE=your_merchant_code
// TRIPAY_SANDBOX=true
// TRIPAY_RETURN_URL=https://dikari.id/payment/return
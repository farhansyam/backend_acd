<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\PhoneController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

// ─── Public ───────────────────────────────────────────────────────
Route::post('/auth/google', [AuthController::class, 'googleLogin']);


// ─── API Wilayah (public) ─────────────────────────────────────────
// Catatan: provinsi, kota, kecamatan sudah ada di web.php
// Tambahkan kelurahan/desa di sini
Route::get('/villages/{districtId}', function ($districtId) {
    return Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/villages/{$districtId}.json")->json();
});

// Callback Tripay
Route::post('/payment/callback', [PaymentController::class, 'callback'])
    ->name('tripay.callback');


// ─── Protected (butuh Sanctum token) ─────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me',     [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Alamat
    Route::get('/addresses',                    [AddressController::class, 'index']);
    Route::post('/addresses',                   [AddressController::class, 'store']);
    Route::get('/addresses/{address}',          [AddressController::class, 'show']);
    Route::put('/addresses/{address}',          [AddressController::class, 'update']);
    Route::delete('/addresses/{address}',       [AddressController::class, 'destroy']);
    Route::patch('/addresses/{address}/primary', [AddressController::class, 'setPrimary']);

    // Nomor Kontak
    Route::get('/phones',                   [PhoneController::class, 'index']);
    Route::post('/phones',                  [PhoneController::class, 'store']);
    Route::put('/phones/{phone}',           [PhoneController::class, 'update']);
    Route::delete('/phones/{phone}',        [PhoneController::class, 'destroy']);
    Route::patch('/phones/{phone}/primary', [PhoneController::class, 'setPrimary']);

    Route::get('/services',              [OrderController::class, 'getServices']);
    Route::get('/orders',                [OrderController::class, 'index']);
    Route::post('/orders',               [OrderController::class, 'store']);
    Route::get('/orders/{order}',        [OrderController::class, 'show']);
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);

    // Payment
    Route::get('/payment/channels',          [PaymentController::class, 'getPaymentChannels']);
    Route::post('/payment/validate-coupon',  [PaymentController::class, 'validateCoupon']);
    Route::post('/payment/create',           [PaymentController::class, 'createTransaction']);
});

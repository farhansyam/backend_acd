<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\PhoneController;
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
});

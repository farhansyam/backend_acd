<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\PhoneController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\DikariPayController;
use App\Http\Controllers\Api\Technician\SurveyReportController;
use App\Http\Controllers\Api\TechnicianController;
use App\Http\Controllers\Api\AssignmentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; // ← sudah ada di file, cek lagi
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\ComplaintController;

// ─── Public ───────────────────────────────────────────────────────
Route::post('/auth/google', [AuthController::class, 'googleLogin']);

Route::get('/reviews', [RatingController::class, 'public']);

Route::get('/settings/wa', function () {
    return response()->json([
        'wa_ac_industri'         => \App\Models\Setting::get('wa_ac_industri'),
        'wa_cs'                  => \App\Models\Setting::get('wa_cs'),
        'wa_message_ac_industri' => \App\Models\Setting::get('wa_message_ac_industri', 'Halo Ac Dikari, saya ingin konsultasi mengenai AC Industri'),
        'wa_message_cs'          => \App\Models\Setting::get('wa_message_cs', 'Halo AC Dikari, saya butuh bantuan'),
    ]);
});

Route::get('/articles', function () {
    return response()->json([
        'articles' => \App\Models\Article::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(fn($a) => [
                'id'        => $a->id,
                'title'     => $a->title,
                'subtitle'  => $a->subtitle,
                'type'      => $a->type,
                'color_hex' => $a->color_hex,
                'image_url' => $a->image_url,
                'content'   => $a->content,
            ]),
    ]);
});

// ─── API Wilayah (public) ─────────────────────────────────────────
// Catatan: provinsi, kota, kecamatan sudah ada di web.php
// Tambahkan kelurahan/desa di sini
Route::get('/villages/{districtId}', function ($districtId) {
    return Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/villages/{$districtId}.json")->json();
});

// Callback Tripay
Route::post('/payment/callback', [PaymentController::class, 'callback'])
    ->name('tripay.callback');

// Webhook Tripay — topup DikariPay
Route::post('/dikaripay/callback', [DikariPayController::class, 'topupCallback'])
    ->name('tripay.topup.callback');


Route::post('/auth/login', [AuthController::class, 'login']);


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

    // Orders
    Route::get('/services',              [OrderController::class, 'getServices']);
    Route::get('/orders',                [OrderController::class, 'index']);
    Route::post('/orders',               [OrderController::class, 'store']);
    Route::get('/orders/{order}',        [OrderController::class, 'show']);
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::patch('/orders/{order}/confirm', [AssignmentController::class, 'customerConfirm']);

    // Payment
    Route::get('/payment/channels',          [PaymentController::class, 'getPaymentChannels']);
    Route::post('/payment/validate-coupon',  [PaymentController::class, 'validateCoupon']);
    Route::post('/payment/create',           [PaymentController::class, 'createTransaction']);

    // ─── DikariPay ────────────────────────────────────────────
    Route::get('/dikaripay/balance',               [DikariPayController::class, 'balance']);
    Route::post('/dikaripay/topup',                [DikariPayController::class, 'topup']);
    Route::post('/dikaripay/pay',                  [DikariPayController::class, 'payOrder']);

    Route::prefix('bp')->group(function () {
        Route::get('/orders/pending',            [AssignmentController::class, 'pendingOrders']);
        Route::get('/orders/history',            [AssignmentController::class, 'orderHistory']);
        Route::get('/technicians',               [AssignmentController::class, 'myTechnicians']);
        Route::post('/orders/assign',            [AssignmentController::class, 'assign']);
        Route::patch('/orders/{order}/complete', [AssignmentController::class, 'complete']);
        Route::get('/balance',                   [AssignmentController::class, 'balance']);
    });

    // Customer
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/orders/perbaikan', [OrderController::class, 'createPerbaikanOrder']);
        Route::get('/orders/{order}/survey-report', [OrderController::class, 'surveyReport']);
        Route::post('/orders/{order}/survey-respond', [OrderController::class, 'respondSurvey']);
    });

    // Teknisi
    Route::middleware(['auth:sanctum', 'role:teknisi'])->group(function () {
        Route::post('/technician/orders/{order}/survey-report', [SurveyReportController::class, 'store']);
    });

    Route::patch('/orders/{order}/confirm-transport', [OrderController::class, 'confirmTransportFee']);


    // ─── Teknisi Routes ───────────────────────────────────────
    Route::prefix('technician')->group(function () {
        Route::get('/orders',                          [TechnicianController::class, 'myOrders']);
        Route::get('/orders/{order}',                  [TechnicianController::class, 'showOrder']);
        Route::patch('/orders/{order}/complete',       [AssignmentController::class, 'technicianComplete']);
        Route::post('/orders/{order}/report',          [TechnicianController::class, 'submitReport']); // ← tambah
        Route::get('/balance',                         [TechnicianController::class, 'balance']);
        Route::post('/withdraw',                       [TechnicianController::class, 'withdraw']);
        Route::get('/dashboard',                       [TechnicianController::class, 'dashboard']);

        Route::get('/profile',           [TechnicianController::class, 'profile']);
        Route::patch('/districts',       [TechnicianController::class, 'updateDistricts']);
        Route::patch('/password',        [TechnicianController::class, 'updatePassword']);
        Route::get('/withdrawals', [TechnicianController::class, 'withdrawals']);
    });

    // Di dalam auth:sanctum middleware
    Route::post('/orders/{order}/rating', [RatingController::class, 'store']);
    Route::get('/orders/{order}/rating',  [RatingController::class, 'show']);


    // Di dalam middleware auth:sanctum
    Route::get('/complaints',                [ComplaintController::class, 'index']);
    Route::post('/orders/{order}/complaint', [ComplaintController::class, 'store']);
    Route::get('/complaints/{complaint}',    [ComplaintController::class, 'show']);


    Route::post('/auth/fcm-token', function (Request $request) {
        \Log::info('FCM route hit', ['user_id' => $request->user()?->id]);
        $request->validate(['fcm_token' => 'required|string']);
        $request->user()->update(['fcm_token' => $request->fcm_token]);
        return response()->json(['message' => 'FCM token updated.']);
    });
});

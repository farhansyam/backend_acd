<?php

use App\Http\Controllers\ServiceTypeController;
use App\Http\Controllers\TechnicianRegisterController;
use App\Http\Controllers\BusinessPartnerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\BpServiceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ComplaintWebController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\OrderAssignController;

Route::get('/', function () {
    return view('auth.login');
});

// Publik - registrasi teknisi
Route::get('/daftar-teknisi', [TechnicianRegisterController::class, 'showForm'])->name('technician.register');
Route::post('/daftar-teknisi', [TechnicianRegisterController::class, 'store'])->name('technician.register.store');
Route::get('/daftar-teknisi/sukses', [TechnicianRegisterController::class, 'success'])->name('technician.register.success');



Auth::routes();

// API Wilayah (semua user login bisa akses)
Route::get('/api/provinces', function () {
    return Http::get('https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json')->json();
});
Route::get('/api/regencies/{provinceId}', function ($provinceId) {
    return Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/regencies/{$provinceId}.json")->json();
});
Route::get('/api/districts/{cityId}', function ($cityId) {
    return Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/districts/{$cityId}.json")->json();
});


// Route yang bisa diakses adminsuper DAN business_partner
Route::middleware(['auth', 'role:adminsuper,business_partner'])->group(function () {
    // Dashboard (view beda tergantung role)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // BP Services
    Route::resource('bp-services', BpServiceController::class);

    Route::get('bp-technicians', [TechnicianController::class, 'index'])->name('bp-technicians.index');
    Route::get('bp-technicians/{technician}', [TechnicianController::class, 'show'])->name('bp-technicians.show');
    Route::delete('bp-technicians/{technician}', [TechnicianController::class, 'destroy'])->name('bp-technicians.destroy');
    Route::post('bp-technicians/{technician}/toggle-active', [TechnicianController::class, 'toggleActive'])->name('bp-technicians.toggle-active');

    // Approval
    Route::get('bp-approvals', [TechnicianController::class, 'approvalIndex'])->name('bp-technicians.approval');
    Route::post('bp-approvals/{technician}/approve', [TechnicianController::class, 'approve'])->name('bp-technicians.approve');
    Route::post('bp-approvals/{technician}/reject', [TechnicianController::class, 'reject'])->name('bp-technicians.reject');


    // Order management
    Route::get('orders', [OrderAssignController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderAssignController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/assign', [OrderAssignController::class, 'assign'])->name('orders.assign');

    Route::post('orders/{order}/set-transport-fee', [OrderAssignController::class, 'setTransportFee'])->name('orders.setTransportFee');
    Route::post('orders/{order}/assign', [OrderAssignController::class, 'assign'])->name('orders.assign');

    Route::get('complaints', [ComplaintWebController::class, 'index'])->name('complaints.index');
    Route::get('complaints/{complaint}', [ComplaintWebController::class, 'show'])->name('complaints.show');
    Route::patch('complaints/{complaint}', [ComplaintWebController::class, 'update'])->name('complaints.update');

    Route::patch('bp-technicians/{technician}/update-grade', [TechnicianController::class, 'updateGrade'])->name('bp-technicians.update-grade');
    Route::patch('bp-technicians/{technician}/suspend',      [TechnicianController::class, 'suspend'])->name('bp-technicians.suspend');
    Route::patch('bp-technicians/{technician}/activate',     [TechnicianController::class, 'activate'])->name('bp-technicians.activate');
});


// Route khusus adminsuper saja
Route::middleware(['auth', 'role:adminsuper'])->group(function () {
    Route::resource('business-partners', BusinessPartnerController::class);
    Route::resource('service-types', ServiceTypeController::class);

    Route::get('withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');

    Route::get('payments',    [AdminController::class, 'payments'])->name('payments.index');
    Route::get('customers',   [AdminController::class, 'customers'])->name('customers.index');
    Route::get('technicians', [AdminController::class, 'technicians'])->name('technicians.index');
    Route::get('wallets',     [AdminController::class, 'wallets'])->name('wallets.index');

    Route::resource('coupons', CouponController::class);
    Route::post('coupons/{coupon}/toggle', [CouponController::class, 'toggleActive'])->name('coupons.toggle');
    Route::get('coupons-generate', [CouponController::class, 'generateCode'])->name('coupons.generate');

    Route::get('settings',  [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings',  [SettingController::class, 'update'])->name('settings.update');

    Route::resource('articles', ArticleController::class)->except(['show']);
    Route::post('articles/{article}/toggle', [ArticleController::class, 'toggleActive'])->name('articles.toggle');
    // tambah route admin only lainnya di sini
});

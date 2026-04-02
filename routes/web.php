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
});


// Route khusus adminsuper saja
Route::middleware(['auth', 'role:adminsuper'])->group(function () {
    Route::resource('business-partners', BusinessPartnerController::class);
    Route::resource('service-types', ServiceTypeController::class);

    // tambah route admin only lainnya di sini
});

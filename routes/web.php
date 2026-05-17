<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

// redirect root
Route::get('/', fn() => redirect()->route('dashboard'));

// guest (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// auth (sudah login)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // TENANT
    Route::get('/tenant', [TenantController::class, 'index'])->name('tenant');
    Route::get('/tenant/datatable', [TenantController::class, 'getDatatable'])->name('tenant.datatable');
    Route::get('/tenant/form/{id?}', [TenantController::class, 'form'])->name('tenant.form');
    Route::post('/tenant/store', [TenantController::class, 'store'])->name('tenant.store');
    Route::put('/tenant/update/{id}', [TenantController::class, 'update'])->name('tenant.update');
    Route::delete('/tenant/{id}', [TenantController::class, 'destroy'])->name('tenant.destroy');
    Route::patch('/tenant/{id}/toggle', [TenantController::class, 'toggleStatus'])->name('tenant.toggle');

    // STORE
    Route::get('/store', [StoreController::class, 'index'])->name('store');
});

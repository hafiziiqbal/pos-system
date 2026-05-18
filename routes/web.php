<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreSettingController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;
use App\Models\StoreSetting;
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
    Route::get('/tenants/search', [TenantController::class, 'searchTenant']);

    // STORE
    Route::get('/store', [StoreController::class, 'index'])->name('store');
    Route::get('/store/datatable', [StoreController::class, 'getDatatable'])->name('store.datatable');
    Route::get('/store/form/{id?}', [StoreController::class, 'form'])->name('store.form');
    Route::post('/store/store', [StoreController::class, 'store'])->name('store.store');
    Route::put('/store/update/{id}', [StoreController::class, 'update'])->name('store.update');
    Route::delete('/store/{id}', [StoreController::class, 'destroy'])->name('store.destroy');
    Route::patch('/store/{id}/toggle', [StoreController::class, 'toggleStatus'])->name('store.toggle');
    Route::get('/store/search', [StoreController::class, 'searchStore']);

    // STORE SETTING
    Route::get('/store/{store_id}/settings', [StoreSettingController::class, 'index'])->name('store.settings');
    Route::get('/store/{store_id}/settings/data', [StoreSettingController::class, 'getDatatable'])->name('store.settings.datatable');
    Route::post('/store/{store_id}/settings/store', [StoreSettingController::class, 'store'])->name('store.settings.store');
    Route::put('/store/{store_id}/settings/{id}/update', [StoreSettingController::class, 'update'])->name('store.settings.update');
    Route::delete('/store/{store_id}/settings/{id}/destroy', [StoreSettingController::class, 'destroy'])->name('store.settings.destroy');

    // USER
    Route::get('/user', [UserController::class, 'index'])->name('user');
    Route::get('/user/datatable', [UserController::class, 'getDatatable'])->name('user.datatable');
    Route::get('/user/form/{id?}', [UserController::class, 'form'])->name('user.form');
    Route::post('/user/store', [UserController::class, 'store'])->name('user.store');
    Route::put('/user/update/{id}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::patch('/user/{id}/toggle', [UserController::class, 'toggleStatus'])->name('user.toggle');
});

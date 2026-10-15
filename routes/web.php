<?php

use Illuminate\Support\Facades\Route;

// --- 1. KHU VỰC KHÁCH HÀNG (CUSTOMER) ---
Route::middleware(['auth', 'role:customer,admin'])->group(function () {
    Route::get('/cart', function () { return view('pages.cart'); })->name('cart');
    Route::get('/checkout', function () { return view('pages.checkout'); })->name('checkout');
    Route::get('/subscriptions/setup', function () { return view('pages.subscription'); })->name('subscription.setup');
});

// --- 2. KHU VỰC BAN QUẢN TRỊ (ADMIN) ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () { return view('admin.dashboard'); })->name('dashboard');
    Route::get('/users', function () { return view('admin.users.index'); })->name('users.index');
    Route::get('/settings', function () { return view('admin.settings'); })->name('settings');
});

// --- 3. KHU VỰC NÔNG TRẠI (FARM STAFF) ---
Route::middleware(['auth', 'role:farm_staff,admin'])->prefix('farm')->name('farm.')->group(function () {
    Route::get('/plots', function () { return view('farm.plots'); })->name('plots');
    Route::get('/batches', function () { return view('farm.batches'); })->name('batches');
    Route::get('/traceability/log', function () { return view('farm.traceability'); })->name('traceability.log');
});

// --- 4. KHU VỰC SALES & CSKH (SALES) ---
Route::middleware(['auth', 'role:sales,admin'])->prefix('sales')->name('sales.')->group(function () {
    Route::get('/orders', function () { return view('sales.orders'); })->name('orders');
    Route::get('/complaints', function () { return view('sales.complaints'); })->name('complaints');
});
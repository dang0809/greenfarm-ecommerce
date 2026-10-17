<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Customer\HomePage;
use App\Livewire\Customer\ProductPage;
use App\Livewire\Customer\AboutPage;
use App\Livewire\Customer\ProductDetail;

Route::get('/', HomePage::class)->name('home');
Route::get('/login', function () {
    return 'Trang Đăng Nhập (Đang xây dựng)';
})->name('login');
Route::get('/san-pham', ProductPage::class)->name('products.index');

Route::get('/ve-nong-trai', AboutPage::class)->name('about');

Route::get('/san-pham/{slug}', ProductDetail::class)->name('customer.product.detail');
Route::get('/truy-xuat/{batch}', function ($batch) {
    return "Trang chi tiết truy xuất lô: " . $batch;
})->name('customer.traceability.detail');
<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Customer\HomePage;
use App\Livewire\Customer\ProductPage;

Route::get('/', HomePage::class)->name('home');
Route::get('/login', function () {
    return 'Trang Đăng Nhập (Đang xây dựng)';
})->name('login');
Route::get('/san-pham', ProductPage::class)->name('products.index');
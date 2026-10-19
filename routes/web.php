<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
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

// Route xử lý Link Xác Thực Email
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    // 1. Kiểm tra SHA-1 hash email
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        session()->flash('error', 'Link xác thực không hợp lệ!');
        return redirect()->route('home');
    }

    // 2. Kiểm tra chữ ký bảo mật (Hạn 60 phút)
    if (! $request->hasValidSignature()) {
        session()->flash('error', 'Link xác thực đã hết hạn!');
        return redirect()->route('home');
    }

    // 3. Kích hoạt tài khoản: đổi status thành 'active'
    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']); // Hoặc 1 nếu DB lưu dạng integer
    }

    // 4. Đăng nhập và đưa về trang chủ
    Auth::login($user);
    session()->flash('success', 'Xác thực Email thành công! Tài khoản của bạn đã được kích hoạt.');

    return redirect()->route('home');
})->name('verification.verify');
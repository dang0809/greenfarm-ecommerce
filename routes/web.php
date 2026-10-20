<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// Import đầy đủ các Livewire Components
use App\Livewire\Customer\HomePage;
use App\Livewire\Customer\ProductPage;
use App\Livewire\Customer\AboutPage;
use App\Livewire\Customer\ProductDetail;
use App\Livewire\Customer\VipCheckout;
use App\Livewire\Customer\SubscriptionSetup;
use App\Livewire\Customer\CartPage;
use App\Livewire\Customer\CheckoutPage;
use App\Livewire\Customer\ProfilePage;
use App\Livewire\Customer\OrdersPage;
use App\Livewire\Customer\TraceabilityDetail;
/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Ai cũng xem được - Không cần đăng nhập)
|--------------------------------------------------------------------------
*/
Route::get('/', HomePage::class)->name('home');
Route::get('/san-pham', ProductPage::class)->name('products.index');
Route::get('/ve-nong-trai', AboutPage::class)->name('about');
Route::get('/san-pham/{slug}', ProductDetail::class)->name('customer.product.detail');

Route::get('/truy-xuat/{batch}', TraceabilityDetail::class)->name('customer.traceability.detail');

// Route dự phòng: Nếu hệ thống hoặc thư viện cố tình gọi route('login'), tự động đẩy về Trang chủ & mở Popup
Route::get('/login', function () {
    return redirect()->route('home')->with('open_auth_modal', true);
})->name('login');

// Route xử lý Link Xác Thực Email từ Mail gửi về
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
        $user->update(['status' => 'active']);
    }

    // 4. Đăng nhập và đưa về trang chủ
    Auth::login($user);
    session()->flash('success', 'Xác thực Email thành công! Tài khoản của bạn đã được kích hoạt.');

    return redirect()->route('home');
})->name('verification.verify');



/*
|--------------------------------------------------------------------------
| 2. CUSTOMER PROTECTED ROUTES (Bắt buộc Đăng nhập & Đúng vai trò Customer)
|--------------------------------------------------------------------------
*/
Route::middleware(['role:customer'])->group(function () {
    
    // Trang Thanh toán gói VIP
    Route::get('/goi-vip/thanh-toan', VipCheckout::class)->name('customer.vip.checkout');
    Route::get('/goi-vip/thiet-lap-lich', SubscriptionSetup::class)->name('customer.vip.setup');
    Route::get('/gio-hang', CartPage::class)->name('customer.cart');
    Route::get('/ho-so', ProfilePage::class)->name('customer.profile');
    Route::get('/thanh-toan', CheckoutPage::class)->name('customer.checkout');
    // Sau này các trang riêng tư của khách bổ sung ở đây:
    // Route::get('/gio-hang', CartPage::class)->name('customer.cart');
    // Route::get('/don-hang-cua-toi', OrderHistory::class)->name('customer.orders');
});
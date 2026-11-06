<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\Customer\PaymentController;
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
use App\Livewire\Customer\OrderDetail;
use App\Livewire\Customer\TraceabilityDetail;
use App\Http\Controllers\Api\PayOSWebhookController;
// Import đầy đủ các Livewire Farm Staff Components
use App\Livewire\FarmStaff\ProductBatchManager;
use App\Livewire\FarmStaff\FarmPlotManager;
use App\Livewire\FarmStaff\ProfileManager;
use App\Livewire\FarmStaff\CertificationManager;
use App\Livewire\FarmStaff\ProductManager;
use App\Livewire\FarmStaff\CategoryManager;
// Import Authentication & Sales Components
use App\Livewire\Auth\Login;
use App\Livewire\Sales\OrderManager;
use App\Livewire\Sales\SubscriptionManager;
use App\Livewire\Sales\ComplaintManager;
use App\Livewire\Sales\ClearanceManager;
/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Ai cũng xem được - Không cần đăng nhập)
|--------------------------------------------------------------------------
*/
Route::get('/', HomePage::class)->name('home');
Route::get('/quan-tri/dang-nhap', Login::class)->name('login');
Route::get('/san-pham', ProductPage::class)->name('products.index');
Route::get('/ve-nong-trai', AboutPage::class)->name('about');
Route::get('/san-pham/{slug}', ProductDetail::class)->name('customer.product.detail');

Route::get('/truy-xuat/{batch}', TraceabilityDetail::class)->name('customer.traceability.detail');


// Route xử lý Link Xác Thực Email từ Mail gửi về
// Xác thực email và tự động đăng nhập khách hàng.
Route::get(
    '/email/verify/{id}/{hash}',
    function (Request $request, string $id, string $hash) {
        // 1. Kiểm tra chữ ký và thời hạn trước khi xử lý tài khoản.
        if (! $request->hasValidSignature()) {
            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'Link xác thực không hợp lệ hoặc đã hết hạn.'
                );
        }

        // 2. Link này chỉ phục vụ tài khoản khách hàng.
        $user = User::query()
            ->where('role', 'customer')
            ->find($id);

        if (
            ! $user
            || ! hash_equals(
                sha1($user->getEmailForVerification()),
                $hash
            )
        ) {
            return redirect()
                ->route('home')
                ->with('error', 'Link xác thực không hợp lệ.');
        }

        // 3. Không cho kích hoạt tài khoản bị khóa
        // hoặc thuộc trạng thái khác.
        if (
            ! in_array(
                $user->status,
                ['inactive', 'active'],
                true
            )
        ) {
            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'Tài khoản không thể được kích hoạt.'
                );
        }

        $newlyVerified = ! $user->hasVerifiedEmail();

        // 4. Xác thực lần đầu.
        if ($newlyVerified) {
            $user->status = 'active';

            // Phương thức này gán email_verified_at rồi save().
            // Vì status đang thay đổi, cả hai trường được lưu
            // trong cùng lần save.
            if (! $user->markEmailAsVerified()) {
                return redirect()
                    ->route('home')
                    ->with(
                        'error',
                        'Chưa thể kích hoạt tài khoản. Vui lòng thử lại.'
                    );
            }

            event(new \Illuminate\Auth\Events\Verified($user));
        }

        // 5. Chỉ đăng nhập khi tài khoản đã xác thực và active.
        if (
            $user->status !== 'active'
            || ! $user->hasVerifiedEmail()
        ) {
            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'Tài khoản chưa đủ điều kiện đăng nhập.'
                );
        }

        // 6. Tự động đăng nhập vào guard khách hàng.
        $guard = Auth::guard('web');

        // Dọn danh tính/cookie ghi nhớ khách hàng trước đó
        // nếu trình duyệt đang có tài khoản web khác.
        $guard->logoutCurrentDevice();

        $guard->login($user);

        // Không mở lại popup đăng nhập sau khi đã tự đăng nhập.
        $request->session()->forget('open_auth_modal');

        // 7. Về trang chủ; header sẽ đọc được customer vừa đăng nhập.
        return redirect()
            ->route('home')
            ->with(
                'success',
                $newlyVerified
                    ? 'Xác thực email thành công! Bạn đã được tự động đăng nhập.'
                    : 'Email đã được xác thực. Bạn đã được đăng nhập.'
            );
    }
)
    ->whereNumber('id')
    ->name('verification.verify');



/*
|--------------------------------------------------------------------------
| 2. CUSTOMER PROTECTED ROUTES (Bắt buộc Đăng nhập & Đúng vai trò Customer)
|--------------------------------------------------------------------------
*/
Route::middleware(['role:web,customer'])->group(function () {
    
    // Trang Thanh toán gói VIP
    Route::get('/goi-vip/thanh-toan', VipCheckout::class)->name('customer.vip.checkout');
    Route::get('/goi-vip/thiet-lap-lich', SubscriptionSetup::class)->name('customer.vip.setup');
    Route::get('/gio-hang', CartPage::class)->name('customer.cart');
    Route::get('/ho-so', ProfilePage::class)->name('customer.profile');
    Route::get('/thanh-toan', CheckoutPage::class)->name('customer.checkout');

    // 🟢 ROUTES XỬ LÝ KẾT QUẢ THANH TOÁN PAYOS
    Route::get('/thanh-toan/thanh-cong/{order_id}', [PaymentController::class, 'success'])->name('customer.checkout.success');
    Route::get('/thanh-toan/huy/{order_id}', [PaymentController::class, 'cancel'])->name('customer.checkout.cancel');
    Route::get('/don-hang/{order_code}', OrderDetail::class)->name('customer.order.detail');
});


/*
|--------------------------------------------------------------------------
| 3. FARM STAFF PROTECTED ROUTES (Nhân viên Nông trại đã đăng nhập | Vai trò farm_staff)
|--------------------------------------------------------------------------
*/
Route::middleware(['role:staff,farm_staff'])->prefix('nhan-vien-nong-trai')->name('farm-staff.')->group(function () {
    Route::get('/', function () {
            return redirect()->route('farm-staff.farm-plot');
        })->name('dashboard');
    Route::get('/luong-dat', FarmPlotManager::class)->name('farm-plot');
    Route::get('/lo-nong-san', ProductBatchManager::class)->name('product-batch');
    Route::get('/giay-chung-nhan', CertificationManager::class)->name('certification');
    Route::get('/quan-ly-san-pham', ProductManager::class)->name('product-manager');
    Route::get('/quan-ly-danh-muc', CategoryManager::class)->name('category-manager');
    Route::get('/ho-so', ProfileManager::class)->name('profile');
});

/*
|--------------------------------------------------------------------------
| 4. SALES STAFF PROTECTED ROUTES (Nhân viên Bán hàng & Quản lý đơn)
|--------------------------------------------------------------------------
*/
Route::middleware(['role:staff,sales'])->prefix('nhan-vien-ban-hang')->name('sales.')->group(function () {
    Route::get('/', OrderManager::class)->name('orders');
    Route::get('/don-hang', OrderManager::class)->name('orders');
    Route::get('/khieu-nai', ComplaintManager::class)->name('complaints');
    Route::get('/xa-hang', ClearanceManager::class)->name('clearance');
    Route::get('/goi-dinh-ky', SubscriptionManager::class)->name('subscriptions');
});